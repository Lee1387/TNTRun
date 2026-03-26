<?php

declare(strict_types=1);

namespace lee1387\tntrun\player;

final class PlayerSession {
    private bool $inWaitingWorld = false;
    private ?string $gameInstanceId = null;

    public function __construct(
        private string $playerId
    ) {}

    public function getPlayerId(): string {
        return $this->playerId;
    }

    public function isInWaitingWorld(): bool {
        return $this->inWaitingWorld;
    }

    public function joinWaitingWorld(): bool {
        if ($this->inWaitingWorld) {
            return false;
        }

        $this->inWaitingWorld = true;

        return true;
    }

    public function leaveWaitingWorld(): bool {
        if (!$this->inWaitingWorld) {
            return false;
        }

        $this->inWaitingWorld = false;

        return true;
    }

    public function getGameInstanceId(): ?string {
        return $this->gameInstanceId;
    }

    public function assignGameInstance(string $gameInstanceId): void {
        $this->gameInstanceId = $gameInstanceId;
    }

    public function clearGameInstance(): void {
        $this->gameInstanceId = null;
    }
}
