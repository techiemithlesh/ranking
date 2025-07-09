<?php $widget = (is_superadmin_loggedin() ? 3 : 4); ?>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('select_ground') ?></h4>
            </header>
            <div class="panel-body">
                <div class="row mb-sm">
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-3 mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span
                                        class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
								data-plugin-selectTwo data-width='100%' data-placeHolder='Select Branch'");
                                ?>
                                <span class="error"><?= form_error('branch_id') ?></span>
                            </div>
                        </div>
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
                            echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), "class='form-control' id='exam_id' required data-plugin-selectTwo
								data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span
                                    class="required">*</span></label>
                            <?php
                           $arrayClass = $this->app_lib->getSelectClassByBranch($branch_id);
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('subject') ?> <span
                                    class="required">*</span></label>
                            <?php
                            if (!empty(set_value('class_id'))) {
                                $arraySubject = array("" => translate('select'));
                                $assigns = $this->db->get_where('subject_assign', array('class_id' => set_value('class_id'), 'section_id' => set_value('section_id')))->result();
                                foreach ($assigns as $row) {
                                    $arraySubject[$row->subject_id] = get_type_name_by_id('subject', $row->subject_id);
                                }
                            } else {
                                $arraySubject = array("" => translate('select_class_first'));
                            }
                            echo form_dropdown("subject_id", $arraySubject, set_value('subject_id'), "class='form-control' id='subject_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('student') ?> <span
                                    class="required">*</span></label>
                            <select data-plugin-selectTwo class="form-control" name="student_id" id="student_id">

                            </select>
                            <span class="error"><?= form_error('student_id') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="submit" name="search" value="1" class="btn btn btn-default btn-block"> <i
                                class="fas fa-filter"></i> <?= translate('filter') ?></button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <?php if (!empty($skill_criteria)): ?>
            <section class="panel appear-animation" data-appear-animation="<?php echo $global_config['animations']; ?>"
                data-appear-animation-delay="100">
                <?php echo form_open(base_url('SkillReport/saveAssessment')); ?>
                <input type="hidden" name="branch_id" value="<?= $branch_id ?>">
                <input type="hidden" name="student_id" value="<?= $student_id ?>">
                <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
                <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
                <input type="hidden" name="class_id" value="<?= $class_id ?>">
                <input type="hidden" name="section_id" value="<?= $section_id ?>">

                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-file-signature"></i> <?= translate('assesment_entries') ?></h4>
                </header>

                <div class="panel-body">
                    <?php if (count($skill_criteria)) { ?>
                        <div class="table-responsive mt-md mb-lg">
                            <table class="table table-bordered table-condensed mb-none">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?= translate('category') ?></th>
                                        <th><?= translate('remarks/comments') ?></th>
                                        <th><?= translate('assessment_level') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $count = 1;
                                    foreach ($skill_criteria as $criteria): ?>
                                        <tr>
                                            <td><?= $count++ ?></td>
                                            <td><?= $criteria['category_name'] ?></td>
                                            <td><?= $criteria['criteria_text'] ?></td>
                                            <td>
                                                <select name="assessment[<?= $criteria['id'] ?>]" class="form-control">
                                                    <option value="">Select</option>
                                                    <option value="Mastered" <?= (isset($existing_assessments[$criteria['id']]) && $existing_assessments[$criteria['id']] == 'Mastered') ? 'selected' : '' ?>>
                                                        Mastered</option>
                                                    <option value="Progressing" <?= (isset($existing_assessments[$criteria['id']]) && $existing_assessments[$criteria['id']] == 'Progressing') ? 'selected' : '' ?>>
                                                        Progressing</option>
                                                    <option value="Beginning" <?= (isset($existing_assessments[$criteria['id']]) && $existing_assessments[$criteria['id']] == 'Beginning') ? 'selected' : '' ?>>
                                                        Beginning</option>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-info text-center"><?= translate('No skill criteria available.') ?></div>
                    <?php } ?>
                </div>

                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-offset-10 col-md-2">
                            <button type="submit" class="btn btn-default btn-block"
                                data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                                <i class="fas fa-plus-circle"></i> <?= translate('save') ?>
                            </button>
                        </div>
                    </div>
                </div>

                <?php echo form_close(); ?>
            </section>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <strong><?= translate('No records found for the selected criteria.') ?></strong>
            </div>
        <?php endif; ?>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#branch_id').on('change', function () {
            var branchID = $(this).val();
            getClassByBranch(branchID);
            getExamByBranch(branchID);
            $('#subject_id').html('').append('<option value=""><?= translate("select") ?></option>');
        });

        $('#section_id, #class_id').on('change', function () {
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();
            var branchID = $("#branch_id").length ? $('#branch_id').val() : "<?= get_loggedin_branch_id(); ?>";

            if (classID && sectionID) {
                getSubjectsByClassSection(classID, sectionID);
                getStudentsByClass(branchID, classID, sectionID);
            }
        });


        $('form[action*="saveAssessment"]').on('submit', function (e) {
            e.preventDefault();

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var formData = form.serialize();


            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    console.log("Response", response);
                    if (response.success) {
                        
                        swal({
                            text: response.message || 'Assessment saved successfully!',
                            position: 'top-right'
                        });
                        window.location.reload();
                    } else {
                        // Show error message
                        console.log("error response", response);
                        swal({
                            text: response.message || 'Failed to save assessment.',
                            position: 'top-right'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                },
                complete: function () {
                    submitBtn.prop('disabled', false).html('<i class="fas fa-plus-circle"></i> <?= translate("save") ?>');
                }
            });
        });

    });


    function getSubjectsByClassSection(classID, sectionID) {
        $.ajax({
            url: base_url + 'subject/getByClassSection',
            type: 'POST',
            data: { classID: classID, sectionID: sectionID },
            success: function (data) {
                $('#subject_id').html(data);
            }
        });
    }

    function getStudentsByClass(branchID, class_id, section_id) {
        var student_id = "<?= set_value('student_id') ?>";
        $.ajax({
            url: base_url + 'ajax/getStudentByClass',
            type: 'POST',
            data: { branch_id: branchID, class_id: class_id, section_id: section_id, student_id: student_id },
            success: function (data) {
                $('#student_id').html(data);
            }
        });
    }

</script>