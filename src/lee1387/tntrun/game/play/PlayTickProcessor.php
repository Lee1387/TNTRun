<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play;

final class PlayTickProcessor {
    public function __construct(
        private EliminationManager $eliminationManager,
        private BlockFallManager $blockFallManager
    ) {}

    public function tick(): void {
        $this->eliminationManager->tick();
        $this->blockFallManager->tick();
    }
}
