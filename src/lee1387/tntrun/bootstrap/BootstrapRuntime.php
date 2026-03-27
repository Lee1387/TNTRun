<?php

declare(strict_types=1);

namespace lee1387\tntrun\bootstrap;

use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\game\play\PlayTickProcessor;
use lee1387\tntrun\game\queue\QueueTickProcessor;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveService;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use lee1387\tntrun\waiting\WaitingWorldExitCoordinator;
use lee1387\tntrun\waiting\WaitingWorldLoadout;
use lee1387\tntrun\world\TNTRunWorldGuard;

final class BootstrapRuntime {
    public function __construct(
        public readonly OnlinePlayerRegistry $onlinePlayerRegistry,
        public readonly PlayerSessionManager $playerSessionManager,
        public readonly TNTRunWorldGuard $worldGuard,
        public readonly TNTRunPlayerGuard $playerGuard,
        public readonly GameManager $gameManager,
        public readonly PlayTickProcessor $playTickProcessor,
        public readonly QueueTickProcessor $queueTickProcessor,
        public readonly WaitingWorldExitCoordinator $waitingWorldExitCoordinator,
        public readonly WaitingWorldEntryService $waitingWorldEntryService,
        public readonly WaitingWorldLeaveService $waitingWorldLeaveService,
        public readonly WaitingWorldLoadout $waitingWorldLoadout
    ) {}
}
