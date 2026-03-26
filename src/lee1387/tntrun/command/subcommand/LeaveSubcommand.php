<?php

declare(strict_types=1);

namespace lee1387\tntrun\command\subcommand;

use lee1387\tntrun\config\message\LeaveMessages;
use lee1387\tntrun\game\queue\QueueManager;
use lee1387\tntrun\infrastructure\LeaveDestination;
use lee1387\tntrun\infrastructure\WorldLoader;
use lee1387\tntrun\player\PlayerSessionManager;
use pocketmine\player\Player;

final class LeaveSubcommand implements Subcommand {
    public function __construct(
        private LeaveMessages $messages,
        private PlayerSessionManager $playerSessionManager,
        private LeaveDestination $leaveDestination,
        private WorldLoader $worldLoader,
        private QueueManager $queueManager
    ) {}

    public function getName(): string {
        return "leave";
    }

    public function execute(Player $player, array $args): void {
        if ($args !== []) {
            $player->sendMessage($this->messages->usage());
            return;
        }

        $playerSession = $this->playerSessionManager->get($player);
        if ($playerSession === null || !$playerSession->isInWaitingWorld()) {
            $player->sendMessage($this->messages->notInWaitingWorld());
            return;
        }

        if (!$this->leaveDestination->send($player, $this->worldLoader)) {
            $player->sendMessage($this->messages->destinationFailed());
            return;
        }

        $this->queueManager->removePlayerSession($playerSession);
        $playerSession->leaveWaitingWorld();
    }
}
