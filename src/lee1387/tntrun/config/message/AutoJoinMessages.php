<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

final class AutoJoinMessages {
    public function __construct(
        private MessageFormatter $formatter,
        private string $worldNotAvailable,
        private string $teleportFailed
    ) {}

    public function worldNotAvailable(string $worldName): string {
        return $this->formatter->format($this->worldNotAvailable, [
            "{world}" => $worldName,
        ]);
    }

    public function teleportFailed(): string {
        return $this->formatter->format($this->teleportFailed);
    }
}
