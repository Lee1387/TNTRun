<?php

declare(strict_types=1);

namespace lee1387\tntrun\bootstrap;

use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\game\queue\QueueBroadcaster;
use lee1387\tntrun\game\queue\QueueManager;
use lee1387\tntrun\game\vote\VoteBroadcaster;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\player\TNTRunPlayerGuard;
use lee1387\tntrun\TNTRun;
use lee1387\tntrun\waiting\leave\WaitingWorldLeaveService;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use lee1387\tntrun\waiting\WaitingWorldExitCoordinator;
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
        $waitingWorldLoadout = new WaitingWorldLoadout($config->messages->leave(), $config->messages->vote());
        $gameManager = new GameManager();
        $queueManager = new QueueManager(
            $config->arenaConfigs,
            $gameManager,
            $config->queueSettings,
            new QueueBroadcaster($onlinePlayerRegistry, $config->messages->queue()),
            new VoteBroadcaster($onlinePlayerRegistry, $config->messages->vote())
        );
        $waitingWorldExitCoordinator = new WaitingWorldExitCoordinator(
            $queueManager,
            $playerGuard,
            $waitingWorldLoadout
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
            $waitingWorldExitCoordinator
        );

        return new BootstrapRuntime(
            $onlinePlayerRegistry,
            $playerSessionManager,
            $worldGuard,
            $playerGuard,
            $gameManager,
            $queueManager,
            $worldLoader,
            $waitingWorldExitCoordinator,
            $waitingWorldEntryService,
            $waitingWorldLeaveService,
            $waitingWorldLoadout
        );
    }
}
