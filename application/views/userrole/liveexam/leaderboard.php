<section class="panel leaderboard-panel">
    <header class="panel-heading clearfix">
        <h4 class="panel-title pull-left">
            <i class="fa fa-trophy text-warning"></i> <?= translate('leaderboard') ?>
        </h4>
    </header>

    <div class="panel-body">

        <!-- Top 3 -->
        <?php if (!empty($topStudents)): ?>
            <div class="row text-center leaderboard-top" style="margin-bottom:40px;">

                <?php if (isset($topStudents[1])): ?>
                    <div class="col-sm-4">
                        <div class="leader-card rank-2">
                            <div class="avatar">
                                <img src="<?= get_image_url('student', $topStudents[1]['photo']); ?>" class="avatar-img">
                                <span class="medal">🥈</span>
                            </div>
                            <h5><?= $topStudents[1]['first_name']." ".$topStudents[1]['last_name']; ?></h5>
                            <p class="text-muted"><?= $topStudents[1]['class_name']." - ".$topStudents[1]['section_name']; ?></p>
                            <p class="score"><?= $topStudents[1]['obtain_marks']." / ".$topStudents[1]['total_marks']; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($topStudents[0])): ?>
                    <div class="col-sm-4">
                        <div class="leader-card rank-1 big-card">
                            <div class="avatar">
                                <img src="<?= get_image_url('student', $topStudents[0]['photo']); ?>" class="avatar-img">
                                <span class="medal">🥇</span>
                            </div>
                            <h4><?= $topStudents[0]['first_name']." ".$topStudents[0]['last_name']; ?></h4>
                            <p class="text-muted"><?= $topStudents[0]['class_name']." - ".$topStudents[0]['section_name']; ?></p>
                            <p class="score"><?= $topStudents[0]['obtain_marks']." / ".$topStudents[0]['total_marks']; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($topStudents[2])): ?>
                    <div class="col-sm-4">
                        <div class="leader-card rank-3">
                            <div class="avatar">
                                <img src="<?= get_image_url('student', $topStudents[2]['photo']); ?>" class="avatar-img">
                                <span class="medal">🥉</span>
                            </div>
                            <h5><?= $topStudents[2]['first_name']." ".$topStudents[2]['last_name']; ?></h5>
                            <p class="text-muted"><?= $topStudents[2]['class_name']." - ".$topStudents[2]['section_name']; ?></p>
                            <p class="score"><?= $topStudents[2]['obtain_marks']." / ".$topStudents[2]['total_marks']; ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>


        <!-- My Rank -->
        <?php if (!empty($studentRank)): ?>
            <div class="text-center" style="margin-bottom:40px;">
                <h5 style="margin-bottom:15px;"><i class="fa fa-user text-success"></i> <?= translate('your_rank') ?></h5>
                <div class="leader-card my-rank-highlight" style="max-width:400px; margin:0 auto;">
                    <div class="avatar">
                        <img src="<?= get_image_url('student', $studentRank['photo']); ?>" class="avatar-img">
                        <span class="label label-success me-badge">YOU</span>
                    </div>
                    <h4><?= $studentRank['first_name']." ".$studentRank['last_name']; ?></h4>
                    <p class="text-muted"><?= $studentRank['class_name']." - ".$studentRank['section_name']; ?></p>
                    <p class="score"><?= $studentRank['obtain_marks']." / ".$studentRank['total_marks']; ?></p>
                    <span class="label label-primary">Rank #<?= $studentRank['rank_position']; ?></span>
                </div>
            </div>
        <?php endif; ?>


        <!-- Nearby Students -->
        <?php if (!empty($nearbyStudents)): ?>
            <div class="panel panel-default">
                <div class="panel-heading clearfix">
                    <h5 class="panel-title pull-left"><i class="fa fa-users"></i> <?= translate('near_me') ?></h5>
                    <button class="btn btn-xs btn-default pull-right" data-toggle="collapse" data-target="#nearMeTable">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                </div>
                <div id="nearMeTable" class="panel-collapse collapse in">
                    <div class="table-responsive" style="padding:15px;">
                        <table class="table table-striped table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Correct</th>
                                    <th>Wrong</th>
                                    <th>Skipped</th>
                                    <th>Marks</th>
                                    <th>Percentile</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($nearbyStudents as $stu): ?>
                                    <tr class="<?= $stu['student_id'] == $studentRank['student_id'] ? 'success' : '' ?>">
                                        <td><span class="label label-primary">#<?= $stu['rank_position']; ?></span></td>
                                        <td>
                                            <img src="<?= get_image_url('student', $stu['photo']); ?>" class="avatar-mini">
                                            <?= $stu['first_name']." ".$stu['last_name']; ?>
                                            <?php if ($stu['student_id'] == $studentRank['student_id']): ?>
                                                <span class="label label-success">YOU</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $stu['class_name']." - ".$stu['section_name']; ?></td>
                                        <td class=""><?= $stu['correct_ans']; ?></td>
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
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    .leader-card {
        border-radius: 15px;
        padding: 20px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .leader-card:hover { transform: translateY(-4px); transition: 0.3s; }

    .big-card { transform: scale(1.1); z-index: 2; }

    .rank-1 { background: linear-gradient(135deg,#fff59d,#fbc02d); }
    .rank-2 { background: linear-gradient(135deg,#bbdefb,#2196f3); }
    .rank-3 { background: linear-gradient(135deg,#ffccbc,#ff7043); }

    .my-rank-highlight {
        border: 3px solid #28a745;
        background: #e8f5e9;
        box-shadow: 0 0 15px rgba(40,167,69,0.4);
    }

    .avatar { position: relative; display:inline-block; }
    .avatar-img {
        width: 80px; height: 80px; border-radius: 50%;
        border: 3px solid #fff; object-fit:cover;
    }
    .avatar-mini {
        width: 30px; height: 30px; border-radius: 50%;
        margin-right: 5px; object-fit:cover; border: 2px solid #ddd;
    }

    .medal, .me-badge {
        position: absolute; bottom:-6px; right:-6px;
        font-size: 14px; background:#fff; border-radius:50%;
        padding:4px; box-shadow:0 2px 5px rgba(0,0,0,0.2);
    }
    .me-badge {
        background:#28a745; color:#fff; border-radius:12px;
        padding:3px 8px; bottom:-12px; right:-12px;
    }

    .score { font-weight:bold; margin-top:8px; font-size:1.1rem; }
</style>
