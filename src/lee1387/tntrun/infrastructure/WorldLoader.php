<?php

declare(strict_types=1);

namespace lee1387\tntrun\infrastructure;

use pocketmine\world\World;
use pocketmine\world\WorldException;
use pocketmine\world\WorldManager;

final class WorldLoader {
    public function __construct(
        private WorldManager $worldManager
    ) {}

    public function load(string $worldName): ?World {
        $world = $this->worldManager->getWorldByName($worldName);
        if ($world !== null) {
            return $world;
        }

        try {
            if (!$this->worldManager->loadWorld($worldName)) {
                return null;
            }
        } catch (WorldException) {
            return null;
        }

        return $this->worldManager->getWorldByName($worldName);
    }
}
