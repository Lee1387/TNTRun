<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena;

use InvalidArgumentException;
use pocketmine\utils\Config;

final class ArenaConfigLoader {
    public function __construct(
        private Config $config
    ) {}

    public function load(): ArenaConfig {
        $arenaData = $this->requireArray($this->config->get("arena"), "arena");

        return new ArenaConfig(
            $this->requireNonEmptyString($arenaData, "name", "arena.name"),
            $this->requireNonEmptyString($arenaData, "world", "arena.world"),
            $this->loadSpawn($arenaData, "waiting-spawn"),
            $this->loadSpawn($arenaData, "spectator-spawn"),
            $this->loadFloorRegion($arenaData),
            $this->requireInt($arenaData, "elimination-y", "arena.elimination-y"),
            $this->requireIntAtLeast($arenaData, "min-players", "arena.min-players", 2),
            $this->requireIntAtLeast($arenaData, "max-players", "arena.max-players", 2),
            $this->requireIntAtLeast($arenaData, "countdown-seconds", "arena.countdown-seconds", 1),
            $this->requireIntAtLeast($arenaData, "block-fall-delay-ticks", "arena.block-fall-delay-ticks", 1)
        );
    }

    /**
     * @param array<string, mixed> $arenaData
     */
    private function loadSpawn(array $arenaData, string $key): ArenaSpawn {
        $path = "arena." . $key;
        $spawnData = $this->requireArrayKey($arenaData, $key, $path);

        return new ArenaSpawn(
            $this->requireNumeric($spawnData, "x", $path . ".x"),
            $this->requireNumeric($spawnData, "y", $path . ".y"),
            $this->requireNumeric($spawnData, "z", $path . ".z"),
            $this->getOptionalNumeric($spawnData, "yaw", $path . ".yaw", 0.0),
            $this->getOptionalNumeric($spawnData, "pitch", $path . ".pitch", 0.0)
        );
    }

    /**
     * @param array<string, mixed> $arenaData
     */
    private function loadFloorRegion(array $arenaData): Cuboid {
        $regionData = $this->requireArrayKey($arenaData, "floor-region", "arena.floor-region");
        $firstCorner = $this->requireArrayKey($regionData, "first", "arena.floor-region.first");
        $secondCorner = $this->requireArrayKey($regionData, "second", "arena.floor-region.second");

        return Cuboid::fromCorners(
            $this->requireInt($firstCorner, "x", "arena.floor-region.first.x"),
            $this->requireInt($firstCorner, "y", "arena.floor-region.first.y"),
            $this->requireInt($firstCorner, "z", "arena.floor-region.first.z"),
            $this->requireInt($secondCorner, "x", "arena.floor-region.second.x"),
            $this->requireInt($secondCorner, "y", "arena.floor-region.second.y"),
            $this->requireInt($secondCorner, "z", "arena.floor-region.second.z")
        );
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
    private function requireNonEmptyString(array $data, string $key, string $path): string {
        if (!\array_key_exists($key, $data)) {
            throw new InvalidArgumentException(\sprintf('Missing config key "%s".', $path));
        }

        if (!\is_string($data[$key])) {
            throw new InvalidArgumentException(\sprintf('Config key "%s" must be a string.', $path));
        }

        $value = trim($data[$key]);
        if ($value === "") {
            throw new InvalidArgumentException(\sprintf('Config key "%s" cannot be empty.', $path));
        }

        return $value;
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

        if (\is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false) {
            return (int) $value;
        }

        throw new InvalidArgumentException(\sprintf('Config key "%s" must be an integer.', $path));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireIntAtLeast(array $data, string $key, string $path, int $minimum): int {
        $value = $this->requireInt($data, $key, $path);

        if ($value < $minimum) {
            throw new InvalidArgumentException(\sprintf('Config key "%s" must be at least %d.', $path, $minimum));
        }

        return $value;
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

        if (!is_numeric($value)) {
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
