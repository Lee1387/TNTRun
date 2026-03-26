<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting;

use lee1387\tntrun\config\message\LeaveMessages;
use lee1387\tntrun\config\message\VoteMessages;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Dye;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class WaitingWorldLoadout {
    private const VOTE_ITEM_SLOT = 4;
    private const LEAVE_ITEM_SLOT = 8;

    public function __construct(
        private LeaveMessages $leaveMessages,
        private VoteMessages $voteMessages
    ) {}

    public function apply(Player $player): void {
        $player->getInventory()->setItem(self::VOTE_ITEM_SLOT, $this->createVoteItem());
        $player->getInventory()->setItem(self::LEAVE_ITEM_SLOT, $this->createLeaveItem());
    }

    public function clear(Player $player): void {
        $inventory = $player->getInventory();
        for ($slot = 0; $slot < $inventory->getSize(); $slot++) {
            if ($this->isLoadoutItem($inventory->getItem($slot))) {
                $inventory->clear($slot);
            }
        }

        if ($this->isLoadoutItem($player->getOffHandInventory()->getItem(0))) {
            $player->getOffHandInventory()->clear(0);
        }

        if ($this->isLoadoutItem($player->getCursorInventory()->getItem(0))) {
            $player->getCursorInventory()->clear(0);
        }
    }

    public function isVoteItem(Item $item): bool {
        return $item->getTypeId() === VanillaItems::PAPER()->getTypeId()
            && $item->getCustomName() === $this->voteMessages->itemName();
    }

    public function isLeaveItem(Item $item): bool {
        return $item instanceof Dye
            && $item->getColor() === DyeColor::RED
            && $item->getCustomName() === $this->leaveMessages->itemName();
    }

    private function isLoadoutItem(Item $item): bool {
        return $this->isVoteItem($item) || $this->isLeaveItem($item);
    }

    private function createVoteItem(): Item {
        return VanillaItems::PAPER()
            ->setCustomName($this->voteMessages->itemName());
    }

    private function createLeaveItem(): Item {
        return VanillaItems::DYE()
            ->setColor(DyeColor::RED)
            ->setCustomName($this->leaveMessages->itemName());
    }
}
