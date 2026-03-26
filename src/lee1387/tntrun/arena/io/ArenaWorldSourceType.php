<?php

declare(strict_types=1);

namespace lee1387\tntrun\arena\io;

enum ArenaWorldSourceType: string {
    case DIRECTORY = "directory";
    case TAR = "tar";
    case ZIP = "zip";
}
