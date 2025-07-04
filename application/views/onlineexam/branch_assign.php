<?php $widget = 4; ?>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <?php echo form_open('', array('class' => 'validate')); ?>
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('select_ground') ?></h4>
            </header>
            <div class="panel-body">
                <div class="row mb-sm">
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arrayClass = $this->app_lib->getSelectClassList();
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?php echo $widget; ?>">
                        <div class="form-group">
                            <label class="control-label"><?= translate('subject') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySubject = array("" => translate('select_class_first'));
                            echo form_dropdown("subject_id", $arraySubject, set_value('subject_id'), "class='form-control' id='subject_id' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="button" id="search-btn" class="btn btn-default btn-block"><i
                                class="fas fa-filter"></i> <?= translate('filter') ?></button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <div class="row" id="question_table_section" style="display:none;">
            <div class="col-md-12">
                <section class="panel">
                    <header class="panel-heading">
                        <h4 class="panel-title"><i class="fas fa-file-circle-question"></i>
                            <?= translate('question') . " " . translate('list') ?></h4>
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12 text-right" style="margin-bottom: 10px;">
                                <button type="button" class="btn btn-primary mb-4" id="assignBranchBtn">Assign
                                    Branch</button>
                            </div>
                        </div>
                        <table class="table table-bordered table-hover table-condensed table-question" width="100%">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select_all"></th>
                                    <th><?= translate('sl') ?></th>
                                    <th><?= translate('question') ?></th>
                                    <th><?= translate('group') ?></th>
                                    <th><?= translate('class') ?></th>
                                    <th><?= translate('subject') ?></th>
                                    <th><?= translate('type') ?></th>
                                    <th><?= translate('level') ?></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div id="no_data_found" style="display:none; text-align:center; padding:20px;">
                            <strong>No questions found for the selected filter.</strong>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<!-- MODAL FOR BRANCH ASSIGN START HERE-->
<div class="zoom-anim-dialog modal-block modal-block-xl mfp-hide" id="modal">
    <section class="panel">
        <header class="panel-heading d-flex justify-content-between align-items-center">
            <h4 class="panel-title mb-0">
                <i class="fas fa-check-circle"></i> <?php echo translate('Assign Branch'); ?>
                <small class="text-muted d-block">Select branches to assign selected questions</small>
            </h4>
            <div>
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="selectAllBranches">
                    <label class="form-check-label" for="selectAllBranches">
                        <?php echo translate('Select All Branches'); ?>
                    </label>
                </div>
            </div>
        </header>

        <div class="panel-body">
            <div class="table-responsive">
                <table id="branchTable" class="table table-bordered table-striped mb-none" width="100%">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th><?php echo translate('Branch Name'); ?></th>
                            <th class="text-center"><?php echo translate('Assign'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic rows will be appended here -->
                    </tbody>
                </table>
            </div>
        </div>

        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-default modal-dismiss">
                        <i class="fas fa-times"></i> <?php echo translate('close'); ?>
                    </button>
                    <button type="button" class="btn btn-primary" id="assignBranchModalBtn">
                        <i class="fas fa-check"></i> <?php echo translate('Assign Branch'); ?>
                    </button>
                </div>
            </div>
        </footer>
    </section>
</div>

<!-- MODAL FOR BRANCH ASSIGN END HERE-->

<script type="text/javascript">

    $(document).ready(function () {
        // Initially disable the Assign button
        $('#assignBranchBtn').prop('disabled', true);

        // Select All Questions logic
        $(document).on('change', '#select_all', function () {
            var checked = $(this).prop('checked');
            $('.question-assign').prop('checked', checked).trigger('change');
        });

        // Individual question checkbox change logic
        $(document).on('change', '.question-assign', function () {
            var total = $('.question-assign').length;
            var checked = $('.question-assign:checked').length;

            // Update select_all state
            $('#select_all').prop('checked', total === checked);

            // Enable/disable Assign button
            $('#assignBranchBtn').prop('disabled', checked === 0);
        });

        // Function to get selected question IDs
        function getSelectedQuestions() {
            return $('.question-assign:checked').map(function () {
                return $(this).data('id');
            }).get();
        }

        // Initialize DataTable for Branch modal
        const branchTable = $('#branchTable').DataTable({
            pageLength: 10,
            'drawCallback': function () {
                $('input[name="branch_ids[]"]').each(function () {
                    $(this).prop('checked', selectedBranches.includes($(this).val()));
                });
                updateSelectAllBranchesCheckboxState();
            }
        });

        let selectedBranches = [];

        // Assign Branch button click → open modal
        $('#assignBranchBtn').on('click', function () {
            const selectedQuestions = getSelectedQuestions();

            if (selectedQuestions.length === 0) {
                alert('Please select at least one question.');
                return;
            }

            // Fetch branches for the selected class
            const classID = $('#class_id').val();
            $.ajax({
                url: base_url + 'onlineexam/getBranchesByClass',
                type: 'POST',
                data: { class_id: classID },
                success: function (response) {
                    try {
                        const branches = JSON.parse(response);
                        branchTable.clear();

                        if (branches.length > 0) {
                            branches.forEach((branch, index) => {
                                branchTable.row.add([
                                    index + 1,
                                    branch.name,
                                    `<div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="branch_ids[]" value="${branch.id}" id="branch_${branch.id}">
                                    <label class="form-check-label" for="branch_${branch.id}">${branch.name}</label>
                                </div>`
                                ]);
                            });
                        } else {
                            branchTable.row.add(["", "No branches available.", ""]);
                        }

                        branchTable.draw();
                        updateSelectAllBranchesCheckboxState();
                        mfp_modal('#modal');
                    } catch (e) {
                        alert('Error parsing branch response.');
                    }
                },
                error: function () {
                    alert('An error occurred while fetching branches.');
                }
            });
        });

        // Modal select all branches logic
        $('#selectAllBranches').on('change', function () {
            const isChecked = $(this).prop('checked');
            selectedBranches = [];

            $('input[name="branch_ids[]"]').prop('checked', isChecked);

            if (isChecked) {
                $('input[name="branch_ids[]"]').each(function () {
                    selectedBranches.push($(this).val());
                });
            }

            branchTable.draw();
        });

        // Update selectedBranches array on branch checkbox change
        $(document).on('change', 'input[name="branch_ids[]"]', function () {
            const branchID = $(this).val();
            if ($(this).prop('checked')) {
                if (!selectedBranches.includes(branchID)) {
                    selectedBranches.push(branchID);
                }
            } else {
                selectedBranches = selectedBranches.filter(id => id !== branchID);
            }
            updateSelectAllBranchesCheckboxState();
        });

        // Update Select All checkbox based on individual selections
        function updateSelectAllBranchesCheckboxState() {
            const visibleCheckboxes = $('input[name="branch_ids[]"]');
            const allSelected = visibleCheckboxes.length > 0 && visibleCheckboxes.length === selectedBranches.length;
            $('#selectAllBranches').prop('checked', allSelected);
        }

        // Final assign branch to selected questions
        $('#assignBranchModalBtn').on('click', function () {
            const selectedQuestions = getSelectedQuestions();

            if (selectedBranches.length === 0) {
                alert('Please select at least one branch.');
                return;
            }

            $.ajax({
                url: base_url + 'onlineexam/assignQuestionsToBranches',
                type: 'POST',
                data: {
                    question_ids: selectedQuestions,
                    branch_ids: selectedBranches
                },
                success: function (response) {
                    // console.log("branch_assign", response);
                    if (response.status === 'success') {
                        swal({
                            toast: true,
                            position: 'top-end',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 8000
                        });
                        $.magnificPopup.close();
                        $('#search-btn').trigger('click');
                    } else {
                        alert('Failed to assign branches.');
                    }
                },
                error: function () {
                    alert('An error occurred while assigning branches.');
                }
            });
        });

        // Reload subject dropdown on section change
        $('#section_id').on('change', function () {
            loadSubjects();
        });

        function loadSubjects() {
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();
            $.ajax({
                url: base_url + 'subject/getByClassSection',
                type: 'POST',
                data: { classID: classID, sectionID: sectionID },
                success: function (data) {
                    $('#subject_id').html(data);
                }
            });
        }

        // Search button click → load questions table
        $('#search-btn').on('click', function () {
            $('#question_table_section').show();

            $('.table-question').DataTable().destroy();

            $('.table-question').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: base_url + 'onlineexam/getQuestionsForAssignmentDT',
                    type: 'POST',
                    data: function (d) {
                        d.class_id = $('#class_id').val();
                        d.section_id = $('#section_id').val();
                        d.subject_id = $('#subject_id').val();
                    }
                },
                columns: [
                    { data: 0, orderable: false }, // checkbox
                    { data: 1 },
                    { data: 2 },
                    { data: 3 },
                    { data: 4 },
                    { data: 5 },
                    { data: 6 },
                    { data: 7 }
                ],
                order: [],
                columnDefs: [
                    { orderable: false, targets: [0] },
                ],
                fnDrawCallback: function () {
                    $('[data-toggle="tooltip"]').tooltip();
                    $('#select_all').prop('checked', false);
                    $('#assignBranchBtn').prop('disabled', true);
                }
            });
        });
    });

</script>