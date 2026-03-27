<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

use lee1387\tntrun\config\ConfigValueReader;
use pocketmine\utils\Config;

final class MessagesConfigLoader {
    public function __construct(
        private Config $config,
        private ConfigValueReader $valueReader = new ConfigValueReader()
    ) {}

    public function load(): Messages {
        $formatter = new MessageFormatter();
        $joinData = $this->valueReader->requireMap($this->config->get("join"), "join");
        $leaveData = $this->valueReader->requireMap($this->config->get("leave"), "leave");
        $queueData = $this->valueReader->requireMap($this->config->get("queue"), "queue");
        $playData = $this->valueReader->requireMap($this->config->get("play"), "play");
        $voteData = $this->valueReader->requireMap($this->config->get("vote"), "vote");

        return new Messages(
            $this->loadJoinMessages($formatter, $joinData),
            $this->loadLeaveMessages($formatter, $leaveData),
            $this->loadQueueMessages($formatter, $queueData),
            $this->loadPlayMessages($formatter, $playData),
            $this->loadVoteMessages($formatter, $voteData)
        );
    }

    /**
     * @param array<string, mixed> $joinData
     */
    private function loadJoinMessages(MessageFormatter $formatter, array $joinData): JoinMessages {
        return new JoinMessages(
            $formatter,
            $this->valueReader->requireString($joinData, "already-joined", "join.already-joined"),
            $this->valueReader->requireString($joinData, "world-not-available", "join.world-not-available"),
            $this->valueReader->requireString($joinData, "teleport-failed", "join.teleport-failed"),
            $this->valueReader->requireString($joinData, "auto-join-world-not-available", "join.auto-join-world-not-available"),
            $this->valueReader->requireString($joinData, "auto-join-teleport-failed", "join.auto-join-teleport-failed")
        );
    }

    /**
     * @param array<string, mixed> $leaveData
     */
    private function loadLeaveMessages(MessageFormatter $formatter, array $leaveData): LeaveMessages {
        return new LeaveMessages(
            $formatter,
            $this->valueReader->requireString($leaveData, "item-name", "leave.item-name"),
            $this->valueReader->requireString($leaveData, "not-in-waiting-world", "leave.not-in-waiting-world"),
            $this->valueReader->requireString($leaveData, "destination-failed", "leave.destination-failed")
        );
    }

    /**
     * @param array<string, mixed> $queueData
     */
    private function loadQueueMessages(MessageFormatter $formatter, array $queueData): QueueMessages {
        return new QueueMessages(
            $formatter,
            $this->valueReader->requireString($queueData, "join-broadcast", "queue.join-broadcast"),
            $this->valueReader->requireString($queueData, "leave-broadcast", "queue.leave-broadcast")
        );
    }

    /**
     * @param array<string, mixed> $playData
     */
    private function loadPlayMessages(MessageFormatter $formatter, array $playData): PlayMessages {
        return new PlayMessages(
            $formatter,
            $this->valueReader->requireString($playData, "play-again-item-name", "play.play-again-item-name"),
            $this->valueReader->requireString($playData, "spectate-item-name", "play.spectate-item-name"),
            $this->valueReader->requireString($playData, "spectate-form-title", "play.spectate-form-title"),
            $this->valueReader->requireString($playData, "spectate-form-content", "play.spectate-form-content"),
            $this->valueReader->requireString($playData, "spectate-no-players", "play.spectate-no-players"),
            $this->valueReader->requireString($playData, "spectate-no-game", "play.spectate-no-game"),
            $this->valueReader->requireString($playData, "spectate-player-unavailable", "play.spectate-player-unavailable"),
            $this->valueReader->requireStringList($playData, "elimination-messages", "play.elimination-messages")
        );
    }

    /**
     * @param array<string, mixed> $voteData
     */
    private function loadVoteMessages(MessageFormatter $formatter, array $voteData): VoteMessages {
        return new VoteMessages(
            $formatter,
            $this->valueReader->requireString($voteData, "item-name", "vote.item-name"),
            $this->valueReader->requireString($voteData, "form-title", "vote.form-title"),
            $this->valueReader->requireString($voteData, "form-content", "vote.form-content"),
            $this->valueReader->requireString($voteData, "form-current-vote-none", "vote.form-current-vote-none"),
            $this->valueReader->requireString($voteData, "form-selected-suffix", "vote.form-selected-suffix"),
            $this->valueReader->requireString($voteData, "selected-broadcast", "vote.selected-broadcast"),
            $this->valueReader->requireString($voteData, "no-game", "vote.no-game"),
            $this->valueReader->requireString($voteData, "closed", "vote.closed"),
            $this->valueReader->requireString($voteData, "submitted", "vote.submitted")
        );
    }
}
