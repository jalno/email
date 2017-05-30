<?php
namespace packages\email\notifications;
use \packages\base\event;
use \packages\notifications;
use \packages\email\api;
class channel extends notifications\channel{
	public function notify(event $event){
		$api = new api();
		$api->template($event->getName(), $event->getArguments());
		foreach($event->getTargetUsers() as $user){
			$api->to($user->email, $user->getFullName());
			$api->toUser($user);
			$api->send();
		}
	}
}