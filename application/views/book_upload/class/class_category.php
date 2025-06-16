<div class="row">

	<div class="col-md-5">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="far fa-edit"></i>
					<?= translate('add') . " " . translate('class_category') ?></h4>
			</header>
			<?php echo form_open($this->uri->uri_string()); ?>
			<div class="panel-body">
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


	<div class="col-md-7">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title"><i class="fas fa-list-ul"></i>
					<?= translate('class_category') . " " . translate('list') ?></h4>
			</header>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-condensed mb-none">
						<thead>
							<tr>
								<th><?= translate('sl') ?></th>
								<th><?= translate('name') ?></th>
								<th><?= translate('action') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$count = 1;
							if (count($class_category)) {
								foreach ($class_category as $row):
									?>
									<tr>
										<td><?php echo $count++; ?></td>
										<td><?php echo $row['name']; ?></td>
										<td>
											<a class="btn btn-default btn-circle icon" href="javascript:void(0);"
												onclick="openModal(this)" data-id="<?= $row['id'] ?>" data-category-name="<?= $row['name'] ?>">
												<i class="fas fa-pen-nib"></i>
											</a>

											<!-- delete link -->
											<?php echo btn_delete('StudentBookUpload/bookClassCategoryDelete/' . $row['id']); ?>
										</td>
									</tr>
									<?php
								endforeach;
							} else {
								echo '<tr><td colspan="4"><h5 class="text-danger text-center">' . translate('no_information_available') . '</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
</div>


<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
	<section class="panel">
		<?php echo form_open('StudentBookUpload/classCategory_edit', array('class' => 'frm-submit')); ?>
		<header class="panel-heading">
			<h4 class="panel-title"><i class="far fa-edit"></i>
				<?= translate('edit') . " " . translate('class_category') ?></h4>
		</header>
		<div class="panel-body">
			<?php if (isset($error['category_id'])): ?>
				<span class="error text-danger"><?= $error['category_id'] ?></span>
			<?php endif; ?>
			<input type="hidden" name="category_id" id="category_id" value="" />

			<div class="form-group mb-md">
				<label class="control-label"><?= translate('name') ?> <span class="required">*</span></label>
				<input type="text" class="form-control" name="category_name" id="category_name" value="" />
				<span class="error"><?= form_error('category_name') ?></span>
			</div>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-md-12 text-right">
					<button type="submit" class="btn btn-default"
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

<script>
	function openModal(element) {
   
    const categoryId = $(element).data('id');
	const categoryName = $(element).data('category-name');

    $('#category_id').val(categoryId);
    $('#category_name').val(categoryName);
    
    $.magnificPopup.open({
        items: {
            src: '#modal',
            type: 'inline'
        },
        modal: true
    });

}

</script>