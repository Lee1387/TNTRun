<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

use InvalidArgumentException;

final class QueueSettings {
    public function __construct(
        private int $readyCountdownSeconds,
        private int $fullCountdownSeconds
    ) {
        if ($this->readyCountdownSeconds < 1) {
            throw new InvalidArgumentException("Queue ready countdown seconds must be at least 1.");
        }

        if ($this->fullCountdownSeconds < 1) {
            throw new InvalidArgumentException("Queue full countdown seconds must be at least 1.");
        }

        if ($this->fullCountdownSeconds > $this->readyCountdownSeconds) {
            throw new InvalidArgumentException("Queue full countdown seconds must be less than or equal to ready countdown seconds.");
        }
    }

    public function getReadyCountdownSeconds(): int {
        return $this->readyCountdownSeconds;
    }

    public function getFullCountdownSeconds(): int {
        return $this->fullCountdownSeconds;
    }
}
