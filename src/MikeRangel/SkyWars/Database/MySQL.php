<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @HonorGames_ 
*/
namespace MikeRangel\SkyWars\Database;

class MySQL {
    private static $data;

    public function __construct(array $data) {
        self::$data = $data;
    }

    public function getMySQL() : bool {
        if (!(mysqli_connect(self::$data['host'], self::$data['user'], self::$data['password']))) {
            return false;
        } else {
            return true;
        }
    }

    public function getUser() : PlayerConection {
        return new PlayerConection(self::$data);
    }
}
?>