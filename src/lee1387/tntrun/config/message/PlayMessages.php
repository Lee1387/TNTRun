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

    public function randomEliminationBroadcast(string $playerName): string {
        $template = $this->eliminationMessages[\array_rand($this->eliminationMessages)];

        return $this->formatter->format($template, [
            '{player}' => $playerName,
        ]);
    }
}
