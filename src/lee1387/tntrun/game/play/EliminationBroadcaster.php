<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play;

use lee1387\tntrun\config\message\PlayMessages;
use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\player\OnlinePlayerRegistry;

final class EliminationBroadcaster {
    public function __construct(
        private OnlinePlayerRegistry $onlinePlayerRegistry,
        private PlayMessages $messages
    ) {}

    public function broadcast(GameInstance $gameInstance, string $playerName): void {
        $message = $this->messages->randomEliminationBroadcast($playerName);

        foreach ($gameInstance->getPlayerIds() as $playerId) {
            $player = $this->onlinePlayerRegistry->getById($playerId);
            if ($player === null) {
                continue;
            }

            $player->sendMessage($message);
        }
    }
}
