<?php

namespace Nozell\Menu;

use JsonException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\Server;
use Nozell\Main;
use Vecnavium\FormsUI\CustomForm;

class GiveKeyMenu {

    public static function open(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) {
                return;
            }

            [$keyTypeIndex, $amount, $targetIndex] = $data;

            $keyTypes = ["mage", "ice", "ender", "magma", "pegasus"];
            $keyType = $keyTypes[$keyTypeIndex] ?? null;

            $onlinePlayers = array_values(Server::getInstance()->getOnlinePlayers());
            $targetPlayer = $onlinePlayers[$targetIndex] ?? null;

            if ($keyType === null || !is_numeric($amount) || $amount <= 0 || $targetPlayer === null) {
                $player->sendMessage("§cDatos inválidos proporcionados.");
                return;
            }

            Main::addKeyType($targetPlayer, $keyType, (int)$amount);
            $targetPlayer->sendMessage("§bHas recibido §e{$amount} keys de tipo {$keyType}");
            $player->sendMessage("§aHas dado exitosamente §e{$amount} keys de tipo {$keyType} §aa {$targetPlayer->getName()}.");
        });

        $onlinePlayers = array_map(fn(Player $p) => $p->getName(), array_values(Server::getInstance()->getOnlinePlayers()));

        $form->setTitle("Dar Key");
        $form->addDropdown("Selecciona el tipo de key", ["Mage", "Ice", "Ender", "Magma", "Pegasus"]);
        $form->addInput("Cantidad", "Ingresa la cantidad de keys");
        $form->addDropdown("Selecciona el jugador", $onlinePlayers);

        $player->sendForm($form);
    }
}