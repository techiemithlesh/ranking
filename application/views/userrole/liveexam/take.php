<style>
    body {
        background: #f4f7fb;
    }

    /* Layout */
    .exam-wrapper {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        font-family: "Nunito", Arial, sans-serif;
    }

    /* Header */
    .exam-header {
        position: sticky;
        top: 0;
        z-index: 100;
        background: #fff;
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #ddd;
    }

    .exam-header .timer {
        font-size: 16px;
        font-weight: 600;
    }

    .exam-header .live {
        color: #d32f2f;
        font-weight: 700;
    }

    /* Content */
    .exam-content {
        flex: 1;
        max-width: 1100px;
        /* was 900px */
        width: 100%;
        margin: auto;
        padding: 12px;
        /* slightly reduced */
    }

    .question-count {
        text-align: center;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    /* Question box */
    #question_area {
        background: #ffffff;
        border-radius: 14px;
        padding: 16px;
        min-height: 280px;
    }

    /* Media */
    #question_area iframe {
        width: 100% !important;
        height: auto;
        aspect-ratio: 16 / 9;
        border: 0;
        display: block;
        background: #000;
    }

    /* Images */
    #question_area img {
        max-width: 100% !important;
        height: auto !important;
        display: block;
        margin: 0 auto;
    }

    #question_area p,
    #question_area div {
        margin-bottom: 12px;
    }

    /* Option cards */
    .option-card {
        background: #f9fafb;
        border: 2px solid #ddd;
        border-radius: 12px;
        padding: 14px;
        font-size: 18px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .option-card input {
        transform: scale(1.4);
        margin-right: 12px;
    }

    .option-card.active {
        border-color: #4caf50;
        background: #e8f5e9;
    }

    /* Footer */
    .exam-footer {
        position: sticky;
        bottom: 0;
        background: #ffffff;
        padding: 12px;
        text-align: center;
        border-top: 1px solid #ddd;
    }

    .exam-footer button {
        padding: 14px 42px;
        font-size: 18px;
        border-radius: 10px;
    }

    @media (min-width: 992px) {
        #question_area {
            padding: 14px;
        }
    }
</style>



<section class="panel">
    <div class="exam-wrapper">
        <!-- HEADER -->
        <div class="exam-header">
            <div class="timer">
                ⏱ <span id="remain_time"><?= $exam->duration ?></span>
            </div>
            <div class="live">🔴 LIVE EXAM</div>
        </div>

        <!-- CONTENT -->
        <div class="exam-content">
            <div class="question-count">
                <?= translate('question') ?>
                <span id="current_q">–</span> / <?= $exam->questions_qty ?>
            </div>

            <div id="question_area">
                <div class="alert alert-info text-center p-lg">
                    <?= translate('waiting_for_host') ?>...
                </div>
            </div>
        </div>

    </div>

</section>


<script>
    var session_id = "<?= $session->id ?>";
    var pollInterval = null;
    var lastVersion = 0;
    var pollInProgress = false;
    var heartbeatTimer = null;
    var startingCountdownInterval = null;

    /* ---------- POLL ---------- */
    function pollCurrentQuestion() {

        if (pollInProgress) return;
        pollInProgress = true;

        $.getJSON(base_url + "Liveexam_student/getCurrentQuestion", {
                session_id: session_id,
                last_version: lastVersion
            })
            .done(function(resp) {

                console.log("Poll response:", resp);

                /* ================= ACTIVE ================= */
                if (resp.status === 1) {

                    if (resp.current_step_version !== undefined) {
                        lastVersion = resp.current_step_version;
                    }

                    if (resp.html) {
                        $("#question_area")
                            .html(resp.html)
                            .data("qid", resp.current_step);

                        normalizeYouTubeEmbeds();
                    }


                    if (resp.current_index !== undefined) {
                        $("#current_q").text(resp.current_index);
                    }

                    pollInProgress = false;
                    return;
                }

                /* ================= COMPLETED ================= */
                if (resp.code === "completed") {

                    clearInterval(pollInterval);
                    clearInterval(heartbeatTimer);

                    if (resp.is_published == 1) {
                        window.open(base_url + "Liveexam_student/leaderboard/" + resp.session_code);

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

                    pollInProgress = false;
                    return;
                }

                /* ================= ABORTED ================= */
                if (resp.code === "aborted") {

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

                    pollInProgress = false;
                    return;
                }

                /* ================= WAITING ================= */
                if (resp.code === "waiting") {

                    $("#question_area").html(
                        '<div class="alert alert-info text-center">' + resp.message + '</div>'
                    );
                    pollInProgress = false;
                    return;
                }

                if (resp.code === "starting") {

                    if (resp.go_live_at) {
                        startStartingCountdown(resp.go_live_at);
                    } else {
                        $("#question_area").html(
                            '<div class="alert alert-info text-center">' + resp.message + '</div>'
                        );
                    }

                    $("#question_area").html(
                        '<div class="alert alert-info text-center">' + resp.message + '</div>'
                    );
                    pollInProgress = false;
                    return;
                }

                /* ================= FALLBACK (DO NOTHING) ================= */
                pollInProgress = false;

            })
            .fail(function() {
                pollInProgress = false;
            });
    }

    function startStartingCountdown(goLiveAt) {
        // Prevent multiple intervals from running
        if (startingCountdownInterval) return;

        const target = new Date(goLiveAt.replace(" ", "T")).getTime();

        startingCountdownInterval = setInterval(function() {
            const now = new Date().getTime();
            const diff = Math.ceil((target - now) / 1000);

            if (diff <= 0) {
                clearInterval(startingCountdownInterval);
                startingCountdownInterval = null;
                $("#question_area").html(
                    '<div class="alert alert-success text-center">🚀 Exam is starting now! Loading...</div>'
                );
                // Force an immediate poll to get the first question
                pollCurrentQuestion();
            } else {
                $("#question_area").html(`
                <div class="text-center" style="padding: 40px;">
                    <h2 style="color: #d32f2f; font-weight: 800; font-size: 48px;">${diff}</h2>
                    <p class="text-muted">The Host is preparing the first question. Get ready!</p>
                </div>
            `);
            }
        }, 1000);
    }

    function startTimer() {
        elapsed_seconds = 0;
        var duration = "<?= $exam->duration ?>"; // HH:MM:SS
        var parts = duration.split(":");
        var totalSeconds = (+parts[0] * 3600) + (+parts[1] * 60) + (+parts[2]);

        var timerInterval = setInterval(function() {
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


    /* ---------- HEARTBEAT ---------- */
    function startHeartbeat() {
        heartbeatTimer = setInterval(function() {
            $.post(base_url + "Liveexam_student/studentHeartbeat", {
                session_id: session_id
            });
        }, 10000);
    }

    /* ---------- YOUTUBE FIX ---------- */
    function normalizeYouTubeEmbeds() {
        $("#question_area iframe").each(function() {
            let src = $(this).attr("src");
            if (!src) return;
            src = src.split("?")[0] + "?autoplay=1&controls=0&rel=0";
            $(this).attr("src", src);
        });
    }

    $(document).on('submit', '#answerForm', function(e) {
        e.preventDefault();
        var form = $(this);

        // ✅ Prevent poll overwrite during submission
        clearInterval(pollInterval);

        $.post(base_url + "Liveexam_student/submitAnswer", form.serialize(), function(resp) {
            try {
                var data = JSON.parse(resp);
                if (data.status == 1) {
                    // ✅ Lock the form once submitted
                    // alertMsg("Answer saved", "success", "Success", "");
                    // form.find("input, button").prop("disabled", true);
                    swal({
                        title: "Saved!",
                        text: "Your answer has been recorded.",
                        type: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });
                    form.find("input, button").prop("disabled", true);
                    // Optional: add a visual 'submitted' state to the card
                    form.find(".option-card.active").css("background", "#c8e6c9");
                } else {
                    alertMsg(data.message, "error", "Error", "");
                }
            } catch (e) {
                alert("Invalid response from server");
            }
        }).always(function() {
            // ✅ Resume polling after submit
            pollInterval = setInterval(pollCurrentQuestion, 5000);
        });
    });

    /* ---------- INIT ---------- */
    $(document).ready(function() {
        pollCurrentQuestion();
        pollInterval = setInterval(pollCurrentQuestion, 5000);
        startTimer();
        startHeartbeat();
    });
</script>