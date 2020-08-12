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
        $sql = $this->getMySQL()->query("SELECT table_name FROM information_schema.tables WHERE table_schema='minigames' AND table_name='skywars'");
        $count = $sql->num_rows;
        if ($count == 0) {
            $table = $this->getMySQL()->query("CREATE TABLE skywars(
                name text NOT NULL,
                status text NOT NULL,
                count int NOT NULL
            )");
            if ($table) {
                SkyWars::getInstance()->getLogger()->notice(Color::GREEN . 'The skywars table has been created successfully.');
            } else {
                SkyWars::getInstance()->getLogger()->notice(Color::GREEN . 'The skywars table was not created correctly.');
            }
            $this->getMySQL()->close();
        }
    }

    public function createTableID() : void {
        $sql = $this->getMySQL()->query("SELECT table_name FROM information_schema.tables WHERE table_schema='minigames' AND table_name='SWID'");
        $count = $sql->num_rows;
        if ($count == 0) {
            $table = $this->getMySQL()->query("CREATE TABLE SWID(
                id int NOT NULL
            )");
            if ($table) {
                SkyWars::getInstance()->getLogger()->notice(Color::GREEN . 'The SWID table has been created successfully.');
            } else {
                SkyWars::getInstance()->getLogger()->notice(Color::GREEN . 'The SWID table was not created correctly.');
            }
            $this->addID();
            $this->getMySQL()->close();
        }
    }
  
    public function addID() {
        $this->getMySQL()->query("INSERT INTO SWID (id) VALUES (1)");
        $this->getMySQL()->close();
    }

    public function removeID() {
            $sql = $this->getMySQL()->query("DELETE FROM 'SWID'");
            $this->getMySQL()->close();
    }

    public function setID(int $value) {
            $this->getMySQL()->query("UPDATE SWID SET id='$value'");
            $this->getMySQL()->close();
    }

    public function getID() {
        $count = null;
        $resultado = mysqli_query($this->getMySQL(), "SELECT * FROM SWID");
        while ($consulta = mysqli_fetch_array($resultado)) {
            $count = $consulta['id'];
        }
        return $count;
    }

    public function add(string $arena) {
        $this->getMySQL()->query("INSERT INTO skywars (name, status, count) VALUES ('$arena' ,'editing', 0)");
        $this->getMySQL()->close();
    }

    public function remove(string $arena) {
        $sql = $this->getMySQL()->query("DELETE FROM '$arena'");
        $this->getMySQL()->close();
    }

    public function inDatabase(string $arena) : bool {
        $sql = $this->getMySQL()->query("SELECT * FROM skywars where name='$arena'");
        $count = $sql->num_rows;
        if ($count == 0) {
            return false;
        } else {
            return true;
        }
        $this->getMySQL()->close();
    }

    public function getArenas() {
        $sql = mysqli_query($this->getMySQL(), "SELECT * FROM skywars");
        $count = $sql->num_rows;
        if ($count != 0) {
            return $count;
        }
    }

    public function setCount(string $arena, int $value) {
        if ($this->inDatabase($arena)) {
            $this->getMySQL()->query("UPDATE skywars SET count='$value' WHERE name='$arena'");
            $this->getMySQL()->close();
        }
    }

    public function getCount(string $arena) {
        $count = null;
        if ($this->inDatabase($arena)) {
            $resultado = mysqli_query($this->getMySQL(), "SELECT * FROM skywars");
            while ($consulta = mysqli_fetch_array($resultado)) {
                if ($arena == $consulta['name']) {
                    $count = $consulta['count'];
                }
            }
            return $count;
        }
        return $count;
    }

    public function setStatus(string $arena, string $status) {
        if ($this->inDatabase($arena)) {
            $this->getMySQL()->query("UPDATE skywars SET status='$status' WHERE name='$arena'");
            $this->getMySQL()->close();
        }
    }

    public function getStatus(string $arena) {
        $status = null;
        if ($this->inDatabase($arena)) {
            $resultado = mysqli_query($this->getMySQL(), "SELECT * FROM skywars");
            while ($consulta = mysqli_fetch_array($resultado)) {
                if ($arena == $consulta['name']) {
                    $status = $consulta['status'];
                }
            }
            return $status;
        }
        return $status;
    }
}
?>