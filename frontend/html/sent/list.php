<?php
use \packages\base;
use \packages\base\translator;

use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;

use \themes\clipone\utility;
$this->the_header();
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-envelope"></i> <?php echo translator::trans('email.sent'); ?>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('search'); ?>" href="#search" data-toggle="modal"><i class="fa fa-search"></i></a>
					<?php if($this->canSend){ ?><a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('email.send'); ?>" href="<?php echo userpanel\url('email/send'); ?>"><i class="fa fa-plus"></i></a><?php } ?>
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th class="center">#</th>
								<th><?php echo translator::trans('email.send_at'); ?></th>
								<th><?php echo translator::trans('email.user.sender'); ?></th>
								<th><?php echo translator::trans('email.address.sender'); ?></th>
								<th><?php echo translator::trans('email.user.receiver'); ?></th>
								<th><?php echo translator::trans('email.address.receiver'); ?></th>
								<th><?php echo translator::trans('email.text'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach($this->getDataList() as $row){
							?>
							<tr>
								<td class="center"><?php echo $row->id; ?></td>
								<td class="ltr"><?php echo date::format('Y/m/d H:i', $row->send_at); ?></td>
								<td><?php if($row->sender_user){ ?>
									<a href="<?php echo userpanel\url('users/view/'.$row->sender_user->id); ?>"><?php echo $row->sender_user->name." ".$row->sender_user->lastname; ?></a>
									<?php
									}else{
										echo translator::trans('email.user.system');
									}
								?></td>
								<td class="ltr"><?php echo $row->sender_address->address; ?></td>
								<td<?php if(!$row->receiver_user)echo(" class=\"center\""); ?>><?php if($row->receiver_user){ ?>
									<a href="<?php echo userpanel\url('users/view/'.$row->receiver_user->id); ?>"><?php echo $row->receiver_user->name." ".$row->receiver_user->lastname; ?></a>
								<?php }else{
									echo translator::trans('email.user.receiver.unknown');
								} ?></td>
								<td class="ltr"><?php echo $row->receiver_address; ?></td>
								<td><?php echo $row->text; ?></td>
							</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>
				<?php $this->paginator(); ?>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="search" tabindex="-1" data-show="true" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title"><?php echo translator::trans('search'); ?></h4>
	</div>
	<div class="modal-body">
		<form id="email_sent_search" class="form-horizontal" action="<?php echo userpanel\url("email/sent"); ?>" method="GET">
			<input type="hidden" name="user" value="">
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
					'type' => 'address',
					'name' => 'sender_address',
					'label' => translator::trans("email.address.sender"),
					'ltr' => true
				),
				array(
					'type' => 'hidden',
					'name' => 'receiver_user'
				),
				array(
					'name' => 'receiver_user_name',
					'label' => translator::trans("email.user.receiver")
				),
				array(
					'type' => 'address',
					'name' => 'receiver_address',
					'label' => translator::trans("email.address.receiver"),
					'ltr' => true
				),
				array(
					'name' => 'text',
					'label' => translator::trans("email.text"),
				),
				array(
					'name' => 'status',
					'type' => 'select',
					'label' => translator::trans("email.sent.status"),
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
		<button type="submit" form="email_sent_search" class="btn btn-success"><?php echo translator::trans("search"); ?></button>
		<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo translator::trans('cancel'); ?></button>
	</div>
</div>
<?php
$this->the_footer();
