<?php

namespace Nozell\translate;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use rxduz\core\Main;

class Translator {
    use SingletonTrait;

    private const EMPTY_MESSAGE = TextFormat::RED . "This message does not exist";
    private array $messages = [];

    public function init(): void {
        $this->loadMessages();
    }

    public function getMessage(string $key, array $replace = []): string {
        $message = $this->messages[$key] ?? self::EMPTY_MESSAGE;
        return $this->replacePlaceholders($message, $replace);
    }

    private function loadMessages(): void {
        $configPath = Main::getInstance()->getDataFolder() . "/messages.yml";
        $config = new Config($configPath, Config::YAML);
        $this->messages = $config->getAll();
    }

    private function replacePlaceholders(string $message, array $replace): string {
        foreach ($replace as $placeholder => $value) {
            $message = str_replace($placeholder, (string) $value, $message);
        }
        return TextFormat::colorize($message);
    }
}