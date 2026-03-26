<?php

declare(strict_types=1);

namespace lee1387\tntrun\player;

use lee1387\tntrun\world\TNTRunWorldGuard;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
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

    public function prepare(Player $player): void {
        $player->getEffects()->clear();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->setHealth($player->getMaxHealth());

        $hungerManager = $player->getHungerManager();
        $hungerManager->setFood($hungerManager->getMaxFood());
        $hungerManager->setSaturation($hungerManager->getMaxFood());
        $hungerManager->setExhaustion(0.0);
        $hungerManager->setFoodTickTimer(0);

        if ($player->getGamemode() === GameMode::ADVENTURE()) {
            $this->applyNightVision($player);
            return;
        }

        $player->setGamemode(GameMode::ADVENTURE());
        $this->applyNightVision($player);
    }

    public function cleanup(Player $player): void {
        $player->getEffects()->remove(VanillaEffects::NIGHT_VISION());
    }

    private function applyNightVision(Player $player): void {
        $player->getEffects()->add(new EffectInstance(
            VanillaEffects::NIGHT_VISION(),
            20,
            visible: false,
            infinite: true
        ));
    }
}
