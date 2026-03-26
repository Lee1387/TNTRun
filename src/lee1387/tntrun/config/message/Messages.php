<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

final class Messages {
    public function __construct(
        private CommandMessages $command,
        private JoinMessages $join,
        private LeaveMessages $leave,
        private AutoJoinMessages $autoJoin,
        private QueueMessages $queue
    ) {}

    public function command(): CommandMessages {
        return $this->command;
    }

    public function join(): JoinMessages {
        return $this->join;
    }

    public function leave(): LeaveMessages {
        return $this->leave;
    }

    public function autoJoin(): AutoJoinMessages {
        return $this->autoJoin;
    }

    public function queue(): QueueMessages {
        return $this->queue;
    }
}
