<?php

declare(strict_types=1);

namespace lee1387\tntrun\game;

enum GameState {
    case WAITING;
    case STARTING;
    case RUNNING;
    case ENDING;
}
