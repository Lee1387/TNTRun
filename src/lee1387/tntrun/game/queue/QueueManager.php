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
        private GameManager $gameManager
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

        $playerSession->clearGameInstance();

        $gameInstance = $this->queueAssigner->findMostPopulatedJoinableGameInstance($playerSession, $this->gameManager->getGameInstances());
        if ($gameInstance !== null) {
            $gameInstance->addPlayer($playerSession);

            return $gameInstance;
        }

        $queuePool = $this->queueAssigner->determineQueuePoolForNewGameInstance($this->queuePools, $this->gameManager->getGameInstances());
        if ($queuePool === null) {
            return null;
        }

        $gameInstance = $this->gameManager->createGameInstance($queuePool);
        $gameInstance->addPlayer($playerSession);

        return $gameInstance;
    }

    public function removePlayerSession(PlayerSession $playerSession): void {
        $gameInstance = $this->gameManager->findGameInstanceByPlayerSession($playerSession);
        if ($gameInstance === null) {
            $playerSession->clearGameInstance();
            return;
        }

        $gameInstance->removePlayer($playerSession);
        if ($gameInstance->isEmpty()) {
            $this->gameManager->removeGameInstance($gameInstance);
        }
    }

    private function beginStartPath(): ?GameInstance {
        if ($this->queueAssigner->findLockedGameInstance($this->gameManager->getGameInstances()) !== null) {
            return null;
        }

        $gameInstance = $this->queueAssigner->findMostPopulatedReadyGameInstance($this->gameManager->getGameInstances());
        if ($gameInstance === null) {
            return null;
        }

        if (!$gameInstance->beginStartPath()) {
            return null;
        }

        return $gameInstance;
    }

    public function tick(): void {
        $lockedGameInstance = $this->queueAssigner->findLockedGameInstance($this->gameManager->getGameInstances());
        if ($lockedGameInstance !== null) {
            $lockedGameInstance->tickStartPath();
            return;
        }

        $this->beginStartPath();
    }
}
