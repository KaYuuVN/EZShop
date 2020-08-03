<?php

namespace KaYuuVN\ShopKUI;

use pocketmine\{Server, Player};
use pocketmine\utils\Config;
use pocketmine\command\{CommandSender, ConsoleCommandSender, Command};
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use jojoe77777\FormAPI\{CustomForm, SimpleForm, ModalForm};

class Main extends PluginBase implements Listener{
  
public function onEnable(): void{
  $this->getServer()->getPluginManager()->registerEvents($this, $this);
  @mkdir($this->getDataFolder());
  $this->saveDefaultConfig();
  $this->saveResource("shop.yml");
  $this->config = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
  $this->message = $this->config->get("messages");
  $this->shop = $this->config->get("shop");
  $this->money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
}
public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		if(!($sender instanceof Player)){
				$this->getLogger()->notice("Please Dont Use that command in here.");
				return true;
			}
		switch($cmd->getName()){
			case "shop":
			$this->OpenShop($sender);
			break;
	   }
	return true;
	}
	public function OpenShop($player){
	  $form = new SimpleForm(function(Player $player, $data = null){

	  	foreach($this->shop as $i=>$s){
	  	if($data === null){
	  		return true;
	  	}
	  		if($data == $i){$this->Items($player,$i);}
	   }
	  });
	  $form->setTitle($this->message["title"]);
    $form->setContent($this->message["content"]);
	  foreach($this->shop as $i=>$s){
	    $form->addButton($this->shop[$i]["category_name"],$i,$this->shop[$i]["category_image"]);
	  }
	  $player->sendForm($form);
	}
  public function Items($player, $id, $message = null){
    $form = new SimpleForm(function(Player $player, $data = null) use ($id){
    	if($data === null){
    		return $this->OpenShop($player);
    	}
    	foreach($this->shop[$id]['category_item'] as $i=>$item){
    		if($data == explode("-",$i)[1]){
              $this->Cart($player, explode("-",$i)[1]);
    		}
    	}
    });
	  $form->setTitle($this->message["title"]." [".$this->shop[$id]["category_name"]."]");
	  if($message != null){
	  	$form->setContent($message);
	  }else {$form->setContent($this->shop[$id]["category_content"]);}
	  foreach($this->shop[$id]['category_item'] as $i=>$item){
	    $form->addButton($this->shop[$id]['category_item'][$i]['name'],explode("-",$i)[1],$this->shop[$id]['category_item'][$i]['image']);
	  }
	  $player->sendForm($form);
    }
    public function Cart($player, $id){
    	$form = new CustomForm(function(Player $player, $data = null) use ($id){

         if($data[1] != null){
         	if($data[1] <= $this->shop[$id]['category_item']["item-".$id]['max_amount']){
         		if($this->money->myMoney($player) >= $this->shop[$id]['category_item']["item-".$id]['money']){
         			$this->money->reduceMoney($player, $data[1]*$this->shop[$id]['category_item']["item-".$id]['money']);
         			              $player->getInventory()->addItem(Item::get(explode(":", $this->shop[$id]['category_item']["item-".$id]['id'])[0],explode(":",$this->shop[$id]['category_item']["item-".$id]['id'])[1],$data[1]));
         			              $this->Items($player, $id, $this->message['sucess']);
         		}else{$this->Items($player, $id, $this->message['error_money']);}
         	}
         }
    	});
    	$form->setTitle($this->message["title"]." [".$this->shop[$id]["category_name"]."] [".$this->shop[$id]['category_item']["item-".$id]['name']."]");
    	$form->addLabel($this->message['label']);
    	$form->addInput("Amount: ","Max: ".$this->shop[$id]['category_item']["item-".$id]['max_amount'],1);
    	 $player->sendForm($form);
    }
  }