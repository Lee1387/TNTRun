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
use lee1387\tntrun\game\queue\task\QueueTickTask;
use lee1387\tntrun\infrastructure\OnlinePlayerRegistry;
use lee1387\tntrun\infrastructure\WorldLoader;
use lee1387\tntrun\player\PlayerLifecycleListener;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\waiting\AutoJoinListener;
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
        $gameManager = new GameManager($playerSessionManager);
        $queueBroadcaster = new QueueBroadcaster($onlinePlayerRegistry, $messages->queue());
        $queueManager = new QueueManager($arenaConfigs, $gameManager, $queueSettings, $queueBroadcaster);
        $worldLoader = new WorldLoader($this->plugin->getServer()->getWorldManager());
        $waitingWorldEntryService = new WaitingWorldEntryService(
            $waitingWorld,
            $queueManager,
            $playerSessionManager,
            $worldLoader
        );

        $this->registerCommand(new TNTRunCommand(
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
        ));

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

        $this->plugin->getScheduler()->scheduleRepeatingTask(new QueueTickTask($queueManager), 20);
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
