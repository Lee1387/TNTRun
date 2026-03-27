<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

use InvalidArgumentException;

final class PlayMessages {
    /**
     * @param list<string> $eliminationMessages
     */
    public function __construct(
        private MessageFormatter $formatter,
        private string $playAgainItemName,
        private string $spectateItemName,
        private string $spectateFormTitle,
        private string $spectateFormContent,
        private string $spectateNoPlayers,
        private string $spectateNoGame,
        private string $spectatePlayerUnavailable,
        private array $eliminationMessages
    ) {
        if ($this->eliminationMessages === []) {
            throw new InvalidArgumentException('Play elimination messages cannot be empty.');
        }

        foreach ($this->eliminationMessages as $eliminationMessage) {
            if ($eliminationMessage === '') {
                throw new InvalidArgumentException('Play elimination messages cannot contain empty values.');
            }
        }
    }

    public function playAgainItemName(): string {
        return $this->formatter->format($this->playAgainItemName);
    }

    public function spectateItemName(): string {
        return $this->formatter->format($this->spectateItemName);
    }

    public function spectateFormTitle(): string {
        return $this->formatter->format($this->spectateFormTitle);
    }

    public function spectateFormContent(): string {
        return $this->formatter->format($this->spectateFormContent);
    }

    public function spectateNoPlayers(): string {
        return $this->formatter->format($this->spectateNoPlayers);
    }

    public function spectateNoGame(): string {
        return $this->formatter->format($this->spectateNoGame);
    }

    public function spectatePlayerUnavailable(): string {
        return $this->formatter->format($this->spectatePlayerUnavailable);
    }

    public function randomEliminationBroadcast(string $playerName): string {
        $template = $this->eliminationMessages[\array_rand($this->eliminationMessages)];

        return $this->formatter->format($template, [
            '{player}' => $playerName,
        ]);
    }
}
