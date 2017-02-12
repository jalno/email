<?php
namespace packages\email\sent;
use \packages\base\db\dbObject;
class attachment extends dbObject{
	protected $dbTable = "email_sent_attachments";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'mail' => array('type' => 'int', 'required' => true),
        'size' => array('type' => 'int', 'required' => true),
        'name' => array('type' => 'text', 'required' => true),
        'file' => array('type' => 'text', 'required' => true)
    );
	protected $relations = array(
		'mail' => array('hasOne', 'packages\\email\\sent', 'mail')
	);
}
