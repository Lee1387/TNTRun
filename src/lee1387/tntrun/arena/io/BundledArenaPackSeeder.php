<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena\io;

use FilesystemIterator;
use SplFileInfo;

final class BundledArenaPackSeeder {
    /**
     * @var array<string, SplFileInfo>
     */
    private array $resources;

    /**
     * @var \Closure(string): bool
     */
    private \Closure $saveResource;

    /**
     * @param array<string, SplFileInfo> $resources
     * @param \Closure(string): bool $saveResource
     */
    public function __construct(
        private string $dataFolder,
        array $resources,
        \Closure $saveResource
    ) {
        $this->resources = $resources;
        $this->saveResource = $saveResource;
    }

    public function seed(): void {
        $arenasDirectory = $this->dataFolder . "arenas";
        if (!$this->shouldSeedBundledArenaPacks($arenasDirectory)) {
            return;
        }

        foreach ($this->resources as $resourcePath => $_) {
            $normalizedResourcePath = \str_replace("\\", "/", $resourcePath);
            if (!\str_starts_with($normalizedResourcePath, "arenas/")) {
                continue;
            }

            ($this->saveResource)($normalizedResourcePath);
        }
    }

    private function shouldSeedBundledArenaPacks(string $arenasDirectory): bool {
        if (!is_dir($arenasDirectory)) {
            return true;
        }

        foreach (new FilesystemIterator($arenasDirectory, FilesystemIterator::SKIP_DOTS) as $_) {
            return false;
        }

        return true;
    }
}
