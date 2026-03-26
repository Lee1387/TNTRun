<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\vote\form;

use JsonSerializable;
use lee1387\tntrun\config\message\VoteMessages;
use lee1387\tntrun\game\GameInstance;
use pocketmine\form\Form;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

final class ArenaVoteForm implements Form, JsonSerializable {
    /**
     * @var list<string>
     */
    private array $arenaNames;
    /**
     * @var array<string, \lee1387\tntrun\arena\ArenaConfig>
     */
    private array $arenaConfigs;

    public function __construct(
        private GameInstance $gameInstance,
        private string $playerId,
        private VoteMessages $messages
    ) {
        $this->arenaConfigs = $this->gameInstance->getVotableArenaConfigs();
        $this->arenaNames = \array_keys($this->arenaConfigs);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        $currentVote = $this->gameInstance->getPlayerVoteArenaName($this->playerId);
        $currentVoteDisplay = $currentVote === null
            ? $this->messages->formCurrentVoteNone()
            : $this->arenaConfigs[$currentVote]->getDisplayName();
        $buttons = [];

        foreach ($this->arenaNames as $arenaName) {
            $text = $this->arenaConfigs[$arenaName]->getDisplayName();
            if ($currentVote === $arenaName) {
                $text .= $this->messages->formSelectedSuffix();
            }

            $buttons[] = ["text" => $text];
        }

        return [
            "type" => "form",
            "title" => $this->messages->formTitle(),
            "content" => $this->messages->formContent($currentVoteDisplay),
            "buttons" => $buttons,
        ];
    }

    public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            return;
        }

        if (!\is_int($data)) {
            throw new FormValidationException("Expected vote form response to be an integer or null.");
        }

        if (!isset($this->arenaNames[$data])) {
            throw new FormValidationException("Vote form button index is out of range.");
        }

        if ($player->getUniqueId()->toString() !== $this->playerId) {
            return;
        }

        if (!$this->gameInstance->hasPlayerId($this->playerId)) {
            $player->sendMessage($this->messages->noGame());
            return;
        }

        if (!$this->gameInstance->isVotingOpen()) {
            $player->sendMessage($this->messages->closed());
            return;
        }

        $arenaName = $this->arenaNames[$data];
        if (!$this->gameInstance->submitVote($this->playerId, $arenaName)) {
            $player->sendMessage($this->messages->closed());
            return;
        }

        $player->sendMessage($this->messages->submitted($this->arenaConfigs[$arenaName]->getDisplayName()));
    }
}
