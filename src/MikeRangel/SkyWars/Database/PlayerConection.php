<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @HonorGames_ 
*/
namespace MikeRangel\SkyWars\Database;
use MikeRangel\SkyWars\{SkyWars};
use pocketmine\{Server, Player};
use RuntimeException;
use mysqli;

class PlayerConection {
    private static $data;

    public function __construct(array $data) {
        self::$data = $data;
    }

    public function getMySQL() : mysqli {
        return new mysqli(self::$data['host'], self::$data['user'], self::$data['password'], 'minigames');
    }

    public function createTable() : void {
        $sql = $this->getMySQL()->query("SELECT table_name FROM information_schema.tables WHERE table_schema='minigames' AND table_name='skywarslb'");
        $count = $sql->num_rows;
        if ($count == 0) {
            $table = $this->getMySQL()->query("CREATE TABLE skywarslb(
                id int UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name text NOT NULL,
                experience int NOT NULL,
                plays int NOT NULL,
                wins int NOT NULL,
                kills int NOT NULL,
                deaths int NOT NULL
            )");
            if ($table) {
                SkyWars::getInstance()->getLogger()->notice(Color::GREEN . 'The skywarslb table has been created successfully.');
            } else {
                SkyWars::getInstance()->getLogger()->notice(Color::GREEN . 'The skywarslb table was not created correctly.');
            }
            $this->getMySQL()->close();
        }
    }

    public function inDatabase(Player $player) : bool {
        $username = $player->getName();
        $sql = $this->getMySQL()->query("SELECT * FROM skywarslb where name='$username'");
        $count = $sql->num_rows;
        if ($count == 0) {
            return false;
        } else {
            return true;
        }
        $this->getMySQL()->close();
    }

    public function add(Player $player) {
        if (!$this->inDatabase($player)) {
            $username = $player->getName();
            $ip = $player->getAddress();
            $this->getMySQL()->query("INSERT INTO skywarslb (name, experience, plays, wins, kills, deaths) VALUES ('$username', 0, 0, 0, 0, 0)");
            $this->getMySQL()->close();
        }
    }

    public function remove(Player $player) {
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $sql = $this->getMySQL()->query("DELETE FROM skywarslb where name='$username'");
            $this->getMySQL()->close();
        }
    }

    public function addXp(Player $player, int $value) {
        $xp = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $xp = $out['experience'];
                }
            }
            $final = $xp + $value;
            $this->getMySQL()->query("UPDATE skywarslb SET experience='$final' WHERE name='$username'");
            $this->getMySQL()->close();
        }
    }

    public function removeXp(Player $player, int $value) {
        $xp = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $xp = $out['experience'];
                }
            }
            $final = $xp - $value;
            if ($xp > 0) {
                $this->getMySQL()->query("UPDATE skywarslb SET experience='$final' WHERE name='$username'");
                $this->getMySQL()->close();
            }
        }
    }

    public function addPlays(Player $player, int $value) {
        $plays = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $plays = $out['plays'];
                }
            }
            $final = $plays + $value;
            $this->getMySQL()->query("UPDATE skywarslb SET plays='$final' WHERE name='$username'");
            $this->getMySQL()->close();
        }
    }

    public function removePlays(Player $player, int $value) {
        $plays = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $plays = $out['plays'];
                }
            }
            $final = $plays - $value;
            if ($plays > 0) {
                $this->getMySQL()->query("UPDATE skywarslb SET plays='$final' WHERE name='$username'");
                $this->getMySQL()->close();
            }
        }
    }

    public function addWins(Player $player, int $value) {
        $wins = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $wins = $out['wins'];
                }
            }
            $final = $wins + $value;
            $this->getMySQL()->query("UPDATE skywarslb SET wins='$final' WHERE name='$username'");
            $this->getMySQL()->close();
        }
    }

    public function removeWins(Player $player, int $value) {
        $wins = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $wins = $out['wins'];
                }
            }
            $final = $wins - $value;
            if ($wins > 0) {
                $this->getMySQL()->query("UPDATE skywarslb SET wins='$final' WHERE name='$username'");
                $this->getMySQL()->close();
            }
        }
    }

    public function addKills(Player $player, int $value) {
        $kills = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $kills = $out['kills'];
                }
            }
            $final = $kills + $value;
            $this->getMySQL()->query("UPDATE skywarslb SET kills='$final' WHERE name='$username'");
            $this->getMySQL()->close();
        }
    }

    public function removeKills(Player $player, int $value) {
        $kills = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $kills = $out['kills'];
                }
            }
            $final = $kills - $value;
            if ($kills > 0) {
                $this->getMySQL()->query("UPDATE skywarslb SET kills='$final' WHERE name='$username'");
                $this->getMySQL()->close();
            }
        }
    }

    public function addDeaths(Player $player, int $value) {
        $deaths = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $deaths = $out['deaths'];
                }
            }
            $final = $deaths + $value;
            $this->getMySQL()->query("UPDATE skywarslb SET deaths='$final' WHERE name='$username'");
            $this->getMySQL()->close();
        }
    }

    public function removeDeaths(Player $player, int $value) {
        $deaths = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $deaths = $out['deaths'];
                }
            }
            $final = $deaths - $value;
            if ($deaths > 0) {
                $this->getMySQL()->query("UPDATE skywarslb SET deaths='$final' WHERE name='$username'");
                $this->getMySQL()->close();
            }
        }
    }

    public function addCoins(Player $player, int $value) {
        $coins = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $coins = $out['coins'];
                }
            }
            $final = $coins + $value;
            $this->getMySQL()->query("UPDATE skywarslb SET coins='$final' WHERE name='$username'");
            $this->getMySQL()->close();
        }
    }

    public function removeCoins(Player $player, int $value) {
        $coins = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywarslb");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $coins = $out['coins'];
                }
            }
            $final = $coins - $value;
            if ($coins > 0) {
                $this->getMySQL()->query("UPDATE skywarslb  SET coins='$final' WHERE name='$username'");
                $this->getMySQL()->close();
            }
        }
    }
}
?>