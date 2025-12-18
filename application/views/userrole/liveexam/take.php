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
    var exam_id = "<?= $exam->id ?>";
    var pollInterval = null;
    var elapsed_seconds = 0;
    var heartbeatTimer = null;
    var lastVersion = 0;
    var pollInProgress = false;


    /* Leave session */
    window.addEventListener("beforeunload", function() {
        navigator.sendBeacon(
            base_url + "Liveexam_student/leaveSession",
            new URLSearchParams({
                session_id: session_id
            })
        );
    });

    /* Poll current question (HOST CONTROLLED) */
    function pollCurrentQuestion() {

        if (pollInProgress) return; // 🚫 block overlap
        pollInProgress = true;

        $.getJSON(base_url + "Liveexam_student/getCurrentQuestion", {
            session_id: session_id,
            last_version: lastVersion
        }, function(resp) {

            /* ---------- STATUS 1 ---------- */
            if (resp.status === 1) {

                // 🔹 No change → stop immediately
                if (resp.changed === false) {
                    pollInProgress = false;
                    return;
                }

                // 🔹 Update version FIRST
                if (typeof resp.current_step_version !== "undefined") {
                    lastVersion = resp.current_step_version;
                }

                // 🔹 Render only if question actually changed
                if ($("#question_area").data("qid") !== resp.current_step && resp.html) {
                    $("#question_area").fadeOut(150, function() {
                        $(this)
                            .html(resp.html)
                            .data("qid", resp.current_step)
                            .fadeIn(150, normalizeYouTubeEmbeds);
                    });

                    $("#submitBtn").prop("disabled", true);
                }

                // 🔹 Update question counter
                if (resp.current_index) {
                    $("#current_q").text(resp.current_index);
                }

                pollInProgress = false; // ✅ FIX
                return;
            }

            /* ---------- STATUS 0 (END STATES) ---------- */
            if (resp.code === "completed") {
                clearInterval(pollInterval);
                clearInterval(heartbeatTimer);
                pollInProgress = false;

                window.open(
                    base_url + "Liveexam_student/leaderboard/" + resp.session_code,
                    "_blank"
                );

                setTimeout(() => {
                    window.location.href = base_url + "liveexam_student";
                }, 8000);

                return;
            }

            if (resp.code === "aborted") {
                clearInterval(pollInterval);
                clearInterval(heartbeatTimer);
                pollInProgress = false;

                swal({
                    text: "The exam was aborted by the host.",
                    type: "warning",
                    confirmButtonText: "OK",
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = base_url + "liveexam_student";
                });

                return;
            }

            // 🔹 Fallback
            $("#question_area").html(
                '<div class="alert alert-info text-center">' +
                (resp.message || "Waiting for host...") +
                '</div>'
            );

            pollInProgress = false;

        }).fail(function() {
            pollInProgress = false;
        });
    }



    /* Timer */
    function startTimer() {
        var duration = "<?= $exam->duration ?>";
        var parts = duration.split(":");
        var totalSeconds =
            (+parts[0] * 3600) + (+parts[1] * 60) + (+parts[2]);

        setInterval(function() {
            elapsed_seconds++;
            var remaining = totalSeconds - elapsed_seconds;

            if (remaining <= 0) {
                $("#remain_time").text("00:00:00");
                return;
            }

            var h = Math.floor(remaining / 3600);
            var m = Math.floor((remaining % 3600) / 60);
            var s = remaining % 60;

            $("#remain_time").text(
                String(h).padStart(2, "0") + ":" +
                String(m).padStart(2, "0") + ":" +
                String(s).padStart(2, "0")
            );
        }, 1000);
    }

    /* Heartbeat */
    function startHeartbeat() {
        heartbeatTimer = setInterval(function() {
            $.post(base_url + "Liveexam_student/studentHeartbeat", {
                session_id: session_id
            });
        }, 10000);
    }

    /* Option select */
    $(document).on("click", ".option-card", function() {
        $(".option-card").removeClass("active");
        $(this).addClass("active");
        $(this).find("input").prop("checked", true).trigger("change");
    });

    $(document).on("change", "input[name='answer']", function() {
        $("#submitBtn").prop("disabled", false);
    });

    /* Submit answer */
    $(document).on("submit", "#answerForm", function(e) {
        e.preventDefault();
        var form = $(this);

        $.post(
            base_url + "Liveexam_student/submitAnswer",
            form.serialize(),
            function(resp) {
                try {
                    var data = JSON.parse(resp);
                    if (data.status == 1) {
                        form.find("input, button").prop("disabled", true);
                        $("#submitBtn").prop("disabled", true);
                    }
                } catch (e) {
                    alert("Invalid response from server");
                }
            }
        );
    });

    function normalizeYouTubeEmbeds() {
        $("#question_area iframe").each(function() {
            let src = $(this).attr("src");
            if (!src) return;

            // Only apply to YouTube
            if (src.includes("youtube.com") || src.includes("youtu.be")) {

                // Remove existing params
                src = src.split("?")[0];

                // Add clean params
                src += "?autoplay=1&mute=1&controls=0&rel=0&modestbranding=1&playsinline=1";

                $(this).attr("src", src);
                $(this).attr("allow", "autoplay; encrypted-media");
            }
        });
    }


    /* Init */
    $(document).ready(function() {
        pollCurrentQuestion();
        normalizeYouTubeEmbeds();
        pollInterval = setInterval(pollCurrentQuestion, 5000);
        startTimer();
        startHeartbeat();
    });
</script>