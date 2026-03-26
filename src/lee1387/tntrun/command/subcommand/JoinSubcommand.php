<?php

declare(strict_types=1);

namespace lee1387\tntrun\command\subcommand;

use lee1387\tntrun\TNTRun;
use lee1387\tntrun\waiting\WaitingWorldEntryResult;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class JoinSubcommand implements Subcommand {
    public function __construct(
        private TNTRun $plugin
    ) {}

    public function getName(): string {
        return "join";
    }

    public function execute(Player $player): void {
        $result = $this->plugin->getWaitingWorldEntryService()->enter($player);

        if ($result === WaitingWorldEntryResult::SUCCESS) {
            return;
        }

        $player->sendMessage(match ($result) {
            WaitingWorldEntryResult::ALREADY_JOINED => TextFormat::YELLOW . "You are already in the TNTRun waiting world.",
            WaitingWorldEntryResult::WORLD_NOT_AVAILABLE => TextFormat::RED . 'The TNTRun waiting world "' . $this->plugin->getWaitingWorld()->getWorldName() . '" could not be loaded.',
            WaitingWorldEntryResult::TELEPORT_FAILED => TextFormat::RED . "Failed to teleport you to the TNTRun waiting world.",
        });
    }
}
