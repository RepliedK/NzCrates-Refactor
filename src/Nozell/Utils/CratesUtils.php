<?php

namespace Nozell\Utils;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use Nozell\Main;
use JsonException;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class CratesUtils {

    /**
     * @param Player $player
     * @param string $type
     * @return mixed
     */
    public static function getKeyType(Player $player, string $type): mixed {
        $mage = new Config(Main::getInstance()->getDataFolder() . "mage.yml", Config::YAML);
        $ice = new Config(Main::getInstance()->getDataFolder() . "ice.yml", Config::YAML);
        $ender = new Config(Main::getInstance()->getDataFolder() . "ender.yml", Config::YAML);
        $magma = new Config(Main::getInstance()->getDataFolder() . "magma.yml", Config::YAML);
        $pegasus = new Config(Main::getInstance()->getDataFolder() . "pegasus.yml", Config::YAML);
        switch($type){
            case "mage":
                if($mage->exists($player->getName())) return $mage->get($player->getName());
                return 0;
            case "ice":
                if($ice->exists($player->getName())) return $ice->get($player->getName());
                return 0;
            case "ender":
                if($ender->exists($player->getName())) return $ender->get($player->getName());
                return 0;
            case "magma":
                if($magma->exists($player->getName())) return $magma->get($player->getName());
                return 0;
            case "pegasus":
                if($pegasus->exists($player->getName())) return $pegasus->get($player->getName());
                return 0;
            default:
                return 0;
        }
    }

    /** @throws JsonException */
    public static function addKeyType(Player $player, string $type, int $int) {
        $mage = new Config(Main::getInstance()->getDataFolder() . "mage.yml", Config::YAML);
        $ice = new Config(Main::getInstance()->getDataFolder() . "ice.yml", Config::YAML);
        $ender = new Config(Main::getInstance()->getDataFolder() . "ender.yml", Config::YAML);
        $magma = new Config(Main::getInstance()->getDataFolder() . "magma.yml", Config::YAML);
        $pegasus = new Config(Main::getInstance()->getDataFolder() . "pegasus.yml", Config::YAML);
        switch($type){
            case "mage":
                if($mage->exists($player->getName())){
                    $mage->set($player->getName(), $mage->get($player->getName()) + $int);
                } else {
                    $mage->set($player->getName(), $int);
                }
                $mage->save();
                break;
            case "ice":
                if($ice->exists($player->getName())){
                    $ice->set($player->getName(), $ice->get($player->getName()) + $int);
                } else {
                    $ice->set($player->getName(), $int);
                }
                $ice->save();
                break;
            case "ender":
                if($ender->exists($player->getName())){
                    $ender->set($player->getName(), $ender->get($player->getName()) + $int);
                } else {
                    $ender->set($player->getName(), $int);
                }
                $ender->save();
                break;
            case "magma":
                if($magma->exists($player->getName())){
                    $magma->set($player->getName(), $magma->get($player->getName()) + $int);
                } else {
                    $magma->set($player->getName(), $int);
                }
                $magma->save();
                break;
            case "pegasus":
                if($pegasus->exists($player->getName())){
                    $pegasus->set($player->getName(), $pegasus->get($player->getName()) + $int);
                } else {
                    $pegasus->set($player->getName(), $int);
                }
                $pegasus->save();
                break;
        }
    }

    public static function removeKeyBox(Player $player, string $type, int $int) {
        $mage = new Config(Main::getInstance()->getDataFolder() . "mage.yml", Config::YAML);
        $ice = new Config(Main::getInstance()->getDataFolder() . "ice.yml", Config::YAML);
        $ender = new Config(Main::getInstance()->getDataFolder() . "ender.yml", Config::YAML);
        $magma = new Config(Main::getInstance()->getDataFolder() . "magma.yml", Config::YAML);
        $pegasus = new Config(Main::getInstance()->getDataFolder() . "pegasus.yml", Config::YAML);
        switch($type){
            case "mage":
                if($mage->exists($player->getName())){
                    $mage->set($player->getName(), $mage->get($player->getName()) - $int);
                }
                $mage->save();
                break;
            case "ice":
                if($ice->exists($player->getName())){
                    $ice->set($player->getName(), $ice->get($player->getName()) - $int);
                }
                $ice->save();
                break;
            case "ender":
                if($ender->exists($player->getName())){
                    $ender->set($player->getName(), $ender->get($player->getName()) - $int);
                }
                $ender->save();
                break;
            case "magma":
                if($magma->exists($player->getName())){
                    $magma->set($player->getName(), $magma->get($player->getName()) - $int);
                }
                $magma->save();
                break;
            case "pegasus":
                if($pegasus->exists($player->getName())){
                    $pegasus->set($player->getName(), $pegasus->get($player->getName()) - $int);
                }
                $pegasus->save();
                break;
        }
    }

    public static function playSound(Player $player, string $sound, int $volume, float $pitch) {
        $packet = new PlaySoundPacket();
        $packet->x = $player->getPosition()->getX();
        $packet->y = $player->getPosition()->getY();
        $packet->z = $player->getPosition()->getZ();
        $packet->soundName = $sound;
        $packet->volume = $volume;
        $packet->pitch = $pitch;
        $player->getNetworkSession()->sendDataPacket($packet);
    }
}
