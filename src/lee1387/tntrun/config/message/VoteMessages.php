<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

use pocketmine\utils\TextFormat;

final class VoteMessages {
    public function __construct(
        private MessageFormatter $formatter,
        private string $itemName,
        private string $formTitle,
        private string $formContent,
        private string $formCurrentVoteNone,
        private string $formSelectedSuffix,
        private string $selectedBroadcast,
        private string $noGame,
        private string $closed,
        private string $submitted
    ) {}

    public function itemName(): string {
        return $this->formatter->format($this->itemName);
    }

    public function formTitle(): string {
        return $this->formatter->format($this->formTitle);
    }

    public function formContent(string $currentVote): string {
        return $this->formatter->format($this->formContent, [
            "{current-vote}" => $currentVote,
        ]);
    }

    public function formCurrentVoteNone(): string {
        return $this->formatter->format($this->formCurrentVoteNone);
    }

    public function formSelectedSuffix(): string {
        $suffix = \ltrim($this->formatter->format($this->formSelectedSuffix));
        if ($suffix === "") {
            return "";
        }

        // Bedrock form buttons can swallow plain leading spaces before color codes,
        // so emit the separator after a reset code instead of relying on raw prefix whitespace.
        return TextFormat::RESET . " " . $suffix;
    }

    public function selectedBroadcast(string $arenaName, int $voteCount): string {
        return $this->formatter->format($this->selectedBroadcast, [
            "{arena}" => $arenaName,
            "{votes}" => (string) $voteCount,
        ]);
    }

    public function noGame(): string {
        return $this->formatter->format($this->noGame);
    }

    public function closed(): string {
        return $this->formatter->format($this->closed);
    }

    public function submitted(string $arenaName): string {
        return $this->formatter->format($this->submitted, [
            "{arena}" => $arenaName,
        ]);
    }
}
