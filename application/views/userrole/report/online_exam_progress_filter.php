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
                            <label class="control-label"><?= translate('exam') ?> <span class="required">*</span></label>
                            <select name="exam_id" id="examID" class="form-control" data-plugin-selectTwo data-width="100%" required>
                                <option value=""><?= translate('select') ?></option>
                                <?php
                                $studentID = get_loggedin_user_id();
                                $exams = $this->db->query("
                                    SELECT DISTINCT oe.id, oe.title
                                    FROM online_exam_submitted oes
                                    INNER JOIN online_exam oe ON oe.id = oes.online_exam_id
                                    WHERE oes.student_id = ?
                                    ORDER BY oe.title ASC
                                ", [$studentID])->result();

                                foreach ($exams as $exam) {
                                    echo '<option value="' . $exam->id . '">' . $exam->title . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mt-4 pt-2" style="margin-top: 20px;">
                        <button type="submit" name="search" value="1" class="btn btn-warning btn-block" style="margin-top:8px;">
                            <i class="fas fa-chart-line"></i> <?= translate('generate_report') ?>
                        </button>
                    </div>

                </div>
            </div>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>
