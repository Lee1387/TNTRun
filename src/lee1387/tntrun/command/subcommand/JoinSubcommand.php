<?php

declare(strict_types=1);

namespace lee1387\tntrun\command\subcommand;

use lee1387\tntrun\config\message\JoinMessages;
use lee1387\tntrun\waiting\WaitingWorld;
use lee1387\tntrun\waiting\WaitingWorldEntryResult;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class JoinSubcommand implements Subcommand {
    private const USAGE_MESSAGE = TextFormat::RED . "Usage: /tntrun join";

    public function __construct(
        private JoinMessages $messages,
        private WaitingWorld $waitingWorld,
        private WaitingWorldEntryService $waitingWorldEntryService
    ) {}

    public function getName(): string {
        return "join";
    }

    public function execute(Player $player, array $args): void {
        if ($args !== []) {
            $player->sendMessage(self::USAGE_MESSAGE);
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
