<?php
use \packages\base\translator;
use \packages\userpanel;
use \packages\email\receiver;
use \themes\clipone\utility;
$this->the_header();
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-rss"></i> <?php echo translator::trans("settings.email.receivers"); ?>
				<div class="panel-tools">
					<?php if($this->canAdd){ ?>
					<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('add'); ?>" href="<?php echo userpanel\url('settings/email/receivers/add'); ?>"><i class="fa fa-plus"></i></a>
					<?php } ?>
					<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('search'); ?>" href="#search" data-toggle="modal"><i class="fa fa-search"></i></a>
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<?php
						$hasButtons = $this->hasButtons();
						?>
						<thead>
							<tr>
								<th class="center">#</th>
								<th><?php echo translator::trans('email.receiver.title'); ?></th>
								<th><?php echo translator::trans('email.receiver.hostname'); ?></th>
								<th><?php echo translator::trans('email.receiver.status'); ?></th>
								<?php if($hasButtons){ ?><th></th><?php } ?>
							</tr>
						</thead>
						<tbody>
						<?php
							foreach($this->getDataList() as $item){
								$this->setButtonParam('view', 'link', userpanel\url("settings/email/receivers/view/".$item->id));
								$this->setButtonParam('edit', 'link', userpanel\url("settings/email/receivers/edit/".$item->id));
								$this->setButtonParam('delete', 'link', userpanel\url("settings/email/receivers/delete/".$item->id));
								$statusClass = utility::switchcase($item->status, array(
									'label label-success' => receiver::active,
									'label label-danger' => receiver::deactive
								));
								$statusTxt = utility::switchcase($item->status, array(
									'email.receiver.status.active' => receiver::active,
									'email.receiver.status.deactive' => receiver::deactive
								));
						?>
						<tr>
							<td class="center"><?php echo $item->id; ?></td>
							<td><?php echo $item->title; ?></td>
							<td class="ltr"><?php
							echo("<span class=\"label label-warning\">");
							switch($item->type){
								case(receiver::IMAP):echo 'IMAP';break;
								case(receiver::POP3):echo 'POP3';break;
								case(receiver::NNTP):echo 'NNTP';break;
							}
							echo("</span> ");
							if($item->encryption){
								echo(" <span class=\"label label-primary\">");
								switch($item->encryption){
									case(receiver::SSL):echo 'SSL';break;
									case(receiver::TLS):echo 'TLS';break;
								}
								echo("</span> ");
							}
							echo $item->hostname.':'.$item->port;
							
							?></td>
							<td><span class="<?php echo $statusClass; ?>"><?php echo translator::trans($statusTxt); ?></span></td>
							<?php
							if($hasButtons){
								echo("<td class=\"center\">".$this->genButtons()."</td>");
							}
							?>
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
		<form id="receivers_search_form" class="form-horizontal" action="<?php echo userpanel\url("settings/email/receivers"); ?>" method="GET">
			<?php
			$this->setHorizontalForm('sm-3','sm-9');
			$feilds = array(
				array(
					'name' => 'id',
					'type' => 'number',
					'label' => translator::trans("email.receiver.id")
				),
				array(
					'name' => 'title',
					'label' => translator::trans("email.receiver.title")
				),
				array(
					'name' => 'hostname',
					'label' => translator::trans("email.receiver.hostname"),
					'ltr' => true
				),
				array(
					'name' => 'port',
					'label' => translator::trans("email.receiver.port"),
					'ltr' => true
				),
				array(
					'name' => 'username',
					'label' => translator::trans("email.receiver.username"),
					'ltr' => true
				),
				array(
					'type' => 'select',
					'name' => 'type',
					'label' => translator::trans("email.receiver.type"),
					'options' => $this->getTypesForSelect(),
					'ltr' => true
				),
				array(
					'type' => 'select',
					'name' => 'encryption',
					'label' => translator::trans("email.receiver.encryption"),
					'options' => $this->getEncryptionsForSelect(),
					'ltr' => true
				),
				array(
					'type' => 'select',
					'name' => 'status',
					'label' => translator::trans("email.receiver.status"),
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
		<button type="submit" form="receivers_search_form" class="btn btn-success"><?php echo translator::trans("search"); ?></button>
		<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo translator::trans('cancel'); ?></button>
	</div>
</div>
<?php
$this->the_footer();
