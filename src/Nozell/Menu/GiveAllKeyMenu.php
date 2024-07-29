<?php

namespace Nozell\Menu;

use pocketmine\player\Player;
use pocketmine\Server;
use Nozell\Main;
use Vecnavium\FormsUI\CustomForm;

class GiveAllKeyMenu {

    public static function open(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) {
                return;
            }

            [$keyTypeIndex, $amount] = $data;

            $keyTypes = ["mage", "ice", "ender", "magma", "pegasus"];
            $keyType = $keyTypes[$keyTypeIndex] ?? null;

            if ($keyType === null || !is_numeric($amount) || $amount <= 0) {
                $player->sendMessage("§cDatos inválidos proporcionados.");
                return;
            }

            foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                Main::addKeyType($onlinePlayer, $keyType, (int)$amount);
                $onlinePlayer->sendMessage("§bHas recibido §e{$amount} keys de tipo {$keyType}");
            }

            $player->sendMessage("§aHas dado exitosamente §e{$amount} keys de tipo {$keyType} §aa todos los jugadores en línea.");
        });

        $form->setTitle("Dar Keys a Todos");
        $form->addDropdown("Selecciona el tipo de key", ["Mage", "Ice", "Ender", "Magma", "Pegasus"]);
        $form->addInput("Cantidad", "Ingresa la cantidad de keys");

        $player->sendForm($form);
    }
}