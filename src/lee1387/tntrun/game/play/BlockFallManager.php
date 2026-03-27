<?php

declare(strict_types=1);

namespace lee1387\tntrun\game\play;

use lee1387\tntrun\game\GameManager;
use lee1387\tntrun\player\OnlinePlayerRegistry;
use lee1387\tntrun\world\WorldLoader;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\World;

final class BlockFallManager {
    private const SUPPORT_SEARCH_RADIUS = 1;
    private const TRAIL_SAMPLES_PER_BLOCK = 4;

    /**
     * @var array<string, ScheduledBlockFall>
     */
    private array $scheduledBlockFalls = [];
    /**
     * @var array<string, PlayerTrailPoint>
     */
    private array $lastTrailPoints = [];

    public function __construct(
        private WorldLoader $worldLoader,
        private GameManager $gameManager,
        private OnlinePlayerRegistry $onlinePlayerRegistry
    ) {}

    public function tick(): void {
        foreach ($this->scheduledBlockFalls as $key => $scheduledBlockFall) {
            if (!$scheduledBlockFall->tick()) {
                continue;
            }

            $world = $this->worldLoader->load($scheduledBlockFall->getWorldName());
            if ($world !== null) {
                $world->setBlockAt(
                    $scheduledBlockFall->getX(),
                    $scheduledBlockFall->getY(),
                    $scheduledBlockFall->getZ(),
                    VanillaBlocks::AIR()
                );
            }

            unset($this->scheduledBlockFalls[$key]);
        }

        $this->scheduleActiveGameplayBlockFalls();
    }

    private function scheduleActiveGameplayBlockFalls(): void {
        $activePlayerIds = [];

        foreach ($this->gameManager->getGameInstances() as $gameInstance) {
            $selectedArenaConfig = $gameInstance->getSelectedArenaConfig();
            if (
                !$gameInstance->hasStartedGameplay()
                || $selectedArenaConfig === null
            ) {
                continue;
            }

            foreach ($gameInstance->getActivePlayerIds() as $playerId) {
                $player = $this->onlinePlayerRegistry->getById($playerId);
                if (
                    $player === null
                    || $player->getWorld()->getFolderName() !== $selectedArenaConfig->getWorldName()
                ) {
                    continue;
                }

                $activePlayerIds[$playerId] = true;

                $currentTrailPoint = PlayerTrailPoint::fromPlayer($player);
                $this->scheduleTrailBlockFalls(
                    $this->lastTrailPoints[$playerId] ?? null,
                    $currentTrailPoint,
                    $player->getWorld(),
                    $selectedArenaConfig->getBlockFallDelayTicks()
                );
                $this->lastTrailPoints[$playerId] = $currentTrailPoint;
            }
        }

        foreach (\array_keys($this->lastTrailPoints) as $playerId) {
            if (!isset($activePlayerIds[$playerId])) {
                unset($this->lastTrailPoints[$playerId]);
            }
        }
    }

    private function scheduleTrailBlockFalls(
        ?PlayerTrailPoint $previousTrailPoint,
        PlayerTrailPoint $currentTrailPoint,
        World $world,
        int $blockFallDelayTicks
    ): void {
        if (
            $previousTrailPoint === null
            || $previousTrailPoint->getWorldName() !== $currentTrailPoint->getWorldName()
        ) {
            $this->scheduleBlockFallAt(
                $world,
                $currentTrailPoint->getX(),
                $currentTrailPoint->getSupportY(),
                $currentTrailPoint->getZ(),
                $blockFallDelayTicks
            );

            return;
        }

        $deltaX = $currentTrailPoint->getX() - $previousTrailPoint->getX();
        $deltaZ = $currentTrailPoint->getZ() - $previousTrailPoint->getZ();
        $steps = max(1, (int) ceil(max(abs($deltaX), abs($deltaZ)) * self::TRAIL_SAMPLES_PER_BLOCK));
        $supportY = min($previousTrailPoint->getSupportY(), $currentTrailPoint->getSupportY());

        for ($step = 0; $step <= $steps; ++$step) {
            $progress = $step / $steps;

            $this->scheduleBlockFallAt(
                $world,
                $previousTrailPoint->getX() + ($deltaX * $progress),
                $supportY,
                $previousTrailPoint->getZ() + ($deltaZ * $progress),
                $blockFallDelayTicks
            );
        }
    }

    private function scheduleBlockFallAt(
        World $world,
        float $sampleX,
        int $y,
        float $sampleZ,
        int $blockFallDelayTicks
    ): void {
        $supportBlock = $this->resolveSupportBlock($world, $sampleX, $y, $sampleZ);
        if ($supportBlock === null) {
            return;
        }

        $this->scheduleBlockFall($supportBlock, $blockFallDelayTicks);
    }

    private function scheduleBlockFall(Block $block, int $blockFallDelayTicks): void {
        if ($block->getCollisionBoxes() === []) {
            return;
        }

        $scheduledBlockFall = ScheduledBlockFall::fromBlock($block, $blockFallDelayTicks);
        $this->scheduledBlockFalls[$scheduledBlockFall->getKey()] ??= $scheduledBlockFall;
    }

    private function resolveSupportBlock(World $world, float $sampleX, int $y, float $sampleZ): ?Block {
        $baseX = (int) floor($sampleX);
        $baseZ = (int) floor($sampleZ);
        $closestBlock = null;
        $closestDistanceSquared = null;

        for ($x = $baseX - self::SUPPORT_SEARCH_RADIUS; $x <= $baseX + self::SUPPORT_SEARCH_RADIUS; ++$x) {
            for ($z = $baseZ - self::SUPPORT_SEARCH_RADIUS; $z <= $baseZ + self::SUPPORT_SEARCH_RADIUS; ++$z) {
                $candidateBlock = $world->getBlockAt($x, $y, $z);
                if ($candidateBlock->getCollisionBoxes() === []) {
                    continue;
                }

                $distanceSquared = (($x + 0.5) - $sampleX) ** 2 + (($z + 0.5) - $sampleZ) ** 2;
                if ($closestDistanceSquared !== null && $distanceSquared >= $closestDistanceSquared) {
                    continue;
                }

                $closestBlock = $candidateBlock;
                $closestDistanceSquared = $distanceSquared;
            }
        }

        return $closestBlock;
    }
}
