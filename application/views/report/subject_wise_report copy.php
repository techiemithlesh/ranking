<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                <div>
                    <h4 class="panel-title" style="display:inline-block; margin:0;">
                        <?= translate('select_ground') ?>
                    </h4>
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
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%' data-placeholder='Search a branch'");
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <input type="hidden" id="branch_id" name="branch_id" value="<?= get_loggedin_branch_id(); ?>">
                    <?php endif; ?>

                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                        <?php
                        $arrayClass = $this->app_lib->getSelectClassByBranch($branch_id);
                        echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' required data-plugin-selectTwo data-width='100%'");
                        ?>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required data-plugin-selectTwo data-width='100%'");
                            ?>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('Exam_type') ?> <span
                                class="required">*</span></label>
                        <select class='form-control' name="exam_type" data-plugin-selectTwo data-width="100%"
                            id="exam_type">
                            <option value="">Select Exam Type</option>
                            <option value="live_exam" <?= ($exam_type == 'live_exam' ? 'selected' : '') ?>>
                                <?= translate('live_exam') ?>
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-sm exam-col">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?></label>
                            <select name="exam_id" id="exam_id" class="form-control" data-plugin-selectTwo
                                data-width="100%"></select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm" id="session_code_container">
                        <label class="control-label"><?= translate('session') ?></label>
                        <select class="form-control" name="session_code" id="session_code" data-plugin-selectTwo
                            data-width="100%">
                            <?php if (!empty($sessionCode)): ?>
                                <option value="<?= $sessionCode; ?>" selected><?= $sessionCode; ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-sm subject-col">
                        <div class="form-group">
                            <label class="control-label"><?= translate('subject') ?></label>
                            <select name="subject_id" id="subject_id" class="form-control" data-plugin-selectTwo
                                data-width="100%"></select>
                        </div>
                    </div>

                </div>
            </div>

            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="submit" name="search" value="1" class="btn btn-default btn-block">
                            <i class="fas fa-filter"></i> <?= translate('filter') ?>
                        </button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('subject_wise_report_card'); ?></h4>
            </header>

            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover mb-none">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= translate('student_name') ?></th>
                                <th><?= translate('total_questions') ?></th>
                                <th><?= translate('skipped_questions') ?></th>
                                <th><?= translate('wrong__questions') ?></th>
                                <th><?= translate('obtained_marks') ?></th>
                                <th><?= translate('total_marks') ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($report_rows)): ?>
                                <?php $i = 1;
                                foreach ($report_rows as $row): ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= html_escape($row['full_name']); ?></td>
                                        <td><?= (int) $row['total_questions']; ?></td>
                                        <td><?= (int) $row['skipped']; ?></td>
                                        <td><?= (int) $row['wrong']; ?></td>
                                        <td><?= (float) $row['obtained_marks']; ?></td>
                                        <td><?= (float) $row['total_marks']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-danger">
                                        <?= translate('no_records_found'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        var selectExamType = <?= json_encode($exam_type ?? null) ?>;
        var selectedExam = <?= json_encode($exam_id ?? '') ?>;
        var selectedSubject = <?= json_encode($subject_id ?? '') ?>;
        var selectedSession = <?= json_encode($sessionCode ?? '') ?>;

        if (selectExamType !== 'live_exam') {
            $('#session_code_container').hide();
        }

        // 🔥 Auto-load subjects for NON-superadmin
        <?php if (!is_superadmin_loggedin()): ?>
            let branchID = $('#branch_id').val();
            $.ajax({
                url: base_url + 'ajax/getSubjectByBranch',
                method: 'POST',
                data: { branch_id: branchID },
                success: function (data) {
                    $('#subject_id').html(data);

                    if (selectedSubject) {
                        $('#subject_id').val(selectedSubject).trigger('change');
                    }
                }
            });
        <?php endif; ?>
        
        $('#branch_id').on('change', function () {
            var branchID = $(this).val();
            getClassByBranch(branchID);

            $.ajax({
                url: base_url + 'ajax/getSubjectByBranch',
                method: 'POST',
                data: { branch_id: branchID },
                success: function (data) {
                    $('#subject_id').html(data);

                    if (selectedSubject) {
                        $('#subject_id').val(selectedSubject).trigger('change');
                    }
                }
            })
        });

        $('#exam_type').on('change', function () {
            var examType = $(this).val();
            var branchID = $('#branch_id').val();
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();

            if (examType === 'live_exam') {
                $('#session_code_container').show();
            } else {
                $('#session_code_container').hide();
            }

            if (examType !== '') {
                $.ajax({
                    url: base_url + "ajax/getExamType",
                    type: 'POST',
                    data: {
                        exam_type: examType,
                        branch_id: branchID,
                        class_id: classID,
                        section_id: sectionID
                    },
                    success: function (data) {
                        $('#exam_id').html(data);

                        if (selectedExam) {
                            $('#exam_id').val(selectedExam).trigger('change');
                        }
                    }
                });
            }
        });

        $('#exam_id').on('change', function () {
            var exam_id = $(this).val();

            if (!exam_id) return;

            $.ajax({
                url: base_url + 'LiveExam/getSessionsByExamWithAll',
                method: "POST",
                data: { exam_id: exam_id },
                success: function (data) {
                    $('#session_code').html(data);

                    if (selectedSession) {
                        $('#session_code').val(selectedSession).trigger('change');
                    }
                }
            })
        });

    });
</script>