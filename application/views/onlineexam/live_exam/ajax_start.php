<?php if (!empty($questions)) : ?>
    <?php $totalQuestions = count($questions); ?>
    <div class="row mt-lg">

        <!-- LEFT PANEL (Participants + Time + Map) -->
        <div class="col-md-5">
            <div id="sessionInfo" class="alert alert-info mt-md"></div>

            <div class="row">

                <!-- Participants -->
                <div class="col-sm-12">
                    <section class="panel pg-fw">
                        <div class="panel-body">
                            <h5 class="chart-title mb-xs">
                                <i class="fas fa-user-friends"></i> <?= translate('participants') ?>
                            </h5>
                            <div class="mt-md">
                                <ul id="host_participants_list" class="list-unstyled">
                                    <?php if (!empty($participants)) : ?>
                                        <?php foreach ($participants as $p) : ?>
                                            <li id="p_<?= intval($p->student_id) ?>">
                                                <strong><?= html_escape($p->name ?? ('#' . $p->student_id)) ?></strong>
                                                <?php if (!empty($p->admission_no)) : ?>
                                                    <span class="text-muted">(<?= html_escape($p->admission_no) ?>)</span>
                                                <?php endif; ?>
                                                <div class="small text-muted">
                                                    <?= !empty($p->joined_at) ? date('d-M H:i', strtotime($p->joined_at)) : '' ?>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <li class="text-muted"><?= translate('no_participants_yet') ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Timer -->
                <div class="col-sm-12">
                    <section class="panel pg-fw">
                        <div class="panel-body">
                            <h5 class="chart-title mb-xs">
                                <i class="fas fa-clock"></i> <?= translate('time_status') ?>
                            </h5>
                            <div class="mt-md">
                                <div class="row">
                                    <div class="col-sm-6"><h5><?= translate('total_time') ?>:</h5></div>
                                    <div class="col-sm-6"><h5 class="text-dark"><?= $exam->duration ?></h5></div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6"><h5><?= translate('remain_time') ?>:</h5></div>
                                    <div class="col-sm-6"><h5 class="remain_duration text-dark"><?= $exam->duration ?></h5></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Question Map -->
                <div class="col-sm-12">
                    <section class="panel pg-fw">
                        <div class="panel-body">
                            <h5 class="chart-title mb-xs">
                                <i class="fas fa-circle-question"></i> <?= translate('question_map') ?>
                            </h5>
                            <div class="mt-lg">
                                <nav>
                                    <ul class="on_answer_box questionColor">
                                        <?php foreach ($questions as $key => $q) : ?>
                                            <li>
                                                <a class="que_btn <?= $key == 0 ? 'active' : '' ?>"
                                                   id="question<?= $key+1 ?>"
                                                   href="javascript:void(0);">
                                                    <?= $key+1 ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </section>
                </div>

            </div>
        </div>

        <!-- RIGHT PANEL (Question display) -->
        <div class="col-md-7">
            <div class="box wizard" data-initialize="wizard" id="fueluxWizard">
                <div class="steps-container">
                    <ul class="steps hidden">
                        <?php foreach (range(1, $totalQuestions) as $i): ?>
                            <li data-step="<?= $i ?>" class="<?= $i==1 ? 'active':'' ?>"></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="box-body step-content">
                    <?php foreach ($questions as $key => $q): ?>
                        <div class="clearfix step-pane <?= $key==0 ? 'active':'' ?>" data-step="<?= $key+1 ?>" data-question-id="<?= $q->question_id ?>">
                            <section class="panel pg-fw">
                                <div class="panel-body">
                                    <h5 class="chart-title mb-xs">
                                        <i class="fas fa-clipboard-question"></i>
                                        <?= translate('question') ?> <?= $key+1 ?> of <?= $totalQuestions ?>
                                    </h5>
                                    <div class="mt-lg">
                                        <p><?= $q->question ?></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    <?php endforeach; ?>

                    <!-- Host Controls -->
                    <div class="question-answer-button text-center">
                        <button class="btn btn-default btn-prev mr-xs mt-sm" type="button" id="prevbutton" disabled>
                            <i class="fa fa-angle-left"></i> <?= translate('previous') ?>
                        </button>
                        <button class="btn btn-default btn-next mr-xs mt-sm" type="button" id="nextbutton">
                            <?= translate('next') ?> <i class="fa fa-angle-right"></i>
                        </button>
                        <button class="btn btn-danger mr-xs mt-sm" type="button" id="end_session_btn">
                            <i class="fas fa-stop-circle"></i> <?= translate('end_session') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
<?php else: ?>
    <div class="alert alert-subl mt-lg text-center">
        <?= translate('no_questions_have_been_assigned') ?> !
    </div>
<?php endif; ?>
