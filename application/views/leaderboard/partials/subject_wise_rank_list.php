<style>
    .rank-badge {
        font-size: 13px;
        font-weight: bold;
        padding: 4px 10px;
        border-radius: 12px;
        color: #fff;
        display: inline-block;
    }

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
</style>

<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><?= translate('subject_wise_leaderboard'); ?></h4>
    </header>

    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-none">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= translate('student') ?></th>
                        <th><?= translate('photo') ?></th>
                        <th><?= translate('obtained_marks') ?></th>
                        <th><?= translate('total_marks') ?></th>
                        <th><?= translate('percentage') ?></th>
                        <th><?= translate('rank') ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $rank = 1;
                    if (!empty($leaderboard)):
                        foreach ($leaderboard as $row):
                            ?>
                            <tr>
                                <td><?= $rank ?></td>

                                <td>
                                    <?= $row['first_name'] . ' ' . $row['last_name']; ?>
                                </td>

                                <td>
                                    <img src="<?= get_image_url('student', $row['photo']); ?>" class="img-circle" width="40"
                                        height="40">
                                </td>

                                <td><strong><?= $row['obtained_marks']; ?></strong></td>

                                <td><?= $row['total_marks']; ?></td>

                                <td>
                                    <span class="label label-info">
                                        <?= number_format($row['percentage'], 2); ?>%
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    $badge = "";
                                    if ($rank == 1)
                                        $badge = "rank-1";
                                    elseif ($rank == 2)
                                        $badge = "rank-2";
                                    elseif ($rank == 3)
                                        $badge = "rank-3";
                                    ?>
                                    <span class="rank-badge <?= $badge ?>"><?= $rank ?></span>
                                </td>
                            </tr>
                            <?php
                            $rank++;
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>