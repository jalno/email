<?php
namespace packages\email\events;
use \packages\base\event;
use \packages\email\events\senders\sender;
class senders extends event{
	private $senders = array();
	public function addSender(sender $sender){
		$this->senders[$sender->getName()] = $sender;
	}
	public function getSenderNames(){
		return array_keys($this->senders);
	}
	public function getByName($name){
		return (isset($this->senders[$name]) ? $this->senders[$name] : null);
	}
	public function getByHandler($handler){
		foreach($this->senders as $sender){
			if($sender->getHandler() == $handler){
				return $sender;
			}
		}
		return null;
	}
	public function get(){
		return $this->senders;
	}
}
