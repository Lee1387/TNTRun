<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena\io;

use FilesystemIterator;
use PharData;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

final class ArenaWorldArchiveExtractor {
    public function extract(ArenaWorldSource $worldSource, string $destinationPath, string $arenaName): string {
        if ($worldSource->getType() === ArenaWorldSourceType::ZIP) {
            $this->extractZip($worldSource->getPath(), $destinationPath, $arenaName);
        } else {
            $this->extractTar($worldSource->getPath(), $destinationPath, $arenaName);
        }

        return $this->resolveExtractedWorldPath($destinationPath, $worldSource->getWorldName(), $arenaName);
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
            if (!$item instanceof SplFileInfo || !$item->isDir()) {
                continue;
            }

            $candidatePath = $item->getPathname();
            if ($this->isValidWorldDirectory($candidatePath)) {
                $worldDirectories[] = $candidatePath;
            }
        }

        return $worldDirectories;
    }

    private function isValidWorldDirectory(string $directoryPath): bool {
        return \is_dir($directoryPath) && \is_file($directoryPath . DIRECTORY_SEPARATOR . "level.dat");
    }
}
