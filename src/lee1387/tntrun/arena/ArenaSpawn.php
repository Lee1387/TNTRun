<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena;

use InvalidArgumentException;

final class ArenaSpawn {
    public function __construct(
        private float $x,
        private float $y,
        private float $z,
        private float $yaw = 0.0,
        private float $pitch = 0.0
    ) {
        foreach ([
            "x" => $this->x,
            "y" => $this->y,
            "z" => $this->z,
            "yaw" => $this->yaw,
            "pitch" => $this->pitch,
        ] as $field => $value) {
            if (!is_finite($value)) {
                throw new InvalidArgumentException(\sprintf('Arena spawn %s must be a finite number.', $field));
            }
        }
    }

    public function getX(): float {
        return $this->x;
    }

    public function getY(): float {
        return $this->y;
    }

    public function getZ(): float {
        return $this->z;
    }

    public function getYaw(): float {
        return $this->yaw;
    }

    public function getPitch(): float {
        return $this->pitch;
    }
}
