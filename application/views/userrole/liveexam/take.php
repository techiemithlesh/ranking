<section class="panel">
    <header class="panel-heading d-flex justify-content-between align-items-center">
        <h4 class="panel-title">
            <i class="fas fa-graduation-cap"></i>
            <?= html_escape($exam->title) ?> - <?= translate('live_exam') ?>
            <span class="badge badge-info text-right">
                <?= translate('session_code') ?>: <?= html_escape($session->session_code) ?>
            </span>
        </h4>

    </header>

    <div class="panel-body">
        <!-- Exam Info Inline -->
        <div class="row mb-md">
            <div class="col-md-12">
                <p>
                    <strong><?= translate('subject') ?>:</strong>
                    <?= str_replace('<br>', ', ', $this->onlineexam_model->getSubjectDetails($exam->subject_id)); ?>
                    &nbsp; | &nbsp;
                    <strong><?= translate('total_questions') ?>:</strong> <?= $exam->questions_qty ?>
                    &nbsp; | &nbsp;
                    <strong><?= translate('duration') ?>:</strong> <?= $exam->duration ?>
                </p>
            </div>
        </div>

        <div class="row">
            <!-- Left Side -->
            <div class="col-md-5">
                <!-- Timer -->
                <section class="panel pg-fw mb-md">
                    <div class="panel-body">
                        <h5 class="chart-title mb-xs">
                            <i class="fas fa-clock"></i> <?= translate('time_status') ?>
                        </h5>
                        <p><strong><?= translate('total_time') ?>:</strong> <?= $exam->duration ?></p>
                        <p><strong><?= translate('remain_time') ?>:</strong>
                            <span id="remain_time"><?= $exam->duration ?></span>
                        </p>
                    </div>
                </section>

                <!-- Question Map -->
                <section class="panel pg-fw">
                    <div class="panel-body">
                        <ul class="on_answer_box questionColor d-flex flex-wrap">
                            <?php for ($i = 1; $i <= $exam->questions_qty; $i++): ?>
                                <li class="mr-xs mb-xs">
                                    <a href="javascript:void(0)" class="que_btn <?= ($i == 1 ? 'active' : '') ?>"
                                        data-question-index="<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </div>
                </section>

            </div>

            <!-- Right Side: Question Full Width -->
            <div class="col-md-7">
                <div id="question_area" class="h-100">
                    <div class="alert alert-info text-center p-lg">
                        <?= translate('waiting_for_host') ?>...
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script type="text/javascript">
    var session_id = "<?= $session->id ?>";
    var exam_id = "<?= $exam->id ?>";
    var pollInterval = null;
    var elapsed_seconds = 0;
    var heartbeatTimer = null;

    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon(base_url + "Liveexam_student/leaveSession",
            new URLSearchParams({ session_id: session_id })
        );
    });

    // -----------------------------
    // Poll Current Question
    // -----------------------------
    function pollCurrentQuestion() {
        $.getJSON(base_url + "Liveexam_student/getCurrentQuestion", { session_id: session_id }, function (resp) {
            console.log("poll response", resp);

            if (resp.status === 1) {
                // ✅ Only reload question if it changed
                if ($("#question_area").data("qid") !== resp.current_step) {
                    $("#question_area").html(resp.html).data("qid", resp.current_step);
                }

                if (resp.current_step) {
                    $(".que_btn").removeClass("active");
                    $(".que_btn[data-question-index='" + resp.current_index + "']").addClass("active");
                }
            } else {
                // 🔹 Handle special end states
                if (resp.code === "completed") {
                    clearInterval(pollInterval);
                    clearInterval(heartbeatTimer);

                    if (resp.is_published == 1) {
                        // swal({
                        //     title: "Exam Completed!",
                        //     text: "Congratulations! Your result is ready.",
                        //     type: "success",
                        //     confirmButtonText: "Download Report",
                        //     allowOutsideClick: false
                        // }).then(() => {
                        //     window.open(base_url + "Liveexam_student/studentReport/" + resp.session_code, "_blank");

                        //     // Redirect to dashboard after short delay
                        //     setTimeout(() => {
                        //         window.location.href = base_url + "liveexam_student";
                        //     }, 10000);
                        // });
                        window.open(base_url + "Liveexam_student/studentReport/" + resp.session_code);

                        setTimeout(() => {
                            window.location.href = base_url + "liveexam_student";
                        }, 10000);

                    } else {
                        swal({
                            title: "Thank You!",
                            text: "You have successfully completed the exam. Results will be published soon.",
                            type: "success",
                            confirmButtonText: "OK",
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.href = base_url + "liveexam_student";
                        });
                    }
                } else if (resp.code === "aborted") {
                    clearInterval(pollInterval);
                    clearInterval(heartbeatTimer);

                    swal({
                        text: "The exam was aborted by the host.",
                        type: "warning",
                        confirmButtonText: "OK",
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = base_url + "liveexam_student";
                    });
                } else {
                    $("#question_area").html('<div class="alert alert-info text-center">' + resp.message + '</div>');
                }
            }
        });
    }

    // -----------------------------
    // Timer
    // -----------------------------
    function startTimer() {
        elapsed_seconds = 0;
        var duration = "<?= $exam->duration ?>"; // HH:MM:SS
        var parts = duration.split(":");
        var totalSeconds = (+parts[0] * 3600) + (+parts[1] * 60) + (+parts[2]);

        var timerInterval = setInterval(function () {
            elapsed_seconds++;
            var remaining = totalSeconds - elapsed_seconds;

            if (remaining <= 0) {
                clearInterval(timerInterval);
                $("#remain_time").text("00:00:00");
                $("#answerForm").submit(); // auto-submit
                return;
            }

            var rh = Math.floor(remaining / 3600);
            var rm = Math.floor((remaining % 3600) / 60);
            var rs = remaining % 60;

            $("#remain_time").text(
                String(rh).padStart(2, "0") + ":" +
                String(rm).padStart(2, "0") + ":" +
                String(rs).padStart(2, "0")
            );
        }, 1000);
    }

    // -----------------------------
    // Heartbeat (student presence)
    // -----------------------------
    function startHeartbeat() {
        heartbeatTimer = setInterval(function () {
            $.post(base_url + "Liveexam_student/studentHeartbeat", {
                session_id: session_id
            });
        }, 10000); // every 10s
    }

    // -----------------------------
    // Submit Answer
    // -----------------------------
    $(document).on('submit', '#answerForm', function (e) {
        e.preventDefault();

        var form = $(this);

        // ✅ Prevent poll overwrite during submission
        clearInterval(pollInterval);

        $.post(base_url + "Liveexam_student/submitAnswer", form.serialize(), function (resp) {
            try {
                var data = JSON.parse(resp);
                if (data.status == 1) {
                    alertMsg("Answer saved", "success", "Success", "");
                    // ✅ Lock the form once submitted
                    form.find("input, button").prop("disabled", true);
                } else {
                    alertMsg(data.message, "error", "Error", "");
                }
            } catch (e) {
                alert("Invalid response from server");
            }
        }).always(function () {
            // ✅ Resume polling after submit
            pollInterval = setInterval(pollCurrentQuestion, 5000);
        });
    });

    // -----------------------------
    // Init on Load
    // -----------------------------
    $(document).ready(function () {
        pollCurrentQuestion(); // initial load
        pollInterval = setInterval(pollCurrentQuestion, 5000); // poll every 5s
        startTimer();
        startHeartbeat();
    });
</script>