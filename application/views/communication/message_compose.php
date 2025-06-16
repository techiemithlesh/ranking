<div class="panel">
	<div class="panel-heading">
		<h4 class="panel-title"><?= translate('write_message') ?></h4>
	</div>
	<?php echo form_open_multipart('communication/message_send', array('class' => 'frm-submit-data')); ?>
	<div class="panel-body">
		<?php if (is_superadmin_loggedin()) { ?>
			<div class="form-group">
				<label class="control-label"><?= translate('branch') ?> <span class="required">*</span></label>
				<?php
				$arrayBranch = $this->app_lib->getSelectList('branch');
				echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branchID'
                    data-plugin-selectTwo data-width='100%' data-placeHolder='Select Branch'");
				?>
				<span class="error"></span>
			</div>
		<?php } ?>
		<div class="form-group">
			<label class="control-label"><?= translate('role') ?> <span class="required">*</span></label>
			<?php
			$role_list = $this->app_lib->getRolesForMsg(1);
			echo form_dropdown("role_id", $role_list, set_value('role_id'), "class='form-control' data-plugin-selectTwo id='roleID'
                    data-width='100%' data-minimum-results-for-search='Infinity' ");
			?>
			<span class="error"></span>
		</div>
		<div class="form-group class_div" <?php if (empty($class_id)) { ?> style="display: none" <?php } ?>>
			<label class="control-label"><?= translate('class') ?> <span class="required">*</span></label>
			<?php
			$arrayClass = $this->app_lib->getClass($branch_id);
			echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' data-plugin-selectTwo
					data-width='100%' data-minimum-results-for-search='Infinity' ");
			?>
			<span class="error"></span>
		</div>
		<div class="form-group">
			<label class="control-label"><?= translate('receiver') ?> <span
					class="required recived_required">*</span></label>
			<?php if (!is_student_loggedin()): ?>
				<div class="input-group mb-sm">
					<div class="input-group-addon p-xs">
						<input type="checkbox" name="send_to_all" id="send_to_all" value="1">
					</div>
					<input type="text" class="form-control" readonly
						value="<?= translate('send_to_all_in_selected_role') ?>">
				</div>
			<?php endif; ?>

			<?php
			$arrayUser = array("" => translate('select'));
			echo form_dropdown("receiver_id", $arrayUser, set_value('receiver_id'), "class='form-control' id='receiverID' data-plugin-selectTwo data-width='100%'
					data-minimum-results-for-search='Infinity' ");
			?>
			<span class="error"></span>
		</div>
		<div class="form-group">
			<label class="control-label"><?= translate('subject') ?> <span class="required">*</span></label>
			<input id="subject" name="subject" type="text" class="form-control" value="">
			<span class="error"></span>
		</div>
		<div class="form-group">
			<label class="control-label"><?= translate('message') ?> <span class="required">*</span></label>
			<textarea name="message_body" class="form-control summernote" id="summernote" rows="10"></textarea>
			<span class="error"></span>
		</div>

		<div class="form-group">
			<label class="control-label">Attachment File</label>
			<div class="col-md-12 row">
				<div class="fileupload fileupload-new" data-provides="fileupload">
					<div class="input-append">
						<div class="uneditable-input">
							<i class="fas fa-file fileupload-exists"></i>
							<span class="fileupload-preview"></span>
						</div>
						<span class="btn btn-default btn-file">
							<span class="fileupload-exists">Change</span>
							<span class="fileupload-new">Select file</span>
							<input type="file" name="attachment_file" />
						</span>
						<a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">Remove</a>
					</div>
				</div>
				<span class="error"></span>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="row">
			<div class="col-md-12">
				<div class="pull-right">
					<button type="reset" class="btn btn-default">
						<i class="fas fa-times"></i><span> <?= translate('discard') ?></span>
					</button>
					<button type="submit" name="submit" value="send" class="btn btn-default"
						data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
						<i class="fas fa-paper-plane"></i><span> <?= translate('send') ?></span>
					</button>
				</div>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$(document).on('change', '#branchID', function () {
			var branchID = $(this).val();
			getClassByBranch(branchID);
			$('#roleID').val('').trigger('change.select2');
			$('#receiverID').empty().html("<option value=''><?= translate('select_user') ?>");
		});

		$(document).on('change', '#roleID', function () {
			var roleID = $(this).val();
			var branchID = $('#branchID').val();
			if (roleID == 6) {
				$.ajax({
					url: base_url + "communication/getParentListBranch",
					type: 'POST',
					data: {
						branch_id: branchID
					},
					success: function (data) {
						$('#receiverID').html(data);
					}
				});
				$(".class_div").hide(400);
			} else if (roleID == 7) {
				$(".class_div").show(400);;
				$('#receiverID').empty().html("<option value=''><?= translate('select_user') ?>");
			} else {
				$(".class_div").hide(400);
				$.ajax({
					url: base_url + "communication/getStafflistRole",
					type: 'POST',
					data: {
						branch_id: branchID,
						role_id: roleID
					},
					success: function (data) {
						$('#receiverID').html(data);
					}
				});
			}
		});

		$(document).on('change', '#class_id', function () {
			var classID = $(this).val();
			var branchID = $('#branchID').val();
			$.ajax({
				url: base_url + "communication/getStudentByClass",
				type: 'POST',
				data: {
					branch_id: branchID,
					class_id: classID
				},
				success: function (data) {
					$('#receiverID').html(data);
				}
			});
		});


		$('#send_to_all').on('change', function () {
			if ($(this).is(':checked')) {
				$('#receiverID').val('').trigger('change');
				$('#receiverID').prop('disabled', true);
				$('.recived_required').hide();
				$('#receiverID').removeAttr('required');
				$('#receiverID').find('option[value="all"]').remove();

				$('#receiverID').append('<option value="all" selected>All users in selected role</option>');
			} else {
				$('#receiverID').prop('disabled', false);
				// $('#receiverID option[value="all"]').remove();
				$('.recived_required').show(); // Shows the red asterisk again
				$('#receiverID').attr('required', 'required');
				$('#receiverID option[value="all"]').remove();
			}
		});
	});



</script>