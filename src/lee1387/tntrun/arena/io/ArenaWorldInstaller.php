<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena\io;

use DirectoryIterator;
use lee1387\tntrun\arena\ArenaConfig;
use PharData;
use RuntimeException;
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
        if (is_dir($targetWorldPath)) {
            return;
        }

        $worldSource = $arenaConfig->getWorldSource();
        if ($worldSource->getType() === ArenaWorldSourceType::DIRECTORY) {
            $this->copyDirectory($worldSource->getPath(), $targetWorldPath);
            $this->assertWorldInstalled($targetWorldPath, $arenaConfig->getName());

            return;
        }

        $temporaryExtractionPath = $this->createTemporaryExtractionPath($arenaConfig->getName());

        try {
            if ($worldSource->getType() === ArenaWorldSourceType::ZIP) {
                $this->extractZip($worldSource->getPath(), $temporaryExtractionPath, $arenaConfig->getName());
            } else {
                $this->extractTar($worldSource->getPath(), $temporaryExtractionPath, $arenaConfig->getName());
            }

            $extractedWorldPath = $this->resolveExtractedWorldPath($temporaryExtractionPath, $arenaConfig->getWorldName(), $arenaConfig->getName());
            $this->copyDirectory($extractedWorldPath, $targetWorldPath);
            $this->assertWorldInstalled($targetWorldPath, $arenaConfig->getName());
        } finally {
            $this->deleteDirectory($temporaryExtractionPath);
        }
    }

    private function createTemporaryExtractionPath(string $arenaName): string {
        $temporaryPath = $this->temporaryDirectory . DIRECTORY_SEPARATOR . $arenaName . "_" . uniqid("", true);
        $this->ensureDirectoryExists($temporaryPath);

        return $temporaryPath;
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
        if (is_dir($expectedWorldPath)) {
            return $expectedWorldPath;
        }

        $subdirectories = [];
        $files = [];

        foreach (new DirectoryIterator($extractionPath) as $item) {
            if ($item->isDot()) {
                continue;
            }

            if ($item->isDir()) {
                $subdirectories[] = $item->getPathname();
                continue;
            }

            $files[] = $item->getFilename();
        }

        if (\count($subdirectories) === 1 && $files === []) {
            return $subdirectories[0];
        }

        if (is_file($extractionPath . DIRECTORY_SEPARATOR . "level.dat")) {
            return $extractionPath;
        }

        throw new RuntimeException(\sprintf(
            'Failed to resolve the extracted world folder for arena "%s". The archive must contain a world folder or world files rooted correctly.',
            $arenaName
        ));
    }

    private function assertWorldInstalled(string $worldPath, string $arenaName): void {
        if (!is_file($worldPath . DIRECTORY_SEPARATOR . "level.dat")) {
            throw new RuntimeException(\sprintf(
                'Arena "%s" did not install a valid world. The installed world is missing "level.dat".',
                $arenaName
            ));
        }
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

    private function ensureDirectoryExists(string $directoryPath): void {
        if (is_dir($directoryPath)) {
            return;
        }

        if (!mkdir($directoryPath, 0777, true) && !is_dir($directoryPath)) {
            throw new RuntimeException(\sprintf('Failed to create directory "%s".', $directoryPath));
        }
    }
}
