<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

final class Messages {
    public function __construct(
        private JoinMessages $join,
        private LeaveMessages $leave,
        private QueueMessages $queue,
        private PlayMessages $play,
        private VoteMessages $vote
    ) {}

    public function join(): JoinMessages {
        return $this->join;
    }

    public function leave(): LeaveMessages {
        return $this->leave;
    }

    public function queue(): QueueMessages {
        return $this->queue;
    }

    public function play(): PlayMessages {
        return $this->play;
    }

    public function vote(): VoteMessages {
        return $this->vote;
    }
}
