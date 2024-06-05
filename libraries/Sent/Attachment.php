<?php
namespace packages\email\Sent;
use \packages\base\DB\DBObject;
class Attachment extends DBObject{
	protected $dbTable = "email_sent_attachments";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'mail' => array('type' => 'int', 'required' => true),
        'size' => array('type' => 'int', 'required' => true),
        'name' => array('type' => 'text', 'required' => true),
        'file' => array('type' => 'text', 'required' => true)
    );
	protected $relations = array(
		'mail' => array('hasOne', \packages\email\Sent::class, 'mail')
	);
}
