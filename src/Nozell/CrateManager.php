<?php

namespace Nozell;

use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use SaraiCsDev\Animation\AnimationHandler;
use pocketmine\world\Position;
use pocketmine\world\particle\LavaParticle;

class CrateManager {

    private Main $mainPlugin;
    public Config $crateData;

    public function __construct(Main $mainPlugin) {
        $this->mainPlugin = $mainPlugin;
        $this->loadCrates();
    }

    private function loadCrates(): void {
        $this->crateData = new Config($this->mainPlugin->getDataFolder() . "crates.yml", Config::YAML);
    }

    public function saveCrates(): void {
        $this->crateData->save();
    }

    public function createCrate(string $crateLabel, array $crateItems): void {
        $serializedItems = [];

        foreach ($crateItems as $crateItem) {
            $serializedItems[] = $this->serializeItem($crateItem);
        }

        $this->crateData->set($crateLabel, serialize($serializedItems));
        $this->saveCrates();
    }

    public function getCrate(string $crateLabel): array {
        if (!$this->crateData->exists($crateLabel)) {
            var_dump("Crate not found.");
            return [];
        }

        $deserializedData = unserialize($this->crateData->get($crateLabel));
        $itemsList = [];

        foreach ($deserializedData as $itemData) {
            $item = $this->deserializeItem($itemData);
            $itemsList[] = $item;
        }

        return $itemsList;
    }

    public function editCrate(string $crateLabel, array $crateItems): void {
        $serializedItems = [];

        foreach ($crateItems as $crateItem) {
            $serializedItems[] = $this->serializeItem($crateItem);
        }

        $this->crateData->set($crateLabel, serialize($serializedItems));
        $this->saveCrates();
    }

    public function removeCrate(string $crateLabel): void {
        $this->crateData->remove($crateLabel);
        $this->saveCrates();
    }

    public function listCrates(): array {
        return array_keys($this->crateData->getAll());
    }

    public function crateExists(string $crateLabel): bool {
        return $this->crateData->exists($crateLabel);
    }

    public function getRandomItemFromCrate(string $crateLabel, string $playerUsername, object $entityInstance, string $animationFile, string $animationController): void {
        $targetPlayer = $this->mainPlugin->getServer()->getPlayerExact($playerUsername);

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
        $randomItem = $this->deserializeItem($deserializedData[$randomIndex]);
        $playerInventory = $targetPlayer->getInventory();
        $itemLabel = $randomItem->getName();

        if (!$playerInventory->canAddItem($randomItem)) {
            $targetPlayer->getWorld()->dropItem($targetPlayer->getPosition()->asVector3(), $randomItem);
            $targetPlayer->sendMessage(Crates::getInstance()->prefix . TextFormat::colorize(str_replace(["userName", "{itemName}"], [$targetPlayer->getName(), $itemLabel], Main::getInstance()->config->get("won_item"))));
            return;
        }

        $actionsQueue = [
            [
                'actions' => [
                    function(Player $targetPlayer) use ($randomItem, $itemLabel, $playerInventory, $crateLabel, $entityInstance) {
                        $targetPlayer->sendTitle(TextFormat::colorize("&e» You won&8:\n&a» {$itemLabel}"));
                        $playerInventory->addItem($randomItem);
                        Main::PlaySound($targetPlayer, "firework.twinkle", 100, 500);
                        $position = $entityInstance->getPosition();

                        $worldInstance = $entityInstance->getWorld();

                        $worldInstance->addParticle($position, new LavaParticle());

                        $worldInstance->addParticle($position, new LavaParticle());

                        $worldInstance->addParticle($position->add(1, 0, 0), new LavaParticle());

                        $worldInstance->addParticle($position->add(0, 1, 0), new LavaParticle());

                        $worldInstance->addParticle($position->add(0, 0, 1), new LavaParticle());
                        $onlinePlayers = $this->mainPlugin->getServer()->getOnlinePlayers();
                        foreach ($onlinePlayers as $onlinePlayer){
                            $onlinePlayer->sendTip(TextFormat::colorize(str_replace(["{userName}", "{itemName}", "{crateName}"], [$targetPlayer->getName(), $itemLabel, $crateLabel], Main::getInstance()->config->get("won_alert"))));
                        }
                    }
                ]
            ],
            [
                'actions' => [
                    function(Player $targetPlayer) use ($randomItem, $itemLabel, $entityInstance, $animationFile, $animationController) {
                        $targetPlayer->sendTitle(TextFormat::colorize("&e1"), "", 5, 20, 5);
                        Main::PlaySound($targetPlayer, "note.harp", 100, 500);
                    }
                ]
            ],
            [
                'actions' => [
                    function(Player $targetPlayer) use ($randomItem, $itemLabel, $entityInstance, $animationFile, $animationController) {
                        $targetPlayer->sendTitle(TextFormat::colorize("&g2"), "", 5, 20, 5);
                        Main::PlaySound($targetPlayer, "note.harp", 100, 500);
                    }
                ]
            ],
            [
                'actions' => [
                    function(Player $targetPlayer) use ($randomItem, $itemLabel, $entityInstance, $animationFile, $animationController) {
                        $targetPlayer->sendTitle(TextFormat::colorize("&63"), "", 5, 20, 5);
                        AnimationHandler::playAnimationForAll($entityInstance, $animationFile, $animationController, "play");
                        Main::PlaySound($targetPlayer, "note.harp", 100, 500);
                    }
                ]
            ]
        ];

        $pluginScheduler = Main::getInstance()->getScheduler();
        $pluginScheduler->scheduleRepeatingTask(new CooldownTask($targetPlayer, $actionsQueue), 20);
    }

    public function serializeItem(Item $item): string {
        $nbtHandler = new BigEndianNbtSerializer();
        $serializedData = $nbtHandler->write(new TreeRoot($item->nbtSerialize()));
    
        return $this->sanitizeString($serializedData);
    }
    
    public function deserializeItem(string $data): Item {
        $cleanData = $this->sanitizeString($data);
    
        if (!mb_check_encoding($cleanData, "UTF-8")) {
            var_dump("Invalid Serialized Item");
            return VanillaItems::AIR();
        }
    
        $nbtHandler = new BigEndianNbtSerializer();
        return Item::nbtDeserialize($nbtHandler->read($cleanData)->mustGetCompoundTag());
    }

    private function sanitizeString(string $data): string {
        return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
    }
    
    public function cleanMessage(string $message): string {
        $bannedWords = Crates::getInstance()->config->getNested("banned_list", []);
        return str_replace($bannedWords, '', $message);
    }
    
    public function getAllItemsFromCrate(string $crateLabel): array {
        
        if (!$this->crateData->exists($crateLabel)) {
            var_dump("Crate not found");
            return [];
        }

        $deserializedData = unserialize($this->crateData->get($crateLabel));
        $itemsList = [];

        foreach ($deserializedData as $itemData) {
            $item = $this->deserializeItem($itemData);
            $itemsList[] = $item;
        }

        return $itemsList;
    }
}
