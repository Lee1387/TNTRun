<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

final class QueueState {
    private QueuePhase $queuePhase = QueuePhase::WAITING;
    private ?int $countdownSecondsRemaining = null;

    public function __construct(
        private QueuePool $queuePool,
        private QueueSettings $queueSettings
    ) {}

    public function getQueuePool(): QueuePool {
        return $this->queuePool;
    }

    public function isReady(): bool {
        return $this->queuePhase === QueuePhase::READY;
    }

    public function isLocked(): bool {
        return $this->queuePhase === QueuePhase::LOCKED;
    }

    public function lock(int $playerCount): bool {
        if ($this->queuePhase !== QueuePhase::READY || !$this->isStartable($playerCount)) {
            return false;
        }

        $this->queuePhase = QueuePhase::LOCKED;
        $this->countdownSecondsRemaining = $this->isFull($playerCount)
            ? $this->queueSettings->getFullCountdownSeconds()
            : $this->queueSettings->getReadyCountdownSeconds();

        return true;
    }

    public function isFull(int $playerCount): bool {
        return $playerCount >= $this->queuePool->getMaxPlayers();
    }

    public function isStartable(int $playerCount): bool {
        return $playerCount >= $this->queuePool->getMinPlayers();
    }

    public function isJoinable(int $playerCount): bool {
        return (
            $this->queuePhase === QueuePhase::WAITING
            || $this->queuePhase === QueuePhase::READY
        ) && !$this->isFull($playerCount);
    }

    public function tickCountdown(): bool {
        if (!$this->isLocked() || $this->countdownSecondsRemaining === null || $this->countdownSecondsRemaining === 0) {
            return false;
        }

        --$this->countdownSecondsRemaining;
        if ($this->countdownSecondsRemaining === 0) {
            $this->queuePhase = QueuePhase::COUNTDOWN_COMPLETE;
        }

        return true;
    }

    public function refresh(int $playerCount): void {
        if (
            $this->queuePhase !== QueuePhase::WAITING
            && $this->queuePhase !== QueuePhase::READY
        ) {
            return;
        }

        $this->queuePhase = $this->isStartable($playerCount) ? QueuePhase::READY : QueuePhase::WAITING;
    }
}
