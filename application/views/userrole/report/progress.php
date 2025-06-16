<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-cloud-upload-alt"></i> <?= translate('Progress_Report') ?></h4>
    </header>
    <div class="panel-body">
        <!-- EXAM TYPE CONTAINER START HERE -->
        <div class="row mb-md">
            <div class="col-md-6 mb-sm">
                <div class="form-group">
                    <label class="control-label"><?= translate('exam') ?> <span class="required">*</span></label>
                    <?php
                    $arrayExam = array("" => translate('select'));
                    if (!empty($exams)) {
                        foreach ($exams as $row) {
                            $arrayExam[$row->id] = $this->application_model->exam_name_by_id($row->id);
                        }
                    }
                    echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), "class='form-control' id='exam_id' 
                        required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                    ?>
                </div>
            </div>
        </div>
        <!-- EXAM TYPE CONTAINER END HERE -->

        <!-- REPORT CARD CONTAINER START HERE -->
        <div id="report_cards" style="display: none;">
            <div class="row">
                <div class="col-sm-3">
                    <div class="card mt-20">
                    <img class="card-img-top"
									src="<?php echo base_url('assets/reports/50b31774-42c8-45fd-a724-afd588e063eqda.jpeg'); ?>"
									alt="Card image" style="width:100%;height: 415px;">
                        <div class="card-body">
                        <h4 class="card-title">Student Progress Report</h4>
                            <a href="#" class="btn btn-primary"
                                onclick="setSession('<?= base_url('userrole/annual_examination_report') ?>')">Visit
                                Report</a>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-sm-3">
                    <div class="card mt-20">
                        <img class="card-img-top"
                            src="<?php echo base_url('assets/reports/70d145f2-a29f-4e6c-a43c-90c09f5e742a75.png'); ?>"
                            alt="Card image" style="width:100%;height: 415px;">
                        <div class="card-body">
                            <h4 class="card-title">Skill Based - Mount Kilimanjaro Report</h4>
                            <a href="<?= base_url('report/skill_based_report') ?>" class="btn btn-primary"
                                target="_blank">Visit Report</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mt-20">
                        <img class="card-img-top"
                            src="<?php echo base_url('assets/reports/5e2b5355-1w585-4f38-a9e1-ee4fb982fbfe1.png'); ?>"
                            alt="Card image" style="width:100%;height: 415px;">
                        <div class="card-body">
                            <h4 class="card-title">Subject Based - Mount Etna Report</h4>
                            <a href="<?= base_url('report/subject_based_report') ?>" class="btn btn-primary"
                                target="_blank">Visit Report</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mt-20">
                        <img class="card-img-top"
                            src="<?php echo base_url('assets/reports/e4e3c2cd-7149-43d6-92c4-983b821fc1bb.jpg'); ?>"
                            alt="Card image" style="width:100%;height: 415px;">
                        <div class="card-body">
                            <h4 class="card-title">Exam Type + Skill Based - Mount Nemrut Report</h4>
                            <a href="<?= base_url('report/type_skill_based_report') ?>" class="btn btn-primary"
                                target="_blank">Visit Report</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mt-20">
                        <img class="card-img-top"
                            src="<?php echo base_url('assets/reports/5c1f48f7-f611-4315-aaad-9c9157f3e448.png'); ?>"
                            alt="Card image" style="width:100%;height: 415px;">
                        <div class="card-body">
                            <h4 class="card-title">Term End Report</h4>
                            <a href="<?= base_url('report/term_end_report') ?>" class="btn btn-primary"
                                target="_blank">Visit Report</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mt-20">
                        <img class="card-img-top"
                            src="<?php echo base_url('assets/reports/Mount_Miller_Skill_Based_Report_With_Student_Image11.png'); ?>"
                            alt="Card image" style="width:100%;height: 415px;">
                        <div class="card-body">
                            <h4 class="card-title">Grade Book Report</h4>
                            <a href="<?= base_url('report/grade_book_report') ?>" class="btn btn-primary"
                                target="_blank">Visit Report</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mt-20">
                        <img class="card-img-top"
                            src="<?php echo base_url('assets/reports/term_end_report_versin_2.png'); ?>"
                            alt="Card image" style="width:100%;height: 415px;">
                        <div class="card-body">
                            <h4 class="card-title">Term End Report Version 2</h4>
                            <a href="<?= base_url('report/term_end_report_versin_2') ?>" class="btn btn-primary"
                                target="_blank">Visit Report</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mt-20">
                        <img class="card-img-top"
                            src="<?php echo base_url('assets/reports/term_end_report_versin_3.png'); ?>"
                            alt="Card image" style="width:100%;height: 415px;">
                        <div class="card-body">
                            <h4 class="card-title">Term End Report Version 3</h4>
                            <a href="<?= base_url('report/term_end_report_versin_3') ?>" class="btn btn-primary"
                                target="_blank">Visit Report</a>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
        <!-- REPORT CARD CONTAINER END HERE -->
        <div id="error_msg"></div>
    </div>
</section>

<script>
    $(document).ready(function () {
        $('#exam_id').on('change', function () {
            var examID = $(this).val();
            if (examID) {
                $('#report_cards').show();
            } else {
                $('#report_cards').hide();
            }
        });
    });

    function setSession(tourl) {
        $('#error_msg').html('');
        var examID = $('#exam_id').val();

        console.log("FUnciton call", examID);

        if (!examID) {
            $('#error_msg').html('<p style="color:red;">Please select an exam</p>');
            return false;
        }


        $.ajax({
            url: "<?= base_url('Userrole/setExamId') ?>",
            type: "post",
            data: { exam_id: examID },
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
        })
    }

</script>