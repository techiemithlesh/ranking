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
	$(document).ready(function() {

		const hasActiveSession = <?= $active_session ? 'true' : 'false' ?>;
		const resumeSessionId = <?= $active_session ? (int)$active_session->id : 'null' ?>;
		const resumeElapsed = <?= (int)$elapsed_seconds ?>;

		if (hasActiveSession) {

			$("#examModal").modal({
				show: true,
				backdrop: "static",
				keyboard: false
			});

			$.post(base_url + "LiveExam/ajaxGetQuestions", {
				exam_id: <?= (int)$exam->id ?>
			}, function(resp) {

				if (resp.status === 1 && resp.resume === 1) {

					$("#exam_questions").html(resp.page);

					// Restore session
					window._live_session = {
						id: resp.session_id,
						session_code: resp.session_code,
						join_link: resp.join_link
					};

					// Show session info again
					$("#sessionInfo").html(
						`<p><strong>Session Code:</strong> ${resp.session_code}</p>
                     <p><strong>Join Link:</strong>
                     <input type="text" value="${resp.join_link}" readonly style="width:80%;"></p>`
					);

					// Restore question
					$(".step-pane").removeClass("active");
					$(".step-pane[data-question-id='" + resp.current_question_id + "']").addClass("active");

					$(".que_btn").removeClass("active");
					$("#question" + resp.current_index).addClass("active");

					currentStep = resp.current_index;

					// Resume timer
					elapsed_seconds = resumeElapsed;
					startTimer();
					startHeartbeat();
					fetchAnswers();
				}
			}, 'json');
		}
	});



	var examDuration = "<?= $exam->duration; ?>";
	var totalQuestions = 0;
	var currentStep = 1;
	var timerInterval = null;
	var elapsed_seconds = 0;
	let sessionEndedManually = false;

	window._live_session = null;
	var heartbeatTimer = null;

	// -----------------------------
	// Start Hosting Exam
	// -----------------------------
	$(document).on("click", ".start_btn", function() {
		var $this = $(this);
		var examID = $this.attr("data-examid");

		$.ajax({
			type: "POST",
			url: base_url + "LiveExam/ajaxGetQuestions",
			data: {
				exam_id: examID
			},
			dataType: "JSON",
			beforeSend: function() {
				$this.button("loading");
				clearInterval(timerInterval);
			},
			success: function(data) {
				if (data.status === 1 && $("#exam_questions").length) {
					totalQuestions = parseInt(data.total_questions) || 0;
					$("#exam_questions").html(data.page);

					// Reset to first question
					currentStep = 1;
					showStep(1);

					// Start timer
					startTimer();

					// Create live session (waiting)
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
			error: function() {
				alert("Error occurred, please try again.");
			},
			complete: function() {
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
			url: base_url + "LiveExam/startSession",
			data: {
				exam_id: examID,
				current_question_id: currentQuestionId
			},
			dataType: "JSON",
			success: function(resp) {
				if (resp.status === 1) {
					window._live_session = {
						id: resp.session_id,
						session_code: resp.session_code,
						join_link: resp.join_link
					};

					$("#sessionInfo").html(
						`<p><strong>Session Code:</strong> ${resp.session_code}</p>
						 <p><strong>Join Link:</strong>
						 <input type="text" id="joinLink" value="${resp.join_link}" readonly style="width:80%;">
						 <button onclick="copyJoinLink()">Copy</button></p>`
					);

					// Start heartbeat polling
					startHeartbeat();
				} else {
					alertMsg(resp.message, "error", "Error", "");
				}
			},
			error: function() {
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

		$(".step-pane").removeClass("active");
		$(`[data-step='${step}']`).addClass("active");

		$(".que_btn").removeClass("active");
		$("#question" + step).addClass("active");

		$("#prevbutton").prop("disabled", step === 1);
		$("#nextbutton").prop("disabled", step === totalQuestions);

		var qid = $(".step-pane[data-step='" + step + "']").attr("data-question-id");
		if (window._live_session && qid) {
			setSessionCurrentQuestion(window._live_session.id, qid);
			fetchAnswers();
		}
	}

	$(document).on("click", "#prevbutton", function() {
		showStep(currentStep - 1);
	});

	$(document).on("click", "#nextbutton", function() {
		showStep(currentStep + 1);
	});

	$(document).on("click", ".que_btn", function() {
		var step = parseInt(this.id.replace("question", ""));
		showStep(step);
	});

	// -----------------------------
	// Timer
	// -----------------------------
	function startTimer() {
		elapsed_seconds = 0;
		timerInterval = setInterval(function() {
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
			clearInterval(timerInterval);
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
		$.post(base_url + "LiveExam/setCurrentQuestion", {
			session_id: sessionId,
			question_id: qid
		});
	}

	// -----------------------------
	// Fetch Participants
	// -----------------------------
	function fetchParticipants() {
		if (!window._live_session?.id) return;

		$.getJSON(base_url + "LiveExam/getParticipants", {
			session_id: window._live_session.id
		}, function(resp) {
			if (resp.status === 1) {
				let listHtml = "";
				if (resp.participants.length > 0) {
					resp.participants.forEach(function(p) {
						let badgeClass = "badge-secondary";
						let statusLabel = p.live_status;

						if (p.live_status === "active") {
							badgeClass = "badge-success";
							statusLabel = "Active"
						} else if (p.live_status === "left") {
							badgeClass = "badge-danger";
							statusLabel = "Left";
						} else if (p.live_status === "completed") {
							badgeClass = "badge-info";
							statusLabel = "Completed";
						}

						listHtml += `
						<li id="p_${p.student_id}">
							<strong>${p.student_name}</strong>
							${p.register_no ? `<span class="text-muted">(${p.register_no})</span>` : ""}
							<span class="badge ${badgeClass} ml-2">${statusLabel}</span>
							<div class="small text-muted">
								Joined: ${p.joined_at ? new Date(p.joined_at).toLocaleTimeString() : ""}
							</div>
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
	// Fetch Answers (only current question)
	// -----------------------------
	function fetchAnswers() {
		if (!window._live_session?.id) return;
		var qid = $(".step-pane[data-step='" + currentStep + "']").attr("data-question-id");
		if (!qid) return;

		$.getJSON(base_url + "LiveExam/getSessionAnswers", {
				session_id: window._live_session.id,
				question_id: qid
			},
			function(resp) {
				if (resp.status === 1) {
					let answers = resp.data || resp.answers || [];
					let answersHtml = "";

					if (answers.length > 0) {
						answers.forEach(a => {
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
	// Unified Heartbeat
	// -----------------------------
	function startHeartbeat() {
		if (heartbeatTimer) clearInterval(heartbeatTimer);

		heartbeatTimer = setInterval(function() {
			if (!window._live_session?.id) return;

			fetchParticipants();
			fetchAnswers();

			// simple ping (keeps session alive)
			$.post(base_url + "LiveExam/sessionHeartbeat", {
				session_id: window._live_session.id
			});
		}, 5000);
	}

	// -----------------------------
	// Activate Session Once (modal fully shown)
	// -----------------------------
	$("#examModal").on("shown.bs.modal", function() {
		if (window._live_session?.id) {
			$.post(base_url + "LiveExam/activateSession", {
				session_id: window._live_session.id
			});
		}
	});

	// -----------------------------
	// End Session
	// -----------------------------
	$(document).on("click", "#end_session_btn", function() {
		if (!confirm("Are you sure you want to end this live exam session?")) return;
		sessionEndedManually = true; // mark as manual end
		endLiveSession();
	});


	// If host closes modal without ending → auto abort
	$("#examModal").on("hidden.bs.modal", function() {
		if (window._live_session?.id && !sessionEndedManually) {
			endLiveSession(true); // mark aborted
		}
	});

	function endLiveSession(aborted = false) {
		if (!window._live_session?.id) return;

		let publish = 0;
		if (!aborted) {
			if (confirm("Do you want to publish the results now?")) {
				publish = 1;
			}
		}

		$.ajax({
			type: "POST",
			url: base_url + "LiveExam/endSession",
			data: {
				session_id: window._live_session.id,
				aborted: aborted ? 1 : 0,
				publish: publish
			},
			success: function(res) {
				try {
					var data = JSON.parse(res);
					if (data.status === 1) {
						alertMsg("Session ended successfully!", "success", "Done", "");
						$("#examModal").modal("hide");
						clearInterval(heartbeatTimer);

						if (data.redirect_url) {
							setTimeout(() => {
								window.location.href = data.redirect_url;
							}, 5000);
						}

					} else {
						alertMsg(data.message || "Unable to end session", "error", "Error", "");
					}
				} catch (e) {
					console.error("Invalid response", res);
				}
			},
			error: function() {
				alert("Error occurred while ending session.");
			}
		});
	}
</script>