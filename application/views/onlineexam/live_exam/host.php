<style type="text/css">
	.radio-custom p {
		margin: 0;
	}
</style>
<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-list-ul"></i> <?= translate('online_exam') . " " . translate('list') ?>
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
					<div id="exam_questions"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	/* =========================
   GLOBAL STATE
========================= */

	var examDuration = "<?= $exam->duration ?>";
	var totalQuestions = 0;
	var currentStep = 1;
	var elapsed_seconds = 0;
	var timerInterval = null;

	var heartbeatTimer = null;
	var graceInterval = null;

	var lastHeartbeatAt = Date.now();
	var graceSeconds = <?= (int) $grace_seconds ?>;

	let sessionEndedManually = false;
	let examLive = false;
	window._live_session = null;


	/* =========================
	   UI STATES
	========================= */

	function setWaitingUI() {

		$("#goLiveBtn").show();
		$("#waitingLabel").show();

		$("#prevbutton").hide();
		$("#nextbutton").hide();
		$("#end_session_btn").hide();

		$(".que_btn").addClass("disabled").css("pointer-events", "none");

		$("#host_answers_table").closest("section").hide();
	}

	function setLiveUI() {

		$("#goLiveBtn").hide();
		$("#waitingLabel").hide();

		// ✅ FIX: explicitly enable buttons
		$("#prevbutton").show().prop("disabled", false);
		$("#nextbutton").show().prop("disabled", false);
		$("#end_session_btn").show();

		$(".que_btn").removeClass("disabled").css("pointer-events", "auto");

		$("#host_answers_table").closest("section").show();
	}


	/* =========================
	   RESUME ON REFRESH
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
		}, function(resp) {

			if (resp.status !== 1 || resp.resume !== 1) return;

			$("#exam_questions").html(resp.page);

			window._live_session = {
				id: resp.session_id,
				session_code: resp.session_code,
				join_link: resp.join_link
			};

			$("#sessionInfo").html(`
			<p><strong>Session Code:</strong> ${resp.session_code}</p>
			<p><strong>Join Link:</strong>
			<input type="text" value="${resp.join_link}" readonly style="width:80%;"></p>
		`);

			totalQuestions = $(".step-pane").length;
			currentStep = parseInt(resp.current_index) || 1;

			syncWizardUI(currentStep);
			elapsed_seconds = resumeElapsed;

			activateSessionAndStartPolling();

			if (resp.is_live == 1) {
				examLive = true;
				setLiveUI();
				startTimer();
				startHeartbeat();
			} else {
				setWaitingUI();
			}

		}, 'json');
	});


	/* =========================
	   UI SYNC
	========================= */

	function syncWizardUI(step) {

		$(".step-pane").removeClass("active");
		$(".step-pane[data-step='" + step + "']").addClass("active");

		$(".que_btn").removeClass("active");
		$("#question" + step).addClass("active");

		$("#prevbutton").prop("disabled", step === 1);
		$("#nextbutton").prop("disabled", step === totalQuestions);
	}


	/* =========================
	   START EXAM (FRESH)
	========================= */

	$(document).on("click", ".start_btn", function() {

		var examID = $(this).data("examid");

		$.post(base_url + "LiveExam/ajaxGetQuestions", {
			exam_id: examID
		}, function(resp) {

			if (resp.status !== 1) return;

			$("#exam_questions").html(resp.page);
			totalQuestions = $(".step-pane").length;
			currentStep = 1;

			syncWizardUI(1);
			hostSessionGenerate(examID);

			$("#examModal").modal({
				show: true,
				backdrop: "static",
				keyboard: false
			});

		}, 'json');
	});


	/* =========================
	   CREATE SESSION
	========================= */

	function hostSessionGenerate(examID) {

		let qid = $(".step-pane[data-step='1']").data("question-id");

		$.post(base_url + "LiveExam/startSession", {
			exam_id: examID,
			current_question_id: qid
		}, function(resp) {

			if (resp.status !== 1) return;

			window._live_session = {
				id: resp.session_id,
				session_code: resp.session_code,
				join_link: resp.join_link
			};

			$("#sessionInfo").html(`
			<p><strong>Session Code:</strong> ${resp.session_code}</p>
			<p><strong>Join Link:</strong>
			<input type="text" value="${resp.join_link}" readonly style="width:80%;"></p>
		`);

			activateSessionAndStartPolling();

		}, 'json');
	}


	/* =========================
	   ACTIVATE SESSION (WAITING)
	========================= */

	function activateSessionAndStartPolling() {

		$.post(base_url + "LiveExam/activateSession", {
			session_id: window._live_session.id
		}, function() {

			fetchParticipants();
			setWaitingUI();

			if (heartbeatTimer) clearInterval(heartbeatTimer);
			heartbeatTimer = setInterval(function() {
				fetchParticipants();
			}, 5000);

		});
	}


	/* =========================
	   GO LIVE
	========================= */

	$(document).on("click", "#goLiveBtn", function() {

		let firstQid = $(".step-pane[data-step='1']").data("question-id");

		$.post(base_url + "LiveExam/goLive", {
			session_id: window._live_session.id,
			first_question_id: firstQid
		}, function(resp) {

			if (resp.status !== 1) return;

			examLive = true;

			setLiveUI();
			syncWizardUI(currentStep); // extra safety

			startTimer();
			startHeartbeat();
			fetchAnswers();

			alertMsg("Exam is now LIVE!", "success", "Live", "");
		}, 'json');
	});


	/* =========================
	   HEARTBEAT + GRACE
	========================= */

	function startHeartbeat() {

		if (heartbeatTimer) clearInterval(heartbeatTimer);

		heartbeatTimer = setInterval(function() {

			if (!window._live_session?.id || !examLive) return;

			$.post(base_url + "LiveExam/sessionHeartbeat", {
				session_id: window._live_session.id
			});

			lastHeartbeatAt = Date.now();
			hideGraceUI();

			fetchParticipants();
			fetchAnswers();

		}, 5000);
	}

	setInterval(function() {

		if (!window._live_session?.id || !examLive) return;

		let diff = (Date.now() - lastHeartbeatAt) / 1000;

		if (diff > 6) {
			showGraceUI();
		}

	}, 1000);

	function showGraceUI() {

		if ($("#hostGraceBox").is(":visible")) return;

		let remaining = graceSeconds;
		$("#hostGraceBox").show();
		$("#graceTimer").text(remaining);

		graceInterval = setInterval(function() {
			remaining--;
			$("#graceTimer").text(remaining);

			if (remaining <= 0) {
				clearInterval(graceInterval);
				location.reload();
			}
		}, 1000);
	}

	function hideGraceUI() {
		clearInterval(graceInterval);
		$("#hostGraceBox").hide();
	}


	/* =========================
	   PARTICIPANTS
	========================= */

	function fetchParticipants() {

		if (!window._live_session?.id) return;

		$.getJSON(base_url + "LiveExam/getParticipants", {
			session_id: window._live_session.id
		}, function(resp) {

			if (resp.status !== 1) return;

			let html = "";

			if (resp.participants.length === 0) {
				html = `<li class="text-muted">No participants yet</li>`;
			} else {
				resp.participants.forEach(p => {
					html += `<li><strong>${p.student_name}</strong>
				<span class="badge badge-success ml-2">${p.live_status}</span></li>`;
				});
			}

			$("#host_participants_list").html(html);
		});
	}


	/* =========================
	   ANSWERS
	========================= */

	function fetchAnswers() {

		if (!window._live_session?.id || !examLive) return;

		let qid = $(".step-pane[data-step='" + currentStep + "']").data("question-id");
		if (!qid) return;

		$.getJSON(base_url + "LiveExam/getSessionAnswers", {
			session_id: window._live_session.id,
			question_id: qid
		}, function(resp) {

			if (resp.status !== 1) return;

			let answers = resp.data || resp.answers || [];
			let html = "";

			if (answers.length === 0) {
				html = `<tr><td colspan="3" class="text-muted text-center">No answers yet</td></tr>`;
			} else {
				answers.forEach(a => {
					html += `<tr>
					<td>${a.student_name}</td>
					<td>${a.answer}</td>
					<td>${a.submitted_at}</td>
				</tr>`;
				});
			}

			$("#host_answers_table tbody").html(html);
		});
	}


	/* =========================
	   NAVIGATION
	========================= */

	function showStep(step) {

		if (step < 1 || step > totalQuestions) return;

		currentStep = step;
		syncWizardUI(step);

		let qid = $(".step-pane[data-step='" + step + "']").data("question-id");

		if (window._live_session && qid && examLive) {
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


	/* =========================
	   TIMER
	========================= */

	function startTimer() {

		if (timerInterval) clearInterval(timerInterval);

		timerInterval = setInterval(function() {

			if (!examLive) return;

			elapsed_seconds++;
			let parts = examDuration.split(":").map(Number);
			let total = parts[0] * 3600 + parts[1] * 60 + parts[2];
			let remain = total - elapsed_seconds;

			if (remain <= 0) {
				clearInterval(timerInterval);
				$(".remain_duration").text("00:00:00");
				return;
			}

			let h = String(Math.floor(remain / 3600)).padStart(2, "0");
			let m = String(Math.floor((remain % 3600) / 60)).padStart(2, "0");
			let s = String(remain % 60).padStart(2, "0");

			$(".remain_duration").text(`${h}:${m}:${s}`);

		}, 1000);
	}


	/* =========================
	   END SESSION (UNCHANGED)
	========================= */

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
		}, function() {
			$("#examModal").modal("hide");
			clearInterval(heartbeatTimer);
		});
	}
</script>