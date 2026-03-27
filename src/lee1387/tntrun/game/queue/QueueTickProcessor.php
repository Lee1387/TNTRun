<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\game\start\GameStartManager;
use lee1387\tntrun\game\vote\VoteBroadcaster;

final class QueueTickProcessor {
    public function __construct(
        private GameManager $gameManager,
        private QueueBroadcaster $queueBroadcaster,
        private VoteBroadcaster $voteBroadcaster,
        private GameStartManager $gameStartManager
    ) {}

    public function tick(): void {
        foreach ($this->gameManager->getGameInstances() as $gameInstance) {
            if ($gameInstance->hasCompletedQueueCountdown()) {
                if (!$gameInstance->hasTransferredPlayersToSelectedArena()) {
                    $this->gameStartManager->transferPlayersToSelectedArena($gameInstance);
                    continue;
                }

                $this->gameStartManager->tickArenaCountdown($gameInstance);
                continue;
            }

            $countdownSecondsRemaining = $gameInstance->getQueueCountdownSecondsRemaining();
            if (
                $countdownSecondsRemaining !== null
                && $countdownSecondsRemaining <= 5
                && $gameInstance->isVotingOpen()
            ) {
                $voteResult = $gameInstance->closeVoting();
                $gameInstance->lockQueue();
                $this->voteBroadcaster->broadcastSelection($gameInstance, $voteResult);
            }

            if (
                $countdownSecondsRemaining !== null
                && $countdownSecondsRemaining <= 5
                && !$gameInstance->hasPreparedSelectedArena()
            ) {
                $this->gameStartManager->prepareSelectedArena($gameInstance);
            }

            if ($countdownSecondsRemaining !== null) {
                $this->queueBroadcaster->sendCountdown($gameInstance, $countdownSecondsRemaining);
            }

            $gameInstance->tickQueueCountdown();
        }
    }
}
