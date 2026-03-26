<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

final class QueueMessages {
    public function __construct(
        private MessageFormatter $formatter,
        private string $joinBroadcast,
        private string $leaveBroadcast
    ) {}

    public function joinBroadcast(string $playerName, int $currentPlayers, int $maxPlayers): string {
        return $this->formatter->format($this->joinBroadcast, [
            "{player}" => $playerName,
            "{current}" => (string) $currentPlayers,
            "{max}" => (string) $maxPlayers,
        ]);
    }

    public function leaveBroadcast(string $playerName, int $currentPlayers, int $maxPlayers): string {
        return $this->formatter->format($this->leaveBroadcast, [
            "{player}" => $playerName,
            "{current}" => (string) $currentPlayers,
            "{max}" => (string) $maxPlayers,
        ]);
    }
}
