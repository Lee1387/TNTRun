<?php

declare(strict_types=1);

namespace lee1387\tntrun\player;

use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\world\TNTRunWorldGuard;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Human;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

final class TNTRunPlayerGuard {
    public function __construct(
        private PlayerSessionManager $playerSessionManager,
        private TNTRunWorldGuard $worldGuard,
        private GameManager $gameManager
    ) {}

    public function isProtected(Human $player): bool {
        return $player instanceof Player && (
            ($this->playerSessionManager->get($player)?->isInWaitingWorld() ?? false)
            || $this->worldGuard->isProtectedWorld($player->getWorld())
        );
    }

    public function prepare(Player $player): void {
        $player->setNoClientPredictions(false);
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

    public function prepareSpectator(Player $player): void {
        $player->setNoClientPredictions(false);
        $player->setGamemode(GameMode::SPECTATOR());
    }

    public function cleanup(Player $player): void {
        $player->setNoClientPredictions(false);

        if ($player->getGamemode() === GameMode::SPECTATOR()) {
            $player->setGamemode(GameMode::ADVENTURE());
        }

        $player->getEffects()->remove(VanillaEffects::NIGHT_VISION());
    }

    public function freeze(Player $player): void {
        $player->setNoClientPredictions();
    }

    public function unfreeze(Player $player): void {
        $player->setNoClientPredictions(false);
    }

    public function isSpectator(Player $player): bool {
        $playerSession = $this->playerSessionManager->get($player);
        if ($playerSession === null) {
            return false;
        }

        $gameInstance = $this->gameManager->findGameInstanceByPlayerSession($playerSession);
        if ($gameInstance === null) {
            return false;
        }

        return $gameInstance->isSpectatorPlayerId($playerSession->getPlayerId());
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
