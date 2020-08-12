<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Events;
use MikeRangel\Core\{Proxy\Proxy};
use MikeRangel\SkyWars\{SkyWars, PluginUtils, Form\FormManager, Tasks\ArenaID, Arena\Arena, Entity\types\EntityHuman, Entity\types\EntityStats};
use pocketmine\{Server, Player, event\Listener, math\Vector3, item\Item, utils\TextFormat as Color};
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use pocketmine\event\{inventory\InventoryPickupItemEvent, player\PlayerPreLoginEvent, player\PlayerChatEvent, player\PlayerCommandPreprocessEvent, player\PlayerQuitEvent, player\PlayerDropItemEvent, player\PlayerMoveEvent, player\PlayerItemHeldEvent, player\PlayerInteractEvent, player\PlayerExhaustEvent, block\BlockBreakEvent, block\BlockPlaceEvent, entity\EntityLevelChangeEvent, entity\EntityDamageEvent, entity\EntityDamageByChildEntityEvent, entity\EntityDamageByEntityEvent};
use pocketmine\level\sound\{BlazeShootSound};

class GlobalEvents implements Listener {

    public function onChat(PlayerChatEvent $event) {
        $database = SkyWars::getDatabase()->getArenas();
        $player = $event->getPlayer();
        $args = explode(' ',$event->getMessage());
        if (in_array($player->getName(), SkyWars::$data['configurator'])) {
            $event->setCancelled(true);
            switch ($args[0]) {
                case 'help':
                    default:
                    $date = [
                        'help: Help commands.',
                        'setlobby: Register the lobby.',
                        'setlobbysp: Register the lobby specters.',
                        'setspawn <slot>: Set spawns.',
                        'done: Enable arena.'
                    ];
                    $player->sendMessage(Color::GOLD . 'SkyWars Configuration Commands:');
                    foreach ($date as $help) {
                        $player->sendMessage(Color::GRAY . $help);
                    }
                break;
                case 'setlobby':
                    Arena::setLobbyWaiting($player);
                break;
                case 'setlobbysp':
                    Arena::setLobbySpecters($player);
                break;
                case 'setspawn':
                    if (!empty($args[1])) {
                        Arena::setSpawns($player, $args[1]);
                    } else {
                        $player->sendMessage(Color::RED . 'Usage: setspawn <slot>');
                    }
                break;
                case 'done':
                    $database->setStatus('SW-' . SkyWars::$data['id'][$player->getName()], 'waiting');
                    SkyWars::$data['id'][$player->getName()] = '';
                    $index = array_search($player->getName(), SkyWars::$data['configurator']);
		            if  ($index != -1)  {
			            unset(SkyWars::$data['configurator'][$index]);
                    }
                    $player->sendMessage(Color::GREEN . 'Installation mode has been completed, arena created.');
                    $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
                    $player->setGamemode(2);
                break;
            }
        }
    }

    public function onCommands(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
        $cmd = explode(' ', strtolower($event->getMessage()));
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($cmd[0] === '/gamemode') {
                    $event->setCancelled(true);
                } else if ($cmd[0] === '/gm') {
                    $event->setCancelled(true);
                } else if ($cmd[0] === '/fly') {
                    $event->setCancelled(true);
                } else if ($cmd[0] === '/tp') {
                    $event->setCancelled(true);
                } else if ($cmd[0] === '/kick') {
                    $event->setCancelled(true);
                } else if ($cmd[0] === '/stop') {
                    $event->setCancelled(true);
                } else if ($cmd[0] === '/kill') {
                    $event->setCancelled(true);
                } else if ($cmd[0] === '/give') {
                    $event->setCancelled(true);
                }
            }
        }
    }
                   
    public function setFunctions(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $id = $event->getItem()->getId();
        $damage = $event->getItem()->getDamage();
        $name = $event->getItem()->getCustomName();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($player->getGamemode() != 1) {
                    if ($id == 261 && $name == Color::BOLD . Color::GREEN . "KITS\n§r§fClick to select") {
                        FormManager::getKitsUI($player);
                    } else if ($id == 54 && $name == Color::BOLD . Color::GREEN . "VOTE CHEST\n§r§fClick to select") {
                        FormManager::getVotesUI($player);
                    } else if ($id == 355 && $damage == 14 && $name == Color::BOLD . Color::RED . "LEAVE\n§r§fClick to select") {
                        $index = array_search($player->getName(), SkyWars::$data['queue']);
		                if ($index != -1) {
                            unset(SkyWars::$data['queue'][$index]);
                        }
                        Proxy::transfer($player, 'lobby');
                    } else if ($id == 331 && $name == Color::BOLD . Color::RED . "LEAVE QUEUE\n§r§fClick to select") {
                        $index = array_search($player->getName(), SkyWars::$data['queue']);
		                if ($index != -1) {
                            unset(SkyWars::$data['queue'][$index]);
                        }
                        $player->sendMessage(Color::BOLD . Color::GREEN . '» ' . Color::RESET . Color::YELLOW . 'The browser for a new game has been canceled.');
                        $player->getInventory()->clearAll();
                        $player->getInventory()->setItem(0, Item::get(381, 0, 1)->setCustomName(Color::BOLD . Color::GREEN . "PLAYERS REAMING\n§r§fClick to select"));
                        $player->getInventory()->setItem(4, Item::get(120, 0, 1)->setCustomName(Color::BOLD . Color::GREEN . "RANDOM GAME\n§r§fClick to select"));
                        $player->getInventory()->setItem(8, Item::get(355, 14, 1)->setCustomName(Color::BOLD . Color::RED . "LEAVE\n§r§fClick to select"));
                    }
                }
            }
        }
    }

    public function onHeld(PlayerItemHeldEvent $event) {
    	$player = $event->getPlayer();
        $item = $event->getItem()->getCustomName();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($player->getGamemode() == 3) {
                    if ($item == Color::BOLD . Color::GREEN . "PLAYERS REAMING\n§r§fClick to select") {
                        FormManager::getPlayersUI($player);
                    } else if ($item == Color::BOLD . Color::GREEN . "RANDOM GAME\n§r§fClick to select") {
                        if (!in_array($player->getName(), SkyWars::$data['queue'])) {
                            SkyWars::$data['queue'][] = $player->getName();
                            SkyWars::getInstance()->getScheduler()->scheduleRepeatingTask(new ArenaID($player), 10);
                        }
                    } else if ($item == Color::BOLD . Color::RED . "LEAVE\n§r§fClick to select") {
                        $index = array_search($player->getName(), SkyWars::$data['queue']);
		                if ($index != -1) {
                            unset(SkyWars::$data['queue'][$index]);
                        }
                        Proxy::transfer($player, 'lobby');
                    }
                }
            }
        }
    }

    public function onMove(PlayerMoveEvent $event) {
        $database = SkyWars::getDatabase()->getArenas();
        $player = $event->getPlayer();
        foreach (Arena::getArenas() as $arena) {   
            $config = SkyWars::getConfigs('Arenas/' . $arena);
            $lobby = $config->get('lobby');
            $lobbys = $config->get('lobbyspecters');
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($database->getStatus($arena) == 'waiting') {
                    if ($player->getY() < 3) {
                        $player->teleport(new Vector3($lobby[0], $lobby[1], $lobby[2]));
                    }
                } else if ($database->getStatus($arena) == 'end') {
                    if ($player->getY() < 3) {
                        $player->teleport(new Vector3($lobbys[0], $lobbys[1], $lobbys[2]));
                    }
                }
            }
        }
    }

    public function onProtect(EntityDamageEvent $event) {
        $database = SkyWars::getDatabase()->getArenas();
        $player = $event->getEntity();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($event instanceof EntityDamageEvent && ($event->getEntity() instanceof Player)) {
                    if ($database->getStatus($arena) == 'waiting' || 
                        $database->getStatus($arena) == 'starting' ||
                        $database->getStatus($arena) == 'end'
                    ) {
                        $event->setCancelled(true);
                    } else {
                        if ($event instanceof EntityDamageByEntityEvent) {
                            $damager = $event->getDamager();
                            if ($damager instanceof Player) {
                                SkyWars::$data['damager'][$player->getName()] = $damager->getName();
                            }
                        }
                        if ($event instanceof EntityDamageByChildEntityEvent) {
                            $damager = $event->getDamager();
                            if ($damager instanceof Player) {
                                SkyWars::$data['damager'][$player->getName()] = $damager->getName();
                            }
                        }
                        if (Arena::getTimeGame($arena) >= 589 && Arena::getTimeGame($arena) <= 600) {
                            $event->setCancelled(true);
                        }
                        if ($player->getGamemode() == 3) {
                            $event->setCancelled(true);
                        }
                    }
                }
            }
        }
    }

    public function onDrop(PlayerDropItemEvent $event) {
        $database = SkyWars::getDatabase()->getArenas();
        $player = $event->getPlayer();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($database->getStatus($arena) == 'waiting' || 
                    $database->getStatus($arena) == 'starting' ||
                    $database->getStatus($arena) == 'end'
                ) {
                    $event->setCancelled(true);
                } else {
                    if ($player->getGamemode() == 3) {
                        $event->setCancelled(true);
                    } else {
                        $event->setCancelled(false);
                    }
                }
            }
        }
    }

    public function onBlock(BlockBreakEvent $event) {
        $database = SkyWars::getDatabase()->getArenas();
        $player = $event->getPlayer();
        $block = $event->getBlock();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($database->getStatus($arena) == 'waiting' || 
                    $database->getStatus($arena) == 'starting' ||
                    $database->getStatus($arena) == 'end'
                ) {
                    $event->setCancelled(true);
                } else {
                    if ($player->getGamemode() == 3) {
                        $event->setCancelled(true);
                    }
                    if (count(SkyWars::$data['vote'][Arena::getName($arena)]['op']) > count(SkyWars::$data['vote'][Arena::getName($arena)]['normal'])) {
                        $protection = Enchantment::getEnchantment(Enchantment::PROTECTION);
                        $punch = Enchantment::getEnchantment(Enchantment::PUNCH);
                        if ($block->getID() == 56) {
                            $item = Item::get(310, 0, 1);
                            $item->addEnchantment(new EnchantmentInstance($protection));
                            $item1 = Item::get(311, 0, 1);
                            $item1->addEnchantment(new EnchantmentInstance($protection));
                            $item2 = Item::get(312, 0, 1);
                            $item2->addEnchantment(new EnchantmentInstance($protection));
                            $item3 = Item::get(313, 0, 1);
                            $item3->addEnchantment(new EnchantmentInstance($protection));
                            $item4 = Item::get(276, 0, 1);
                            $item4->addEnchantment(new EnchantmentInstance($punch));
                            $array = [$item, $item1, $item2, $item3, $item4];
                            Server::getInstance()->getLevelByName(Arena::getName($arena))->dropItem(new Vector3($block->getX(), $block->getY(), $block->getZ()), $array[array_rand($array)]);
                        } else if ($block->getID() == 14) {
                            $item = Item::get(314, 0, 1);
                            $item->addEnchantment(new EnchantmentInstance($protection));
                            $item1 = Item::get(315, 0, 1);
                            $item1->addEnchantment(new EnchantmentInstance($protection));
                            $item2 = Item::get(316, 0, 1);
                            $item2->addEnchantment(new EnchantmentInstance($protection));
                            $item3 = Item::get(317, 0, 1);
                            $item3->addEnchantment(new EnchantmentInstance($protection));
                            $item4 = Item::get(286, 0, 1);
                            $item4->addEnchantment(new EnchantmentInstance($punch));
                            $array = [$item, $item1, $item2, $item3, $item4];
                            Server::getInstance()->getLevelByName(Arena::getName($arena))->dropItem(new Vector3($block->getX(), $block->getY(), $block->getZ()), $array[array_rand($array)]);
                        } else if ($block->getID() == 15){
                            $item = Item::get(306, 0, 1);
                            $item->addEnchantment(new EnchantmentInstance($protection));
                            $item1 = Item::get(307, 0, 1);
                            $item1->addEnchantment(new EnchantmentInstance($protection));
                            $item2 = Item::get(308, 0, 1);
                            $item2->addEnchantment(new EnchantmentInstance($protection));
                            $item3 = Item::get(309, 0, 1);
                            $item3->addEnchantment(new EnchantmentInstance($protection));
                            $item4 = Item::get(257, 0, 1);
                            $item4->addEnchantment(new EnchantmentInstance($punch));
                            $array = [$item, $item1, $item2, $item3, $item4];
                            Server::getInstance()->getLevelByName(Arena::getName($arena))->dropItem(new Vector3($block->getX(), $block->getY(), $block->getZ()), $array[array_rand($array)]);
                        }
                    } else {
                        if ($block->getID() == 56) {
                            $array = [310, 311, 312, 313, 276];
                            Server::getInstance()->getLevelByName(Arena::getName($arena))->dropItem(new Vector3($block->getX(), $block->getY(), $block->getZ()), Item::get($array[array_rand($array)], 0, 1));
                        } else if ($block->getID() == 14) {
                            $array = [314, 315, 316, 317, 286];
                            Server::getInstance()->getLevelByName(Arena::getName($arena))->dropItem(new Vector3($block->getX(), $block->getY(), $block->getZ()), Item::get($array[array_rand($array)], 0, 1));
                        } else if ($block->getID() == 15){
                            $array = [306, 307, 308, 309, 257];
                            Server::getInstance()->getLevelByName(Arena::getName($arena))->dropItem(new Vector3($block->getX(), $block->getY(), $block->getZ()), Item::get($array[array_rand($array)], 0, 1));
                        }
                    }
                    $event->setCancelled(false);
                }
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event) {
        $database = SkyWars::getDatabase()->getArenas();
        $player = $event->getPlayer();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($database->getStatus($arena) == 'waiting' || 
                    $database->getStatus($arena) == 'starting'
                ) {
                    $event->setCancelled(true);
                } else {
                    $event->setCancelled(false);
                }
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event) {
        $database = SkyWars::getDatabase()->getArenas();
        $player = $event->getPlayer();
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($player->getGamemode() == 0) {
                    foreach ($player->getLevel()->getPlayers() as $players) {
                        if ($database->getStatus($arena) == 'waiting' || $database->getStatus($arena) == 'ingame') {
                            $remain = (count(Arena::getPlayers($arena)) - 1);
                            $players->sendMessage(Color::GREEN . Color::BOLD . '» ' . Color::RESET . Color::RED . $player->getName() . ' ' . 'Left the game.' . ' ' . Color::RED . '[' . Color::RED . $remain . Color::RED . '/' . Color::RED . Arena::getSpawns($arena) . Color::RED . ']');
                            if ($database->getStatus($arena) == 'ingame') {
                                if ($remain > 1) {
                                    $players->sendMessage(Color::RED . $remain . ' players remain alive.');
                                }
                            }
                        }
                    }
                }
                $index = array_search($player->getName(), SkyWars::$data['queue']);
		        if ($index != -1) {
			        unset(SkyWars::$data['queue'][$index]);
                }
                $player->getArmorInventory()->clearAll();
                $player->setAllowFlight(false);
                $player->setFlying(false);
                $player->removeAllEffects();
                $player->setGamemode(2);
                $player->setHealth(20);
                $player->setFood(20);
            }
        }
    }

    public function onHunger(PlayerExhaustEvent $event) {
        $database = SkyWars::getDatabase()->getArenas();
        $player = $event->getPlayer();
        if (!$player instanceof Player) return;            
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($database->getStatus($arena) == 'waiting' || 
                    $database->getStatus($arena) == 'starting' ||
                    $database->getStatus($arena) == 'end'
                ) {
                    $event->setCancelled(true);
                } else {
                    if ($player->getGamemode() == 3) {
                        $event->setCancelled(true);
                    } else {
                        $event->setCancelled(false);
                    }
                }
            }
        }
    }

    public function onDamage(EntityDamageEvent $event) {
        $database = SkyWars::getDatabase()->getArenas();
        $player = $event->getEntity();
        if (!$player instanceof Player) return;
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                if ($database->getStatus($arena) == 'ingame') {
                    switch ($event->getCause()) {
                        case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                            if ($event instanceof EntityDamageByEntityEvent) {
                                $damager = $event->getDamager();
                                if ($damager instanceof Player) {
                                    if ($event->getFinalDamage() >= $player->getHealth()) {
                                        $event->setCancelled(true);
                                        PluginUtils::getEventDamage($player, $arena, 'He has been killed by', $damager);
                                    }
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_PROJECTILE:
                            if ($event instanceof EntityDamageByEntityEvent) {
                                $damager = $event->getDamager();
                                if ($damager instanceof Player) {
                                    if ($event->getFinalDamage() >= $player->getHealth()) {
                                        $event->setCancelled(true);
                                        PluginUtils::getEventDamage($player, $arena, 'He has been killed with arrows by', $damager);
                                    }
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_FIRE:
                        case EntityDamageEvent::CAUSE_FIRE_TICK:
                        case EntityDamageEvent::CAUSE_LAVA:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->setCancelled(true);
                                $damage = null;
                                foreach ($player->getLevel()->getPlayers() as $players) {
                                    if (SkyWars::$data['damager'][$player->getName()] == $players->getName()) {
                                        $damage = $players->getName();
                                    }
                                }
                                if ($damage != null) {
                                    $damager = Server::getInstance()->getPlayer($damage);
                                    PluginUtils::getEventDamage($player, $arena, 'Has died burned by', $damager);
                                } else {
                                    PluginUtils::getEventDamage($player, $arena, 'Has died burned');
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
                        case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->setCancelled(true);
                                $damage = null;
                                foreach ($player->getLevel()->getPlayers() as $players) {
                                    if (SkyWars::$data['damager'][$player->getName()] == $players->getName()) {
                                        $damage = $players->getName();
                                    }
                                }
                                if ($damage != null) {
                                    $damager = Server::getInstance()->getPlayer($damage);
                                    PluginUtils::getEventDamage($player, $arena, 'It has exploded into a thousand pieces by', $damager);
                                } else {
                                    PluginUtils::getEventDamage($player, $arena, 'It has exploded into a thousand pieces');
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_FALL:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->setCancelled(true);
                                $damage = null;
                                foreach ($player->getLevel()->getPlayers() as $players) {
                                    if (SkyWars::$data['damager'][$player->getName()] == $players->getName()) {
                                        $damage = $players->getName();
                                    }
                                }
                                if ($damage != null) {
                                    $damager = Server::getInstance()->getPlayer($damage);
                                    PluginUtils::getEventDamage($player, $arena, 'He died from a strong blow to the floor by', $damager);
                                } else {
                                    PluginUtils::getEventDamage($player, $arena, 'He died from a strong blow to the floor');
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_VOID:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->setCancelled(true);
                                $damage = null;
                                foreach ($player->getLevel()->getPlayers() as $players) {
                                    if (SkyWars::$data['damager'][$player->getName()] == $players->getName()) {
                                        $damage = $players->getName();
                                    }
                                }
                                if ($damage != null) {
                                    $damager = Server::getInstance()->getPlayer($damage);
                                    PluginUtils::getEventDamage($player, $arena, 'Has fallen into the void by', $damager);
                                } else {
                                    PluginUtils::getEventDamage($player, $arena, 'Has fallen into the void');
                                }
                            }
                        break;
                        case EntityDamageEvent::CAUSE_MAGIC:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->setCancelled(true);
                                $damage = null;
                                foreach ($player->getLevel()->getPlayers() as $players) {
                                    if (SkyWars::$data['damager'][$player->getName()] == $players->getName()) {
                                        $damage = $players->getName();
                                    }
                                }
                                if ($damage != null) {
                                    $damager = Server::getInstance()->getPlayer($damage);
                                    PluginUtils::getEventDamage($player, $arena, 'Has died for potions by', $damager);
                                } else {
                                    PluginUtils::getEventDamage($player, $arena, 'Has died for potions');
                                }
                            }
                        break;
                        default:
                            if ($event->getFinalDamage() >= $player->getHealth()) {
                                $event->setCancelled(true);
                                PluginUtils::getEventDamage($player, $arena, 'Has died');
                            }
                        break;
                    }
                }
            }
        }
    }
}
?>