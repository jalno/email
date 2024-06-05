<?php
use \packages\base;
use \packages\base\Json;
use \packages\base\Translator;
use \packages\userpanel;
use \packages\userpanel\Date;
use \packages\email\GateWay\Number;
use \themes\clipone\Utility;

$this->the_header();
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-edit"></i>
                <span><?php echo Translator::trans("settings.email.templates.edit"); ?></span>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
            </div>
            <div class="panel-body">
                <form class="tempates_form" action="<?php echo userpanel\url('settings/email/templates/edit/'.$this->getTemplate()->id); ?>" method="post">
					<div class="col-md-6">
						<?php
						$this->createField(array(
							'type' => 'select',
							'name' => 'name',
							'label' => Translator::trans("email.template.name"),
							'options' => $this->getTemplatesForSelect()
						));
						$this->createField(array(
							'type' => 'select',
							'name' => 'lang',
							'label' => Translator::trans("email.template.lang"),
							'options' => $this->getLanguagesForSelect()
						));
						$this->createField(array(
							'name' => 'subject',
							'label' => Translator::trans("email.template.subject")
						));
						$this->createField(array(
							'name' => 'status',
							'type' => 'select',
							'label' => Translator::trans("email.template.status"),
							'options' => $this->getTemplateStatusForSelect()
						));
						?>
					</div>
					<div class="col-md-6">
						<div class="table-responsive container-table-variables">
			                <table class="table table-variables">
								<thead>
									<tr>
										<th><?php echo Translator::trans('email.template.variable.key'); ?></th>
										<th><?php echo Translator::trans('email.template.variable.description'); ?></th>
									</tr>
								</thead>
								<tbody>

								</tbody>
							</table>
						</div>
					</div>
					<div class="col-xs-12">
						<?php 
							$this->createField(array(
								'type' => 'textarea',
								'name' => 'html',
								'label' => Translator::trans("email.template.text"),
								'rows' => 4,
								'class' => 'form-control ckeditor'
							));
						?>
					</div>
					<div class="col-md-12">
		                <p>
		                    <a href="<?php echo userpanel\url('settings/email/templates'); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo Translator::trans('return'); ?></a>
		                    <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> <?php echo Translator::trans("submit"); ?></button>
		                </p>
					</div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$this->the_footer();
