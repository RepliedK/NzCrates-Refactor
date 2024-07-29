<?php

namespace Nozell;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Nozell\Command\CratesCommand;
use Nozell\Entity\MageBoxEntity;
use Nozell\Entity\PegasusBoxEntity;
use muqsit\invmenu\InvMenuHandler;
use Nozell\Entity\EnderBoxEntity;
use Nozell\Entity\IceBoxEntity;
use Nozell\Entity\MagmaBoxEntity;
use Nozell\CrateManager;
use customiesdevs\customies\entity\CustomiesEntityFactory;
use Nozell\Utils\CratesUtils;
use pocketmine\player\Player;
use pocketmine\resourcepacks\ZippedResourcePack;
use Symfony\Component\Filesystem\Path;
use function array_merge;
use function str_replace;

class Main extends PluginBase implements Listener {
    
    private CrateManager $crateManager;
    public Config $config;

    /** @var Main $this */
    public static Main $this;

    public function onEnable(): void{
        
        if(!InvMenuHandler::isRegistered()){     InvMenuHandler::register($this);

        }
        $this->saveDefaultConfig();
		$this->saveResource("Crates.mcpack");    
        $rpManager = $this->getServer()->getResourcePackManager();
		$rpManager->setResourceStack(array_merge($rpManager->getResourceStack(), [new ZippedResourcePack(Path::join($this->getDataFolder(), "Crates.mcpack"))]));
		(new \ReflectionProperty($rpManager, "serverForceResources"))->setValue($rpManager, true);

        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        
        self::$this = $this;
        
        $this->crateManager = new CrateManager($this);

        $this->getServer()->getCommandMap()->register("crates", new CratesCommand("crates", "Abre el menú principal de crates", "/crates"));

        CustomiesEntityFactory::getInstance()->registerEntity(MageBoxEntity::class, "crates:mage_chest", null, "minecraft:humanoid");
        CustomiesEntityFactory::getInstance()->registerEntity(IceBoxEntity::class, "crates:ice_chest", null, "minecraft:humanoid");
        CustomiesEntityFactory::getInstance()->registerEntity(EnderBoxEntity::class, "crates:grand_ender_chest", null, "minecraft:humanoid");
        CustomiesEntityFactory::getInstance()->registerEntity(MagmaBoxEntity::class, "crates:dark_magma", null, "minecraft:humanoid");
        CustomiesEntityFactory::getInstance()->registerEntity(PegasusBoxEntity::class, "crates:golden_pegasus", null, "minecraft:humanoid");
    }

    public static function getInstance(): self {
        return self::$this;
    }
    
    public function getCrateManager(): CrateManager {
        return $this->crateManager;
    }

    /**
     * @param Player $player
     * @param string $type
     * @return mixed
     */
    public static function getKeyType(Player $player, string $type): mixed {
        return CratesUtils::getKeyType($player, $type);
    }

    /** @throws JsonException */
    public static function addKeyType(Player $player, string $type, int $int) {
        CratesUtils::addKeyType($player, $type, $int);
    }

    public static function removeKeyBox(Player $player, string $type, int $int) {
        CratesUtils::removeKeyBox($player, $type, $int);
    }

    public static function playSound(Player $player, string $sound, int $volume, float $pitch) {
        CratesUtils::playSound($player, $sound, $volume, $pitch);
    }
}
