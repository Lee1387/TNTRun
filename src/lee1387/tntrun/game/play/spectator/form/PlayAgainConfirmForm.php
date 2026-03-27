<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play\spectator\form;

use JsonSerializable;
use lee1387\tntrun\config\message\JoinMessages;
use lee1387\tntrun\waiting\WaitingWorld;
use lee1387\tntrun\waiting\WaitingWorldEntryResult;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class PlayAgainConfirmForm implements Form, JsonSerializable {
    public function __construct(
        private WaitingWorld $waitingWorld,
        private WaitingWorldEntryService $waitingWorldEntryService,
        private JoinMessages $messages
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        return [
            'type' => 'modal',
            'title' => TextFormat::GREEN . 'Play Again',
            'content' => TextFormat::GRAY . 'Queue up for a new TNTRun game?',
            'button1' => TextFormat::GREEN . 'Play Again',
            'button2' => TextFormat::RED . 'Cancel',
        ];
    }

    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            return;
        }

        if (!\is_bool($data)) {
            throw new FormValidationException('Expected play again confirmation response to be a boolean or null.');
        }

        if (!$data) {
            return;
        }

        $result = $this->waitingWorldEntryService->enter($player);
        if ($result === WaitingWorldEntryResult::SUCCESS) {
            return;
        }

        $player->sendMessage(match ($result) {
            WaitingWorldEntryResult::ALREADY_JOINED => $this->messages->alreadyJoined(),
            WaitingWorldEntryResult::WORLD_NOT_AVAILABLE => $this->messages->worldNotAvailable($this->waitingWorld->getWorldName()),
            WaitingWorldEntryResult::TELEPORT_FAILED => $this->messages->teleportFailed(),
        });
    }
}
