<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

use lee1387\tntrun\arena\ArenaConfig;

final class QueuePoolFactory {
    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     * @return array<string, QueuePool>
     */
    public function build(array $arenaConfigs): array {
        $queuePoolArenas = [];

        foreach ($arenaConfigs as $arenaConfig) {
            $queuePoolId = $this->getQueuePoolId($arenaConfig);
            $queuePoolArenas[$queuePoolId][$arenaConfig->getName()] = $arenaConfig;
        }

        \ksort($queuePoolArenas);

        $queuePools = [];
        foreach ($queuePoolArenas as $queuePoolId => $poolArenaConfigs) {
            $queuePools[$queuePoolId] = new QueuePool($queuePoolId, $poolArenaConfigs);
        }

        return $queuePools;
    }

    private function getQueuePoolId(ArenaConfig $arenaConfig): string {
        return "{$arenaConfig->getMinPlayers()}-{$arenaConfig->getMaxPlayers()}-{$arenaConfig->getCountdownSeconds()}";
    }
}
