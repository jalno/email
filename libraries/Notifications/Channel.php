<?php

namespace packages\email\Notifications;

use packages\base\EventInterface;
use packages\base\Translator;
use packages\email\API;
use packages\email\DeactivedAdressException;
use packages\email\DefaultAddressException;
use packages\email\Template;
use packages\notifications\IChannel;

class Channel implements IChannel
{
    public function notify(EventInterface $event): void
    {
        if (!$this->canNotify($event)) {
            return;
        }
        try {
            foreach ($event->getTargetUsers() as $user) {
                $api = new API();
                $arguments = array_replace(['user' => $user], $event->getArguments());
                $api->template($event->getName(), $arguments);
                $api->to($user->email, $user->getFullName());
                $api->toUser($user);
                $api->send();
            }
        } catch (DeactivedAdressException $e) {
        } catch (DefaultAddressException $e) {
        }
    }

    public function canNotify(EventInterface $event): bool
    {
        $lang = Translator::getShortCodeLang();
        $template = new Template();
        $template->where('name', $event->getName());
        $template->where('lang', $lang);
        $template->where('status', Template::active);

        return $template->has();
    }

    public function getName(): string
    {
        return 'email';
    }
}
