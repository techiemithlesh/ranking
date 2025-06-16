<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-cloud-upload-alt"></i> <?= translate('Progress_Report') ?></h4>
    </header>
    <div class="panel-body">
        <!-- EXAM SELECTION -->
        <div class="row mb-md">
            <div class="col-md-6 mb-sm">
                <input type="hidden" name="student_id" id="student_id" value="<?= $stu['student_id'] ?>" />
                <div class="form-group">
                    <label class="control-label"><?= translate('exam') ?> <span class="required">*</span></label>
                    <?php
                    $arrayExam = array("" => translate('select'));
                    if (!empty($exams)) {
                        foreach ($exams as $row) {
                            $arrayExam[$row->id] = $this->application_model->exam_name_by_id($row->id);
                        }
                    }
                    echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), 
                        "class='form-control' id='exam_id' required data-plugin-selectTwo data-width='100%'");
                    ?>
                </div>
            </div>
        </div>

        <!-- REPORT CARD CONTAINER -->
        <div id="report_cards" style="display: none;">
            <div class="row">
                <div class="col-sm-3">
                    <div class="card mt-20">
                        <img class="card-img-top"
                            src="<?= base_url('assets/reports/skillbased_template.jpg'); ?>"
                            alt="Report Image" style="width:100%; height: 415px;">
                        <div class="card-body">
                            <h4 class="card-title">Skill-Based Report</h4>
                            <a href="#" class="btn btn-primary"
                                 onclick="setSession('<?= base_url('report/skill_based_report') ?>')">
                                Visit Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ERROR MESSAGE CONTAINER -->
        <div id="error_msg"></div>
    </div>
</section>

<script>
    $(document).ready(function () {
        $('#report_cards').hide();

        $('#exam_id').on('change', function () {
            $('#report_cards').toggle($(this).val() !== "");
        });
    });

    function setSession(tourl) {
        $('#error_msg').html('');
        var examID = $('#exam_id').val();
        var studentId = $('#student_id').val();

        if (!examID) {
            $('#error_msg').html('<p style="color:red;">Please select an exam.</p>');
            return false;
        }

        $.ajax({
            url: "<?= base_url('userrole/ReportCardSessionSet') ?>",
            type: "POST",
            data: { exam_id: examID, student_id: studentId },
            dataType: "json",
            success: function (data) {
                if (data.status) {
                    window.location.href = tourl;
                } else {
                    $('#error_msg').html('<p style="color:red;">' + data.message + '</p>');
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                $('#error_msg').html('<p style="color:red;">Something went wrong. Please try again later.</p>');
            }
        });
    }
</script>
