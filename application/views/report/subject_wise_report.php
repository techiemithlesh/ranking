<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                <div>
                    <h4 class="panel-title" style="display:inline-block; margin:0;">
                        <?= translate('select_ground') ?>
                    </h4>
                </div>
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

                    <div class="col-md-3 mb-sm" id="session_code_container">
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

        <!-- TABULAR FORMAT CODE GOES HERE -->
       

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){

       

    });
</script>