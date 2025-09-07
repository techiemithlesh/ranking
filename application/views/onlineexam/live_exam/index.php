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



<script type="text/javascript">
    $(document).ready(function () {
        // initiate Datatable
        initDatatable('.live-exam-list', 'liveexam/getLiveExamListDT', {}, 25);

        $('#class_id').on('change', function () {
            var classID = $(this).val();
            $.ajax({
                url: base_url + 'onlineexam/getByClass',
                type: 'POST',
                data: {
                    classID: classID
                },
                success: function (data) {
                    $('#subject_id').html(data);
                }
            });
        });


    });



</script>