<?php if (!empty($question)): ?>
    <?php
    $quesOption = [
        'opt_1' => 1,
        'opt_2' => 2,
        'opt_3' => 3,
        'opt_4' => 4,
    ];
    ?>
    <?= form_open('#', ['id' => 'answerForm']) ?>
        <input type="hidden" name="online_exam_id" value="<?= $exam_id ?>">
        <input type="hidden" name="session_id" value="<?= $session_id ?>">
        <input type="hidden" name="question_id" value="<?= $question->question_id ?>">

        <section class="panel pg-fw">
            <div class="panel-body">
                <h5 class="chart-title mb-xs">
                    <i class="fas fa-clipboard-question"></i>
                    <?= translate('question') ?>
                </h5>

                <div class="mt-lg">
                    <p><?= $question->question ?></p>

                    <div class="mt-lg mb-sm">
                        <?php if ($question->type == 1): // MCQ ?>
                            <?php foreach ($quesOption as $k => $v): ?>
                                <?php if (!empty($question->{$k})): ?>
                                    <div class="radio-custom radio-success mt-md">
                                        <input type="radio" value="<?= $v ?>"
                                               name="answer[<?= $question->question_id ?>][<?= $question->type ?>]"
                                               id="opt<?= $question->question_id . $v ?>">
                                        <label for="opt<?= $question->question_id . $v ?>">
                                            <?= $question->{$k} ?>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        <?php elseif ($question->type == 2): // Multi select ?>
                            <?php foreach ($quesOption as $k => $v): ?>
                                <?php if (!empty($question->{$k})): ?>
                                    <div class="checkbox-replace mt-lg">
                                        <label class="i-checks">
                                            <input type="checkbox"
                                                   name="answer[<?= $question->question_id ?>][<?= $question->type ?>][]"
                                                   value="<?= $v ?>"> <i></i>
                                            <?= $question->{$k} ?>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        <?php elseif ($question->type == 3): // True/False ?>
                            <div class="radio-custom radio-success mt-md">
                                <input type="radio" value="1"
                                       name="answer[<?= $question->question_id ?>][<?= $question->type ?>]"
                                       id="tf1<?= $question->question_id ?>">
                                <label for="tf1<?= $question->question_id ?>">TRUE</label>
                            </div>
                            <div class="radio-custom radio-success mt-md">
                                <input type="radio" value="2"
                                       name="answer[<?= $question->question_id ?>][<?= $question->type ?>]"
                                       id="tf0<?= $question->question_id ?>">
                                <label for="tf0<?= $question->question_id ?>">FALSE</label>
                            </div>

                        <?php elseif ($question->type == 4): // Text answer ?>
                            <div class="form-group">
                                <label class="control-label">Answer</label>
                                <input type="text" class="form-control"
                                       name="answer[<?= $question->question_id ?>][<?= $question->type ?>]">
                            </div>
                        <?php endif; ?>

                        <!-- Marks / Negative marking -->
                        <?php if ($exam->marks_display == 1 || $exam->neg_mark == 1): ?>
                            <div class="ques-marks mt-lg">
                                <div class="row">
                                    <?php if ($exam->marks_display == 1): ?>
                                        <div class="col-xs-6">Marks :
                                            <strong><?= $question->marks ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($exam->neg_mark == 1): ?>
                                        <div class="col-xs-6 text-right">
                                            <?= translate('negative_marks') ?> :
                                            <strong><?= $question->neg_marks ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-lg">
                    <button type="submit" class="btn btn-success">
                        <?= translate('submit_answer') ?>
                    </button>
                </div>
            </div>
        </section>
    <?= form_close() ?>
<?php endif; ?>
