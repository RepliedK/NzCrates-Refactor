<?php

namespace Nozell\Entity;

use JsonException;
use Forms\SimpleForm;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use Nozell\Main;
use pocketmine\entity\Living;
use pocketmine\world\particle\SnowballPoofParticle;

class IceBoxEntity extends Living {

    private array $cooldowns = []; // Para gestionar los cooldowns

    /**
     * @throws JsonException
     */
    public function __construct(Location $location, ?CompoundTag $nbt = null){
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

    // Parámetros de la espiral
    $radius = 1.0; // Radio de la espiral
    $height = 2.0; // Altura de la espiral
    $particlesPerRevolution = 3; // Partículas por revolución
    $revolutions = 2; // Número de revoluciones
    $angleIncrement = (2 * M_PI) / $particlesPerRevolution;
    $particleSpacing = $height / ($particlesPerRevolution * $revolutions); // Espaciado de partículas en altura

    // Generar partículas en espiral
    for ($i = 0; $i < $particlesPerRevolution * $revolutions; $i++) {
        $angle = $i * $angleIncrement;
        $y = $i * $particleSpacing;
        $x = $radius * cos($angle);
        $z = $radius * sin($angle);
        $world->addParticle($pos->add($x, $y, $z), new SnowballPoofParticle());
    }

    $floatingText = $config->get("icefloatingtext");
    $formattedText = str_replace("\n", "\n", $floatingText);
    $this->setNameTag($formattedText);
    return parent::onUpdate($currentTick);
}
    public function attack(EntityDamageEvent $source): void {
        $source->cancel();
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof Player) {
                $currentTime = microtime(true);
                $playerName = $damager->getName();

                // Comprobar cooldown
                if (isset($this->cooldowns[$playerName]) && $currentTime < $this->cooldowns[$playerName] + 20) {
                    $damager->sendMessage("§cDebes esperar antes de volver a abrir la crate.");
                    return;
                }

                if ($damager->getInventory()->getItemInHand()->getTypeId() === VanillaItems::DIAMOND_SWORD()->getTypeId()) {
                    if (Server::getInstance()->isOp($playerName) or $damager->hasPermission("box.dell")) {
                        $this->flagForDespawn();
                        return;
                    }
                } else {
                    $this->cooldowns[$playerName] = $currentTime;
                    $this->openFormVote($damager);
                }
            }
        }
    }

    public function openFormVote(Player $player): void {
        $boutique = Main::getKeyType($player, "ice");
        $form = new SimpleForm(function(Player $player, $args = null) use ($boutique) {
            if ($args === null) {
                return false;
            }
            switch ($args) {
                case 0:
                    return false;
                case 1:
                    if (!self::canOpenCrate($player)) {
                        $player->sendMessage("§cDebes tener un slot vacío antes de abrir una crate.");
                        return false;
                    }
                    if ($boutique !== 0) {
                        Main::removeKeyBox($player, "ice", 1);
                        Main::getInstance()->getCrateManager()->getRandomItemFromCrate("ice", $player->getName(), $this, "animation.dark_magma.opened", "controller.animation.dark_magma.opened");
                        $player->sendMessage("§aHas abierto una crate exitosamente!");
                    } else {
                        $player->sendMessage("§cAl parecer no tienes keys!");
                        return false;
                    }
                    break;
            }
            return false;
        });
        $config = Main::getInstance()->getConfig();
        $titleText = $config->get("iceformtitle");
        $formattedTitleText = str_replace("\n", "\n", $titleText);
        $contentText = $config->get("iceformcontent");
        $formattedContentText = str_replace("\n", "\n", $contentText);
        $form->setTitle($titleText);
        $form->setContent($contentText);
        $form->addButton("§cSalir");
        $form->addButton("§eAbrir la crate");
        $player->sendForm($form);
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
