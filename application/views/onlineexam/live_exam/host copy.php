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
					<div id="exam_questions"></div>
				</div>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
	var examDuration = "<?= $exam->duration; ?>";
	var totalQuestions = 0;
	var currentStep = 1;
	var interval = null;
	var elapsed_seconds = 0;

	window._live_session = null;

	// -----------------------------
	// Start Hosting Exam
	// -----------------------------
	$(document).on("click", ".start_btn", function () {
		var $this = $(this);
		var examID = $this.attr("data-examid");

		$.ajax({
			type: "POST",
			url: base_url + "liveexam/ajaxGetQuestions",
			data: { exam_id: examID },
			dataType: "JSON",
			beforeSend: function () {
				$this.button("loading");
				clearInterval(interval);
			},
			success: function (data) {
				if (data.status === 1 && $("#exam_questions").length) {
					totalQuestions = parseInt(data.total_questions) || 0;
					$("#exam_questions").html(data.page);

					// Reset & show first question
					currentStep = 1;
					showStep(1);

					// Start exam timer
					startTimer();

					// Create live session
					hostSessionGenerate(examID);

					// Open modal
					$("#examModal").modal({
						show: true,
						backdrop: "static",
						keyboard: false
					});
				} else {
					alertMsg(data.message || "Error loading questions", "error", "Error", "");
				}
			},
			error: function () {
				alert("Error occurred, please try again.");
			},
			complete: function () {
				$this.button("reset");
			}
		});
	});

	// -----------------------------
	// Generate Live Session
	// -----------------------------
	function hostSessionGenerate(examID) {
		var currentQuestionId = $(".step-pane[data-step='1']").data("question-id");

		$.ajax({
			type: "POST",
			url: base_url + "liveexam/startSession",
			data: { exam_id: examID, current_question_id: currentQuestionId },
			dataType: "JSON",
			success: function (resp) {
				if (resp.status === 1) {
					window._live_session = {
						id: resp.session_id,
						session_code: resp.session_code,
						join_link: resp.join_link
					};

					// Show session info
					$("#sessionInfo").html(
						`<p><strong>Session Code:</strong> ${resp.session_code}</p>
						 <p><strong>Join Link:</strong>
						 <input type="text" id="joinLink" value="${resp.join_link}" readonly style="width:80%;">
						 <button onclick="copyJoinLink()">Copy</button></p>`
					);

					// Start polling participants & answers
					startHostPolling();
				} else {
					alertMsg(resp.message, "error", "Error", "");
				}
			},
			error: function () {
				alert("Error creating live session");
			}
		});
	}

	function copyJoinLink() {
		var input = document.getElementById("joinLink");
		input.select();
		document.execCommand("copy");
		alert("Join link copied!");
	}

	// -----------------------------
	// Navigation
	// -----------------------------
	function showStep(step) {
		if (step < 1 || step > totalQuestions) return;

		currentStep = step;

		// Switch active question
		$(".step-pane").removeClass("active");
		$(`[data-step='${step}']`).addClass("active");

		// Highlight active in map
		$(".que_btn").removeClass("active");
		$("#question" + step).addClass("active");

		// Button states
		$("#prevbutton").prop("disabled", step === 1);
		$("#nextbutton").prop("disabled", step === totalQuestions);

		// Update current question in session
		var qid = $(".step-pane[data-step='" + step + "']").attr("data-question-id");
		if (window._live_session && qid) {
			setSessionCurrentQuestion(window._live_session.id, qid);
		}
	}

	$(document).on("click", "#prevbutton", function () {
		showStep(currentStep - 1);
	});

	$(document).on("click", "#nextbutton", function () {
		showStep(currentStep + 1);
	});

	$(document).on("click", ".que_btn", function () {
		var step = parseInt(this.id.replace("question", ""));
		showStep(step);
	});

	// -----------------------------
	// Timer
	// -----------------------------
	function startTimer() {
		elapsed_seconds = 0;
		interval = setInterval(function () {
			$(".remain_duration").text(durationUpdate());
		}, 1000);
	}

	function durationUpdate() {
		elapsed_seconds++;
		var parts = examDuration.split(":");
		var h = parseInt(parts[0]) || 0;
		var m = parseInt(parts[1]) || 0;
		var s = parseInt(parts[2]) || 0;

		var totalSeconds = h * 3600 + m * 60 + s;
		var remaining = totalSeconds - elapsed_seconds;

		if (remaining <= 0) {
			clearInterval(interval);
			return "00:00:00";
		}

		var rh = Math.floor(remaining / 3600);
		var rm = Math.floor((remaining % 3600) / 60);
		var rs = remaining % 60;

		return (
			String(rh).padStart(2, "0") + ":" +
			String(rm).padStart(2, "0") + ":" +
			String(rs).padStart(2, "0")
		);
	}

	// -----------------------------
	// Update Session Current Question
	// -----------------------------
	function setSessionCurrentQuestion(sessionId, qid) {
		$.post(base_url + "liveexam/setCurrentQuestion",
			{ session_id: sessionId, question_id: qid }
		);
	}

	// -----------------------------
	// Fetch Participants
	// -----------------------------
	function fetchParticipants() {
		if (!window._live_session?.id) return;
		$.getJSON(base_url + "liveexam/getParticipants", { session_id: window._live_session.id }, function (resp) {
			// console.log("siwndn", resp);
			if (resp.status === 1) {
				let listHtml = "";
				if (resp.participants.length > 0) {
					resp.participants.forEach(function (p) {
						listHtml += `
				<li id="p_${p.student_id}">
					<strong>${p.student_name}</strong>
					${p.register_no ? `<span class="text-muted">(${p.register_no})</span>` : ""}
					<span class="text-muted small">
						${p.joined_at ? new Date(p.joined_at).toLocaleTimeString() : ""}
					</span>
				</li>`;
					});
				} else {
					listHtml = `<li class="text-muted">No participants yet</li>`;
				}
				$("#host_participants_list").html(listHtml);
			}
		});
	}

	// -----------------------------
	// Fetch Answers
	// -----------------------------
	function fetchAnswers() {
		if (!window._live_session?.id) return;

		// get current visible question id
		var qid = $(".step-pane[data-step='" + currentStep + "']").attr("data-question-id");
		if (!qid) return;

		$.getJSON(base_url + "liveexam/getSessionAnswers",
			{ session_id: window._live_session.id, question_id: qid },
			function (resp) {
				if (resp.status === 1) {
					let answersHtml = "";
					if (resp.data.length > 0) {
						resp.data.forEach(a => {
							answersHtml += `
							<tr>
								<td>${a.student_name}</td>
								<td>${a.answer}</td>
								<td>${a.submitted_at}</td>
							</tr>`;
						});
					} else {
						answersHtml = `<tr><td colspan="3" class="text-muted text-center">
						<?= translate('no_answers_yet') ?>
					</td></tr>`;
					}
					$("#host_answers_table tbody").html(answersHtml);
				}
			}
		);
	}


	// -----------------------------
	// Polling
	// -----------------------------
	function startHostPolling() {
		setInterval(fetchParticipants, 5000);
		setInterval(fetchAnswers, 5000);
	}

	// -----------------------------
	// End Session
	// -----------------------------
	$(document).on("click", "#end_session_btn", function () {
		if (!confirm("Are you sure you want to end this live exam session?")) return;
		$.ajax({
			type: "POST",
			url: base_url + "liveexam/endSession",
			data: { session_id: window._live_session.id },
			success: function (res) {
				try {
					var data = JSON.parse(res);
					if (data.status === 1) {
						alertMsg("Session ended successfully!", "success", "Done", "");
						$("#examModal").modal("hide");
					} else {
						alertMsg(data.message || "Unable to end session", "error", "Error", "");
					}
				} catch (e) {
					alert("Invalid response from server.");
				}
			},
			error: function () {
				alert("Error occurred while ending session.");
			}
		});
	});
</script>