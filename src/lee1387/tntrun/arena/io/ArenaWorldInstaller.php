<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena\io;

use DirectoryIterator;
use lee1387\tntrun\arena\ArenaConfig;
use RuntimeException;

final class ArenaWorldInstaller {
    private ArenaWorldArchiveExtractor $archiveExtractor;

    public function __construct(
        private string $worldsDirectory,
        private string $temporaryDirectory,
        ?ArenaWorldArchiveExtractor $archiveExtractor = null
    ) {
        $this->archiveExtractor = $archiveExtractor ?? new ArenaWorldArchiveExtractor();
    }

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
            $extractedWorldPath = $this->archiveExtractor->extract($worldSource, $temporaryExtractionPath, $arenaName);
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

    private function assertWorldInstalled(string $worldPath, string $arenaName): void {
        if (!$this->isValidWorldDirectory($worldPath)) {
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
