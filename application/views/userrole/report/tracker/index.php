<section class="panel">
    <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
    <header class="panel-heading">
        <h4 class="panel-title"><?= translate('progress_tracker') ?></h4>
    </header>
    <div class="panel-body">
        <div class="row mb-sm">
            <div class="col-md-offset-3 col-md-6 mb-sm">
                <!-- Hidden input for student ID -->
                <input type="hidden" id="student_id" value="<?= $stu['student_id'] ?>">
                <div class="form-group">
                    <label class="control-label"><?= translate('subject') ?> <span class="required">*</span></label>
                    <select name="subject_id" class="form-control" id="subject_id" required data-plugin-selectTwo
                        data-width='100%' data-minimum-results-for-search='Infinity'>
                        <option value=""><?= translate('select_subject') ?></option>
                        <?php if (!empty($subjects)): ?>
                            <?php foreach ($subjects as $id => $name): ?>
                                <option value="<?= $id ?>" <?= set_select('subject_id', $id, (!empty($progressDetails) && $progressDetails['subject_id'] == $id)) ?>>
                                    <?= htmlspecialchars($name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                </div>
            </div>
        </div>
    </div>
    <footer class="panel-footer">
        <div class="row">
            <div class="col-md-offset-10 col-md-2">
                <button type="submit" name="submit" value="search" class="btn btn btn-default btn-block"> <i
                        class="fas fa-filter"></i> <?= translate('filter') ?></button>
            </div>
        </div>
    </footer>
    <?php echo form_close(); ?>
</section>


<?php if (!empty($progressDetails) && isset($progressDetails['subject_name'])): ?>
    <section class="panel appear-animation" data-appear-animation="<?= $global_config['animations'] ?>"
        data-appear-animation-delay="100">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-users"></i> <?= translate('my_progress') ?></h4>
        </header>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered table-hover table-condensed mb-none text-dark px-2">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= htmlspecialchars($progressDetails['subject_name']); ?></td>
                                
                                <td class="text-right">
                                    <a href="<?= base_url('report/student_progress/' . $progressDetails['student_id'] . '/' . $progressDetails['subject_id']) ?>">
                                        <i class="fas fa-chart-line text-primary"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <p>No progress data available. Please select a subject.</p>
<?php endif; ?>


<script type="text/javascript">

    var studentID = $('#student_id').val();
    console.log("jjfj", studentID);

    loadSubjects(studentID);

    function loadSubjects(studentID) {
        if (!studentID) {
            console.error('Student ID not found');
            $('#error_msg').html('<div class="alert alert-danger">Student ID not found</div>');
            return;
        }

        $.ajax({
            url: "<?= base_url('userrole/getSubjectByStudent') ?>",
            type: "POST",
            data: {
                student_id: studentID,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            },
            dataType: "json",
            success: function (response) {
                var $subjectDropdown = $('#subject_id');
                $subjectDropdown.empty().append('<option value=""><?= translate("select_subject") ?></option>');

                if (response && Object.keys(response).length > 0) {
                    $.each(response, function (key, value) {
                        $subjectDropdown.append('<option value="' + key + '">' + value + '</option>');
                    });
                    $subjectDropdown.trigger('change');
                } else {
                    $('#error_msg').html('<div class="alert alert-warning">No subjects found</div>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading subjects:', error);
                $('#error_msg').html('<div class="alert alert-danger">Error loading subjects. Please try again.</div>');
            }
        });
    }


</script>