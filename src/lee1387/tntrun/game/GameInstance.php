<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\player\PlayerSession;

final class GameInstance {
    /**
     * @var array<string, true>
     */
    private array $playerIds = [];

    private GameState $state = GameState::WAITING;

    public function __construct(
        private string $id,
        private ?ArenaConfig $arenaConfig = null
    ) {}

    public function getId(): string {
        return $this->id;
    }

    public function getArenaConfig(): ?ArenaConfig {
        return $this->arenaConfig;
    }

    public function assignArena(ArenaConfig $arenaConfig): void {
        $this->arenaConfig = $arenaConfig;
    }

    public function getState(): GameState {
        return $this->state;
    }

    public function setState(GameState $state): void {
        $this->state = $state;
    }

    public function hasPlayer(PlayerSession $playerSession): bool {
        return isset($this->playerIds[$playerSession->getPlayerId()]);
    }

    public function addPlayer(PlayerSession $playerSession): bool {
        $playerId = $playerSession->getPlayerId();
        if ($playerSession->getGameInstanceId() !== null && $playerSession->getGameInstanceId() !== $this->id) {
            return false;
        }

        if (isset($this->playerIds[$playerId])) {
            return false;
        }

        $this->playerIds[$playerId] = true;
        $playerSession->assignGameInstance($this->id);

        return true;
    }

    public function canAcceptPlayer(PlayerSession $playerSession): bool {
        if ($this->state !== GameState::WAITING) {
            return false;
        }

        if ($this->hasPlayer($playerSession)) {
            return true;
        }

        return !$this->isFull();
    }

    public function removePlayer(PlayerSession $playerSession): bool {
        $playerId = $playerSession->getPlayerId();
        if (!isset($this->playerIds[$playerId])) {
            return false;
        }

        unset($this->playerIds[$playerId]);
        if ($playerSession->getGameInstanceId() === $this->id) {
            $playerSession->clearGameInstance();
        }

        return true;
    }

    public function getPlayerCount(): int {
        return \count($this->playerIds);
    }

    public function isFull(): bool {
        if ($this->arenaConfig === null) {
            return false;
        }

        return $this->getPlayerCount() >= $this->arenaConfig->getMaxPlayers();
    }

    public function isEmpty(): bool {
        return $this->playerIds === [];
    }

    /**
     * @return list<string>
     */
    public function getPlayerIds(): array {
        return \array_keys($this->playerIds);
    }
}
