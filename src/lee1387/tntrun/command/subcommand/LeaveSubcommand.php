<?php

declare(strict_types=1);

namespace lee1387\tntrun\command\subcommand;

use lee1387\tntrun\TNTRun;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class LeaveSubcommand implements Subcommand {
    public function __construct(
        private TNTRun $plugin
    ) {}

    public function getName(): string {
        return "leave";
    }

    public function execute(Player $player, array $args): void {
        if ($args !== []) {
            $player->sendMessage(TextFormat::RED . "Usage: /tntrun leave");
            return;
        }

        $playerSession = $this->plugin->getPlayerSessionManager()->get($player);
        $waitingWorld = $this->plugin->getWaitingWorld();
        if ($playerSession === null || !$waitingWorld->isPlayerJoined($playerSession)) {
            $player->sendMessage(TextFormat::YELLOW . "You are not in the TNTRun waiting world.");
            return;
        }

        if (!$this->plugin->getLeaveDestination()->send($player, $this->plugin->getWorldLoader())) {
            $player->sendMessage(TextFormat::RED . "Failed to send you to the configured leave destination.");
            return;
        }

        $this->plugin->getGameManager()->removePlayerSession($playerSession);
        $waitingWorld->leavePlayer($playerSession);
    }
}
