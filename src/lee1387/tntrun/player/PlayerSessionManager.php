<?php

declare(strict_types=1);

namespace lee1387\tntrun\player;

use pocketmine\player\Player;

final class PlayerSessionManager {
    /**
     * @var array<string, PlayerSession>
     */
    private array $playerSessions = [];

    public function get(Player $player): ?PlayerSession {
        $playerId = $player->getUniqueId()->toString();

        return $this->getById($playerId);
    }

    public function getById(string $playerId): ?PlayerSession {
        return $this->playerSessions[$playerId] ?? null;
    }

    public function getOrCreate(Player $player): PlayerSession {
        $playerId = $player->getUniqueId()->toString();

        return $this->playerSessions[$playerId] ??= new PlayerSession($playerId);
    }

    public function remove(Player $player): void {
        $playerId = $player->getUniqueId()->toString();
        unset($this->playerSessions[$playerId]);
    }
}
