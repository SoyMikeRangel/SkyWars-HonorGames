<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Form;
use MikeRangel\SkyWars\{SkyWars, PluginUtils, Arena\Arena, Tasks\Emotes, Tasks\ArenaID, Form2\CustomForm, Form\MenuForm, Form\elements\Button};
use pocketmine\{Server, Player, item\Item, entity\Effect, math\Vector3, entity\EffectInstance, utils\TextFormat as Color};

class FormManager {

    public static function getPlayersUI(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::GREEN . 'PLAYERS REAMING', Color::GRAY . 'Choose who to see.',
        self::getButtonsAlive($player),
        static function (Player $player, Button $button) : void {
            $explode = explode("\n", $button->getText());
            $name = substr($explode[0], 3);
            $pl = Server::getInstance()->getPlayer($name);
            $player->teleport(new Vector3($pl->getX(), $pl->getY(), $pl->getZ()));
        }));
    }

    public static function getButtonsAlive(Player $player) {
        $buttons = [];
        foreach (Arena::getArenas() as $arena) {
            if ($player->getLevel()->getFolderName() == Arena::getName($arena)) {
                foreach ($player->getLevel()->getPlayers() as $players) {
                    if ($players->getGamemode() == 0) {
                        $buttons[] = new Button(Color::BOLD . $players->getName() . "\n" . Color::RESET . Color::BLACK . 'Click to view');
                    }
                }           
            }
        }
        return $buttons;
    }

    public static function getVotesUI(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::GREEN . 'VOTE CHEST', Color::GRAY . 'Vote for your favorite chest.',
        [
            new Button(Color::GRAY . 'OP[' . Color::GREEN . count(SkyWars::$data['vote'][$player->getLevel()->getFolderName()]['op']) . Color::GRAY . ']' . Color::RESET . "\n" . Color::BLACK . 'Click to select'),
            new Button(Color::GRAY . 'Basic[' . Color::GREEN . count(SkyWars::$data['vote'][$player->getLevel()->getFolderName()]['normal']) . Color::GRAY . ']' . Color::RESET . "\n" . Color::BLACK . 'Click to select'),
        ],
        static function (Player $player, Button $button) : void {
            switch ($button->getValue()) {
                case 0:
                    if ($player->hasPermission('skywars.vote.perm')) {
                        if (count(Server::getInstance()->getLevelByName($player->getLevel()->getFolderName())->getPlayers()) < 2) {
                            $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::RED . 'More players are needed to access this feature.');
                        } else {
                            PluginUtils::setVote($player, $player->getLevel()->getFolderName(), 'op');
                        }
                    } else {
                        $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::RED . 'Adquire a range to access this function.');
                    }
                break;
                case 1:
                    if ($player->hasPermission('skywars.vote.perm')) {
                        if (count(Server::getInstance()->getLevelByName($player->getLevel()->getFolderName())->getPlayers()) < 2) {
                            $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::RED . 'More players are needed to access this feature');
                        } else {
                            PluginUtils::setVote($player, $player->getLevel()->getFolderName(), 'normal');
                        }
                    } else {
                        $player->sendMessage(Color::BOLD . Color::GREEN . '»' . Color::RESET . Color::RED . 'Adquire a range to access this function.');
                    }
                break;
            }
        }));
    }

    public static function getKitsUI(Player $player) {
        $player->sendForm(new MenuForm(Color::BOLD . Color::GREEN . 'KITS', Color::GRAY . 'Select your kit.',
        [
            new Button(Color::BOLD . 'Builder' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked'),
            new Button(Color::BOLD . 'Gappler' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked'),
            new Button(Color::BOLD . 'Rusher' . Color::RESET . "\n" . Color::RESET . Color::BLACK . 'Status: ' . Color::GREEN . 'Unlocked')
        ],
        static function (Player $player, Button $button) : void {
            switch ($button->getValue()) {
                case 0:
                    $config = SkyWars::getConfigs('kits');
                    $config->set($player->getName(), 'builder');
                    $config->save();
                break;
                case 1:
                    $config = SkyWars::getConfigs('kits');
                    $config->set($player->getName(), 'gappler');
                    $config->save();
                break;
                case 2:
                    $config = SkyWars::getConfigs('kits');
                    $config->set($player->getName(), 'rusher');
                    $config->save();
                break;
            }
        }));
    }
}
?>