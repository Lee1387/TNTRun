<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

use InvalidArgumentException;
use lee1387\tntrun\config\ConfigValueReader;
use pocketmine\utils\Config;

final class MessagesConfigLoader {
    public function __construct(
        private Config $config,
        private ConfigValueReader $valueReader = new ConfigValueReader()
    ) {}

    public function load(): Messages {
        $formatter = new MessageFormatter();
        $commandData = $this->valueReader->requireMap($this->config->get("command"), "command");
        $joinData = $this->valueReader->requireMap($this->config->get("join"), "join");
        $leaveData = $this->valueReader->requireMap($this->config->get("leave"), "leave");
        $autoJoinData = $this->valueReader->requireMap($this->config->get("auto-join"), "auto-join");
        $queueData = $this->valueReader->requireMap($this->config->get("queue"), "queue");

        return new Messages(
            $this->loadCommandMessages($formatter, $commandData),
            $this->loadJoinMessages($formatter, $joinData),
            $this->loadLeaveMessages($formatter, $leaveData),
            $this->loadAutoJoinMessages($formatter, $autoJoinData),
            $this->loadQueueMessages($formatter, $queueData)
        );
    }

    /**
     * @param array<string, mixed> $commandData
     */
    private function loadCommandMessages(MessageFormatter $formatter, array $commandData): CommandMessages {
        return new CommandMessages(
            $formatter,
            $this->valueReader->requireString($commandData, "usage", "command.usage"),
            $this->valueReader->requireString($commandData, "player-only", "command.player-only")
        );
    }

    /**
     * @param array<string, mixed> $joinData
     */
    private function loadJoinMessages(MessageFormatter $formatter, array $joinData): JoinMessages {
        return new JoinMessages(
            $formatter,
            $this->valueReader->requireString($joinData, "usage", "join.usage"),
            $this->valueReader->requireString($joinData, "already-joined", "join.already-joined"),
            $this->valueReader->requireString($joinData, "world-not-available", "join.world-not-available"),
            $this->valueReader->requireString($joinData, "teleport-failed", "join.teleport-failed")
        );
    }

    /**
     * @param array<string, mixed> $leaveData
     */
    private function loadLeaveMessages(MessageFormatter $formatter, array $leaveData): LeaveMessages {
        return new LeaveMessages(
            $formatter,
            $this->valueReader->requireString($leaveData, "usage", "leave.usage"),
            $this->valueReader->requireString($leaveData, "not-in-waiting-world", "leave.not-in-waiting-world"),
            $this->valueReader->requireString($leaveData, "destination-failed", "leave.destination-failed")
        );
    }

    /**
     * @param array<string, mixed> $autoJoinData
     */
    private function loadAutoJoinMessages(MessageFormatter $formatter, array $autoJoinData): AutoJoinMessages {
        return new AutoJoinMessages(
            $formatter,
            $this->valueReader->requireString($autoJoinData, "world-not-available", "auto-join.world-not-available"),
            $this->valueReader->requireString($autoJoinData, "teleport-failed", "auto-join.teleport-failed")
        );
    }

    /**
     * @param array<string, mixed> $queueData
     */
    private function loadQueueMessages(MessageFormatter $formatter, array $queueData): QueueMessages {
        return new QueueMessages(
            $formatter,
            $this->valueReader->requireString($queueData, "join-broadcast", "queue.join-broadcast"),
            $this->valueReader->requireString($queueData, "leave-broadcast", "queue.leave-broadcast"),
            $this->valueReader->requireString($queueData, "countdown-tip", "queue.countdown-tip"),
            $this->valueReader->requireString($queueData, "countdown-title", "queue.countdown-title"),
            $this->loadQueueCountdownTitleOverrides($queueData)
        );
    }

    /**
     * @param array<string, mixed> $queueData
     * @return array<int, string>
     */
    private function loadQueueCountdownTitleOverrides(array $queueData): array {
        if (!\array_key_exists("countdown-title-overrides", $queueData)) {
            throw new InvalidArgumentException('Missing config key "queue.countdown-title-overrides".');
        }

        $overrideData = $queueData["countdown-title-overrides"];
        if (!\is_array($overrideData)) {
            throw new InvalidArgumentException('Config key "queue.countdown-title-overrides" must be an array.');
        }

        $overrides = [];

        foreach ($overrideData as $seconds => $message) {
            if (!\is_numeric($seconds)) {
                throw new InvalidArgumentException('Config key "queue.countdown-title-overrides" must use numeric keys only.');
            }

            $secondValue = (int) $seconds;
            if ($secondValue < 1 || $secondValue > 10) {
                throw new InvalidArgumentException('Config key "queue.countdown-title-overrides" must only define countdowns between 1 and 10.');
            }

            if (!\is_string($message)) {
                throw new InvalidArgumentException(\sprintf('Config key "queue.countdown-title-overrides.%s" must be a string.', $seconds));
            }

            $overrides[$secondValue] = \trim($message);
        }

        return $overrides;
    }
}
