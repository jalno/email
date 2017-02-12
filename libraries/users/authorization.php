<?php
namespace packages\email;
use \packages\userpanel\authorization as UserPanelAuthorization;

class authorization extends UserPanelAuthorization{
	static function is_accessed($permission, $prefix = 'email'){
		return parent::is_accessed($permission, $prefix);
	}
	static function haveOrFail($permission, $prefix = 'email'){
		parent::haveOrFail($permission, $prefix);
	}
}
