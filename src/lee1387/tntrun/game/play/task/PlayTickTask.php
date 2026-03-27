<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play\task;

use lee1387\tntrun\game\play\PlayTickProcessor;
use pocketmine\scheduler\Task;

final class PlayTickTask extends Task {
    public function __construct(
        private PlayTickProcessor $playTickProcessor
    ) {}

    public function onRun(): void {
        $this->playTickProcessor->tick();
    }
}
