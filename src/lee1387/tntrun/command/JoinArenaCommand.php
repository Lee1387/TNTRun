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

final class JoinArenaCommand extends Command implements PluginOwned {
    public function __construct(
        private TNTRun $plugin
    ) {
        parent::__construct(
            "tntrun",
            "Join the configured TNTRun arena.",
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

        if (\count($args) !== 1 || strtolower($args[0]) !== "join") {
            $sender->sendMessage(TextFormat::RED . "Usage: /tntrun join");
            return;
        }

        $arena = $this->plugin->getArena();
        if ($arena->isPlayerJoined($sender)) {
            $sender->sendMessage(TextFormat::YELLOW . "You have already joined the TNTRun arena.");
            return;
        }

        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($arena->getWorldName());
        if ($world === null) {
            $sender->sendMessage(TextFormat::RED . 'The arena world "' . $arena->getWorldName() . '" is not loaded.');
            return;
        }

        if (!$sender->teleport($arena->getWaitingSpawn()->toLocation($world))) {
            $sender->sendMessage(TextFormat::RED . "Failed to teleport you to the arena waiting spawn.");
            return;
        }

        $arena->joinPlayer($sender);
        $sender->sendMessage(TextFormat::GREEN . 'You joined arena "' . $arena->getName() . '".');
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }
}
