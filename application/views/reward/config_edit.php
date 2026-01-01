<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <i class="fas fa-edit"></i> <?= translate('edit_reward_config'); ?>
                </h4>
            </div>

            <?php echo form_open($this->uri->uri_string(), ['class' => 'form-horizontal frm-submit-data']); ?>
            <div class="panel-body">
                <input type="hidden" name="reward_config_id" value="<?= $reward['id'] ?>" />

                <!-- Branch -->
                <?php if (is_superadmin_loggedin()): ?>
                    <div class="form-group mt-md">
                        <label class="col-md-3 control-label"><?= translate('branch') ?> <span
                                class="required">*</span></label>
                        <div class="col-md-6">
                            <?php
                            $arrayBranch = $this->app_lib->getSelectList('branch');
                            echo form_dropdown(
                                "branch_id",
                                $arrayBranch,
                                $reward['branch_id'] ?? '',
                                "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%' onchange='getClassByBranch(this.value)' required"
                            );
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Class -->
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

                <!-- Section -->
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

                <!-- Exam Type -->
                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('exam_type') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <select class="form-control" name="exam_type" id="exam_type" data-plugin-selectTwo
                            data-width="100%" required>
                            <option value="">Select</option>
                            <option value="offline" <?= $reward['exam_type'] == 'offline' ? 'selected' : '' ?>>
                                <?= translate('offline') ?>
                            </option>
                            <option value="online" <?= $reward['exam_type'] == 'online' ? 'selected' : '' ?>>
                                <?= translate('online') ?>
                            </option>
                            <option value="live_exam" <?= $reward['exam_type'] == 'live_exam' ? 'selected' : '' ?>>
                                <?= translate('live_exam') ?>
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Exam -->
                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('exam') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <select class="form-control" name="exam_id" id="exam_id" data-plugin-selectTwo data-width="100%"
                            required>
                            <option value="<?= $reward['exam_id'] ?>" selected><?= $reward['exam_name'] ?? '' ?>
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Reward Basis -->
                <div class="form-group live-only" <?= $reward['exam_type'] != 'live_exam' ? 'style="display:none;"' : '' ?>>
                    <label class="col-md-3 control-label"><?= translate('reward_basis') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <select class="form-control" name="reward_basis" id="reward_basis" data-plugin-selectTwo
                            data-width="100%">
                            <option value="percentage" <?= $reward['reward_basis'] == 'percentage' ? 'selected' : '' ?>>
                                <?= translate('percentage') ?>
                            </option>
                            <option value="rank" <?= $reward['reward_basis'] == 'rank' ? 'selected' : '' ?>>
                                <?= translate('rank') ?>
                            </option>
                            <option value="percentile" <?= $reward['reward_basis'] == 'percentile' ? 'selected' : '' ?>>
                                <?= translate('percentile') ?>
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Qualifying Value -->
                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('qualifying_value') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="qualifying_value" min="0" step="0.01"
                            value="<?= $reward['qualifying_value'] ?? '' ?>"
                            placeholder="e.g. 60 (for percentage) or 5 (for rank)" required>
                        <small class="help-block text-muted">
                            Value meaning depends on basis — percentage, percentile, or rank threshold.
                        </small>
                    </div>
                </div>

                <!-- Reward Scope (Exam / Session) -->
                <div class="form-group live-only" <?= $reward['exam_type'] != 'live_exam' ? 'style="display:none;"' : '' ?>>
                    <label class="col-md-3 control-label"><?= translate('reward_scope') ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="reward_scope" data-plugin-selectTwo data-width="100%">
                            <option value="exam" <?= $reward['reward_scope'] == 'exam' ? 'selected' : '' ?>>
                                <?= translate('per_exam') ?>
                            </option>
                            <option value="session" <?= $reward['reward_scope'] == 'session' ? 'selected' : '' ?>>
                                <?= translate('per_session') ?>
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Coin Reward -->
                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('coin_reward') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <input type="number" class="form-control" name="coin_reward" min="1" placeholder="e.g. 50"
                            value="<?= $reward['coin_reward'] ?? '' ?>" required>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label class="col-md-3 control-label"><?= translate('status') ?></label>
                    <div class="col-md-6">
                        <select class="form-control" name="is_active" data-plugin-selectTwo data-width="100%">
                            <option value="1" <?= $reward['is_active'] == 1 ? 'selected' : '' ?>><?= translate('active') ?>
                            </option>
                            <option value="0" <?= $reward['is_active'] == 0 ? 'selected' : '' ?>>
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
        const base = base_url;
        const selectedBranch = "<?= $reward['branch_id'] ?? '' ?>";
        const selectedClass = "<?= $reward['class_id'] ?? '' ?>";
        const selectedSection = "<?= $reward['section_id'] ?? '' ?>";
        const selectedExamType = "<?= $reward['exam_type'] ?>";
        const selectedExamId = "<?= $reward['exam_id'] ?? '' ?>";

        // Toggle visibility for live exam fields
        function toggleLiveFields() {
            const type = $('#exam_type').val();
            if (type === 'live_exam') {
                $('.live-only').slideDown(200);
            } else {
                $('.live-only').slideUp(200);
            }
        }
        toggleLiveFields();

        if (selectedBranch) {
            $('#branch_id').val(selectedBranch).trigger('change');

            $.post(base_url + 'ajax/getClassByBranch', { branch_id: selectedBranch }, function (classData) {
                $('#class_id').html(classData);

                // ✅ delay ensures Select2 picks up selected value
                setTimeout(function () {
                    $('#class_id').val(selectedClass).trigger('change.select2');
                }, 300);

                $.post(base_url + 'ajax/getSectionByClass', {
                    branch_id: selectedBranch,
                    class_id: selectedClass
                }, function (sectionData) {
                    $('#section_id').html(sectionData);

                    // ✅ same here
                    setTimeout(function () {
                        $('#section_id').val(selectedSection).trigger('change.select2');
                    }, 300);

                    if (selectedExamType) {
                        $.post(base_url + 'ajax/getExamByType', {
                            exam_type: selectedExamType,
                            branch_id: selectedBranch,
                            class_id: selectedClass,
                            section_id: selectedSection
                        }, function (examData) {
                            $('#exam_id').html(examData);
                            setTimeout(function () {
                                $('#exam_id').val(selectedExamId).trigger('change.select2');
                            }, 300);
                        });
                    }
                });
            });
        }


        // On Exam Type change
        $('#exam_type').on('change', function () {
            toggleLiveFields();

            const examType = $(this).val();
            const branchID = $('#branch_id').val();
            const classID = $('#class_id').val();
            const sectionID = $('#section_id').val();

            if (examType) {
                $.ajax({
                    url: base + 'ajax/getExamByType',
                    type: 'POST',
                    data: { exam_type: examType, branch_id: branchID, class_id: classID, section_id: sectionID },
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