<style>
    .nav-tab {
        padding: 10px 20px;
        cursor: pointer;
        border-bottom: 2px solid transparent;
    }

    .nav-tab.tab-active {
        border-bottom: 2px solid #f46c6c;
        font-weight: bold;
        color: #f46c6c;
    }

    .reward-card {
        transition: transform 0.2s ease-in-out;
        padding: 10px;
        border-radius: 12px;
    }

    .reward-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    .tab-content {
        margin-top: 20px;
    }
</style>

<section class="panel">
    <header class="panel-heading text-center">
        <h4 class="panel-title"><i class="fas fa-coins"></i> <?= translate('my_coins') ?></h4>
    </header>

    <div class="panel-body text-center">
        <p class="mb-1"><?= translate('your_available_reward_coins') ?>:</p>
        <div
            style="display:inline-block; padding:10px 25px; background: linear-gradient(145deg, #ff735c, #ff8f7a); color:white; border-radius:20px; font-size:24px; font-weight:bold;">
            <?= isset($wallet['total_coins']) ? $wallet['total_coins'] : 0 ?>
        </div>

        <!-- Nav Tabs (Bootstrap 3 format) -->
        <ul class="nav nav-tabs nav-justified" role="tablist" style="margin-top: 25px;">
            <li role="presentation" class="active">
                <a href="#rewardsTab" aria-controls="rewardsTab" role="tab" data-toggle="tab">
                    <?= translate('rewards') ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#historyTab" aria-controls="historyTab" role="tab" data-toggle="tab">
                    <?= translate('history') ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#redeemTab" aria-controls="redeemTab" role="tab" data-toggle="tab">
                    <?= translate('redeem') ?>
                </a>
            </li>
        </ul>

        <!-- Tab Panes -->
        <div class="tab-content">

            <!-- Rewards Tab -->
            <div role="tabpanel" class="tab-pane fade in active" id="rewardsTab">
                <?php if (!empty($rewards)): ?>
                    <div class="row">
                        <?php foreach ($rewards as $r): ?>
                            <div class="col-xs-12 col-sm-6 col-lg-3 mb-md">
                                <div class="panel panel-default shadow-sm reward-card">
                                    <div class="panel-body text-center">
                                        <h5 class="mt-0 mb-xs">
                                            <?= htmlspecialchars($r['exam_name']); ?>
                                            <small class="text-muted">(<?= ucfirst($r['exam_type']); ?>)</small>
                                        </h5>

                                        <!-- Reward Basis & Qualifying Value -->
                                        <p class="text-muted small">
                                            <?php
                                            switch ($r['reward_basis']) {
                                                case 'rank':
                                                    echo translate('rank') . " ≤ " . intval($r['qualifying_value']);
                                                    break;
                                                case 'percentile':
                                                    echo translate('percentile') . " ≥ " . floatval($r['qualifying_value']) . "%";
                                                    break;
                                                default:
                                                    echo translate('percentage') . " ≥ " . floatval($r['qualifying_value']) . "%";
                                                    break;
                                            }
                                            ?>
                                            <br>
                                            <span class="label label-info">
                                                <?= translate('reward_scope') ?>: <?= ucfirst($r['reward_scope']); ?>
                                            </span>
                                        </p>

                                        <!-- Coin Reward -->
                                        <div class="mt-md">
                                            <span class="label label-warning" style="font-size:14px;">
                                                +<?= (int) $r['coin_reward']; ?> <i class="fa fa-coins"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <?= translate('no_reward_opportunities_found'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- History Tab -->
            <div role="tabpanel" class="tab-pane fade" id="historyTab">
                <?php if (!empty($history)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-condensed mb-none">
                            <thead>
                                <tr>
                                    <th><?= translate('exam_name') ?></th>
                                    <th><?= translate('exam_type') ?></th>
                                    <th><?= translate('reward_scope') ?></th>
                                    <th><?= translate('session_code') ?></th>
                                    <th><?= translate('coins') ?></th>
                                    <th><?= translate('remarks') ?></th>
                                    <th><?= translate('date') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $h): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($h['exam_name']); ?></td>
                                        <td><span class="label label-default"><?= ucfirst($h['exam_type']); ?></span></td>
                                        <td><span class="label label-info"><?= ucfirst($h['reward_scope']); ?></span></td>
                                        <td><?= !empty($h['session_code']) ? $h['session_code'] : '-'; ?></td>
                                        <td><strong class="text-success">+<?= $h['earned_coins']; ?></strong></td>
                                        <td><?= !empty($h['remarks']) ? htmlspecialchars($h['remarks']) : '-'; ?></td>
                                        <td><?= get_nicetime($h['date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        <?= translate('no_records_found'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Redeem Tab -->
            <div role="tabpanel" class="tab-pane fade" id="redeemTab">
                <div class="text-center py-4">
                    <p class="lead"><?= translate('coming_soon'); ?>...</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>

    $(document).ready(function () {
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            $(".tab-pane").removeClass("in active");
            $(target).addClass("in active");
        });
    });
</script>