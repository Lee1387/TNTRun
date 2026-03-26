<?php

declare(strict_types=1);

namespace lee1387\tntrun\world\listener;

use lee1387\tntrun\world\TNTRunWorldGuard;
use pocketmine\block\BaseFire;
use pocketmine\block\Liquid;
use pocketmine\block\utils\Fallable;
use pocketmine\entity\object\FallingBlock;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\block\BlockPreExplodeEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\block\FarmlandHydrationChangeEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityPreExplodeEvent;
use pocketmine\event\Listener;

final class TNTRunWorldProtectionListener implements Listener {
    public function __construct(
        private TNTRunWorldGuard $worldGuard
    ) {}

    public function onLeavesDecay(LeavesDecayEvent $event): void {
        if ($this->worldGuard->isProtectedBlock($event->getBlock())) {
            $event->cancel();
        }
    }

    public function onBlockSpread(BlockSpreadEvent $event): void {
        if (!$this->worldGuard->isProtectedBlock($event->getBlock())) {
            return;
        }

        $event->cancel();
    }

    public function onBlockGrow(BlockGrowEvent $event): void {
        if ($this->worldGuard->isProtectedBlock($event->getBlock())) {
            $event->cancel();
        }
    }

    public function onBlockForm(BlockFormEvent $event): void {
        if ($this->worldGuard->isProtectedBlock($event->getBlock())) {
            $event->cancel();
        }
    }

    public function onFarmlandHydrationChange(FarmlandHydrationChangeEvent $event): void {
        if ($this->worldGuard->isProtectedBlock($event->getBlock())) {
            $event->cancel();
        }
    }

    public function onBlockBurn(BlockBurnEvent $event): void {
        if ($this->worldGuard->isProtectedBlock($event->getBlock())) {
            $event->cancel();
        }
    }

    public function onBlockPreExplode(BlockPreExplodeEvent $event): void {
        if ($this->worldGuard->isProtectedBlock($event->getBlock())) {
            $event->cancel();
        }
    }

    public function onEntityPreExplode(EntityPreExplodeEvent $event): void {
        if ($this->worldGuard->isProtectedEntity($event->getEntity())) {
            $event->cancel();
        }
    }

    public function onBlockUpdate(BlockUpdateEvent $event): void {
        if (!$this->worldGuard->isProtectedBlock($event->getBlock())) {
            return;
        }

        $block = $event->getBlock();
        if ($block instanceof Liquid || $block instanceof BaseFire || $block instanceof Fallable) {
            $event->cancel();
        }
    }

    public function onEntityBlockChange(EntityBlockChangeEvent $event): void {
        if (
            $event->getEntity() instanceof FallingBlock
            && $this->worldGuard->isProtectedEntity($event->getEntity())
        ) {
            $event->cancel();
        }
    }
}
