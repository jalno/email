<?php
namespace packages\email\events;
use \packages\base\event;
use \packages\email\sent;
class send extends event{
	protected $email;
	public function __construct(sent $email){
		$this->email = $email;
	}
	public function getemail(){
		return $this->email;
	}
}
