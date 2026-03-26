<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\vote;

use lee1387\tntrun\config\message\VoteMessages;
use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\player\OnlinePlayerRegistry;

final class VoteBroadcaster {
    public function __construct(
        private OnlinePlayerRegistry $onlinePlayerRegistry,
        private VoteMessages $messages
    ) {}

    public function broadcastSelection(GameInstance $gameInstance, VoteResult $voteResult): void {
        $message = $this->messages->selectedBroadcast(
            $voteResult->getArenaConfig()->getDisplayName(),
            $voteResult->getVoteCount()
        );

        foreach ($gameInstance->getPlayerIds() as $playerId) {
            $player = $this->onlinePlayerRegistry->getById($playerId);
            if ($player === null) {
                continue;
            }

            $player->sendMessage($message);
        }
    }
}
