<?php
declare(strict_types=1);
/* 
 * Author: @MikeRangelMR
 * Status: Private
 * Server: @HonorGames_ 
*/
namespace MikeRangel\SkyWars\Bossbar;
use pocketmine\{Server, Player, entity\Entity, entity\EntityIds, entity\Attribute, math\Vector3};
use pocketmine\network\mcpe\protocol\{AddActorPacket, BossEventPacket, RemoveActorPacket, SetActorDataPacket, UpdateAttributesPacket};

class Bossbar extends Vector3 {
    private static $healthPercent = 0, $maxHealthPercent = 1;
    private static $entityId;
    private static $metadata = [];
    private static $viewers = [];

	public function __construct(string $title = '', float $hp = 1, float $maxHp = 1) {
        parent::__construct(0, 255);
        $flags = (
            (1 << Entity::DATA_FLAG_INVISIBLE) |
            (1 << Entity::DATA_FLAG_IMMOBILE)
        );
        self::$metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title]
        ];
        self::$entityId = Entity::$entityCount++;
        $this->setHealthPercent($hp, $maxHp);
    }

	public function updateForAll() : void {
        foreach (self::$viewers as $player) {
            $this->updateFor($player);
        }
    }

	public function updateFor(Player $player, $title = '', $hp = 1) : void {
		$pk = new BossEventPacket();
		$pk->bossEid = self::$entityId;
		$pk->eventType = BossEventPacket::TYPE_TITLE;
		$pk->healthPercent = $hp ?? $this->getHealthPercent();
		$pk->title = $title;
		$pk2 = clone $pk;
		$pk2->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;
		$player->dataPacket($pk);
		$player->dataPacket($pk2);
        $player->dataPacket($this->getHealthPacket());
        $mpk = new SetActorDataPacket();
        $mpk->entityRuntimeId = self::$entityId;
        $mpk->metadata = self::$metadata;
        $player->dataPacket($mpk);
    }

	public function getHealthPercent() : float {
        return self::$healthPercent;
    }

	public function setHealthPercent(?float $hp = null, ?float $maxHp = null, bool $update = true) : void {
        if ($maxHp !== null) {
            self::$maxHealthPercent = $maxHp;
        }
        if ($hp !== null) {
            if ($hp > self::$maxHealthPercent) {
                self::$maxHealthPercent = $hp;
            }
            self::$healthPercent = $hp;
        }

        if ($update) {
            $this->updateForAll();
        }
    }

	public function getMetadata(int $key) {
        return isset(self::$metadata[$key]) ? self::$metadata[$key][1] : null;
    }

	protected function getHealthPacket() : UpdateAttributesPacket {
        $attr = Attribute::getAttribute(Attribute::HEALTH);
        $attr->setMaxValue(self::$maxHealthPercent);
        $attr->setValue(self::$healthPercent);
        $pk = new UpdateAttributesPacket();
        $pk->entityRuntimeId = self::$entityId;
        $pk->entries = [$attr];
        return $pk;
    }

	public function showTo(Player $player, string $title, bool $isViewer = true) : void {
        $pk = new AddActorPacket();
        $pk->entityRuntimeId = self::$entityId;
        $pk->type = EntityIds::SHULKER;
        $pk->metadata = self::$metadata;
        $pk->position = $this;
        $player->dataPacket($pk);
        $player->dataPacket($this->getHealthPacket());
        $pk2 = new BossEventPacket();
        $pk2->bossEid = self::$entityId;
        $pk2->eventType = BossEventPacket::TYPE_SHOW;
        $pk2->title = $title;
        $pk2->healthPercent = self::$healthPercent;
        $pk2->color = 0;
        $pk2->overlay = 0;
        $pk2->unknownShort = 0;
        $player->dataPacket($pk2);
        if ($isViewer) {
            self::$viewers[$player->getLoaderId()] = $player;
        }
    }

	public function hideFrom(Player $player) : void {
        $pk = new BossEventPacket();
        $pk->bossEid = self::$entityId;
        $pk->eventType = BossEventPacket::TYPE_HIDE;
        $player->dataPacket($pk);
        $pk2 = new RemoveActorPacket();
        $pk2->entityUniqueId = self::$entityId;
        $player->dataPacket($pk2);
        if (isset(self::$viewers[$player->getLoaderId()])) {
            unset(self::$viewers[$player->getLoaderId()]);
        }
    }

	public function getViewers() : array {
        return self::$viewers;
    }
}
?>