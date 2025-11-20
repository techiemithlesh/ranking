<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><?= translate('leaderboard_result') ?></h4>
    </header>
    <div class="panel-body">
        <div class="row">
            <?php
            $rankIcons = ['🥇', '🥈', '🥉'];
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
                    <div class="card shadow rounded border-0 <?= $isTop3 ? 'border-top border-4 border-warning' : '' ?>">
                        <div class="card-body text-center">
                            <h3 class="mb-0 fw-bold"><?= $badge ?></h3>
                            <img src="<?= $student_photo ?>" class="rounded-circle my-2" width="60" height="60"
                                style="object-fit: cover; border: 2px solid #dee2e6;" alt="<?= $name ?>" />
                            <h5 class="card-title mt-2"><?= $name ?></h5>
                            <p class="text-muted mb-1"><?= $register ?></p>
                            <p class="text-uppercase small text-secondary"> <?= $subject ?></p>

                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: <?= $percentage ?>%; padding: 0 5px; background-color: <?= $barColor; ?>"
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