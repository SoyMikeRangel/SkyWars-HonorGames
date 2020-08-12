<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @HonorGames_ 
*/
namespace MikeRangel\SkyWars\Database;
use MikeRangel\SkyWars\{SkyWars};
use pocketmine\{utils\TextFormat as Color};
use mysqli;

class MySQL {
    private static $data;

    public function __construct(array $data) {
        self::$data = $data;
    }

    public function getStatus() : bool {
        if (!(mysqli_connect(self::$data['host'], self::$data['user'], self::$data['password']))) {
            return false;
        } else {
            return true;
        }
    }

    public static function getMySQL() : mysqli {
        return new mysqli(self::$data['host'], self::$data['user'], self::$data['password']);
    }

    public function createOthers() : void {
        foreach (['skywars'] as $databases) {
            $this->createDatabase($databases);
        }
        $this->getUser()->createTable();
    }

    public function createDatabase(string $value) {
        $sql = $this->getMySQL()->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='$value'");
        $count = $sql->num_rows;
        if ($count == 0) {
            $database = $this->getMySQL()->query("CREATE DATABASE $value");
            if ($database) {
                SkyWars::getInstance()->getLogger()->notice(Color::GREEN . 'The ' . $value . ' database has been created successfully.');
            } else {
                SkyWars::getInstance()->getLogger()->notice(Color::GREEN . 'The ' . $value . ' database was not created correctly.');
            }
            $this->getMySQL()->close();
        }
    }

    public function getUser() : PlayerConection {
        return new PlayerConection(self::$data);
    }

    public function getArenas() : ArenaConection {
        return new ArenaConection(self::$data);
    }
}
?>