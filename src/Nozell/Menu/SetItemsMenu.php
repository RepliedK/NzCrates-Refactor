<?php
    
namespace Nozell\Menu;

use pocketmine\player\Player;
use pocketmine\Server;
use Nozell\Main;
use Vecnavium\FormsUI\CustomForm;
use muqsit\invmenu\InvMenu;

use pocketmine\item\Item;
use pocketmine\inventory\Inventory;
use pocketmine\utils\TextFormat;

class SetItemsMenu {

    public static function open(Player $player): void {
        $form = new CustomForm(function (Player $player, ?array $data) {
            if ($data === null) {
                return;
            }

            [$crateTypeIndex] = $data;

            $crateTypes = ["mage", "ice", "ender", "magma", "pegasus"];
            $crateType = $crateTypes[$crateTypeIndex] ?? null;

            if ($crateType === null) {
                $player->sendMessage(TextFormat::RED . "Datos inválidos proporcionados.");
                return;
            }

            self::openCrateMenu($player, $crateType);
        });

        $form->setTitle("Definir Items para Crate");
        $form->addDropdown("Selecciona el tipo de crate", ["Mage", "Ice", "Ender", "Magma", "Pegasus"]);

        $player->sendForm($form);
    }

    public static function openCrateMenu(Player $player, string $crateType): void {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName("Crate: " . ucfirst($crateType));

        $crateManager = Main::getInstance()->getCrateManager();
        $items = $crateManager->getAllItemsFromCrate($crateType);

        $inventory = $menu->getInventory();
        foreach ($items as $item) {
            $inventory->addItem($item);
        }

        $menu->setInventoryCloseListener(function (Player $player, Inventory $inventory) use ($crateType): void {
            $crateManager = Main::getInstance()->getCrateManager();
            $crateItems = [];

            foreach ($inventory->getContents() as $item) {
                $crateItems[] = $item;
            }

            $crateManager->createCrate($crateType, $crateItems);
            $player->sendMessage(TextFormat::GREEN . "Items guardados en el crate '$crateType'.");
        });

        $menu->send($player);
    }
}
