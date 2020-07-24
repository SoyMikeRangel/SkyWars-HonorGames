<?php
declare(strict_types = 1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @HonorGames_ 
*/
namespace MikeRangel\SkyWars\Form;
use pocketmine\{Player, form\Form as IForm};

abstract class Form implements IForm{
    protected $data = [];
    private $callable;

    public function __construct(?callable $callable) {
        $this->callable = $callable;
    }

    public function sendToPlayer(Player $player) : void {
        $player->sendForm($this);
    }

    public function getCallable() : ?callable {
        return $this->callable;
    }

    public function setCallable(?callable $callable) {
        $this->callable = $callable;
    }

    public function handleResponse(Player $player, $data) : void {
        $this->processData($data);
        $callable = $this->getCallable();
        if($callable !== null) {
            $callable($player, $data);
        }
    }

    public function processData(&$data) : void {
    }

    public function jsonSerialize(){
        return $this->data;
    }
}
?>