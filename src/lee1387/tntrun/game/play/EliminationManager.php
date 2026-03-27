<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play;

use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use pocketmine\player\Player;

final class EliminationManager {
    public function __construct(
        private GameManager $gameManager,
        private OnlinePlayerRegistry $onlinePlayerRegistry,
        private TNTRunPlayerGuard $playerGuard,
        private EliminationBroadcaster $eliminationBroadcaster
    ) {}

    public function tick(): void {
        foreach ($this->gameManager->getGameInstances() as $gameInstance) {
            $selectedArenaConfig = $gameInstance->getSelectedArenaConfig();
            if (
                !$gameInstance->hasStartedGameplay()
                || $selectedArenaConfig === null
            ) {
                continue;
            }

            foreach ($gameInstance->getActivePlayerIds() as $playerId) {
                $player = $this->onlinePlayerRegistry->getById($playerId);
                if (
                    $player === null
                    || $player->getWorld()->getFolderName() !== $selectedArenaConfig->getWorldName()
                    || $player->getLocation()->y >= $selectedArenaConfig->getEliminationY()
                ) {
                    continue;
                }

                if (!$player->teleport($selectedArenaConfig->getSpectatorSpawn()->toLocation($player->getWorld()))) {
                    continue;
                }

                if (!$gameInstance->markPlayerIdSpectator($playerId)) {
                    continue;
                }

                $this->playerGuard->prepareSpectator($player);
                $this->eliminationBroadcaster->broadcast($gameInstance, $player->getName());
            }
        }
    }

    public function broadcastDepartureIfActive(GameInstance $gameInstance, Player $player): void {
        if (
            !$gameInstance->hasStartedGameplay()
            || !$gameInstance->isActivePlayerId($player->getUniqueId()->toString())
        ) {
            return;
        }

        $this->eliminationBroadcaster->broadcast($gameInstance, $player->getName());
    }
}
