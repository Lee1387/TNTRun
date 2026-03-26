<?php

declare(strict_types=1);

namespace lee1387\tntrun\command\subcommand;

use lee1387\tntrun\config\message\LeaveMessages;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveResult;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveService;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class LeaveSubcommand implements Subcommand {
    private const USAGE_MESSAGE = TextFormat::RED . "Usage: /tntrun leave";

    public function __construct(
        private LeaveMessages $messages,
        private WaitingWorldLeaveService $waitingWorldLeaveService
    ) {}

    public function getName(): string {
        return "leave";
    }

    public function execute(Player $player, array $args): void {
        if ($args !== []) {
            $player->sendMessage(self::USAGE_MESSAGE);
            return;
        }

        $result = $this->waitingWorldLeaveService->leave($player);
        if ($result === WaitingWorldLeaveResult::NOT_IN_WAITING_WORLD) {
            $player->sendMessage($this->messages->notInWaitingWorld());
            return;
        }

        if ($result === WaitingWorldLeaveResult::DESTINATION_FAILED) {
            $player->sendMessage($this->messages->destinationFailed());
        }
    }
}
