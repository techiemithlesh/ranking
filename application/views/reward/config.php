<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('select_ground') ?></h4>
                <div class="panel-btn">
                    <a href="javascript:void(0);" id="addConfig" class="btn btn-default btn-circle">
                        <i class="fas fa-plus-circle"></i> <?= translate('add_reward_config') ?>
                    </a>
                </div>
            </header>
            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate', 'id' => 'myFormId', 'method' => 'post')); ?>
            <div class="panel-body">
                <div class="row mb-sm">
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span
                                        class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
													data-plugin-selectTwo data-width='100%' data-placeholder='Search a branch'");
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                        <?php
                        $arrayClass = $this->app_lib->getSelectClassList($branch_id);
                        echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
												required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                        ?>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
													data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('Exam_type') ?> <span
                                class="required">*</span></label>
                        <?php
                        $selectedExamType = isset($filter_exam_type) ? $filter_exam_type : '';
                        ?>
                        <select class='form-control' name="exam_type" data-plugin-selectTwo data-width="100%"
                            id="exam_type">
                            <option value="">Select Exam Type</option>
                            <option value="offline" <?= ($selectedExamType == 'offline' ? 'selected' : '') ?>>
                                <?= translate('offline') ?>
                            </option>
                            <option value="online" <?= ($selectedExamType == 'online' ? 'selected' : '') ?>>
                                <?= translate('ONLINE') ?>
                            </option>
                            <option value="live_exam" <?= ($selectedExamType == 'live_exam' ? 'selected' : '') ?>>
                                <?= translate('live_exam') ?>
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span
                                    class="required">*</span></label>
                            <select name="exam_id" class="form-control" data-plugin-selectTwo
                                data-width="100%"></select>
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

        <!-- reward config view -->
        <?php if (!empty($reward_configs)): ?>
            <section class="panel">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-list-ul"></i>
                        <?php echo translate('reward_config') . " " . translate('list'); ?></h4>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-condensed mb-none dataTable">
                            <thead>
                                <tr>
                                    <th><?= translate('sl') ?></th>
                                    <th><?= translate('branch') ?></th>
                                    <th><?= translate('class') ?></th>
                                    <th><?= translate('section') ?></th>
                                    <th><?= translate('exam_type') ?></th>
                                    <th><?= translate('exam_name') ?></th>
                                    <th><?= translate('reward_basis') ?></th>
                                    <th><?= translate('qualifying_value') ?></th>
                                    <th><?= translate('reward_scope') ?></th>
                                    <th><?= translate('coin_reward') ?></th>
                                    <th><?= translate('status') ?></th>
                                    <th><?= translate('action') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reward_configs)): ?>
                                    <?php foreach ($reward_configs as $i => $config): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= $config['branch_name'] ?? 'N/A' ?></td>
                                            <td><?= $config['class_name'] ?? 'N/A' ?></td>
                                            <td><?= $config['section_name'] ?? 'N/A' ?></td>
                                            <td><span class="label label-default"><?= ucfirst($config['exam_type']) ?></span></td>
                                            <td><?= $config['exam_name'] ?? 'N/A' ?></td>
                                            <td>
                                                <?php
                                                switch ($config['reward_basis']) {
                                                    case 'rank':
                                                        echo '<span class="label label-primary">Rank Based</span>';
                                                        break;
                                                    case 'percentile':
                                                        echo '<span class="label label-info">Percentile Based</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="label label-success">Percentage Based</span>';
                                                        break;
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                if ($config['reward_basis'] == 'rank')
                                                    echo 'Top ≤ ' . $config['qualifying_value'];
                                                elseif ($config['reward_basis'] == 'percentile')
                                                    echo $config['qualifying_value'] . '%+';
                                                else
                                                    echo $config['qualifying_value'] . '%+';
                                                ?>
                                            </td>
                                            <td><span class="label label-warning"><?= ucfirst($config['reward_scope']) ?></span>
                                            </td>
                                            <td><strong class="text-success">+<?= $config['coin_reward'] ?></strong></td>
                                            <td>
                                                <?php if ($config['is_active']): ?>
                                                    <span style="color: green;">Active</span>
                                                <?php else: ?>
                                                    <span style="color: red;">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('rewards/configEdit/' . $config['id']) ?>"
                                                    class="btn btn-xs btn-default">
                                                    <i class="fa fa-pencil"></i> <?= translate('edit') ?>
                                                </a>
                                                <?= btn_delete_ajax('rewards/delete/' . $config['id']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center text-danger">
                                            <?= translate('no_reward_configuration_found') ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <div class="alert alert-info text-center mt-4">
                <strong><?= translate('reward_config') ?>:</strong> <?= translate('no_data_available') ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Reward Config Modal -->
<div id="addConfigModal" class="zoom-anim-dialog modal-block mfp-hide modal-block-lg">
    <section class="panel">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?= translate('add_reward_config'); ?></h4>
        </div>
        <?php if ($message = set_alert('error')): ?>
            <div class="alert alert-danger p-sm"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($message = set_alert('success')): ?>
            <div class="alert alert-success p-sm"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php echo form_open('Rewards/configSave', array('class' => 'form-horizontal frm-submit-data')); ?>
        <div class="panel-body">


            <?php if (is_superadmin_loggedin()): ?>
                <div class="form-group mt-md">
                    <label class="col-md-3 control-label"><?= translate('branch') ?> <span class="required">*</span></label>
                    <div class="col-md-9">
                        <?php
                        $arrayBranch = $this->app_lib->getSelectList('branch');
                        echo form_dropdown("branch_id", $arrayBranch, "", "class='form-control' id='modal_branch_id'
                            data-plugin-selectTwo data-width='100%' data-placeholder='Select Branch' required");
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <h5 class="text-primary mt-md mb-sm"><i class="fas fa-university"></i> <?= translate('basic_details') ?>
            </h5>
            <hr class="mt-xs mb-md" />

            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('class') ?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <select class="form-control" name="class_id" id="modal_class_id" data-plugin-selectTwo
                        data-width="100%" required>
                        <option value=""><?= translate('select_branch_first') ?></option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('section') ?> <span
                        class="required">*</span></label>
                <div class="col-md-9">
                    <select class="form-control" name="section_id" id="modal_section_id" data-plugin-selectTwo
                        data-width="100%" required>
                        <option value=""><?= translate('select_class_first') ?></option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('exam_type') ?> <span
                        class="required">*</span></label>
                <div class="col-md-9">
                    <select class="form-control" name="exam_type" id="modal_exam_type" data-plugin-selectTwo
                        data-width="100%" required>
                        <option value=""><?= translate('select') ?></option>
                        <option value="offline"><?= translate('offline') ?></option>
                        <option value="online"><?= translate('online') ?></option>
                        <option value="live_exam"><?= translate('live_exam') ?></option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('exam') ?> <span class="required">*</span></label>
                <div class="col-md-9">
                    <select class="form-control" name="exam_id" id="modal_exam_id" data-plugin-selectTwo
                        data-width="100%" required>
                        <option value=""><?= translate('select_exam_type_first') ?></option>
                    </select>
                </div>
            </div>

            <!-- Dynamic Reward Basis & Scope -->
            <h5 class="text-primary mt-md mb-sm"><i class="fas fa-gift"></i> <?= translate('reward_settings') ?></h5>
            <hr class="mt-xs mb-md" />

            <div class="form-group" style="display: none;">
                <label class="col-md-3 control-label"><?= translate('reward_basis') ?> <span
                        class="required">*</span></label>
                <div class="col-md-9">
                    <select class="form-control" name="reward_basis" id="reward_basis" data-plugin-selectTwo
                        data-width="100%" required>
                        <option value="percentage"><?= translate('percentage_based') ?></option>
                        <option value="rank"><?= translate('rank_based') ?></option>
                        <option value="percentile"><?= translate('percentile_based') ?></option>
                    </select>
                    <small class="text-muted">
                        * Percentage → Reward by marks%, Rank → Reward top X ranks, Percentile → Reward by performance
                        percentile.
                    </small>
                </div>
            </div>

            <div class="form-group" style="display: none;">
                <label class="col-md-3 control-label"><?= translate('reward_scope') ?> <span
                        class="required">*</span></label>
                <div class="col-md-9">
                    <select class="form-control" name="reward_scope" id="reward_scope" data-plugin-selectTwo
                        data-width="100%" required>
                        <option value="exam"><?= translate('per_exam_once') ?></option>
                        <option value="session"><?= translate('per_session_each_time') ?></option>
                    </select>
                    <small class="text-muted">
                        * Exam → Reward once per exam. Session → Reward each live exam attempt.
                    </small>
                </div>
            </div>


            <div class="form-group">
                <label class="col-md-3 control-label" for="qualifying_value"><?= translate('qualifying_value') ?> <span
                        class="required">*</span></label>
                <div class="col-md-9">
                    <input type="number" class="form-control" name="qualifying_value" id="qualifying_value" min="0"
                        required placeholder="e.g. 80">
                    <small class="text-muted">
                        * <?= translate('value_represents_percentage_rank_or_percentile_based_on_selected_basis') ?>
                    </small>
                </div>
            </div>


            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('coin_reward') ?> <span
                        class="required">*</span></label>
                <div class="col-md-9">
                    <input type="number" class="form-control" name="coin_reward" min="1" required placeholder="e.g. 50">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label"><?= translate('status') ?></label>
                <div class="col-md-9">
                    <select class="form-control" name="is_active" data-plugin-selectTwo data-width="100%">
                        <option value="1"><?= translate('active') ?></option>
                        <option value="0"><?= translate('inactive') ?></option>
                    </select>
                </div>
            </div>
        </div>

        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-default mr-xs" id="savebtn"
                        data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                        <i class="fas fa-save"></i> <?= translate('save') ?>
                    </button>
                    <button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close(); ?>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        $('#addConfig').on('click', function () {
            resetModalForm();
            mfp_modal('#addConfigModal');
        });

        $('#branch_id').on('change', function () {
            var branchID = $(this).val();
            getClassByBranch(branchID);
            getSectionByClass(0, 0); // Reset sections
            $('#exam_type').val('').trigger('change');
            $('select[name="exam_id"]').html('<option value="">Select Exam Type First</option>');
        });


        $('#class_id').on('change', function () {
            var classID = $(this).val();
            getSectionByClass(classID, 0);
            $('#exam_type').val('').trigger('change');
            $('select[name="exam_id"]').html('<option value="">Select Exam Type First</option>');
        });

        // Modal branch change - FIXED
        $('#modal_branch_id').on('change', function () {
            var branchID = $(this).val();
            //   console.log('Modal branch changed:', branchID);

            if (branchID) {
                // Load classes for selected branch
                $.ajax({
                    url: base_url + 'ajax/getClassByBranch',
                    type: 'POST',
                    data: { branch_id: branchID },
                    success: function (response) {
                        console.log('Classes response:', response);
                        $('#modal_class_id').html(response);
                        $('#modal_class_id').trigger('change.select2');
                    },
                    error: function (xhr, status, error) {
                        console.error("Error loading classes:", error);
                        $('#modal_class_id').html('<option value="">Error loading classes</option>');
                    }
                });

                // Reset dependent dropdowns
                $('#modal_section_id').html('<option value="">Select Class First</option>');
                $('#modal_exam_type').val('').trigger('change');
                $('#modal_exam_id').html('<option value="">Select Exam Type First</option>');
            } else {
                // Reset all dependent dropdowns
                $('#modal_class_id').html('<option value="">Select Branch First</option>');
                $('#modal_section_id').html('<option value="">Select Class First</option>');
                $('#modal_exam_type').val('').trigger('change');
                $('#modal_exam_id').html('<option value="">Select Exam Type First</option>');
            }
        });

        // Modal class change - FIXED
        $('#modal_class_id').on('change', function () {
            var classID = $(this).val();
            var branchID = $('#modal_branch_id').val();
            // console.log('Modal class changed:', classID, 'Branch:', branchID);

            if (classID && branchID) {
                // Load sections for selected class
                $.ajax({
                    url: base_url + 'ajax/getSectionByClass',
                    type: 'POST',
                    data: {
                        class_id: classID,
                        branch_id: branchID
                    },
                    success: function (response) {
                        console.log('Sections response:', response);
                        $('#modal_section_id').html(response);
                        $('#modal_section_id').trigger('change.select2');
                    },
                    error: function (xhr, status, error) {
                        console.error("Error loading sections:", error);
                        $('#modal_section_id').html('<option value="">Error loading sections</option>');
                    }
                });

                // Reset dependent dropdowns
                $('#modal_exam_type').val('').trigger('change');
                $('#modal_exam_id').html('<option value="">Select Exam Type First</option>');
            } else {
                $('#modal_section_id').html('<option value="">Select Class First</option>');
                $('#modal_exam_type').val('').trigger('change');
                $('#modal_exam_id').html('<option value="">Select Exam Type First</option>');
            }
        });

        // Modal section change
        $('#modal_section_id').on('change', function () {
            $('#modal_exam_type').val('').trigger('change');
            $('#modal_exam_id').html('<option value="">Select Exam Type First</option>');
        });

        // Main form exam type change
        $('#exam_type').on('change', function () {
            var examType = $(this).val();
            var branchID = $('#branch_id').val();
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();

            if (examType) {
                $.ajax({
                    url: base_url + 'ajax/getExamByType',
                    type: 'POST',
                    data: {
                        exam_type: examType,
                        branch_id: branchID,
                        class_id: classID,
                        section_id: sectionID
                    },
                    success: function (data) {
                        $('select[name="exam_id"]').html(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            } else {
                $('select[name="exam_id"]').html('<option value="">Select Exam Type First</option>');
            }
        });

        // Modal exam type change - FIXED
        $('#modal_exam_type').on('change', function () {
            var examType = $(this).val();
            var branchID = $('#modal_branch_id').val();
            var classID = $('#modal_class_id').val();
            var sectionID = $('#modal_section_id').val();

            if (examType === 'live_exam') {
                $('#reward_basis').closest('.form-group').show();
                $('#reward_scope').closest('.form-group').show();
                $('#reward_scope').closest('.form-group').show();
            } else {
                $('#reward_basis').closest('.form-group').hide();
                $('#reward_scope').closest('.form-group').hide();
                $('#reward_basis').val('percentage').trigger('change');
                $('#reward_scope').val('exam').trigger('change');
            }

            if (examType && branchID && classID && sectionID) {
                $.ajax({
                    url: base_url + 'ajax/getExamByType',
                    type: 'POST',
                    data: {
                        exam_type: examType,
                        branch_id: branchID,
                        class_id: classID,
                        section_id: sectionID
                    },
                    success: function (data) {
                        console.log('Exams response:', data);
                        $('#modal_exam_id').html(data);
                        $('#modal_exam_id').trigger('change.select2');
                    },
                    error: function (xhr, status, error) {
                        console.error("Error loading exams:", error);
                        $('#modal_exam_id').html('<option value="">Error loading exams</option>');
                    }
                });
            } else {
                $('#modal_exam_id').html('<option value="">Please select all fields above</option>');
            }
        });

    });


    $('#reward_basis').on('change', function () {
        let val = $(this).val();
        let label = '<?= translate('minimum_percentage') ?>';
        if (val === 'rank') label = '<?= translate('top_rank_upto') ?>';
        else if (val === 'percentile') label = '<?= translate('minimum_percentile') ?>';
        $('label[for="qualifying_value"]').text(label);
    });


    // Function to reset modal form
    function resetModalForm() {
        $('#modal_branch_id').val('').trigger('change');
        $('#modal_class_id').html('<option value="">Select Branch First</option>');
        $('#modal_section_id').html('<option value="">Select Class First</option>');
        $('#modal_exam_type').val('').trigger('change');
        $('#modal_exam_id').html('<option value="">Select Exam Type First</option>');
        $('input[name="min_percentage"]').val('');
        $('input[name="coin_reward"]').val('');
        $('select[name="is_active"]').val('1').trigger('change');
    }
</script>