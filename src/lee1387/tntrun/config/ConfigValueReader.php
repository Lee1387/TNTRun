<?php

declare(strict_types=1);

namespace lee1387\tntrun\config;

use InvalidArgumentException;
use lee1387\tntrun\arena\ArenaSpawn;

final class ConfigValueReader {
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function requireArrayKey(array $data, string $key, string $path): array {
        if (!\array_key_exists($key, $data)) {
            throw new InvalidArgumentException(\sprintf('Missing config key "%s".', $path));
        }

        return $this->requireArray($data[$key], $path);
    }

    /**
     * @return array<string, mixed>
     */
    public function requireArray(mixed $value, string $path): array {
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
    public function requireString(array $data, string $key, string $path): string {
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
    public function requireInt(array $data, string $key, string $path): int {
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
    public function requireBool(array $data, string $key, string $path): bool {
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
    public function requireNumeric(array $data, string $key, string $path): float {
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
    public function getOptionalNumeric(array $data, string $key, string $path, float $default): float {
        if (!\array_key_exists($key, $data)) {
            return $default;
        }

        return $this->requireNumeric($data, $key, $path);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function loadSpawn(array $data, string $key, string $path): ArenaSpawn {
        $spawnData = $this->requireArrayKey($data, $key, $path);

        return new ArenaSpawn(
            $this->requireNumeric($spawnData, "x", $path . ".x"),
            $this->requireNumeric($spawnData, "y", $path . ".y"),
            $this->requireNumeric($spawnData, "z", $path . ".z"),
            $this->getOptionalNumeric($spawnData, "yaw", $path . ".yaw", 0.0),
            $this->getOptionalNumeric($spawnData, "pitch", $path . ".pitch", 0.0)
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return list<ArenaSpawn>
     */
    public function loadSpawnList(array $data, string $key, string $path): array {
        if (!\array_key_exists($key, $data)) {
            throw new InvalidArgumentException(\sprintf('Missing config key "%s".', $path));
        }

        if (!\is_array($data[$key]) || !\array_is_list($data[$key])) {
            throw new InvalidArgumentException(\sprintf('Config key "%s" must be a list of spawns.', $path));
        }

        $spawns = [];

        foreach ($data[$key] as $index => $spawnData) {
            $spawnPath = "$path.$index";
            $spawnMap = $this->requireArray($spawnData, $spawnPath);

            $spawns[] = new ArenaSpawn(
                $this->requireNumeric($spawnMap, "x", $spawnPath . ".x"),
                $this->requireNumeric($spawnMap, "y", $spawnPath . ".y"),
                $this->requireNumeric($spawnMap, "z", $spawnPath . ".z"),
                $this->getOptionalNumeric($spawnMap, "yaw", $spawnPath . ".yaw", 0.0),
                $this->getOptionalNumeric($spawnMap, "pitch", $spawnPath . ".pitch", 0.0)
            );
        }

        return $spawns;
    }
}
