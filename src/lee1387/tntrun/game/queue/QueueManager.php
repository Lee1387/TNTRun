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
        $currentGameInstance = $this->gameManager->reconcilePlayerSessionMembership($playerSession);
        if ($currentGameInstance !== null) {
            return $currentGameInstance;
        }

        $playerSession->clearGameInstance();

        $gameInstance = $this->queueAssigner->findMostPopulatedJoinableGameInstance($playerSession, $this->gameManager->getGameInstances());
        if ($gameInstance !== null) {
            if ($gameInstance->addPlayer($playerSession)) {
                $this->queueBroadcaster->broadcastJoin($gameInstance, $playerSession);
            }

            return $gameInstance;
        }

        $queuePool = $this->queueAssigner->determineQueuePoolForNewGameInstance($this->queuePools, $this->gameManager->getGameInstances());
        if ($queuePool === null) {
            return null;
        }

        $gameInstance = $this->gameManager->createGameInstance($queuePool, $this->queueSettings);
        if ($gameInstance->addPlayer($playerSession)) {
            $this->queueBroadcaster->broadcastJoin($gameInstance, $playerSession);
        }

        return $gameInstance;
    }

    public function removePlayerSession(PlayerSession $playerSession): void {
        $gameInstance = $this->gameManager->reconcilePlayerSessionMembership($playerSession);
        if ($gameInstance === null) {
            $playerSession->clearGameInstance();
            return;
        }

        if ($gameInstance->removePlayer($playerSession)) {
            $this->queueBroadcaster->broadcastLeave($gameInstance, $playerSession);
        }

        if ($gameInstance->isEmpty()) {
            $this->gameManager->removeGameInstance($gameInstance);
        }
    }

    public function tick(): void {
        foreach ($this->gameManager->getGameInstances() as $gameInstance) {
            if ($gameInstance->hasCompletedQueueCountdown()) {
                continue;
            }

            $countdownSecondsRemaining = $gameInstance->getQueueCountdownSecondsRemaining();
            if ($countdownSecondsRemaining !== null) {
                $this->queueBroadcaster->sendCountdown($gameInstance, $countdownSecondsRemaining);
            }

            $gameInstance->tickQueueCountdown();
        }
    }
}
