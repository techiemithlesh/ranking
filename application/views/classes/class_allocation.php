<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="<?= base_url('classes') ?>"><i class="fas fa-graduation-cap"></i> <?= translate('class') ?></a>
			</li>
			<?php if (get_permission('section', 'is_view')): ?>
				<li>
					<a href="<?= base_url('sections') ?>"><i class="fas fa-award"></i> <?= translate('section') ?></a>
				</li>
			<?php endif; ?>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active">
				<div class="row">
					<?php if (get_permission('classes', 'is_add')): ?>
						<div class="col-md-5 pr-xs">
							<section class="panel panel-custom">
								<div class="panel-heading panel-heading-custom">
									<h4 class="panel-title"><i class="far fa-edit"></i> <?= translate('create_class') ?>
									</h4>
								</div>
								<?php echo form_open($this->uri->uri_string(), array('class' => 'frm-submit')); ?>
								<div class="panel-body panel-body-custom">

									<div class="form-group">
										<label class="control-label"><?= translate('name') ?> <span
												class="required">*</span></label>
										<input type="text" class="form-control" name="name" value="" />
										<span class="error"></span>
									</div>


								</div>
								<footer class="panel-footer panel-footer-custom">
									<div class="text-right">
										<button type="submit" class="btn btn-default"
											data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
											<i class="fas fa-plus-circle"></i> <?= translate('save') ?>
										</button>
									</div>
								</footer>
								<?php echo form_close(); ?>
							</section>
						</div>
					<?php endif; ?>
					<div class="col-md-<?php if (get_permission('classes', 'is_add')) {
						echo "7 pl-xs";
					} else {
						echo "12";
					} ?>">
						<section class="panel panel-custom">
							<header class="panel-heading panel-heading-custom">
								<h4 class="panel-title"><i class="fas fa-list-ul"></i> <?= translate('class_list') ?>
								</h4>
							</header>
							<div class="panel-body panel-body-custom">
								<div class="table-responsive">
									<table
										class="table table-bordered table-hover table-condensed tbr-top mb-none dataTable">
										<thead>
											<tr>
												<th>#</th>
												<th><?= translate('class_name') ?></th>
												<th><?= translate('action') ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$count = 1;
											if (count($classlist)) {
												foreach ($classlist as $row):
													?>
													<tr>
														<td><?php echo $count++; ?></td>
														<td><?php echo $row['name']; ?></td>

														<td>
															<?php if (get_permission('classes', 'is_edit')): ?>
																<!--update link-->
																<a href="javascript:void(0);"
																	class="btn btn-default btn-circle icon"
																	data-name="<?= $row['name'] ?>" data-id="<?= $row['id'] ?>"
																	id="editBtn">
																	<i class="fas fa-pen-nib"></i>
																</a>
															<?php endif;
															if (get_permission('classes', 'is_delete')): ?>
																<!--delete link-->
																<?php echo btn_delete('classes/delete/' . $row['id']); ?>
															<?php endif; ?>
														</td>
													</tr>
													<?php
												endforeach;
											} else {
												echo '<tr><td colspan="6"><h5 class="text-danger text-center">' . translate('no_information_available') . '</td></tr>';
											}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</section>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- EDIT MODAL CLASS -->

<?php if (get_permission('classes', 'is_edit')) {
	?>
	<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
		<section class="panel">
			<?php echo form_open('class/update', array('class' => 'edit-frm-submit', 'enctype' => 'multipart/form-data')); ?>
			<input type="hidden" name="class_id" id="class_id" value="" />
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-edit"></i>
					<?= translate('edit') . " " . translate('class') ?></h4>
			</header>
			<div class="panel-body">

				<div class="form-group mb-md">
					<label class="control-label"><?= translate('Name') ?> <span class="required">*</span></label>
					<input type="text" class="form-control" value="" name="name" id="class_name">
					<span class="error"></span>
				</div>

			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default mr-xs"
							data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<i class="fas fa-plus-circle"></i> <?= translate('update') ?>
						</button>
						<button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close(); ?>
		</section>
	</div>
	<?php
}

?>

<script type="text/javascript">

	$(document).ready(function () {
		$('.dataTable').dataTable({
			"pagelenght": 10,
		});

	})

	$(document).on('click', '#editBtn', function () {
		getEditModal(this);
	});


	function getEditModal(e) {
		var classId = $(e).data('id');
		var className = $(e).data('name');
		$('#class_id').val(classId);
		$('#class_name').val(className);
		mfp_modal('#modal');
	}

	$('.edit-frm-submit').on('submit', function (e) {
		e.preventDefault();
		var formData = new FormData(this);

		$.ajax({
			url: '<?= base_url('classes/updateClass') ?>',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			dataType: 'json',
			beforeSend: function () {
				$('.edit-frm-submit button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Processing').attr('disabled', true);
				$('.edit-frm-submit .error').html('');
			},
			success: function (data) {
				if (data.status === 'fail') {
					$.each(data.error, function (index, value) {
						$('[name="' + index + '"]').parents(".form-group").find(".error").html(value);
					});
				} else if (data.status === 'success') {
					if (data.url) {
						window.location.href = data.url;
					} else {
						location.reload(true);
					}
				}
			},
			error: function () {
				alert('Something went wrong. Please try again.');
			},
			complete: function () {
				$('.edit-frm-submit button[type="submit"]').html('<i class="fas fa-plus-circle"></i> Update').attr('disabled', false);
			}
		});
	});


</script>