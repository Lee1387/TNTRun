<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting\listener;

use lee1387\tntrun\game\queue\QueueManager;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use lee1387\tntrun\waiting\WaitingWorld;
use lee1387\tntrun\waiting\WaitingWorldLoadout;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;

final class WaitingWorldExitListener implements Listener {
    public function __construct(
        private WaitingWorld $waitingWorld,
        private QueueManager $queueManager,
        private PlayerSessionManager $playerSessionManager,
        private TNTRunPlayerGuard $playerGuard,
        private WaitingWorldLoadout $waitingWorldLoadout
    ) {}

    /**
     * @priority MONITOR
     */
    public function onPlayerTeleport(EntityTeleportEvent $event): void {
        if ($event->isCancelled()) {
            return;
        }

        $player = $event->getEntity();
        if (!$player instanceof Player) {
            return;
        }

        $playerSession = $this->playerSessionManager->get($player);
        if ($playerSession === null || !$playerSession->isInWaitingWorld()) {
            return;
        }

        if ($event->getFrom()->getWorld()->getFolderName() !== $this->waitingWorld->getWorldName()) {
            return;
        }

        if ($event->getTo()->getWorld()->getFolderName() === $this->waitingWorld->getWorldName()) {
            return;
        }

        if ($playerSession->consumeManagedWaitingWorldExit()) {
            $playerSession->leaveWaitingWorld();
            $this->playerGuard->cleanup($player);
            $this->waitingWorldLoadout->clear($player);
            return;
        }

        $this->queueManager->removePlayerSession($playerSession);
        $playerSession->leaveWaitingWorld();
        $this->playerGuard->cleanup($player);
        $this->waitingWorldLoadout->clear($player);
    }
}
