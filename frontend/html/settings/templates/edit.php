<?php
use \packages\base;
use \packages\base\json;
use \packages\base\translator;
use \packages\userpanel;
use \packages\userpanel\date;
use \packages\email\gateway\number;
use \themes\clipone\utility;

$this->the_header();
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-edit"></i>
                <span><?php echo translator::trans("settings.email.templates.edit"); ?></span>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
            </div>
            <div class="panel-body">
                <form class="create_form" action="<?php echo userpanel\url('settings/email/templates/edit/'.$this->getTemplate()->id); ?>" method="post">
					<div class="col-md-6">
						<?php
						$this->createField(array(
							'type' => 'select',
							'name' => 'name',
							'label' => translator::trans("email.template.name"),
							'options' => $this->getTemplatesForSelect()
						));
						$this->createField(array(
							'type' => 'select',
							'name' => 'lang',
							'label' => translator::trans("email.template.lang"),
							'options' => $this->getLanguagesForSelect()
						));
						$this->createField(array(
							'name' => 'subject',
							'label' => translator::trans("email.template.subject")
						));
						$this->createField(array(
							'type' => 'textarea',
							'name' => 'html',
							'label' => translator::trans("email.template.text"),
							'rows' => 4,
							'class' => 'form-control ckeditor'
						));
						$this->createField(array(
							'name' => 'status',
							'type' => 'select',
							'label' => translator::trans("email.template.status"),
							'options' => $this->getTemplateStatusForSelect()
						));
						?>
					</div>
					<div class="col-md-6">
						<div class="table-responsive container-table-variables">
			                <table class="table table-variables">
								<thead>
									<tr>
										<th><?php echo translator::trans('email.template.variable.key'); ?></th>
										<th><?php echo translator::trans('email.template.variable.description'); ?></th>
									</tr>
								</thead>
								<tbody>

								</tbody>
							</table>
						</div>
					</div>
					<div class="col-md-12">
		                <p>
		                    <a href="<?php echo userpanel\url('settings/email/templates'); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('return'); ?></a>
		                    <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> <?php echo translator::trans("submit"); ?></button>
		                </p>
					</div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$this->the_footer();
