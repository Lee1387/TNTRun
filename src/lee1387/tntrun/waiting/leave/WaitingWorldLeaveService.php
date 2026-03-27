<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting\leave;

use lee1387\tntrun\game\queue\QueueManager;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\waiting\WaitingWorldExitCoordinator;
use lee1387\tntrun\world\WorldLoader;
use pocketmine\player\Player;

final class WaitingWorldLeaveService {
    public function __construct(
        private PlayerSessionManager $playerSessionManager,
        private LeaveDestination $leaveDestination,
        private WorldLoader $worldLoader,
        private QueueManager $queueManager,
        private WaitingWorldExitCoordinator $waitingWorldExitCoordinator
    ) {}

    public function leave(Player $player): WaitingWorldLeaveResult {
        $playerSession = $this->playerSessionManager->get($player);
        if (
            $playerSession === null
            || (
                !$playerSession->isInWaitingWorld()
                && $this->queueManager->findGameInstanceByPlayerSession($playerSession) === null
            )
        ) {
            return WaitingWorldLeaveResult::NOT_IN_WAITING_WORLD;
        }

        $this->waitingWorldExitCoordinator->markManagedExit($playerSession);
        if (!$this->leaveDestination->send($player, $this->worldLoader)) {
            $this->waitingWorldExitCoordinator->clearManagedExit($playerSession);
            return WaitingWorldLeaveResult::DESTINATION_FAILED;
        }

        $this->waitingWorldExitCoordinator->handleLeave($player, $playerSession);

        return WaitingWorldLeaveResult::SUCCESS;
    }
}
