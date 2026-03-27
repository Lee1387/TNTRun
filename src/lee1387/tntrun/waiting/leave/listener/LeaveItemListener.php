<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting\leave\listener;

use lee1387\tntrun\config\message\LeaveMessages;
use lee1387\tntrun\game\play\spectator\form\LeaveGameConfirmForm;
use lee1387\tntrun\player\TNTRunHotbarItems;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveResult;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveService;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\player\Player;

final class LeaveItemListener implements Listener {
    public function __construct(
        private TNTRunHotbarItems $hotbarItems,
        private TNTRunPlayerGuard $playerGuard,
        private WaitingWorldLeaveService $waitingWorldLeaveService,
        private LeaveMessages $messages
    ) {}

    public function onPlayerItemHeld(PlayerItemHeldEvent $event): void {
        if (
            !$this->playerGuard->isSpectator($event->getPlayer())
            || !$this->hotbarItems->isLeaveItem($event->getItem())
        ) {
            return;
        }

        $event->cancel();
        $event->getPlayer()->sendForm(new LeaveGameConfirmForm(
            $this->waitingWorldLeaveService,
            $this->messages
        ));
    }

    public function onPlayerItemUse(PlayerItemUseEvent $event): void {
        if (
            $this->playerGuard->isSpectator($event->getPlayer())
            || !$this->hotbarItems->isLeaveItem($event->getItem())
        ) {
            return;
        }

        $event->cancel();
        $this->handleLeave($event->getPlayer());
    }

    private function handleLeave(Player $player): void {
        $result = $this->waitingWorldLeaveService->leave($player);
        if ($result === WaitingWorldLeaveResult::DESTINATION_FAILED) {
            $player->sendMessage($this->messages->destinationFailed());
            return;
        }

        if ($result === WaitingWorldLeaveResult::NOT_IN_WAITING_WORLD) {
            $player->sendMessage($this->messages->notInWaitingWorld());
        }
    }
}
