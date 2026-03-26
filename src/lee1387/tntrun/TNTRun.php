<?php

declare(strict_types=1);

namespace lee1387\tntrun;

use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\command\TNTRunCommand;
use lee1387\tntrun\config\TNTRunConfigLoader;
use lee1387\tntrun\support\LeaveDestination;
use lee1387\tntrun\support\WorldLoader;
use lee1387\tntrun\waiting\WaitingWorld;
use lee1387\tntrun\waiting\WaitingWorldEntryService;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use RuntimeException;

final class TNTRun extends PluginBase {
    private WaitingWorld $waitingWorld;

    /**
     * @var array<string, ArenaConfig>
     */
    private array $arenaConfigs = [];

    private LeaveDestination $leaveDestination;
    private WorldLoader $worldLoader;
    private WaitingWorldEntryService $waitingWorldEntryService;

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->saveResource("arenas.yml");

        try {
            $config = (new TNTRunConfigLoader(
                $this->getConfig(),
                new Config($this->getDataFolder() . "arenas.yml", Config::YAML)
            ))->load();
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException(
                "Failed to load TNTRun configuration: " . $exception->getMessage(),
                previous: $exception
            );
        }

        $this->waitingWorld = new WaitingWorld($config["waitingWorld"]);
        $this->arenaConfigs = $config["arenas"];
        $this->leaveDestination = $config["leaveDestination"];
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

    /**
     * @return array<string, ArenaConfig>
     */
    public function getArenaConfigs(): array {
        return $this->arenaConfigs;
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
}
