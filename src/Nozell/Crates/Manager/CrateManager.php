<?php

namespace Nozell\Crates\Manager;

use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\entity\Entity;
use Nozell\Crates\Utils\CooldownTask;
use Nozell\Crates\Utils\ItemSerializer;
use Nozell\Crates\Utils\LavaParticleEffect;
use Nozell\Crates\Utils\SoundEffect;
use pocketmine\scheduler\TaskScheduler;

class CrateManager {
    use LavaParticleEffect;
    use SoundEffect;

    public Config $crateData;

    public function __construct() {
        $this->loadCrates();
    }

    private function loadCrates(): void {
        $this->crateData = new Config(Main::getInstance()->getDataFolder() . "crates.yml", Config::YAML);
    }

    public function saveCrates(): void {
        $this->crateData->save();
    }

    public function getCrate(string $crateLabel): array {
        if (!$this->crateData->exists($crateLabel)) {
            var_dump("Crate not found.");
            return [];
        }

        $deserializedData = unserialize($this->crateData->get($crateLabel));
        $itemsList = [];

        foreach ($deserializedData as $itemData) {
            $item = ItemSerializer::deserialize($itemData);
            $itemsList[] = $item;
        }

        return $itemsList;
    }

    public function addCrateItems(string $crateLabel, array $crateItems): void {
        $serializedItems = [];

        foreach ($crateItems as $crateItem) {
            $serializedItems[] = ItemSerializer::serialize($crateItem);
        }

        $this->crateData->set($crateLabel, serialize($serializedItems));
        $this->saveCrates();
    }

    public function crateExists(string $crateLabel): bool {
        return $this->crateData->exists($crateLabel);
    }

    public function getRandomItemFromCrate(string $crateLabel, string $name, Entity $entity): void {
        $targetPlayer = Main::getInstance()->getServer()->getPlayerExact($name);

        if (!$targetPlayer instanceof Player) {
            var_dump("Player Not Found");
            return;
        }

        if (!$this->crateData->exists($crateLabel)) {
            var_dump("Crate not found");
            return;
        }

        $deserializedData = unserialize($this->crateData->get($crateLabel));
        $randomIndex = array_rand($deserializedData);
        $randomItem = ItemSerializer::deserialize($deserializedData[$randomIndex]);
        $playerInventory = $targetPlayer->getInventory();
        $itemLabel = $randomItem->getName();

        if (!$playerInventory->canAddItem($randomItem)) return;

        $actionsQueue = [
            [
                'actions' => [
                    function(Player $targetPlayer) use ($randomItem, $itemLabel, $playerInventory, $crateLabel, $entity) {
                        $targetPlayer->sendMessage(TextFormat::colorize("&e» You won &a» {$itemLabel}"));
                        $playerInventory->addItem($randomItem);
                        self::playSound($targetPlayer, "firework.twinkle", 100, 500);

                        self::addLavaParticles($entity->getWorld(), $entity->getPosition());

                        $onlinePlayers = Main::getInstance()->getServer()->getOnlinePlayers();
                        foreach ($onlinePlayers as $onlinePlayer) {
                            $onlinePlayer->sendTip(TextFormat::colorize(str_replace(["{userName}", "{itemName}", "{crateName}"], [$targetPlayer->getName(), $itemLabel, $crateLabel], Main::getInstance()->config->get("won_alert"))));
                        }
                    }
                ]
            ],
            [
                'actions' => [
                    function(Player $targetPlayer) use ($randomItem, $itemLabel, $entity) {
                        $targetPlayer->sendTitle(TextFormat::colorize("&e1"), "", 5, 20, 5);
                        self::playSound($targetPlayer, "note.harp", 100, 500);
                    }
                ]
            ],
            [
                'actions' => [
                    function(Player $targetPlayer) use ($randomItem, $itemLabel, $entity) {
                        $targetPlayer->sendTitle(TextFormat::colorize("&g2"), "", 5, 20, 5);
                        self::playSound($targetPlayer, "note.harp", 100, 500);
                    }
                ]
            ],
            [
                'actions' => [
                    function(Player $targetPlayer) use ($randomItem, $itemLabel, $entity) {
                        $targetPlayer->sendTitle(TextFormat::colorize("&63"), "", 5, 20, 5);
                        self::playSound($targetPlayer, "note.harp", 100, 500);
                    }
                ]
            ]
        ];

        $pluginScheduler = Main::getInstance()->getScheduler();
        $pluginScheduler->scheduleRepeatingTask(new CooldownTask($targetPlayer, $actionsQueue), 20);
    }

    public function getCrateItems(string $crateLabel): array {
        if (!$this->crateData->exists($crateLabel)) {
            var_dump("Crate not found");
            return [];
        }

        $deserializedData = unserialize($this->crateData->get($crateLabel));
        $itemsList = [];

        foreach ($deserializedData as $itemData) {
            $item = ItemSerializer::deserialize($itemData);
            $itemsList[] = $item;
        }

        return $itemsList;
    }
}
