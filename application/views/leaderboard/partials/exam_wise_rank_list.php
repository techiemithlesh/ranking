<style>
    .rank-badge {
        font-size: 13px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 12px;
        color: #fff;
        display: inline-block;
        min-width: 32px;
        text-align: center;
    }

    /* Top 3 medals */
    .rank-1 {
        background: #FFD700;
    }

    /* Gold */
    .rank-2 {
        background: #C0C0C0;
    }

    /* Silver */
    .rank-3 {
        background: #CD7F32;
    }

    /* Bronze */

    /* Ranks 4+ */
    .rank-default {
        background: #6c757d;
    }

    /* Highlight for logged-in student */
    .my-rank-row {
        background: #FFF7CC !important;
        font-weight: 600;
        border-left: 4px solid #FFC400;
        animation: glowPulse 1.8s ease-in-out infinite;
    }

    /* Glow effect */
    @keyframes glowPulse {
        0% {
            box-shadow: 0 0 0px rgba(255, 196, 0, 0.4);
        }

        50% {
            box-shadow: 0 0 12px rgba(255, 196, 0, 0.8);
        }

        100% {
            box-shadow: 0 0 0px rgba(255, 196, 0, 0.4);
        }
    }

    /* YOU tag */
    .you-badge {
        background: #FF9800;
        color: #fff;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 6px;
    }

    .rank-ribbon-box {
        background: #FFF4C2;
        border-left: 4px solid #FFC400;
        padding: 12px 18px;
        margin-bottom: 15px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        font-weight: 600;
        color: #5A4A00;
    }

    .rank-ribbon-box i {
        font-size: 20px;
        margin-right: 10px;
        color: #E0A800;
    }
</style>

<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><?= translate('subject_wise_leaderboard'); ?></h4>
    </header>

    <div class="panel-body">

        <?php if (isset($loggedStudentID) && !empty($leaderboard)): ?>

            <?php
            $myRank = null;
            $totalStudents = count($leaderboard);

            foreach ($leaderboard as $index => $row) {
                if ($row['student_id'] == $loggedStudentID) {
                    $myRank = $index + 1;
                    break;
                }
            }
            ?>

            <?php if ($myRank): ?>
                <div class="rank-ribbon-box">
                    <i class="fas fa-trophy"></i>
                    Your Rank: <?= $myRank ?> of <?= $totalStudents ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>


        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-none">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= translate('student') ?></th>
                        <th><?= translate('photo') ?></th>
                        <th><?= translate('sessions') ?></th>
                        <th><?= translate('obtained_marks') ?></th>
                        <th><?= translate('total_marks') ?></th>
                        <th><?= translate('percentage') ?></th>
                        <th><?= translate('rank') ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php $rank = 1;
                    if (!empty($leaderboard)): ?>
                        <?php foreach ($leaderboard as $row): ?>

                            <?php
                            $isMe = (isset($loggedStudentID) && $loggedStudentID == $row['student_id']);
                            ?>

                            <tr class="<?= $isMe ? 'my-rank-row' : '' ?>" id="<?= $isMe ? 'my-rank' : '' ?>">

                                <td><?= $rank ?></td>

                                <td>
                                    <?= $row['first_name'] . ' ' . $row['last_name']; ?>
                                    <?php if ($isMe): ?>
                                        <span class="you-badge">YOU</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <img src="<?= get_image_url('student', $row['photo']); ?>" class="img-circle" width="40"
                                        height="40">
                                </td>

                                <td><?=  (int)$row['sessions_count'] ?></td>

                                <td><strong><?= $row['obtained_marks']; ?></strong></td>
                                <td><?= $row['total_marks']; ?></td>

                                <td>
                                    <span class="label label-info">
                                        <?= number_format($row['percentage'], 2); ?>%
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    $badgeClass = "rank-default";
                                    if ($rank == 1)
                                        $badgeClass = "rank-1";
                                    elseif ($rank == 2)
                                        $badgeClass = "rank-2";
                                    elseif ($rank == 3)
                                        $badgeClass = "rank-3";
                                    ?>
                                    <span class="rank-badge <?= $badgeClass ?>"><?= $rank ?></span>
                                </td>

                            </tr>

                            <?php $rank++; endforeach; ?>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</section>

<script>
    $(document).ready(function () {
        let myRow = document.getElementById('my-rank');
        if (myRow) {
            setTimeout(() => {
                myRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 400);
        }
    });
</script>