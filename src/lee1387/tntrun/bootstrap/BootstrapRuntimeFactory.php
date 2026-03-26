<?php

declare(strict_types=1);

namespace lee1387\tntrun\bootstrap;

use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\game\queue\QueueBroadcaster;
use lee1387\tntrun\game\queue\QueueManager;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use lee1387\tntrun\TNTRun;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveService;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use lee1387\tntrun\waiting\WaitingWorldLoadout;
use lee1387\tntrun\world\TNTRunWorldGuard;
use lee1387\tntrun\world\WorldLoader;

final class BootstrapRuntimeFactory {
    public function __construct(
        private TNTRun $plugin
    ) {}

    public function create(BootstrapConfig $config): BootstrapRuntime {
        $onlinePlayerRegistry = new OnlinePlayerRegistry();
        $playerSessionManager = new PlayerSessionManager();
        $worldGuard = new TNTRunWorldGuard([$config->waitingWorld->getWorldName()]);
        $playerGuard = new TNTRunPlayerGuard($playerSessionManager, $worldGuard);
        $waitingWorldLoadout = new WaitingWorldLoadout();
        $queueManager = new QueueManager(
            $config->arenaConfigs,
            new GameManager(),
            $config->queueSettings,
            new QueueBroadcaster($onlinePlayerRegistry, $config->messages->queue())
        );
        $worldLoader = new WorldLoader($this->plugin->getServer()->getWorldManager());
        $waitingWorldEntryService = new WaitingWorldEntryService(
            $config->waitingWorld,
            $queueManager,
            $playerSessionManager,
            $worldLoader,
            $playerGuard,
            $waitingWorldLoadout
        );
        $waitingWorldLeaveService = new WaitingWorldLeaveService(
            $playerSessionManager,
            $config->leaveDestination,
            $worldLoader,
            $queueManager,
            $playerGuard,
            $waitingWorldLoadout
        );

        return new BootstrapRuntime(
            $onlinePlayerRegistry,
            $playerSessionManager,
            $worldGuard,
            $playerGuard,
            $queueManager,
            $worldLoader,
            $waitingWorldEntryService,
            $waitingWorldLeaveService,
            $waitingWorldLoadout
        );
    }
}
