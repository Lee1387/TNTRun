# TNTRun

## Main Config

General plugin configuration lives in `resources/config.yml` and is copied to:

```text
plugin_data/TNTRun/config.yml
```

This file currently controls:

- the TNTRun waiting world
- whether players auto-join the waiting world on connect
- where players are sent when they leave TNTRun

## Arena Packs

Arenas are not defined in `config.yml`.

Instead, each arena is its own pack inside:

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
If `plugin_data/TNTRun/arenas/` is empty on first startup, the plugin will copy bundled example arena packs there automatically.

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

Notes:

- the arena name comes from the folder name, so it does not need to be repeated in `arena.yml`
- the world source name must match the arena folder name
- `player-spawns` must contain at least `max-players` entries
- the plugin currently validates arena packs at startup

## Building

To build the plugin phar:

```powershell
php -d phar.readonly=0 tools/build-plugin.php
```
