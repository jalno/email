<?php
namespace packages\email\notifications;

use packages\base\{Event, Translator};
use packages\notifications;
use packages\email\{API, Template, DeactivedAdressException, DefaultAddressException};

class Channel extends notifications\Channel {

	public function notify(Event $event){
		$lang = Translator::getShortCodeLang();
		$template = new Template();
		$template->where('name', $event->getName());
		$template->where('lang', $lang);
		$template->where('status', Template::active);
		if ($template->has()) {
			try {
				foreach ($event->getTargetUsers() as $user) {
					$api = new API();
					$arguments = array_replace(array('user' => $user), $event->getArguments());
					$api->template($event->getName(), $arguments);
					$api->to($user->email, $user->getFullName());
					$api->toUser($user);
					$api->send();
				}
			} catch (DeactivedAdressException $e) {				
			} catch (DefaultAddressException $e) {
			}
		}
	}

	public function getName(): string {
		return "email";
	}

}