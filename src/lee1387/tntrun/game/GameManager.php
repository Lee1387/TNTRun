<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\game\queue\QueuePool;
use lee1387\tntrun\game\queue\QueueSettings;
use lee1387\tntrun\player\PlayerSession;
use lee1387\tntrun\player\PlayerSessionManager;

final class GameManager {
    /**
     * @var array<string, GameInstance>
     */
    private array $gameInstances = [];

    private int $nextGameInstanceId = 1;

    public function __construct(
        private PlayerSessionManager $playerSessionManager
    ) {}

    public function createGameInstance(QueuePool $queuePool, QueueSettings $queueSettings): GameInstance {
        $gameInstanceId = "game_" . $this->nextGameInstanceId++;
        $gameInstance = new GameInstance($gameInstanceId, $queuePool, $queueSettings);
        $this->gameInstances[$gameInstanceId] = $gameInstance;

        return $gameInstance;
    }

    /**
     * @return array<string, GameInstance>
     */
    public function getGameInstances(): array {
        return $this->gameInstances;
    }

    public function removeGameInstance(GameInstance $gameInstance): void {
        foreach ($gameInstance->getPlayerIds() as $playerId) {
            $playerSession = $this->playerSessionManager->getById($playerId);
            if ($playerSession !== null && $playerSession->getGameInstanceId() === $gameInstance->getId()) {
                $playerSession->clearGameInstance();
            }
        }

        unset($this->gameInstances[$gameInstance->getId()]);
    }

    private function findGameInstanceByPlayerSession(PlayerSession $playerSession): ?GameInstance {
        $gameInstanceId = $playerSession->getGameInstanceId();
        if ($gameInstanceId === null) {
            return null;
        }

        return $this->gameInstances[$gameInstanceId] ?? null;
    }

    public function reconcilePlayerSessionMembership(PlayerSession $playerSession): ?GameInstance {
        $matchingGameInstances = $this->findGameInstancesContainingPlayerSession($playerSession);
        $preferredGameInstance = $this->resolvePreferredGameInstance($playerSession, $matchingGameInstances);
        if ($preferredGameInstance === null) {
            if ($playerSession->getGameInstanceId() !== null) {
                $playerSession->clearGameInstance();
            }

            return null;
        }

        foreach ($matchingGameInstances as $gameInstance) {
            if ($gameInstance->getId() === $preferredGameInstance->getId()) {
                continue;
            }

            $gameInstance->removePlayer($playerSession);
            if ($gameInstance->isEmpty()) {
                $this->removeGameInstance($gameInstance);
            }
        }

        if ($playerSession->getGameInstanceId() !== $preferredGameInstance->getId()) {
            $playerSession->assignGameInstance($preferredGameInstance->getId());
        }

        return $preferredGameInstance;
    }

    /**
     * @return list<GameInstance>
     */
    private function findGameInstancesContainingPlayerSession(PlayerSession $playerSession): array {
        $matchingGameInstances = [];

        foreach ($this->gameInstances as $gameInstance) {
            if (!$gameInstance->hasPlayer($playerSession)) {
                continue;
            }

            $matchingGameInstances[] = $gameInstance;
        }

        return $matchingGameInstances;
    }

    /**
     * @param list<GameInstance> $matchingGameInstances
     */
    private function resolvePreferredGameInstance(PlayerSession $playerSession, array $matchingGameInstances): ?GameInstance {
        $currentGameInstance = $this->findGameInstanceByPlayerSession($playerSession);
        if ($currentGameInstance !== null && $currentGameInstance->hasPlayer($playerSession)) {
            return $currentGameInstance;
        }

        if ($matchingGameInstances === []) {
            return null;
        }

        return $matchingGameInstances[0];
    }
}
