<?php

declare(strict_types=1);

namespace lee1387\tntrun\config;

use InvalidArgumentException;
use lee1387\tntrun\game\queue\QueueSettings;
use lee1387\tntrun\infrastructure\LeaveDestination;
use lee1387\tntrun\waiting\WaitingWorld;
use pocketmine\utils\Config;

final class TNTRunConfigLoader {
    public function __construct(
        private Config $config,
        private ConfigValueReader $valueReader = new ConfigValueReader()
    ) {}

    /**
     * @return array{waitingWorld: WaitingWorld, queueSettings: QueueSettings, leaveDestination: LeaveDestination}
     */
    public function load(): array {
        $waitingWorldData = $this->valueReader->requireMap($this->config->get("waiting-world"), "waiting-world");

        return [
            "waitingWorld" => $this->loadWaitingWorld($waitingWorldData),
            "queueSettings" => $this->loadQueueSettings($waitingWorldData),
            "leaveDestination" => $this->loadLeaveDestination(),
        ];
    }

    /**
     * @param array<mixed> $waitingWorldData
     */
    private function loadWaitingWorld(array $waitingWorldData): WaitingWorld {
        return new WaitingWorld(
            $this->valueReader->requireBool($waitingWorldData, "auto-join", "waiting-world.auto-join"),
            $this->valueReader->requireString($waitingWorldData, "world", "waiting-world.world"),
            $this->valueReader->loadSpawn($waitingWorldData, "spawn", "waiting-world.spawn")
        );
    }

    /**
     * @param array<mixed> $waitingWorldData
     */
    private function loadQueueSettings(array $waitingWorldData): QueueSettings {
        return new QueueSettings(
            $this->valueReader->requireInt($waitingWorldData, "ready-countdown-seconds", "waiting-world.ready-countdown-seconds"),
            $this->valueReader->requireInt($waitingWorldData, "full-countdown-seconds", "waiting-world.full-countdown-seconds")
        );
    }

    private function loadLeaveDestination(): LeaveDestination {
        $leaveData = $this->valueReader->requireMap($this->config->get("leave-destination"), "leave-destination");
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
