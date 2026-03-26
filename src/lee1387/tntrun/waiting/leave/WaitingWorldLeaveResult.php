<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting\leave;

enum WaitingWorldLeaveResult {
    case SUCCESS;
    case NOT_IN_WAITING_WORLD;
    case DESTINATION_FAILED;
}
