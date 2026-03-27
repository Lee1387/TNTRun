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
use RuntimeException;

final class BootstrapRuntimeFactory {
    public function __construct(
        private TNTRun $plugin
    ) {}

    public function create(BootstrapConfig $config): BootstrapRuntime {
        $onlinePlayerRegistry = new OnlinePlayerRegistry();
        $playerSessionManager = new PlayerSessionManager();
        $worldGuard = new TNTRunWorldGuard($this->resolveProtectedWorldNames($config));
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
        $worldLoader = new WorldLoader($this->plugin->getServer()->getWorldManager(), $worldGuard);
        $worldLoader->applyManagedWorldPolicies();

        if ($config->waitingWorld->isAutoJoinEnabled() && $worldLoader->loadAndSetAsDefault($config->waitingWorld->getWorldName()) === null) {
            throw new RuntimeException(\sprintf(
                'The TNTRun waiting world "%s" could not be loaded for auto-join startup.',
                $config->waitingWorld->getWorldName()
            ));
        }

        $waitingWorldExitCoordinator = new WaitingWorldExitCoordinator(
            $queueManager,
            $playerGuard,
            $waitingWorldLoadout
        );
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

    /**
     * @return list<string>
     */
    private function resolveProtectedWorldNames(BootstrapConfig $config): array {
        $worldNames = [
            $config->waitingWorld->getWorldName() => true,
        ];

        foreach ($config->arenaConfigs as $arenaConfig) {
            $worldNames[$arenaConfig->getWorldName()] = true;
        }

        return \array_keys($worldNames);
    }
}
