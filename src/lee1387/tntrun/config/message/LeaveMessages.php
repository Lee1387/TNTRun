<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

final class LeaveMessages {
    public function __construct(
        private MessageFormatter $formatter,
        private string $itemName,
        private string $notInWaitingWorld,
        private string $destinationFailed
    ) {}

    public function itemName(): string {
        return $this->formatter->format($this->itemName);
    }

    public function notInWaitingWorld(): string {
        return $this->formatter->format($this->notInWaitingWorld);
    }

    public function destinationFailed(): string {
        return $this->formatter->format($this->destinationFailed);
    }
}
