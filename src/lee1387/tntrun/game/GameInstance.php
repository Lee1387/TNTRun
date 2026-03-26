<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\game\queue\QueuePool;
use lee1387\tntrun\game\queue\QueueSettings;
use lee1387\tntrun\game\queue\QueueState;
use lee1387\tntrun\player\PlayerSession;

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

    public function getQueuePool(): QueuePool {
        return $this->queueState->getQueuePool();
    }

    public function hasCompletedQueueCountdown(): bool {
        return $this->queueState->hasCompletedCountdown();
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

        if (!$this->canAcceptNewPlayers()) {
            return false;
        }

        $this->playerIds[$playerId] = true;
        $playerSession->assignGameInstance($this->id);
        $this->refreshQueueState();

        return true;
    }

    public function canAcceptPlayer(PlayerSession $playerSession): bool {
        if ($this->hasPlayer($playerSession)) {
            return true;
        }

        return $this->canAcceptNewPlayers();
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

    private function refreshQueueState(): void {
        $this->queueState->refresh($this->getPlayerCount());
    }

    private function canAcceptNewPlayers(): bool {
        return $this->queueState->isJoinable($this->getPlayerCount());
    }
}
