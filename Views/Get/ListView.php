<?php
namespace packages\email\Views\Get;
use \packages\base\Views\Traits\Form as FormTrait;
use \packages\email\Authorization;

class ListView extends \packages\email\Views\ListView{
	use FormTrait;
	protected $canSend;
	static protected $navigation;
	function __construct(){
		$this->canSend = Authorization::is_accessed('send');
	}

	public static function onSourceLoad(){
		self::$navigation = Authorization::is_accessed('get_list');
	}
}
