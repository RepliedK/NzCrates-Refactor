<?php

namespace Nozell\Menu;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Skin;
use pocketmine\lang\Translatable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use Nozell\Entity\MageBoxEntity;
use Nozell\Entity\EnderBoxEntity;
use Nozell\Entity\PegasusBoxEntity;
use Nozell\Entity\IceBoxEntity;
use Nozell\Entity\MagmaBoxEntity;
use Nozell\Main;
use Vecnavium\FormsUI\CustomForm;

class SpawnBoxMenu {

    public static function open(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) {
                return;
            }

            [$crateTypeIndex] = $data;

            $crateTypes = ["mage", "ice", "ender", "magma", "pegasus"];
            $crateType = $crateTypes[$crateTypeIndex] ?? null;

            if ($crateType === null) {
                $player->sendMessage("§cDatos inválidos proporcionados.");
                return;
            }

            switch ($crateType) {
                case "mage":
                    new MageBoxEntity($player->getLocation(), new CompoundTag());
                    break;
                case "ice":
                    new IceBoxEntity($player->getLocation(), new CompoundTag());
                    break;
                case "ender":
                    new EnderBoxEntity($player->getLocation(), new CompoundTag());
                    break;
                case "magma":
                    new MagmaBoxEntity($player->getLocation(), new CompoundTag());
                    break;
                case "pegasus":
                    new PegasusBoxEntity($player->getLocation(), new CompoundTag());
                    break;
            }

            $player->sendMessage("§aCrate '$crateType' spawneada en tu ubicación actual.");
        });

        $form->setTitle("Spawnear Crate");
        $form->addDropdown("Selecciona el tipo de crate", ["Mage", "Ice", "Ender", "Magma", "Pegasus"]);

        $player->sendForm($form);
    }
}