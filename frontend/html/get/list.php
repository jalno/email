<?php
use \packages\base\translator;
use \packages\userpanel;
use \packages\userpanel\date;
use \packages\email\authorization;

$this->the_header();
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-envelope"></i> <?php echo translator::trans('email.get'); ?>
		<div class="panel-tools">
			<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('search'); ?>" href="#search" data-toggle="modal"><i class="fa fa-search"></i></a>
			<?php if($this->canSend){ ?><a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('email.send'); ?>" href="<?php echo userpanel\url('email/send'); ?>"><i class="fa fa-plus"></i></a><?php } ?>
			<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
		</div>
	</div>
	<div class="panel-body">
		<div class="row messages">
			<ul class="messages-list col-sm-3">
				<?php foreach($this->getDataList() as $email){ ?>
				<li class="messages-item" data-email="<?php echo $email->id; ?>" data-to="<?php echo $email->receiver_address; ?>" data-from="<?php echo $email->sender_address; ?>" data-type="get">
					<span class="messages-item-from">
						<?php if($email->sender_user){ ?>
							<a href="<?php echo userpanel\url('users/view/'.$email->sender_user->id); ?>"><?php echo $email->sender_user->getFullName(); ?></a>
						<?php }else{
							echo $email->sender_address;
						}
						?>
					</span>
					<div class="messages-item-time">
						<span class="text ltr" data-time="<?php echo date::format("l LTS", $email->receive_at); ?>"><?php echo date::format("l", $email->receive_at); ?></span>
						<div class="messages-item-actions">
							<?php if(authorization::is_accessed('send')){ ?>
							<a target="_blank" href="<?php echo userpanel\url("email/send", ['reply'=>$email->id]); ?>"><i class="fa fa-mail-reply"></i></a>
							<?php } ?>
						</div>
					</div>
					<span class="messages-item-subject"><?php echo htmlentities($email->subject); ?></span>
					<span class="messages-item-preview"><?php echo substr($email->text, 0, 75); ?></span>
				</li>
				<?php } ?>
			</ul>
			<div class="col-sm-9 messages-content">
				<div class="message-header">
					<div class="message-time"></div>
					<div class="message-from"></div>
					<div class="message-to"></div>
					<div class="message-subject"></div>
					<div class="message-actions">
						<?php if(authorization::is_accessed('send')){ ?>
							<a class="forward tooltips" title="Forward" target="_blank" href="#"><i class="fa fa-long-arrow-right"></i></a>
						<?php
						}
						if(authorization::is_accessed('send')){ ?>
							<a class="send tooltips" title="Send email" target="_blank" href="#"><i class="fa fa-reply"></i></a>
						<?php } ?>
						<a class="open-email tooltips" title="Open" target="_blank" href="#"><i class="fa fa-envelope-open-o"></i></a>
						<a class="load-email tooltips" href="#" title="<?php echo translator::trans("email.get.loadcontent"); ?>"><i class="fa fa-puzzle-piece"></i></a>
					</div>
				</div>
				<div class="message-content ltr">
					<iframe src="" frameborder="0"></iframe>
				</div>
			</div>
		</div>
		<?php $this->paginator(); ?>
	</div>
</div>
<div class="modal fade" id="search" tabindex="-1" data-show="true" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title"><?php echo translator::trans('search'); ?></h4>
	</div>
	<div class="modal-body">
		<form id="emaillist_search" class="form-horizontal" action="<?php echo userpanel\url("email/get"); ?>" method="GET">
			<?php
			$this->setHorizontalForm('sm-3','sm-9');
			$feilds = array(
				array(
					'name' => 'id',
					'type' => 'address',
					'label' => translator::trans("email.id"),
					'ltr' => true
				),
				array(
					'type' => 'hidden',
					'name' => 'sender_user'
				),
				array(
					'name' => 'sender_user_name',
					'label' => translator::trans("email.user.sender")
				),
				array(
					'type' => 'email',
					'name' => 'sender_address',
					'label' => translator::trans("email.address.sender"),
					'ltr' => true
				),
				array(
					'type' => 'hidden',
					'name' => 'receiver_user'
				),
				array(
					'name' => 'text',
					'label' => translator::trans("email.text"),
				),
				array(
					'name' => 'status',
					'type' => 'select',
					'label' => translator::trans("email.get.status"),
					'options' => $this->getStatusForSelect()
				),
				array(
					'type' => 'select',
					'label' => translator::trans('search.comparison'),
					'name' => 'comparison',
					'options' => $this->getComparisonsForSelect()
				)
			);
			foreach($feilds as $input){
				echo $this->createField($input);
			}
			?>
		</form>
	</div>
	<div class="modal-footer">
		<button type="submit" form="emaillist_search" class="btn btn-success"><?php echo translator::trans("search"); ?></button>
		<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo translator::trans('cancel'); ?></button>
	</div>
</div>
<?php
$this->the_footer();
