<?php

namespace SkyBlock;

use pocketmine\scheduler\Task;

class PluginHearbeat extends Task {

    /** @var int */
    private $nextUpdate = 0;

    /**
     * PluginHearbeat constructor.
     *
     * @param SkyBlock $owner
     */
    public function __construct(SkyBlock $owner) {
		$this->plugin = $owner;
    }

    public function onRun(Int $currentTick) {
        $this->nextUpdate++;
        /** @var SkyBlock $owner */
        $owner = $this->plugin;
        if($this->nextUpdate == 120) {
            $this->nextUpdate = 0;
            $owner->getIslandManager()->update();
        }
        $owner->getInvitationHandler()->tick();
        $owner->getResetHandler()->tick();
    }

}