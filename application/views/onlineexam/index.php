<?php $currency_symbol = $global_config['currency_symbol']; ?>
<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#list" data-toggle="tab">
					<i class="fas fa-list-ul"></i> <?= translate('online_exam') . " " . translate('list') ?>
				</a>
			</li>
			<?php if (get_permission('online_exam', 'is_add')): ?>
				<li>
					<a href="#add" data-toggle="tab">
						<i class="far fa-edit"></i> <?= translate('add') . " " . translate('online_exam') ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
		<div class="tab-content">
			<div class="tab-pane box active mb-md" id="list">
				<table class="table table-bordered table-hover mb-none table-condensed exam-list" width="100%">
					<thead>
						<tr>
							<th class="no-sort"><?= translate('sl') ?></th>
							<?php if (is_superadmin_loggedin()): ?>
								<th><?= translate('branch') ?></th>
							<?php endif; ?>
							<th><?= translate('title') ?></th>
							<th><?= translate('class') ?> (<?= translate('section') ?>)</th>
							<th><?= translate('questions_qty') ?></th>
							<th><?= translate('start_time') ?></th>
							<th><?= translate('end_time') ?></th>
							<th><?= translate('duration') ?></th>
							<th class="no-sort"><?= translate('exam') . " " . translate('fees') ?></th>
							<th class="no-sort"><?= translate('exam_status') ?></th>
							<th class="no-sort"><?= translate('created_by') ?></th>
							<th><?= translate('action') ?></th>
						</tr>
					</thead>

				</table>
			</div>
			<?php if (get_permission('online_exam', 'is_add')): ?>
				<div class="tab-pane" id="add">
					<?php echo form_open('onlineexam/exam_save', array('class' => 'form-bordered form-horizontal frm-submit')); ?>

					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('title') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="title" value="" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('class') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<?php
							$arrayClass = $this->app_lib->getSelectClassList();
							echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0,1)'
								data-plugin-selectTwo data-width='100%' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('section') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<select class="form-control" name="section[]" id="section_id" data-plugin-selectTwo multiple>
							</select>
							<span class="error"></span>
							<div class="checkbox-replace mt-sm pr-xs pull-right">
								<label class="i-checks"><input type="checkbox" class="chk-sendsmsmail"
										name="chk_section"><i></i> Select All</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('subject') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<select class="form-control" name="subject[]" id="subject_id" data-plugin-selectTwo multiple>
							</select>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('start_date') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<div class="input-group">
								<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
								<input type="text" class="form-control" name="start_date"
									value="<?= set_value('start_date', date('Y-m-d')) ?>" data-plugin-datepicker
									data-plugin-options='{ "todayHighlight" : true }' />
							</div>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('end_date') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<div class="input-group">
								<span class="input-group-addon"><i class="far fa-calendar-alt"></i></span>
								<input type="text" class="form-control" name="end_date"
									value="<?= set_value('end_date', date('Y-m-d')) ?>" data-plugin-datepicker
									data-plugin-options='{ "todayHighlight" : true }' />
							</div>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('start_time') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<div class="input-group">
								<span class="input-group-addon"><i class="far fa-clock"></i></span>
								<input type="text" data-plugin-timepicker class="form-control" name="start_time" value="" />
							</div>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('end_time') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<div class="input-group">
								<span class="input-group-addon"><i class="far fa-clock"></i></span>
								<input type="text" data-plugin-timepicker class="form-control" name="end_time" id="end_time"
									value="" />
							</div>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('duration'); ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" data-plugin-timepicker
								data-plugin-options='{"showMeridian" : false, "minuteStep" : 5}' name="duration"
								value="0.00" autocomplete="off" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('limits_of_participation'); ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="participation_limit" autocomplete="off"
								placeholder="Limits on student participation in exams" value="" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('mark') . " " . translate('type'); ?>
							<span class="required">*</span></label>
						<div class="col-md-6">
							<?php
							$arrayClass = array(
								'' => translate('select'),
								1 => translate('percent'),
								0 => translate('fixed'),
							);
							echo form_dropdown("mark_type", $arrayClass, set_value('mark_type'), "class='form-control'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('passing_mark'); ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="passing_mark" autocomplete="off" value="" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?php echo translate('instruction'); ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<textarea name="instruction" rows="2" class="form-control"></textarea>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('exam') . " " . translate('type') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<?php
							$arrayClass = array(
								'' => translate('select'),
								0 => translate('free'),
								1 => translate('paid'),
							);
							echo form_dropdown("exam_type", $arrayClass, set_value('exam_type'), "class='form-control' id='examType'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group hidden-div" id="examFee">
						<label class="col-md-3 control-label"><?= translate('exam') . " " . translate('fees') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="exam_fee" autocomplete="off" value="" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('question') . " " . translate('type') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<?php
							$arrayClass = array(
								'' => translate('select'),
								0 => translate('fixed'),
								1 => translate('random'),
							);
							echo form_dropdown("question_type", $arrayClass, set_value('question_type'), "class='form-control' id='questionType'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('result_publish') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<?php
							$arrayClass = array(
								'' => translate('select'),
								1 => "Automatic/Immediate",
								0 => "Manually",
							);
							echo form_dropdown("publish_result", $arrayClass, set_value('publish_result'), "class='form-control' id='publish_result'
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label
							class="col-md-3 control-label"><?= translate('negative_mark') . " " . translate('applicable') ?></label>
						<div class="col-md-6">
							<div class="material-switch mt-xs">
								<input class="switch_menu" id="negative_marking" name="negative_marking" type="checkbox" />
								<label for="negative_marking" class="label-primary"></label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('marks_display') ?></label>
						<div class="col-md-6 mb-lg">
							<div class="material-switch mt-xs">
								<input class="switch_menu" id="marks_display" name="marks_display" checked
									type="checkbox" />
								<label for="marks_display" class="label-primary"></label>
							</div>
						</div>
					</div>

					<footer class="panel-footer">
						<div class="row">
							<div class="col-md-offset-3 col-md-2">
								<button type="submit" class="btn btn-default btn-block"
									data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
									<i class="fas fa-plus-circle"></i> <?= translate('save') ?>
								</button>
							</div>
						</div>
					</footer>
					<?php echo form_close(); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- MODAL FOR BRANCH ASSIGN START HERE-->
<div class="zoom-anim-dialog modal-block modal-block-xl mfp-hide" id="modal">
	<section class="panel">
		<header class="panel-heading d-flex justify-content-between align-items-center">
			<h4 class="panel-title mb-0">
				<i class="fas fa-check-circle"></i> <?php echo translate('Assign Branch'); ?>
				<small class="text-muted d-block">Select branches to assign selected questions</small>
			</h4>
			<div>
				<div class="form-check mb-0">
					<input class="form-check-input" type="checkbox" id="selectAllBranches">
					<label class="form-check-label" for="selectAllBranches">
						<?php echo translate('Select All Branches'); ?>
					</label>
				</div>
			</div>
		</header>

		<div class="panel-body">
			<div class="table-responsive">
				<table id="branchTable" class="table table-bordered table-striped mb-none" width="100%">
					<input type="hidden" name="exam_id" id="assign_exam_id" value="">
					<thead>
						<tr>
							<th style="width: 50px;">#</th>
							<th><?php echo translate('Branch Name'); ?></th>
							<th class="text-center"><?php echo translate('Assign'); ?></th>
						</tr>
					</thead>
					<tbody>
						<!-- Dynamic rows will be appended here -->
					</tbody>
				</table>
			</div>
		</div>

		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button class="btn btn-default modal-dismiss">
						<i class="fas fa-times"></i> <?php echo translate('close'); ?>
					</button>
					<button type="submit" class="btn btn-primary" id="assignBranchModalBtn">
						<i class="fas fa-check"></i> <?php echo translate('Assign Branch'); ?>
					</button>
				</div>
			</div>
		</footer>
	</section>
</div>

<!-- MODAL FOR BRANCH ASSIGN END HERE-->

<script type="text/javascript">
	$(document).ready(function () {
		// initiate Datatable
		initDatatable('.exam-list', 'onlineexam/getExamListDT', {}, 25);
		$('#class_id').on('change', function () {
			var classID = $(this).val();
			$.ajax({
				url: base_url + 'onlineexam/getByClass',
				type: 'POST',
				data: {
					classID: classID
				},
				success: function (data) {
					$('#subject_id').html(data);
				}
			});
		});

		$('#assignBranchModalBtn').on('click', function () {
			var examID = $('#assign_exam_id').val();
			var branches = [];
			$('#branchTable input[name="branches[]"]:checked').each(function () {
				branches.push($(this).val());
			});

			$.ajax({
				url: base_url + 'onlineexam/assignBranchToExam',
				type: 'POST',
				data: { exam_id: examID, branches: branches },
				dataType: 'json',
				success: function (response) {
					console.log("re++s", response);
					if (response.status === 'success') {
						swal({
							toast: true,
							position: 'top-end',
							title: response.message,
							showConfirmButton: false,
							timer: 8000
						});
						$.magnificPopup.close();
						$('#search-btn').trigger('click');
					} else {
						alert('Failed to assign branches.');
					}
				},
				error: function () {
					Swal.fire({
						title: 'Error',
						text: 'An unexpected error occurred.',
						confirmButtonText: 'OK'
					});
				}
			});
		});


	});

	function confirmModal(publish_url) {
		swal({
			title: "Are You Sure?",
			text: "<?= translate('make') . ' ' . translate('result_publish'); ?>",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn btn-default swal2-btn-default",
			cancelButtonClass: "btn btn-default swal2-btn-default",
			confirmButtonText: "Yes, Continue",
			cancelButtonText: "Cancel",
			buttonsStyling: false,
		}).then((result) => {
			if (result.value) {
				$.ajax({
					url: publish_url,
					type: "POST",
					success: function (data) {
						swal({
							title: "Deleted",
							text: "Successfully result publish.",
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

	function shareExamLink(url) {
		const fullUrl = encodeURIComponent(url);
		const shareOptions = `
		<div style="text-align:left;">
			<p><strong>Exam Link:</strong><br><input type="text" value="${url}" id="examLinkInput" style="width:100%;" readonly /></p>
			<button class="btn btn-primary" onclick="copyExamLink()">Copy Link</button>
		</div>
	`;

		bootbox.dialog({
			title: "Share Exam Link",
			message: shareOptions
		});
	}

	function copyExamLink() {
		var copyText = document.getElementById("examLinkInput");
		copyText.select();
		copyText.setSelectionRange(0, 99999);
		document.execCommand("copy");
		Swal({
			title: 'Copied!',
			text: 'Link copied to clipboard',
			timer: 1500,
			buttonsStyling: false,
			showCloseButton: true,
			focusConfirm: false,
			confirmButtonClass: "btn btn-default swal2-btn-default",
			type: "success"
		});

	}

	// BRANCH ASSIGN START HERE
	function openAssignBranchModal($id) {
		console.log("assign branch called", $id);

		if ($id) {
			$.ajax({
				url: base_url + 'onlineexam/getBrancheswithExamAssignment',
				method: "POST",
				data: { exam_id: $id },
				success: function (response) {
					$('#branchTable tbody').html(response);
					$('#assign_exam_id').val($id);
				},
				error: function () {
					alert('Error loading branch data.');
				}
			});
		}

		mfp_modal('#modal');
	}
	// BRANCH ASSIGN END HERE

</script>