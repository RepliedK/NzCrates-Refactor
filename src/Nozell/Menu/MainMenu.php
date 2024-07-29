<?php

namespace Nozell\Menu;

use pocketmine\player\Player;
use Vecnavium\FormsUI\SimpleForm;

class MainMenu {

    public static function open(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) {
                return;
            }

            switch ($data) {
                case 0:
                    if ($player->hasPermission("box.give.all")) {
                        GiveAllKeyMenu::open($player);
                    } else {
                        $player->sendMessage("§cNo tienes permiso para usar esta opción.");
                    }
                    break;
                case 1:
                    if ($player->hasPermission("box.give")) {
                        GiveKeyMenu::open($player);
                    } else {
                        $player->sendMessage("§cNo tienes permiso para usar esta opción.");
                    }
                    break;
                case 2:
                    if ($player->hasPermission("keys.info")) {
                        KeyMenu::open($player);
                    } else {
                        $player->sendMessage("§cNo tienes permiso para usar esta opción.");
                    }
                    break;
                case 3:
                    if ($player->hasPermission("box.spawn")) {
                        SetItemsMenu::open($player);
                    } else {
                        $player->sendMessage("§cNo tienes permiso para usar esta opción.");
                    }
                    break;
                case 4:
                    if ($player->hasPermission("box.spawn")) {
                        SpawnBoxMenu::open($player);
                    } else {
                        $player->sendMessage("§cNo tienes permiso para usar esta opción.");
                    }
                    break;
            }
        });

        $form->setTitle("§l§6Main Menu");
        $form->setContent("§eSelecciona una opción:");

        if ($player->hasPermission("box.give.all")) {
            $form->addButton("§bGive All Keys");
        } else {
            $form->addButton("§7Give All Keys\n§cBloqueado");
        }
        if ($player->hasPermission("box.give")) {
            $form->addButton("§aGive Key");
        } else {
            $form->addButton("§7Give Key\n§cBloqueado");
        }
        if ($player->hasPermission("keys.info")) {
            $form->addButton("§dView Keys");
        } else {
            $form->addButton("§7View Keys\n§cBloqueado");
        }
        if ($player->hasPermission("box.spawn")) {
            $form->addButton("§cSet Items for Crate");
        } else {
            $form->addButton("§7Set Items for Crate\n§cBloqueado");
        }
        if ($player->hasPermission("box.spawn")) {
            $form->addButton("§6Spawn Crate");
        } else {
            $form->addButton("§7Spawn Crate\n§cBloqueado");
        }

        $player->sendForm($form);
    }
}
