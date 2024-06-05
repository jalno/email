<?php
namespace packages\email\Sender;
use \packages\email\Sender;
use \packages\email\Sent;
abstract class Handler{
	abstract public function __construct(Sender $sender);
	abstract public function send(Sent $email);
}

