<?php
namespace packages\email\events;
use \packages\base\events;
use \packages\base\event;
use \packages\email\template;
class templates extends event{
	private $templates = array();
	public function addTemplate(template $template){
		$this->templates[$template->name] = $template;
	}
	public function getTemplateNames(){
		return array_keys($this->templates);
	}
	public function getByName($name){
		return (isset($this->templates[$name]) ? $this->templates[$name] : null);
	}
	public function get(){
		if(!$this->templates){
			$this->trigger();
		}
		return $this->templates;
	}
	public function trigger(){
		events::trigger($this);
	}
}
