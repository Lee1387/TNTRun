<?php

declare(strict_types=1);

namespace lee1387\tntrun;

use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\command\JoinLobbyCommand;
use lee1387\tntrun\config\TNTRunConfigLoader;
use lee1387\tntrun\lobby\Lobby;
use lee1387\tntrun\world\WorldLoader;
use pocketmine\plugin\PluginBase;
use RuntimeException;

final class TNTRun extends PluginBase {
    private Lobby $lobby;

    /**
     * @var array<string, ArenaConfig>
     */
    private array $arenaConfigs = [];

    private WorldLoader $worldLoader;

    protected function onEnable(): void {
        $this->saveDefaultConfig();

        try {
            $config = (new TNTRunConfigLoader($this->getConfig()))->load();
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException(
                "Failed to load TNTRun configuration: " . $exception->getMessage(),
                previous: $exception
            );
        }

        $this->lobby = new Lobby($config["lobby"]);
        $this->arenaConfigs = $config["arenas"];
        $this->worldLoader = new WorldLoader($this->getServer()->getWorldManager());

        $this->getServer()->getCommandMap()->register(
            $this->getDescription()->getName(),
            new JoinLobbyCommand($this)
        );
    }

    public function getLobby(): Lobby {
        return $this->lobby;
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
}
