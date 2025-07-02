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
                            <label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
                            <?php
                            $arrayClass = $this->app_lib->getSelectClassList();
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), false);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?php echo $widget; ?>">
                        <div class="form-group">
                            <label class="control-label"><?= translate('subject') ?> <span class="required">*</span></label>
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
                        <button type="button" id="search-btn" class="btn btn-default btn-block"><i class="fas fa-filter"></i> <?= translate('filter') ?></button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <div class="row" id="question_table_section" style="display:none;">
            <div class="col-md-12">
                <section class="panel">
                    <header class="panel-heading">
                        <h4 class="panel-title"><i class="fas fa-file-circle-question"></i> <?= translate('question') . " " . translate('list') ?></h4>
                    </header>
                    <div class="panel-body">
                        <table class="table table-bordered table-hover table-condensed table-question" width="100%">
                            <thead>
                                <tr>
                                    <th><?= translate('sl') ?></th>
                                    <th><?= translate('question') ?></th>
                                    <th><?= translate('group') ?></th>
                                    <th><?= translate('class') ?></th>
                                    <th><?= translate('subject') ?></th>
                                    <th><?= translate('type') ?></th>
                                    <th><?= translate('level') ?></th>
                                    <th><?= translate('action') ?></th>
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

<script type="text/javascript">
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

    $('#section_id').on('change', function () {
        loadSubjects();
    });

    $('#search-btn').on('click', function () {
        $('#question_table_section').show();

        $('.table-question').DataTable().destroy(); // Destroy previous instance

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
                { data: 0 },
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
                { orderable: false, targets: "no-sort" },
                { orderable: false, targets: [-1], class: "action" },
            ],
            fnDrawCallback: function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>
