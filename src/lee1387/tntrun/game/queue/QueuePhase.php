<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\queue;

enum QueuePhase {
    case WAITING;
    case READY;
    case LOCKED;
    case COUNTDOWN_COMPLETE;
}
