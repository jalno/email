<?php
use packages\base\Translator;
use packages\userpanel;

$this->the_header();
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-plus"></i>
                <span><?php echo Translator::trans('email.send'); ?></span>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
            </div>
            <div class="panel-body">
                <form class="create_form " action="<?php echo userpanel\url('email/send'); ?>" method="post">
					<?php
                    $this->createField([
                        'type' => 'select',
                        'name' => 'from',
                        'label' => Translator::trans('email.sender.address'),
                        'ltr' => true,
                        'options' => $this->getAddressesForSelect(),
                    ]);
$this->createField([
    'name' => 'to',
    'label' => Translator::trans('email.receiver.address'),
    'ltr' => true,
]);
$this->createField([
    'name' => 'subject',
    'label' => Translator::trans('email.subject'),
]);
$this->createField([
    'type' => 'textarea',
    'name' => 'html',
    'class' => 'form-control ckeditor',
    'label' => Translator::trans('email.text'),
]);
?>
					<hr>
					<div class="row">
						<div class="col-md-4 col-md-offset-4">
							<div class="btn-group col-xs-12">
								<button type="submit" class="btn btn-success col-xs-10"><i class="fa fa-paper-plane"></i> <?php echo Translator::trans('send'); ?></button>
								<span class="btn btn-file  btn-success  col-xs-2">
									<i class="fa fa-paperclip"></i>
									<input type="file" name="attachments[]" multiple="mutliple">
								</span>
							</div>
						</div>
					</div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $this->the_footer();
