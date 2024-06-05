<?php

namespace packages\email\Processes;

use packages\base\Log;
use packages\base\Process;
use packages\email\Receiver;

class Email extends Process
{
    public function checkForNewEmail(?array $data = null): void
    {
        if (isset($data['verbose']) and $data['verbose']) {
            Log::setLevel('debug');
        }
        $receiver = new Receiver();
        $receiver->where('status', Receiver::active);
        foreach ($receiver->get() as $receiver) {
            $unreads = $receiver->check();
            if ($unreads) {
                $receiver->getEmails($unreads);
            }
        }
    }
}
