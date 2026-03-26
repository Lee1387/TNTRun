<?php

declare(strict_types=1);

namespace lee1387\tntrun\command;

use lee1387\tntrun\command\subcommand\Subcommand;
use lee1387\tntrun\config\message\CommandMessages;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

final class TNTRunCommand extends Command implements PluginOwned {
    private const USAGE = "/tntrun <join|leave>";

    /**
     * @var array<string, Subcommand>
     */
    private array $subcommands = [];

    public function __construct(
        private Plugin $plugin,
        private CommandMessages $messages,
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

        $usageMessage = $this->messages->usage();

        if (!$sender instanceof Player) {
            $sender->sendMessage($this->messages->playerOnly());
            return;
        }

        if ($args === []) {
            $sender->sendMessage($usageMessage);
            return;
        }

        $subcommand = \strtolower($args[0]);
        if (!isset($this->subcommands[$subcommand])) {
            $sender->sendMessage($usageMessage);
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
