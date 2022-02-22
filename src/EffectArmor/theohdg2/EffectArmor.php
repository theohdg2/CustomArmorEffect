<?php

namespace EffectArmor\theohdg2;

use pocketmine\command\defaults\EffectCommand;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\Limits;

class EffectArmor extends PluginBase
{
    protected function onLoad(): void
    {
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->getScheduler()->scheduleRepeatingTask(new class($this->getDataFolder()."config.yml") extends Task{
            private Config $config;
            public function __construct(string $config){
                $this->config = new Config($config);
            }

            public function onRun(): void{
                foreach ($this->config->get("armor") as $data){
                    $armor[$data["id"]]["effect"]= (StringToEffectParser::getInstance()->parse(strtolower(explode(":",$data["effect"] ?? throw new \Exception("EffectArmor is bad configured"))[0])));
                    $armor[$data["id"]]["amplifier"] = explode(":", $data["effect"])[1];
                    $armor[$data["id"]]["visible"] = $data["visible"] ?? false;
                    $armor[$data["id"]]["effect"] !== null ?: throw new \Exception("effect bad configurate");
                }
                foreach (Server::getInstance()->getOnlinePlayers() as $player){
                    foreach ($player->getArmorInventory()->getContents(false) as $armors){
                        if(isset($armor[$armors->getId()])){
                            $player->getEffects()->add(new EffectInstance($armor[$armors->getId()]["effect"],210,$armor[$armors->getId()]["amplifier"],$armor[$armors->getId()]["visible"]));
                        }
                    }

                }
            }
        },2);
    }

}