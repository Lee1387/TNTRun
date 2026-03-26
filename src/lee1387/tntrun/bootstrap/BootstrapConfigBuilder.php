<?php

declare(strict_types=1);

namespace lee1387\tntrun\bootstrap;

use lee1387\tntrun\arena\ArenaConfig;
use lee1387\tntrun\arena\io\ArenaPackLoader;
use lee1387\tntrun\arena\io\ArenaWorldArchiveExtractor;
use lee1387\tntrun\arena\io\ArenaWorldInstaller;
use lee1387\tntrun\arena\io\BundledArenaPackSeeder;
use lee1387\tntrun\config\message\Messages;
use lee1387\tntrun\config\message\MessagesConfigLoader;
use lee1387\tntrun\config\TNTRunConfigLoader;
use lee1387\tntrun\TNTRun;
use pocketmine\utils\Config;

final class BootstrapConfigBuilder {
    public function __construct(
        private TNTRun $plugin
    ) {}

    public function build(): BootstrapConfig {
        $this->saveResources();
        $this->seedBundledArenaPacks();

        $config = (new TNTRunConfigLoader($this->plugin->getConfig()))->load();
        $messages = $this->loadMessages();
        $arenaConfigs = $this->loadArenaConfigs();
        $this->installArenaWorlds($arenaConfigs);

        return new BootstrapConfig(
            $config["waitingWorld"],
            $config["leaveDestination"],
            $config["queueSettings"],
            $messages,
            $arenaConfigs
        );
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
}
