<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena;

final class Cuboid {
    public function __construct(
        private int $minX,
        private int $minY,
        private int $minZ,
        private int $maxX,
        private int $maxY,
        private int $maxZ
    ) {}

    public static function fromCorners(
        int $firstX,
        int $firstY,
        int $firstZ,
        int $secondX,
        int $secondY,
        int $secondZ
    ): self {
        return new self(
            min($firstX, $secondX),
            min($firstY, $secondY),
            min($firstZ, $secondZ),
            max($firstX, $secondX),
            max($firstY, $secondY),
            max($firstZ, $secondZ)
        );
    }

    public function getMinX(): int {
        return $this->minX;
    }

    public function getMinY(): int {
        return $this->minY;
    }

    public function getMinZ(): int {
        return $this->minZ;
    }

    public function getMaxX(): int {
        return $this->maxX;
    }

    public function getMaxY(): int {
        return $this->maxY;
    }

    public function getMaxZ(): int {
        return $this->maxZ;
    }

    public function contains(int $x, int $y, int $z): bool {
        return $x >= $this->minX
            && $x <= $this->maxX
            && $y >= $this->minY
            && $y <= $this->maxY
            && $z >= $this->minZ
            && $z <= $this->maxZ;
    }

    public function isSingleLayer(): bool {
        return $this->minY === $this->maxY;
    }
}
