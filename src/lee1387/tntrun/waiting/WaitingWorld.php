<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting;

use InvalidArgumentException;
use lee1387\tntrun\arena\ArenaSpawn;

final class WaitingWorld {
    public function __construct(
        private bool $autoJoin,
        private string $worldName,
        private ArenaSpawn $spawn
    ) {
        if ($this->worldName === "") {
            throw new InvalidArgumentException("Waiting world name cannot be empty.");
        }
    }

    public function isAutoJoinEnabled(): bool {
        return $this->autoJoin;
    }

    public function getWorldName(): string {
        return $this->worldName;
    }

    public function getSpawn(): ArenaSpawn {
        return $this->spawn;
    }
}
