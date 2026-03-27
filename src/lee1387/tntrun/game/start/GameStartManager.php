<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\start;

use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\waiting\WaitingWorldExitCoordinator;
use lee1387\tntrun\world\WorldLoader;

final class GameStartManager {
    public function __construct(
        private WorldLoader $worldLoader,
        private OnlinePlayerRegistry $onlinePlayerRegistry,
        private PlayerSessionManager $playerSessionManager,
        private WaitingWorldExitCoordinator $waitingWorldExitCoordinator
    ) {}

    public function prepareSelectedArena(GameInstance $gameInstance): bool {
        if ($gameInstance->hasPreparedSelectedArena()) {
            return true;
        }

        $selectedArenaConfig = $gameInstance->getSelectedArenaConfig();
        if ($selectedArenaConfig === null) {
            return false;
        }

        if ($this->worldLoader->load($selectedArenaConfig->getWorldName()) === null) {
            return false;
        }

        $gameInstance->markSelectedArenaPrepared();

        return true;
    }

    public function transferPlayersToSelectedArena(GameInstance $gameInstance): bool {
        if ($gameInstance->hasTransferredPlayersToSelectedArena()) {
            return true;
        }

        $selectedArenaConfig = $gameInstance->getSelectedArenaConfig();
        if ($selectedArenaConfig === null) {
            return false;
        }

        $world = $this->worldLoader->load($selectedArenaConfig->getWorldName());
        if ($world === null) {
            return false;
        }

        $gameInstance->markSelectedArenaPrepared();
        $allPlayersTransferred = true;
        $playerSpawns = $selectedArenaConfig->getPlayerSpawns();

        foreach ($gameInstance->getPlayerIds() as $index => $playerId) {
            $player = $this->onlinePlayerRegistry->getById($playerId);
            if ($player === null) {
                continue;
            }

            $playerSession = $this->playerSessionManager->get($player);
            if ($playerSession === null || !$playerSession->isInWaitingWorld()) {
                continue;
            }

            $this->waitingWorldExitCoordinator->markManagedExit($playerSession);
            if (!$player->teleport($playerSpawns[$index]->toLocation($world))) {
                $this->waitingWorldExitCoordinator->clearManagedExit($playerSession);
                $allPlayersTransferred = false;
                continue;
            }

            $this->waitingWorldExitCoordinator->handleArenaTransfer($player, $playerSession);
        }

        if ($allPlayersTransferred) {
            $gameInstance->markPlayersTransferredToSelectedArena();
        }

        return $allPlayersTransferred;
    }
}
