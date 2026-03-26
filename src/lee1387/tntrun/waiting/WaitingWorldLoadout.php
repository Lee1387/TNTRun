<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting;

use pocketmine\block\utils\DyeColor;
use pocketmine\item\Dye;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class WaitingWorldLoadout {
    private const LEAVE_ITEM_NAME = TextFormat::RED . "Leave Game";
    private const LEAVE_ITEM_SLOT = 8;

    public function apply(Player $player): void {
        $player->getInventory()->setItem(self::LEAVE_ITEM_SLOT, $this->createLeaveItem());
    }

    public function clear(Player $player): void {
        $inventory = $player->getInventory();
        for ($slot = 0; $slot < $inventory->getSize(); $slot++) {
            if ($this->isLeaveItem($inventory->getItem($slot))) {
                $inventory->clear($slot);
            }
        }

        if ($this->isLeaveItem($player->getOffHandInventory()->getItem(0))) {
            $player->getOffHandInventory()->clear(0);
        }

        if ($this->isLeaveItem($player->getCursorInventory()->getItem(0))) {
            $player->getCursorInventory()->clear(0);
        }
    }

    public function isLeaveItem(Item $item): bool {
        return $item instanceof Dye
            && $item->getColor() === DyeColor::RED
            && $item->getCustomName() === self::LEAVE_ITEM_NAME;
    }

    private function createLeaveItem(): Item {
        return VanillaItems::DYE()
            ->setColor(DyeColor::RED)
            ->setCustomName(self::LEAVE_ITEM_NAME);
    }
}
