<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting;

use lee1387\tntrun\player\TNTRunHotbarItems;
use pocketmine\player\Player;

final class WaitingWorldLoadout {
    private const VOTE_ITEM_SLOT = 4;
    private const LEAVE_ITEM_SLOT = 8;

    public function __construct(
        private TNTRunHotbarItems $hotbarItems
    ) {}

    public function apply(Player $player): void {
        $player->getInventory()->setItem(self::VOTE_ITEM_SLOT, $this->hotbarItems->createVoteItem());
        $player->getInventory()->setItem(self::LEAVE_ITEM_SLOT, $this->hotbarItems->createLeaveItem());
    }

    public function clear(Player $player): void {
        $this->hotbarItems->clearManagedItems($player);
    }
}
