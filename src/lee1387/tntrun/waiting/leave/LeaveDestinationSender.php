<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting\leave;

use lee1387\tntrun\world\WorldLoader;
use pocketmine\player\Player;

final class LeaveDestinationSender {
    public function __construct(
        private WorldLoader $worldLoader
    ) {}

    public function send(Player $player, LeaveDestination $leaveDestination): bool {
        if ($leaveDestination->isTransfer()) {
            return $player->transfer($leaveDestination->getAddress(), $leaveDestination->getPort());
        }

        $world = $this->worldLoader->load($leaveDestination->getWorldName());
        if ($world === null) {
            return false;
        }

        return $player->teleport($leaveDestination->getSpawn()->toLocation($world));
    }
}
