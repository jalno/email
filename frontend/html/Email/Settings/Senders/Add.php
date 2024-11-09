<?php
use packages\base\Translator;
use packages\email\Sender\Address;
use packages\userpanel;

$this->the_header();
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-plus"></i>
                <span><?php echo Translator::trans('settings.email.senders.add'); ?></span>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <form class="senders_form" action="<?php echo userpanel\url('settings/email/senders/add'); ?>" method="post">
						<div class="addressesfields"></div>
						<div class="col-md-6">
							<?php
                            $this->createField([
                                'name' => 'title',
                                'label' => Translator::trans('email.sender.title'),
                            ]);
$this->createField([
    'name' => 'sender',
    'type' => 'select',
    'label' => Translator::trans('email.sender.type'),
    'options' => $this->getSendersForSelect(),
]);
$this->createField([
    'name' => 'status',
    'type' => 'select',
    'label' => Translator::trans('email.sender.status'),
    'options' => $this->getSenderStatusForSelect(),
]);
?>

							<table class="table table-addresses">
								<thead>
									<tr>
										<th>#</th>
										<th><?php echo Translator::trans('email.address'); ?></th>
										<th><?php echo Translator::trans('email.address.name'); ?></th>
										<th><?php echo Translator::trans('email.address.status'); ?></th>
										<th><?php echo Translator::trans('email.address.primary'); ?></th>
										<th class="table-tools">
											<a class="btn btn-xs btn-link btn-address-add tooltips" title="<?php echo Translator::trans('email.address.add'); ?>" href="#address-add" data-toggle="modal"><i class="fa fa-plus"></i></a>
										</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
						<div class="col-md-6">
							<?php
foreach ($this->getSenders()->get() as $sender) {
    $name = $sender->getName();
    echo "<div class=\"senderfields sender-{$name}\">";
    foreach ($sender->getFields() as $field) {
        $this->createField($field);
    }
    echo '</div>';
}
?>
						</div>
						<div class="col-md-12">
			                <p>
			                    <a href="<?php echo userpanel\url('settings/email/senders'); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo Translator::trans('return'); ?></a>
			                    <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> <?php echo Translator::trans('submit'); ?></button>
			                </p>
						</div>
	                </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="address-add" tabindex="-1" data-show="true" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title"><?php echo Translator::trans('email.address.add'); ?></h4>
	</div>
	<div class="modal-body">
		<form id="address_add_form" class="form-horizontal" action="#" method="POST">
			<?php
            $this->setHorizontalForm('sm-3', 'sm-9');
$feilds = [
    [
        'type' => 'email',
        'name' => 'address',
        'label' => Translator::trans('email.address'),
        'ltr' => true,
    ],
    [
        'name' => 'name',
        'label' => Translator::trans('email.address.name'),
    ],
    [
        'type' => 'select',
        'name' => 'status',
        'label' => Translator::trans('email.address.status'),
        'options' => [
            [
                'value' => Address::active,
                'title' => Translator::trans('email.address.status.active'),
            ],
            [
                'value' => Address::deactive,
                'title' => Translator::trans('email.address.status.deactive'),
            ],
        ],
    ],
    [
        'type' => 'checkbox',
        'label' => Translator::trans('email.address.primary'),
        'name' => 'primary',
        'options' => [
            [
                'value' => 1,
            ],
        ],
    ],
];
foreach ($feilds as $input) {
    echo $this->createField($input);
}
?>
		</form>
	</div>
	<div class="modal-footer">
		<button type="submit" form="address_add_form" class="btn btn-success"><?php echo Translator::trans('submit'); ?></button>
		<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo Translator::trans('cancel'); ?></button>
	</div>
</div>
<div class="modal fade" id="address-delete" tabindex="-1" data-show="true" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title"><?php echo Translator::trans('email.address.delete'); ?></h4>
	</div>
	<div class="modal-body">
		<form id="address_delete_form" class="form-horizontal" action="#" method="POST">
			<input type="hidden" name="address" value="">
			<p>آیا شما از حذف این شماره مطمئن هستید؟</p>
		</form>
	</div>
	<div class="modal-footer">
		<button type="submit" form="address_delete_form" class="btn btn-danger"><?php echo Translator::trans('submit'); ?></button>
		<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo Translator::trans('cancel'); ?></button>
	</div>
</div>
<?php
$this->the_footer();
