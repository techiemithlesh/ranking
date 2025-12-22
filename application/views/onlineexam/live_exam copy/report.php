<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('online_exam_report') ?></h4>
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
													data-plugin-selectTwo data-width='100%' data-placeholder='Search a brnach'");
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                        <?php
                        if (!is_superadmin_loggedin()) {
                            $branch_id = get_loggedin_branch_id();
                        }
                        $arrayClass = $this->app_lib->getSelectClassByBranch($branch_id);
                        echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
												required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                        ?>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
													data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' "); ?>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span
                                    class="required">*</span></label>
                            <select name="exam_id" id="examID" class="form-control" data-plugin-selectTwo
                                data-width="100%"></select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('session') ?> <span class="required">*</span></label>

                        <select class='form-control' name="session_code" data-plugin-selectTwo data-width="100%"
                            id="session_code">

                        </select>
                    </div>

                </div>
                <div class="row mt-20">
                    <div class="col-md-12 text-center">
                        <button type="submit" name="search" value="1"
                            class="btn btn-primary"><?= translate('filter') ?></button>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<?php if (!empty($reports) && count($reports) > 0): ?>
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title">
                <i class="fas fa-list-ul"></i> <?= translate('online_exam') . " " . translate('list') ?>
            </h4>
        </header>
        <div class="panel-body">
            <table class="table table-bordered table-condensed table-hover table-export report-list" width="100%">
                <thead>
                    <tr>
                        <th><?= translate('sl') ?></th>
                        <th><?= translate('student_name') ?></th>
                        <th><?= translate('roll') ?> (<?= translate('section') ?>)</th>
                        <th><?= translate('exam_name') ?></th>
                        <th><?= translate('exam_date') ?></th>
                        <th><?= translate('total_question') ?></th>
                        <th><?= translate('attempted') ?></th>
                        <th><?= translate('correct') ?></th>
                        <th><?= translate('wrong') ?></th>
                        <th><?= translate('obtained') ?></th>
                        <th><?= translate('total_marks') ?></th>
                        <th><?= translate('percentage') ?></th>
                        <th><?= translate('result') ?></th>
                        <th><?= translate('rank') ?>/<?= translate('total_students') ?></th>
                        <th><?= translate('action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= html_escape($row['student_name']) ?></td>
                            <td><?= html_escape($row['roll']) ?> (<?= html_escape($row['section_name']) ?>)</td>
                            <td><?= html_escape($row['exam_name']) ?></td>
                            <td><?= html_escape($row['exam_date']) ?></td>
                            <td><?= $row['total_question'] ?></td>
                            <td><?= $row['total_answered'] ?></td>
                            <td><?= $row['correct_ans'] ?></td>
                            <td><?= $row['wrong_ans'] ?></td>
                            <td><?= $row['total_obtain_marks'] ?></td>
                            <td><?= $row['total_marks'] ?></td>
                            <td><?= $row['percentage'] ?>%</td>
                            <td><?= $row['result_status'] ?></td>
                            <td><?= $row['rank'] ?>/<?= $row['total_students'] ?></td>
                            <td>
                                <a href="<?= base_url('Liveexam_student/verify?session=' . $row['session_code'] . '&student=' . $row['student_id']) ?>"
                                    class="btn btn-default btn-xs" target="_blank">
                                    <i class="fas fa-file-pdf"></i> <?= translate('view_report') ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php else: ?>
    <div class="alert alert-warning text-center">
        <?= translate('no_records_found') ?>
    </div>
<?php endif; ?>



<!-- JS Scripts -->
<script type="text/javascript">
    $(document).ready(function () {
        $('#class_id').on('change', function () {
            var class_id = $(this).val();
            var branch_id = $("#branch_id").length ? $('#branch_id').val() : "";
            $.ajax({
                url: base_url + 'LiveExam/getLiveExamByClass',
                type: 'POST',
                data: { class_id: class_id },
                success: function (data) {
                    $('#examID').html(data);
                }
            });
        });

        $('#branch_id').on('change', function () {
            let branchID = $(this).val();
            getClassByBranch(branchID);
        });

        $('#examID').on('change', function () {
            let exam_id = $(this).val();

            $.ajax({
                url: base_url + 'LiveExam/getSessionsByExam',
                type: 'POST',
                data: { exam_id: exam_id },
                success: function (data) {
                    $('#session_code').html(data);
                }
            });
        });
    });
</script>