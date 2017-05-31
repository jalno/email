<?php
namespace packages\email\notifications;
use \packages\base\event;
use \packages\base\translator;
use \packages\notifications;
use \packages\email\api;
use \packages\email\template;
class channel extends notifications\channel{
	public function notify(event $event){
		$lang = translator::getShortCodeLang();
		$template = new template();
		$template->where('name', $event->getName());
		$template->where('lang', $lang);
		if($template->has()){
			foreach($event->getTargetUsers() as $user){
				$api = new api();
				$arguments = array_replace(array('user' => $user), $event->getArguments());
				$api->template($event->getName(), $arguments);
				$api->to($user->email, $user->getFullName());
				$api->toUser($user);
				$api->send();
			}
		}
	}
}