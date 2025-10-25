<style>
    .panel-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .session-toggle {
        display: flex;
        align-items: center;
        gap: 5px;
        /* Adds spacing between checkbox and text */
    }

    .session-toggle input[type="checkbox"] {
        margin-left: 5px;
    }

    #toggleSessionFilterLabel {
        margin-bottom: 0px;
    }
</style>
<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading d-flex justify-content-between align-items-center">
                <h4 class="panel-title"><?= translate('select_ground') ?></h4>
            </header>

            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
            <div class="panel-body">
                <div class="row mb-sm">
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span
                                        class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' onchange='getClassByBranch(this.value)'
								data-plugin-selectTwo data-width='100%'");
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arrayClass = $this->app_lib->getSelectClassList($branch_id);
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,1)'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), true);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>

                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="submit" name="search" value="1" class="btn btn-default btn-block"> <i
                                class="fas fa-filter"></i> <?= translate('filter') ?></button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <?php if (isset($rewards)): ?>
            <section class="panel appear-animation" data-appear-animation="<?= $global_config['animations'] ?>"
                data-appear-animation-delay="100">
                <header class="panel-heading">
                    <div class="panel-btn">
                        <button class="btn btn-default btn-circle" id="student_bulk_delete"
                            data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                            <i class="fas fa-trash-alt"></i> <?= translate('bulk_delete') ?>
                        </button>
                    </div>
                    <h4 class="panel-title"><i class="fas fa-user-graduate"></i> <?php echo translate('student_list'); ?>
                    </h4>
                </header>
                <div class="panel-body mb-md">
                    <table class="table table-bordered table-condensed table-hover table-export">
                        <thead>
                            <tr>
                                <th><?= translate('name') ?></th>
                                <th><?= translate('register_no') ?></th>
                                <th><?= translate('class') ?></th>
                                <th><?= translate('section') ?></th>
                                <th><?= translate('Current_rewards') ?></th>
                                <th><?= translate('action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rewards)): ?>
                                <?php foreach ($rewards as $row): ?>
                                    <tr>
                                        <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
                                        <td><?= $row['register_no'] ?></td>
                                        <td><?= $row['class_name'] ?></td>
                                        <td><?= $row['section_name'] ?></td>
                                        <td><?= $row['total_coins'] ?></td>
                                        <td>
                                            <a href="javascript:void(0);" onclick="studentRewardView('<?= $row['student_id'] ?>');"
                                                class="btn btn-default btn-circle icon" data-toggle="tooltip"
                                                data-original-title="<?= translate('reward_history') ?>">
                                                <i class="fas fa-coins"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted"><?= translate('no_records_found') ?></td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="quickView">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title">
                <i class="fas fa-gift"></i> <?= translate('reward_history') ?>
            </h4>
        </header>

        <div class="panel-body">
            <div class="text-center mb-md">
                <h4 class="text-weight-semibold" id="quick_full_name">Student Name</h4>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-condensed mb-none">
                    <thead>
                        <tr>
                            <th><?= translate('exam') ?></th>
                            <th><?= translate('exam_type') ?></th>
                            <th><?= translate('earned_coins') ?></th>
                            <th><?= translate('remarks') ?></th>
                            <th><?= translate('date') ?></th>
                        </tr>
                    </thead>
                    <tbody id="reward_table_body">
                        <!-- JS will populate this -->
                        <tr>
                            <td colspan="5" class="text-center text-muted"><?= translate('loading') ?>...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-default modal-dismiss"><?= translate('close') ?></button>
                </div>
            </div>
        </footer>
    </section>
</div>



<script type="text/javascript">
    $(document).ready(function () {


    });
</script>