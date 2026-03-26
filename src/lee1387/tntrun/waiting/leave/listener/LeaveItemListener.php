<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting\leave\listener;

use lee1387\tntrun\config\message\LeaveMessages;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveResult;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveService;
use lee1387\tntrun\waiting\WaitingWorldLoadout;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;

final class LeaveItemListener implements Listener {
    public function __construct(
        private WaitingWorldLoadout $waitingWorldLoadout,
        private WaitingWorldLeaveService $waitingWorldLeaveService,
        private LeaveMessages $messages
    ) {}

    public function onPlayerItemUse(PlayerItemUseEvent $event): void {
        if (!$this->waitingWorldLoadout->isLeaveItem($event->getItem())) {
            return;
        }

        $event->cancel();

        $player = $event->getPlayer();
        $result = $this->waitingWorldLeaveService->leave($player);
        if ($result === WaitingWorldLeaveResult::DESTINATION_FAILED) {
            $player->sendMessage($this->messages->destinationFailed());
        }
    }
}
