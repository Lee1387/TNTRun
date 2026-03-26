<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena;

use InvalidArgumentException;

final class ArenaConfig {
    public function __construct(
        private string $name,
        private string $worldName,
        private ArenaSpawn $spectatorSpawn,
        private int $eliminationY,
        private int $minPlayers,
        private int $maxPlayers,
        private int $countdownSeconds,
        private int $blockFallDelayTicks
    ) {
        if ($this->name === '') {
            throw new InvalidArgumentException("Arena name cannot be empty.");
        }

        if ($this->worldName === '') {
            throw new InvalidArgumentException("Arena world name cannot be empty.");
        }

        if ($this->minPlayers < 2) {
            throw new InvalidArgumentException("Arena minimum players must be at least 2.");
        }

        if ($this->maxPlayers < $this->minPlayers) {
            throw new InvalidArgumentException("Arena maximum players must be greater than or equal to minimum players.");
        }

        if ($this->countdownSeconds < 1) {
            throw new InvalidArgumentException("Arena countdown seconds must be at least 1.");
        }

        if ($this->blockFallDelayTicks < 1) {
            throw new InvalidArgumentException("Arena block fall delay must be at least 1 tick.");
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function getWorldName(): string {
        return $this->worldName;
    }

    public function getSpectatorSpawn(): ArenaSpawn {
        return $this->spectatorSpawn;
    }

    public function getEliminationY(): int {
        return $this->eliminationY;
    }

    public function getMinPlayers(): int {
        return $this->minPlayers;
    }

    public function getMaxPlayers(): int {
        return $this->maxPlayers;
    }

    public function getCountdownSeconds(): int {
        return $this->countdownSeconds;
    }

    public function getBlockFallDelayTicks(): int {
        return $this->blockFallDelayTicks;
    }
}
