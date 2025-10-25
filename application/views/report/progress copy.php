<!doctype html>
<html class="fixed sidebar-left-sm <?php echo ($theme_config['dark_skin'] == 'true' ? 'dark' : 'sidebar-light'); ?>">
<!-- html header -->
<?php $this->load->view('layout/header.php'); ?>

<body class="loading-overlay-showing" data-loading-overlay>
	<!-- page preloader -->
	<div class="loading-overlay dark">
		<div class="ring-loader">
			Loading <span></span>
		</div>
	</div>
	<section class="body">
		<!-- top navbar-->
		<?php $this->load->view('layout/topbar.php'); ?>
		<div class="inner-wrapper">
			<!-- sidebar -->
			<?php
			if (is_student_loggedin() || is_parent_loggedin()) {
				$this->load->view('userrole/sidebar');
			} else {
				$this->load->view('layout/sidebar');
			}
			?>
			<!-- page main content -->
			<section role="main" class="content-body">
				<header class="page-header">
					<a class="page-title-icon" href="<?php echo base_url('dashboard'); ?>"><i
							class="fas fa-home"></i></a>
					<h2><?php echo $title; ?></h2>
				</header>
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
										<label class="control-label"><?= translate('class') ?> <span
												class="required">*</span></label>
										<?php
										$arrayClass = $this->app_lib->getSelectClassList($branch_id);
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
												<input type="text" class="form-control" data-plugin-datepicker
													name="first_date"
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
												<input type="text" class="form-control" data-plugin-datepicker
													name="last_date"
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
										<a href="<?= base_url('report/skill_based_report') ?>" class="btn btn-primary"
											target="_blank">Visit Report</a>
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
										<a href="<?= base_url('report/term_end_report') ?>" class="btn btn-primary"
											target="_blank">Visit Report</a>
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
										<a href="<?= base_url('report/grade_book_report') ?>" class="btn btn-primary"
											target="_blank">Visit Report</a>
									</div>
								</div>
							</div>

							<div class="col-sm-3">
								<div class="card mt-20">
									<img class="card-img-top"
										src="<?php echo base_url('assets/reports/term_end_report_versin_2.png'); ?>"
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
									<img class="card-img-top"
										src="<?php echo base_url('assets/reports/term_end_report_versin_3.png'); ?>"
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
			</section>
		</div>
	</section>
	</div>
	</section>

	<!-- JS Script -->
	<?php $this->load->view('layout/script.php'); ?>

	<?php
	$alertclass = "";
	if ($this->session->flashdata('alert-message-success')) {
		$alertclass = "success";
	} else if ($this->session->flashdata('alert-message-error')) {
		$alertclass = "error";
	} else if ($this->session->flashdata('alert-message-info')) {
		$alertclass = "info";
	}
	if ($alertclass != ''):
		$alert_message = $this->session->flashdata('alert-message-' . $alertclass);
		?>
		<script type="text/javascript">
			swal({
				toast: true,
				position: 'top-end',
				type: '<?php echo $alertclass ?>',
				title: '<?php echo $alert_message ?>',
				confirmButtonClass: 'btn btn-default',
				buttonsStyling: false,
				timer: 8000
			})
		</script>
	<?php endif; ?>

	<script type="text/javascript">
		$(document).ready(function () {
			// Initially hide the reports section and date fields
			$('.report-cards-container').hide();
			$('#from_date').closest('.col-md-3').hide();
			$('#last_date').closest('.col-md-3').hide();

			// Function to show delete confirmation modal
			function confirm_modal(delete_url) {
				swal({
					title: "<?php echo translate('are_you_sure') ?>",
					text: "<?php echo translate('delete_this_information') ?>",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn btn-default swal2-btn-default",
					cancelButtonClass: "btn btn-default swal2-btn-default",
					confirmButtonText: "<?php echo translate('yes_continue') ?>",
					cancelButtonText: "<?php echo translate('cancel') ?>",
					buttonsStyling: false,
					footer: "<?php echo translate('deleted_note') ?>"
				}).then((result) => {
					if (result.value) {
						$.ajax({
							url: delete_url,
							type: "POST",
							success: function (data) {
								swal({
									title: "<?php echo translate('deleted') ?>",
									text: "<?php echo translate('information_deleted') ?>",
									buttonsStyling: false,
									showCloseButton: true,
									focusConfirm: false,
									confirmButtonClass: "btn btn-default swal2-btn-default",
									type: "success"
								}).then((result) => {
									if (result.value) {
										location.reload();
									}
								});
							}
						});
					}
				});
			}

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
							$('#from_date').closest('.col-md-3').hide();
							$('#last_date').closest('.col-md-3').hide();
						} else {
							$('.report-cards-container').show();
							$('#from_date').closest('.col-md-3').show();
							$('#last_date').closest('.col-md-3').show();
						}
					},
					error: function (error) {
						console.log("Error checking report:", error);
					}
				});
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

</body>

</html>