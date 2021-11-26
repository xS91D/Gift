<?php

namespace Gift;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\command\{Command, CommandSender, ConsoleCommandSender};
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;

class Main extends PluginBase implements Listener
{

    public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this,$this);
    $this->getLogger()->info("§bGift_v1.0.0");
    @mkdir($this->getDataFolder()."/Items/",0777,true);
    //福袋數據庫
    $this->g = new Config($this->getDataFolder()."Gifts.yml",Config::YAML,array());
}

   public function onJoin(PlayerJoinEvent $event){
   $name = strtolower($event->getPlayer()->getName());
   if(!file_exists($this->getDataFolder()."/Items/".$name.".yml")){
   $gift = new Config($this->getDataFolder()."/Items/".$name.".yml",Config::YAML,array());
   $gift->set("物品欄",array());
   $gift->save();
   }
}


  public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
  switch($command->getName()){

  case "gift":
  if(!isset($args[0])){
  $sender->sendMessage("§b- /gift me -- 查看禮物庫存");
  $sender->sendMessage("§b- /gift use -- 打開所有擁有的禮物");
  if($sender->isOp() ){
  $sender->sendMessage("§c- /gift give <ID> <禮物> -- 給予玩家禮物(OP)");
  $sender->sendMessage("§c- /gift giveall -- 給予線上玩家禮物(OP)");
}
  return true;
}
  switch($args[0]){

  case "me":
  $gift = new Config($this->getDataFolder()."/Items/".strtolower($sender->getName()).".yml",Config::YAML,array());
  $wp = $gift->get("物品欄");
  $wps = implode(",",$wp);
  $sender->sendMessage("§e>>> 你的福袋欄目前擁有: §6{$wps}");
  return true;

  case "give"://給予禮包
  if( $sender->isOp() || $sender->getName() == "CONSOLE"){
  if(isset($args[1]) && isset($args[2])){
  $gift = new Config($this->getDataFolder()."/Items/".strtolower($args[1]).".yml",Config::YAML,array());
  $wp = $gift->get("物品欄");
  $wp[] = $args[2];
  if($this->g->exists($args[2])){
  $gift->set("物品欄",$wp);
  $gift->save();
  $sender->sendMessage("§e>>> 成功贈送§a{$args[1]}§b福袋>>§6{$args[2]}");
  }else{
  $sender->sendMessage("§c!!! 不存在此福袋 !!!");
  }
  }else{
  $sender->sendMessage("§e> /gift give <玩家名> <福袋名>");
  }
  }else{
  $sender->sendMessage("§c!!! 權限不足 !!!");
  }
  break;
  
  case "giveall"://給予所有在線玩家
  if( $sender->isOp() || $sender->getName() == "CONSOLE"){
  if(isset($args[1])){
  foreach($this->getServer()->getOnlinePlayers() as $ps){
  $gift = new Config($this->getDataFolder()."/Items/".strtolower($ps->getName()).".yml",Config::YAML,array());
  $wp = $gift->get("物品欄");
  $wp[] = $args[1];
  if($this->g->exists($args[1])){
  $gift->set("物品欄",$wp);
  $gift->save();
  $sender->sendMessage("§e>>> 成功贈送§a所有在線玩家§b福袋>>§6{$args[1]}");
  $this->getServer()->broadcastMessage("§c>>> 系統提示 §a玩家§b{$sender->getName()}§a給所有在線玩家發送了福袋>>§f{$args[1]}");
  }else{
  $sender->sendMessage("§c!!! 不存在此福袋 !!!");
}
   }
}else{
   $sender->sendMessage("§e> /gift giveall <福袋名>");
   }
}else{
   $sender->sendMessage("§c!!! 權限不足 !!!");
}
   break;
    
   case "use": 
   $gift = new Config($this->getDataFolder() . "/Items/" . strtolower($sender->getName()) . ".yml", Config::YAML, array());
   $wp = $gift->get("物品欄");
   foreach ($wp as $GiftName){
   $cmd =  $this->g->get($GiftName)["指令"];
   $cmd = str_replace("%p",$sender->getName(),$cmd);
   foreach ($cmd as $v){
   $sender->sendMessage("§e>>> 你已開啟{$GiftName}");
   $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $v);
   $gift->set("物品欄",[]);
   $gift->save();
   }
}
    break;

    }

   }
    return true;
} 
   
    public function onDisable(){
    $this->getLogger()->info("Gift_v1.0.0 Disabled"); 
    }

}


