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

    public function belongsToQueuePool(string $queuePoolId): bool {
        return $this->queuePool->getId() === $queuePoolId;
    }

    public function getMaxPlayers(): int {
        return $this->queuePool->getMaxPlayers();
    }

    public function hasCompletedCountdown(): bool {
        return $this->queuePhase === QueuePhase::COUNTDOWN_COMPLETE;
    }

    public function getCountdownSecondsRemaining(): ?int {
        return $this->countdownSecondsRemaining;
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

    public function lock(): void {
        if ($this->queuePhase !== QueuePhase::READY || $this->countdownSecondsRemaining === null) {
            return;
        }

        $this->queuePhase = QueuePhase::PREPARING;
    }

    public function tickCountdown(): bool {
        if (
            (
                $this->queuePhase !== QueuePhase::READY
                && $this->queuePhase !== QueuePhase::PREPARING
            )
            || $this->countdownSecondsRemaining === null
            || $this->countdownSecondsRemaining === 0
        ) {
            return false;
        }

        --$this->countdownSecondsRemaining;
        if ($this->countdownSecondsRemaining === 0) {
            $this->queuePhase = QueuePhase::COUNTDOWN_COMPLETE;
        }

        return true;
    }

    public function refresh(int $playerCount): void {
        if ($this->queuePhase === QueuePhase::COUNTDOWN_COMPLETE) {
            return;
        }

        if (!$this->isStartable($playerCount)) {
            $this->queuePhase = QueuePhase::WAITING;
            $this->countdownSecondsRemaining = null;

            return;
        }

        if ($this->queuePhase === QueuePhase::PREPARING) {
            return;
        }

        $this->queuePhase = QueuePhase::READY;
        if ($this->countdownSecondsRemaining === null) {
            $this->countdownSecondsRemaining = $this->queueSettings->getReadyCountdownSeconds();
        }

        if (
            $this->isFull($playerCount)
            && $this->countdownSecondsRemaining > $this->queueSettings->getFullCountdownSeconds()
        ) {
            $this->countdownSecondsRemaining = $this->queueSettings->getFullCountdownSeconds();
        }
    }
}
