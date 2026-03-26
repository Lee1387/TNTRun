<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\player\PlayerSession;
use lee1387\tntrun\player\PlayerSessionManager;

final class GameManager {
    /**
     * @var array<string, ArenaConfig>
     */
    private array $arenaConfigs;

    /**
     * @var array<string, GameInstance>
     */
    private array $gameInstances = [];

    private int $nextGameInstanceId = 1;

    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     */
    public function __construct(
        array $arenaConfigs,
        private PlayerSessionManager $playerSessionManager
    ) {
        \ksort($arenaConfigs);
        $this->arenaConfigs = $arenaConfigs;
    }

    /**
     * @return array<string, ArenaConfig>
     */
    public function getArenaConfigs(): array {
        return $this->arenaConfigs;
    }

    public function getArenaConfig(string $arenaName): ?ArenaConfig {
        return $this->arenaConfigs[$arenaName] ?? null;
    }

    /**
     * @return array<string, GameInstance>
     */
    public function getGameInstances(): array {
        return $this->gameInstances;
    }

    public function getGameInstance(string $gameInstanceId): ?GameInstance {
        return $this->gameInstances[$gameInstanceId] ?? null;
    }

    public function createGameInstance(?ArenaConfig $arenaConfig = null): GameInstance {
        $gameInstanceId = "game_" . $this->nextGameInstanceId++;
        $gameInstance = new GameInstance($gameInstanceId, $arenaConfig);
        $this->gameInstances[$gameInstanceId] = $gameInstance;

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

        return $this->getGameInstance($gameInstanceId);
    }
}
