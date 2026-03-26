<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\player\PlayerSession;

final class QueueAssigner {
    /**
     * @param array<string, GameInstance> $gameInstances
     */
    public function findMostPopulatedJoinableGameInstance(PlayerSession $playerSession, array $gameInstances): ?GameInstance {
        $selectedGameInstance = null;
        $selectedPlayerCount = -1;

        foreach ($gameInstances as $gameInstance) {
            if (!$gameInstance->canAcceptPlayer($playerSession)) {
                continue;
            }

            $playerCount = $gameInstance->getPlayerCount();
            if ($playerCount <= $selectedPlayerCount) {
                continue;
            }

            $selectedGameInstance = $gameInstance;
            $selectedPlayerCount = $playerCount;
        }

        return $selectedGameInstance;
    }

    /**
     * @param array<string, QueuePool> $queuePools
     * @param array<string, GameInstance> $gameInstances
     */
    public function determineQueuePoolForNewGameInstance(array $queuePools, array $gameInstances): ?QueuePool {
        $mostPopulatedGameInstance = null;
        $selectedPlayerCount = -1;

        foreach ($gameInstances as $gameInstance) {
            $playerCount = $gameInstance->getPlayerCount();
            if ($playerCount <= $selectedPlayerCount) {
                continue;
            }

            $mostPopulatedGameInstance = $gameInstance;
            $selectedPlayerCount = $playerCount;
        }

        if ($mostPopulatedGameInstance !== null) {
            return $mostPopulatedGameInstance->getQueuePool();
        }

        $queuePoolId = \array_key_first($queuePools);
        if ($queuePoolId === null) {
            return null;
        }

        return $queuePools[$queuePoolId];
    }
}
