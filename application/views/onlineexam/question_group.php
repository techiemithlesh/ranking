<div class="row">
	<?php if (get_permission('question_group', 'is_add')): ?>
		<div class="col-md-5">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="far fa-edit"></i>
						<?php echo translate('add') . " " . translate('group'); ?></h4>
				</header>
				<?php echo form_open($this->uri->uri_string()); ?>
				<div class="panel-body">
					<div class="form-group mb-md">
						<label class="control-label"><?php echo translate('group') . " " . translate('name'); ?> <span
								class="required">*</span></label>
						<input type="text" class="form-control" name="group_name"
							value="<?php echo set_value('group_name'); ?>" />
						<span class="error"><?php echo form_error('group_name'); ?></span>
					</div>
				</div>
				<div class="panel-footer">
					<div class="row">
						<div class="col-md-12">
							<button class="btn btn-default pull-right" type="submit" name="group" value="1"><i
									class="fas fa-plus-circle"></i> <?php echo translate('save'); ?></button>
						</div>
					</div>
				</div>
				<?php echo form_close(); ?>
			</section>
		</div>
	<?php endif; ?>
	<?php if (get_permission('question_group', 'is_view')): ?>
		<div class="col-md-<?php if (get_permission('question_group', 'is_add')) {
			echo "7";
		} else {
			echo "12";
		} ?>">
			<section class="panel">
				<header class="panel-heading">
					<h4 class="panel-title"><i class="fas fa-list-ul"></i>
						<?php echo translate('group') . " " . translate('list'); ?></h4>
				</header>

				<div class="panel-body">
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-condensed mb-none">
							<thead>
								<tr>
									<th><?= translate('branch') ?></th>
									<th><?php echo translate('name'); ?></th>
									<th><?php echo translate('group') . " " . translate('id'); ?></th>
									<th><?php echo translate('action'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php

								if (!empty($categorylist)) {
									foreach ($categorylist as $row):
										?>
										<tr>
											<td><?= $row['branch_name'] ?></td>
											<td><?= $row['name']; ?></td>
											<td><?= $row['id']; ?></td>
											<td class="action">
												<?php if (get_permission('question_group', 'is_edit')): ?>
													<?php if (!$is_global || is_superadmin_loggedin()): ?>
														<a href="javascript:void(0);" onclick="getQuestionGroup('<?= $row['id']; ?>')"
															class="btn btn-circle btn-default icon"
															title="<?= !$is_global ? translate('edit') : (is_superadmin_loggedin() ? translate('edit') : translate('cannot_edit_global')) ?>"
															<?= ($is_global && !is_superadmin_loggedin()) ? 'disabled style="pointer-events: none; opacity: 0.6;"' : '' ?>>
															<i class="fas fa-pen-nib"></i>
														</a>
													<?php endif; ?>
												<?php endif; ?>

												<?php if (get_permission('question_group', 'is_delete')): ?>
													<?php if (!$is_global || is_superadmin_loggedin()): ?>
														<?= btn_delete('onlineexam/group_delete/' . $row['id']); ?>
													<?php endif; ?>
												<?php endif; ?>
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
<?php endif; ?>
<?php if (get_permission('question_group', 'is_edit')): ?>
	<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
		<section class="panel">
			<header class="panel-heading">
				<h4 class="panel-title">
					<i class="far fa-edit"></i> <?php echo translate('edit') . " " . translate('category'); ?>
				</h4>
			</header>
			<?php echo form_open('onlineexam/group_edit', array('class' => 'frm-submit')); ?>
			<div class="panel-body">
				<input type="hidden" name="group_id" id="egroup_id" value="">
				<div class="form-group mb-md">
					<label class="control-label"><?php echo translate('group') . " " . translate('name'); ?> <span
							class="required">*</span></label>
					<input type="text" class="form-control" value="" name="group_name" id="egroup_name" />
					<span class="error"></span>
				</div>
			</div>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-md-12 text-right">
						<button type="submit" class="btn btn-default"
							data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
							<i class="fas fa-plus-circle"></i> <?php echo translate('update'); ?>
						</button>
						<button class="btn btn-default modal-dismiss"><?php echo translate('cancel'); ?></button>
					</div>
				</div>
			</footer>
			<?php echo form_close(); ?>
		</section>
	</div>
<?php endif; ?>