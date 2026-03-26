<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting\leave;

use lee1387\tntrun\game\queue\QueueManager;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use lee1387\tntrun\waiting\WaitingWorldLoadout;
use lee1387\tntrun\world\WorldLoader;
use pocketmine\player\Player;

final class WaitingWorldLeaveService {
    public function __construct(
        private PlayerSessionManager $playerSessionManager,
        private LeaveDestination $leaveDestination,
        private WorldLoader $worldLoader,
        private QueueManager $queueManager,
        private TNTRunPlayerGuard $playerGuard,
        private WaitingWorldLoadout $waitingWorldLoadout
    ) {}

    public function leave(Player $player): WaitingWorldLeaveResult {
        $playerSession = $this->playerSessionManager->get($player);
        if ($playerSession === null || !$playerSession->isInWaitingWorld()) {
            return WaitingWorldLeaveResult::NOT_IN_WAITING_WORLD;
        }

        $playerSession->markManagedWaitingWorldExit();
        if (!$this->leaveDestination->send($player, $this->worldLoader)) {
            $playerSession->clearManagedWaitingWorldExit();
            return WaitingWorldLeaveResult::DESTINATION_FAILED;
        }

        $this->queueManager->removePlayerSession($playerSession);
        $playerSession->leaveWaitingWorld();
        $this->playerGuard->cleanup($player);
        $this->waitingWorldLoadout->clear($player);

        return WaitingWorldLeaveResult::SUCCESS;
    }
}
