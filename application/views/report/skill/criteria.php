<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <div class="tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#criteriaList" data-toggle="tab"><i class="fas fa-list-ul"></i>
                            <?= translate('Skill_criteria') ?></a>
                    </li>
                    <?php if (is_superadmin_loggedin() || is_admin_loggedin() || is_teacher_loggedin()) { ?>
                        <li>
                            <a href="#create" data-toggle="tab"><i class="far fa-edit"></i>
                                <?= translate('create_skill_criteria') ?></a>
                        </li>
                    <?php } ?>
                </ul>
                <div class="tab-content">
                    <div id="criteriaList" class="tab-pane active mb-md">
                        <table class="table table-bordered table-hover table-condensed mb-none table_default">
                            <thead>
                                <tr>
                                    <th><?= translate('sl') ?></th>
                                    <th><?= translate('branch') ?></th>
                                    <th><?= translate('class') ?></th>
                                    <th><?= translate('section') ?></th>
                                    <th><?= translate('subject') ?></th>
                                    <th><?= translate('exam') ?></th>
                                    <th><?= translate('skill_category') ?></th>
                                    <th><?= translate('category_remarks') ?></th>
                                    <th><?= translate('Action') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 1;
                                foreach ($skill_criteria as $row):
                                    ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo html_escape($row['branch_name']); ?></td>
                                        <td><?php echo html_escape($row['class_name']); ?></td>
                                        <td><?php echo html_escape($row['section_name']); ?></td>
                                        <td><?php echo html_escape($row['subject_name']); ?></td>
                                        <td><?php echo html_escape($row['exam_name']); ?></td>
                                        <td><?php echo html_escape($row['skill_category_name']); ?></td>
                                        <td><?php echo html_escape($row['criteria_text']); ?></td>
                                        <td>

                                            <a href="#" class="btn btn-circle icon btn-default" data-toggle="tooltip"
                                                data-id="<?= $row['id'] ?>" data-branch_name="<?= $row['branch_name'] ?>"
                                                data-branch-id="<?= $row['branch_id'] ?>"
                                                data-class-id="<?= $row['class_id'] ?>"
                                                data-section-id="<?= $row['section_id'] ?>"
                                                data-exam-id="<?= $row['exam_id'] ?>"
                                                data-subject-id="<?= $row['subject_id'] ?>"
                                                data-category-id="<?= $row['skill_category_id'] ?>"
                                                data-category-name="<?= $row['skill_category_name'] ?>"
                                                data-original-title="<?php echo translate('edit'); ?>"
                                                data-criteria-text="<?= $row['criteria_text'] ?>"
                                                onclick="getEditModal(this)">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Delete Button -->
                                            <?php echo btn_delete('skillReport/deleteCriteria/' . $row['id']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>
                    <div class="tab-pane" id="create">
                        <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
                        <?php if (is_superadmin_loggedin()): ?>
                            <div class="form-group">
                                <label class="col-md-3 control-label"><?php echo translate('branch'); ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6">
                                    <?php
                                    $arrayBranch = $this->app_lib->getSelectList('branch');
                                    echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
								        data-plugin-selectTwo data-width='100%' data-placeHolder='Select Branch'");
                                    ?>
                                    <span class="error"></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="col-md-3 control-label"><?= translate('exam') ?> <span
                                    class="required">*</span></label>
                            <div class="col-md-6">
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
                                <span class="error"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label"><?= translate('class') ?> <span
                                    class="required">*</span></label>
                            <div class="col-md-6 mb-md">
                                <?php
                                $arrayClass = $this->app_lib->getClass($branch_id);
                                echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
								        required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                                ?>
                                <span class="error"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <div class="col-md-6 mb-md">
                                <?php
                                $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                                echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
								    data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                                ?>
                                <span class="error"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label"><?= translate('subject') ?> <span
                                    class="required">*</span></label>
                            <div class="col-md-6 mb-md">
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
                                <span class="error"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label"><?= translate('skill_category') ?> <span
                                    class="required">*</span></label>
                            <div class="col-md-6 mb-md">
                                <select data-plugin-selectTwo class="form-control" name="skill_category_id"
                                    id="skill_category_id">

                                </select>
                                <span class="error"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label"><?= translate('criteria / remarks') ?> <span
                                    class="required">*</span></label>
                            <div class="col-md-6">
                                <!-- <textarea class="form-control" name="criteria_text"
                                    rows="3"><?= set_value('criteria_text') ?></textarea> -->

                                <div id="remarks-container">
                                    <div class="remarks-group">
                                        <textarea class="form-control" name="criteria_text[]" rows="3"
                                            required></textarea>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary mt-sm" onclick="addMoreRemarks()">
                                    <i class="fas fa-plus"></i> Add More
                                </button>

                                <span class="error"></span>
                            </div>
                        </div>

                        <footer class="panel-footer mt-lg">
                            <div class="row">
                                <div class="col-md-2 col-md-offset-3">
                                    <button type="submit" class="btn btn-default btn-block"
                                        data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                                        <i class="fas fa-plus-circle"></i> <?= translate('save') ?>
                                    </button>
                                </div>
                            </div>
                        </footer>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- EDIT MODAL START HERE -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="editModal">
    <section class="panel">
        <?php echo form_open('skillReport/updateCriteria', array('class' => 'edit-frm-submit')); ?>

        <input type="hidden" name="criteria_id" id="criteria_id" value="" />
        <header class="panel-heading">
            <h4 class="panel-title"><i class="far fa-edit"></i>
                <?= translate('edit') . " " . translate('criteria') ?></h4>
        </header>
        <div class="panel-body">
            <?php if (is_superadmin_loggedin()): ?>
                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
                    <?php
                    $arrayBranch = $this->app_lib->getSelectList('branch');
                    echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='edit_branch_id'
                        data-plugin-selectTwo data-width='100%' data-placeholder='Select a branch'");
                    ?>
                    <span class="error"></span>
                </div>
            <?php else: ?>
                <input type="hidden" name="branch_id" id="branch_id" value="<?= get_loggedin_branch_id(); ?>" />
            <?php endif; ?>

            <div class="form-group mb-md">
                <label class="control-label"><?= translate('exam') ?> <span class="required">*</span></label>
                <select class="form-control" name="exam_id" id="edit_exam_id" data-plugin-selectTwo data-width="100%"
                    data-minimum-results-for-search="Infinity">
                    <option value=""><?= translate('select') ?></option>
                </select>
                <span class="error"></span>
            </div>

            <div class="form-group mb-md">
                <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                <select class="form-control" name="class_id" id="edit_class_id" data-plugin-selectTwo data-width="100%"
                    data-minimum-results-for-search="Infinity">
                    <option value=""><?= translate('select') ?></option>
                </select>
                <span class="error"></span>
            </div>

            <div class="form-group mb-md">
                <label class="control-label"><?= translate('section') ?> <span class="required">*</span></label>
                <select class="form-control" name="section_id" id="edit_section_id" data-plugin-selectTwo
                    data-width="100%" data-minimum-results-for-search="Infinity">
                    <option value=""><?= translate('select') ?></option>
                </select>
                <span class="error"></span>
            </div>

            <div class="form-group">
                <label class="control-label"><?= translate('subject') ?> <span class="required">*</span></label>
                <select class="form-control" name="subject_id" id="edit_subject_id" data-plugin-selectTwo
                    data-width="100%" data-minimum-results-for-search="Infinity">
                    <option value=""><?= translate('select') ?></option>
                </select>
                <span class="error"></span>
            </div>

            <div class="form-group mb-md">
                <label class="control-label"><?= translate('skill_category') ?> <span class="required">*</span></label>
                <select class="form-control" name="skill_category_id" id="edit_skill_category_id" data-plugin-selectTwo
                    data-width="100%" data-minimum-results-for-search="Infinity">
                    <option value=""><?= translate('select') ?></option>
                </select>
                <span class="error"></span>
            </div>

            <div class="form-group mb-md">
                <label class="control-label"><?= translate('criteria / remarks') ?> <span
                        class="required">*</span></label>
                <textarea class="form-control" name="criteria_text" id="edit_criteria_text" rows="3"></textarea>
                <span class="error"></span>
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-default mr-xs"
                        data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                        <i class="fas fa-plus-circle"></i> <?= translate('update') ?>
                    </button>
                    <button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
                </div>
            </div>
        </footer>
        <?php echo form_close(); ?>
    </section>
</div>
<!-- EDIT MODAL END HERE -->


<script type="text/javascript">

    function addMoreRemarks() {
        const container = document.getElementById('remarks-container');
        const newGroup = document.createElement('div');
        newGroup.classList.add('remarks-group', 'mt-sm');
        newGroup.innerHTML = `
        <textarea class="form-control" name="criteria_text[]" rows="3" required></textarea>
        <button type="button" class="btn btn-sm btn-danger mt-sm" onclick="this.parentNode.remove()">
            <i class="fas fa-minus"></i> Remove
        </button>
    `;
        container.appendChild(newGroup);
    }

    $(document).ready(function () {

        function fetchExam(branchID, examDropdownID, selectedExamID = null) {
            if (branchID) {
                $.ajax({
                    url: "<?= base_url('ajax/getExamByBranch') ?>",
                    method: 'POST',
                    data: { branch_id: branchID },
                    success: function (data) {
                        $(examDropdownID).html(data);
                        if (examDropdownID) {
                            $(examDropdownID).val(selectedExamID).trigger("change");
                        }
                    }
                });
            } else {
                $(examDropdownID).html('<option value=""><?= translate("select") ?></option>');
            }

        }

        function fetchClass(branchID, classDropdownID, selectedClassID = null) {
            if (branchID) {
                $.ajax({
                    url: "<?= base_url('ajax/getClassByBranch') ?>",
                    method: 'POST',
                    data: { branch_id: branchID },
                    success: function (data) {
                        $(classDropdownID).html(data);
                        if (classDropdownID) {
                            $(classDropdownID).val(selectedClassID).trigger("change");
                        }
                    }
                });
            } else {
                $(classDropdownID).html('<option value=""><?= translate("select") ?></option>');
            }
        }


        function fetchSection(classID, sectionDropdownID, selectedSectionID = null) {
            if (classID) {
                $.ajax({
                    url: "<?= base_url('ajax/getSectionByClass') ?>",
                    method: 'POST',
                    data: { class_id: classID },
                    success: function (data) {
                        $(sectionDropdownID).html(data);
                        if (sectionDropdownID) {
                            $(sectionDropdownID).val(selectedSectionID).trigger("change");
                        }
                    }
                });
            } else {
                $(sectionDropdownID).html('<option value=""><?= translate("select") ?></option>');
            }
        }

        function fetchSubject(classID, sectionID, subjectDropdownID, selectedSubjectID = null) {
            if (classID && sectionID) {
                $.ajax({
                    url: base_url + 'subject/getByClassSection',
                    type: 'POST',
                    data: {
                        classID: classID,
                        sectionID: sectionID
                    },
                    success: function (data) {
                        console.log("fetchsubject", data);
                        $(subjectDropdownID).html(data);
                        if (selectedSubjectID) {
                            $(subjectDropdownID).val(selectedSubjectID).trigger("change");
                        }
                    },
                    error: function () {
                        $(subjectDropdownID).html('<option value=""><?= translate("error_loading") ?></option>');
                    }
                });
            } else {
                $(subjectDropdownID).html('<option value=""><?= translate("select") ?></option>');
            }
        }

        function fetchSkillCategory(branchID, subjectID, skillCategoryDropdownID, selectedCategoryID = null) {
            if (branchID && subjectID) {
                $.ajax({
                    url: "<?= base_url('ajax/getSkillCategory') ?>",
                    type: 'POST',
                    data: {
                        branch_id: branchID,
                        subject_id: subjectID
                    },
                    success: function (data) {
                        $(skillCategoryDropdownID).html(data);
                        if (selectedCategoryID) {
                            $(skillCategoryDropdownID).val(selectedCategoryID).trigger("change");
                        }
                    },
                    error: function () {
                        $(skillCategoryDropdownID).html('<option value=""><?= translate("error_loading") ?></option>');
                    }
                });
            } else {
                $(skillCategoryDropdownID).html('<option value=""><?= translate("select_subject_first") ?></option>');
            }
        }


        window.getEditModal = function (el) {

            var criteria_id = $(el).data('id');
            var branch_id = $(el).data('branch-id');
            var class_id = $(el).data('class-id');
            var section_id = $(el).data('section-id');
            var subject_id = $(el).data('subject-id');
            var exam_id = $(el).data('exam-id');
            var category_id = $(el).data('category-id');
            var criteria_text = $(el).data('criteria-text');

            $('#criteria_id').val(criteria_id);
            $('#edit_branch_id').val(branch_id).trigger('change');
            $('#edit_class_id').val(class_id).trigger('change');
            $('#edit_section_id').val(section_id).trigger('change');
            $('#edit_subject_id').val(subject_id).trigger('change');
            $('#edit_exam_id').val(exam_id).trigger('change');
            $('#edit_skill_category_id').val(category_id).trigger('change');
            $('#edit_criteria_text').val(criteria_text);

            fetchExam(branch_id, '#edit_exam_id', exam_id);
            fetchClass(branch_id, '#edit_class_id', class_id);

            setTimeout(() => {

                fetchSection(class_id, '#edit_section_id', section_id);

                setTimeout(() => {

                    fetchSubject(class_id, section_id, '#edit_subject_id', subject_id);

                    setTimeout(() => {

                        fetchSkillCategory(branch_id, subject_id, '#edit_skill_category_id', category_id);
                    }, 500);

                }, 500);

            }, 500);


            mfp_modal("#editModal");
        }


        $('#edit_branch_id').on('change', function () {
            var branchID = $(this).val();
            fetchClass(branchID, '#edit_class_id');
            fetchExam(branchID, '#edit_exam_id');
            $('#edit_subject_id').html('<option value=""><?= translate("select") ?></option>');
        });

        $('#edit_class_id').on('change', function () {
            var classID = $(this).val();
            fetchSection(classID, '#edit_section_id');
        });

        $('#edit_section_id').on('change', function () {
            var classID = $('#edit_class_id').val();
            var sectionID = $(this).val();
            fetchSubject(classID, sectionID, '#edit_subject_id');
        });

        $('#edit_subject_id').on('change', function () {
            var subjectID = $(this).val();
            var branchID = $('#edit_branch_id').val();
            fetchSkillCategory(branchID, subjectID, '#edit_skill_category_id');
        });


        var branchID = $('#branch_id').val();
        if (branchID) {
            getClassByBranch(branchID);
            getExamByBranch(branchID);
        }


        $('#branch_id').on('change', function () {
            var branchID = $(this).val();
            getClassByBranch(branchID);
            getExamByBranch(branchID);
            $('#subject_id').html('').append('<option value=""><?= translate("select") ?></option>');
        });

        $('#section_id').on('change', function () {
            var classID = $('#class_id').val();
            var sectionID = $(this).val();
            $.ajax({
                url: base_url + 'subject/getByClassSection',
                type: 'POST',
                data: {
                    classID: classID,
                    sectionID: sectionID
                },
                success: function (data) {
                    $('#subject_id').html(data);
                }
            });
        });


        $('#subject_id').on('change', function () {
            var subjectID = $(this).val();
            var branchID = $('#branch_id').val();

            if (subjectID) {
                $.ajax({
                    url: base_url + 'ajax/getSkillCategory',
                    type: 'POST',
                    data: {
                        subject_id: subjectID,
                        branch_id: branchID
                    },

                    success: function (response) {
                        console.log("res", response);
                        $('#skill_category_id').html(response);
                    },
                    error: function () {
                        $('#skill_category_id').html('<option value=""><?= translate("error_loading") ?></option>');
                    }
                });
            } else {
                $('#skill_category_id').html('<option value=""><?= translate("select_subject_first") ?></option>');
            }
        });

    });


    $(".edit-frm-submit").submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr("action");

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            dataType: "json",
            success: function (response) {
                if (response.status == "success") {

                    location.reload();
                } else {
                    $(".error").html("");
                    $.each(response.errors, function (key, value) {
                        $("#" + key).next(".error").html(value);
                    });
                }
            }
        });
    });

</script>