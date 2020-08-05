<?php
declare(strict_types = 1);
namespace MikeRangel\SkyWars\Extensions;
use MikeRangel\SkyWars\SkyWars;
use MikeRangel\SkyWars\Extensions\entity\FireworksRocket;
use MikeRangel\SkyWars\Extensions\item\Fireworks;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class FireworksTask extends Task
{

	public $seconds = 0;
	public $player;

	public function __construct(Player $player)
	{
		$this->player = $player;
	}

	/**
	 * @inheritDoc
	 */
	public function onRun(int $currentTick)
	{
		if ($this->seconds < 9) {
			$fw = ItemFactory::get(Item::FIREWORKS);
			$fw->addExplosion(Fireworks::TYPE_STAR, Fireworks::COLOR_WHITE, "", false, false);
			$fw->setFlightDuration(1);
			$fw1 = ItemFactory::get(Item::FIREWORKS);
			$fw1->addExplosion(Fireworks::TYPE_STAR, Fireworks::COLOR_GOLD, "", false, false);
			$fw1->setFlightDuration(1);
			if ($this->player !== null) {
				$nbt = FireworksRocket::createBaseNBT(new Vector3($this->player->x, $this->player->y, $this->player->z), new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
				$entity = FireworksRocket::createEntity("FireworksRocket", $this->player->getLevel(), $nbt, $fw);
				$entity2 = FireworksRocket::createEntity("FireworksRocket", $this->player->getLevel(), $nbt, $fw1);
				if ($entity instanceof FireworksRocket) {
					$entity->spawnToAll();
					$entity2->spawnToAll();
				}
			}
		} else {
			SkyWars::getInstance()->getScheduler()->cancelTask($this->getTaskId());
		}
		$this->seconds++;
	}
}