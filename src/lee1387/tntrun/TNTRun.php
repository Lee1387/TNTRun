<?php

declare(strict_types=1);

namespace lee1387\tntrun;

use InvalidArgumentException;
use lee1387\tntrun\bootstrap\TNTRunBootstrap;
use pocketmine\plugin\PluginBase;
use RuntimeException;

final class TNTRun extends PluginBase {
    protected function onEnable(): void {
        try {
            (new TNTRunBootstrap($this))->boot();
        } catch (InvalidArgumentException | RuntimeException $exception) {
            throw new RuntimeException(
                "Failed to initialize TNTRun: {$exception->getMessage()}",
                previous: $exception
            );
        }
    }
}
