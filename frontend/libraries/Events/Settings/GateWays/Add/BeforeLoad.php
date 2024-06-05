<?php
namespace themes\clipone\Events\SMS\Settings\GateWays\Add;
use \packages\base\Event;
class BeforeLoad extends Event{
	public $view;
	function __construct($view){
		$this->view = $view;
	}
}
