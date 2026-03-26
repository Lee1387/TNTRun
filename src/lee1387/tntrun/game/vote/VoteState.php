<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\vote;

use InvalidArgumentException;
use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\game\queue\QueuePool;

final class VoteState {
    /**
     * @var array<string, ArenaConfig>
     */
    private array $votableArenaConfigs;
    /**
     * @var array<string, string>
     */
    private array $playerVotes = [];
    private bool $votingOpen = true;
    private ?VoteResult $selectedVoteResult = null;

    public function __construct(QueuePool $queuePool) {
        $this->votableArenaConfigs = $this->determineVotableArenaConfigs($queuePool->getArenaConfigs());

        if ($this->votableArenaConfigs === []) {
            throw new InvalidArgumentException("Vote state must have at least one votable arena.");
        }
    }

    public function isVotingOpen(): bool {
        return $this->votingOpen;
    }

    /**
     * @return array<string, ArenaConfig>
     */
    public function getVotableArenaConfigs(): array {
        return $this->votableArenaConfigs;
    }

    public function getPlayerVoteArenaName(string $playerId): ?string {
        return $this->playerVotes[$playerId] ?? null;
    }

    public function submitVote(string $playerId, string $arenaName): bool {
        if (!$this->votingOpen || !isset($this->votableArenaConfigs[$arenaName])) {
            return false;
        }

        $this->playerVotes[$playerId] = $arenaName;

        return true;
    }

    public function removeVote(string $playerId): void {
        unset($this->playerVotes[$playerId]);
    }

    public function close(): VoteResult {
        if (!$this->votingOpen && $this->selectedVoteResult !== null) {
            return $this->selectedVoteResult;
        }

        $this->votingOpen = false;
        $this->selectedVoteResult = $this->resolveSelectedVoteResult();

        return $this->selectedVoteResult;
    }

    public function reopen(): void {
        $this->votingOpen = true;
        $this->selectedVoteResult = null;
    }

    public function getSelectedArenaConfig(): ?ArenaConfig {
        return $this->selectedVoteResult?->getArenaConfig();
    }

    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     * @return array<string, ArenaConfig>
     */
    private function determineVotableArenaConfigs(array $arenaConfigs): array {
        if (\count($arenaConfigs) <= 5) {
            return $arenaConfigs;
        }

        $arenaNames = \array_keys($arenaConfigs);
        \shuffle($arenaNames);

        $votableArenaConfigs = [];
        foreach (\array_slice($arenaNames, 0, 5) as $arenaName) {
            $votableArenaConfigs[$arenaName] = $arenaConfigs[$arenaName];
        }

        return $votableArenaConfigs;
    }

    private function resolveSelectedVoteResult(): VoteResult {
        if ($this->playerVotes === []) {
            /** @var non-empty-list<string> $arenaNames */
            $arenaNames = \array_keys($this->votableArenaConfigs);

            return new VoteResult($this->pickRandomArenaConfig($arenaNames), 0);
        }

        $voteCounts = [];
        $winningArenaNames = [];

        foreach ($this->playerVotes as $arenaName) {
            $voteCounts[$arenaName] = ($voteCounts[$arenaName] ?? 0) + 1;
        }

        $highestVoteCount = 0;

        foreach ($voteCounts as $arenaName => $voteCount) {
            if ($voteCount > $highestVoteCount) {
                $highestVoteCount = $voteCount;
                $winningArenaNames = [$arenaName];
                continue;
            }

            if ($voteCount === $highestVoteCount) {
                $winningArenaNames[] = $arenaName;
            }
        }

        if ($winningArenaNames === []) {
            /** @var non-empty-list<string> $arenaNames */
            $arenaNames = \array_keys($this->votableArenaConfigs);

            return new VoteResult($this->pickRandomArenaConfig($arenaNames), 0);
        }

        return new VoteResult($this->pickRandomArenaConfig($winningArenaNames), $highestVoteCount);
    }

    /**
     * @param list<string> $arenaNames
     */
    private function pickRandomArenaConfig(array $arenaNames): ArenaConfig {
        if ($arenaNames === []) {
            throw new InvalidArgumentException("Cannot pick a random arena from an empty vote list.");
        }

        return $this->votableArenaConfigs[$arenaNames[\random_int(0, \count($arenaNames) - 1)]];
    }
}
