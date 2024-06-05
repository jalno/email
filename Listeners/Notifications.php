<?php

namespace packages\email\Listeners;

use packages\email\Notifications\Channel;
use packages\notifications\Events\Channels;

class Notifications
{
    public function channels(Channels $channels): void
    {
        $channels->add(Channel::class);
    }
}
