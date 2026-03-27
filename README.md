# TNTRun

TNTRun is a PocketMine-MP minigame plugin built around:

- a shared waiting world
- automatic queueing
- arena-pack based map management
- in-waiting-world arena voting

## Download

- Latest Dev Build: https://poggit.pmmp.io/ci/Lee1387/TNTRun/~
- Latest Stable Release: coming soon

## Overview

Players can enter TNTRun with:

```text
/tntrun join
/tntrun leave
```

On dedicated TNTRun servers, players can also be sent into the waiting world automatically on join by enabling `waiting-world.auto-join` in `config.yml`.

## First Startup

General plugin configuration lives in:

```text
plugin_data/TNTRun/config.yml
```

Player-facing plugin messages live in:

```text
plugin_data/TNTRun/messages.yml
```

Arena packs live in:

```text
plugin_data/TNTRun/arenas/
```

If `plugin_data/TNTRun/arenas/` is empty on first startup, TNTRun will seed bundled example arena packs automatically.

## Configuration

### Main Config

`config.yml` currently controls:

- the TNTRun waiting world
- whether players auto-join the waiting world on connect
- waiting-world queue countdown behavior
- where players are sent when they leave TNTRun

Queue pools are derived automatically from arena `min-players` and `max-players`.
Players are assigned automatically after entering the waiting world.
The current queueing logic prefers the most populated joinable queue and creates a new queue when needed.

### Messages

`messages.yml` controls player-facing text such as:

- join and leave messages
- queue broadcast messages
- vote item and vote form text
- vote result announcements

## Arena Packs

Arenas are not defined in `config.yml`.

Each arena is its own pack inside:

```text
plugin_data/TNTRun/arenas/<arenaName>/
```

Each arena pack must contain:

- `arena.yml`
- exactly one world source named after the arena:
  - `<arenaName>/`
  - `<arenaName>.zip`
  - `<arenaName>.tar`

A bundled example pack is available at [`resources/arenas/aladdin`](resources/arenas/aladdin).

### Pack Layout

Example:

```text
plugin_data/TNTRun/arenas/aladdin/
  arena.yml
  aladdin.tar
```

Or:

```text
plugin_data/TNTRun/arenas/aladdin/
  arena.yml
  aladdin/
```

### arena.yml

Each arena pack needs an `arena.yml` file beside the world source. Example:

```yml
spectator-spawn:
  x: 0.5
  y: 85.0
  z: 0.5
  yaw: 0.0
  pitch: 0.0
player-spawns:
  - x: 10.5
    y: 64.0
    z: 10.5
    yaw: 0.0
    pitch: 0.0
  - x: 20.5
    y: 64.0
    z: 20.5
    yaw: 0.0
    pitch: 0.0
  - x: 30.5
    y: 64.0
    z: 30.5
    yaw: 0.0
    pitch: 0.0
  - x: 40.5
    y: 64.0
    z: 40.5
    yaw: 0.0
    pitch: 0.0
  - x: 50.5
    y: 64.0
    z: 50.5
    yaw: 0.0
    pitch: 0.0
  - x: 60.5
    y: 64.0
    z: 60.5
    yaw: 0.0
    pitch: 0.0
  - x: 70.5
    y: 64.0
    z: 70.5
    yaw: 0.0
    pitch: 0.0
  - x: 80.5
    y: 64.0
    z: 80.5
    yaw: 0.0
    pitch: 0.0
elimination-y: 58
min-players: 2
max-players: 8
countdown-seconds: 10
block-fall-delay-ticks: 8
```

### Arena Notes

- the arena name comes from the folder name, so it does not need to be repeated in `arena.yml`
- the world source name must match the arena folder name
- `player-spawns` must contain at least `max-players` entries
- the plugin currently validates arena packs at startup

## Development

### Build

To build the plugin phar:

```powershell
php -d phar.readonly=0 tools/build-plugin.php
```

### Code Quality

To run the formatter:

```powershell
composer format
```

To run static analysis:

```powershell
composer analyse
```
