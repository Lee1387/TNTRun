<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play\spectator\form;

use JsonSerializable;
use lee1387\tntrun\config\message\PlayMessages;
use lee1387\tntrun\game\GameInstance;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

final class SpectatePlayerForm implements Form, JsonSerializable {
    /**
     * @param list<string> $targetPlayerIds
     * @param list<string> $targetPlayerNames
     */
    public function __construct(
        private GameInstance $gameInstance,
        private string $spectatorPlayerId,
        private array $targetPlayerIds,
        private array $targetPlayerNames,
        private OnlinePlayerRegistry $onlinePlayerRegistry,
        private PlayMessages $messages
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        return [
            'type' => 'form',
            'title' => $this->messages->spectateFormTitle(),
            'content' => $this->messages->spectateFormContent(),
            'buttons' => \array_map(
                static fn (string $playerName): array => ['text' => $playerName],
                $this->targetPlayerNames
            ),
        ];
    }

    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            return;
        }

        if (!\is_int($data)) {
            throw new FormValidationException('Expected spectate form response to be an integer or null.');
        }

        if (!isset($this->targetPlayerIds[$data])) {
            throw new FormValidationException('Spectate form button index is out of range.');
        }

        if ($player->getUniqueId()->toString() !== $this->spectatorPlayerId) {
            return;
        }

        if (
            !$this->gameInstance->hasPlayerId($this->spectatorPlayerId)
            || !$this->gameInstance->isSpectatorPlayerId($this->spectatorPlayerId)
        ) {
            $player->sendMessage($this->messages->spectateNoGame());
            return;
        }

        $targetPlayerId = $this->targetPlayerIds[$data];
        if (!$this->gameInstance->isActivePlayerId($targetPlayerId)) {
            $player->sendMessage($this->messages->spectatePlayerUnavailable());
            return;
        }

        $targetPlayer = $this->onlinePlayerRegistry->getById($targetPlayerId);
        if ($targetPlayer === null || !$player->teleport($targetPlayer->getLocation())) {
            $player->sendMessage($this->messages->spectatePlayerUnavailable());
        }
    }
}
