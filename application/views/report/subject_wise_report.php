<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><i class="fas fa-filter"></i> <?= translate('select_ground') ?></h4>
            </header>

            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate', 'id' => 'reportForm', 'method' => 'post')); ?>
            <div class="panel-body">
                <div class="row">
                    <input type="hidden" name="exam_type" id="exam_type" value="live_exam">

                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-3 mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'");
                                ?>
                            </div>
                        </div>
                        <?php $grid = "col-md-3"; ?>
                    <?php else: ?>
                        <input type="hidden" id="branch_id" name="branch_id" value="<?= get_loggedin_branch_id(); ?>">
                        <?php $grid = "col-md-4"; ?>
                    <?php endif; ?>

                    <div class="<?= $grid ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                            <?php
                            $arrayClass = $this->app_lib->getSelectClassByBranch($branch_id);
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' required data-plugin-selectTwo data-width='100%'");
                            ?>
                        </div>
                    </div>

                    <div class="<?= $grid ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required data-plugin-selectTwo data-width='100%'");
                            ?>
                        </div>
                    </div>

                    <div class="<?= $grid ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span class="required">*</span></label>
                            <select name="exam_id" id="exam_id" class="form-control" data-plugin-selectTwo data-width="100%">
                                <?php if (!empty($exam_id)):
                                    $examName = $this->db->select('title')->where('id', $exam_id)->get('online_exam')->row()->title; ?>
                                    <option value="<?= $exam_id ?>" selected><?= $examName ?></option>
                                <?php else: ?>
                                    <option value=""><?= translate('select_class_section_first') ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('session') ?></label>
                            <select class="form-control" name="session_code" id="session_code" data-plugin-selectTwo data-width="100%">
                                <?php if (!empty($sessionCode)): ?>
                                    <option value="<?= $sessionCode ?>" selected><?= $sessionCode ?></option>
                                <?php else: ?>
                                    <option value=""><?= translate('select_exam_first') ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('subject') ?></label>
                            <select name="subject_id" id="subject_id" class="form-control" data-plugin-selectTwo data-width="100%">
                                <?php if (!empty($subject_id)):
                                    $subjectName = $this->db->select('name')->where('id', $subject_id)->get('subject')->row()->name; ?>
                                    <option value="<?= $subject_id ?>" selected><?= $subjectName ?></option>
                                <?php else: ?>
                                    <option value=""><?= translate('select_exam_first') ?></option>
                                <?php endif; ?>
                            </select>
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

        <?php if (isset($report_rows)): ?>
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
                                <?php if (!empty($report_rows)): $i = 1;
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
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-danger"><?= translate('no_records_found'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var selectedExam = <?= json_encode($exam_id ?? '') ?>;
        var selectedSubject = <?= json_encode($subject_id ?? '') ?>;
        var selectedSession = <?= json_encode($sessionCode ?? '') ?>;

        // 1. Branch change logic
        $('#branch_id').on('change', function() {
            var branchID = $(this).val();
            if (typeof getClassByBranch === 'function') {
                getClassByBranch(branchID);
            }
        });

        // 2. Class/Section change logic
        $('#class_id, #section_id').on('change', function(e, isInitial) {
            var branchID = $('#branch_id').val();
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();

            if (classID && sectionID) {
                $.ajax({
                    url: base_url + "ajax/getExamByType",
                    type: 'POST',
                    data: {
                        exam_type: 'live_exam',
                        branch_id: branchID,
                        class_id: classID,
                        section_id: sectionID
                    },
                    success: function(data) {
                        $('#exam_id').html(data);
                        if (selectedExam) {
                            $('#exam_id').val(selectedExam).trigger('change', [true]);
                            if (!isInitial) selectedExam = null;
                        }
                    }
                });
            }
        });

        // 3. Exam change logic
        $('#exam_id').on('change', function(e, isInitial) {
            var exam_id = $(this).val();

            if (!exam_id) {
                $('#session_code, #subject_id').html('<option value=""><?= translate('select_exam_first') ?></option>').trigger('change');
                return;
            }

            // ONLY show "Loading..." if this isn't the initial page load trigger
            if (!isInitial) {
                $('#session_code, #subject_id').html('<option value="">Loading...</option>').trigger('change');
            }

            // Fetch Sessions
            $.ajax({
                url: base_url + 'LiveExam/getSessionsByExamWithAll',
                method: "POST",
                data: {
                    exam_id: exam_id
                },
                success: function(data) {
                    $('#session_code').html(data);
                    if (selectedSession) {
                        $('#session_code').val(selectedSession).trigger('change');
                        selectedSession = null;
                    }
                }
            });

            // Fetch Subjects
            $.ajax({
                url: base_url + 'ajax/getSubjectByExam',
                method: "POST",
                data: {
                    exam_id: exam_id
                },
                success: function(data) {
                    $('#subject_id').html(data);
                    if (selectedSubject) {
                        $('#subject_id').val(selectedSubject).trigger('change');
                        selectedSubject = null;
                    }
                }
            });
        });

        // Initial Trigger on page load
        if ($('#class_id').val() && $('#section_id').val()) {
            $('#section_id').trigger('change', [true]);
        }
    });
</script>