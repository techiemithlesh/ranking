<?php $currency_symbol = $global_config['currency_symbol']; ?>
<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#list" data-toggle="tab">
                    <i class="fas fa-list-ul"></i> <?= translate('online_exam_for_live') . " " . translate('list') ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane box active mb-md" id="list">
                <table class="table table-bordered table-hover mb-none table-condensed live-exam-list" width="100%">
                    <thead>
                        <tr>
                            <th class="no-sort"><?= translate('sl') ?></th>
                            <?php if (is_superadmin_loggedin()): ?>
                                <th><?= translate('branch') ?></th>
                            <?php endif; ?>
                            <th><?= translate('title') ?></th>
                            <th><?= translate('class') ?> (<?= translate('section') ?>)</th>
                            <th><?= translate('questions_qty') ?></th>
                            <th><?= translate('start_time') ?></th>
                            <th><?= translate('end_time') ?></th>
                            <th><?= translate('duration') ?></th>
                            <th class="no-sort"><?= translate('exam') . " " . translate('fees') ?></th>
                            <th class="no-sort"><?= translate('exam_status') ?></th>
                            <th><?= translate('action') ?></th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
</section>


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
                    <input type="hidden" name="exam_id" id="assign_exam_id" value="">
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
                    <button type="submit" class="btn btn-primary" id="assignBranchModalBtn">
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
        initDatatable('.live-exam-list', 'LiveExam/getLiveExamListDT', {}, 25);

        $('#assignBranchModalBtn').on('click', function () {
            var examID = $('#assign_exam_id').val();
            var branches = [];
            $('#branchTable input[name="branches[]"]:checked').each(function () {
                branches.push($(this).val());
            });

            $.ajax({
                url: base_url + 'onlineexam/assignBranchToExam',
                type: 'POST',
                data: { exam_id: examID, branches: branches },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        alertMsg(response.message, "success", "Success", "");
                        $.magnificPopup.close();
                        $('#search-btn').trigger('click');
                    } else {
                        alertMsg("Failed to assign branches.", "error", "Error", "");
                    }
                },
                error: function () {
                    alertMsg("An unexpected error occurred.", "error", "Error", "");
                }
            });
        });

    });


    function openAssignBranchModal($id) {
        // console.log("assign branch called", $id);

        if ($id) {
            $.ajax({
                url: base_url + 'onlineexam/getBrancheswithExamAssignment',
                method: "POST",
                data: { exam_id: $id },
                success: function (response) {
                    $('#branchTable tbody').html(response);
                    $('#assign_exam_id').val($id);
                },
                error: function () {
                    alertMsg("Error loading branch data.", "error", "Error", "");
                }
            });
        }

        mfp_modal('#modal');
    }


</script>