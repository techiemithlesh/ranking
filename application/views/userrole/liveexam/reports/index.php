<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading bg-yellow" style="font-weight:600;">
                <i class="fas fa-chart-bar"></i> <?= translate('online_exam_progress') ?>
            </header>

            <?php echo form_open($this->uri->uri_string(), ['class' => 'validate', 'id' => 'myFormId', 'method' => 'post']); ?>
            <div class="panel-body">
                <div class="row d-flex align-items-center justify-content-center">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span
                                    class="required">*</span></label>
                            <select name="exam_id" id="examID" class="form-control" required data-plugin-selectTwo
                                data-width="100%"></select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label"><?= translate('session') ?> <span
                                    class="required">*</span></label>
                            <select class='form-control' required name="session_code" data-plugin-selectTwo
                                data-width="100%" id="session_code"></select>

                        </div>
                    </div>

                    <div class="col-md-3 mt-4 pt-2" style="margin-top: 20px;">
                        <button type="submit" name="search" value="1" class="btn btn-warning btn-block"
                            style="margin-top:8px;">
                            <i class="fas fa-chart-line"></i> <?= translate('generate_report') ?>
                        </button>
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
                                    <i class="fas fa-file-pdf"></i> <?= translate('view') ?>
                                </a>

                                <a href="<?= base_url('Liveexam_student/download?session=' . $row['session_code'] . '&student=' . $row['student_id']) ?>"
                                    class="btn btn-default btn-xs">
                                    <i class="fas fa-file-pdf"></i> <?= translate('download') ?>
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




<script type="text/javascript">
    $(document).ready(function () {
        var branch_id = "<?= $studentDetails['branch_id']; ?>";
        var class_id = "<?= $studentDetails['class_id']; ?>";

        var selectedExam = "<?= isset($examID) ? $examID : '' ?>";
        var selectedSession = "<?= isset($sessionCode) ? $sessionCode : '' ?>";

        $.ajax({
            url: base_url + 'LiveExam/getLiveExamByClass',
            type: 'POST',
            data: { class_id: class_id },
            success: function (data) {
                console.log("data", data);
                $('#examID').html(data);

                if (selectedExam) {
                    $('#examID').val(selectedExam).trigger('change');
                }
            }

        });


        $('#examID').on('change', function () {
            let exam_id = $(this).val();
            $.ajax({
                url: base_url + 'LiveExam/getSessionsByExam',
                type: 'POST',
                data: { exam_id: exam_id },
                success: function (data) {
                    $('#session_code').html(data);

                    if (selectedSession) {
                        $('#session_code').val(selectedSession).trigger('change');
                    }
                }
            });
        });
    });
</script>