<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play\spectator;

use lee1387\tntrun\player\TNTRunHotbarItems;
use pocketmine\player\Player;

final class SpectatorLoadout {
    private const PLAY_AGAIN_ITEM_SLOT = 0;
    private const SPECTATE_ITEM_SLOT = 4;
    private const LEAVE_ITEM_SLOT = 8;

    public function __construct(
        private TNTRunHotbarItems $hotbarItems
    ) {}

    public function apply(Player $player): void {
        $this->hotbarItems->clearManagedItems($player);
        $player->getInventory()->setItem(self::PLAY_AGAIN_ITEM_SLOT, $this->hotbarItems->createPlayAgainItem());
        $player->getInventory()->setItem(self::SPECTATE_ITEM_SLOT, $this->hotbarItems->createSpectateItem());
        $player->getInventory()->setItem(self::LEAVE_ITEM_SLOT, $this->hotbarItems->createLeaveItem());
    }
}
