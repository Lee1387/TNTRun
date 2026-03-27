<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\start;

use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use lee1387\tntrun\waiting\WaitingWorldExitCoordinator;
use lee1387\tntrun\world\WorldLoader;

final class GameStartManager {
    public function __construct(
        private WorldLoader $worldLoader,
        private PlayerSessionManager $playerSessionManager,
        private TNTRunPlayerGuard $playerGuard,
        private WaitingWorldExitCoordinator $waitingWorldExitCoordinator,
        private ArenaStartBroadcaster $arenaStartBroadcaster,
        private OnlinePlayerRegistry $onlinePlayerRegistry
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

            $this->playerGuard->freeze($player);
            $this->waitingWorldExitCoordinator->markManagedExit($playerSession);
            if (!$player->teleport($playerSpawns[$index]->toLocation($world))) {
                $this->waitingWorldExitCoordinator->clearManagedExit($playerSession);
                $this->playerGuard->unfreeze($player);
                $allPlayersTransferred = false;
                continue;
            }

            $this->waitingWorldExitCoordinator->handleArenaTransfer($player, $playerSession);
        }

        if ($allPlayersTransferred) {
            $gameInstance->markPlayersTransferredToSelectedArena();
            $this->startArenaCountdown($gameInstance);
        }

        return $allPlayersTransferred;
    }

    public function tickArenaCountdown(GameInstance $gameInstance): void {
        if ($gameInstance->hasCompletedArenaCountdown()) {
            if ($gameInstance->hasBroadcastedArenaGo()) {
                return;
            }

            $this->arenaStartBroadcaster->sendGo($gameInstance);
            $this->unfreezePlayers($gameInstance);
            $gameInstance->markArenaGoBroadcasted();
            return;
        }

        if (!$gameInstance->hasStartedArenaCountdown()) {
            if (!$this->startArenaCountdown($gameInstance)) {
                return;
            }
            return;
        }

        if ($gameInstance->tickArenaCountdown()) {
            return;
        }

        $countdownSecondsRemaining = $gameInstance->getArenaCountdownSecondsRemaining();
        if ($countdownSecondsRemaining !== null) {
            $this->arenaStartBroadcaster->sendCountdown($gameInstance, $countdownSecondsRemaining);
        }
    }

    private function startArenaCountdown(GameInstance $gameInstance): bool {
        if (!$gameInstance->startArenaCountdown()) {
            return false;
        }

        $countdownSecondsRemaining = $gameInstance->getArenaCountdownSecondsRemaining();
        if ($countdownSecondsRemaining !== null) {
            $this->arenaStartBroadcaster->sendCountdown($gameInstance, $countdownSecondsRemaining);
        }

        return true;
    }

    private function unfreezePlayers(GameInstance $gameInstance): void {
        foreach ($gameInstance->getPlayerIds() as $playerId) {
            $player = $this->onlinePlayerRegistry->getById($playerId);
            if ($player === null) {
                continue;
            }

            $this->playerGuard->unfreeze($player);
        }
    }
}
