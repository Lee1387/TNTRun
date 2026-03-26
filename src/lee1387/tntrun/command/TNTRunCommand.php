<?php

declare(strict_types=1);

namespace lee1387\tntrun\command;

use lee1387\tntrun\command\subcommand\Subcommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

final class TNTRunCommand extends Command implements PluginOwned {
    private const USAGE = "/tntrun <join|leave>";
    private const USAGE_MESSAGE = TextFormat::RED . "Usage: " . self::USAGE;
    private const PLAYER_ONLY_MESSAGE = TextFormat::RED . "This command can only be used in-game.";

    /**
     * @var array<string, Subcommand>
     */
    private array $subcommands = [];

    public function __construct(
        private Plugin $plugin,
        Subcommand ...$subcommands
    ) {
        parent::__construct(
            "tntrun",
            "Join or leave the TNTRun waiting world.",
            self::USAGE
        );

        $this->setPermission("tntrun.command.use");

        foreach ($subcommands as $subcommand) {
            $this->registerSubcommand($subcommand);
        }
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$this->testPermission($sender)) {
            return;
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage(self::PLAYER_ONLY_MESSAGE);
            return;
        }

        if ($args === []) {
            $sender->sendMessage(self::USAGE_MESSAGE);
            return;
        }

        $subcommand = \strtolower($args[0]);
        if (!isset($this->subcommands[$subcommand])) {
            $sender->sendMessage(self::USAGE_MESSAGE);
            return;
        }

        /** @var list<string> $subcommandArgs */
        $subcommandArgs = \array_slice($args, 1);
        $this->subcommands[$subcommand]->execute($sender, $subcommandArgs);
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    private function registerSubcommand(Subcommand $subcommand): void {
        $this->subcommands[$subcommand->getName()] = $subcommand;
    }
}
