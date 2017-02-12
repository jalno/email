<?php
namespace packages\email\processes;
use \packages\base\process;
use \packages\email\receiver;
class email extends process{
	public function checkForNewEmail(){
		$receiver = new receiver;
		$receiver->where("status", receiver::active);
		foreach($receiver->get() as $receiver){
			$unreads = $receiver->check();
			if($unreads){
				$receiver->getEmails($unreads);
			}
		}
	}
}
