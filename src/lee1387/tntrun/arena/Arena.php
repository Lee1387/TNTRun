<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena;

use pocketmine\player\Player;

final class Arena {
    /**
     * @var array<string, true>
     */
    private array $joinedPlayerIds = [];

    public function __construct(
        private ArenaConfig $config
    ) {}

    public function getConfig(): ArenaConfig {
        return $this->config;
    }

    public function getName(): string {
        return $this->config->getName();
    }

    public function getWorldName(): string {
        return $this->config->getWorldName();
    }

    public function getWaitingSpawn(): ArenaSpawn {
        return $this->config->getWaitingSpawn();
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
