<?php

declare(strict_types=1);

namespace lee1387\tntrun\config;

use InvalidArgumentException;
use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\arena\ArenaSpawn;
use lee1387\tntrun\support\LeaveDestination;
use lee1387\tntrun\waiting\WaitingWorldConfig;
use pocketmine\utils\Config;

final class TNTRunConfigLoader {
    public function __construct(
        private Config $config,
        private Config $arenasConfig
    ) {}

    /**
     * @return array{waitingWorld: WaitingWorldConfig, leaveDestination: LeaveDestination, arenas: array<string, ArenaConfig>}
     */
    public function load(): array {
        return [
            "waitingWorld" => $this->loadWaitingWorldConfig(),
            "leaveDestination" => $this->loadLeaveDestination(),
            "arenas" => $this->loadArenaConfigs(),
        ];
    }

    private function loadWaitingWorldConfig(): WaitingWorldConfig {
        $waitingWorldData = $this->requireArray($this->config->get("waiting-world"), "waiting-world");

        return new WaitingWorldConfig(
            $this->requireBool($waitingWorldData, "auto-join", "waiting-world.auto-join"),
            $this->requireString($waitingWorldData, "world", "waiting-world.world"),
            $this->loadSpawn($waitingWorldData, "spawn", "waiting-world.spawn")
        );
    }

    private function loadLeaveDestination(): LeaveDestination {
        $leaveData = $this->requireArray($this->config->get("leave-destination"), "leave-destination");
        $type = $this->requireString($leaveData, "type", "leave-destination.type");

        if ($type === LeaveDestination::TYPE_WORLD) {
            return LeaveDestination::world(
                $this->requireString($leaveData, "world", "leave-destination.world"),
                $this->loadSpawn($leaveData, "spawn", "leave-destination.spawn")
            );
        }

        if ($type === LeaveDestination::TYPE_TRANSFER) {
            return LeaveDestination::transfer(
                $this->requireString($leaveData, "address", "leave-destination.address"),
                $this->requireInt($leaveData, "port", "leave-destination.port")
            );
        }

        throw new InvalidArgumentException('Config key "leave-destination.type" must be either "world" or "transfer".');
    }

    /**
     * @return array<string, ArenaConfig>
     */
    private function loadArenaConfigs(): array {
        $arenasData = $this->requireArray($this->arenasConfig->getAll(), "arenas.yml");
        $arenaConfigs = [];

        foreach ($arenasData as $arenaName => $arenaData) {
            $normalizedArenaName = $this->normalizeArenaName($arenaName);
            if (isset($arenaConfigs[$normalizedArenaName])) {
                throw new InvalidArgumentException(\sprintf('Duplicate arena name "%s" found in config.', $normalizedArenaName));
            }

            $arenaPath = "arenas.yml." . $normalizedArenaName;
            $arenaConfigs[$normalizedArenaName] = $this->loadArenaConfig(
                $normalizedArenaName,
                $this->requireArray($arenaData, $arenaPath),
                $arenaPath
            );
        }

        return $arenaConfigs;
    }

    /**
     * @param array<string, mixed> $arenaData
     */
    private function loadArenaConfig(string $arenaName, array $arenaData, string $arenaPath): ArenaConfig {
        return new ArenaConfig(
            $arenaName,
            $this->requireString($arenaData, "world", $arenaPath . ".world"),
            $this->loadSpawn($arenaData, "spectator-spawn", $arenaPath . ".spectator-spawn"),
            $this->requireInt($arenaData, "elimination-y", $arenaPath . ".elimination-y"),
            $this->requireInt($arenaData, "min-players", $arenaPath . ".min-players"),
            $this->requireInt($arenaData, "max-players", $arenaPath . ".max-players"),
            $this->requireInt($arenaData, "countdown-seconds", $arenaPath . ".countdown-seconds"),
            $this->requireInt($arenaData, "block-fall-delay-ticks", $arenaPath . ".block-fall-delay-ticks")
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function loadSpawn(array $data, string $key, string $path): ArenaSpawn {
        $spawnData = $this->requireArrayKey($data, $key, $path);

        return new ArenaSpawn(
            $this->requireNumeric($spawnData, "x", $path . ".x"),
            $this->requireNumeric($spawnData, "y", $path . ".y"),
            $this->requireNumeric($spawnData, "z", $path . ".z"),
            $this->getOptionalNumeric($spawnData, "yaw", $path . ".yaw", 0.0),
            $this->getOptionalNumeric($spawnData, "pitch", $path . ".pitch", 0.0)
        );
    }

    private function normalizeArenaName(string $arenaName): string {
        $normalizedArenaName = \trim($arenaName);
        if ($normalizedArenaName === "") {
            throw new InvalidArgumentException("Arena names cannot be empty.");
        }

        return $normalizedArenaName;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function requireArrayKey(array $data, string $key, string $path): array {
        if (!\array_key_exists($key, $data)) {
            throw new InvalidArgumentException(\sprintf('Missing config key "%s".', $path));
        }

        return $this->requireArray($data[$key], $path);
    }

    /**
     * @return array<string, mixed>
     */
    private function requireArray(mixed $value, string $path): array {
        if (!\is_array($value)) {
            throw new InvalidArgumentException(\sprintf('Config key "%s" must be an array.', $path));
        }

        foreach ($value as $key => $_) {
            if (!\is_string($key)) {
                throw new InvalidArgumentException(\sprintf('Config key "%s" must use string keys only.', $path));
            }
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireString(array $data, string $key, string $path): string {
        if (!\array_key_exists($key, $data)) {
            throw new InvalidArgumentException(\sprintf('Missing config key "%s".', $path));
        }

        if (!\is_string($data[$key])) {
            throw new InvalidArgumentException(\sprintf('Config key "%s" must be a string.', $path));
        }

        return \trim($data[$key]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireInt(array $data, string $key, string $path): int {
        if (!\array_key_exists($key, $data)) {
            throw new InvalidArgumentException(\sprintf('Missing config key "%s".', $path));
        }

        $value = $data[$key];
        if (\is_int($value)) {
            return $value;
        }

        if (\is_string($value) && \filter_var($value, \FILTER_VALIDATE_INT) !== false) {
            return (int) $value;
        }

        throw new InvalidArgumentException(\sprintf('Config key "%s" must be an integer.', $path));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireBool(array $data, string $key, string $path): bool {
        if (!\array_key_exists($key, $data)) {
            throw new InvalidArgumentException(\sprintf('Missing config key "%s".', $path));
        }

        if (!\is_bool($data[$key])) {
            throw new InvalidArgumentException(\sprintf('Config key "%s" must be a boolean.', $path));
        }

        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireNumeric(array $data, string $key, string $path): float {
        if (!\array_key_exists($key, $data)) {
            throw new InvalidArgumentException(\sprintf('Missing config key "%s".', $path));
        }

        $value = $data[$key];
        if (!\is_int($value) && !\is_float($value) && !\is_string($value)) {
            throw new InvalidArgumentException(\sprintf('Config key "%s" must be numeric.', $path));
        }

        if (!\is_numeric($value)) {
            throw new InvalidArgumentException(\sprintf('Config key "%s" must be numeric.', $path));
        }

        return (float) $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getOptionalNumeric(array $data, string $key, string $path, float $default): float {
        if (!\array_key_exists($key, $data)) {
            return $default;
        }

        return $this->requireNumeric($data, $key, $path);
    }
}
