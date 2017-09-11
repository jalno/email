<?php
namespace packages\email\views\get;
use \packages\email\get as email;
use \packages\email\Html2Text;
class view extends \packages\email\view{
	public function setEmail(email $email){
		$this->setData($email, "email");
	}
	protected function getEmail():email{
		return $this->getData("email");
	}
	public function setContent(string $content){
		$this->setData($content, 'content');
	}
	protected function getContent():string{
		return $this->getData('content');
	}
	public function isHTML(){
		$this->setData('html', 'content-type');
	}
	public function isText(){
		$this->setData('text', 'content-type');
	}
	protected function getContentType():string{
		return $this->getData('content-type');
	}
	public function hasExternalFiles(bool $has = null):bool{
		if($has !== null){
			$this->setData($has, 'has-external-files');
		}
		return (bool)$this->getData('has-external-files');
	}
	protected function getHTML():string{
		$content = $this->getContent();
		if($this->getContentType() == 'text'){
			$content = '<pre>'.htmlentities($content).'</pre>';
		}
		return $content;
	}
	protected function getText():string{
		$content = $this->getContent();
		if($this->getContentType() == 'html'){
			if(!$content = $this->getEmail()->text){
				$content = Html2Text::convert($content, true);
			}
		}
		return $content;
	}
	public function export(){
		$email = $this->getEmail();
		return array(
			'data' => array(
				'receive_at' => $email->receive_at,
				'sender_name' => $email->sender_name,
				'sender_address' => $email->sender_address,
				'sender_user' => $email->sender_user,
				'receiver' => $email->data['receiver'],
				'receiver_name' => $email->receiver_name,
				'receiver_address' => $email->receiver_address,
				'subject' => $email->subject,
				'text' => $this->getText(),
				'html' => $this->getHTML(),
				'status' => $email->status
			)
		);
	}
}
