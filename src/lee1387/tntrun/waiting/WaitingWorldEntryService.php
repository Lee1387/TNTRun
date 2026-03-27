<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting;

use lee1387\tntrun\game\queue\QueueManager;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use lee1387\tntrun\world\WorldLoader;
use pocketmine\player\Player;

final class WaitingWorldEntryService {
    public function __construct(
        private WaitingWorld $waitingWorld,
        private QueueManager $queueManager,
        private PlayerSessionManager $playerSessionManager,
        private WorldLoader $worldLoader,
        private TNTRunPlayerGuard $playerGuard,
        private WaitingWorldLoadout $waitingWorldLoadout
    ) {}

    public function enter(Player $player): WaitingWorldEntryResult {
        $playerSession = $this->playerSessionManager->getOrCreate($player);

        if ($playerSession->isInWaitingWorld()) {
            return WaitingWorldEntryResult::ALREADY_JOINED;
        }

        $currentGameInstance = $this->queueManager->findGameInstanceByPlayerSession($playerSession);

        $world = $this->worldLoader->load($this->waitingWorld->getWorldName());
        if ($world === null) {
            return WaitingWorldEntryResult::WORLD_NOT_AVAILABLE;
        }

        if (!$player->teleport($this->waitingWorld->getSpawn()->toLocation($world))) {
            return WaitingWorldEntryResult::TELEPORT_FAILED;
        }

        if ($currentGameInstance !== null) {
            $this->queueManager->removePlayerSession($playerSession);
        }

        if (!$playerSession->joinWaitingWorld()) {
            return WaitingWorldEntryResult::ALREADY_JOINED;
        }

        $this->playerGuard->prepare($player);
        $this->waitingWorldLoadout->apply($player);
        $this->queueManager->assignPlayerSession($playerSession);

        return WaitingWorldEntryResult::SUCCESS;
    }
}
