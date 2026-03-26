<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\game\queue\QueuePool;
use lee1387\tntrun\game\queue\QueueSettings;
use lee1387\tntrun\player\PlayerSession;

final class GameManager {
    /**
     * @var array<string, GameInstance>
     */
    private array $gameInstances = [];
    /**
     * @var array<string, string>
     */
    private array $playerGameInstanceIds = [];

    private int $nextGameInstanceId = 1;

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
            unset($this->playerGameInstanceIds[$playerId]);
        }

        unset($this->gameInstances[$gameInstance->getId()]);
    }

    public function findGameInstanceByPlayerSession(PlayerSession $playerSession): ?GameInstance {
        $gameInstanceId = $this->playerGameInstanceIds[$playerSession->getPlayerId()] ?? null;
        if ($gameInstanceId === null) {
            return null;
        }

        return $this->gameInstances[$gameInstanceId] ?? null;
    }

    public function assignPlayerSession(GameInstance $gameInstance, PlayerSession $playerSession): bool {
        $currentGameInstance = $this->findGameInstanceByPlayerSession($playerSession);
        if ($currentGameInstance !== null) {
            return $currentGameInstance->getId() === $gameInstance->getId();
        }

        if (!$gameInstance->addPlayerId($playerSession->getPlayerId())) {
            return false;
        }

        $this->playerGameInstanceIds[$playerSession->getPlayerId()] = $gameInstance->getId();

        return true;
    }

    public function removePlayerSession(PlayerSession $playerSession): ?GameInstance {
        $gameInstance = $this->findGameInstanceByPlayerSession($playerSession);
        if ($gameInstance === null) {
            return null;
        }

        if (!$gameInstance->removePlayerId($playerSession->getPlayerId())) {
            return null;
        }

        unset($this->playerGameInstanceIds[$playerSession->getPlayerId()]);

        return $gameInstance;
    }
}
