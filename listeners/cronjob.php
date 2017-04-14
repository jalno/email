<?php
namespace packages\email\listeners;
use \packages\cronjob\events\tasks;
use \packages\cronjob\task;
use \packages\cronjob\task\schedule;
class cronjob{
	public function tasks(tasks $event){
		$event->addTask($this->taskProcesses());
	}
	private function taskProcesses(){
		$task = new task();
		$task->name = "email_receive";
		$task->process = "packages\\email\\processes\\email@checkForNewEmail";
		$task->parameters = array();
		$task->schedules = array(
			new schedule()
		);
		return $task;
	}
}
