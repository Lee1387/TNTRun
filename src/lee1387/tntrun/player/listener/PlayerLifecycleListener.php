<?php

declare(strict_types=1);

namespace lee1387\tntrun\player\listener;

use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\waiting\WaitingWorldExitCoordinator;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;

final class PlayerLifecycleListener implements Listener {
    public function __construct(
        private PlayerSessionManager $playerSessionManager,
        private OnlinePlayerRegistry $onlinePlayerRegistry,
        private WaitingWorldExitCoordinator $waitingWorldExitCoordinator
    ) {}

    /**
     * @priority LOWEST
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $this->onlinePlayerRegistry->track($event->getPlayer());
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $this->cleanup($event->getPlayer());
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerKick(PlayerKickEvent $event): void {
        if ($event->isCancelled()) {
            return;
        }

        $this->cleanup($event->getPlayer());
    }

    private function cleanup(Player $player): void {
        $playerSession = $this->playerSessionManager->get($player);
        if ($playerSession !== null) {
            $this->waitingWorldExitCoordinator->handleLeave($player, $playerSession);
            $this->playerSessionManager->remove($player);
        }

        $this->onlinePlayerRegistry->untrack($player);
    }
}
