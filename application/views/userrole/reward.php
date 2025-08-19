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
        <!-- <p class="mt-2 small text-muted"><?= translate('preferred_currency') ?>: Danish Kroner</p> -->

        <!-- Tabs -->
        <ul class="nav nav-tabs nav-justified mt-8" role="tablist" style="padding-top: 20px;">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#rewardsTab"><?= translate('rewards') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#historyTab"><?= translate('history') ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#redeemTab"><?= translate('redeem') ?></a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3">
            <div id="rewardsTab" class="tab-pane active">
                <div id="rewardsTab" class="tab-pane active show active">
                    <?php if (!empty($rewards)): ?>
                        <div class="row">
                            <?php foreach ($rewards as $r): ?>
                                <div class="col-12 col-sm-6 col-lg-3 mb-4">
                                    <div class="card h-100 shadow-sm reward-card border-0">
                                        <div class="card-body d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="fw-semibold fs-6 mb-1">
                                                    <?= $r['exam_name'] ?>
                                                    <span class="text-muted">(<?= ucfirst($r['exam_type']) ?>)</span>
                                                </div>
                                                <div class="text-muted small">
                                                    <?= translate('score') ?>         <?= $r['min_percentage'] ?>%+
                                                    <?= translate('to_earn') ?>
                                                </div>
                                            </div>
                                            <div class="mt-3 text-end">
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6">
                                                    +<?= $r['coin_reward'] ?> <i class="fas fa-coins"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted text-center py-4"><?= translate('no_reward_opportunities_found') ?></div>
                    <?php endif; ?>
                </div>

            </div>
            <div id="historyTab" class="tab-pane fade text-left">
                <?php if (!empty($history)): ?>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th><?= translate('exam_name') ?></th>
                                <th><?= translate('exam_type') ?></th>
                                <th><?= translate('coins') ?></th>
                                <th><?= translate('date') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                                <tr>
                                    <td><?= $h['exam_name'] ?></td>
                                    <td><?= ucfirst($h['exam_type']) ?></td>
                                    <td><?= $h['earned_coins'] ?></td>
                                    <td><?= get_nicetime($h['date']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-muted"><?= translate('no_records_found') ?></div>
                <?php endif; ?>
            </div>

            <div id="redeemTab" class="tab-pane fade text-muted">
                <p class="mt-3"><?= translate('coming_soon') ?>...</p>
            </div>
        </div>
    </div>
</section>