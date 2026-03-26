<?php

declare(strict_types=1);

namespace lee1387\tntrun\player;

use pocketmine\player\Player;

final class OnlinePlayerRegistry {
    /**
     * @var array<string, Player>
     */
    private array $players = [];

    public function track(Player $player): void {
        $this->players[$player->getUniqueId()->toString()] = $player;
    }

    public function untrack(Player $player): void {
        unset($this->players[$player->getUniqueId()->toString()]);
    }

    public function getById(string $playerId): ?Player {
        return $this->players[$playerId] ?? null;
    }
}
