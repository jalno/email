<?php
namespace packages\email\views\settings\receivers;
use \packages\userpanel\views\listview as list_view;
use \packages\email\authorization;
use \packages\base\views\traits\form as formTrait;
class listview extends list_view{
	use formTrait;
	protected $canAdd;
	protected $canEdit;
	protected $canDel;
	static protected $navigation;
	function __construct(){
		$this->canAdd = authorization::is_accessed('settings_receivers_add');
		$this->canEdit = authorization::is_accessed('settings_receivers_edit');
		$this->canDel = authorization::is_accessed('settings_receivers_delete');
	}
	public static function onSourceLoad(){
		self::$navigation = authorization::is_accessed('settings_receivers_list');
	}
}
