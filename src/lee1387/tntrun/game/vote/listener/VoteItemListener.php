<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\vote\listener;

use lee1387\tntrun\config\message\VoteMessages;
use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\game\vote\form\ArenaVoteForm;
use lee1387\tntrun\player\PlayerSessionManager;
use lee1387\tntrun\player\TNTRunHotbarItems;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;

final class VoteItemListener implements Listener {
    public function __construct(
        private TNTRunHotbarItems $hotbarItems,
        private PlayerSessionManager $playerSessionManager,
        private GameManager $gameManager,
        private VoteMessages $messages
    ) {}

    public function onPlayerItemUse(PlayerItemUseEvent $event): void {
        if (!$this->hotbarItems->isVoteItem($event->getItem())) {
            return;
        }

        $event->cancel();

        $player = $event->getPlayer();
        $playerSession = $this->playerSessionManager->get($player);
        if ($playerSession === null || !$playerSession->isInWaitingWorld()) {
            $player->sendMessage($this->messages->noGame());
            return;
        }

        $gameInstance = $this->gameManager->findGameInstanceByPlayerSession($playerSession);
        if ($gameInstance === null || !$gameInstance->hasPlayerId($playerSession->getPlayerId())) {
            $player->sendMessage($this->messages->noGame());
            return;
        }

        if (!$gameInstance->isVotingOpen()) {
            $player->sendMessage($this->messages->closed());
            return;
        }

        $player->sendForm(new ArenaVoteForm(
            $gameInstance,
            $playerSession->getPlayerId(),
            $this->messages
        ));
    }
}
