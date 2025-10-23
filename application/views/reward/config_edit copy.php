<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <div class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-plus-circle"></i> <?= translate('edit_reward_config'); ?></h4>
            </div>
            <?php echo form_open($this->uri->uri_string(), array('class' => 'form-horizontal frm-submit-data')); ?>
            <div class="panel-body">
                <input type="hidden" name="reward_config_id" value="<?=$reward['id'] ?>"/>
                <?php if (is_superadmin_loggedin()): ?>
                    <div class="form-group mt-md">
                        <label class="col-md-3 control-label"><?= translate('branch') ?> <span
                                class="required">*</span></label>
                        <div class="col-md-6">
                            <?php
                            $arrayBranch = $this->app_lib->getSelectList('branch');
                            echo form_dropdown("branch_id", $arrayBranch, $reward['branch_id'] ?? '', "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%' onchange='getClassByBranch(this.value)' required");
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('class') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <select class="form-control" name="class_id" id="class_id" data-plugin-selectTwo
                            data-width="100%" onchange='getSectionByClass(this.value,0,1)' required>
                            <?php if (isset($reward['class_id'])): ?>
                                <option value="<?= $reward['class_id'] ?>" selected><?= $reward['class_name'] ?></option>
                            <?php else: ?>
                                <option value=""><?= translate('select_branch_first') ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('section') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <select class="form-control" name="section_id" id="section_id" data-plugin-selectTwo
                            data-width="100%" required>
                            <?php if (isset($reward['section_id'])): ?>
                                <option value="<?= $reward['section_id'] ?>" selected><?= $reward['section_name'] ?>
                                </option>
                            <?php else: ?>
                                <option value=""><?= translate('select_class_first') ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('exam_type') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <select class="form-control" name="exam_type" id="exam_type" data-plugin-selectTwo
                            data-width="100%" required>
                            <option value=""><?= translate('select') ?></option>
                            <option value="offline" <?= ($reward['exam_type'] ?? '') == 'offline' ? 'selected' : '' ?>>
                                <?= translate('offline') ?>
                            </option>
                            <option value="online" <?= ($reward['exam_type'] ?? '') == 'online' ? 'selected' : '' ?>>
                                <?= translate('online') ?>
                            </option>
                            <option value="live_exam" <?= ($reward['exam_type'] ?? '') == 'live_exam' ? 'selected' : '' ?>>
                                <?= translate('live_exam') ?>
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('exam') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <select class="form-control" name="exam_id" id="exam_id" data-plugin-selectTwo data-width="100%"
                            required>
                            <?php if (isset($reward['exam_id'])): ?>
                                <option value="<?= $reward['exam_id'] ?>" selected><?= $reward['exam_name'] ?></option>
                            <?php else: ?>
                                <option value=""><?= translate('select_exam_type_first') ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('min_percentage') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="min_percentage" min="0" max="100" required
                            placeholder="e.g. 60" value="<?= $reward['min_percentage'] ?? '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('coin_reward') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="coin_reward" min="1" required
                            placeholder="e.g. 50" value="<?= $reward['coin_reward'] ?? '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('status') ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="is_active" data-plugin-selectTwo data-width="100%">
                            <option value="1" <?= ($reward['is_active'] ?? '') == 1 ? 'selected' : '' ?>>
                                <?= translate('active') ?>
                            </option>
                            <option value="0" <?= ($reward['is_active'] ?? '') == 0 ? 'selected' : '' ?>>
                                <?= translate('inactive') ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-center">
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
</div>


<script type="text/javascript">
    $(document).ready(function () {
        const selectedBranch = "<?= $reward['branch_id'] ?? '' ?>";
        const selectedClass = "<?= $reward['class_id'] ?? '' ?>";
        const selectedSection = "<?= $reward['section_id'] ?? '' ?>";
        const selectedExamType = "<?= $reward['exam_type'] ?? '' ?>";
        const selectedExamId = "<?= $reward['exam_id'] ?? '' ?>";

        if (selectedBranch) {
            $('#branch_id').val(selectedBranch).trigger('change');

            $.post(base_url + 'ajax/getClassByBranch', { branch_id: selectedBranch }, function (classData) {
                $('#class_id').html(classData).val(selectedClass).trigger('change');

                $.post(base_url + 'ajax/getSectionByClass', {
                    branch_id: selectedBranch,
                    class_id: selectedClass
                }, function (sectionData) {
                    $('#section_id').html(sectionData).val(selectedSection).trigger('change');

                    if (selectedExamType) {
                        $.post(base_url + 'ajax/getExamType', {
                            exam_type: selectedExamType,
                            branch_id: selectedBranch,
                            class_id: selectedClass,
                            section_id: selectedSection
                        }, function (examData) {
                            $('#exam_id').html(examData).val(selectedExamId).trigger('change');
                        });
                    }
                });
            });
        }

        $('#exam_type').on('change', function () {
            var examType = $(this).val();
            var branchID = $('#branch_id').val();
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();

            if (examType) {
                $.ajax({
                    url: base_url + 'ajax/getExamType',
                    type: 'POST',
                    data: {
                        exam_type: examType,
                        branch_id: branchID,
                        class_id: classID,
                        section_id: sectionID
                    },
                    success: function (data) {
                        $('#exam_id').html(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            } else {
                $('#exam_id').html('<option value="">Select Exam Type First</option>');
            }
        });
    });


</script>