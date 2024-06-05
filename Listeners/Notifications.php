<?php
namespace packages\email\Listeners;

use packages\notifications\Events\Channels;
use packages\email\Notifications\Channel;

class Notifications {
	public function channels(Channels $channels): void {
		$channels->add(Channel::class);
	}
}
