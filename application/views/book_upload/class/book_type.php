<div class="row">
	<!-- Add Book Type Category Form -->
	<div class="col-md-5">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-edit"></i>
					<?= translate('add') . " " . translate('book_type_category') ?>
				</h4>
			</header>
			<?php echo form_open($this->uri->uri_string()); ?>
			<div class="panel-body">
				<div class="form-group">
					<label class="control-label"><?= translate('class_category') ?> <span
							class="required">*</span></label>
					<?php
					$categoryOptions = array_column($class_categories, 'name', 'id');
					echo form_dropdown('class_category_id', $categoryOptions, set_value('class_category_id'), "class='form-control' data-plugin-selectTwo");
					?>
					<span class="error"><?= form_error('class_category_id') ?></span>
				</div>

				<div class="form-group mb-md">
					<label class="control-label"><?= translate('name') ?> <span class="required">*</span></label>
					<input type="text" class="form-control" name="name" value="<?= set_value('name') ?>" />
					<span class="error"><?= form_error('name') ?></span>
				</div>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-md-12">
						<button class="btn btn-default pull-right" type="submit" name="save" value="1">
							<i class="fas fa-plus-circle"></i> <?= translate('save') ?>
						</button>
					</div>
				</div>
			</div>
			<?php echo form_close(); ?>
		</section>
	</div>

	<!-- Book Type Category List -->
	<div class="col-md-7">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-list-ul"></i>
					<?= translate('book_type_category') . " " . translate('list') ?>
				</h4>
			</header>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-condensed mb-none">
						<thead>
							<tr>
								<th><?= translate('sl') ?></th>
								<th><?= translate('class_category') ?></th>
								<th><?= translate('category_name') ?></th>
								<th><?= translate('action') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($book_type_category)): ?>
								<?php foreach ($book_type_category as $index => $row): ?>
									<tr>
										<td><?= $index + 1 ?></td>
										<td><?= $row['class_category_name'] ?></td>
										<td><?= $row['name'] ?></td>
										<td>
											<a class="btn btn-default btn-circle icon" href="javascript:void(0);"
												onclick="openModal(this)" data-id="<?= $row['id'] ?>"
												data-category-name="<?= $row['name'] ?>"
												data-class-category-id="<?= $row['class_category_id'] ?>">
												<i class="fas fa-pen-nib"></i>
											</a>
											<?= btn_delete('StudentBookUpload/bookTypeCategoryDelete/' . $row['id']) ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="3">
										<h5 class="text-danger text-center">
											<?= translate('no_information_available') ?>
										</h5>
									</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>


<!-- Edit Modal -->
<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel">
		
		<?php echo form_open('StudentBookUpload/bookTypeCategoryEdit', ['class' => 'frm-submit']); ?>
		<header class="panel-heading">
			<h4 class="panel-title"><i class="far fa-edit"></i>
				<?= translate('edit') . " " . translate('book_type_category') ?>
			</h4>
		</header>
		<div class="panel-body">
			<input type="hidden" name="category_id" id="category_id" value="<?= $categoryId ?>" />
			<div class="form-group">
				<label class="control-label"><?= translate('class_category') ?> <span class="required">*</span></label>
				<select name="class_category_id" id="modal_class_category_id" class="form-control"
					data-plugin-selectTwo>
					<!-- Options will be populated dynamically -->
				</select>
				<span class="error"><?= form_error('class_category_id') ?></span>
			</div>
			<div class="form-group mb-md">
				<label class="control-label"><?= translate('name') ?> <span class="required">*</span></label>
				<input type="text" class="form-control" name="category_name" id="modal_category_name" value="" />
				<span class="error"><?= form_error('category_name') ?></span>
			</div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button type="submit" class="btn btn-default">
						<i class="fas fa-plus-circle"></i> <?= translate('update') ?>
					</button>
					<button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
				</div>
			</div>
		</footer>
		<?php echo form_close(); ?>
	</section>
</div>


<script>
	function openModal(element) {
		const categoryId = $(element).data('id');
		const categoryName = $(element).data('category-name');
		const classCategoryId = $(element).data('class-category-id');


		console.log('Opening modal with:', {
			categoryId,
			categoryName,
			classCategoryId
		});

		// Populate hidden field and input
		$('#category_id').val(categoryId);
		$('#modal_category_name').val(categoryName);

		// Populate the class_category_id dropdown
		const categoryOptions = <?= json_encode($class_categories) ?>;
		const dropdown = $('#modal_class_category_id');
		dropdown.empty();

		categoryOptions.forEach(category => {
			dropdown.append(
				`<option value="${category.id}" ${classCategoryId == category.id ? 'selected' : ''}>
				${category.name}
			</option>`
			);
		});

		$.magnificPopup.open({
			items: {
				src: '#modal',
				type: 'inline'
			},
			modal: true
		});
	}

</script>