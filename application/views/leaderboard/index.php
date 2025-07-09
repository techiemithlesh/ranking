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
</style>

<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('select_ground') ?></h4>
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
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
													data-plugin-selectTwo data-width='100%' data-placeholder='Search a brnach'");
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                        <?php
                       $arrayClass = $this->app_lib->getSelectClassByBranch($branch_id);
                        echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
												required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                        ?>
                    </div>

                    <div class="col-md-3 mb-sm">
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

                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('Exam_type') ?> <span
                                class="required">*</span></label>
                        <?php
                        $selectedExamType = isset($filter_exam_type) ? $filter_exam_type : '';
                        ?>
                        <select class='form-control' name="exam_type" data-plugin-selectTwo data-width="100%"
                            id="exam_type">
                            <option value="">Select Exam Type</option>
                            <option value="offline" <?= ($selectedExamType == 'offline' ? 'selected' : '') ?>>OFFLINE
                            </option>
                            <option value="online" <?= ($selectedExamType == 'online' ? 'selected' : '') ?>>ONLINE</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span
                                    class="required">*</span></label>
                            <select name="exam_id" class="form-control" data-plugin-selectTwo
                                data-width="100%"></select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm">
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
                        <button type="submit" name="search" value="1" class="btn btn-default btn-block"> <i
                                class="fas fa-filter"></i> <?= translate('filter') ?></button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>


        <!-- leaderboard view -->
        <?php if (!empty($leaderboard)): ?>
            <section class="panel">
                <header class="panel-heading">
                    <h4 class="panel-title"><?= translate('leaderboard_result') ?></h4>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <?php
                        $rankIcons = ['🥇', '🥈', '🥉']; // Top 3 emoji icons
                        $count = 1;
                        foreach ($leaderboard as $row):
                            $rank = $count;
                            $isTop3 = $rank <= 3;
                            $student_photo = get_image_url('student', $row['photo']);
                            $subject = $row['subject_name'];
                            $register = $row['register_no'];
                            $name = $row['full_name'];
                            $marks = isset($row['obtain_mark']) ? $row['obtain_mark'] : $row['marks'];
                            $badge = $count <= 3 ? $rankIcons[$count - 1] : "#$count";
                            $percentage = (float) $row['percentage'];

                            // Dynamic progress bar color
                            if ($percentage >= 90) {
                                $barColor = '#28a745';
                            } elseif ($percentage >= 70) {
                                 $barColor = '#17a2b8';
                            } elseif ($percentage >= 50) {
                                $barColor = '#ffc107';
                            } else {
                                $barColor = '#dc3545';
                            }
                            ?>
                            <div class="col-md-4">
                                <div
                                    class="card shadow rounded border-0 <?= $isTop3 ? 'border-top border-4 border-warning' : '' ?>">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0 fw-bold"><?= $badge ?></h3>
                                        <img src="<?= $student_photo ?>" class="rounded-circle my-2" width="60" height="60"
                                            style="object-fit: cover; border: 2px solid #dee2e6;" alt="<?= $name ?>" />
                                        <h5 class="card-title mt-2"><?= $name ?></h5>
                                        <p class="text-muted mb-1"><?= $register ?></p>
                                        <p class="text-uppercase small text-secondary"> <?= $subject ?></p>

                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar"  role="progressbar"
                                                style="width: <?= $percentage ?>%; padding: 0 5px; background-color: <?=$barColor; ?>"
                                                aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                                <span style="font-weight: bold;"><?= $percentage ?>%</span>
                                                
                                            </div>
                                        </div>
                                        <span class="badge bg-dark mt-3">Rank #<?= $rank ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php $count++; endforeach; ?>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <div class="alert alert-info text-center mt-4">
                <strong><?= translate('leaderboard_result') ?>:</strong> <?= translate('no_data_available') ?>
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

        // Fetch subjects when section is changed
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

        // EXAM FETCHED
        $('#exam_type').on('change', function () {
            var examType = $(this).val();
            var branchID = $('#branch_id').val();
            var classID = $('#class_id').val();
            var sectionID = $('#section_id').val();

            if (examType) {
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
                        $('select[name="exam_id"]').html(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            } else {
                $('select[name="exam_id"]').html('<option value="">' + translate("select_exam_type_first") + '</option>');
            }
        });

    });

</script>