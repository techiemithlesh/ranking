<section class="panel">
    <div class="panel-heading">
        <h4 class="panel-title"><?= translate('assign_subject_to_branch') ?></h4>
    </div>
    <div class="panel-body">
        <?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit')); ?>

        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('branch') ?> <span class="required">*</span></label>
            <div class="col-md-6">
                <?php
                $arrayBranch = $this->app_lib->getSelectBranchGlobal('branch');
                echo form_dropdown("branch_id[]", $arrayBranch, set_value('branch_id[]'), "class='form-control' id='branch_id'
									data-width='100%' data-plugin-selectTwo multiple  data-placeHolder='Search Branch'");
                ?>
                <span class="error"></span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('subjects') ?> <span class="required">*</span></label>
            <div class="col-md-6">
                <?php
                $arraySubject = $this->app_lib->getSelectSubjectGlobal();
                echo form_dropdown(
                    "subject_assign[]",
                    $arraySubject,
                    set_value('subject_assign[]'),
                    "class='form-control' id='subject_assign' multiple
                data-width='100%' data-plugin-selectTwo data-placeholder='Select Subject'"
                );
                ?>
            </div>
        </div>

        <div class="form-group mt-lg">
            <div class="col-md-offset-3 col-md-6">
                <button type="submit" class="btn btn-default">
                    <i class="fas fa-save"></i> <?= translate('save') ?>
                </button>
            </div>
        </div>

        <?php echo form_close(); ?>
    </div>
</section>

<section class="panel">
    <div class="panel-heading">
        <h4 class="panel-title"><?= translate('assigned_subject_list') ?></h4>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-condensed">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= translate('branch') ?></th>
                        <th><?= translate('subject_name') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    if (count($assigned_subject_list)) {
                        foreach ($assigned_subject_list as $row):
                            ?>
                            <tr>
                                <td><?= $count++; ?></td>
                                <td><?= $row['branch_name']; ?></td>
                                <td><?= $row['subject_name']; ?></td>
                            </tr>
                            <?php
                        endforeach;
                    } else {
                        echo '<tr><td colspan="3" class="text-center">' . translate('no_information_available') . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>