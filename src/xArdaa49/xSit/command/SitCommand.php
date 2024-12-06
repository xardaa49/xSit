<?php
/***
 *                 .oooooo..o  o8o      .
 *                d8P'    `Y8  `"'    .o8
 *    oooo    ooo Y88bo.      oooo  .o888oo
 *     `88b..8P'   `"Y8888o.  `888    888
 *       Y888'         `"Y88b  888    888
 *     .o8"'88b   oo     .d8P  888    888 .
 *    o88'   888o 8""88888P'  o888o   "888"
 *
 *
 *    @name xSit
 *    @author xArdaa49
 *    @version 1.0.0
 */
namespace xArdaa49\xSit\command;

use pocketmine\{command\Command, command\CommandSender, player\Player, utils\TextFormat};
use xArdaa49\xSit\xSit;

class SitCommand extends Command {
    /**
     * @var xSit $pluginSit
     */
    private xSit $pluginSit;

    /**
     * @param xSit $pluginSit
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(xSit $pluginSit, string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->pluginSit = $pluginSit;
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("xsit.permission.use");
        $this->setPermissionMessage("");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) return false;

        if ($this->pluginSit->isPlayerSiting($sender)) {
            $this->pluginSit->removeSitPlayer($sender);
        } else {
            $this->pluginSit->sitPlayer($sender, $sender->getWorld()->getBlock($sender->getPosition()->add(0, -0.5, 0)));
        }
    }
}
