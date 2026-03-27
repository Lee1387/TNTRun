<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting;

use lee1387\tntrun\game\queue\QueueManager;
use lee1387\tntrun\player\PlayerSession;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use pocketmine\player\Player;

final class WaitingWorldExitCoordinator {
    /**
     * @var array<string, true>
     */
    private array $managedExitPlayerIds = [];

    public function __construct(
        private QueueManager $queueManager,
        private TNTRunPlayerGuard $playerGuard,
        private WaitingWorldLoadout $waitingWorldLoadout
    ) {}

    public function markManagedExit(PlayerSession $playerSession): void {
        $this->managedExitPlayerIds[$playerSession->getPlayerId()] = true;
    }

    public function clearManagedExit(PlayerSession $playerSession): void {
        unset($this->managedExitPlayerIds[$playerSession->getPlayerId()]);
    }

    public function consumeManagedExit(PlayerSession $playerSession): bool {
        if (!isset($this->managedExitPlayerIds[$playerSession->getPlayerId()])) {
            return false;
        }

        unset($this->managedExitPlayerIds[$playerSession->getPlayerId()]);

        return true;
    }

    public function handleLeave(Player $player, PlayerSession $playerSession): void {
        $this->clearManagedExit($playerSession);
        if ($playerSession->isInWaitingWorld()) {
            $this->queueManager->removePlayerSession($playerSession);
        } else {
            $this->queueManager->removePlayerSessionSilently($playerSession);
        }

        if ($playerSession->isInWaitingWorld()) {
            $playerSession->leaveWaitingWorld();
        }

        $this->playerGuard->cleanup($player);
        $this->waitingWorldLoadout->clear($player);
    }

    public function handleArenaTransfer(Player $player, PlayerSession $playerSession): void {
        $this->clearManagedExit($playerSession);
        $playerSession->leaveWaitingWorld();
        $this->waitingWorldLoadout->clear($player);
    }
}
