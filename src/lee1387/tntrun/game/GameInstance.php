<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\game\queue\QueuePhase;
use lee1387\tntrun\game\queue\QueuePool;
use lee1387\tntrun\player\PlayerSession;

final class GameInstance {
    /**
     * @var array<string, true>
     */
    private array $playerIds = [];
    private QueuePhase $queuePhase = QueuePhase::WAITING;

    public function __construct(
        private string $id,
        private QueuePool $queuePool
    ) {}

    public function getId(): string {
        return $this->id;
    }

    public function getQueuePool(): QueuePool {
        return $this->queuePool;
    }

    public function isWaiting(): bool {
        return $this->queuePhase === QueuePhase::WAITING;
    }

    public function isReady(): bool {
        return $this->queuePhase === QueuePhase::READY;
    }

    public function isLocked(): bool {
        return $this->queuePhase === QueuePhase::LOCKED;
    }

    public function lockQueue(): bool {
        if ($this->isLocked() || !$this->isStartable()) {
            return false;
        }

        $this->queuePhase = QueuePhase::LOCKED;

        return true;
    }

    public function unlockQueue(): bool {
        if (!$this->isLocked()) {
            return false;
        }

        $this->queuePhase = QueuePhase::WAITING;
        $this->refreshQueuePhase();

        return true;
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

        if (!$this->isJoinable()) {
            return false;
        }

        $this->playerIds[$playerId] = true;
        $playerSession->assignGameInstance($this->id);
        $this->refreshQueuePhase();

        return true;
    }

    public function canAcceptPlayer(PlayerSession $playerSession): bool {
        if ($this->hasPlayer($playerSession)) {
            return true;
        }

        return $this->isJoinable();
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
        $this->refreshQueuePhase();

        return true;
    }

    public function getPlayerCount(): int {
        return \count($this->playerIds);
    }

    public function isFull(): bool {
        return $this->getPlayerCount() >= $this->queuePool->getMaxPlayers();
    }

    public function isStartable(): bool {
        return $this->getPlayerCount() >= $this->queuePool->getMinPlayers();
    }

    public function isJoinable(): bool {
        return !$this->isLocked() && !$this->isFull();
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

    private function refreshQueuePhase(): void {
        if ($this->isLocked()) {
            return;
        }

        $this->queuePhase = $this->isStartable() ? QueuePhase::READY : QueuePhase::WAITING;
    }
}
