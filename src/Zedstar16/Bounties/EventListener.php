<?php

namespace Zedstar16\Bounties;


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Position;
use pocketmine\Player;
use onebone\economyapi\EconomyAPI;
use pocketmine\Server;

class EventListener implements Listener
{

    private $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $pn = $event->getPlayer()->getName();
        if ($this->plugin->getBounty($pn) == null) $this->plugin->addBounty($pn, 0);
        $this->plugin->renderScoretag($event->getPlayer());
    }

    public function onDeath(PlayerDeathEvent $event)
    {

        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();
        if (!$cause instanceof EntityDamageByEntityEvent) {
            return;
        }
        $damager = $cause->getDamager();
        if (!$damager instanceof Player) {
            return;
        }
        if ($damager->getName() === $player->getName()) {
            return;
        }
        $this->plugin->claimBounty($player, $damager);
    }

}