<?php

declare(strict_types=1);

namespace lee1387\tntrun;

use lee1387\tntrun\arena\Arena;
use lee1387\tntrun\arena\ArenaConfigLoader;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use RuntimeException;

final class TNTRun extends PluginBase {
    use SingletonTrait;

    private Arena $arena;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        $this->saveDefaultConfig();

        try {
            $arenaConfig = (new ArenaConfigLoader($this->getConfig()))->load();
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException(
                "Failed to load TNTRun arena configuration: " . $exception->getMessage(),
                previous: $exception
            );
        }

        $this->arena = new Arena($arenaConfig);

        $this->getLogger()->info(\sprintf(
            'Loaded arena "%s" in world "%s".',
            $this->arena->getName(),
            $this->arena->getWorldName()
        ));
    }

    public function getArena(): Arena {
        return $this->arena;
    }
}
