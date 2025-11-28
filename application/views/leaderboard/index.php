<style>
    .card {
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .progress-bar {
        border-radius: 1rem;
    }

    .card img.rounded-circle {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }


    .info-star {
        color: #ffbf00;
        font-weight: bold;
        cursor: pointer;
        margin-left: 8px;
        font-size: 16px;
        vertical-align: middle;
    }

    .filter-info-text {
        font-size: 13px;
        margin-top: 8px;
    }
</style>

<?php
$selectedExamType = isset($exam_type) ? $exam_type : '';
?>

<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading" style="display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h4 class="panel-title" style="display:inline-block; margin:0;">
                        <?= translate('select_ground') ?>
                        <span id="infoStar" class="info-star" data-toggle="tooltip" data-placement="right" title="">
                            *
                        </span>
                    </h4>
                </div>
                <!-- optional small status text (updated dynamically by JS) -->
                <div id="filterStatus" class="filter-info-text text-muted"></div>
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
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%' data-placeholder='Search a branch'");
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <input type="hidden" id="branch_id" name="branch_id" value="<?= get_loggedin_branch_id(); ?>">
                    <?php endif; ?>


                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                        <?php
                        $arrayClass = $this->app_lib->getSelectClassByBranch($branch_id);
                        echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' required data-plugin-selectTwo data-width='100%'");
                        ?>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required data-plugin-selectTwo data-width='100%'");
                            ?>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('Exam_type') ?> <span
                                class="required">*</span></label>
                        <select class='form-control' name="exam_type" data-plugin-selectTwo data-width="100%"
                            id="exam_type">
                            <option value="">Select Exam Type</option>
                            <option value="offline" <?= ($selectedExamType == 'offline' ? 'selected' : '') ?>>OFFLINE
                            </option>
                            <option value="online" <?= ($selectedExamType == 'online' ? 'selected' : '') ?>>ONLINE</option>
                            <option value="live_exam" <?= ($selectedExamType == 'live_exam' ? 'selected' : '') ?>>
                                <?= translate('live_exam') ?>
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-sm exam-col">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span
                                    class="required exam_required">*</span></label>
                            <select name="exam_id" id="exam_id" class="form-control" data-plugin-selectTwo
                                data-width="100%"></select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm" id="session_code_container" style="display:none;">
                        <label class="control-label"><?= translate('session') ?></label>
                        <select class="form-control" name="session_code" id="session_code" data-plugin-selectTwo
                            data-width="100%">
                            <?php if (!empty($sessionCode)): ?>
                                <option value="<?= $sessionCode; ?>" selected><?= $sessionCode; ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-sm subject-col">
                        <div class="form-group">
                            <label class="control-label"><?= translate('subject') ?></label>
                            <select name="subject_id" id="subject_id" class="form-control" data-plugin-selectTwo
                                data-width="100%"></select>
                        </div>
                    </div>

                </div>
            </div>

            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="submit" name="search" value="1" class="btn btn-default btn-block">
                            <i class="fas fa-filter"></i> <?= translate('filter') ?>
                        </button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <?php if (!empty($leaderboard)): ?>

            <?php if ($exam_type == 'live_exam'): ?>

                <?php if (!empty($filter_subject_id)): ?>
                    <!-- SUBJECT-WISE LIVE EXAM RANK -->
                    <?php $this->load->view('leaderboard/partials/subject_wise_rank_list', $this->data); ?>
                <?php else: ?>
                    <!-- EXAM-WISE LIVE EXAM RANK -->
                    <?php $this->load->view('leaderboard/partials/table_live_exam', $this->data); ?>
                <?php endif; ?>

            <?php else: ?>
                <!-- ONLINE / OFFLINE CARD VIEW -->
                <?php $this->load->view('leaderboard/partials/card_exam', $this->data); ?>
            <?php endif; ?>

        <?php else: ?>

            <div class="alert alert-info text-center mt-4">
                <strong><?= translate('leaderboard_result') ?>:</strong> <?= translate('no_data_available') ?>
            </div>

        <?php endif; ?>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        /* ------------------------------------------------------
           PERSISTED VALUES FROM PHP
        ------------------------------------------------------ */
        var preExamType = "<?= isset($exam_type) ? $exam_type : '' ?>";
        var preExamId = "<?= isset($filter_exam_id) ? $filter_exam_id : '' ?>";
        var preSubjectId = "<?= isset($filter_subject_id) ? $filter_subject_id : '' ?>";
        var preSessionCode = "<?= isset($sessionCode) ? $sessionCode : '' ?>";
        var preBranchId = "<?= set_value('branch_id', $branch_id) ?>";
        var preClassId = "<?= set_value('class_id') ?>";
        var preSectionId = "<?= set_value('section_id') ?>";

        //console.log("branch", preBranchId);

        /* ------------------------------------------------------
           INIT TOOLTIP
        ------------------------------------------------------ */
        $('[data-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            html: true
        });

        /* ------------------------------------------------------
           TOOLTIP + STATUS TEXT UPDATE
        ------------------------------------------------------ */
        function updateInfoTooltip() {
            var examType = $('#exam_type').val();
            var subjectName = $('#subject_id option:selected').text() || '';
            var sessionText = $('#session_code option:selected').text() || '';

            var msg = "Select an exam or subject to view ranking";

            if (examType === 'live_exam') {

                if ($('#subject_id').val()) {
                    msg = "<b>Subject-wise Ranking:</b> Global ranking across ALL live exam sessions."
                        + "<br><b>Subject:</b> " + subjectName;

                    $('#filterStatus').text(
                        'Showing Global Subject-wise Ranking → "' + subjectName + '"'
                    );

                } else if ($('#exam_id').val()) {
                    msg = "<b>Exam-wise Ranking:</b> Live exam ranking."
                        + (sessionText ? "<br><b>Session:</b> " + sessionText : "");

                    $('#filterStatus').text(
                        "Showing Exam-wise Live Ranking"
                        + (sessionText ? " (Session: " + sessionText + ")" : "")
                    );

                } else {
                    $('#filterStatus').text("");
                }

            } else if (examType === 'online') {
                msg = "<b>Online Exam:</b> Showing leaderboard for selected exam.";
                $('#filterStatus').text("Showing: Online Exam Leaderboard");

            } else if (examType === 'offline') {
                msg = "<b>Offline Exam:</b> Showing leaderboard for selected exam.";
                $('#filterStatus').text("Showing: Offline Exam Leaderboard");

            } else {
                $('#filterStatus').text("");
            }

            $('#infoStar').attr('title', msg).tooltip('fixTitle');
        }

        /* ------------------------------------------------------
           AJAX LOADERS
        ------------------------------------------------------ */
        function loadSubjects(branchID) {
            $.ajax({
                url: base_url + 'ajax/getSubjectByBranch',
                type: 'POST',
                data: { branch_id: branchID },
                success: function (data) {
                    $('#subject_id').html(data);

                    if (preSubjectId) {
                        $('#subject_id').val(preSubjectId).trigger('change');
                    }

                    updateInfoTooltip();
                }
            });
        }

        function loadExams(examType) {
            var branchID = $('#branch_id').val();
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();

            if (!examType) {
                $('#exam_id').html('<option value="">Select Exam Type First</option>');
                return;
            }

            $.ajax({
                url: base_url + 'ajax/getExamType',
                type: 'POST',
                data: {
                    exam_type: examType,
                    branch_id: branchID,
                    class_id: classID,
                    section_id: sectionID
                },
                success: function (data) {
                    $('#exam_id').html(data);

                    if (preExamId) {
                        $('#exam_id').val(preExamId).trigger('change');
                    }

                    updateInfoTooltip();
                }
            });
        }

        function loadSessions(exam_id) {
            if (!exam_id) return;

            $.ajax({
                url: base_url + 'LiveExam/getSessionsByExamWithAll',
                type: 'POST',
                data: { exam_id: exam_id },
                success: function (data) {
                    $('#session_code').html(data);

                    if (preSessionCode) {
                        $('#session_code').val(preSessionCode).trigger('change');
                    }

                    $('#session_code_container').show();
                    updateInfoTooltip();
                }
            });
        }

        /* ------------------------------------------------------
           EVENT HANDLERS
        ------------------------------------------------------ */

        // Branch change
        $('#branch_id').on('change', function () {
            var branchID = $(this).val();

            if (typeof getClassByBranch === 'function') getClassByBranch(branchID);
            loadSubjects(branchID);
        });

        // Subject change — KEY BEHAVIOR
        $('#subject_id').on('change', function () {
            var examType = $('#exam_type').val();
            var subjectVal = $(this).val();

            if (examType === 'live_exam') {

                // SUBJECT SELECTED => SUBJECT-WISE
                if (subjectVal) {
                    $('#exam_id').closest('.exam-col').hide();
                    $('.exam_required').hide();
                    $('#exam_id').prop('required', false);
                    $('#session_code_container').hide();

                } else {
                    // NO SUBJECT => EXAM-WISE
                    $('#exam_id').closest('.exam-col').show();
                    $('.exam_required').show();
                    $('#exam_id').prop('required', true);

                    // ALWAYS VISIBLE
                    $('#session_code_container').show();
                }
            }

            updateInfoTooltip();
        });

        // Exam type change — MAIN FIX APPLIED HERE
        $('#exam_type').on('change', function () {
            var examType = $(this).val();
            var subjectVal = $('#subject_id').val();

            $('#exam_id').closest('.exam-col').show();
            $('.exam_required').show();
            $('#exam_id').prop('required', true);

            if (examType === 'live_exam') {

                if (subjectVal) {
                    // SUBJECT-WISE
                    $('#exam_id').closest('.exam-col').hide();
                    $('.exam_required').hide();
                    $('#session_code_container').hide();

                } else {
                    // EXAM-WISE
                    $('#session_code_container').show();
                }

            } else {
                $('#session_code_container').hide();
            }

            loadExams(examType);
            updateInfoTooltip();
        });

        // Exam change
        $('#exam_id').on('change', function () {
            var exam_id = $(this).val();
            var examType = $('#exam_type').val();
            var subjectVal = $('#subject_id').val();

            if (examType === 'live_exam' && !subjectVal) {

                // EXAM-WISE MODE → ALWAYS visible
                $('#session_code_container').show();

                if (exam_id) loadSessions(exam_id);

            } else {
                $('#session_code_container').hide();
            }

            updateInfoTooltip();
        });

        // Session change → update tooltip
        $('#session_code').on('change', function () {
            updateInfoTooltip();
        });

        // Class change
        $('#class_id').on('change', function () {
            var classID = $(this).val();

            if (typeof getSectionByClass === 'function') {
                getSectionByClass(classID, 0);
            }

            if ($('#branch_id').val()) loadSubjects($('#branch_id').val());
            if ($('#exam_type').val()) loadExams($('#exam_type').val());
        });

        /* ------------------------------------------------------
           INITIAL RESTORE
        ------------------------------------------------------ */
        (function initRestore() {

            if (preBranchId) $('#branch_id').val(preBranchId).trigger('change');
            if (preClassId) $('#class_id').val(preClassId).trigger('change');

            setTimeout(function () {
                if (typeof getSectionByClass === 'function') {
                    getSectionByClass(preClassId, preSectionId || 0);
                }
            }, 200);

            if (preExamType) {
                $('#exam_type').val(preExamType).trigger('change');
            }

            setTimeout(function () {

                if ($('#exam_type').val() === 'live_exam') {

                    if ($('#subject_id').val()) {
                        // SUBJECT MODE
                        $('#exam_id').closest('.exam-col').hide();
                        $('.exam_required').hide();
                        $('#session_code_container').hide();

                    } else {
                        // EXAM MODE
                        $('#session_code_container').show();
                        if ($('#exam_id').val()) loadSessions($('#exam_id').val());
                    }
                }

                updateInfoTooltip();
            }, 600);

        })();

    });
</script>