<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\game\queue\QueuePool;
use lee1387\tntrun\game\queue\QueueSettings;
use lee1387\tntrun\game\queue\QueueState;
use lee1387\tntrun\game\vote\VoteResult;
use lee1387\tntrun\game\vote\VoteState;

final class GameInstance {
    /**
     * @var array<string, true>
     */
    private array $playerIds = [];
    private QueueState $queueState;
    private VoteState $voteState;
    private bool $selectedArenaPrepared = false;
    private bool $playersTransferredToSelectedArena = false;

    public function __construct(
        private string $id,
        QueuePool $queuePool,
        QueueSettings $queueSettings
    ) {
        $this->queueState = new QueueState($queuePool, $queueSettings);
        $this->voteState = new VoteState($queuePool);
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

    public function hasPlayerId(string $playerId): bool {
        return isset($this->playerIds[$playerId]);
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
        $this->voteState->removeVote($playerId);
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
     * @return array<string, ArenaConfig>
     */
    public function getVotableArenaConfigs(): array {
        return $this->voteState->getVotableArenaConfigs();
    }

    public function getPlayerVoteArenaName(string $playerId): ?string {
        return $this->voteState->getPlayerVoteArenaName($playerId);
    }

    public function isVotingOpen(): bool {
        return $this->voteState->isVotingOpen();
    }

    public function submitVote(string $playerId, string $arenaName): bool {
        if (!$this->hasPlayerId($playerId)) {
            return false;
        }

        return $this->voteState->submitVote($playerId, $arenaName);
    }

    public function closeVoting(): VoteResult {
        return $this->voteState->close();
    }

    public function getSelectedArenaConfig(): ?ArenaConfig {
        return $this->voteState->getSelectedArenaConfig();
    }

    public function hasPreparedSelectedArena(): bool {
        return $this->selectedArenaPrepared;
    }

    public function markSelectedArenaPrepared(): void {
        $this->selectedArenaPrepared = true;
    }

    public function hasTransferredPlayersToSelectedArena(): bool {
        return $this->playersTransferredToSelectedArena;
    }

    public function markPlayersTransferredToSelectedArena(): void {
        $this->playersTransferredToSelectedArena = true;
    }

    public function lockQueue(): void {
        $this->queueState->lock();
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
        $hadCountdown = $this->queueState->getCountdownSecondsRemaining() !== null;

        $this->queueState->refresh($this->getPlayerCount());
        if (
            $hadCountdown
            && $this->queueState->getCountdownSecondsRemaining() === null
            && !$this->queueState->hasCompletedCountdown()
        ) {
            $this->selectedArenaPrepared = false;
            $this->playersTransferredToSelectedArena = false;
            $this->voteState->reopen();
        }
    }
}
