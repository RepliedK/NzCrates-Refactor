<?php

namespace Nozell\Crates\Utils;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;

trait SoundEffect {
    public static function playSound(Player $player, string $sound, int $volume, float $pitch): void {
        $packet = new PlaySoundPacket();
        $position = $player->getPosition();
        $packet->x = $position->getX();
        $packet->y = $position->getY();
        $packet->z = $position->getZ();
        $packet->soundName = $sound;
        $packet->volume = $volume;
        $packet->pitch = $pitch;
        $player->getNetworkSession()->sendDataPacket($packet);
    }
}