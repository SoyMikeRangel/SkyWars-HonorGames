<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Tasks;
use MikeRangel\{Loader};
use MikeRangel\SkyWars\{SkyWars, ResetMap, PluginUtils, Arena\Arena, Extensions\FireworksTask};
use MikeRangel\Core\{Lobby\Device, Proxy\Proxy};
use pocketmine\{Server, Player, level\Level, item\Item, level\Position, entity\Skin, math\Vector3, scheduler\Task, utils\TextFormat as Color};
use pocketmine\network\mcpe\protocol\{ActorEventPacket, LevelEventPacket, LevelSoundEventPacket, ChangeDimensionPacket, PlayStatusPacket, types\DimensionIds};

class GameScheduler extends Task {

    public function onRun(int $currentTick) : void {
        if (count(Arena::getArenas()) > 0) {
            foreach (Arena::getArenas() as $arena) {
                $database = SkyWars::getDatabase()->getArenas();
                $arenas = Server::getInstance()->getLevelByName(Arena::getName($arena));
                $timelobby = Arena::getTimeWaiting($arena);
                $timestarting = Arena::getTimeStarting($arena);
                $timegame = Arena::getTimeGame($arena);
                $timerefill = Arena::getTimeRefill($arena);
                $timeend = Arena::getTimeEnd($arena);
                if ($arenas instanceof Level) {
                    if ($database->getStatus($arena) == 'waiting') {
                        $arenas->setTime(20000);
                        $arenas->stopTime();
                        foreach ($arenas->getPlayers() as $player) {
                            SkyWars::getBossbar()->showTo($player, 'Complete...');
                            $player->setNameTag(Color::DARK_GRAY . '[' . Color::GRAY . $player->getID() . Color::DARK_GRAY . ']' . ' ' . Color::GRAY . $player->getName());
                            $player->setScoreTag(Device::getDevice($player));
                            SkyWars::$data['damager'][$player->getName()] = 'string';
                        }
                        if (count(Arena::getPlayers($arena)) < 2) {
                            foreach ($arenas->getPlayers() as $player) {
                                SkyWars::getReloadArena($arena);
                                SkyWars::getBossbar()->updateFor($player, Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::RED . 'Waiting for players' . Color::BOLD . Color::GREEN . ' «' . Color::RESET, 0);
                            }
                        } else {
                            $timelobby--;
                            Arena::setTimeWaiting($arena, $timelobby);
                            foreach ($arenas->getPlayers() as $player) {
                                SkyWars::getBossbar()->updateFor($player, Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::GRAY . 'Starting game in ' . Color::GREEN . PluginUtils::getTimeParty($timelobby) . Color::GRAY . ' seconds' . Color::BOLD . Color::GREEN . ' «' . Color::RESET . "\n\n" . Color::GRAY . '            ' . 'OP [' . Color::GREEN . count(SkyWars::$data['vote'][Arena::getName($arena)]['op']) . Color::GRAY . ']' . ' - ' . 'Basic [' . Color::GREEN . count(SkyWars::$data['vote'][Arena::getName($arena)]['normal']) . Color::GRAY . ']', 100);
                                if (count(Arena::getPlayers($arena)) == Arena::getSpawns($arena)) {
                                    $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::YELLOW . ' The arena has reached its maximum capacity, starting the game.');
                                    $database->setStatus($arena, 'starting');
                                    $player->getInventory()->clearAll();
                                    $player->getArmorInventory()->clearAll();
                                }
                                #animations.
                                if ($timelobby >= 1 && $timelobby <= 10) {
                                    $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_NOTE, $timestarting);
                                }
                                if ($timelobby == 0) {
                                    $database->setStatus($arena, 'starting');
                                    $player->getInventory()->clearAll();
                                    $player->getArmorInventory()->clearAll();
                                }
                            }
                        }
                    } else if ($database->getStatus($arena) == 'starting') {
                        $alive = 0;
                        $timestarting--;
                        Arena::setTimeStarting($arena, $timestarting);
                        foreach ($arenas->getPlayers() as $player) {
                            $alive++;
                            if ($timestarting == 0) {
                                $lobby = SkyWars::getConfigs('Arenas/' . $arena);
                                $spawn = $lobby->get('slot-' . $alive);
                                $spawns = new Position($spawn[0], $spawn[1], $spawn[2], $arenas);
                                $arenas->loadChunk($spawns->getFloorX(), $spawns->getFloorZ());
                                $player->teleport($spawns);
                                $player->getInventory()->clearAll();
                                $player->getArmorInventory()->clearAll();
                                $player->getInventory()->addItem(Item::get(274, 0, 1));
                                $database->setStatus($arena, 'ingame');
                                SkyWars::$data['damager'][$player->getName()] = 'string';
                                $kits = SkyWars::getConfigs('kits');
                                PluginUtils::getKits($player, $kits->get($player->getName()));
                                PluginUtils::playSound($player, 'conduit.activate', 1, 1);
                                $player->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);
                                if (count(SkyWars::$data['vote'][Arena::getName($arena)]['op']) > count(SkyWars::$data['vote'][Arena::getName($arena)]['normal'])) {
                                    $player->addTitle(Color::GREEN . Color::BOLD . 'Starting', Color::GRAY . 'Mode: OP');
                                    PluginUtils::chestOP(Arena::getName($arena));
                                } else {
                                    $player->addTitle(Color::GREEN . Color::BOLD . 'Starting', Color::GRAY . 'Mode: Basic');
                                    PluginUtils::chestDefault(Arena::getName($arena));
                                }
                                $player->setGamemode(0);
                            }
                        }
                    } else if ($database->getStatus($arena) == 'ingame') {
                        $arenas->setTime(0);
                        $arenas->stopTime();
                        $timegame--;
                        $timerefill--;
                        Arena::setTimeGame($arena, $timegame);
                        Arena::setTimeRefill($arena, $timerefill);
                        foreach ($arenas->getPlayers() as $player) {
                            $player->setNameTag(Color::DARK_GRAY . '[' . Color::GRAY . $player->getID() . Color::DARK_GRAY . ']' . ' ' . Color::GRAY . $player->getName());
                            $player->setScoreTag(PluginUtils::viewHealth($player) . "\n" . Device::getDevice($player));
                            SkyWars::getBossbar()->updateFor($player, '   ' . Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::GRAY . 'The game ends in ' . Color::GREEN . PluginUtils::getTimeParty($timegame) . Color::GRAY . ' seconds' . Color::BOLD . Color::GREEN . ' «' . Color::RESET . "\n\n" . Color::GRAY . 'Alive: ' . Color::GREEN . count(Arena::getPlayers($arena)) . '/' . Arena::getSpawns($arena) . '   ' . Color::GRAY . 'Spectators: ' . Color::GREEN . count(Arena::getSpecters($arena)) . '   ' . Color::GRAY . 'Refill in: ' . Color::GREEN . PluginUtils::getTimeParty($timerefill) . Color::RESET, 100);
                            if ($timerefill == 0) {
                                if (count(SkyWars::$data['vote'][Arena::getName($arena)]['op']) > count(SkyWars::$data['vote'][Arena::getName($arena)]['normal'])) {
                                    $player->addTitle(Color::AQUA . Color::BOLD . 'Filled Chests', Color::GRAY . 'Mode: OP');
                                    PluginUtils::chestOP(Arena::getName($arena));
                                } else {
                                    $player->addTitle(Color::AQUA . Color::BOLD . 'Filled Chests', Color::GRAY . 'Mode: Basic');
                                    PluginUtils::chestDefault(Arena::getName($arena));
                                }
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_CHEST_OPEN);
                                Arena::setTimeRefill($arena, 180);
                            }
                            #animations.
                            if ($timegame == 599) {
                                if (count(SkyWars::$data['vote'][Arena::getName($arena)]['op']) > count(SkyWars::$data['vote'][Arena::getName($arena)]['normal'])) {
                                    PluginUtils::chestOP(Arena::getName($arena));
                                } else {
                                    PluginUtils::chestDefault(Arena::getName($arena));
                                }
                                $player->sendMessage(Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::GRAY . 'Activating blows and damage in the game in: ' . Color::GREEN . '10');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 594) {
                                $player->sendMessage(Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::GRAY . 'Activating blows and damage in the game in: ' . Color::GREEN . '5');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 592) {
                                $player->sendMessage(Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::GRAY . 'Activating blows and damage in the game in: ' . Color::GREEN . '3');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 591) {
                                $player->sendMessage(Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::GRAY . 'Activating blows and damage in the game in: ' . Color::GREEN . '2');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            } else if ($timegame == 590) {
                                $player->sendMessage(Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::GRAY . 'Activating blows and damage in the game in: ' . Color::GREEN . '1');
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_POP);
                            }
                            #game.
                            if ($timegame == 589) {
                                $player->addTitle(Color::GREEN . Color::BOLD . 'The Battle is Enabled', Color::GRAY . '@MikeRangelMR');
                                $this->pvp = 11;
                                $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BUBBLE_UP);
                            } else if ($timegame == 0) {
                                $database->setStatus($arena, 'end');
                            }
                            if (count(Arena::getPlayers($arena)) == 1) {
                                $database->setStatus($arena, 'end');
                                if ($player->getGamemode() == 0) {
                                    $user = SkyWars::getDatabase()->getUser();
                                    if ($user->inDatabase($player)) {
                                        $user->addWins($player, 1);
                                        $user->addCoins($player, 3);
                                    }
                                    $arenas->setTime(20000);
                                    $arenas->stopTime();
                                    $player->addTitle(Color::GREEN . Color::BOLD . 'Congratulations', Color::GRAY . '@HonorGames_');
                                    $player->setGamemode(0);
                                    //$player->getInventory()->clearAll();
                                    $player->getArmorInventory()->clearAll();
                                    SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new FireworksTask($player), 10);
                                } else if ($player->getGamemode() == 3) {
                                    $player->addTitle(Color::GREEN . Color::BOLD . 'You Loser', Color::GRAY . '@HonorGames_');
                                    $player->getInventory()->clearAll();
                                    $player->getArmorInventory()->clearAll();
                                }
                            }
                        }
                    } else if ($database->getStatus($arena) == 'end') {
                        $timeend--;
                        Arena::setTimeEnd($arena, $timeend);
                        foreach ($arenas->getPlayers() as $player) {
                            $player->setNameTag(Color::DARK_GRAY . '[' . Color::GRAY . $player->getID() . Color::DARK_GRAY . ']' . ' ' . Color::GRAY . $player->getName());
                            $player->setScoreTag(Device::getDevice($player));
                            SkyWars::getBossbar()->updateFor($player, Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::GRAY . 'Reseting game in ' . Color::GREEN . $timeend . Color::GRAY . ' seconds' . Color::BOLD . Color::GREEN . ' «' . Color::RESET, 100);
                        }
                        if ($timeend == 4) {
                            foreach ($arenas->getPlayers() as $player) {
                                $player->getInventory()->clearAll();
                                $player->sendMessage(Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::YELLOW . 'The search for a new game will begin, cancel the wait using the remaining item of the leave to continue watching.');
                                if (!in_array($player->getName(), SkyWars::$data['queue'])) {
                                    SkyWars::$data['queue'][] = $player->getName();
                                    SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
                                }
                            }
                        } else if ($timeend == 3) {
                            foreach ($arenas->getPlayers() as $player) {
                                $player->sendPopup(Color::GREEN . 'Reseting game in 3 seconds.');
                            }
                        } else if ($timeend == 2) {
                            foreach ($arenas->getPlayers() as $player) {
                                $player->sendPopup(Color::YELLOW . 'Reseting game in 2 seconds.');
                            }
                        } else if ($timeend == 1) {
                            foreach ($arenas->getPlayers() as $player) {
                                $player->sendPopup(Color::RED . 'Reseting game in 1 second.');
                            }
                        } else if ($timeend == 0) {
                            ResetMap::resetZip(Arena::getName($arena));
                            SkyWars::getReloadArena($arena);
                            foreach ($arenas->getPlayers() as $player) {
                                $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
                                $player->getInventory()->clearAll();
                                $player->getArmorInventory()->clearAll();
                                $player->setAllowFlight(false);
                                $player->setFlying(false);
                                $player->removeAllEffects();
                                $player->setGamemode(2);
                                $player->setHealth(20);
                                $player->setFood(20);   
                                Proxy::transfer($player, 'lobby');
                            }
                        }
                    }
                }
            }
        }
    }
}
?>