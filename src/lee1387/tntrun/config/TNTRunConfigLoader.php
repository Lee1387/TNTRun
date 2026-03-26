<?php

declare(strict_types=1);

namespace lee1387\tntrun\config;

use InvalidArgumentException;
use lee1387\tntrun\support\LeaveDestination;
use lee1387\tntrun\waiting\WaitingWorldConfig;
use pocketmine\utils\Config;

final class TNTRunConfigLoader {
    public function __construct(
        private Config $config,
        private ConfigValueReader $valueReader = new ConfigValueReader()
    ) {}

    /**
     * @return array{waitingWorld: WaitingWorldConfig, leaveDestination: LeaveDestination}
     */
    public function load(): array {
        return [
            "waitingWorld" => $this->loadWaitingWorldConfig(),
            "leaveDestination" => $this->loadLeaveDestination(),
        ];
    }

    private function loadWaitingWorldConfig(): WaitingWorldConfig {
        $waitingWorldData = $this->valueReader->requireArray($this->config->get("waiting-world"), "waiting-world");

        return new WaitingWorldConfig(
            $this->valueReader->requireBool($waitingWorldData, "auto-join", "waiting-world.auto-join"),
            $this->valueReader->requireString($waitingWorldData, "world", "waiting-world.world"),
            $this->valueReader->loadSpawn($waitingWorldData, "spawn", "waiting-world.spawn")
        );
    }

    private function loadLeaveDestination(): LeaveDestination {
        $leaveData = $this->valueReader->requireArray($this->config->get("leave-destination"), "leave-destination");
        $type = $this->valueReader->requireString($leaveData, "type", "leave-destination.type");

        if ($type === LeaveDestination::TYPE_WORLD) {
            return LeaveDestination::world(
                $this->valueReader->requireString($leaveData, "world", "leave-destination.world"),
                $this->valueReader->loadSpawn($leaveData, "spawn", "leave-destination.spawn")
            );
        }

        if ($type === LeaveDestination::TYPE_TRANSFER) {
            return LeaveDestination::transfer(
                $this->valueReader->requireString($leaveData, "address", "leave-destination.address"),
                $this->valueReader->requireInt($leaveData, "port", "leave-destination.port")
            );
        }

        throw new InvalidArgumentException('Config key "leave-destination.type" must be either "world" or "transfer".');
    }
}
