<?php
namespace packages\email\listeners;
use \packages\notifications\events\channels;
use \packages\email\notifications\channel;
class notifications{
	public function channels(channels $channels){
		$channels->add(channel::class);
	}
}
