<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena;

final class Arena {
    public function __construct(
        private ArenaConfig $config
    ) {}

    public function getConfig(): ArenaConfig {
        return $this->config;
    }

    public function getName(): string {
        return $this->config->getName();
    }

    public function getWorldName(): string {
        return $this->config->getWorldName();
    }
}
