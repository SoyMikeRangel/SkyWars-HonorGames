<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @HonorGames_ 
*/
namespace MikeRangel\SkyWars\Database;
use pocketmine\{Server, Player};
use RuntimeException;
use mysqli;

class PlayerConection {
    private static $data;

    public function __construct(array $data) {
        self::$data = $data;
    }

    public function getMySQL() : mysqli {
        return new mysqli(self::$data['host'], self::$data['user'], self::$data['password'], self::$data['database']);
    }

    public function inDatabase(Player $player) : bool {
        $username = $player->getName();
        $sql = $this->getMySQL()->query("SELECT * FROM skywars where name='$username'");
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
            $this->getMySQL()->query("INSERT INTO skywars (name, wins, kills) VALUES ('$username', 0, 0)");
            $this->getMySQL()->close();
        }
    }

    public function remove(Player $player) {
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $sql = $this->getMySQL()->query("DELETE FROM skywars where name='$username'");
            $this->getMySQL()->close();
        } else {
            throw new RuntimeException('This player does not exist in the database.');
        }
    }

    public function addWins(Player $player, int $value) {
        $wins = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywars");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $wins = $out['wins'];
                }
            }
            $final = $wins + $value;
            $this->getMySQL()->query("UPDATE skywars SET wins='$final' WHERE name='$username'");
            $this->getMySQL()->close();
        } else {
            throw new RuntimeException('This player does not exist in the database.');
        }
    }

    public function removeWins(Player $player, int $value) {
        $wins = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywars");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $wins = $out['wins'];
                }
            }
            $final = $wins - $value;
            if ($wins > 0) {
                $this->getMySQL()->query("UPDATE skywars SET wins='$final' WHERE name='$username'");
                $this->getMySQL()->close();
            }
        } else {
            throw new RuntimeException('This player does not exist in the database.');
        }
    }

    public function addKills(Player $player, int $value) {
        $kills = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywars");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $kills = $out['kills'];
                }
            }
            $final = $kills + $value;
            $this->getMySQL()->query("UPDATE skywars SET kills='$final' WHERE name='$username'");
            $this->getMySQL()->close();
        } else {
            throw new RuntimeException('This player does not exist in the database.');
        }
    }

    public function removeKills(Player $player, int $value) {
        $kills = 0;
        if ($this->inDatabase($player)) {
            $username = $player->getName();
            $request = mysqli_query($this->getMySQL(), "SELECT * FROM skywars");
            while ($out = mysqli_fetch_array($request)) {
                if ($username == $out['name']) {
                    $kills = $out['kills'];
                }
            }
            $final = $kills - $value;
            if ($kills > 0) {
                $this->getMySQL()->query("UPDATE skywars SET kills='$final' WHERE name='$username'");
                $this->getMySQL()->close();
            }
        } else {
            throw new RuntimeException('This player does not exist in the database.');
        }
    }
}
?>