<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

use InvalidArgumentException;
use lee1387\tntrun\arena\ArenaConfig;

final class QueuePool {
    private int $minPlayers;
    private int $maxPlayers;
    private int $countdownSeconds;

    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     */
    public function __construct(
        private string $id,
        array $arenaConfigs
    ) {
        if ($this->id === "") {
            throw new InvalidArgumentException("Queue pool ID cannot be empty.");
        }

        if ($arenaConfigs === []) {
            throw new InvalidArgumentException("Queue pools must contain at least one arena.");
        }

        \ksort($arenaConfigs);

        $firstArenaName = \array_key_first($arenaConfigs);
        $referenceArena = $arenaConfigs[$firstArenaName];
        $this->minPlayers = $referenceArena->getMinPlayers();
        $this->maxPlayers = $referenceArena->getMaxPlayers();
        $this->countdownSeconds = $referenceArena->getCountdownSeconds();

        foreach ($arenaConfigs as $arenaConfig) {
            if (
                $arenaConfig->getMinPlayers() !== $this->minPlayers
                || $arenaConfig->getMaxPlayers() !== $this->maxPlayers
                || $arenaConfig->getCountdownSeconds() !== $this->countdownSeconds
            ) {
                throw new InvalidArgumentException("All queue-pool arenas must share the same min players, max players, and countdown seconds.");
            }
        }
    }

    public function getId(): string {
        return $this->id;
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
}
