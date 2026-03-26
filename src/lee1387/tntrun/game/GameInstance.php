<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\game\queue\QueuePool;
use lee1387\tntrun\game\queue\QueueSettings;
use lee1387\tntrun\game\queue\QueueState;

final class GameInstance {
    /**
     * @var array<string, true>
     */
    private array $playerIds = [];
    private QueueState $queueState;

    public function __construct(
        private string $id,
        QueuePool $queuePool,
        QueueSettings $queueSettings
    ) {
        $this->queueState = new QueueState($queuePool, $queueSettings);
    }

    public function getId(): string {
        return $this->id;
    }

    public function belongsToQueuePool(string $queuePoolId): bool {
        return $this->queueState->belongsToQueuePool($queuePoolId);
    }

    public function getMaxPlayers(): int {
        return $this->queueState->getMaxPlayers();
    }

    public function hasCompletedQueueCountdown(): bool {
        return $this->queueState->hasCompletedCountdown();
    }

    public function addPlayerId(string $playerId): bool {
        if (isset($this->playerIds[$playerId])) {
            return false;
        }

        if (!$this->canAcceptNewPlayers()) {
            return false;
        }

        $this->playerIds[$playerId] = true;
        $this->refreshQueueState();

        return true;
    }

    public function removePlayerId(string $playerId): bool {
        if (!isset($this->playerIds[$playerId])) {
            return false;
        }

        unset($this->playerIds[$playerId]);
        $this->refreshQueueState();

        return true;
    }

    public function getPlayerCount(): int {
        return \count($this->playerIds);
    }

    public function isEmpty(): bool {
        return $this->playerIds === [];
    }

    public function getQueueCountdownSecondsRemaining(): ?int {
        return $this->queueState->getCountdownSecondsRemaining();
    }

    public function tickQueueCountdown(): bool {
        return $this->queueState->tickCountdown();
    }

    /**
     * @return list<string>
     */
    public function getPlayerIds(): array {
        return \array_keys($this->playerIds);
    }

    public function canAcceptNewPlayers(): bool {
        return $this->queueState->isJoinable($this->getPlayerCount());
    }

    private function refreshQueueState(): void {
        $this->queueState->refresh($this->getPlayerCount());
    }
}
