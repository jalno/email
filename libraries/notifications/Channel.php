<?php
namespace packages\email\notifications;

use packages\base\{EventInterface, Translator};
use packages\notifications\IChannel;
use packages\email\{API, Template, DeactivedAdressException, DefaultAddressException};

class Channel implements IChannel {

	public function notify(EventInterface $event): void {
		if (!$this->canNotify($event)) {
			return;
		}
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

	public function canNotify(EventInterface $event): bool {
		$lang = Translator::getShortCodeLang();
		$template = new Template();
		$template->where('name', $event->getName());
		$template->where('lang', $lang);
		$template->where('status', Template::active);
		return $template->has();
	}

	public function getName(): string {
		return "email";
	}

}