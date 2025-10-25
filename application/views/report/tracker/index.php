

<?php $widget = (is_superadmin_loggedin() ? 3 : 4); ?>
<section class="panel">
	<header class="panel-heading">
		<h4 class="panel-title"><?= translate('progress_report') ?></h4>
	</header>
	<?php echo form_open($this->uri->uri_string()); ?>
	<div class="panel-body">
		<div class="row mb-sm">

			<?php if (is_superadmin_loggedin()): ?>
				<div class="col-md-3 mb-sm">
					<div class="form-group">
						<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
						<?php
						$arrayBranch = $this->app_lib->getSelectList('branch');
						echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
									data-plugin-selectTwo data-width='100%' data-placeHolder='Select Branch'");
						?>
					</div>
					<span class="error"><?= form_error('branch_id') ?></span>
				</div>
			<?php endif; ?>
			<div class="col-md-<?php echo $widget; ?> mb-sm">
				<div class="form-group">
					<label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
					<?php
					if (!is_superadmin_loggedin()) {
						$branch_id = get_loggedin_branch_id();
					}
					$arrayClass = $this->app_lib->getSelectClassList($branch_id);
					echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
					 	data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
					?>
					<span class="error"><?= form_error('class_id') ?></span>
				</div>
			</div>
			<div class="col-md-<?php echo $widget; ?> mb-sm">
				<div class="form-group">
					<label class="control-label"><?= translate('section') ?> <span class="required">*</span></label>
					<?php
					$arraySection = $this->app_lib->getSections(set_value('class_id'), false);
					echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id'
						data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
					?>
					<span class="error"><?= form_error('section_id') ?></span>
				</div>
			</div>

			<div class="col-md-<?php echo $widget; ?> mb-sm">
				<div class="form-group">
					<label class="control-label"><?= translate('student') ?> <span class="required">*</span></label>
					<select data-plugin-selectTwo class="form-control" name="student_id" id="student_id">

					</select>
				</div>
			</div>

			<div class="col-md-<?php echo $widget; ?> mb-sm">
				<div class="form-group">
					<label class="control-label"><?= translate('subject') ?> <span class="required">*</span></label>
					<?php
					$arraySubject = array("" => translate('select_student_first'));
					echo form_dropdown("subject_id", $arraySubject, set_value('subject_id'), "class='form-control' id='subject_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
					?>
					<span class="error"><?= form_error('subject_id') ?></span>
				</div>
			</div>


		</div>
	</div>
	<footer class="panel-footer">
		<div class="row">
			<div class="col-md-offset-10 col-md-2">
			<button type="submit" name="search" value="1" class="btn btn-filter btn-block"> 
                <i class="fas fa-search"></i> View Progress
            </button>
			</div>
		</div>
	</footer>
	<?php echo form_close(); ?>
</section>



<?php if (isset($progressDetails)): ?>
	<section class="panel appear-animation" data-appear-animation="<?= $global_config['animations'] ?>"
		data-appear-animation-delay="100">
		<header class="panel-heading">
			<h4 class="panel-title"><i class="fas fa-users"></i> <?= translate('progress_track') ?></h4>
		</header>
		<div class="panel-body">
			<div class="row">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Subject</th>
							<th class="text-right">Action</th>
						</tr>
					</thead>
					<tbody class="px-2">
						<?php if (!empty($progressDetails)): ?>

							<tr>
								<td><?= $progressDetails['subject_name']; ?></td>

								<td class="text-right">
									<a href="<?= base_url('report/student_progress/' . $progressDetails['studentId'] . '/' . $progressDetails['subjectId']) ?>"
										class="btn btn-sm btn-primary" title="View Progress">
										<i class="fas fa-chart-line"></i> View
									</a>
								</td>

							</tr>
						<?php else: ?>
							<tr>
								<td colspan="5">No progress details available.</td>
							</tr>
						<?php endif; ?>

					</tbody>
				</table>
			</div>
		</div>

	</section>
<?php endif; ?>

<script type="text/javascript">
	$(document).ready(function () {

		var branchID = "";

		$('#branch_id').on('change', function () {
			branchID = $(this).val();
			getClassByBranch(branchID);
			$('#subject_id').html('').append('<option value=""><?= translate("select") ?></option>');
		});

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



		$('#section_id').on('change', function () {
			var section_id = $(this).val();
			var class_id = $('#class_id').val();
			var branchID = $("#branch_id").length ? $('#branch_id').val() : "<?php echo get_loggedin_branch_id(); ?>";

			getStudentByClass(branchID, class_id, section_id);
		});


		function setSession(tourl) {
			$('#error_msg').html('');
			$.ajax({
				url: "<?= base_url('Report/ReportSessionStore') ?>",
				type: "post",
				data: $("#myFormId").serialize(0),
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
			})
		}
	});


	function getStudentByClass(branchID, class_id, section_id) {
		var student_id = "<?= set_value('student_id') ?>";

		$.ajax({
			url: base_url + 'ajax/getStudentByClass',
			type: 'POST',
			data: {
				branch_id: branchID,
				class_id: class_id,
				section_id: section_id,
				student_id: student_id
			},
			success: function (data) {
				$('#student_id').html(data);
			}
		});
	}

</script>