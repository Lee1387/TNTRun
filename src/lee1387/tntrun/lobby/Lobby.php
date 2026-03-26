<?php

declare(strict_types=1);

namespace lee1387\tntrun\lobby;

use lee1387\tntrun\arena\ArenaSpawn;
use pocketmine\player\Player;

final class Lobby {
    /**
     * @var array<string, true>
     */
    private array $joinedPlayerIds = [];

    public function __construct(
        private LobbyConfig $config
    ) {}

    public function getWorldName(): string {
        return $this->config->getWorldName();
    }

    public function getSpawn(): ArenaSpawn {
        return $this->config->getSpawn();
    }

    public function isPlayerJoined(Player $player): bool {
        return isset($this->joinedPlayerIds[$player->getUniqueId()->toString()]);
    }

    public function joinPlayer(Player $player): bool {
        $playerId = $player->getUniqueId()->toString();
        if (isset($this->joinedPlayerIds[$playerId])) {
            return false;
        }

        $this->joinedPlayerIds[$playerId] = true;

        return true;
    }
}
