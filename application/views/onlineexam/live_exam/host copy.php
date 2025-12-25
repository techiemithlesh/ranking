<style type="text/css">
	.radio-custom p {
		margin: 0;
	}

	#participantCount {
		font-weight: bold;
		margin-left: 15px;
	}

	#countdownBox {
		position: relative;
		z-index: 9999;
	}


	#countdownNumber {
		font-size: 64px;
		font-weight: bold;
		animation: pulse 1s infinite;
	}
	

	@keyframes pulse {
		0% {
			transform: scale(1);
			opacity: 1;
		}

		50% {
			transform: scale(1.2);
			opacity: 0.7;
		}

		100% {
			transform: scale(1);
			opacity: 1;
		}
	}
</style>
<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-list-ul"></i> <?= translate('live_exam') . " " . translate('list') ?>
		</h4>
	</header>
	<div class="panel-body">
		<h4 class="text-center mb-lg mt-lg"><span
				class="text-weight-bold"><?= translate('exam') . " " . translate('name') ?> </span> :
			<?php echo $exam->title; ?>
		</h4>
		<div class="table-responsive mb-md">
			<table class="table table-striped table-condensed mb-none">
				<tbody>
					<tr>
						<th><?= translate('start_time') ?></th>
						<td><?php echo _d($exam->exam_start) . "<p class='text-muted'>" . date("h:i A", strtotime($exam->exam_start)); ?>
							</p>
						</td>
						<th><?= translate('end_time') ?></th>
						<td><?php echo _d($exam->exam_end) . "<p class='text-muted'>" . date("h:i A", strtotime($exam->exam_end)); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th><?= translate('class') ?></th>
						<td><?php echo $exam->class_name; ?>
							(<?php echo $this->onlineexam_model->getSectionDetails($exam->section_id); ?>)</td>
						<th><?= translate('subject') ?></th>
						<td><?php echo str_replace('<br>', ' ', $this->onlineexam_model->getSubjectDetails($exam->subject_id)); ?>
						</td>
					</tr>
					<tr>
						<th><?= translate('total') . " " . translate('question') ?></th>
						<td><?php echo $exam->questions_qty; ?></td>
						<th><?= translate('duration') ?></th>
						<td><?php echo $exam->duration; ?></td>
					</tr>
					<tr>
						<th><?= translate('exam') . " " . translate('total_attempt') ?></th>
						<td><?php echo $exam->limits_participation; ?></td>
					</tr>
					<tr>
						<th><?= translate('passing_mark') ?> </th>
						<td><?php echo $exam->passing_mark . ($exam->mark_type == 1 ? ' (%)' : ''); ?></td>
						<th><?= translate('negative_mark') ?></th>
						<td><?php echo ($exam->neg_mark == 1) ? translate('yes') : translate('no'); ?></td>
					</tr>

				</tbody>
			</table>
		</div>
		<span class="text-weight-bold"><?= translate('instruction') ?> :</span>
		<p><?php echo $exam->instruction; ?></p>
		<div class="text-center">
			<button class="btn btn-default btn-lg mt-lg start_btn" data-examid="<?php echo $exam->id; ?>"
				data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing"><i
					class="fas fa-computer-mouse"></i> <?= translate('host_exam') ?></button>

		</div>
	</div>
</section>
<div class="questionmodal">
	<div id="examModal" class="modal fade" role="dialog">
		<div class="modal-dialog modal-dialogfullwidth">
			<!-- Modal content-->
			<div class="modal-content modal-contentfull">
				<div class="modal-header">
					<button type="button" class="close questionclose" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"><i class="fas fa-users-between-lines"></i> <?php echo $exam->title ?></h4>
				</div>
				<div class="modal-body">
					<div id="hostGraceBox" class="alert alert-warning text-center" style="display:none;">
						⚠️ Connection lost. Reconnecting…
						<br>
						<strong>Time left:</strong> <span id="graceTimer"></span> sec
					</div>
					<div class="row">
						<div class="col-md-5">
							<div id="sessionInfo" class="alert alert-info mt-md"></div>
						</div>
					</div>

					<div id="countdownBox" class="text-center" style="display:none;">
						<h2 class="text-success">
							🚀 Exam starting in
							<span id="countdownNumber">10</span>
						</h2>
					</div>

					<div id="exam_questions"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	/* =====================================================
   		GLOBAL STATE
	===================================================== */

	let examLive = false;
	let sessionEndedManually = false;

	let totalQuestions = 0;
	let currentStep = 1;
	let elapsed_seconds = 0;

	let timerInterval = null;
	let heartbeatTimer = null;

	window._live_session = null;

	const examDuration = "<?= $exam->duration ?>";
	const graceSeconds = <?= (int)$grace_seconds ?>;

	let waitingPoller = null;
	var participantTimer = null;


	/* =====================================================
	   UI HELPERS
	===================================================== */

	function setWaitingUI() {
		examLive = false;


		$("#goLiveBtn").show();
		$("#countdownBox").hide();
		$("#exam_questions").show();

		$("#prevbutton, #nextbutton, #end_session_btn").hide();
		$(".que_btn").addClass("disabled").css("pointer-events", "none");
		$('#sessionInfo').show();

		// ✅ START WAITING PARTICIPANT POLL
		if (waitingPoller) clearInterval(waitingPoller);
		waitingPoller = setInterval(fetchParticipants, 4000);

		// stop heartbeat if running
		if (heartbeatTimer) clearInterval(heartbeatTimer);
	}

	function setStartingUI() {
		examLive = false;

		$("#goLiveBtn").hide();
		$("#exam_questions").hide();
		$("#countdownBox").show();

		$("#prevbutton, #nextbutton, #end_session_btn").hide();
		$('#sessionInfo').hide();

		if (waitingPoller) clearInterval(waitingPoller);
	}

	function setLiveUI() {
		examLive = true;

		$("#goLiveBtn").hide();
		$("#countdownBox").hide();
		$("#exam_questions").show();


		$("#prevbutton, #nextbutton, #end_session_btn").show();
		$(".que_btn").removeClass("disabled").css("pointer-events", "auto");
		$('#sessionInfo').show();

		if (waitingPoller) clearInterval(waitingPoller);
	}

	/* =========================
	   INIT / RESUME
	========================= */

	$(document).ready(function() {

		const hasActiveSession = <?= $active_session ? 'true' : 'false' ?>;
		const resumeElapsed = <?= (int)$elapsed_seconds ?>;

		if (!hasActiveSession) return;

		$("#examModal").modal({
			show: true,
			backdrop: "static",
			keyboard: false
		});

		$.post(base_url + "LiveExam/ajaxGetQuestions", {
			exam_id: <?= (int)$exam->id ?>
		}, handleSessionResponse, "json");
	});

	/* =====================================================
	   INIT (HOST BUTTON CLICK)
	   IMPORTANT: Create session FIRST
	===================================================== */

	$(document).on("click", ".start_btn", function() {

		const examID = $(this).data("examid");

		$("#examModal").modal({
			show: true,
			backdrop: "static",
			keyboard: false
		});

		// STEP 1: CREATE SESSION
		$.post(base_url + "LiveExam/startSession", {
			exam_id: examID
		}, function(resp) {

			if (resp.status !== 1) {
				alert("Failed to start session");
				return;
			}

			window._live_session = {
				id: resp.session_id,
				session_code: resp.session_code,
				join_link: resp.join_link
			};

			renderSessionInfo(resp);

			// STEP 2: LOAD QUESTIONS
			loadExam(examID);


		}, "json");
	});

	function renderSessionInfo(resp) {
		$("#sessionInfo").html(`
		<p><strong>Session Code:</strong> ${resp.session_code}</p>
		<p><strong>Join Link:</strong>
		<input type="text" value="${resp.join_link}" readonly style="width:80%;"></p>
		<p class="text-muted">Share the session code or join link with your students to let them join the live exam.</p>
		`);
	}

	/* =====================================================
	   LOAD / RESUME EXAM
	===================================================== */

	function loadExam(examID) {

		$.post(base_url + "LiveExam/ajaxGetQuestions", {
			exam_id: examID
		}, function(resp) {

			if (resp.status !== 1) {
				alert(resp.message || "Session not found");
				return;
			}

			$("#exam_questions").html(resp.page);


			totalQuestions = $(".step-pane").length;
			currentStep = parseInt(resp.current_index || 1);

			syncWizardUI(currentStep);

			// --- STATE HANDLING ---
			if (resp.session_status === "waiting") {
				setWaitingUI();
			} else if (resp.session_status === "starting") {
				setStartingUI();
				startCountdown(resp.go_live_at);
			} else if (resp.session_status === "active") {
				setLiveUI();
				startTimer();
				startHeartbeat();
			} else if (resp.session_status === "ended") {
				alert("This live exam session has ended.");
				$("#examModal").modal("hide");
			} else if (resp.session_status === "aborted") {
				alert("This live exam session was aborted by the host.");
				window.location.reload();

			}

		}, "json");
	}

	/* =====================================================
	   GO LIVE (HOST)
	===================================================== */

	$(document).on("click", "#goLiveBtn", function() {

		if (!confirm("⚠️ Are you sure you want to GO LIVE?\n\nThis will start the exam for all students.")) {
			return;
		}

		const firstQid = $(".step-pane[data-step='1']").data("question-id");

		$.post(base_url + "LiveExam/goLive", {
			session_id: window._live_session.id,
			first_question_id: firstQid
		}, function(resp) {

			if (resp.status !== 1) return;

			setStartingUI();
			startCountdown(resp.go_live_at);

		}, "json");
	});

	/* =====================================================
	   COUNTDOWN (10 SECONDS)
	===================================================== */

	function startCountdown(goLiveAt) {

		// const target = new Date(goLiveAt.replace(" ", "T")).getTime();
		const target = new Date(goLiveAt.replace(/-/g, "/")).getTime();

		$("#countdownBox").show();

		const interval = setInterval(function() {

			const now = Date.now();
			const diff = Math.ceil((target - now) / 1000);

			$("#countdownNumber").text(diff > 0 ? diff : 0);

			if (diff <= 0) {
				clearInterval(interval);

				// Re-check server to confirm ACTIVE
				loadExam(<?= (int)$exam->id ?>);
			}

		}, 1000);
	}

	function handleSessionResponse(resp) {

		if (resp.status !== 1) return;

		$("#exam_questions").html(resp.page);

		totalQuestions = $(".step-pane").length;
		currentStep = parseInt(resp.current_index || 1);
		elapsed_seconds = <?= (int)$elapsed_seconds ?>;

		if (!window._live_session && resp.session_id) {
			window._live_session = {
				id: resp.session_id,
				session_code: resp.session_code,
				join_link: resp.join_link
			};
			renderSessionInfo(resp); // make session visible on refresh
		}


		syncWizardUI(currentStep);

		if (resp.session_status === "active") {

			clearAllWaitingTimers();

			examLive = true;
			$("#countdownBox").hide();
			$("#exam_questions").show();

			setLiveUI();
			startTimer();
			startHeartbeat();
			fetchAnswers();

		} else if (resp.session_status === "starting") {

			examLive = false;
			setWaitingUI();
			$("#exam_questions").hide();
			startHostCountdown(resp.go_live_at);

		} else if (resp.status_code === "aborted") {

			examLive = false;
			setWaitingUI();
			startWaitingPolling();

		} else {
			examLive = false;
			setWaitingUI();
			startWaitingPolling();
		}
	}

	/* =====================================================
	   NAVIGATION
	===================================================== */

	function syncWizardUI(step) {

		$(".step-pane").removeClass("active");
		$(".step-pane[data-step='" + step + "']").addClass("active");

		$(".que_btn").removeClass("active");
		$("#question" + step).addClass("active");

		updateNavButtons();
	}

	function updateNavButtons() {
		if (currentStep <= 1) $("#prevbutton").addClass("disabled").css("pointer-events", "none");
		else $("#prevbutton").removeClass("disabled").css("pointer-events", "auto");

		if (currentStep >= totalQuestions) $("#nextbutton").addClass("disabled").css("pointer-events", "none");
		else $("#nextbutton").removeClass("disabled").css("pointer-events", "auto");
	}


	function showStep(step) {

		if (step < 1 || step > totalQuestions) return;

		currentStep = step;
		syncWizardUI(step);

		updateNavButtons();

		if (examLive) {
			const qid = $(".step-pane[data-step='" + step + "']").data("question-id");

			$.post(base_url + "LiveExam/setCurrentQuestion", {
				session_id: window._live_session.id,
				question_id: qid
			});

			fetchAnswers();
		}
	}

	$(document).on("click", "#prevbutton", () => showStep(currentStep - 1));
	$(document).on("click", "#nextbutton", () => showStep(currentStep + 1));
	$(document).on("click", ".que_btn", function() {
		showStep(parseInt(this.id.replace("question", "")));
	});

	/* =====================================================
	   TIMER
	===================================================== */

	function startTimer() {

		if (timerInterval) clearInterval(timerInterval);

		timerInterval = setInterval(function() {

			if (!examLive) return;

			elapsed_seconds++;

			const parts = examDuration.split(":").map(Number);
			const total = parts[0] * 3600 + parts[1] * 60 + parts[2];
			const remain = total - elapsed_seconds;

			if (remain <= 0) {
				clearInterval(timerInterval);
				$(".remain_duration").text("00:00:00");
				return;
			}

			const h = String(Math.floor(remain / 3600)).padStart(2, "0");
			const m = String(Math.floor((remain % 3600) / 60)).padStart(2, "0");
			const s = String(remain % 60).padStart(2, "0");

			$(".remain_duration").text(`${h}:${m}:${s}`);

		}, 1000);
	}

	/* =====================================================
	   HEARTBEAT + ANSWER + PARTICIPANTS (ONLY WHEN ACTIVE BUT FETCH PARTICIPANTS ALWAYS)
	===================================================== */

	function startHeartbeat() {

		if (heartbeatTimer) clearInterval(heartbeatTimer);

		heartbeatTimer = setInterval(function() {

			if (!examLive) return;

			$.post(base_url + "LiveExam/sessionHeartbeat", {
				session_id: window._live_session.id
			});

			fetchParticipants();
			if (examLive) {
				fetchAnswers();
			}

		}, 5000);
	}

	function fetchParticipants() {

		if (!window._live_session?.id) return;


		$.getJSON(base_url + "LiveExam/getParticipants", {
			session_id: window._live_session.id
		}, function(resp) {

			if (resp.status !== 1) return;

			let html = resp.participants.length ?
				resp.participants.map(p =>
					`<li>${p.student_name}
                <span class="badge badge-success ml-2">${p.live_status}</span></li>`
				).join("") :
				`<li class="text-muted">No participants yet</li>`;
			$("#participantCount").text(resp.total + " / " + <?= (int)$student_count ?>);
			$("#host_participants_list").html(html);
		});
	}

	function fetchAnswers() {

		if (!examLive || !window._live_session?.id) return;

		const qid = $(".step-pane[data-step='" + currentStep + "']").data("question-id");
		if (!qid) return;

		$.getJSON(base_url + "LiveExam/getSessionAnswers", {
			session_id: window._live_session.id,
			question_id: qid
		}, function(resp) {

			if (resp.status !== 1) return;

			let html = resp.data.length ?
				resp.data.map(a =>
					`<tr>
                    <td>${a.student_name}</td>
                    <td>${a.answer}</td>
                    <td>${a.submitted_at}</td>
                 </tr>`
				).join("") :
				`<tr><td colspan="3" class="text-center text-muted">No answers yet</td></tr>`;

			$("#host_answers_table tbody").html(html);
		});
	}

	/* =====================================================
	   END SESSION (FULL)
	===================================================== */

	$(document).on("click", "#end_session_btn", function() {
		if (!confirm("Are you sure you want to end this live exam session?")) return;
		sessionEndedManually = true;
		endLiveSession();
	});

	$("#examModal").on("hidden.bs.modal", function() {
		if (window._live_session?.id && !sessionEndedManually) {
			endLiveSession(true);
		}
	});

	function endLiveSession(aborted = false) {

		if (!window._live_session?.id) return;

		let publish = 0;
		if (!aborted && confirm("Do you want to publish the results now?")) {
			publish = 1;
		}

		$.post(base_url + "LiveExam/endSession", {
			session_id: window._live_session.id,
			aborted: aborted ? 1 : 0,
			publish: publish
		}, function(resp) {

			let data = {};
			try {
				data = typeof resp === "string" ? JSON.parse(resp) : resp;
			} catch (e) {}

			$("#examModal").modal("hide");
			clearAllTimers();

			if (publish === 1 && data.redirect_url) {
				setTimeout(() => window.location.href = data.redirect_url, 500);
			}
		}, "json");
	}

	function clearAllWaitingTimers() {
		clearInterval(waitingPoller);
		waitingPoller = null;
	}

	function clearAllTimers() {
		clearInterval(timerInterval);
		clearInterval(heartbeatTimer);
		clearInterval(participantTimer);
		clearInterval(waitingPoller);
	}
</script>