<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @PacmanLivePE 
*/
namespace MikeRangel\SkyWars\Executor;
use MikeRangel\SkyWars\{SkyWars, Arena\Arena, Form\FormManager, Entity\EntityManager, Entity\types\EntityHuman, Entity\types\EntityStats};
use pocketmine\{Server, Player, utils\TextFormat as Color};
use pocketmine\command\{PluginCommand, CommandSender};

class Commands extends PluginCommand {

    public function __construct(SkyWars $plugin) {
        parent::__construct('sw', $plugin);
        $this->setDescription('SkyWars commands.');
    }

    public function execute(CommandSender $player, $label, array $args) {
        if (!isset($args[0])) {
            $player->sendMessage(Color::RED . 'Usage: /sw help');
            return false;
        }
        switch ($args[0]) {
            case 'help':
                $date = [
                    '/sw help: Help commands.',
                    '/sw create <arena> <maxslots> <id>: Create arena.',
                    '/sw credits: View author.'
                ];
                $player->sendMessage(Color::GOLD . 'SkyWars Commands:');
                foreach ($date as $help) {
                    $player->sendMessage(Color::GREEN . $help);
                }
            break;
            case 'create':
                if ($player->isOp()) {
                    if (isset($args[1], $args[2], $args[3])) {
                        if (file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[1])) {
                            $database = SkyWars::getDatabase()->getArenas();
                            if ($database->inDatabase('SW-' . $args[3])) {
                                Arena::addArena($player, $args[1], $args[2], $args[3]);
                            } else {
                                $player->sendMessage(SkyWars::getPrefix() . Color::RED . 'This arena already exists.');
                            }
                        } else {
                            $player->sendMessage(SkyWars::getPrefix() . Color::RED . 'This world does not exist.');
                        }
                    } else {
                        $player->sendMessage(SkyWars::getPrefix() . Color::RED . 'Usage: /sw create <arena> <maxslots> <id>');
                    }
                } else {
                    $player->sendMessage(SkyWars::getPrefix() . Color::RED . 'You do not have permissions to run this command.');
                }
            break;
            case 'credits':
                $description = [
                    'Author: ' . Color::GRAY . '@MikeRangelMR',
                    'Status: ' . Color::GREEN . 'SkyWars is private.'
                ];
                foreach ($description as $credits) {
                    $player->sendMessage(Color::GOLD . $credits);
                }
            break;
        }
        return true;
    }
}
?>
