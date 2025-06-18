<section class="panel">
    <div class="panel-heading">
        <h4 class="panel-title"><?= translate('assign_class_to_branch') ?></h4>
    </div>
    <div class="panel-body">
        <?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered frm-submit')); ?>

        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('branch') ?> <span class="required">*</span></label>
            <div class="col-md-6">
                <?php
                $arrayBranch = $this->app_lib->getSelectBranchGlobal('branch');
                echo form_dropdown(
                    "branch_id[]",
                    $arrayBranch,
                    set_value('branch_id[]'),
                    "class='form-control' id='branch_id' multiple
                    data-width='100%' data-plugin-selectTwo data-placeholder='Select Branch'"
                );
                ?>
                <span class="error"></span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-3 control-label"><?= translate('classes') ?> <span class="required">*</span></label>
            <div class="col-md-6">
                <?php
                $arrayClass = $this->app_lib->getSelectListGlobal('class');
                echo form_dropdown(
                    "class_assign[]",
                    $arrayClass,
                    set_value('class_assign[]'),
                    "class='form-control' id='class_assign' multiple
                data-width='100%' data-plugin-selectTwo data-placeholder='Select Class'"
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
        <h4 class="panel-title"><?= translate('assigned_class_list') ?></h4>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-condensed dataTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= translate('branch') ?></th>
                        <th><?= translate('class_name') ?></th>
                        <th><?= translate('action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    if (count($assigned_class_list)) {
                        foreach ($assigned_class_list as $row):
                            ?>
                            <tr>
                                <td><?= $count++; ?></td>
                                <td><?= $row['branch_name']; ?></td>
                                <td><?= $row['class_name']; ?></td>
                                <td><?php echo btn_delete('classes/deleteClassAssign/' . $row['id']); ?></td>
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

<script type="text/javascript">

    $(document).ready(function () {
        $('.dataTable').dataTable({
            "pagelenght": 10,
        });
    })
</script>