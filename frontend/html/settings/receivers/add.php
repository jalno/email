<?php
use \packages\base;
use \packages\base\translator;
use \packages\userpanel;
use \themes\clipone\utility;

$this->the_header();
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-plus"></i>
                <span><?php echo translator::trans("settings.email.receivers.add"); ?></span>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <form class="create_form" action="<?php echo userpanel\url('settings/email/receivers/add'); ?>" method="post">
						<div class="col-md-6">
							<?php
							$this->createField(array(
								'name' => 'title',
								'label' => translator::trans("email.receiver.title")
							));
							$this->createField(array(
								'name' => 'hostname',
								'label' => translator::trans("email.receiver.hostname"),
								'ltr' => true
							));
							$this->createField(array(
								'type' => 'number',
								'name' => 'port',
								'label' => translator::trans("email.receiver.port")
							));
							$this->createField(array(
								'name' => 'status',
								'type' => 'select',
								'label' => translator::trans("email.receiver.status"),
								'options' => $this->getStatusForSelect()
							));

							?>
						</div>
						<div class="col-md-6">
							<?php
							$this->createField(array(
								'type' => 'select',
								'name' => 'type',
								'label' => translator::trans("email.receiver.type"),
								'options' => $this->getTypesForSelect(),
								'ltr' => true
							));
							$this->createField(array(
								'type' => 'select',
								'name' => 'encryption',
								'label' => translator::trans("email.receiver.encryption"),
								'options' => $this->getEncryptionsForSelect(),
								'ltr' => true
							));
							$this->createField(array(
								'name' => 'username',
								'label' => translator::trans("email.receiver.username"),
								'ltr' => true
							));
							$this->createField(array(
								'type' => 'password',
								'name' => 'password',
								'label' => translator::trans("email.receiver.password"),
								'ltr' => true
							));
							?>
						</div>
						<div class="col-md-12">
			                <p>
			                    <a href="<?php echo userpanel\url('settings/email/receivers'); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('return'); ?></a>
			                    <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> <?php echo translator::trans("submit"); ?></button>
			                </p>
						</div>
	                </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$this->the_footer();
