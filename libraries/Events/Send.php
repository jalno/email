<?php
namespace packages\email\Events;
use \packages\base\Event;
use \packages\email\Sent;
class Send extends Event{
	protected $email;
	public function __construct(Sent $email){
		$this->email = $email;
	}
	public function getemail(){
		return $this->email;
	}
}
