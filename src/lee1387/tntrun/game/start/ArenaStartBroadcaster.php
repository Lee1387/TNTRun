<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\start;

use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use pocketmine\utils\TextFormat;

final class ArenaStartBroadcaster {
    public function __construct(
        private OnlinePlayerRegistry $onlinePlayerRegistry
    ) {}

    public function sendCountdown(GameInstance $gameInstance, int $countdownSecondsRemaining): void {
        $title = $this->createCountdownTitle($countdownSecondsRemaining);
        if ($title === null || $title === "") {
            return;
        }

        foreach ($gameInstance->getPlayerIds() as $playerId) {
            $player = $this->onlinePlayerRegistry->getById($playerId);
            if ($player === null) {
                continue;
            }

            $player->sendTitle($title, "", 0, 20, 0);
        }
    }

    public function sendGo(GameInstance $gameInstance): void {
        foreach ($gameInstance->getPlayerIds() as $playerId) {
            $player = $this->onlinePlayerRegistry->getById($playerId);
            if ($player === null) {
                continue;
            }

            $player->sendTitle(TextFormat::GREEN . "GO!", "", 0, 20, 0);
        }
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
}
