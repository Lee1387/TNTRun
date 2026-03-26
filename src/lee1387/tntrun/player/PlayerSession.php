<?php

declare(strict_types=1);

namespace lee1387\tntrun\player;

final class PlayerSession {
    private bool $inWaitingWorld = false;
    private bool $managedWaitingWorldExit = false;

    public function __construct(
        private string $playerId
    ) {}

    public function getPlayerId(): string {
        return $this->playerId;
    }

    public function isInWaitingWorld(): bool {
        return $this->inWaitingWorld;
    }

    public function isInTNTRun(): bool {
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
        $this->managedWaitingWorldExit = false;

        return true;
    }

    public function markManagedWaitingWorldExit(): void {
        $this->managedWaitingWorldExit = true;
    }

    public function consumeManagedWaitingWorldExit(): bool {
        if (!$this->managedWaitingWorldExit) {
            return false;
        }

        $this->managedWaitingWorldExit = false;

        return true;
    }
}
