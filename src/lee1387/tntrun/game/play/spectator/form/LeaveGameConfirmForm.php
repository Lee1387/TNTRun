<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play\spectator\form;

use JsonSerializable;
use lee1387\tntrun\config\message\LeaveMessages;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveResult;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveService;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class LeaveGameConfirmForm implements Form, JsonSerializable {
    public function __construct(
        private WaitingWorldLeaveService $waitingWorldLeaveService,
        private LeaveMessages $messages
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        return [
            'type' => 'modal',
            'title' => TextFormat::RED . 'Leave Game',
            'content' => TextFormat::GRAY . 'Leave this TNTRun game?',
            'button1' => TextFormat::RED . 'Leave Game',
            'button2' => TextFormat::GREEN . 'Stay',
        ];
    }

    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            return;
        }

        if (!\is_bool($data)) {
            throw new FormValidationException('Expected leave game confirmation response to be a boolean or null.');
        }

        if (!$data) {
            return;
        }

        $result = $this->waitingWorldLeaveService->leave($player);
        if ($result === WaitingWorldLeaveResult::DESTINATION_FAILED) {
            $player->sendMessage($this->messages->destinationFailed());
        }
    }
}
