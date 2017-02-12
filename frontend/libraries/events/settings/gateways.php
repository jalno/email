<?php
namespace themes\clipone\events\sms\settings\gateways\add;
use \packages\base\event;
class beforeLoad extends event{
	public $view;
	function __construct($view){
		$this->view = $view;
	}
}
