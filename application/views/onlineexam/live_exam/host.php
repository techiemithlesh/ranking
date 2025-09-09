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
	var examDuration = "<?php echo $exam->duration; ?>";
	var totalQuestions = 0;
	var currentStep = 1;
	var interval = null;
	var elapsed_seconds = 0;

	// Start Hosting Exam
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
				if (data.status === 1) {
					if ($("#exam_questions").length) {
						totalQuestions = parseInt(data.total_questions) || 0;
						$("#exam_questions").html(data.page);

						// Reset to first question
						currentStep = 1;
						showStep(1);

						// Timer Start
						startTimer();

						// 🔹 Call session generate now
						hostSessionGenerate(examID);

						// Open Modal
						$("#examModal").modal({
							show: true,
							backdrop: "static",
							keyboard: false
						});
					}
				} else {
					alertMsg(data.message, "error", "Error", "");
				}
			},
			error: function () {
				alert("Error occurred, please try again.");
				$this.button("reset");
			},
			complete: function () {
				$this.button("reset");
			}
		});
	});


	function hostSessionGenerate(examID) {
		$.ajax({
			type: "POST",
			url: base_url + "liveexam/startSession",
			data: { exam_id: examID },
			dataType: "JSON",
			success: function (resp) {
				if (resp.status === 1) {
					// Show session info
					$("#sessionInfo").html(
						'<p><strong>Session Code:</strong> ' + resp.session_code + '</p>' +
						'<p><strong>Join Link:</strong> ' +
						'<input type="text" id="joinLink" value="' + resp.join_link + '" readonly style="width:80%;"> ' +
						'<button onclick="copyJoinLink()">Copy</button></p>'
					);
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

	// Show Step (without FuelUX)
	function showStep(step) {
		if (step < 1 || step > totalQuestions) return;

		currentStep = step;

		// Hide all, show current
		$(".step-pane").removeClass("active");
		$('[data-step="' + step + '"]').addClass("active");

		// Highlight active button
		$(".que_btn").removeClass("active");
		$("#question" + step).addClass("active");

		// Prev/Next button handling
		$("#prevbutton").prop("disabled", step === 1);
		$("#nextbutton").prop("disabled", step === totalQuestions);
	}

	// Timer
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




	// Prev/Next buttons
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

	// End Session
	$(document).on("click", "#end_session_btn", function () {
		if (!confirm("Are you sure you want to end this live exam session?")) return;

		$.ajax({
			type: "POST",
			url: base_url + "liveexam/endSession",
			data: { exam_id: $("input[name='online_exam_id']").val() },
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