<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena;

use InvalidArgumentException;
use lee1387\tntrun\arena\io\ArenaWorldSource;

final class ArenaConfig {
    /**
     * @var list<ArenaSpawn>
     */
    private array $playerSpawns;

    /**
     * @param list<ArenaSpawn> $playerSpawns
     */
    public function __construct(
        private string $name,
        private ArenaWorldSource $worldSource,
        private ArenaSpawn $spectatorSpawn,
        array $playerSpawns,
        private int $eliminationY,
        private int $minPlayers,
        private int $maxPlayers,
        private int $countdownSeconds,
        private int $blockFallDelayTicks
    ) {
        $this->playerSpawns = $playerSpawns;

        if ($this->name === '') {
            throw new InvalidArgumentException("Arena name cannot be empty.");
        }

        if ($this->minPlayers < 2) {
            throw new InvalidArgumentException("Arena minimum players must be at least 2.");
        }

        if ($this->maxPlayers < $this->minPlayers) {
            throw new InvalidArgumentException("Arena maximum players must be greater than or equal to minimum players.");
        }

        if (\count($this->playerSpawns) < $this->maxPlayers) {
            throw new InvalidArgumentException("Arena player spawns must be greater than or equal to maximum players.");
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

    public function getWorldSource(): ArenaWorldSource {
        return $this->worldSource;
    }

    public function getWorldName(): string {
        return $this->worldSource->getWorldName();
    }

    public function getSpectatorSpawn(): ArenaSpawn {
        return $this->spectatorSpawn;
    }

    /**
     * @return list<ArenaSpawn>
     */
    public function getPlayerSpawns(): array {
        return $this->playerSpawns;
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
