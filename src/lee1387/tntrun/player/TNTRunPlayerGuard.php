<?php

declare(strict_types=1);

namespace lee1387\tntrun\player;

use lee1387\tntrun\world\TNTRunWorldGuard;
use pocketmine\entity\Human;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

final class TNTRunPlayerGuard {
    public function __construct(
        private PlayerSessionManager $playerSessionManager,
        private TNTRunWorldGuard $worldGuard
    ) {}

    public function isProtected(Human $player): bool {
        return $player instanceof Player && (
            ($this->playerSessionManager->get($player)?->isInTNTRun() ?? false)
            || $this->worldGuard->isProtectedWorld($player->getWorld())
        );
    }

    public function applyAdventureMode(Player $player): void {
        if ($player->getGamemode() === GameMode::ADVENTURE()) {
            return;
        }

        $player->setGamemode(GameMode::ADVENTURE());
    }
}
