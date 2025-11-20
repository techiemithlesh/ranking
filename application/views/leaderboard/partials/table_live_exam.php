<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-list-ol"></i> <?= translate('leaderboard') ?></h4>
    </header>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-condensed mb-none datatable-export">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= translate('rank') ?></th>
                        <th><?= translate('student') ?></th>
                        <th><?= translate('class') ?></th>
                        <th><?= translate('correct') ?></th>
                        <th><?= translate('wrong') ?></th>
                        <th><?= translate('skipped') ?></th>
                        <th><?= translate('marks') ?></th>
                        <th><?= translate('percentage') ?></th>
                        <th><?= translate('percentile') ?></th>
                        <th><?= translate('action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    foreach ($leaderboard as $row): ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><span class="badge bg-primary"><?= $row['rank_position']; ?></span></td>
                            <td><?= $row['first_name'] . " " . $row['last_name']; ?></td>
                            <td><?= $row['class_name'] . " - " . $row['section_name']; ?></td>
                            <td class="text-success"><?= $row['correct_ans']; ?></td>
                            <td class="text-danger"><?= $row['wrong_ans']; ?></td>
                            <td><?= $row['total_skipped']; ?></td>
                            <td><strong><?= $row['obtain_marks']; ?>/<?= $row['total_marks']; ?></strong></td>
                            <td><?= $row['percentage']; ?>%</td>
                            <td><?= $row['percentile']; ?>%</td>
                            <td>

                                <a href="<?= base_url('Liveexam_student/verify?session=' . $row['session_code'] . '&student=' . $row['student_id']) ?>"
                                    target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> <?= translate('view') ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>