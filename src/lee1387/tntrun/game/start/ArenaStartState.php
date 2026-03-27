<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\start;

use InvalidArgumentException;

final class ArenaStartState {
    private bool $selectedArenaPrepared = false;
    private bool $playersTransferred = false;
    private ?int $countdownSecondsRemaining = null;
    private bool $countdownCompleted = false;
    private bool $goBroadcasted = false;

    public function hasPreparedSelectedArena(): bool {
        return $this->selectedArenaPrepared;
    }

    public function markSelectedArenaPrepared(): void {
        $this->selectedArenaPrepared = true;
    }

    public function hasTransferredPlayers(): bool {
        return $this->playersTransferred;
    }

    public function markPlayersTransferred(): void {
        $this->selectedArenaPrepared = true;
        $this->playersTransferred = true;
    }

    public function hasStartedCountdown(): bool {
        return $this->countdownSecondsRemaining !== null || $this->countdownCompleted;
    }

    public function startCountdown(int $seconds): bool {
        if ($seconds < 1) {
            throw new InvalidArgumentException("Arena countdown seconds must be at least 1.");
        }

        if ($this->hasStartedCountdown()) {
            return false;
        }

        $this->countdownSecondsRemaining = $seconds;

        return true;
    }

    public function getCountdownSecondsRemaining(): ?int {
        return $this->countdownSecondsRemaining;
    }

    public function tickCountdown(): bool {
        if ($this->countdownSecondsRemaining === null) {
            return false;
        }

        --$this->countdownSecondsRemaining;
        if ($this->countdownSecondsRemaining !== 0) {
            return false;
        }

        $this->countdownSecondsRemaining = null;
        $this->countdownCompleted = true;

        return true;
    }

    public function hasCompletedCountdown(): bool {
        return $this->countdownCompleted;
    }

    public function hasBroadcastedGo(): bool {
        return $this->goBroadcasted;
    }

    public function markGoBroadcasted(): void {
        $this->goBroadcasted = true;
    }

    public function reset(): void {
        $this->selectedArenaPrepared = false;
        $this->playersTransferred = false;
        $this->countdownSecondsRemaining = null;
        $this->countdownCompleted = false;
        $this->goBroadcasted = false;
    }
}
