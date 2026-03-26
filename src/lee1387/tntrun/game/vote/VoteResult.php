<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\vote;

use lee1387\tntrun\arena\ArenaConfig;

final class VoteResult {
    public function __construct(
        private ArenaConfig $arenaConfig,
        private int $voteCount
    ) {}

    public function getArenaConfig(): ArenaConfig {
        return $this->arenaConfig;
    }

    public function getVoteCount(): int {
        return $this->voteCount;
    }
}
