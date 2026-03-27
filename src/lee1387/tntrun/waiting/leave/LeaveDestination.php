<?php

declare(strict_types=1);

namespace lee1387\tntrun\waiting\leave;

use InvalidArgumentException;
use lee1387\tntrun\arena\ArenaSpawn;
use LogicException;

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

    public function isTransfer(): bool {
        return $this->type === self::TYPE_TRANSFER;
    }

    public function getAddress(): string {
        if ($this->address === null) {
            throw new LogicException("Transfer leave destination is incomplete.");
        }

        return $this->address;
    }

    public function getPort(): int {
        if ($this->port === null) {
            throw new LogicException("Transfer leave destination is incomplete.");
        }

        return $this->port;
    }

    public function getWorldName(): string {
        if ($this->worldName === null) {
            throw new LogicException("World leave destination is incomplete.");
        }

        return $this->worldName;
    }

    public function getSpawn(): ArenaSpawn {
        if ($this->spawn === null) {
            throw new LogicException("World leave destination is incomplete.");
        }

        return $this->spawn;
    }
}
