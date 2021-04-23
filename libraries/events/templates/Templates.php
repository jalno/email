<?php
namespace packages\email\events;

use packages\base\Event;
use packages\notifications;
use packages\userpanel\User;
use packages\email\Template;

class Templates extends Event {

	/**
	 * @var array<strting,Template>
	 */
	private $templates = array();

	public function addTemplate(Template $template): void {
		$this->templates[$template->name] = $template;
	}

	/**
	 * @return string[]
	 */
	public function getTemplateNames(): array {
		return array_keys($this->templates);
	}

	public function getByName(string $name): ?Template {
		return $this->templates[$name] ?? null;
	}

	/**
	 * @return array<strting,Template>
	 */
	public function get(): array {
		if (!$this->templates) {
			$this->trigger();
			$this->includeNotifiables();
		}
		return $this->templates;
	}

	protected function includeNotifiables(): void {
		foreach (notifications\Api::getEvents() as $event) {
			if ($this->getByName($event::getName())) {
				continue;
			}
			$this->importEventAsTemplate($event);
		}
	}

	/**
	 * @param class-string<notifications\Notifiable> $event
	 */
	protected function importEventAsTemplate(string $event): void {
		$template = new Template();
		$template->name = $event::getName();
		foreach ($event::getParameters() as $variable) {
			$template->addVariable($variable);
		}
		$template->addVariable(User::class);
		$this->addTemplate($template);
	}
}
