<?php

declare(strict_types=1);

namespace lee1387\tntrun\world;

use pocketmine\world\World;
use pocketmine\world\WorldException;
use pocketmine\world\WorldManager;

final class WorldLoader {
    public function __construct(
        private WorldManager $worldManager,
        private ?TNTRunWorldGuard $worldGuard = null
    ) {}

    public function load(string $worldName): ?World {
        $world = $this->worldManager->getWorldByName($worldName);
        if ($world !== null) {
            $this->applyManagedWorldPolicy($world);
            return $world;
        }

        try {
            if (!$this->worldManager->loadWorld($worldName)) {
                return null;
            }
        } catch (WorldException) {
            return null;
        }

        $world = $this->worldManager->getWorldByName($worldName);
        if ($world === null) {
            return null;
        }

        $this->applyManagedWorldPolicy($world);

        return $world;
    }

    public function loadAndSetAsDefault(string $worldName): ?World {
        $world = $this->load($worldName);
        if ($world === null) {
            return null;
        }

        $this->worldManager->setDefaultWorld($world);

        return $world;
    }

    public function applyManagedWorldPolicies(): void {
        foreach ($this->worldManager->getWorlds() as $world) {
            $this->applyManagedWorldPolicy($world);
        }
    }

    private function applyManagedWorldPolicy(World $world): void {
        if ($this->worldGuard === null || !$this->worldGuard->isProtectedWorld($world)) {
            return;
        }

        $world->setAutoSave(false);
    }
}
