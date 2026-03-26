<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

use InvalidArgumentException;
use lee1387\tntrun\arena\ArenaConfig;

final class QueuePool {
    /**
     * @var array<string, ArenaConfig>
     */
    private array $arenaConfigs;
    private int $minPlayers;
    private int $maxPlayers;

    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     */
    public function __construct(
        private string $id,
        array $arenaConfigs
    ) {
        $this->arenaConfigs = $arenaConfigs;

        if ($this->id === "") {
            throw new InvalidArgumentException("Queue pool ID cannot be empty.");
        }

        if ($this->arenaConfigs === []) {
            throw new InvalidArgumentException("Queue pools must contain at least one arena.");
        }

        \ksort($this->arenaConfigs);

        $firstArenaName = \array_key_first($this->arenaConfigs);
        $referenceArena = $this->arenaConfigs[$firstArenaName];
        $this->minPlayers = $referenceArena->getMinPlayers();
        $this->maxPlayers = $referenceArena->getMaxPlayers();

        foreach ($this->arenaConfigs as $arenaConfig) {
            if (
                $arenaConfig->getMinPlayers() !== $this->minPlayers
                || $arenaConfig->getMaxPlayers() !== $this->maxPlayers
            ) {
                throw new InvalidArgumentException("All queue-pool arenas must share the same min players and max players.");
            }
        }
    }

    public function getId(): string {
        return $this->id;
    }

    /**
     * @return array<string, ArenaConfig>
     */
    public function getArenaConfigs(): array {
        return $this->arenaConfigs;
    }

    public function getMinPlayers(): int {
        return $this->minPlayers;
    }

    public function getMaxPlayers(): int {
        return $this->maxPlayers;
    }
}
