<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading bg-yellow" style="font-weight:600;">
                <i class="fas fa-chart-bar"></i> <?= translate('online_exam_progress') ?>
            </header>

            <?php echo form_open($this->uri->uri_string(), ['class' => 'validate', 'id' => 'myFormId', 'method' => 'post']); ?>
            <div class="panel-body">
                <div class="row d-flex align-items-center justify-content-center">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label"><?= translate('subject') ?> <span
                                    class="required">*</span></label>
                            <select class='form-control' required name="subject_id" data-plugin-selectTwo
                                data-width="100%" id="subject_id"></select>

                        </div>
                    </div>

                    <div class="col-md-3 mt-4 pt-2" style="margin-top: 20px;">
                        <button type="submit" name="search" value="1" class="btn btn-warning btn-block"
                            style="margin-top:8px;">
                            <i class="fas fa-chart-line"></i> <?= translate('generate_report') ?>
                        </button>
                    </div>

                </div>
            </div>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<?php if (!empty($leaderboard)): ?>

    <?php $this->load->view('leaderboard/partials/subject_wise_rank_list', $this->data); ?>

<?php else: ?>

    <div class="alert alert-info text-center mt-4">
        <strong><?= translate('leaderboard_result') ?>:</strong> <?= translate('no_data_available') ?>
    </div>

<?php endif; ?>


<script type="text/javascript">
    $(document).ready(function () {

        var branch_id = "<?= $studentDetails['branch_id']; ?>";
        var selectedSubjectId = "<?= isset($subjectId) ? $subjectId : '' ?>";

        $.ajax({
            url: base_url + 'ajax/getSubjectByBranch',
            type: 'POST',
            data: { branch_id: branch_id },
            success: function (data) {
                $('#subject_id').html(data);

                if (selectedSubjectId) {
                    $('#subject_id').val(selectedSubjectId).trigger('change');
                }
            }
        });

    });
</script>