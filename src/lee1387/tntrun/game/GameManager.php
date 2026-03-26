<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\game\queue\QueuePool;
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

    public function createGameInstance(QueuePool $queuePool): GameInstance {
        $gameInstanceId = "game_" . $this->nextGameInstanceId++;
        $gameInstance = new GameInstance($gameInstanceId, $queuePool);
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
}
