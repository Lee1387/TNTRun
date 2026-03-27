<?php

declare(strict_types=1);

namespace lee1387\tntrun\player;

use lee1387\tntrun\config\message\LeaveMessages;
use lee1387\tntrun\config\message\PlayMessages;
use lee1387\tntrun\config\message\VoteMessages;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Dye;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class TNTRunHotbarItems {
    public function __construct(
        private LeaveMessages $leaveMessages,
        private VoteMessages $voteMessages,
        private PlayMessages $playMessages
    ) {}

    public function clearManagedItems(Player $player): void {
        $inventory = $player->getInventory();
        for ($slot = 0; $slot < $inventory->getSize(); ++$slot) {
            if ($this->isManagedItem($inventory->getItem($slot))) {
                $inventory->clear($slot);
            }
        }

        if ($this->isManagedItem($player->getOffHandInventory()->getItem(0))) {
            $player->getOffHandInventory()->clear(0);
        }

        if ($this->isManagedItem($player->getCursorInventory()->getItem(0))) {
            $player->getCursorInventory()->clear(0);
        }
    }

    public function createVoteItem(): Item {
        return VanillaItems::PAPER()
            ->setCustomName($this->voteMessages->itemName());
    }

    public function isVoteItem(Item $item): bool {
        return $item->getTypeId() === VanillaItems::PAPER()->getTypeId()
            && $item->getCustomName() === $this->voteMessages->itemName();
    }

    public function createLeaveItem(): Item {
        return VanillaItems::DYE()
            ->setColor(DyeColor::RED)
            ->setCustomName($this->leaveMessages->itemName());
    }

    public function isLeaveItem(Item $item): bool {
        return $item instanceof Dye
            && $item->getColor() === DyeColor::RED
            && $item->getCustomName() === $this->leaveMessages->itemName();
    }

    public function createPlayAgainItem(): Item {
        return VanillaItems::SLIMEBALL()
            ->setCustomName($this->playMessages->playAgainItemName());
    }

    public function isPlayAgainItem(Item $item): bool {
        return $item->getTypeId() === VanillaItems::SLIMEBALL()->getTypeId()
            && $item->getCustomName() === $this->playMessages->playAgainItemName();
    }

    public function createSpectateItem(): Item {
        return VanillaItems::COMPASS()
            ->setCustomName($this->playMessages->spectateItemName());
    }

    public function isSpectateItem(Item $item): bool {
        return $item->getTypeId() === VanillaItems::COMPASS()->getTypeId()
            && $item->getCustomName() === $this->playMessages->spectateItemName();
    }

    private function isManagedItem(Item $item): bool {
        return $this->isVoteItem($item)
            || $this->isLeaveItem($item)
            || $this->isPlayAgainItem($item)
            || $this->isSpectateItem($item);
    }
}
