<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play\spectator\listener;

use lee1387\tntrun\config\message\JoinMessages;
use lee1387\tntrun\game\play\spectator\form\PlayAgainConfirmForm;
use lee1387\tntrun\player\TNTRunHotbarItems;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use lee1387\tntrun\waiting\WaitingWorld;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;

final class PlayAgainItemListener implements Listener {
    public function __construct(
        private TNTRunHotbarItems $hotbarItems,
        private TNTRunPlayerGuard $playerGuard,
        private WaitingWorld $waitingWorld,
        private WaitingWorldEntryService $waitingWorldEntryService,
        private JoinMessages $messages
    ) {}

    public function onPlayerItemHeld(PlayerItemHeldEvent $event): void {
        if (
            !$this->playerGuard->isSpectator($event->getPlayer())
            || !$this->hotbarItems->isPlayAgainItem($event->getItem())
        ) {
            return;
        }

        $event->cancel();
        $event->getPlayer()->sendForm(new PlayAgainConfirmForm(
            $this->waitingWorld,
            $this->waitingWorldEntryService,
            $this->messages
        ));
    }
}
