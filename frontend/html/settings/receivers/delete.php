<?php
use \packages\base\translator;
use \packages\userpanel;
$this->the_header();
$receiver = $this->getReceiver();
?>
<div class="row">
	<div class="col-md-12">
		<form action="<?php echo userpanel\url('settings/email/receivers/delete/'.$receiver->id); ?>" method="POST" role="form" class="form-horizontal">
			<div class="alert alert-block alert-warning fade in">
				<h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> <?php echo translator::trans('attention'); ?>!</h4>
				<p>
					<?php echo translator::trans("email.receiver.delete.warning", array('receiver' => $receiver->id)); ?>
				</p>
				<p>
					<a href="<?php echo userpanel\url('settings/email/receivers'); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('back'); ?></a>
					<button type="submit" class="btn btn-danger"><i class="fa fa-trash-o tip"></i> <?php echo translator::trans("delete") ?></button>
				</p>
			</div>
		</form>
	</div>
</div>
<?php
$this->the_footer();
