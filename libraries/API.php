<?php
namespace packages\email;

use packages\base\{Options, Events, Translator, Packages, Date, IO, IO\File, IO\NotFoundException};
use packages\userpanel\User;
use packages\email\{Html2Text, Sent, Template, Sender, Sender\Address, Events as EmailEvents};

class API{
	private $subject;
	private $text;
	private $html;
	private $attachments = array();
	private $receiver_name;
	private $receiver_address;
	private $receiver_user;
	private $sender_address;
	private $sender_user;
	private $time;
	public function template($name,$parameters = array(),$lang = null){
		if($lang === null){
			$lang = Translator::getShortCodeLang();
		}
		if(!$lang){
			throw new UnKownLanguage();
		}
		$template = new Template();
		$template->where('name', $name);
		$template->where('lang', $lang);
		$template->where('status', Template::active);
		if($template = $template->getOne()){
			$render = $template->render($parameters);
			$this->text = $render['text'];
			$this->html = $render['html'];
			$this->subject = $render['subject'];
		}else{
			$this->text = '';
		}
		return $this;
	}
	public function to($address,$name = null){
		$this->receiver_address = $address;
		$this->receiver_name = $name;
		if($this->receiver_user === null){
			$user = new User();
			$user->where("email", $address);
			if($user = $user->getOne()){
				$this->toUser($user);
			}
		}
		return $this;
	}
	public function toUser(User $receiver_user){
		$this->receiver_user = $receiver_user;
		if(!$this->receiver_address){
			$this->receiver_address = $receiver_user->email;
		}
		if(!$this->receiver_name){
			$this->receiver_name = $receiver_user->getFullName();
		}
		return $this;
	}
	public function fromUser(User $sender_user){
		$this->sender_user  = $sender_user;
		return $this;
	}
	public function fromAddress(Address $address){
		if($address->status == Address::active and $address->sender->status == Sender::active){
			$this->sender_address = $address;
		}else{
			throw new DeactivedAdressException;
		}
		return $this;
	}
	public function fromDefaultAddress(){
		if($defaultAddress = Options::get('packages.email.defaultAddress')){
			if ($address = (new Address)->byID($defaultAddress)) {
				$this->fromAddress($address);
			}else{
				throw new DefaultAddressException();
			}
		}else{
			throw new DefaultAddressException();
		}
	}
	/**
	 * @param string|File $file
	 * @param string|null $name
	 */
	public function addAttachment($file,$name = null) {
		if (is_string($file)) {
			$file = new File\Local($file);
		}
		if (!$file instanceof File) {
			throw new \TypeError("argument 1 is not a File object");
		}
		if (!$file->exists()) {
			throw new NotFoundException($file);
		}
		if (!$name) {
			$name = $file->basename;
		}
		$storage = Packages::package('email')->getHome()->directory('storage/private/attachments');
		if(!$storage->exists()){
			$storage->make(true);
		}
		$real_storage = $storage->getRealPath();
		$real_file = $file->getRealPath();
		if (substr($real_file, 0, strlen($real_storage)) != $real_storage) {
			$new = $storage->file($file->md5());
			$file->copyTo($new);
			$file = $new;
		}
		
		$this->attachments[] = array(
			'file' => $file->getPath(),
			'name' => $name,
			'size' => $file->size(),
		);
		return $this;
	}
	public function text($text){
		$this->text = $text;
		return $this;
	}
	public function html($html){
		$this->html = $html;
		if(!$this->text){
			$this->text = Html2Text::convert($html, true);
		}
		return $this;
	}
	public function subject($subject){
		$this->subject = $subject;
	}
	public function now(){
		$this->time = Date::time();
		return $this;
	}
	public function at($time){
		$this->time = $time;
		return $this;
	}
	public function send(){
		$email = new Sent();
		$email->send_at = $this->time;
		if(!$this->sender_address){
			$this->fromDefaultAddress();
		}

		$email->sender_address = $this->sender_address->id;
		if($this->sender_user){
			$email->sender_user = $this->sender_user->id;
		}
		$email->receiver_name = $this->receiver_name;
		$email->receiver_address = $this->receiver_address;
		if($this->receiver_user){
			$email->receiver_user = $this->receiver_user->id;
		}
		$email->subject = $this->subject;
		$email->text = $this->text;
		$email->html = $this->html;
		if($email->send_at >= Date::time()){
			$email->status = Sent::queued;
		}else{
			$email->status = Sent::sending;
		}
		$email->save();
		foreach($this->attachments as $attachment){
			$attach = new Sent\Attachment();
			$attach->mail = $email->id;
			$attach->size = $attachment['size'];
			$attach->name = $attachment['name'];
			$attach->file = $attachment['file'];
			$attach->save();
		}
		if($email->send_at >= date::time()){
			$email->send();
		}
		Events::trigger(new EmailEvents\Send($email));
		return $email->status;
	}
}
