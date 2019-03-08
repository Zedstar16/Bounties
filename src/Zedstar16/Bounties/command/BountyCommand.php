<?php

namespace Zedstar16\Bounties\command;

use Zedstar16\Bounties\Main;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use onebone\economyapi\EconomyAPI;

class BountyCommand extends PluginCommand
{

    public function __construct(Main $plugin)
    {
        parent::__construct("bounty", $plugin);
        $this->setDescription("Bounties");
        $this->setPermission("bounty");
    }

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        $plugin = $this->getPlugin();
        if (count($args) < 1) {
            $sender->sendMessage(Main::Prefix . "Usage: /bounty <get/me/add/top/about>");
            return false;
        }
        switch ($args[0]) {
            case "get":
                if (isset($args[1])) {
                    $bounty = $plugin->getBounty($args[1]);
                    if ($bounty !== null) {
                        $sender->sendMessage(Main::Prefix . "$args[1]'s current bounty is: $bounty");
                    } else $sender->sendMessage(Main::Prefix . "$args[1] does not have a bounty on them");
                } else $sender->sendMessage(Main::Prefix . "Please provide a player to get a bounty for");
                break;
            case "me":
                $bounty = $plugin->getBounty($sender->getName());
                if ($bounty !== null) {
                    $sender->sendMessage(Main::Prefix . "Your current bounty is: $bounty");
                } else $sender->sendMessage(Main::Prefix . "You do not have a bounty on you");
                break;
            case "about":
                $sender->sendMessage(Main::Prefix . "§aPlugin by §dZedstar16");
                break;
            case "add":
                if (count($args) < 3) {
                    $subject = $sender->getServer()->getPlayer($args[1]);
                    $sendername = $sender->getName();
                    $sendermoney = EconomyAPI::getInstance()->myMoney($sendername);
                    if (is_numeric($args[2])) {
                        $bounty = (int)$args[2];
                        if ($sendermoney < $bounty) {
                            $sender->sendMessage(Main::Prefix . "You do not have enough money to add this bounty");
                            return false;
                        }
                        if ($subject !== null) {
                            EconomyAPI::getInstance()->reduceMoney("$sendername", $bounty);
                            $subjectname = $subject->getName();
                            $plugin->addBounty($subjectname, $bounty);
                            foreach ($sender->getServer()->getOnlinePlayers() as $p) {
                                if ($p->getName() !== $subjectname) {
                                    $p->sendMessage(Main::Prefix . "$sendername added a bounty of $bounty to $subjectname");
                                }
                            }
                            $subject->sendMessage(Main::Prefix . "$sendername added a bounty of $bounty on you!");
                        } else {
                            if ($plugin->getBounty($args[1] !== false)) {
                                $sender->getServer()->broadcastMessage(Main::Prefix . "$sendername added a bounty of $bounty to $args[1]");
                            } else $sender->sendMessage(Main::Prefix . "Player not found in database");

                        }
                    } else $sender->sendMessage(Main::Prefix . "Amount to set must be a number");
                } else $sender->sendMessage(Main::Prefix . "Usage: /bounty add (playername) (amount)");
                break;
            case "top":
                $config = $plugin->bounties->getAll();
                $sender->sendMessage("§bTop 10 Bounties");
                arsort($config, SORT_NUMERIC);
                $i = 1;
                foreach ($config as $key => $val) {
                    if ($i <= 10) {
                        $bounty = $plugin->getBounty($key);
                        $sender->sendMessage("§l§4" . (string)$i . " §r§a$key: §r§b$$bounty");
                        $i++;
                    }
                }
                break;

        }
        return true;
    }

}