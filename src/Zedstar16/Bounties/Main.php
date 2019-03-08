<?php

declare(strict_types=1);

namespace Zedstar16\Bounties;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use onebone\economyapi\EconomyAPI;
use Zedstar16\Bounties\command\BountyCommand;

class Main extends PluginBase
{

    public $bounties;
    public $config;
    const Prefix = "§l§8(§bBounty§8)§r ";

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getLogger()->info("Hello World!");
        $this->getServer()->getCommandMap()->register("bounty", new BountyCommand($this));
        $this->bounties = new Config($this->getDataFolder() . "bounties.yml", Config::YAML);
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }

    /**
     * @param String $player
     * @param Int $amount
     */
    public function addBounty(String $player, Int $amount)
    {
        $currentamount = $this->bounties->get($player);
        if ($currentamount !== null) {
            $this->bounties->set($player, ($currentamount + $amount));
            $this->renderScoretag($this->getServer()->getPlayer($player));
        } else {
            $this->bounties->set($player, $amount);
        }
        $this->bounties->save();
    }

    /**
     * @param String $player
     */
    public function resetBounty(String $player)
    {
        $this->bounties->set($player, 0);
        $this->bounties->save();
        $p = $this->getServer()->getPlayer($player);
        if ($p !== null) {
            $this->renderScoretag($p);
        }
    }

    /**
     * @param String $player
     * @return mixed
     */
    public function getBounty(String $player)
    {
        $bounty = $this->bounties->get($player);
        if ($bounty !== 0) {
            return $bounty;
        } else return null;
    }

    /**
     * @param Player $player
     */
    public function renderScoretag(Player $player)
    {
        $name = $player->getName();
        $bounty = $this->config->get("bounty-tag");
        $bounty = str_replace("{bounty}", $this->getBounty($name), $this->config->get("bounty-tag"));
        if ($this->getBounty($name) > 0) {
            $player->setScoreTag($player->getScoreTag() . "\n" . $bounty);
        }

    }

    /**
     * @param Player $player
     * @param Player $killer
     */
    public function claimBounty(Player $player, Player $killer)
    {
        $bounty = $this->getBounty($player->getName());
        $bountyclaim = $this->config->get("bounty-claimed");
        $bountyclaim = str_replace("{reward}", $bounty, $bountyclaim);
        $bountyclaim = str_replace("{killer}", $killer->getName(), $bountyclaim);
        $bountyclaim = str_replace("{player}", $player->getName(), $bountyclaim);
        if ($this->getBounty($player->getName()) !== null) {
            EconomyAPI::getInstance()->addMoney($killer->getName(), (float)$bounty);
            $this->resetBounty($player->getName());
            $this->getServer()->broadcastMessage($bountyclaim);
        }
        $this->bounties->save();
    }
}
