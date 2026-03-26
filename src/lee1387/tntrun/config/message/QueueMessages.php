<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

final class QueueMessages {
    /**
     * @param array<int, string> $countdownTitleOverrides
     */
    public function __construct(
        private MessageFormatter $formatter,
        private string $joinBroadcast,
        private string $leaveBroadcast,
        private string $countdownTip,
        private string $countdownTitle,
        private array $countdownTitleOverrides
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

    public function countdownTip(int $seconds): string {
        return $this->formatter->format($this->countdownTip, [
            "{seconds}" => $this->formatCountdownSeconds($seconds),
        ]);
    }

    public function countdownTitle(int $seconds): ?string {
        if ($seconds < 1 || $seconds > 10) {
            return null;
        }

        return $this->formatter->format($this->countdownTitleOverrides[$seconds] ?? $this->countdownTitle, [
            "{seconds}" => (string) $seconds,
        ]);
    }

    private function formatCountdownSeconds(int $seconds): string {
        $override = $this->countdownTitleOverrides[$seconds] ?? null;
        if ($override === null) {
            return (string) $seconds;
        }

        return $this->formatter->format($override, [
            "{seconds}" => (string) $seconds,
        ]);
    }
}
