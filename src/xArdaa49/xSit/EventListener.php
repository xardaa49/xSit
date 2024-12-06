<?php
/***
 *                 .oooooo..o  o8o      .
 *                d8P'    `Y8  `"'    .o8
 *    oooo    ooo Y88bo.      oooo  .o888oo
 *     `88b..8P'   `"Y8888o.  `888    888
 *       Y888'         `"Y88b  888    888
 *     .o8"'88b   oo     .d8P  888    888 .
 *    o88'   888o 8""88888P'  o888o   "888"
 *
 *
 *    @name xSit
 *    @author xArdaa49
 *    @version 1.0.0
 */
namespace xArdaa49\xSit;

use pocketmine\{block\Opaque,
    block\Slab,
    block\Stair,
    event\block\BlockBreakEvent,
    event\entity\EntityTeleportEvent,
    event\Listener,
    event\player\PlayerDeathEvent,
    event\player\PlayerInteractEvent,
    event\player\PlayerJoinEvent,
    event\player\PlayerMoveEvent,
    event\player\PlayerQuitEvent,
    event\server\DataPacketReceiveEvent,
    network\mcpe\protocol\InteractPacket,
    player\Player,
    scheduler\ClosureTask};

class EventListener implements Listener{
    /**
     * @var xSit $pluginSit
     */
    private xSit $pluginSit;

    /**
     * @param xSit $pluginSit
     */
    public function __construct(xSit $pluginSit) {
        $this->pluginSit = $pluginSit;
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        $this->pluginSit->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($event): void {
            foreach ($this->pluginSit->sitPlayers as $playerName => $data) {
                $sittingPlayer = $this->pluginSit->getServer()->getPlayerExact($playerName);

                if ($sittingPlayer == null) return;

                $block = $sittingPlayer->getWorld()->getBlock($sittingPlayer->getPosition()->add(0, -0.3, 0));

                $this->pluginSit->sitPlayer($sittingPlayer, $block);
            }
        }), 31);
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        if ($this->pluginSit->isPlayerSiting($player)) $this->pluginSit->removeSitPlayer($player);
    }

    /**
     * @param PlayerDeathEvent $event
     * @return void
     */
    public function onDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();

        if ($this->pluginSit->isPlayerSiting($player)) $this->pluginSit->removeSitPlayer($player);
    }

    /**
     * @param EntityTeleportEvent $event
     * @return void
     */
    public function onTeleport(EntityTeleportEvent $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof Player) return;

        if ($this->pluginSit->isPlayerSiting($entity)) $this->pluginSit->removeSitPlayer($entity);
    }

    /**
     * @param PlayerMoveEvent $event
     * @return void
     */
    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();

        if ($this->pluginSit->isPlayerSiting($player)) {
            $this->pluginSit->optimizeRotation($player);
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBreak(blockbreakevent $event): void
    {
        $block = $event->getBlock();

        if ($block instanceof Stair or $block instanceof Slab) {
            $pos = $block->getPosition()->add(0.5, 1.5, 0.5);
        } elseif ($block instanceof Opaque) {
            $pos = $block->getPosition()->add(0.5, 2.1, 0.5);
        } else {
            return;
        }

        foreach ($this->pluginSit->sitPlayers as $playerName => $data) {
            if ($pos->equals($data["position"])) {
                $sittingPlayer = $this->pluginSit->getServer()->getPlayerExact($playerName);

                if ($sittingPlayer !== null) {
                    $this->pluginSit->removeSitPlayer($sittingPlayer);
                }
            }
        }
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @return void
     */
    public function onData(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();

        if ($player == null) return;

        if ($packet instanceof InteractPacket and $packet->action === InteractPacket::ACTION_LEAVE_VEHICLE && $this->pluginSit->isPlayerSiting($player)) {
            $this->pluginSit->removeSitPlayer($player);
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @return void
     */
    public function onInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($block instanceof Stair or $block instanceof Slab) {
            // $this->pluginSit->sitPlayer($player, $block); Tıklamayla oturmayı aktif eder.
        }
    }
}

