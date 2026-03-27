<?php

declare(strict_types=1);

namespace lee1387\tntrun\bootstrap;

use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\game\queue\QueueManager;
use lee1387\tntrun\game\start\GameStartManager;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveService;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use lee1387\tntrun\waiting\WaitingWorldExitCoordinator;
use lee1387\tntrun\waiting\WaitingWorldLoadout;
use lee1387\tntrun\world\TNTRunWorldGuard;
use lee1387\tntrun\world\WorldLoader;

final class BootstrapRuntime {
    public function __construct(
        public readonly OnlinePlayerRegistry $onlinePlayerRegistry,
        public readonly PlayerSessionManager $playerSessionManager,
        public readonly TNTRunWorldGuard $worldGuard,
        public readonly TNTRunPlayerGuard $playerGuard,
        public readonly GameManager $gameManager,
        public readonly QueueManager $queueManager,
        public readonly GameStartManager $gameStartManager,
        public readonly WorldLoader $worldLoader,
        public readonly WaitingWorldExitCoordinator $waitingWorldExitCoordinator,
        public readonly WaitingWorldEntryService $waitingWorldEntryService,
        public readonly WaitingWorldLeaveService $waitingWorldLeaveService,
        public readonly WaitingWorldLoadout $waitingWorldLoadout
    ) {}
}
