<?php

declare(strict_types=1);

namespace lee1387\tntrun\command;

use lee1387\tntrun\command\subcommand\JoinSubcommand;
use lee1387\tntrun\command\subcommand\LeaveSubcommand;
use lee1387\tntrun\command\subcommand\Subcommand;
use lee1387\tntrun\TNTRun;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

final class TNTRunCommand extends Command implements PluginOwned {
    /**
     * @var array<string, Subcommand>
     */
    private array $subcommands = [];

    public function __construct(
        private TNTRun $plugin
    ) {
        parent::__construct(
            "tntrun",
            "Join or leave the TNTRun waiting world.",
            "/tntrun <join|leave>"
        );

        $this->setPermission("tntrun.command.use");
        $this->registerSubcommand(new JoinSubcommand($this->plugin));
        $this->registerSubcommand(new LeaveSubcommand($this->plugin));
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$this->testPermission($sender)) {
            return;
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return;
        }

        if (\count($args) !== 1) {
            $sender->sendMessage(TextFormat::RED . "Usage: /tntrun <join|leave>");
            return;
        }

        $subcommand = \strtolower($args[0]);
        if (!isset($this->subcommands[$subcommand])) {
            $sender->sendMessage(TextFormat::RED . "Usage: /tntrun <join|leave>");
            return;
        }

        $this->subcommands[$subcommand]->execute($sender);
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    private function registerSubcommand(Subcommand $subcommand): void {
        $this->subcommands[$subcommand->getName()] = $subcommand;
    }
}
