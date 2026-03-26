<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

use lee1387\tntrun\arena\ArenaConfig;
use pocketmine\player\Player;

final class GameInstance {
    /**
     * @var array<string, true>
     */
    private array $playerIds = [];

    private GameState $state = GameState::WAITING;

    public function __construct(
        private string $id,
        private ?ArenaConfig $arenaConfig = null
    ) {}

    public function getId(): string {
        return $this->id;
    }

    public function getArenaConfig(): ?ArenaConfig {
        return $this->arenaConfig;
    }

    public function assignArena(ArenaConfig $arenaConfig): void {
        $this->arenaConfig = $arenaConfig;
    }

    public function getState(): GameState {
        return $this->state;
    }

    public function setState(GameState $state): void {
        $this->state = $state;
    }

    public function hasPlayer(Player $player): bool {
        return isset($this->playerIds[$player->getUniqueId()->toString()]);
    }

    public function addPlayer(Player $player): bool {
        $playerId = $player->getUniqueId()->toString();
        if (isset($this->playerIds[$playerId])) {
            return false;
        }

        $this->playerIds[$playerId] = true;

        return true;
    }

    public function removePlayer(Player $player): bool {
        $playerId = $player->getUniqueId()->toString();
        if (!isset($this->playerIds[$playerId])) {
            return false;
        }

        unset($this->playerIds[$playerId]);

        return true;
    }

    public function getPlayerCount(): int {
        return \count($this->playerIds);
    }

    public function isEmpty(): bool {
        return $this->playerIds === [];
    }

    /**
     * @return list<string>
     */
    public function getPlayerIds(): array {
        return \array_keys($this->playerIds);
    }
}
