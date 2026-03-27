<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play\spectator\listener;

use lee1387\tntrun\config\message\PlayMessages;
use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\game\play\spectator\form\SpectatePlayerForm;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\player\TNTRunHotbarItems;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\player\Player;

final class SpectateItemListener implements Listener {
    public function __construct(
        private TNTRunHotbarItems $hotbarItems,
        private TNTRunPlayerGuard $playerGuard,
        private PlayerSessionManager $playerSessionManager,
        private GameManager $gameManager,
        private OnlinePlayerRegistry $onlinePlayerRegistry,
        private PlayMessages $messages
    ) {}

    public function onPlayerItemHeld(PlayerItemHeldEvent $event): void {
        if (
            !$this->playerGuard->isSpectator($event->getPlayer())
            || !$this->hotbarItems->isSpectateItem($event->getItem())
        ) {
            return;
        }

        $event->cancel();
        $this->openSpectateForm($event->getPlayer());
    }

    private function openSpectateForm(Player $player): void {
        $playerSession = $this->playerSessionManager->get($player);
        $gameInstance = $playerSession === null
            ? null
            : $this->gameManager->findGameInstanceByPlayerSession($playerSession);
        if (
            $playerSession === null
            || $gameInstance === null
            || !$gameInstance->isSpectatorPlayerId($playerSession->getPlayerId())
        ) {
            $player->sendMessage($this->messages->spectateNoGame());
            return;
        }

        $targetPlayerIds = [];
        $targetPlayerNames = [];

        foreach ($gameInstance->getActivePlayerIds() as $targetPlayerId) {
            $targetPlayer = $this->onlinePlayerRegistry->getById($targetPlayerId);
            if ($targetPlayer === null) {
                continue;
            }

            $targetPlayerIds[] = $targetPlayerId;
            $targetPlayerNames[] = $targetPlayer->getName();
        }

        if ($targetPlayerIds === []) {
            $player->sendMessage($this->messages->spectateNoPlayers());
            return;
        }

        $player->sendForm(new SpectatePlayerForm(
            $gameInstance,
            $playerSession->getPlayerId(),
            $targetPlayerIds,
            $targetPlayerNames,
            $this->onlinePlayerRegistry,
            $this->messages
        ));
    }
}
