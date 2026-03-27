<?php

declare(strict_types=1);

namespace lee1387\tntrun\bootstrap;

use lee1387\tntrun\command\subcommand\JoinSubcommand;
use lee1387\tntrun\command\subcommand\LeaveSubcommand;
use lee1387\tntrun\command\TNTRunCommand;
use lee1387\tntrun\game\queue\task\QueueTickTask;
use lee1387\tntrun\game\vote\listener\VoteItemListener;
use lee1387\tntrun\player\listener\PlayerLifecycleListener;
use lee1387\tntrun\player\listener\TNTRunProtectionListener;
use lee1387\tntrun\TNTRun;
use lee1387\tntrun\waiting\leave\listener\LeaveItemListener;
use lee1387\tntrun\waiting\listener\AutoJoinListener;
use lee1387\tntrun\waiting\listener\WaitingWorldExitListener;
use lee1387\tntrun\world\listener\TNTRunWorldProtectionListener;
use pocketmine\event\Listener;

final class BootstrapRegistrar {
    public function __construct(
        private TNTRun $plugin
    ) {}

    public function register(BootstrapConfig $config, BootstrapRuntime $runtime): void {
        $this->registerCommand(new TNTRunCommand(
            $this->plugin,
            new JoinSubcommand(
                $config->messages->join(),
                $config->waitingWorld,
                $runtime->waitingWorldEntryService
            ),
            new LeaveSubcommand(
                $config->messages->leave(),
                $runtime->waitingWorldLeaveService
            )
        ));

        $this->registerListener(new PlayerLifecycleListener(
            $runtime->playerSessionManager,
            $runtime->onlinePlayerRegistry,
            $runtime->waitingWorldExitCoordinator
        ));
        $this->registerListener(new TNTRunProtectionListener($runtime->playerGuard));
        $this->registerListener(new TNTRunWorldProtectionListener($runtime->worldGuard));
        $this->registerListener(new AutoJoinListener(
            $config->waitingWorld,
            $runtime->waitingWorldEntryService,
            $config->messages->join()
        ));
        $this->registerListener(new LeaveItemListener(
            $runtime->waitingWorldLoadout,
            $runtime->waitingWorldLeaveService,
            $config->messages->leave()
        ));
        $this->registerListener(new VoteItemListener(
            $runtime->waitingWorldLoadout,
            $runtime->playerSessionManager,
            $runtime->gameManager,
            $config->messages->vote()
        ));
        $this->registerListener(new WaitingWorldExitListener(
            $config->waitingWorld,
            $runtime->playerSessionManager,
            $runtime->waitingWorldExitCoordinator
        ));

        $this->plugin->getScheduler()->scheduleRepeatingTask(
            new QueueTickTask($runtime->queueManager, $runtime->gameStartManager),
            20
        );
    }

    private function registerCommand(TNTRunCommand $command): void {
        $this->plugin->getServer()->getCommandMap()->register(
            $this->plugin->getDescription()->getName(),
            $command
        );
    }

    private function registerListener(Listener $listener): void {
        $this->plugin->getServer()->getPluginManager()->registerEvents($listener, $this->plugin);
    }
}
