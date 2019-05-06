<?php

namespace SkyBlock;

use Kabluinc\SkyBlockInvSaver\SkyBlockInvSaver;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use SkyBlock\chat\Chat;
use SkyBlock\island\Island;

use pocketmine\math\Vector3;

use pocketmine\tile\Chest;

use pocketmine\tile\Tile;
class SkyBlockListener implements Listener {

    /** @var SkyBlock */
    private $plugin;

    /**
     * EventListener constructor.
     *
     * @param SkyBlock $plugin
     */
    public function __construct(SkyBlock $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    /**
     * Try to register a player
     *
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event) {
        $this->plugin->getSkyBlockManager()->tryRegisterUser($event->getPlayer());
    }

    /**
     * Executes onJoin actions
     *
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event) {
        $this->plugin->getIslandManager()->checkPlayerIsland($event->getPlayer());
    }

    /**
     * Executes onLeave actions
     *
     * @param PlayerQuitEvent $event
	 * @priority HIGHEST
     */
    public function onLeave(PlayerQuitEvent $event) {
		$api = $this->plugin->getServer()->getPluginManager()->getPlugin("SkyBlockInvSaver");
		$player = $event->getPlayer();
		if (strpos($player->getLevel()->getName(), 'skyblock') !== false){
		SkyBlockInvSaver::getInstance()->storeInventory($event->getPlayer(), $player->getLevel());
		}

        $this->plugin->getIslandManager()->unloadByPlayer($event->getPlayer());
    }
    public function addItemMultipleTimes($times, Item $item, array &$array){
        for($i = 0; $i <= $times; $i++) {
            $array[] = $item;
        }
        return $array;
    }

    /**

     * @param ChunkLoadEvent $event

     */

    public function onChunkLoad(ChunkLoadEvent $event): void {

        $level = $event->getLevel();

        if(strpos($level->getName(), 'skyblock') == false) {

            return;

        }

        /** @var IsleGenerator $generator */

        $position = new Vector3(10, 6, 4);

        if($level->getChunk($position->x >> 4, $position->z >> 4) === $event->getChunk() and $event->isNewChunk()) {

            /** @var Chest $chest */

            $chest = Tile::createTile(Tile::CHEST, $level, Chest::createNBT($position));
        $inventory = $chest->getInventory();
        $inventory->addItem(Item::get(Item::WATER, 0, 2));
        $inventory->addItem(Item::get(Item::LAVA, 0, 1));
        $inventory->addItem(Item::get(Item::ICE, 0, 2));
        $inventory->addItem(Item::get(Item::MELON_BLOCK, 0, 1));
        $inventory->addItem(Item::get(Item::BONE, 0, 1));
        $inventory->addItem(Item::get(Item::PUMPKIN_SEEDS, 0, 1));
        $inventory->addItem(Item::get(Item::CACTUS, 0, 1));
        $inventory->addItem(Item::get(Item::SUGARCANE, 0, 1));
        $inventory->addItem(Item::get(Item::BREAD, 0, 1));
        $inventory->addItem(Item::get(Item::WHEAT, 0, 1));
        $inventory->addItem(Item::get(Item::LEATHER_BOOTS, 0, 1));
        $inventory->addItem(Item::get(Item::LEATHER_PANTS, 0, 1));
        $inventory->addItem(Item::get(Item::LEATHER_TUNIC, 0, 1));
        $inventory->addItem(Item::get(Item::LEATHER_CAP, 0, 1));

        }

    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event) {
        $island = $this->plugin->getIslandManager()->getOnlineIsland($event->getPlayer()->getLevel()->getName());
        if($island instanceof Island) {
            if(!$event->getPlayer()->hasPermission("skyblock.edit.others") and !in_array(strtolower($event->getPlayer()->getName()), $island->getAllMembers())) {
                $event->getPlayer()->sendPopup(TextFormat::RED . "You must be part of this island to break here!");
                $event->setCancelled();
            }
            else  {
								    $p = $event->getPlayer();
					foreach($event->getDrops() as $drop) {
			$id = $drop->getId();
			
			if($this->isInventoryFull($p) == true) {
				$p->sendPopup("§cYour inventory is full!");
				return true;
				} else {
			  				$p->getInventory()->addItem(Item::get($id));
							$event->setDrops([]);
				}
                if($event->getBlock()->getId() == Block::COBBLESTONE) {
                    $items = [];
                    $items[] = Item::get(264);
                    $this->addItemMultipleTimes(3, Item::get(265), $items);
                    $this->addItemMultipleTimes(10, Item::get(266), $items);
                    $this->addItemMultipleTimes(20, Item::get(Item::LAPIS_ORE), $items);
                    $this->addItemMultipleTimes(40, Item::get(Item::COAL), $items);
                    $this->addItemMultipleTimes(74, Item::get(Item::COBBLESTONE), $items);
                    $event->setDrops([$items[array_rand($items)]]);
					    $p = $event->getPlayer();
					foreach($event->getDrops() as $drop) {
			$id = $drop->getId();
			  				$p->getInventory()->addItem(Item::get($id));
					  		$event->setDrops([]);
                }
            }
        }
    }
		}
	}
	   public function isInventoryFull(Player $player){
   for($i = 0; $i < $player->getInventory()->getSize(); $i++){
    	if($player->getInventory()->getItem($i)->getId() === 0){
      	return false;
       }
     }
     return true;
  }
  

    /**
     * @param BlockPlaceEvent $event
     */
    public function onPlace(BlockPlaceEvent $event) {
        $island = $this->plugin->getIslandManager()->getOnlineIsland($event->getPlayer()->getLevel()->getName());
        if($island instanceof Island) {
            if(!$event->getPlayer()->hasPermission("skyblock.edit.others") and !in_array(strtolower($event->getPlayer()->getName()), $island->getAllMembers())) {
                $event->getPlayer()->sendPopup(TextFormat::RED . "You must be part of this island to place here!");
                $event->setCancelled();
            }
        }
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onInteract(PlayerInteractEvent $event) {
        $island = $this->plugin->getIslandManager()->getOnlineIsland($event->getPlayer()->getLevel()->getName());
        if($island instanceof Island) {
            if(!$event->getPlayer()->isOp() and !in_array(strtolower($event->getPlayer()->getName()), $island->getAllMembers())) {
                $event->getPlayer()->sendPopup(TextFormat::RED . "You must be part of this island to place here!");
                $event->setCancelled();
            }
        }
    }

    /**
     * Tries to remove a player on change event
     *
     * @param EntityLevelChangeEvent $event
     */
    public function onLevelChange(EntityLevelChangeEvent $event) {
        $entity = $event->getEntity();
        if($entity instanceof Player) {
            if($this->plugin->getIslandManager()->isOnlineIsland($event->getOrigin()->getName())) {
                $this->plugin->getIslandManager()->getOnlineIsland($event->getOrigin()->getName())->tryRemovePlayer($entity);
            }
            else if($this->plugin->getIslandManager()->isOnlineIsland($event->getTarget()->getName())) {
                $this->plugin->getIslandManager()->getOnlineIsland($event->getTarget()->getName())->addPlayer($entity);
            }
        }
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event) {
        $chat = $this->plugin->getChatHandler()->getPlayerChat($event->getPlayer());
        if($chat instanceof Chat) {
            $recipients = $event->getRecipients();
            foreach($recipients as $key => $recipient) {
                if($recipient instanceof Player) {
                    if(!in_array($recipient, $chat->getMembers())) {
                        unset($recipients[$key]);
                    }
                }
            }
        }
        else {
            $recipients = $event->getRecipients();
            foreach($recipients as $key => $recipient) {
                if($recipient instanceof Player) {
                    if($this->plugin->getChatHandler()->isInChat($recipient)) {
                        unset($recipients[$key]);
                    }
                }
            }
        }
        $event->setRecipients($recipients);
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onHurt(EntityDamageEvent $event) {
        if($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            if($entity instanceof Player) {
                if($this->plugin->getIslandManager()->isOnlineIsland($entity->getLevel()->getName())) {
                    $event->setCancelled();
                }
            }
        }
    }

    /**
     * @param LevelUnloadEvent $event
     */
    public function onUnloadLevel(LevelUnloadEvent $event) {
        foreach($event->getLevel()->getPlayers() as $player) {
            $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
        }
    }

}
