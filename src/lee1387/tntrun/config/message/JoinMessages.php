<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

final class JoinMessages {
    public function __construct(
        private MessageFormatter $formatter,
        private string $alreadyJoined,
        private string $worldNotAvailable,
        private string $teleportFailed,
        private string $autoJoinWorldNotAvailable,
        private string $autoJoinTeleportFailed
    ) {}

    public function alreadyJoined(): string {
        return $this->formatter->format($this->alreadyJoined);
    }

    public function worldNotAvailable(string $worldName): string {
        return $this->formatter->format($this->worldNotAvailable, [
            "{world}" => $worldName,
        ]);
    }

    public function teleportFailed(): string {
        return $this->formatter->format($this->teleportFailed);
    }

    public function autoJoinWorldNotAvailable(string $worldName): string {
        return $this->formatter->format($this->autoJoinWorldNotAvailable, [
            "{world}" => $worldName,
        ]);
    }

    public function autoJoinTeleportFailed(): string {
        return $this->formatter->format($this->autoJoinTeleportFailed);
    }
}
