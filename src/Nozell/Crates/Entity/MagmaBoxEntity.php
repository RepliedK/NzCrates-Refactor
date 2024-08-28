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
use pocketmine\world\particle\FlameParticle;

class MagmaBoxEntity extends Living {

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
        return "crates:dark_magma";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(1.8, 0.8, 1.62);
    }
    
    public function getName(): string {
        return "MagmaBoxEntity";
    }

    public function onUpdate(int $currentTick): bool {
        $config = Main::getInstance()->getConfig();
        $pos = $this->getPosition();
        $world = $this->getWorld();
    
        $particlesPerRevolution = 20;
        $heightPerRevolution = 0.5;
        $radius = 1;
        $revolutions = 3;
    
        $time = ($currentTick % ($particlesPerRevolution * $revolutions)) / $particlesPerRevolution;
    
        $angle = $time * 2 * M_PI;
        $x = $radius * cos($angle);
        $z = $radius * sin($angle);
        $y = $heightPerRevolution * $time;
    
        $particlePos = $pos->add($x, $y, $z);
        $world->addParticle($particlePos, new FlameParticle());
    
        $floatingText = $config->get("magmafloatingtext");
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

                    if ($meeting->getKeyMagma() > 0) {
                        $meeting->reduceKeyMagma();
                        Main::getInstance()->getCrateManager()->getRandomItemFromCrate("magma", $damager->getName(), $this);
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
}
