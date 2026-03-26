<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting\leave;

use InvalidArgumentException;
use lee1387\tntrun\arena\ArenaSpawn;
use lee1387\tntrun\world\WorldLoader;
use LogicException;
use pocketmine\player\Player;

final class LeaveDestination {
    public const TYPE_TRANSFER = "transfer";
    public const TYPE_WORLD = "world";

    private function __construct(
        private string $type,
        private ?string $worldName = null,
        private ?ArenaSpawn $spawn = null,
        private ?string $address = null,
        private ?int $port = null
    ) {}

    public static function transfer(string $address, int $port): self {
        $address = \trim($address);
        if ($address === "") {
            throw new InvalidArgumentException("Leave destination transfer address cannot be empty.");
        }

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException("Leave destination transfer port must be between 1 and 65535.");
        }

        return new self(
            self::TYPE_TRANSFER,
            address: $address,
            port: $port
        );
    }

    public static function world(string $worldName, ArenaSpawn $spawn): self {
        $worldName = \trim($worldName);
        if ($worldName === "") {
            throw new InvalidArgumentException("Leave destination world name cannot be empty.");
        }

        return new self(
            self::TYPE_WORLD,
            worldName: $worldName,
            spawn: $spawn
        );
    }

    public function send(Player $player, WorldLoader $worldLoader): bool {
        if ($this->type === self::TYPE_TRANSFER) {
            if ($this->address === null || $this->port === null) {
                throw new LogicException("Transfer leave destination is incomplete.");
            }

            return $player->transfer($this->address, $this->port);
        }

        if ($this->worldName === null || $this->spawn === null) {
            throw new LogicException("World leave destination is incomplete.");
        }

        $world = $worldLoader->load($this->worldName);
        if ($world === null) {
            return false;
        }

        return $player->teleport($this->spawn->toLocation($world));
    }
}
