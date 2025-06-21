<section class="panel">
	<div class="tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#list" data-toggle="tab"><i class="fas fa-list-ul"></i> <?= translate('subject_list') ?></a>
			</li>
			<?php if (get_permission('subject', 'is_add')): ?>
				<li>
					<a href="#create" data-toggle="tab"><i class="far fa-edit"></i> <?= translate('create_subject') ?></a>
				</li>
			<?php endif; ?>
		</ul>
		<div class="tab-content">
			<div id="list" class="tab-pane active">
				<table class="table table-bordered table-hover mb-none table-export">
					<thead>
						<tr>
							<th width="60"><?= translate('sl') ?></th>
							<th><?= translate('branch') ?></th>
							<th><?= translate('subject_name') ?></th>
							<th><?= translate('subject_code') ?></th>
							<th><?= translate('subject_type') ?></th>
							<th><?= translate('subject_author') ?></th>
							<th><?= translate('action') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = 1;
						foreach ($subjectlist as $row):
							?>
							<tr>
								<td><?php echo $count++; ?></td>
								<td><?php echo ($row['created_by_branch'] == NULL) ? 'Global' : get_type_name_by_id('branch', $row['created_by_branch']); ?>
								</td>
								<td><?php echo $row['name']; ?></td>
								<td><?php echo $row['subject_code']; ?></td>
								<td><?php echo $row['subject_type']; ?></td>
								<td><?php echo $row['subject_author']; ?></td>
								<td>
									<?php
									$is_global = ($row['created_by_branch'] == NULL);
									if (get_permission('subject', 'is_edit')):
										if (!$is_global || is_superadmin_loggedin()): ?>
											<a href="<?php echo base_url('subject/edit/' . $row['id']); ?>"
												class="btn btn-circle btn-default icon"
												title="<?= !$is_global ? translate('edit') : (is_superadmin_loggedin() ? translate('edit') : translate('cannot_edit_global')) ?>"
												<?= ($is_global && !is_superadmin_loggedin()) ? 'disabled style="pointer-events: none; opacity: 0.6;"' : '' ?>>
												<i class="fas fa-pen-nib"></i>
											</a>
										<?php endif; endif;

									if (get_permission('subject', 'is_delete')):
										if (!$is_global || is_superadmin_loggedin()): ?>
											<?php echo btn_delete('subject/delete/' . $row['id']); ?>
										<?php endif; endif; ?>
								</td>

							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php if (get_permission('subject', 'is_add')): ?>
				<div class="tab-pane" id="create">
					<?php echo form_open('subject/save', array('class' => 'form-horizontal form-bordered frm-submit')); ?>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('subject_name') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="name" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('subject_code') ?> <span
								class="required">*</span></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="subject_code" />
							<span class="error"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('subject_author') ?></label>
						<div class="col-md-6">
							<input type="text" class="form-control" name="subject_author" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label"><?= translate('subject_type') ?> <span
								class="required">*</span></label>
						<div class="col-md-6 mb-md">
							<?php
							$subjectArray = array(
								'Theory' => 'Theory',
								'Practical' => 'Practical',
								'Optional' => 'Optional',
								'Mandatory' => 'Mandatory'
							);
							echo form_dropdown("subject_type", $subjectArray, set_value("subject_type"), "class='form-control populate' data-plugin-selectTwo data-width='100%'
							data-minimum-results-for-search='Infinity' ");
							?>
							<span class="error"></span>
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