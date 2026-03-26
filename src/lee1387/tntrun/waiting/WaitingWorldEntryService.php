<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting;

use lee1387\tntrun\support\WorldLoader;
use pocketmine\player\Player;

final class WaitingWorldEntryService {
    public function __construct(
        private WaitingWorld $waitingWorld,
        private WorldLoader $worldLoader
    ) {}

    public function enter(Player $player): WaitingWorldEntryResult {
        if ($this->waitingWorld->isPlayerJoined($player)) {
            return WaitingWorldEntryResult::ALREADY_JOINED;
        }

        $world = $this->worldLoader->load($this->waitingWorld->getWorldName());
        if ($world === null) {
            return WaitingWorldEntryResult::WORLD_NOT_AVAILABLE;
        }

        if (!$player->teleport($this->waitingWorld->getSpawn()->toLocation($world))) {
            return WaitingWorldEntryResult::TELEPORT_FAILED;
        }

        if (!$this->waitingWorld->joinPlayer($player)) {
            return WaitingWorldEntryResult::ALREADY_JOINED;
        }

        return WaitingWorldEntryResult::SUCCESS;
    }
}
