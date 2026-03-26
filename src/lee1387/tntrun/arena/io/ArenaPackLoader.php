<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena\io;

use DirectoryIterator;
use InvalidArgumentException;
use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\config\ConfigValueReader;
use pocketmine\utils\Config;
use RuntimeException;

final class ArenaPackLoader {
    public function __construct(
        private string $arenasDirectory,
        private ConfigValueReader $valueReader = new ConfigValueReader()
    ) {}

    /**
     * @return array<string, ArenaConfig>
     */
    public function load(): array {
        $this->ensureArenasDirectoryExists();

        $arenaConfigs = [];

        foreach (new DirectoryIterator($this->arenasDirectory) as $arenaDirectory) {
            if ($arenaDirectory->isDot() || !$arenaDirectory->isDir()) {
                continue;
            }

            $arenaName = $this->normalizeArenaName($arenaDirectory->getFilename());
            if (isset($arenaConfigs[$arenaName])) {
                throw new InvalidArgumentException(\sprintf('Duplicate arena name "%s" found in arena packs.', $arenaName));
            }

            $arenaPath = $arenaDirectory->getPathname();
            $arenaConfigPath = $arenaPath . DIRECTORY_SEPARATOR . "arena.yml";
            if (!is_file($arenaConfigPath)) {
                throw new InvalidArgumentException(\sprintf('Arena pack "%s" must contain an "arena.yml" file.', $arenaName));
            }

            $arenaConfigData = (new Config($arenaConfigPath, Config::YAML))->getAll();
            $arenaConfigKey = "arenas.$arenaName";
            $arenaConfigs[$arenaName] = $this->loadArenaConfig(
                $arenaName,
                $this->valueReader->requireArray($arenaConfigData, $arenaConfigKey),
                $arenaConfigKey,
                $this->detectWorldSource($arenaName, $arenaPath)
            );
        }

        return $arenaConfigs;
    }

    private function ensureArenasDirectoryExists(): void {
        if (is_dir($this->arenasDirectory)) {
            return;
        }

        if (!mkdir($this->arenasDirectory, 0777, true) && !is_dir($this->arenasDirectory)) {
            throw new RuntimeException(\sprintf('Failed to create arenas directory "%s".', $this->arenasDirectory));
        }
    }

    private function detectWorldSource(string $arenaName, string $arenaPath): ArenaWorldSource {
        $sources = [];

        foreach ($this->getWorldSourceCandidates($arenaName, $arenaPath) as [$type, $path]) {
            if (($type === ArenaWorldSourceType::DIRECTORY && is_dir($path)) || ($type !== ArenaWorldSourceType::DIRECTORY && is_file($path))) {
                $sources[] = new ArenaWorldSource($type, $path, $arenaName);
            }
        }

        if (\count($sources) === 1) {
            return $sources[0];
        }

        if ($sources === []) {
            throw new InvalidArgumentException(\sprintf(
                'Arena pack "%s" must contain exactly one world source: "%s", "%s.zip", or "%s.tar".',
                $arenaName,
                $arenaName,
                $arenaName,
                $arenaName
            ));
        }

        throw new InvalidArgumentException(\sprintf(
            'Arena pack "%s" contains multiple world sources. Keep only one of "%s", "%s.zip", or "%s.tar".',
            $arenaName,
            $arenaName,
            $arenaName,
            $arenaName
        ));
    }

    /**
     * @return list<array{ArenaWorldSourceType, string}>
     */
    private function getWorldSourceCandidates(string $arenaName, string $arenaPath): array {
        return [
            [ArenaWorldSourceType::DIRECTORY, $arenaPath . DIRECTORY_SEPARATOR . $arenaName],
            [ArenaWorldSourceType::ZIP, $arenaPath . DIRECTORY_SEPARATOR . "$arenaName.zip"],
            [ArenaWorldSourceType::TAR, $arenaPath . DIRECTORY_SEPARATOR . "$arenaName.tar"],
        ];
    }

    /**
     * @param array<string, mixed> $arenaData
     */
    private function loadArenaConfig(string $arenaName, array $arenaData, string $arenaPath, ArenaWorldSource $worldSource): ArenaConfig {
        return new ArenaConfig(
            $arenaName,
            $worldSource,
            $this->valueReader->loadSpawn($arenaData, "spectator-spawn", $arenaPath . ".spectator-spawn"),
            $this->valueReader->loadSpawnList($arenaData, "player-spawns", $arenaPath . ".player-spawns"),
            $this->valueReader->requireInt($arenaData, "elimination-y", $arenaPath . ".elimination-y"),
            $this->valueReader->requireInt($arenaData, "min-players", $arenaPath . ".min-players"),
            $this->valueReader->requireInt($arenaData, "max-players", $arenaPath . ".max-players"),
            $this->valueReader->requireInt($arenaData, "countdown-seconds", $arenaPath . ".countdown-seconds"),
            $this->valueReader->requireInt($arenaData, "block-fall-delay-ticks", $arenaPath . ".block-fall-delay-ticks")
        );
    }

    private function normalizeArenaName(string $arenaName): string {
        $normalizedArenaName = \trim($arenaName);
        if ($normalizedArenaName === "") {
            throw new InvalidArgumentException("Arena names cannot be empty.");
        }

        return $normalizedArenaName;
    }
}
