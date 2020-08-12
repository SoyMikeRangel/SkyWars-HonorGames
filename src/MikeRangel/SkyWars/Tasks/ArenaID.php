<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE
*/
namespace MikeRangel\SkyWars\Tasks;
use MikeRangel\SkyWars\{SkyWars, PluginUtils, Arena\Arena};
use MikeRangel\Core\{Proxy\Proxy};
use pocketmine\{Server, Player, scheduler\Task, math\Vector3, item\Item, utils\TextFormat as Color};
use pocketmine\network\mcpe\protocol\{AddActorPacket, PlaySoundPacket, LevelSoundEventPacket, StopSoundPacket};
use pocketmine\level\sound\{EndermanTeleportSound};

class ArenaID extends Task {
    public $time = 1;
    public $player;

    public function __construct(Player $player) {
        $this->player = $player;
    }

    public function onRun(int $currentTick) : void {
        $player = $this->player;
        if (in_array($player->getName(), SkyWars::$data['queue'])) {
            $this->time--;
            if ($this->time == 0) {
                $database = SkyWars::getDatabase()->getArenas();
                $index = array_search($player->getName(), SkyWars::$data['queue']);
		        if ($index != -1) {
			        unset(SkyWars::$data['queue'][$index]);
                }
                if ($database->getArenas() == 0) {
                    $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::RED . ' No arenas available for now,' . Color::RED . ' try again.');
                    Proxy::transfer($player, 'lobby');
                } else {
                    $arenas = ($database->getArenas() + 1);
                    if ($database->getID() >= $arenas) {
                        $id = ($database->getID() + 1);
                        $database->setID($id);
                        $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::YELLOW . ' New found arena, you will be transferred.');
                        if (!in_array($player->getName(), SkyWars::$data['queue'])) {
                            SkyWars::$data['queue'][] = $player->getName();
                            SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new NewID($player), 10);
                        }
                    } else {
                        if ($database->getArenas() > 0) {
                            if ($database->getStatus('SW-' . $database->getID()) == 'waiting') {
                                Proxy::transfer($player, 'SW-' . $database->getID())
                                PluginUtils::joinSolo($player, 'SW-' . $database->getID());
                            } else {
                                $id = ($database->getID() + 1);
                                $database->setID($id);
                                $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::YELLOW . ' New found arena, you will be transferred.');
                                if (!in_array($player->getName(), SkyWars::$data['queue'])) {
                                    SkyWars::$data['queue'][] = $player->getName();
                                    SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new NewID($player), 10);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            SkyWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}

class NewGame extends Task {
    public $time = 7;
    public $player;

    public function __construct(Player $player) {
        $this->player = $player;
    }

    public function onRun(int $currentTick) : void {
        $player = $this->player;
        if (in_array($player->getName(), SkyWars::$data['queue'])) {
            $this->time--;
            if ($this->time == 0) {
                SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
            }
        } else {
            SkyWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}


class NewID extends Task {
    public $time = 1;
    public $player;

    public function __construct(Player $player) {
        $this->player = $player;
    }

    public function onRun(int $currentTick) : void {
        $player = $this->player;
        if (in_array($player->getName(), SkyWars::$data['queue'])) {
            $this->time--;
            if ($this->time == 0) {
                SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
            }
        } else {
            SkyWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}
?>