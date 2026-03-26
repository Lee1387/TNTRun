<?php

declare(strict_types=1);

namespace lee1387\tntrun\config\message;

use pocketmine\utils\TextFormat;

final class MessageFormatter {
    /**
     * @param array<string, string> $placeholders
     */
    public function format(string $template, array $placeholders = []): string {
        return TextFormat::colorize(\strtr($template, $placeholders));
    }
}
