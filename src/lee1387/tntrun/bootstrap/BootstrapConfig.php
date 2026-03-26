<?php

declare(strict_types=1);

namespace lee1387\tntrun\bootstrap;

use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\config\message\Messages;
use lee1387\tntrun\game\queue\QueueSettings;
use lee1387\tntrun\waiting\LeaveDestination;
use lee1387\tntrun\waiting\WaitingWorld;

final class BootstrapConfig {
    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     */
    public function __construct(
        public readonly WaitingWorld $waitingWorld,
        public readonly LeaveDestination $leaveDestination,
        public readonly QueueSettings $queueSettings,
        public readonly Messages $messages,
        public readonly array $arenaConfigs
    ) {}
}
