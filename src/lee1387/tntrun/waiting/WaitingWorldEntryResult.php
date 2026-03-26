<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting;

enum WaitingWorldEntryResult {
    case SUCCESS;
    case ALREADY_JOINED;
    case WORLD_NOT_AVAILABLE;
    case TELEPORT_FAILED;
}
