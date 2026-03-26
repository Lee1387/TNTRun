<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue\task;

use lee1387\tntrun\game\GameManager;
use pocketmine\scheduler\Task;

final class QueueTickTask extends Task {
    public function __construct(
        private GameManager $gameManager
    ) {}

    public function onRun(): void {
        $this->gameManager->tick();
    }
}
