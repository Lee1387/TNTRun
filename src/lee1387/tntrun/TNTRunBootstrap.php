<?php

declare(strict_types=1);

namespace lee1387\tntrun;

use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\arena\io\ArenaPackLoader;
use lee1387\tntrun\arena\io\ArenaWorldArchiveExtractor;
use lee1387\tntrun\arena\io\ArenaWorldInstaller;
use lee1387\tntrun\arena\io\BundledArenaPackSeeder;
use lee1387\tntrun\command\subcommand\JoinSubcommand;
use lee1387\tntrun\command\subcommand\LeaveSubcommand;
use lee1387\tntrun\command\TNTRunCommand;
use lee1387\tntrun\config\message\Messages;
use lee1387\tntrun\config\message\MessagesConfigLoader;
use lee1387\tntrun\config\TNTRunConfigLoader;
use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\game\queue\QueueBroadcaster;
use lee1387\tntrun\game\queue\QueueManager;
use lee1387\tntrun\game\queue\QueueSettings;
use lee1387\tntrun\game\queue\task\QueueTickTask;
use lee1387\tntrun\infrastructure\LeaveDestination;
use lee1387\tntrun\infrastructure\OnlinePlayerRegistry;
use lee1387\tntrun\infrastructure\WorldLoader;
use lee1387\tntrun\player\PlayerLifecycleListener;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\waiting\AutoJoinListener;
use lee1387\tntrun\waiting\WaitingWorld;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use lee1387\tntrun\waiting\WaitingWorldExitListener;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

final class TNTRunBootstrap {
    public function __construct(
        private TNTRun $plugin
    ) {}

    public function boot(): void {
        $this->saveResources();
        $this->seedBundledArenaPacks();

        $config = (new TNTRunConfigLoader($this->plugin->getConfig()))->load();
        $messages = $this->loadMessages();
        $arenaConfigs = $this->loadArenaConfigs();
        $this->installArenaWorlds($arenaConfigs);

        $waitingWorld = $config["waitingWorld"];
        $leaveDestination = $config["leaveDestination"];
        $queueSettings = $config["queueSettings"];

        $onlinePlayerRegistry = new OnlinePlayerRegistry();
        $playerSessionManager = new PlayerSessionManager();
        $queueManager = $this->createQueueManager($arenaConfigs, $queueSettings, $onlinePlayerRegistry, $messages);
        $worldLoader = new WorldLoader($this->plugin->getServer()->getWorldManager());
        $waitingWorldEntryService = $this->createWaitingWorldEntryService(
            $waitingWorld,
            $queueManager,
            $playerSessionManager,
            $worldLoader
        );

        $this->registerCommand($this->createCommand(
            $messages,
            $waitingWorld,
            $waitingWorldEntryService,
            $playerSessionManager,
            $leaveDestination,
            $worldLoader,
            $queueManager
        ));
        $this->registerRuntimeListeners(
            $waitingWorld,
            $messages,
            $waitingWorldEntryService,
            $playerSessionManager,
            $onlinePlayerRegistry,
            $queueManager
        );
        $this->scheduleQueueTickTask($queueManager);
    }

    private function saveResources(): void {
        $this->plugin->saveDefaultConfig();
        $this->plugin->saveResource("messages.yml");
    }

    private function seedBundledArenaPacks(): void {
        (new BundledArenaPackSeeder(
            $this->plugin->getDataFolder(),
            $this->plugin->getResources(),
            $this->plugin->saveResource(...)
        ))->seed();
    }

    private function loadMessages(): Messages {
        return (new MessagesConfigLoader(
            new Config($this->plugin->getDataFolder() . "messages.yml", Config::YAML)
        ))->load();
    }

    /**
     * @return array<string, ArenaConfig>
     */
    private function loadArenaConfigs(): array {
        return (new ArenaPackLoader($this->plugin->getDataFolder() . "arenas"))->load();
    }

    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     */
    private function installArenaWorlds(array $arenaConfigs): void {
        (new ArenaWorldInstaller(
            $this->plugin->getServer()->getDataPath() . "worlds",
            $this->plugin->getDataFolder() . "tmp",
            new ArenaWorldArchiveExtractor()
        ))->installAll($arenaConfigs);
    }

    /**
     * @param array<string, ArenaConfig> $arenaConfigs
     */
    private function createQueueManager(
        array $arenaConfigs,
        QueueSettings $queueSettings,
        OnlinePlayerRegistry $onlinePlayerRegistry,
        Messages $messages
    ): QueueManager {
        return new QueueManager(
            $arenaConfigs,
            new GameManager(),
            $queueSettings,
            new QueueBroadcaster($onlinePlayerRegistry, $messages->queue())
        );
    }

    private function createWaitingWorldEntryService(
        WaitingWorld $waitingWorld,
        QueueManager $queueManager,
        PlayerSessionManager $playerSessionManager,
        WorldLoader $worldLoader
    ): WaitingWorldEntryService {
        return new WaitingWorldEntryService(
            $waitingWorld,
            $queueManager,
            $playerSessionManager,
            $worldLoader
        );
    }

    private function createCommand(
        Messages $messages,
        WaitingWorld $waitingWorld,
        WaitingWorldEntryService $waitingWorldEntryService,
        PlayerSessionManager $playerSessionManager,
        LeaveDestination $leaveDestination,
        WorldLoader $worldLoader,
        QueueManager $queueManager
    ): TNTRunCommand {
        return new TNTRunCommand(
            $this->plugin,
            $messages->command(),
            new JoinSubcommand($messages->join(), $waitingWorld, $waitingWorldEntryService),
            new LeaveSubcommand(
                $messages->leave(),
                $playerSessionManager,
                $leaveDestination,
                $worldLoader,
                $queueManager
            )
        );
    }

    private function registerRuntimeListeners(
        WaitingWorld $waitingWorld,
        Messages $messages,
        WaitingWorldEntryService $waitingWorldEntryService,
        PlayerSessionManager $playerSessionManager,
        OnlinePlayerRegistry $onlinePlayerRegistry,
        QueueManager $queueManager
    ): void {
        $this->registerListener(new PlayerLifecycleListener(
            $playerSessionManager,
            $queueManager,
            $onlinePlayerRegistry
        ));
        $this->registerListener(new AutoJoinListener(
            $waitingWorld,
            $waitingWorldEntryService,
            $messages->autoJoin()
        ));
        $this->registerListener(new WaitingWorldExitListener(
            $waitingWorld,
            $queueManager,
            $playerSessionManager
        ));
    }

    private function scheduleQueueTickTask(QueueManager $queueManager): void {
        $this->plugin->getScheduler()->scheduleRepeatingTask(new QueueTickTask($queueManager), 20);
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
