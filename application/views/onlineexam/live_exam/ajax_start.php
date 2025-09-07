<?php
if (!empty($questions)) {
    $totalQuestions = count($questions);
    ?>
    <div class="row mt-lg">

        <div class="col-md-5"> 
            <div class="row">

            <!-- PARTICIPANTS PANEL -->
            <div class="col-sm-12">
                <section class="panel pg-fw">
                    <div class="panel-body">
                        <h5 class="chart-title mb-xs"><i class="fas fa-user-friends"></i> <?=translate('participants')?></h5>
                        <div class="mt-md">
                            <ul id="host_participants_list" class="list-unstyled">
                                <?php if (!empty($participants)) {
                                    foreach ($participants as $p) { ?>
                                        <li id="p_<?= intval($p->student_id) ?>">
                                            <strong><?= html_escape($p->name ?? ('#' . $p->student_id)) ?></strong>
                                            <?php if (!empty($p->admission_no)) { ?>
                                                <span class="text-muted"> (<?= html_escape($p->admission_no) ?>)</span>
                                            <?php } ?>
                                            <div class="small text-muted"><?= !empty($p->joined_at) ? html_escape(date('d-M H:i', strtotime($p->joined_at))) : '' ?></div>
                                        </li>
                                    <?php }
                                } else { ?>
                                    <li class="text-muted"><?= translate('no_participants_yet') ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
                
                <div class="col-sm-12">
                    <section class="panel pg-fw">
                        <div class="panel-body">
                            <h5 class="chart-title mb-xs"><i class="fas fa-clock"></i>
                                <?= translate('time') . " " . translate('status') ?></h5>
                            <div class="time_status mt-md">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h4><?= translate('total') . " " . translate('time') ?> :</h4>
                                    </div>
                                    <div class="col-sm-6">
                                        <h4 class="text-dark"><?= $exam->duration ?></h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h4><?= translate('remain_time') ?> :</h4>
                                    </div>
                                    <div class="col-sm-6">
                                        <h4 class="remain_duration text-dark"><?= $exam->duration ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- QUESTIONS -->
                <div class="col-sm-12">
                    <section class="panel pg-fw">
                        <div class="panel-body">
                            <h5 class="chart-title mb-xs"><i class="fas fa-circle-question"></i>
                                <?= translate('total_questions_map') ?></h5>
                            <div class="mt-lg">
                                <nav>
                                    <ul class="on_answer_box questionColor">
                                        <?php foreach ($questions as $key => $question) { ?>
                                            <li><a class="que_btn <?= $key == 0 ? 'active' : '' ?>"
                                                    id="question<?php echo $key + 1 ?>" href="javascript:void(0);"
                                                    onclick="changeQuestion(<?php echo $key + 1 ?>)"><?php echo $key + 1 ?></a></li>
                                        <?php } ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="box wizard" data-initialize="wizard" id="fueluxWizard">
                <div class="steps-container">
                    <ul class="steps hidden" style="margin-left: 0;">
                        <?php foreach (range(1, $totalQuestions) as $value) { ?>
                            <li data-step="<?= $value ?>" class="<?= $value == 1 ? 'active' : '' ?>"></li>
                        <?php } ?>
                    </ul>
                </div>
                
                <input type="hidden" name="online_exam_id" value="<?= $exam->id ?>">
                <div class="box-body step-content">
                    <?php foreach ($questions as $key => $question) { ?>
                        <div class="clearfix step-pane <?= $key == 0 ? 'active' : '' ?>" data-step="<?= $key + 1 ?>">
                            <section class="panel pg-fw">
                                <div class="panel-body">
                                    <h5 class="chart-title mb-xs"><i class="fas fa-clipboard-question"></i>
                                        <?= translate('question') ?>         <?= $key + 1 ?> of <?= $totalQuestions ?></h5>
                                    <div class="mt-lg">
                                        <p><?= $question->question ?></p>
                                        
                                    </div>
                                </div>
                            </section>
                        </div>
                    <?php } ?>
                    <div class="question-answer-button">
                        <button class="btn btn-default btn-prev mr-xs mt-sm" type="button" name="" id="prevbutton"
                            disabled="disabled"><i class="fa fa-angle-left"></i> Previous</button>
                        <button class="btn btn-default btn-next mr-xs mt-sm" type="button" name="" id="nextbutton"
                            data-last="Complete "><?= translate('next') ?> <i class="fa fa-angle-right"></i></button>

                        <button class="btn btn-danger mr-xs mt-sm" type="button" id="end_session_btn">
                            <i class="fas fa-stop-circle"></i> <?= translate('end_session') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } else {
    echo '<div class="alert alert-subl mt-lg text-center">' . translate('no_questions_have_been_assigned') . ' !</div>';
} ?>