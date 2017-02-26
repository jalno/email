<?php
namespace packages\email\controllers\settings;
use \packages\base;
use \packages\base\NotFound;
use \packages\base\http;
use \packages\base\db;
use \packages\base\db\parenthesis;
use \packages\base\db\duplicateRecord;
use \packages\base\views\FormError;
use \packages\base\view\error;
use \packages\base\inputValidation;
use \packages\base\translator;

use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;

use \packages\email\Html2Text;
use \packages\email\view;
use \packages\email\controller;
use \packages\email\authorization;
use \packages\email\template;
use \packages\email\events\templates as templatesEvent;


class templates extends controller{
	protected $authentication = true;
	public function listtemplates(){
		authorization::haveOrFail('settings_templates_list');
		$view = view::byName("\\packages\\email\\views\\settings\\templates\\listview");
		$template = new template();
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
				if(!in_array($inputs['status'], array(template::active, template::deactive))){
					throw new inputValidation("status");
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
				$parenthesis = new parenthesis();
				foreach(array('name','html') as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where($item,$inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				$template->where($parenthesis);
			}
		}catch(inputValidation $error){
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
		authorization::haveOrFail('settings_templates_add');
		$view = view::byName("\\packages\\email\\views\\settings\\templates\\add");
		$templates = new templatesEvent();
		$view->setTemplates($templates->get());
		if(http::is_post()){
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
					'values' => translator::$allowlangs
				),
				'status' => array(
					'type' => 'number',
					'values' => array(template::active, template::deactive)
				)
			);
			$this->response->setStatus(false);
			try{
				$inputs = $this->checkinputs($inputsRules);
				if(template::where("name", $inputs['name'])->where("lang", $inputs['lang'])->has()){
					throw new duplicateRecord("name");
				}
				$inputs['html'] = preg_replace_callback('/\\[((?:[a-z0-9_]+(?:-(?:\\>|&gt;))?)+)\\]/i', function($matches){
					return html_entity_decode($matches[0]);
				}, $inputs['html']);

				$template = $templates->getByName($inputs['name']);
				$templateObj = new template();
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
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
			}catch(duplicateRecord $error){
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
		authorization::haveOrFail('settings_templates_delete');
		if(!$template = template::byID($data['template'])){
			throw new NotFound;
		}
		$view = view::byName("\\packages\\email\\views\\settings\\templates\\delete");
		$view->setTemplate($template);
		if(http::is_post()){
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
		authorization::haveOrFail('settings_templates_edit');
		if(!$templateObj = template::byID($data['template'])){
			throw new NotFound;
		}
		$view = view::byName("\\packages\\email\\views\\settings\\templates\\edit");
		$view->setTemplate($templateObj);
		$templates = new templatesEvent();
		$view->setTemplates($templates->get());
		if(http::is_post()){
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
					'values' => translator::$allowlangs,
					'optional' => true
				),
				'status' => array(
					'type' => 'number',
					'values' => array(template::active, template::deactive),
					'optional' => true
				)
			);
			$this->response->setStatus(true);
			try{
				$inputs = $this->checkinputs($inputsRules);
				if(isset($inputs['name']) and $inputs['name'] != $templateObj->name){
					$templateExsits = template::where("name", $inputs['name']);
					if(isset($inputs['lang'])){
						$templateExsits->where("lang",$inputs['lang']);
					}else{
						$templateExsits->where("lang",$templateObj->lang);
					}
					if($templateExsits->has()){
						throw new duplicateRecord("name");
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
					if(template::where("name", $templateObj->lang)->were("lang", $inputs['lang'])->has()){
						throw new duplicateRecord("lang");
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
			}catch(inputValidation $error){
				$view->setFormError(FormError::fromException($error));
				$this->response->setStatus(false);
			}catch(duplicateRecord $error){
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
