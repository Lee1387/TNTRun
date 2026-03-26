<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting;

use lee1387\tntrun\config\message\AutoJoinMessages;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

final class AutoJoinListener implements Listener {
    public function __construct(
        private WaitingWorld $waitingWorld,
        private WaitingWorldEntryService $waitingWorldEntryService,
        private AutoJoinMessages $messages
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
            WaitingWorldEntryResult::WORLD_NOT_AVAILABLE => $this->messages->worldNotAvailable($this->waitingWorld->getWorldName()),
            WaitingWorldEntryResult::TELEPORT_FAILED => $this->messages->teleportFailed(),
        });
    }
}
