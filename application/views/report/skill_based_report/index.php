<?php $widget = (is_superadmin_loggedin() ? 2 : 3); ?>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <?php echo form_open($this->uri->uri_string()); ?>
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('select_ground') ?></h4>
            </header>
            <div class="panel-body">
                <div class="row mb-sm">
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-2 mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span
                                        class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
									data-plugin-selectTwo data-width='100%' data-placeHolder='Select Branch'");
                                ?>
                            </div>
                            <span class="error"><?= form_error('branch_id') ?></span>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="branch_id" id="branch_id" value="<?= get_loggedin_branch_id(); ?>" />
                    <?php endif; ?>

                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span
                                    class="required">*</span></label>
                            <?php
                            if (isset($branch_id)) {
                                $arrayExam = array("" => translate('select'));
                                $exams = $this->db->get_where('exam', array('branch_id' => $branch_id, 'session_id' => get_session_id()))->result();
                                foreach ($exams as $row) {
                                    $arrayExam[$row->id] = $this->application_model->exam_name_by_id($row->id);
                                }
                            } else {
                                $arrayExam = array("" => translate('select_branch_first'));
                            }
                            echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), "class='form-control' id='exam_id' data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                            <span class="error"><?= form_error('exam_id') ?></span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arrayClass = $this->app_lib->getClass($branch_id);
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                            <span class="error"><?= form_error('class_id') ?></span>
                        </div>
                    </div>

                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                            <span class="error"><?= form_error('section_id') ?></span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label"><?= translate('student') ?> <span
                                    class="required">*</span></label>

                            <select data-plugin-selectTwo class="form-control" name="student_id" id="student_id">

                            </select>
                            <span class="error"><?= form_error('subject_id') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="submit" name="search" value="1" class="btn btn btn-default btn-block"> <i
                                class="fas fa-filter"></i> <?= translate('get_report') ?></button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <?php if (!empty($this->session->userdata('reportData'))): ?>

            <section class="panel appear-animation" data-appear-animation="<?php echo $global_config['animations']; ?>"
                data-appear-animation-delay="100">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-list-ol"></i> <?= translate('skillbase_report_card'); ?></h4>
                </header>
                <div class="panel-body">
                    <div class="mb-md mt-md">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="card mt-20">
                                    <img class="card-img-top"
                                        src="<?php echo base_url('assets/reports/skillbased_template.jpg'); ?>"
                                        alt="Card image" style="width:100%;height: 415px;">
                                    <div class="card-body">
                                        <h4 class="card-title">Skill Based Report</h4>
                                        <a href="<?= base_url('report/skill_based_report') ?>" class="btn btn-primary"
                                            target="_blank"
                                            onclick="setSession('<?= base_url('report/annual_examination_report') ?>')">Visit
                                            Report</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-sm" id="error_msg">
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        var branchID = $('#branch_id').val();
        if (branchID) {
            getClassByBranch(branchID);
            getExamByBranch(branchID);
            $('#subject_id').html('').append('<option value=""><?= translate("select") ?></option>');
        }

        $('#branch_id').on('change', function () {
            var branchID = $(this).val();
            getClassByBranch(branchID);
            getExamByBranch(branchID);
            $('#subject_id').html('').append('<option value=""><?= translate("select") ?></option>');
        });


        $('#section_id').on('change', function () {
            var section_id = $(this).val();
            var class_id = $('#class_id').val();
            var branch_id = ($("#branch_id").length ? $('#branch_id').val() : "");
            getStudentByClass(branch_id, class_id, section_id);
        });
    });

    function getStudentByClass(branch_id, class_id, section_id) {
        var student_id = "<?= set_value('student_id') ?>";
        $.ajax({
            url: base_url + 'ajax/getStudentByClass',
            type: 'POST',
            data: {
                branch_id: branch_id,
                class_id: class_id,
                section_id: section_id,
                student_id: student_id
            },
            success: function (data) {
                $('#student_id').html(data);
            }
        });
    }

    function setSession(tourl) {
        $('#error_msg').html('');
        $.ajax({
            url: "<?= base_url('Report/examReportSessionStore') ?>",
            type: "post",
            data: $("#myFormId").serialize(),
            dataType: "json",
            success: function (data) {
                if (data?.status) {
                    window.location.href = tourl;
                } else {
                    $('#error_msg').html('<p style="color:red;">' + data?.message + '</p>');
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    }

</script>