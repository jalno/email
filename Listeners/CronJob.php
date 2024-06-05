<?php
namespace packages\email\Listeners;
use \packages\cronjob\Events\Tasks;
use \packages\cronjob\Task;
use \packages\cronjob\Task\Schedule;
class CronJob{
	public function tasks(Tasks $event){
		$event->addTask($this->taskProcesses());
	}
	private function taskProcesses(){
		$task = new Task();
		$task->name = "email_receive";
		$task->process = \packages\email\Processes\Email::class.'@checkForNewEmail';
		$task->parameters = array();
		$task->schedules = array(
			new Schedule()
		);
		return $task;
	}
}
