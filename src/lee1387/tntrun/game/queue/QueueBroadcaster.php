<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

use lee1387\tntrun\config\message\QueueMessages;
use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\player\PlayerSession;
use pocketmine\utils\TextFormat;

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

            $player->sendTip($this->createCountdownTip($countdownSecondsRemaining));

            $title = $this->createCountdownTitle($countdownSecondsRemaining);
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

    private function createCountdownTip(int $seconds): string {
        return TextFormat::AQUA . "Game starts in " . $this->formatCountdownSeconds($seconds);
    }

    private function createCountdownTitle(int $seconds): ?string {
        return match ($seconds) {
            1 => TextFormat::RED . "1",
            2 => TextFormat::GOLD . "2",
            3 => TextFormat::YELLOW . "3",
            4 => TextFormat::AQUA . "4",
            5 => TextFormat::AQUA . "5",
            6 => TextFormat::AQUA . "6",
            7 => TextFormat::AQUA . "7",
            8 => TextFormat::AQUA . "8",
            9 => TextFormat::AQUA . "9",
            10 => TextFormat::AQUA . "10",
            default => null,
        };
    }

    private function formatCountdownSeconds(int $seconds): string {
        return match ($seconds) {
            1 => TextFormat::RED . "1",
            2 => TextFormat::GOLD . "2",
            3 => TextFormat::YELLOW . "3",
            default => TextFormat::WHITE . (string) $seconds,
        };
    }
}
