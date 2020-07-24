<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars;
use MikeRangel\SkyWars\{Database\MySQL, Arena\Arena, Bossbar\Bossbar, Executor\Commands, Tasks\NewMap, Events\GlobalEvents, Tasks\GameScheduler};
use pocketmine\{Server, Player, plugin\PluginBase, entity\Entity, utils\Config, utils\TextFormat as Color};

class SkyWars extends PluginBase {
    public static $instance;
    public static $bossbar;
    public static $database;
    public static $access = [
        'host' => '127.0.0.1',
        'user' => 'mikerangel',
        'password' => 'pulguis36',
        'database' => 'webhonor'
    ];
    public static $data = [
        'prefix' => Color::GOLD . '',
        'id' => [],
        'vote' => [],
        'damager' => [],
        'queue' => [],
        'configurator' => []
    ];

    public function onLoad() : void {
        self::$instance = $this;
        self::$bossbar = new Bossbar();
        self::$database = new MySQL(self::$access);
    }

    public function onEnable() : void {
        if (!self::getDatabase()->getMySQL()) {
            $this->getLogger()->info(Color::RED . 'A connection to the database could not be established.');
            $this->getServer()->shutdown();
        } else {
            $this->getLogger()->info(Color::GREEN . 'Connection to the database has been successfully established.');
        }
        $this->getLogger()->info(Color::GREEN . 'Plugin activated successfully.');
        $this->loadResources();
        $this->loadCommands();
        $this->loadArenas();
        $this->loadEvents();
        $this->loadTasks();
    }

    public static function getInstance() : self {
        return self::$instance;
    }

    public static function getPrefix() : string {
        return self::$data['prefix'];
    }

    public static function getBossbar() : Bossbar {
		return self::$bossbar;
    }
    
    public static function getDatabase() : MySQL {
        return self::$database;
    }

    public static function getConfigs(string $value) : config {
		return new Config(self::getInstance()->getDataFolder() . "{$value}.yml", Config::YAML);
    }

    public static function getReloadArena(string $arena) {
        $config = self::getConfigs('Arenas/' . $arena);
        self::$data['vote'][Arena::getName($arena)]['op'] = [];
        self::$data['vote'][Arena::getName($arena)]['normal'] = [];
        $config->set('status', 'waiting');
        $config->set('lobbytime', 30);
        $config->set('startingtime', 1);
        $config->set('gametime', 600);
        $config->set('refilltime', 120);
        $config->set('endtime', 9);
        $config->save();
    }

    public function loadResources() : void {
        $folder = $this->getDataFolder();
        foreach([$folder, $folder . 'Arenas', $folder . 'Backups'] as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir);
            }
        }
        $config = self::getConfigs('config');
        if ($config->get('chestitems') == null) {
            $config->set('chestitems', [[1,0,30], [1,0,20], [3,0,15], [3,0,25], [4,0,35], [4,0,15], [260,0,5], [261,0,1], [262,0,6], [267,0,1], [268,0,1], [272,0,1], [276,0,1], [283,0,1], [297,0,3], [298,0,1], [299,0,1], [300,0,1], [301,0,1], [303,0,1], [304,0,1], [310,0,1], [313,0,1], [314,0,1], [315,0,1], [316,0,1], [317,0,1], [320,0,4], [354,0,1], [364,0,4], [366,0,5], [391,0,5]]);
            $config->save();
        }
    }

    public function loadArenas() : void {
        foreach (Arena::getArenas() as $arena) {
            if (count(Arena::getArenas()) > 0) {
                self::getReloadArena($arena);
                ResetMap::resetZip(Arena::getName($arena));
            }
        }
    }
    
    public function loadEntitys() : void {
		$values = [];
		foreach ($values as $entitys) {
			Entity::registerEntity($entitys, true);
		}
		unset ($values);
	}

	public function loadCommands() : void {
		$values = [new Commands($this)];
		foreach ($values as $commands) {
			$this->getServer()->getCommandMap()->register('_cmd', $commands);
		}
		unset($values);
	}

	public function loadEvents() : void {
		$values = [new GlobalEvents($this)];
		foreach ($values as $events) {
			$this->getServer()->getPluginManager()->registerEvents($events, $this);
		}
		unset($values);
	}

	public function loadTasks() : void {
		$values = [new GameScheduler($this)];
		foreach ($values as $tasks) {
			$this->getScheduler()->scheduleRepeatingTask($tasks, 20);
		}
        unset($values);
    }

    public function onDisable() : void {
        $this->getLogger()->info(Color::RED . 'Plugin disabled.');
    }
}
?>