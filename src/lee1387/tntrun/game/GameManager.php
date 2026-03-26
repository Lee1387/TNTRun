<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\game\queue\QueueAssigner;
use lee1387\tntrun\game\queue\QueuePool;
use lee1387\tntrun\game\queue\QueuePoolFactory;
use lee1387\tntrun\player\PlayerSession;
use lee1387\tntrun\player\PlayerSessionManager;

final class GameManager {
    /**
     * @var array<string, QueuePool>
     */
    private array $queuePools;

    /**
     * @var array<string, GameInstance>
     */
    private array $gameInstances = [];

    private int $nextGameInstanceId = 1;

    private QueueAssigner $queueAssigner;

    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     */
    public function __construct(
        array $arenaConfigs,
        private PlayerSessionManager $playerSessionManager
    ) {
        \ksort($arenaConfigs);
        $this->queuePools = (new QueuePoolFactory())->build($arenaConfigs);
        $this->queueAssigner = new QueueAssigner();
    }

    public function createGameInstance(QueuePool $queuePool): GameInstance {
        $gameInstanceId = "game_" . $this->nextGameInstanceId++;
        $gameInstance = new GameInstance($gameInstanceId, $queuePool);
        $this->gameInstances[$gameInstanceId] = $gameInstance;

        return $gameInstance;
    }

    public function assignPlayerSession(PlayerSession $playerSession): ?GameInstance {
        $currentGameInstance = $this->findGameInstanceByPlayerSession($playerSession);
        if ($currentGameInstance !== null) {
            return $currentGameInstance;
        }

        $playerSession->clearGameInstance();

        $gameInstance = $this->queueAssigner->findMostPopulatedJoinableGameInstance($playerSession, $this->gameInstances);
        if ($gameInstance !== null) {
            $gameInstance->addPlayer($playerSession);

            return $gameInstance;
        }

        $queuePool = $this->queueAssigner->determineQueuePoolForNewGameInstance($this->queuePools, $this->gameInstances);
        if ($queuePool === null) {
            return null;
        }

        $gameInstance = $this->createGameInstance($queuePool);
        $gameInstance->addPlayer($playerSession);

        return $gameInstance;
    }

    public function removeGameInstance(GameInstance $gameInstance): void {
        foreach ($gameInstance->getPlayerIds() as $playerId) {
            $playerSession = $this->playerSessionManager->getById($playerId);
            if ($playerSession !== null) {
                $playerSession->clearGameInstance();
            }
        }

        unset($this->gameInstances[$gameInstance->getId()]);
    }

    public function findGameInstanceByPlayerSession(PlayerSession $playerSession): ?GameInstance {
        $gameInstanceId = $playerSession->getGameInstanceId();
        if ($gameInstanceId === null) {
            return null;
        }

        return $this->gameInstances[$gameInstanceId] ?? null;
    }

    public function removePlayerSession(PlayerSession $playerSession): void {
        $gameInstance = $this->findGameInstanceByPlayerSession($playerSession);
        if ($gameInstance === null) {
            $playerSession->clearGameInstance();
            return;
        }

        $gameInstance->removePlayer($playerSession);
        if ($gameInstance->isEmpty()) {
            $this->removeGameInstance($gameInstance);
        }
    }
}
