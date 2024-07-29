<?php

namespace Nozell\Menu;

use pocketmine\player\Player;
use Nozell\Main;
use Vecnavium\FormsUI\SimpleForm;

class KeyMenu {

    public static function open(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            // No se necesita manejo de la respuesta ya que solo estamos mostrando información
        });

        $mage = Main::getKeyType($player, "mage");
        $ice = Main::getKeyType($player, "ice");
        $ender = Main::getKeyType($player, "ender");
        $magma = Main::getKeyType($player, "magma");
        $pegasus = Main::getKeyType($player, "pegasus");

        $content = "§bTienes actualmente:\n" .
                   "- §e{$mage}§f Mage\n" .
                   "- §e{$ice}§f Ice\n" .
                   "- §e{$ender}§f Ender\n" .
                   "- §e{$magma}§f Magma\n" .
                   "- §e{$pegasus}§f Pegasus";

        $form->setTitle("Tus Keys");
        $form->setContent($content);
        $form->addButton("Cerrar");

        $player->sendForm($form);
    }
}