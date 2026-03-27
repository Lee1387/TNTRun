<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play;

use InvalidArgumentException;
use pocketmine\block\Block;

final class ScheduledBlockFall {
    public function __construct(
        private string $worldName,
        private int $x,
        private int $y,
        private int $z,
        private int $ticksRemaining
    ) {
        if ($this->ticksRemaining < 1) {
            throw new InvalidArgumentException("Scheduled block fall ticks must be at least 1.");
        }
    }

    public static function fromBlock(Block $block, int $ticksRemaining): self {
        $position = $block->getPosition();

        return new self(
            $position->getWorld()->getFolderName(),
            (int) $position->x,
            (int) $position->y,
            (int) $position->z,
            $ticksRemaining
        );
    }

    public function getKey(): string {
        return "{$this->worldName}:{$this->x}:{$this->y}:{$this->z}";
    }

    public function getWorldName(): string {
        return $this->worldName;
    }

    public function getX(): int {
        return $this->x;
    }

    public function getY(): int {
        return $this->y;
    }

    public function getZ(): int {
        return $this->z;
    }

    public function tick(): bool {
        --$this->ticksRemaining;

        return $this->ticksRemaining === 0;
    }
}
