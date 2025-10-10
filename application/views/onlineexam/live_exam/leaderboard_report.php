<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('leaderboard_report') ?></h4>
            </header>

            <?php echo form_open($this->uri->uri_string(), ['class' => 'validate', 'id' => 'myFormId', 'method' => 'post']); ?>
            <div class="panel-body">
                <div class="row mb-sm">
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown(
                                    "branch_id",
                                    $arrayBranch,
                                    set_value('branch_id', $this->input->post('branch_id')),
                                    "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%'"
                                );
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
                        echo form_dropdown(
                            "class_id",
                            $arrayClass,
                            set_value('class_id', $this->input->post('class_id')),
                            "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' required 
                             data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'"
                        );
                        ?>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id', $this->input->post('class_id')), false);
                            echo form_dropdown(
                                "section_id",
                                $arraySection,
                                set_value('section_id', $this->input->post('section_id')),
                                "class='form-control' id='section_id' required data-plugin-selectTwo data-width='100%'"
                            );
                            ?>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span class="required">*</span></label>
                            <select name="exam_id" id="examID" class="form-control" data-plugin-selectTwo data-width="100%">
                                <?php if (!empty($examID)): ?>
                                    <option value="<?= $examID; ?>" selected>
                                        <?= get_type_tittle_by_id('online_exam', $examID); ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('session') ?></label>
                        <select class="form-control" name="session_code" id="session_code" data-plugin-selectTwo data-width="100%">
                            <?php if (!empty($sessionCode)): ?>
                                <option value="<?= $sessionCode; ?>" selected><?= $sessionCode; ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="row mt-20">
                    <div class="col-md-12 text-center">
                        <button type="submit" name="search" value="1" class="btn btn-primary">
                            <i class="fas fa-filter"></i> <?= translate('filter') ?>
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
            <h4 class="panel-title"><i class="fas fa-list-ol"></i> <?= translate('leaderboard') ?></h4>
        </header>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-condensed mb-none datatable-export">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= translate('rank') ?></th>
                            <th><?= translate('student') ?></th>
                            <th><?= translate('class') ?></th>
                            <th><?= translate('correct') ?></th>
                            <th><?= translate('wrong') ?></th>
                            <th><?= translate('skipped') ?></th>
                            <th><?= translate('marks') ?></th>
                            <th><?= translate('percentage') ?></th>
                            <th><?= translate('percentile') ?></th>
                            <th><?= translate('action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach ($reports as $row): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><span class="badge bg-primary"><?= $row['rank_position']; ?></span></td>
                                <td><?= $row['first_name'] . " " . $row['last_name']; ?></td>
                                <td><?= $row['class_name'] . " - " . $row['section_name']; ?></td>
                                <td class="text-success"><?= $row['correct_ans']; ?></td>
                                <td class="text-danger"><?= $row['wrong_ans']; ?></td>
                                <td><?= $row['total_skipped']; ?></td>
                                <td><strong><?= $row['obtain_marks']; ?>/<?= $row['total_marks']; ?></strong></td>
                                 <td><?= $row['percentage']; ?>%</td>
                                <td><?= $row['percentile']; ?>%</td>
                                <td>
                                   
                                    <a href="<?= base_url('Liveexam_student/verify?session=' . $row['session_code'] . '&student=' . $row['student_id']) ?>" 
                                       target="_blank" class="btn btn-sm btn-info">
                                       <i class="fas fa-eye"></i> <?= translate('view') ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
<?php else: ?>
    <div class="alert alert-warning text-center"><?= translate('no_records_found') ?></div>
<?php endif; ?>


<script type="text/javascript">
$(document).ready(function () {
    var preExamId = "<?= $examID; ?>";
    var preSessionCode = "<?= $sessionCode; ?>";

    $('#class_id').on('change', function () {
        var class_id = $(this).val();
        $.ajax({
            url: base_url + 'LiveExam/getLiveExamByClass',
            type: 'POST',
            data: { class_id: class_id },
            success: function (data) {
                $('#examID').html(data);
                if (preExamId) {
                    $('#examID').val(preExamId).trigger('change');
                }
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
            url: base_url + 'LiveExam/getSessionsByExamWithAll',
            type: 'POST',
            data: { exam_id: exam_id },
            success: function (data) {
                $('#session_code').html(data);
                if (preSessionCode) {
                    $('#session_code').val(preSessionCode).trigger('change');
                }
            }
        });
    });

    
});
</script>
