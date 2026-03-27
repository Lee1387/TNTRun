<?php

declare(strict_types=1);

namespace lee1387\tntrun\player\listener;

use lee1387\tntrun\player\TNTRunPlayerGuard;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

final class TNTRunProtectionListener implements Listener {
    public function __construct(
        private TNTRunPlayerGuard $playerGuard
    ) {}

    public function onBlockBreak(BlockBreakEvent $event): void {
        if ($this->playerGuard->isProtected($event->getPlayer())) {
            $event->cancel();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void {
        if ($this->playerGuard->isProtected($event->getPlayer())) {
            $event->cancel();
        }
    }

    public function onPlayerDropItem(PlayerDropItemEvent $event): void {
        if ($this->playerGuard->isProtected($event->getPlayer())) {
            $event->cancel();
        }
    }

    public function onEntityItemPickup(EntityItemPickupEvent $event): void {
        $collector = $event->getEntity();
        if ($collector instanceof Player && $this->playerGuard->isProtected($collector)) {
            $event->cancel();
        }
    }

    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof Player && $this->playerGuard->isProtected($entity)) {
            $event->cancel();
            return;
        }

        if (!$event instanceof EntityDamageByEntityEvent) {
            return;
        }

        $damager = $event->getDamager();
        if ($damager instanceof Player && $this->playerGuard->isProtected($damager)) {
            $event->cancel();
            return;
        }

        if ($damager instanceof Projectile) {
            $owner = $damager->getOwningEntity();
            if ($owner instanceof Player && $this->playerGuard->isProtected($owner)) {
                $event->cancel();
            }
        }
    }

    public function onEntityTrampleFarmland(EntityTrampleFarmlandEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof Player && $this->playerGuard->isProtected($entity)) {
            $event->cancel();
        }
    }

    public function onPlayerExhaust(PlayerExhaustEvent $event): void {
        if ($this->playerGuard->isProtected($event->getPlayer())) {
            $event->cancel();
        }
    }

    public function onPlayerGameModeChange(PlayerGameModeChangeEvent $event): void {
        if (!$this->playerGuard->isProtected($event->getPlayer())) {
            return;
        }

        $expectedGamemode = $this->playerGuard->isSpectator($event->getPlayer())
            ? GameMode::SPECTATOR()
            : GameMode::ADVENTURE();

        if ($event->getNewGamemode() !== $expectedGamemode) {
            $event->cancel();
        }
    }

    public function onInventoryTransaction(InventoryTransactionEvent $event): void {
        if ($this->playerGuard->isProtected($event->getTransaction()->getSource())) {
            $event->cancel();
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void {
        if (!$this->playerGuard->isProtected($event->getPlayer())) {
            return;
        }

        $event->setDrops([]);
        $event->setXpDropAmount(0);
    }
}
