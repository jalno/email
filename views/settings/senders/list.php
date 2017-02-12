<?php
namespace packages\email\views\settings\senders;
use \packages\userpanel\views\listview as list_view;
use \packages\email\authorization;
use \packages\email\events\senders;
use \packages\base\views\traits\form as formTrait;
class listview extends list_view{
	use formTrait;
	protected $canAdd;
	protected $canEdit;
	protected $canDel;
	static protected $navigation;
	function __construct(){
		$this->canAdd = authorization::is_accessed('settings_senders_add');
		$this->canEdit = authorization::is_accessed('settings_senders_edit');
		$this->canDel = authorization::is_accessed('settings_senders_delete');
	}
	public function getSenders(){
		return $this->getData('senders');
	}
	public function setSenders(senders $senders){
		$this->setData($senders, 'senders');
	}
	public static function onSourceLoad(){
		self::$navigation = authorization::is_accessed('settings_senders_list');
	}
}
