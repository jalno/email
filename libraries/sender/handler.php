<?php
namespace packages\email\sender;
use \packages\email\sender;
use \packages\email\sent;
abstract class handler{
	abstract public function __construct(sender $sender);
	abstract public function send(sent $email);
}
class senderException extends \Exception{}
