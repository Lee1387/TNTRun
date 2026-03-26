<?php

declare(strict_types=1);

namespace lee1387\tntrun\command;

use lee1387\tntrun\TNTRun;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

final class JoinLobbyCommand extends Command implements PluginOwned {
    public function __construct(
        private TNTRun $plugin
    ) {
        parent::__construct(
            "tntrun",
            "Join the TNTRun lobby.",
            "/tntrun join"
        );

        $this->setPermission("tntrun.command.join");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$this->testPermission($sender)) {
            return;
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return;
        }

        if (\count($args) !== 1 || \strtolower($args[0]) !== "join") {
            $sender->sendMessage(TextFormat::RED . "Usage: /tntrun join");
            return;
        }

        $lobby = $this->plugin->getLobby();
        if ($lobby->isPlayerJoined($sender)) {
            $sender->sendMessage(TextFormat::YELLOW . "You are already in the TNTRun lobby.");
            return;
        }

        $world = $this->plugin->getWorldLoader()->load($lobby->getWorldName());
        if ($world === null) {
            $sender->sendMessage(TextFormat::RED . 'The lobby world "' . $lobby->getWorldName() . '" could not be loaded.');
            return;
        }

        if (!$sender->teleport($lobby->getSpawn()->toLocation($world))) {
            $sender->sendMessage(TextFormat::RED . "Failed to teleport you to the TNTRun lobby.");
            return;
        }

        $lobby->joinPlayer($sender);
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }
}
