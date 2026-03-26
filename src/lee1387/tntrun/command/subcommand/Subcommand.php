<?php

declare(strict_types=1);

namespace lee1387\tntrun\command\subcommand;

use pocketmine\player\Player;

interface Subcommand {
    public function getName(): string;

    public function execute(Player $player): void;
}
