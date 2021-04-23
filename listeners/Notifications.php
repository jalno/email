<?php
namespace packages\email\listeners;

use packages\notifications\events\Channels;
use packages\email\notifications\Channel;

class Notifications {
	public function channels(Channels $channels): void {
		$channels->add(Channel::class);
	}
}
