<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title">
            <i class="fas fa-trophy text-warning"></i> <?= translate('leaderboard') ?>
        </h4>
    </header>
    <div class="panel-body">
        <!-- Top 3 Students -->
        <?php if (!empty($topStudents)): ?>
            <div class="row justify-content-center align-items-end leaderboard-top mb-5">

                <?php if (isset($topStudents[1])): ?>
                    <!-- Rank 2 -->
                    <div class="col-md-3 text-center">
                        <div class="leader-card rank-2">
                            <div class="avatar">
                                <img src="<?= get_image_url('student', $topStudents[1]['photo']); ?>" class="avatar-img">
                                <span class="medal">🥈</span>
                            </div>
                            <h5 class="fw-bold mt-2"><?= $topStudents[1]['first_name'] . " " . $topStudents[1]['last_name']; ?>
                            </h5>
                            <p class="text-muted">
                                <?= $topStudents[1]['class_name'] . " - " . $topStudents[1]['section_name']; ?>
                            </p>
                            <p class="score"><?= $topStudents[1]['obtain_marks']; ?> / <?= $topStudents[1]['total_marks']; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($topStudents[0])): ?>
                    <!-- Rank 1 -->
                    <div class="col-md-5 text-center">
                        <div class="leader-card rank-1 big-card">
                            <div class="avatar">
                                <img src="<?= get_image_url('student', $topStudents[0]['photo']); ?>" class="avatar-img">
                                <span class="medal">🥇</span>
                            </div>
                            <h4 class="fw-bold mt-2"><?= $topStudents[0]['first_name'] . " " . $topStudents[0]['last_name']; ?>
                            </h4>
                            <p class="text-muted">
                                <?= $topStudents[0]['class_name'] . " - " . $topStudents[0]['section_name']; ?>
                            </p>
                            <p class="score display-6"><?= $topStudents[0]['obtain_marks']; ?> /
                                <?= $topStudents[0]['total_marks']; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($topStudents[2])): ?>
                    <!-- Rank 3 -->
                    <div class="col-md-3 text-center">
                        <div class="leader-card rank-3">
                            <div class="avatar">
                                <img src="<?= get_image_url('student', $topStudents[2]['photo']); ?>" class="avatar-img">
                                <span class="medal">🥉</span>
                            </div>
                            <h5 class="fw-bold mt-2"><?= $topStudents[2]['first_name'] . " " . $topStudents[2]['last_name']; ?>
                            </h5>
                            <p class="text-muted">
                                <?= $topStudents[2]['class_name'] . " - " . $topStudents[2]['section_name']; ?>
                            </p>
                            <p class="score"><?= $topStudents[2]['obtain_marks']; ?> / <?= $topStudents[2]['total_marks']; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>


        <!-- Rest of Leaderboard Table -->
        <?php if (!empty($otherStudents)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-list-ol"></i> <?= translate('all_rankings') ?></h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= translate('rank') ?></th>
                                <th><?= translate('student') ?></th>
                                <th><?= translate('class') ?></th>
                                <th><?= translate('correct') ?></th>
                                <th><?= translate('wrong') ?></th>
                                <th><?= translate('skipped') ?></th>
                                <th><?= translate('marks') ?></th>
                                <th><?= translate('percentile') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($otherStudents as $stu): ?>
                                <tr>
                                    <td><span class="badge bg-primary"><?= $stu['rank_position']; ?></span></td>
                                    <td>
                                        <img src="<?= get_image_url('student', $stu['photo']); ?>" class="avatar-mini">
                                        <?= $stu['first_name'] . " " . $stu['last_name']; ?>
                                    </td>
                                    <td><?= $stu['class_name'] . " - " . $stu['section_name']; ?></td>
                                    <td class="text-success"><?= $stu['correct_ans']; ?></td>
                                    <td class="text-danger"><?= $stu['wrong_ans']; ?></td>
                                    <td><?= $stu['total_skipped']; ?></td>
                                    <td><strong><?= $stu['obtain_marks']; ?> / <?= $stu['total_marks']; ?></strong></td>
                                    <td><?= $stu['percentile']; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center mt-4">
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
    .leader-card {
        border-radius: 15px;
        padding: 20px;
        background: #f8f9fa;
        transition: all 0.3s ease;
        position: relative;
    }

    .leader-card:hover {
        transform: translateY(-5px);
    }

    .big-card {
        transform: scale(1.1);
        z-index: 2;
    }

    .rank-1 {
        background: linear-gradient(135deg, #ffecb3, #ffd54f);
    }

    .rank-2 {
        background: linear-gradient(135deg, #e3f2fd, #90caf9);
    }

    .rank-3 {
        background: linear-gradient(135deg, #ffe0b2, #ffb74d);
    }

    .avatar {
        position: relative;
        display: inline-block;
    }

    .avatar-img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 3px solid #fff;
        object-fit: cover;
    }

    .medal {
        position: absolute;
        bottom: -5px;
        right: -5px;
        font-size: 1.5rem;
    }

    .score {
        font-weight: bold;
        margin-top: 5px;
    }

    .avatar-mini {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 8px;
        object-fit: cover;
    }
</style>