<section class="panel leaderboard-wrapper">
    <header class="panel-heading text-center">
        <h3 class="fw-bold leaderboard-title">
            <i class="fas fa-trophy text-warning"></i> <?= translate('leaderboard') ?>
        </h3>
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
                            <h5 class="fw-bold mt-3"><?= $topStudents[1]['first_name'] . " " . $topStudents[1]['last_name']; ?>
                            </h5>
                            <p class="text-muted small">
                                <?= $topStudents[1]['class_name'] . " - " . $topStudents[1]['section_name']; ?></p>
                            <p class="score"><?= $topStudents[1]['obtain_marks']; ?> / <?= $topStudents[1]['total_marks']; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($topStudents[0])): ?>
                    <!-- Rank 1 -->
                    <div class="col-md-4 text-center">
                        <div class="leader-card rank-1 big-card">
                            <div class="avatar">
                                <img src="<?= get_image_url('student', $topStudents[0]['photo']); ?>" class="avatar-img">
                                <span class="medal gold">🥇</span>
                            </div>
                            <h4 class="fw-bold mt-3"><?= $topStudents[0]['first_name'] . " " . $topStudents[0]['last_name']; ?>
                            </h4>
                            <p class="text-muted small">
                                <?= $topStudents[0]['class_name'] . " - " . $topStudents[0]['section_name']; ?></p>
                            <p class="score display-6 text-success"><?= $topStudents[0]['obtain_marks']; ?> /
                                <?= $topStudents[0]['total_marks']; ?></p>
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
                            <h5 class="fw-bold mt-3"><?= $topStudents[2]['first_name'] . " " . $topStudents[2]['last_name']; ?>
                            </h5>
                            <p class="text-muted small">
                                <?= $topStudents[2]['class_name'] . " - " . $topStudents[2]['section_name']; ?></p>
                            <p class="score"><?= $topStudents[2]['obtain_marks']; ?> / <?= $topStudents[2]['total_marks']; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <!-- Rest of Leaderboard Table -->
        <!-- Rest of Leaderboard Table -->
        <?php if (!empty($otherStudents)): ?>
            <div class="card shadow-sm leaderboard-table">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-list-ol"></i> <?= translate('all_rankings') ?></h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-export" width="100%">
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
                                    <td class="text-success fw-bold"><?= $stu['correct_ans']; ?></td>
                                    <td class="text-danger fw-bold"><?= $stu['wrong_ans']; ?></td>
                                    <td><?= $stu['total_skipped']; ?></td>
                                    <td><strong><?= $stu['obtain_marks']; ?> / <?= $stu['total_marks']; ?></strong></td>
                                    <td><span class="badge bg-info"><?= $stu['percentile']; ?>%</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center mt-4">
                <?= translate('no_students_ranked'); ?>
            </div>
        <?php endif; ?>


    </div>
</section>

<style>
    .leaderboard-wrapper {
        background: #f9fafb;
        border-radius: 10px;
    }

    .leaderboard-title {
        font-size: 1.8rem;
        margin-bottom: 20px;
    }

    /* Top Cards */
    .leader-card {
        border-radius: 15px;
        padding: 20px;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        min-height: 220px;
    }

    .leader-card:hover {
        transform: translateY(-6px) scale(1.03);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .big-card {
        transform: scale(1.1);
        z-index: 2;
    }

    .rank-1 {
        background: linear-gradient(135deg, #fff8e1, #ffe082);
    }

    .rank-2 {
        background: linear-gradient(135deg, #e3f2fd, #90caf9);
    }

    .rank-3 {
        background: linear-gradient(135deg, #fff3e0, #ffb74d);
    }

    .avatar {
        position: relative;
        display: inline-block;
    }

    .avatar-img {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        border: 4px solid #fff;
        object-fit: cover;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .medal {
        position: absolute;
        bottom: -8px;
        right: -8px;
        font-size: 1.8rem;
        animation: bounce 1.2s infinite;
    }

    .gold {
        animation: pulse 1.5s infinite;
    }

    .score {
        font-weight: bold;
        margin-top: 8px;
        font-size: 1.1rem;
    }

    .avatar-mini {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        margin-right: 8px;
        object-fit: cover;
        border: 2px solid #ddd;
    }

    /* Animations */
    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.9;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Table */
    .leaderboard-table table tbody tr:hover {
        background-color: #f1f1f1;
    }

    .leaderboard-table .badge {
        font-size: 0.85rem;
    }
</style>