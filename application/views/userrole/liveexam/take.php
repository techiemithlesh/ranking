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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .exam-header .timer {
        font-size: 16px;
        font-weight: 600;
    }

    .exam-header .live {
        color: #d32f2f;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #sync_indicator {
        font-size: 12px;
        transition: color 0.3s;
    }

    /* Content */
    .exam-content {
        flex: 1;
        max-width: 1100px;
        width: 100%;
        margin: auto;
        padding: 12px;
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
        padding: 20px;
        min-height: 300px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    /* Media Handling */
    #question_area iframe {
        width: 100% !important;
        aspect-ratio: 16 / 9;
        border-radius: 8px;
        border: 0;
        margin-bottom: 15px;
    }

    #question_area img {
        max-width: 100% !important;
        height: auto !important;
        border-radius: 8px;
        margin: 10px auto;
        display: block;
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
        transition: all 0.2s;
        cursor: pointer;
    }

    .option-card:hover {
        border-color: #bbb;
        background: #f0f2f5;
    }

    .option-card input {
        transform: scale(1.4);
        margin-right: 15px;
    }

    .option-card.active {
        border-color: #4caf50;
        background: #e8f5e9;
    }

    /* Footer */
    .exam-footer {
        background: #ffffff;
        padding: 15px;
        text-align: center;
        border-top: 1px solid #ddd;
    }

    .exam-footer button {
        padding: 12px 40px;
        font-size: 18px;
        border-radius: 10px;
    }

    /* Countdown Styling */
    .countdown-display {
        padding: 60px 20px;
        text-align: center;
    }

    .countdown-number {
        font-size: 72px;
        font-weight: 800;
        color: #d32f2f;
        line-height: 1;
        margin-bottom: 10px;
    }
</style>

<section class="panel">
    <div class="exam-wrapper">
        <div class="exam-header">
            <div class="timer">
                ⏱ <span id="remain_time"><?= $exam->duration ?></span>
            </div>
            <div class="live">
                <span id="sync_indicator">● Connected</span>
                🔴 LIVE EXAM
            </div>
        </div>

        <div class="exam-content">
            <div class="question-count">
                <?= translate('question') ?>
                <span id="current_q">–</span> / <?= $exam->questions_qty ?>
            </div>

            <div id="question_area">
                <div class="alert alert-info text-center p-lg">
                    <i class="fas fa-spinner fa-spin"></i> <?= translate('waiting_for_host') ?>...
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    var session_id = "<?= $session->id ?>";
    var pollInterval = null;
    var lastVersion = 0;
    var pollInProgress = false;
    var heartbeatTimer = null;
    var startingCountdownInterval = null;
    var timerInterval = null; // Reference for the exam duration clock
    var elapsed_seconds = 0; // Current elapsed time in seconds
    var currentHtmlHash = ""; // To track content changes
    var disconnectPopupTimeout = null;

    var disconnectPopupTimeout = null;

    function showDisconnectWarning() {
        if (disconnectPopupTimeout) return;
        disconnectPopupTimeout = setTimeout(function() {
            Swal.fire({
                icon: 'warning',
                title: "Connection Lost",
                text: "Trying to reconnect...",
                showConfirmButton: false,
                allowOutsideClick: false
            });
        }, 12000);
    }

    function hideDisconnectWarning() {
        clearTimeout(disconnectPopupTimeout);
        disconnectPopupTimeout = null;
        Swal.close();
    }

    /* Force retry if stuck in disconnect mode */
    setInterval(function() {
        if ($("#sync_indicator").text().includes("Reconnecting")) pollCurrentQuestion();
    }, 5000);



    /* ---------- POLL LOGIC ---------- */
    function pollCurrentQuestion() {
        if (pollInProgress) return;
        pollInProgress = true;

        $.getJSON(base_url + "Liveexam_student/getCurrentQuestion", {
                session_id: session_id,
                last_version: lastVersion
            })
            .done(function(resp) {
                hideDisconnectWarning();
                $("#sync_indicator").css("color", "#4caf50").text("● Connected");

                // 1. Handle Status: COMPLETED
                if (resp.code === "completed") {
                    stopAllTimers();
                    handleExamEnd(resp);
                    return;
                }

                // 2. Handle Status: ABORTED
                if (resp.code === "aborted") {
                    stopAllTimers();
                    swal("Aborted", "The exam was aborted by the host.", "warning").then(() => {
                        window.location.href = base_url + "liveexam_student";
                    });
                    return;
                }

                // 3. Handle Status: STARTING (Countdown)
                if (resp.code === "starting") {
                    if (resp.go_live_at) {
                        startStartingCountdown(resp.go_live_at);

                    } else {
                        updateQuestionArea('<div class="alert alert-info text-center">' + resp.message + '</div>');
                    }
                    return;
                }

                // 4. Handle Status: ACTIVE (Question display)
                if (resp.status === 1) {
                    if (resp.current_step_version !== undefined) lastVersion = resp.current_step_version;
                    if (resp.current_index !== undefined) $("#current_q").text(resp.current_index);

                    if (resp.html) {
                        updateQuestionArea(resp.html, resp.current_step);
                    }
                }

                // --- TIMER SYNC LOGIC ---
                if (resp.elapsed_seconds !== undefined) {
                    let serverElapsed = parseInt(resp.elapsed_seconds || 0);

                    // Only restart the timer if it's not running, 
                    // OR if the local clock is more than 3 seconds out of sync
                    if (!timerInterval || Math.abs(elapsed_seconds - serverElapsed) > 3) {
                        startTimer(serverElapsed);
                    }
                }
            })
            .fail(function() {
                $("#sync_indicator").css("color", "#f44336").text("● Reconnecting...");
                showDisconnectWarning();
                setTimeout(()=>pollCurrentQuestion(), 3000);
            })
            .always(function() {
                pollInProgress = false;
            });
    }

    /* ---------- SMART UI UPDATE ---------- */
    function updateQuestionArea(newHtml, qid = null) {
        // Only update and animate if the HTML content is actually different
        if (currentHtmlHash !== newHtml) {
            currentHtmlHash = newHtml;

            $("#question_area").fadeOut(200, function() {
                $(this).html(newHtml).fadeIn(300);
                if (qid) $(this).data("qid", qid);
                normalizeYouTubeEmbeds();
            });
        }
    }

    /* ---------- COUNTDOWN ---------- */
    function startStartingCountdown(goLiveAt) {
        if (startingCountdownInterval) return;

        // Cross-browser safe date parsing (Asia/Kolkata compatible)
        const target = new Date(goLiveAt.replace(/-/g, "/")).getTime();

        startingCountdownInterval = setInterval(function() {
            const now = new Date().getTime();
            const diff = Math.ceil((target - now) / 1000);

            if (diff <= 0) {
                clearInterval(startingCountdownInterval);
                startingCountdownInterval = null;
                updateQuestionArea('<div class="alert alert-success text-center">🚀 Exam is starting now! Loading...</div>');
                pollCurrentQuestion();
            } else {
                let countdownHtml = `
                    <div class="countdown-display">
                        <div class="countdown-number">${diff}</div>
                        <p class="text-muted">The Host is preparing the first question. Get ready!</p>
                    </div>`;
                // Use direct HTML update for countdown to avoid fade flickering every second
                if ($("#question_area .countdown-number").length > 0) {
                    $(".countdown-number").text(diff);
                } else {
                    $("#question_area").html(countdownHtml);
                }
            }
        }, 1000);
    }

    /* ---------- HELPERS ---------- */
    function handleExamEnd(resp) {
        if (resp.is_published == 1) {
            window.location.href = base_url + "Liveexam_student/leaderboard/" + resp.session_code;
        } else {
            swal("Thank You!", "Exam completed. Results will be published soon.", "success").then(() => {
                window.location.href = base_url + "liveexam_student";
            });
        }
    }

    function stopAllTimers() {
        clearInterval(pollInterval);
        clearInterval(heartbeatTimer);
        clearInterval(startingCountdownInterval);
    }

    function startTimer(serverOffset = 0) {

        if (timerInterval && Math.abs(elapsed_seconds - serverOffset) < 3) return;

        elapsed_seconds = serverOffset;

        if (timerInterval) clearInterval(timerInterval);

        const duration = "<?= $exam->duration ?>"; // HH:MM:SS
        const parts = duration.split(":").map(Number);
        const totalSeconds = parts[0] * 3600 + parts[1] * 60 + parts[2];

        timerInterval = setInterval(function() {

            elapsed_seconds++;
            let remaining = totalSeconds - elapsed_seconds;

            if (remaining <= 0) {
                clearInterval(timerInterval);
                $("#remain_time").text("00:00:00");
                return;
            }

            const h = String(Math.floor(remaining / 3600)).padStart(2, "0");
            const m = String(Math.floor((remaining % 3600) / 60)).padStart(2, "0");
            const s = String(remaining % 60).padStart(2, "0");

            $("#remain_time").text(`${h}:${m}:${s}`);
        }, 1000);
    }

    function startHeartbeat() {
        heartbeatTimer = setInterval(function() {
            $.post(base_url + "Liveexam_student/studentHeartbeat", {
                session_id: session_id
            });
        }, 10000);
    }

    function normalizeYouTubeEmbeds() {
        $("#question_area iframe").each(function() {
            let src = $(this).attr("src");
            if (src && !src.includes("autoplay")) {
                $(this).attr("src", src.split("?")[0] + "?autoplay=1&controls=0&rel=0");
            }
        });
    }

    /* ---------- FORM SUBMISSION ---------- */
    $(document).on('submit', '#answerForm', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find("button[type='submit']");

        submitBtn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.post(base_url + "Liveexam_student/submitAnswer", form.serialize(), function(resp) {
            var data = JSON.parse(resp);
            if (data.status == 1) {
                swal({
                    title: "Saved!",
                    text: "Your answer has been recorded.",
                    type: "success",
                    timer: 1500,
                    showConfirmButton: false
                });
                form.find("input").prop("disabled", true);
                submitBtn.text("Answered").addClass("btn-success");
            } else {
                swal("Error", data.message, "error");
                submitBtn.prop("disabled", false).text("Submit Answer");
            }
        }).fail(function() {
            swal("Error", "Connection lost. Try again.", "error");
            submitBtn.prop("disabled", false).text("Submit Answer");
        });
    });

    /* ---------- INITIALIZE ---------- */
    $(document).ready(function() {
        pollCurrentQuestion();
        pollInterval = setInterval(pollCurrentQuestion, 5000);
        startHeartbeat();
    });
</script>