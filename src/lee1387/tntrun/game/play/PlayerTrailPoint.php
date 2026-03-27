<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play;

use pocketmine\player\Player;

final class PlayerTrailPoint {
    public function __construct(
        private string $worldName,
        private float $x,
        private float $z,
        private int $supportY
    ) {}

    public static function fromPlayer(Player $player): self {
        $location = $player->getLocation();
        $boundingBox = $player->getBoundingBox();

        return new self(
            $player->getWorld()->getFolderName(),
            $location->x,
            $location->z,
            (int) \floor($boundingBox->minY - 0.01)
        );
    }

    public function getWorldName(): string {
        return $this->worldName;
    }

    public function getX(): float {
        return $this->x;
    }

    public function getZ(): float {
        return $this->z;
    }

    public function getSupportY(): int {
        return $this->supportY;
    }
}
