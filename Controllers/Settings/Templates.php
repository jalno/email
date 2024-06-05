<?php
namespace packages\email\Controllers\Settings;
use \packages\base;
use \packages\base\NotFound;
use \packages\base\HTTP;
use \packages\base\DB;
use \packages\base\DB\Parenthesis;
use \packages\base\DB\DuplicateRecord;
use \packages\base\Views\FormError;
use \packages\base\View\Error;
use \packages\base\InputValidation;
use \packages\base\Translator;

use \packages\userpanel;
use \packages\userpanel\User;
use \packages\userpanel\Date;

use \packages\email\Html2Text;
use \packages\email\View;
use \packages\email\Controller;
use \packages\email\Authorization;
use \packages\email\Template;
use \packages\email\Events\Templates as TemplatesEvent;


class Templates extends Controller{
	protected $authentication = true;
	public function listtemplates(){
		Authorization::haveOrFail('settings_templates_list');
		$view = View::byName(\packages\email\Views\Settings\Templates\ListView::class);
		$template = new Template();
		$inputsRules = array(
			'id' => array(
				'type' => 'number',
				'optional' => true,
				'empty' => true
			),
			'name' => array(
				'type' => 'string',
				'optional' =>true,
				'empty' => true
			),
			'lang' => array(
				'type' => 'string',
				'optional' =>true,
				'empty' => true
			),
			'status' => array(
				'type' => 'number',
				'optional' =>true,
				'empty' => true
			),
			'word' => array(
				'type' => 'string',
				'optional' => true,
				'empty' => true
			),
			'comparison' => array(
				'values' => array('equals', 'startswith', 'contains'),
				'default' => 'contains',
				'optional' => true
			)
		);
		$this->response->setStatus(true);
		try{
			$inputs = $this->checkinputs($inputsRules);
			if(isset($inputs['status']) and $inputs['status'] != 0){
				if(!in_array($inputs['status'], array(Template::active, Template::deactive))){
					throw new InputValidation("status");
				}
			}

			foreach(array('id', 'name', 'lang','status') as $item){
				if(isset($inputs[$item]) and $inputs[$item]){
					$comparison = $inputs['comparison'];
					if(in_array($item, array('id','lang', 'status'))){
						$comparison = 'equals';
					}
					$template->where($item, $inputs[$item], $comparison);
				}
			}
			if(isset($inputs['word']) and $inputs['word']){
				$parenthesis = new Parenthesis();
				foreach(array('name','html') as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where($item,$inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				$template->where($parenthesis);
			}
		}catch(InputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$view->setDataForm($this->inputsvalue($inputsRules));
		$template->orderBy('id', 'ASC');
		$template->pageLimit = $this->items_per_page;
		$items = $template->paginate($this->page);
		$view->setPaginate($this->page, $template->totalCount, $this->items_per_page);
		$view->setDataList($items);
		$this->response->setView($view);
		return $this->response;
	}
	public function add(){
		Authorization::haveOrFail('settings_templates_add');
		$view = View::byName(\packages\email\Views\Settings\Templates\Add::class);
		$templates = new TemplatesEvent();
		$view->setTemplates($templates->get());
		if(HTTP::is_post()){
			$inputsRules = array(
				'name' => array(
					'type' => 'string'
				),
				'subject' => array(
					'type' => 'string'
				),
				'html' => array(),
				'lang' => array(
					'type' => 'string',
					'values' => Translator::$allowlangs
				),
				'status' => array(
					'type' => 'number',
					'values' => array(Template::active, Template::deactive)
				)
			);
			$this->response->setStatus(false);
			try{
				$inputs = $this->checkinputs($inputsRules);
				if(Template::where("name", $inputs['name'])->where("lang", $inputs['lang'])->has()){
					throw new DuplicateRecord("name");
				}
				$inputs['html'] = preg_replace_callback('/\\[((?:[a-z0-9_]+(?:-(?:\\>|&gt;))?)+)\\]/i', function($matches){
					return html_entity_decode($matches[0]);
				}, $inputs['html']);

				$template = $templates->getByName($inputs['name']);
				$templateObj = new Template();
				$templateObj->name = $inputs['name'];
				$templateObj->status = $inputs['status'];
				$templateObj->lang = $inputs['lang'];
				$templateObj->subject = $inputs['subject'];
				$templateObj->html = $inputs['html'];
				$templateObj->text = Html2Text::convert($inputs['html'], true);
				if($template){
					$templateObj->variables = $template->variables;
					$templateObj->event = $template->event;
					$templateObj->render = $template->render;
				}
				$templateObj->save();
				$this->response->setStatus(true);
				$this->response->Go(userpanel\url('settings/email/templates/edit/'.$templateObj->id));
			}catch(InputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}catch(DuplicateRecord $error){
				$view->setFormError(FormError::fromException($error));
			}
			$view->setDataForm($this->inputsvalue($inputsRules));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function delete($data){
		Authorization::haveOrFail('settings_templates_delete');
		$template = (new Template)->byID($data['template']);
		if (!$template) {
			throw new NotFound;
		}
		$view = View::byName(\packages\email\Views\Settings\Templates\Delete::class);
		$view->setTemplate($template);
		if(HTTP::is_post()){
			$template->delete();

			$this->response->setStatus(true);
			$this->response->Go(userpanel\url('settings/email/templates'));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
	public function edit($data){
		Authorization::haveOrFail('settings_templates_edit');
		$templateObj = (new Template)->byID($data['template']);
		if (!$templateObj) {
			throw new NotFound;
		}
		$view = View::byName(\packages\email\Views\Settings\Templates\Edit::class);
		$view->setTemplate($templateObj);
		$templates = new TemplatesEvent();
		$view->setTemplates($templates->get());
		if(HTTP::is_post()){
			$inputsRules = array(
				'name' => array(
					'type' => 'string',
					'optional' => true
				),
				'subject' => array(
					'optional' => true
				),
				'html' => array(
					'optional' => true
				),
				'lang' => array(
					'type' => 'string',
					'values' => Translator::$allowlangs,
					'optional' => true
				),
				'status' => array(
					'type' => 'number',
					'values' => array(Template::active, Template::deactive),
					'optional' => true
				)
			);
			$this->response->setStatus(true);
			try{
				$inputs = $this->checkinputs($inputsRules);
				if(isset($inputs['name']) and $inputs['name'] != $templateObj->name){
					$templateExsits = Template::where("name", $inputs['name']);
					if(isset($inputs['lang'])){
						$templateExsits->where("lang",$inputs['lang']);
					}else{
						$templateExsits->where("lang",$templateObj->lang);
					}
					if($templateExsits->has()){
						throw new DuplicateRecord("name");
					}
					unset($templateExsits);
					
					$template = $templates->getByName($inputs['name']);
					$templateObj->name = $inputs['name'];
					if($template){
						$templateObj->variables = $template->variables;
						$templateObj->event = $template->event;
						$templateObj->render = $template->render;
					}else{
						$templateObj->variables = null;
						$templateObj->event = null;
						$templateObj->render = null;
					}
				}elseif(isset($inputs['lang']) and $inputs['lang'] != $templateObj->lang){
					if(Template::where("name", $templateObj->lang)->were("lang", $inputs['lang'])->has()){
						throw new DuplicateRecord("lang");
					}
				}
				$inputs['html'] = preg_replace_callback('/\\[((?:[a-z0-9_]+(?:-(?:\\>|&gt;))?)+)\\]/i', function($matches){
					return html_entity_decode($matches[0]);
				}, $inputs['html']);

				foreach(array('lang','html','subject', 'status') as $key){
					if(isset($inputs[$key])){
						$templateObj->$key = $inputs[$key];
						if($key == 'html'){
							$templateObj->text = Html2Text::convert($templateObj->$key, true);
						}
					}
				}
				$templateObj->save();
				$this->response->setStatus(true);
			}catch(InputValidation $error){
				$view->setFormError(FormError::fromException($error));
				$this->response->setStatus(false);
			}catch(DuplicateRecord $error){
				$view->setFormError(FormError::fromException($error));
				$this->response->setStatus(false);
			}
			$view->setDataForm($this->inputsvalue($inputsRules));
		}else{
			$this->response->setStatus(true);
		}
		$this->response->setView($view);
		return $this->response;
	}
}
