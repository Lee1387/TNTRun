<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

use lee1387\tntrun\game\GameInstance;

final class QueueAssigner {
    /**
     * @param array<string, GameInstance> $gameInstances
     */
    public function findMostPopulatedJoinableGameInstance(array $gameInstances): ?GameInstance {
        $selectedGameInstance = null;
        $selectedPlayerCount = -1;

        foreach ($gameInstances as $gameInstance) {
            if (!$gameInstance->canAcceptNewPlayers()) {
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
        $selectedQueuePool = null;
        $selectedTotalPlayerCount = null;
        $selectedGameInstanceCount = null;

        foreach ($queuePools as $queuePool) {
            $totalPlayerCount = 0;
            $gameInstanceCount = 0;

            foreach ($gameInstances as $gameInstance) {
                if (!$gameInstance->belongsToQueuePool($queuePool->getId())) {
                    continue;
                }

                $totalPlayerCount += $gameInstance->getPlayerCount();
                ++$gameInstanceCount;
            }

            if (
                $selectedQueuePool !== null
                && (
                    $totalPlayerCount > $selectedTotalPlayerCount
                    || ($totalPlayerCount === $selectedTotalPlayerCount && $gameInstanceCount >= $selectedGameInstanceCount)
                )
            ) {
                continue;
            }

            $selectedQueuePool = $queuePool;
            $selectedTotalPlayerCount = $totalPlayerCount;
            $selectedGameInstanceCount = $gameInstanceCount;
        }

        return $selectedQueuePool;
    }
}
