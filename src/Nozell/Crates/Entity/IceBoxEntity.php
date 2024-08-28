<?php

namespace Nozell\Crates\Entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use Nozell\Crates\Main;
use Nozell\Crates\Meetings\MeetingManager;
use pocketmine\entity\Living;
use pocketmine\world\particle\SnowballPoofParticle;

class IceBoxEntity extends Living {

    public function __construct(Location $location, ?CompoundTag $nbt = null) {
        parent::__construct($location, $nbt);
        $this->setNameTagAlwaysVisible(true);
        $this->setHasGravity(false);
        $this->spawnToAll();
    }
    
    public function canBeMovedByCurrents(): bool {
        return false;
    }
    
    public static function getNetworkTypeId(): string {
        return "crates:ice_chest";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(1.8, 0.8, 1.62);
    }
    
    public function getName(): string {
        return "IceBoxEntity";
    }

    public function onUpdate(int $currentTick): bool {
        $config = Main::getInstance()->getConfig();
        $pos = $this->getPosition();
        $world = $this->getWorld();

        $radius = 1.0;
        $height = 2.0;
        $particlesPerRevolution = 3;
        $revolutions = 2;
        $angleIncrement = (2 * M_PI) / $particlesPerRevolution;
        $particleSpacing = $height / ($particlesPerRevolution * $revolutions);

        for ($i = 0; $i < $particlesPerRevolution * $revolutions; $i++) {
            $angle = $i * $angleIncrement;
            $y = $i * $particleSpacing;
            $x = $radius * cos($angle);
            $z = $radius * sin($angle);
            $world->addParticle($pos->add($x, $y, $z), new SnowballPoofParticle());
        }

        $floatingText = $config->get("icefloatingtext");
        $this->setNameTag($floatingText);
        return parent::onUpdate($currentTick);
    }

    public function attack(EntityDamageEvent $source): void {
        $source->cancel();
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof Player) {
                if ($damager->getInventory()->getItemInHand()->getTypeId() === VanillaItems::DIAMOND_SWORD()->getTypeId()) {
                    if (Server::getInstance()->isOp($damager->getName()) || $damager->hasPermission("box.dell")) {
                        $this->flagForDespawn();
                        return;
                    }
                } else {
                    if (!self::canOpenCrate($damager)) {
                        $damager->sendMessage("§cDebes tener un slot vacío antes de abrir una crate.");
                        return;
                    }

                    $meeting = MeetingManager::getInstance()->getMeeting($damager)->getDataCrates();

                    if ($meeting->getKeyIce() > 0) {
                        $meeting->reduceKeyIce(); 
                        Main::getInstance()->getCrateManager()->getRandomItemFromCrate("ice", $damager->getName(), $this);
                        $damager->sendMessage("§aHas abierto una crate exitosamente!");
                    } else {
                        $damager->sendMessage("§cAl parecer no tienes keys!");
                    }
                }
            }
        }
    }

    public static function canOpenCrate(Player $player): bool {
        $inventory = $player->getInventory();
        $emptySlots = 0;

        for ($i = 0; $i < $inventory->getSize(); $i++) {
            if ($inventory->getItem($i)->isNull()) {
                $emptySlots++;
            }
        }
        return $emptySlots >= 1;
    }

    protected function getInitialDragMultiplier(): float {
        return 0.0;
    }

    protected function getInitialGravity(): float {
        return 0.0;
    }
}
