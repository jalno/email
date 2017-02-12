<?php
namespace packages\email\events;
use \packages\base\event;
use \packages\email\get;
class receive extends event{
	protected $email;
	public function __construct(get $email){
		$this->email = $email;
	}
	public function getemail(){
		return $this->email;
	}
}
