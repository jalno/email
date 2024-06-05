<?php
namespace packages\email;
use \packages\userpanel\Authorization as UserPanelAuthorization;

class Authorization extends UserPanelAuthorization{
	static function is_accessed($permission, $prefix = 'email'){
		return parent::is_accessed($permission, $prefix);
	}
	static function haveOrFail($permission, $prefix = 'email'){
		parent::haveOrFail($permission, $prefix);
	}
}
