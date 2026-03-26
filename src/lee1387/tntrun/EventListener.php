<?php

declare(strict_types=1);

namespace lee1387\tntrun;

use lee1387\tntrun\waiting\WaitingWorldEntryResult;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class EventListener implements Listener {
    public function __construct(
        private TNTRun $plugin
    ) {}

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $waitingWorld = $this->plugin->getWaitingWorld();
        if (!$waitingWorld->isAutoJoinEnabled()) {
            return;
        }

        $player = $event->getPlayer();
        $result = $this->plugin->getWaitingWorldEntryService()->enter($player);
        if ($result === WaitingWorldEntryResult::SUCCESS || $result === WaitingWorldEntryResult::ALREADY_JOINED) {
            return;
        }

        $player->kick(TextFormat::RED . match ($result) {
            WaitingWorldEntryResult::WORLD_NOT_AVAILABLE => 'The TNTRun waiting world "' . $waitingWorld->getWorldName() . '" could not be loaded. Please try again later.',
            WaitingWorldEntryResult::TELEPORT_FAILED => "Failed to send you to the TNTRun waiting world. Please try again later.",
        });
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $this->plugin->getWaitingWorld()->leavePlayer($event->getPlayer());
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerTeleport(EntityTeleportEvent $event): void {
        if ($event->isCancelled()) {
            return;
        }

        $waitingWorld = $this->plugin->getWaitingWorld();
        $player = $event->getEntity();
        if (!$player instanceof Player) {
            return;
        }

        if (!$waitingWorld->isPlayerJoined($player)) {
            return;
        }

        if ($event->getFrom()->getWorld()->getFolderName() !== $waitingWorld->getWorldName()) {
            return;
        }

        if ($event->getTo()->getWorld()->getFolderName() === $waitingWorld->getWorldName()) {
            return;
        }

        $waitingWorld->leavePlayer($player);
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerKick(PlayerKickEvent $event): void {
        if ($event->isCancelled()) {
            return;
        }

        $this->plugin->getWaitingWorld()->leavePlayer($event->getPlayer());
    }
}
