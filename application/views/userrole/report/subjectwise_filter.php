<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading bg-yellow" style="font-weight:600;">
                <i class="fas fa-chart-pie"></i> <?= translate('subject_wise_exam_progress') ?>
            </header>

            <?php echo form_open($this->uri->uri_string(), ['class' => 'validate', 'id' => 'subjectWiseForm', 'method' => 'post']); ?>
            <div class="panel-body">
                <div class="row d-flex align-items-center justify-content-center">

                    <div class="col-md-4 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('subject') ?> <span
                                    class="required">*</span></label>
                            <select name="subject_id" id="subject_id" class="form-control" data-plugin-selectTwo
                                data-width="100%" required>
                                <option value=""><?= translate('select') ?></option>
                                <?php
                                $studentID = get_loggedin_user_id();
                                $student = $this->application_model->getStudentDetails($studentID);
                                $subjects = $this->db->query("
                                    SELECT s.id, s.name FROM subject s
                                    INNER JOIN subject_assign sa ON sa.subject_id = s.id
                                    WHERE sa.class_id = ? AND sa.section_id = ? AND sa.branch_id = ?
                                ", [$student['class_id'], $student['section_id'], $student['branch_id']])->result();

                                foreach ($subjects as $sub) {
                                    echo '<option value="' . $sub->id . '">' . $sub->name . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mt-4 pt-2" style="margin-top: 20px;">
                        <button type="submit" name="search" value="1" class="btn btn-warning btn-block"
                            style="margin-top:8px;">
                            <i class="fas fa-chart-bar"></i> <?= translate('generate_report') ?>
                        </button>
                    </div>

                </div>
            </div>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>