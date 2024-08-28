<?php

declare(strict_types=1);

namespace Nozell\Crates\Meetings;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use Nozell\Crates\Utils\CratesUtils;
use Nozell\Crates\Data\DataCrates;

final class Meeting {
    
    private DataCrates $DataCrates;
    
    public function __construct(
        private readonly Player $player) {
        $this->DataCrates = new DataCrates($this);
    }

    public function getDataCrates(): DataCrates {
        return $this->DataCrates;
    }

    public function getPlayer(): Player {
        return $this->player;
    }

    public function getXuid(): string {
        return $this->player->getXuid();
    }

    public function join(): void {
        
        $player = $this->player;
        $player->sendMessage(TextFormat::colorize('&aCargando Tus Datos ...'));
        $this->DataCrates->setKeyMage(CratesUtils:: getKeyBox($player, "mage"));
        $this->DataCrates->setKeyIce(CratesUtils:: getKeyBox($player, "ice"));
        $this->DataCrates->setKeyEnder(CratesUtils:: getKeyBox($player, "ender"));
        $this->DataCrates->setKeyMagma(CratesUtils:: getKeyBox($player, "magma"));
        $this->DataCrates->setKeyPegasus(CratesUtils:: getKeyBox($player, "pegasus"));
        $player->sendMessage(TextFormat::colorize('&aDatos cargados correctamente'));
        
    }

    public function Close(bool $onClose = false): void {
        $player = $this->player; 
        CratesUtils::setKeyBox($player, "mage", $this->DataCrates->getKeyMage());
        CratesUtils::setKeyBox($player, "ice", $this->DataCrates->getKeyIce());
        CratesUtils::setKeyBox($player, "ender", $this->DataCrates->getKeyEnder());
        CratesUtils::setKeyBox($player, "magma", $this->DataCrates->getKeyMagma());
        CratesUtils::setKeyBox($player, "pegasus", $this->DataCrates->getKeyPegasus());
        
MeetingManager::getInstance()->removeMeeting($player);
    }
}