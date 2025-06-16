<div class="row">
	<div class="col-sm-12">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><?= translate('select_ground') ?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string(), array('class' => 'validate', 'id' => 'myFormId')); ?>
			<div class="panel-body">
				<div class="row mb-sm">
					<?php if (is_superadmin_loggedin()): ?>
						<div class="col-md-3">
							<div class="form-group">
								<label class="control-label"><?= translate('branch') ?> <span
										class="required">*</span></label>
								<?php
								$arrayBranch = $this->app_lib->getSelectList('branch');
								echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
													data-plugin-selectTwo data-width='100%' data-placeholder='Search a brnach'");
								?>
							</div>
						</div>
					<?php endif; ?>
					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="control-label"><?= translate('exam') ?> <span
									class="required">*</span></label>
							<?php
							if (isset($branch_id)) {
								$arrayExam = array("" => translate('select'));
								$exams = $this->db->get_where('exam', array('branch_id' => $branch_id, 'session_id' => get_session_id()))->result();
								foreach ($exams as $row) {
									$arrayExam[$row->id] = $this->application_model->exam_name_by_id($row->id);
								}
							} else {
								$arrayExam = array("" => translate('select_branch_first'));
							}
							echo form_dropdown("exam_id", $arrayExam, set_value('exam_id'), "class='form-control' id='exam_id' required data-plugin-selectTwo
													data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>

					<div class="col-md-3 mb-sm">
						<label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
						<?php
						$arrayClass = $this->app_lib->getClass($branch_id);
						echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
												required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
						?>
					</div>

					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="control-label"><?= translate('section') ?> <span
									class="required">*</span></label>
							<?php
							$arraySection = $this->app_lib->getSections(set_value('class_id'), false);
							echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
													data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>
					</div>

					<div class="col-md-3 mb-sm">
						<div class="form-group">
							<label class="control-label"><?php echo translate('student'); ?> <span
									class="required">*</span></label>
							<?php
							$arraySection = $this->app_lib->getSections(set_value('student_id'), false);
							echo form_dropdown("student_id", $arraySection, set_value('student_id'), "class='form-control' id='student_id' 
													data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
						</div>

					</div>

					<!-- FIRST DATE -->
					<div class="col-md-3 mb-sm" id="from_date">
						<div class="form-group <?php if (form_error('date'))
							echo 'has-error'; ?>">
							<label class="control-label"><?= translate('from_date') ?> <span
									class="required">*</span></label>
							<div class="input-group">
								<input type="text" class="form-control" data-plugin-datepicker name="first_date"
									value="<?= set_value('first_date', date("Y-m-d")) ?>" />
								<span class="input-group-addon"><i class="icon-event icons"></i></span>
							</div>
							<span class="error"><?= form_error('first_date') ?></span>
						</div>
					</div>

					<!-- LAST DATE -->
					<div class="col-md-3 mb-sm" id="last_date">
						<div class="form-group <?php if (form_error('last_date'))
							echo 'has-error'; ?>">
							<label class="control-label"><?= translate('last_date') ?> <span
									class="required">*</span></label>
							<div class="input-group">
								<input type="text" class="form-control" data-plugin-datepicker name="last_date"
									value="<?= set_value('last_date', date("Y-m-d")) ?>" />
								<span class="input-group-addon"><i class="icon-event icons"></i></span>
							</div>
							<span class="error"><?= form_error('last_date') ?></span>
						</div>
					</div>

					<div class="col-md-12 mb-sm" id="error_msg">
					</div>
				</div>
			</div>
	</div>

</div>
<?php echo form_close(); ?>
<div class="report-cards-container">
	<div class="row">
		<div class="col-sm-3">
			<div class="card mt-20">
				<img class="card-img-top"
					src="<?php echo base_url('assets/reports/50b31774-42c8-45fd-a724-afd588e063eqda.jpeg'); ?>"
					alt="Card image" style="width:100%;height: 415px;">
				<div class="card-body">
					<h4 class="card-title">Student Progress Report</h4>
					<a href="#" class="btn btn-primary"
						onclick="setSession('<?= base_url('report/annual_examination_report') ?>')">Visit
						Report</a>
				</div>
			</div>
		</div>
		<?php if (is_superadmin_loggedin()) {
			?>
			<div class="col-sm-3">
				<div class="card mt-20">
					<img class="card-img-top"
						src="<?php echo base_url('assets/reports/70d145f2-a29f-4e6c-a43c-90c09f5e742a75.png'); ?>"
						alt="Card image" style="width:100%;height: 415px;">
					<div class="card-body">
						<h4 class="card-title">Skill Based - Mount Kilimanjaro Report</h4>
						<a href="<?= base_url('report/skill_based_report') ?>" class="btn btn-primary" target="_blank">Visit
							Report</a>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="card mt-20">
					<img class="card-img-top"
						src="<?php echo base_url('assets/reports/5e2b5355-1w585-4f38-a9e1-ee4fb982fbfe1.png'); ?>"
						alt="Card image" style="width:100%;height: 415px;">
					<div class="card-body">
						<h4 class="card-title">Subject Based - Mount Etna Report</h4>
						<a href="<?= base_url('report/subject_based_report') ?>" class="btn btn-primary"
							target="_blank">Visit Report</a>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="card mt-20">
					<img class="card-img-top"
						src="<?php echo base_url('assets/reports/e4e3c2cd-7149-43d6-92c4-983b821fc1bb.jpg'); ?>"
						alt="Card image" style="width:100%;height: 415px;">
					<div class="card-body">
						<h4 class="card-title">Exam Type + Skill Based - Mount Nemrut Report</h4>
						<a href="<?= base_url('report/type_skill_based_report') ?>" class="btn btn-primary"
							target="_blank">Visit Report</a>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="card mt-20">
					<img class="card-img-top"
						src="<?php echo base_url('assets/reports/5c1f48f7-f611-4315-aaad-9c9157f3e448.png'); ?>"
						alt="Card image" style="width:100%;height: 415px;">
					<div class="card-body">
						<h4 class="card-title">Term End Report</h4>
						<a href="<?= base_url('report/term_end_report') ?>" class="btn btn-primary" target="_blank">Visit
							Report</a>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="card mt-20">
					<img class="card-img-top"
						src="<?php echo base_url('assets/reports/Mount_Miller_Skill_Based_Report_With_Student_Image11.png'); ?>"
						alt="Card image" style="width:100%;height: 415px;">
					<div class="card-body">
						<h4 class="card-title">Grade Book Report</h4>
						<a href="<?= base_url('report/grade_book_report') ?>" class="btn btn-primary" target="_blank">Visit
							Report</a>
					</div>
				</div>
			</div>

			<div class="col-sm-3">
				<div class="card mt-20">
					<img class="card-img-top" src="<?php echo base_url('assets/reports/term_end_report_versin_2.png'); ?>"
						alt="Card image" style="width:100%;height: 415px;">
					<div class="card-body">
						<h4 class="card-title">Term End Report Version 2</h4>
						<a href="<?= base_url('report/term_end_report_versin_2') ?>" class="btn btn-primary"
							target="_blank">Visit Report</a>
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="card mt-20">
					<img class="card-img-top" src="<?php echo base_url('assets/reports/term_end_report_versin_3.png'); ?>"
						alt="Card image" style="width:100%;height: 415px;">
					<div class="card-body">
						<h4 class="card-title">Term End Report Version 3</h4>
						<a href="<?= base_url('report/term_end_report_versin_3') ?>" class="btn btn-primary"
							target="_blank">Visit Report</a>
					</div>
				</div>
			</div>
			<?php
		}
		?>

	</div>
</div>



<script type="text/javascript">
	$(document).ready(function () {

		$('.report-cards-container').hide();
		$('#from_date').closest('.col-md-3').hide();
		$('#last_date').closest('.col-md-3').hide();

		// Fetch students when class is changed
		$('#class_id').on('change', function () {
			var class_id = $(this).val();
			var branch_id = $("#branch_id").length ? $('#branch_id').val() : "";
			$.ajax({
				url: base_url + 'ajax/getStudentByClass',
				type: 'POST',
				data: {
					branch_id: branch_id,
					class_id: class_id
				},
				success: function (data) {
					$('#student_id').html(data);
				}
			});
		});

		// Fetch classes and exams when branch is changed
		$('#branch_id').on('change', function () {
			var branchID = $(this).val();
			getClassByBranch(branchID);
			getExamByBranch(branchID);
			$('#subject_id').html('').append('<option value=""><?= translate("select") ?></option>');
		});

		// Fetch subjects when section is changed
		$('#section_id').on('change', function () {
			var classID = $('#class_id').val();
			var sectionID = $(this).val();
			$.ajax({
				url: base_url + 'subject/getByClassSection',
				type: 'POST',
				data: {
					classID: classID,
					sectionID: sectionID
				},
				success: function (data) {
					$('#subject_id').html(data);
				}
			});
		});

		// Handle student selection and check attendance report
		$('#student_id, #branch_id, #exam_id').on('change', function () {
			var studentId = $('#student_id').val();
			if (studentId) {
				checkReportCardAttendance();
			} else {
				$('.report-cards-container').hide();
			}
		});

		function checkReportCardAttendance() {

			var branch_id = $('#branch_id').val();
			var studentId = $('#student_id').val();
			var examId = $('#exam_id').val();
			<?php if (!is_superadmin_loggedin()) { ?>
				branch_id = "<?= get_loggedin_branch_id(); ?>";
			<?php } ?>

			$.ajax({
				url: "<?= base_url('Report/checkExistingReport') ?>",
				type: "POST",
				data: {
					branch_id: branch_id,
					student_id: studentId,
					exam_id: examId
				},
				dataType: "json",
				success: function (data) {
					if (data.status) {
						$('.report-cards-container').show();

						let editBtnHTML = `<button type="button" class="btn btn-warning mb-md" id="edit_date_btn">
								<i class="fa fa-edit"></i> Edit Attendance
						   </button>`;


						$('#from_date').closest('.col-md-3').hide();
						$('#last_date').closest('.col-md-3').hide();
						$('#error_msg').html(editBtnHTML);
					} else {
						$('.report-cards-container').show();
						$('#from_date').closest('.col-md-3').show();
						$('#last_date').closest('.col-md-3').show();
						$('#error_msg').html('');
					}
				},
				error: function (error) {
					console.log("Error checking report:", error);
				}
			});
		}



	});

	$(document).on('click', '#edit_date_btn', function () {
		let isShown = $('#from_date').is(':visible');

		if (!isShown) {
			$('#from_date').closest('.col-md-3').show();
			$('#last_date').closest('.col-md-3').show();
			$(this).html('<i class="fa fa-times"></i> Cancel Edit');
			$(this).removeClass('btn-warning').addClass('btn-danger');
		} else {
			$('#from_date').closest('.col-md-3').hide();
			$('#last_date').closest('.col-md-3').hide();
			$(this).html('<i class="fa fa-edit"></i> Edit Dates');
			$(this).removeClass('btn-danger').addClass('btn-warning');
		}
	});


	function setSession(tourl) {
		$('#error_msg').html('');
		$.ajax({
			url: "<?= base_url('Report/examReportSessionStore') ?>",
			type: "post",
			data: $("#myFormId").serialize(),
			dataType: "json",
			success: function (data) {
				if (data?.status) {
					window.location.href = tourl;
				} else {
					$('#error_msg').html('<p style="color:red;">' + data?.message + '</p>');
				}
			},
			error: function (error) {
				console.log(error);
			}
		});
	}
</script>