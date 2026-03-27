<?php

declare(strict_types=1);

namespace lee1387\tntrun\world;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\world\World;

final class TNTRunWorldGuard {
    /**
     * @var array<string, true>
     */
    private array $protectedWorldNames = [];

    /**
     * @param list<string> $protectedWorldNames
     */
    public function __construct(array $protectedWorldNames) {
        foreach ($protectedWorldNames as $worldName) {
            $this->protectedWorldNames[$worldName] = true;
        }
    }

    public function isProtectedWorld(World $world): bool {
        return $this->isProtectedWorldName($world->getFolderName());
    }

    public function isProtectedWorldName(string $worldName): bool {
        return isset($this->protectedWorldNames[$worldName]);
    }

    public function isProtectedBlock(Block $block): bool {
        return $this->isProtectedWorld($block->getPosition()->getWorld());
    }

    public function isProtectedEntity(Entity $entity): bool {
        return $this->isProtectedWorld($entity->getWorld());
    }
}
