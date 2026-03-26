<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena\io;

use DirectoryIterator;
use FilesystemIterator;
use lee1387\tntrun\arena\ArenaConfig;
use PharData;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

final class ArenaWorldInstaller {
    public function __construct(
        private string $worldsDirectory,
        private string $temporaryDirectory
    ) {}

    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     */
    public function installAll(array $arenaConfigs): void {
        $this->ensureDirectoryExists($this->worldsDirectory);
        $this->ensureDirectoryExists($this->temporaryDirectory);

        foreach ($arenaConfigs as $arenaConfig) {
            $this->install($arenaConfig);
        }
    }

    public function install(ArenaConfig $arenaConfig): void {
        $targetWorldPath = $this->worldsDirectory . DIRECTORY_SEPARATOR . $arenaConfig->getWorldName();
        if ($this->isValidWorldDirectory($targetWorldPath)) {
            return;
        }

        if (is_dir($targetWorldPath)) {
            $this->deleteDirectory($targetWorldPath);
        }

        $worldSource = $arenaConfig->getWorldSource();
        $stagingWorldPath = $this->createTemporaryInstallationPath($arenaConfig->getWorldName());

        try {
            if ($worldSource->getType() === ArenaWorldSourceType::DIRECTORY) {
                $this->copyDirectory($worldSource->getPath(), $stagingWorldPath);
            } else {
                $this->installFromArchive($worldSource, $stagingWorldPath, $arenaConfig->getName());
            }

            $this->assertWorldInstalled($stagingWorldPath, $arenaConfig->getName());
            $this->moveInstalledWorld($stagingWorldPath, $targetWorldPath, $arenaConfig->getName());
        } finally {
            if (is_dir($stagingWorldPath)) {
                $this->deleteDirectory($stagingWorldPath);
            }
        }
    }

    private function installFromArchive(ArenaWorldSource $worldSource, string $stagingWorldPath, string $arenaName): void {
        $temporaryExtractionPath = $this->createTemporaryExtractionPath($arenaName);

        try {
            if ($worldSource->getType() === ArenaWorldSourceType::ZIP) {
                $this->extractZip($worldSource->getPath(), $temporaryExtractionPath, $arenaName);
            } else {
                $this->extractTar($worldSource->getPath(), $temporaryExtractionPath, $arenaName);
            }

            $extractedWorldPath = $this->resolveExtractedWorldPath($temporaryExtractionPath, $worldSource->getWorldName(), $arenaName);
            $this->copyDirectory($extractedWorldPath, $stagingWorldPath);
        } finally {
            $this->deleteDirectory($temporaryExtractionPath);
        }
    }

    private function createTemporaryExtractionPath(string $arenaName): string {
        $temporaryPath = $this->temporaryDirectory . DIRECTORY_SEPARATOR . $arenaName . "_" . uniqid("", true);
        $this->ensureDirectoryExists($temporaryPath);

        return $temporaryPath;
    }

    private function createTemporaryInstallationPath(string $worldName): string {
        return $this->worldsDirectory . DIRECTORY_SEPARATOR . "." . $worldName . "_install_" . uniqid("", true);
    }

    private function extractZip(string $archivePath, string $destinationPath, string $arenaName): void {
        $zipArchive = new ZipArchive();
        if ($zipArchive->open($archivePath) !== true) {
            throw new RuntimeException(\sprintf('Failed to open ZIP world source for arena "%s".', $arenaName));
        }

        try {
            if (!$zipArchive->extractTo($destinationPath)) {
                throw new RuntimeException(\sprintf('Failed to extract ZIP world source for arena "%s".', $arenaName));
            }
        } finally {
            $zipArchive->close();
        }
    }

    private function extractTar(string $archivePath, string $destinationPath, string $arenaName): void {
        try {
            $pharData = new PharData($archivePath);
            $pharData->extractTo($destinationPath, null, true);
        } catch (\Exception $exception) {
            throw new RuntimeException(
                \sprintf('Failed to extract TAR world source for arena "%s".', $arenaName),
                previous: $exception
            );
        }
    }

    private function resolveExtractedWorldPath(string $extractionPath, string $worldName, string $arenaName): string {
        $expectedWorldPath = $extractionPath . DIRECTORY_SEPARATOR . $worldName;
        if ($this->isValidWorldDirectory($expectedWorldPath)) {
            return $expectedWorldPath;
        }

        if ($this->isValidWorldDirectory($extractionPath)) {
            return $extractionPath;
        }

        $worldDirectories = $this->findWorldDirectories($extractionPath);

        if (\count($worldDirectories) === 1) {
            return $worldDirectories[0];
        }

        if ($worldDirectories === []) {
            throw new RuntimeException(\sprintf(
                'Failed to resolve the extracted world folder for arena "%s". The archive must contain a valid world with "level.dat".',
                $arenaName
            ));
        }

        throw new RuntimeException(\sprintf(
            'Failed to resolve the extracted world folder for arena "%s". Multiple possible world folders were found in the archive.',
            $arenaName
        ));
    }

    private function assertWorldInstalled(string $worldPath, string $arenaName): void {
        if (!$this->isValidWorldDirectory($worldPath)) {
            throw new RuntimeException(\sprintf(
                'Arena "%s" did not install a valid world. The installed world is missing "level.dat".',
                $arenaName
            ));
        }
    }

    /**
     * @return list<string>
     */
    private function findWorldDirectories(string $directoryPath): array {
        $worldDirectories = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directoryPath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if (!$item instanceof SplFileInfo) {
                continue;
            }

            if (!$item->isDir()) {
                continue;
            }

            $candidatePath = $item->getPathname();
            if ($this->isValidWorldDirectory($candidatePath)) {
                $worldDirectories[] = $candidatePath;
            }
        }

        return $worldDirectories;
    }

    private function copyDirectory(string $sourcePath, string $destinationPath): void {
        $this->ensureDirectoryExists($destinationPath);

        foreach (new DirectoryIterator($sourcePath) as $item) {
            if ($item->isDot()) {
                continue;
            }

            $destinationItemPath = $destinationPath . DIRECTORY_SEPARATOR . $item->getFilename();
            if ($item->isDir()) {
                $this->copyDirectory($item->getPathname(), $destinationItemPath);
                continue;
            }

            if (!copy($item->getPathname(), $destinationItemPath)) {
                throw new RuntimeException(\sprintf('Failed to copy "%s" to "%s".', $item->getPathname(), $destinationItemPath));
            }
        }
    }

    private function moveInstalledWorld(string $sourcePath, string $destinationPath, string $arenaName): void {
        if (rename($sourcePath, $destinationPath)) {
            return;
        }

        throw new RuntimeException(\sprintf(
            'Failed to move the installed world for arena "%s" into the server worlds directory.',
            $arenaName
        ));
    }

    private function deleteDirectory(string $directoryPath): void {
        if (!is_dir($directoryPath)) {
            return;
        }

        foreach (new DirectoryIterator($directoryPath) as $item) {
            if ($item->isDot()) {
                continue;
            }

            if ($item->isDir()) {
                $this->deleteDirectory($item->getPathname());
                continue;
            }

            if (!unlink($item->getPathname())) {
                throw new RuntimeException(\sprintf('Failed to delete temporary file "%s".', $item->getPathname()));
            }
        }

        if (!rmdir($directoryPath)) {
            throw new RuntimeException(\sprintf('Failed to delete temporary directory "%s".', $directoryPath));
        }
    }

    private function isValidWorldDirectory(string $directoryPath): bool {
        return is_dir($directoryPath) && is_file($directoryPath . DIRECTORY_SEPARATOR . "level.dat");
    }

    private function ensureDirectoryExists(string $directoryPath): void {
        if (is_dir($directoryPath)) {
            return;
        }

        if (!mkdir($directoryPath, 0777, true) && !is_dir($directoryPath)) {
            throw new RuntimeException(\sprintf('Failed to create directory "%s".', $directoryPath));
        }
    }
}
