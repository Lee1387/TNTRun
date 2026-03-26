<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

use lee1387\tntrun\config\message\QueueMessages;
use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\infrastructure\OnlinePlayerRegistry;
use lee1387\tntrun\player\PlayerSession;

final class QueueBroadcaster {
    public function __construct(
        private OnlinePlayerRegistry $onlinePlayerRegistry,
        private QueueMessages $messages
    ) {}

    public function broadcastJoin(GameInstance $gameInstance, PlayerSession $playerSession): void {
        $this->broadcast(
            $gameInstance,
            $this->messages->joinBroadcast(
                $this->resolvePlayerName($playerSession),
                $gameInstance->getPlayerCount(),
                $gameInstance->getMaxPlayers()
            )
        );
    }

    public function broadcastLeave(GameInstance $gameInstance, PlayerSession $playerSession): void {
        $this->broadcast(
            $gameInstance,
            $this->messages->leaveBroadcast(
                $this->resolvePlayerName($playerSession),
                $gameInstance->getPlayerCount(),
                $gameInstance->getMaxPlayers()
            )
        );
    }

    public function sendCountdown(GameInstance $gameInstance, int $countdownSecondsRemaining): void {
        foreach ($gameInstance->getPlayerIds() as $playerId) {
            $player = $this->onlinePlayerRegistry->getById($playerId);
            if ($player === null) {
                continue;
            }

            $player->sendTip($this->messages->countdownTip($countdownSecondsRemaining));

            $title = $this->messages->countdownTitle($countdownSecondsRemaining);
            if ($title === null || $title === "") {
                continue;
            }

            $player->sendTitle($title, "", 0, 20, 0);
        }
    }

    private function broadcast(GameInstance $gameInstance, string $message): void {
        foreach ($gameInstance->getPlayerIds() as $playerId) {
            $player = $this->onlinePlayerRegistry->getById($playerId);
            if ($player === null) {
                continue;
            }

            $player->sendMessage($message);
        }
    }

    private function resolvePlayerName(PlayerSession $playerSession): string {
        $player = $this->onlinePlayerRegistry->getById($playerSession->getPlayerId());

        return $player?->getName() ?? $playerSession->getPlayerId();
    }
}
