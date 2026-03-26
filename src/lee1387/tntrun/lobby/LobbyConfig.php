<?php

declare(strict_types=1);

namespace lee1387\tntrun\lobby;

use InvalidArgumentException;
use lee1387\tntrun\arena\ArenaSpawn;

final class LobbyConfig {
    public function __construct(
        private string $worldName,
        private ArenaSpawn $spawn
    ) {
        if ($this->worldName === "") {
            throw new InvalidArgumentException("Lobby world name cannot be empty.");
        }
    }

    public function getWorldName(): string {
        return $this->worldName;
    }

    public function getSpawn(): ArenaSpawn {
        return $this->spawn;
    }
}
