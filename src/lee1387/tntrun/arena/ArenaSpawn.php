<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena;

use pocketmine\entity\Location;
use pocketmine\world\World;

final class ArenaSpawn {
    public function __construct(
        private float $x,
        private float $y,
        private float $z,
        private float $yaw = 0.0,
        private float $pitch = 0.0
    ) {}

    public function toLocation(World $world): Location {
        return new Location(
            $this->x,
            $this->y,
            $this->z,
            $world,
            $this->yaw,
            $this->pitch
        );
    }
}
