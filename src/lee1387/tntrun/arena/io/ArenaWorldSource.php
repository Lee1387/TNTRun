<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena\io;

use InvalidArgumentException;

final class ArenaWorldSource {
    public function __construct(
        private ArenaWorldSourceType $type,
        private string $path,
        private string $worldName
    ) {
        if ($this->path === "") {
            throw new InvalidArgumentException("Arena world source path cannot be empty.");
        }

        if ($this->worldName === "") {
            throw new InvalidArgumentException("Arena world source world name cannot be empty.");
        }
    }

    public function getType(): ArenaWorldSourceType {
        return $this->type;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getWorldName(): string {
        return $this->worldName;
    }
}
