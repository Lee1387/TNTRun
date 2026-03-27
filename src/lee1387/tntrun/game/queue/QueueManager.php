<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\player\PlayerSession;

final class QueueManager {
    /**
     * @var array<string, QueuePool>
     */
    private array $queuePools;

    private QueueAssigner $queueAssigner;

    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     */
    public function __construct(
        array $arenaConfigs,
        private GameManager $gameManager,
        private QueueSettings $queueSettings,
        private QueueBroadcaster $queueBroadcaster
    ) {
        \ksort($arenaConfigs);
        $this->queuePools = (new QueuePoolFactory())->build($arenaConfigs);
        $this->queueAssigner = new QueueAssigner();
    }

    public function assignPlayerSession(PlayerSession $playerSession): ?GameInstance {
        $currentGameInstance = $this->gameManager->findGameInstanceByPlayerSession($playerSession);
        if ($currentGameInstance !== null) {
            return $currentGameInstance;
        }

        $gameInstance = $this->queueAssigner->findMostPopulatedJoinableGameInstance($this->gameManager->getGameInstances());
        if ($gameInstance !== null) {
            if ($this->gameManager->assignPlayerSession($gameInstance, $playerSession)) {
                $this->queueBroadcaster->broadcastJoin($gameInstance, $playerSession);
            }

            return $gameInstance;
        }

        $queuePool = $this->queueAssigner->determineQueuePoolForNewGameInstance($this->queuePools, $this->gameManager->getGameInstances());
        if ($queuePool === null) {
            return null;
        }

        $gameInstance = $this->gameManager->createGameInstance($queuePool, $this->queueSettings);
        if ($this->gameManager->assignPlayerSession($gameInstance, $playerSession)) {
            $this->queueBroadcaster->broadcastJoin($gameInstance, $playerSession);
        }

        return $gameInstance;
    }

    public function findGameInstanceByPlayerSession(PlayerSession $playerSession): ?GameInstance {
        return $this->gameManager->findGameInstanceByPlayerSession($playerSession);
    }

    public function removePlayerSession(PlayerSession $playerSession): void {
        $this->removePlayerSessionInternal($playerSession, true);
    }

    public function removePlayerSessionSilently(PlayerSession $playerSession): void {
        $this->removePlayerSessionInternal($playerSession, false);
    }

    private function removePlayerSessionInternal(PlayerSession $playerSession, bool $broadcastLeave): void {
        $gameInstance = $this->gameManager->removePlayerSession($playerSession);
        if ($gameInstance === null) {
            return;
        }

        if ($broadcastLeave) {
            $this->queueBroadcaster->broadcastLeave($gameInstance, $playerSession);
        }

        if ($gameInstance->isEmpty()) {
            $this->gameManager->removeGameInstance($gameInstance);
        }
    }
}
