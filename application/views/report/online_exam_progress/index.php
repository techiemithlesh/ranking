<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('online_exam_report') ?></h4>
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
                        if (!is_superadmin_loggedin()) {
                            $branch_id = get_loggedin_branch_id();
                        }
                       $arrayClass = $this->app_lib->getSelectClassByBranch($branch_id);
                        echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
												required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                        ?>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
													data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' "); ?>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('exam') ?> <span
                                    class="required">*</span></label>
                            <select name="exam_id" id="examID" class="form-control" data-plugin-selectTwo
                                data-width="100%"></select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-sm">
                        <label class="control-label"><?= translate('student') ?> <span class="required">*</span></label>

                        <select class='form-control' name="student_id" data-plugin-selectTwo data-width="100%"
                            id="student_id">

                        </select>
                    </div>

                </div>
                <div class="row mt-20">
                    <div class="col-md-12 text-center">
                        <button type="submit" name="search" value="1"
                            class="btn btn-primary"><?= translate('generate_report') ?></button>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<!-- JS Scripts -->
<script type="text/javascript">
    $(document).ready(function () {
        $('#class_id').on('change', function () {
            var class_id = $(this).val();
            var branch_id = $("#branch_id").length ? $('#branch_id').val() : "";

            // Get students
            $.post(base_url + 'ajax/getStudentByClass', {
                branch_id: branch_id,
                class_id: class_id
            }, function (data) {
                $('#student_id').html(data);
            });

            $.ajax({
                url: base_url + 'onlineexam/getExamByClass',
                type: 'POST',
                data: { class_id: class_id },
                success: function (data) {
                    $('#examID').html(data);
                }
            });

        });

        $('#branch_id').on('change', function () {
            let branchID = $(this).val();
            getClassByBranch(branchID);
        });
    });
</script>