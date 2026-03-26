<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

final class CommandMessages {
    public function __construct(
        private MessageFormatter $formatter,
        private string $usage,
        private string $playerOnly
    ) {}

    public function usage(): string {
        return $this->formatter->format($this->usage);
    }

    public function playerOnly(): string {
        return $this->formatter->format($this->playerOnly);
    }
}
