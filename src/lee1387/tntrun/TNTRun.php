<?php

declare(strict_types=1);

namespace lee1387\tntrun;

use InvalidArgumentException;
use lee1387\tntrun\arena\io\ArenaPackLoader;
use lee1387\tntrun\arena\io\ArenaWorldInstaller;
use lee1387\tntrun\arena\io\BundledArenaPackSeeder;
use lee1387\tntrun\command\TNTRunCommand;
use lee1387\tntrun\config\TNTRunConfigLoader;
use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\support\LeaveDestination;
use lee1387\tntrun\support\WorldLoader;
use lee1387\tntrun\waiting\WaitingWorld;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use pocketmine\plugin\PluginBase;
use RuntimeException;

final class TNTRun extends PluginBase {
    private WaitingWorld $waitingWorld;
    private LeaveDestination $leaveDestination;
    private GameManager $gameManager;
    private WorldLoader $worldLoader;
    private WaitingWorldEntryService $waitingWorldEntryService;

    protected function onEnable(): void {
        $this->saveDefaultConfig();

        $bundledArenaPackSeeder = new BundledArenaPackSeeder(
            $this->getDataFolder(),
            $this->getResources(),
            $this->saveResource(...)
        );
        $bundledArenaPackSeeder->seed();

        try {
            $config = (new TNTRunConfigLoader($this->getConfig()))->load();
            $arenaConfigs = (new ArenaPackLoader($this->getDataFolder() . "arenas"))->load();
            (new ArenaWorldInstaller(
                $this->getServer()->getDataPath() . "worlds",
                $this->getDataFolder() . "tmp"
            ))->installAll($arenaConfigs);
        } catch (InvalidArgumentException | RuntimeException $exception) {
            throw new RuntimeException(
                "Failed to initialize TNTRun: {$exception->getMessage()}",
                previous: $exception
            );
        }

        $this->waitingWorld = $config["waitingWorld"];
        $this->leaveDestination = $config["leaveDestination"];
        $this->gameManager = new GameManager($arenaConfigs);
        $this->worldLoader = new WorldLoader($this->getServer()->getWorldManager());
        $this->waitingWorldEntryService = new WaitingWorldEntryService($this->waitingWorld, $this->worldLoader);

        $this->getServer()->getCommandMap()->register(
            $this->getDescription()->getName(),
            new TNTRunCommand($this)
        );
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function getWaitingWorld(): WaitingWorld {
        return $this->waitingWorld;
    }

    public function getWorldLoader(): WorldLoader {
        return $this->worldLoader;
    }

    public function getWaitingWorldEntryService(): WaitingWorldEntryService {
        return $this->waitingWorldEntryService;
    }

    public function getLeaveDestination(): LeaveDestination {
        return $this->leaveDestination;
    }

    public function getGameManager(): GameManager {
        return $this->gameManager;
    }
}
