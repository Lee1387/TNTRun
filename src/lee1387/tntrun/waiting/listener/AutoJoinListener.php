<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting\listener;

use lee1387\tntrun\config\message\JoinMessages;
use lee1387\tntrun\waiting\WaitingWorld;
use lee1387\tntrun\waiting\WaitingWorldEntryResult;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

final class AutoJoinListener implements Listener {
    public function __construct(
        private WaitingWorld $waitingWorld,
        private WaitingWorldEntryService $waitingWorldEntryService,
        private JoinMessages $messages
    ) {}

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        if (!$this->waitingWorld->isAutoJoinEnabled()) {
            return;
        }

        $player = $event->getPlayer();
        $result = $this->waitingWorldEntryService->enter($player);
        if ($result === WaitingWorldEntryResult::SUCCESS || $result === WaitingWorldEntryResult::ALREADY_JOINED) {
            return;
        }

        $player->kick(match ($result) {
            WaitingWorldEntryResult::WORLD_NOT_AVAILABLE => $this->messages->autoJoinWorldNotAvailable($this->waitingWorld->getWorldName()),
            WaitingWorldEntryResult::TELEPORT_FAILED => $this->messages->autoJoinTeleportFailed(),
        });
    }
}
