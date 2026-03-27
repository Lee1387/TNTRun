<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play\task;

use lee1387\tntrun\game\play\BlockFallManager;
use pocketmine\scheduler\Task;

final class BlockFallTask extends Task {
    public function __construct(
        private BlockFallManager $blockFallManager
    ) {}

    public function onRun(): void {
        $this->blockFallManager->tick();
    }
}
