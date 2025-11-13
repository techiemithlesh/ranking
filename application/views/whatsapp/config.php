<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li class="<?php echo (empty(validation_errors()) ? 'active' : ''); ?>">
                <a href="#list" data-toggle="tab"><i class="fas fa-list-ul"></i>
                    <?php echo translate('whatsapp_config_list'); ?></a>
            </li>
            <?php if (get_permission('whatsapp_config', 'is_add')) { ?>
                <li class="<?php echo (!empty(validation_errors()) ? 'active' : ''); ?>">
                    <a href="#create" data-toggle="tab"><i class="far fa-edit"></i>
                        <?php echo translate('config_Add'); ?></a>
                </li>
            <?php } ?>
        </ul>
        <div class="tab-content">
            <div id="list" class="tab-pane <?php echo (empty(validation_errors()) ? 'active' : ''); ?>">
                <table class="table table-bordered table-condensed table-hover mb-none table_default">
                    <thead>
                        <tr>
                            <th><?= translate('sl') ?></th>
                            <?php if (is_superadmin_loggedin()): ?>
                                <th><?= translate('branch') ?></th>
                                <th><?= translate('provider') ?></th>
                            <?php endif; ?>
                            <th><?= translate('instance_id') ?></th>
                            <th><?= translate('access_token') ?></th>
                            <th><?= translate('sender_number'); ?></th>
                            <th><?= translate('alias_name') ?></th>
                            <th><?= translate('country_code') ?></th>
                            <th><?= translate('status') ?></th>
                            <th><?= translate('last_used_at') ?></th>
                            <th><?= translate('created_at') ?></th>
                            <th><?= translate('updated_at') ?></th>
                            <th><?= translate('action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        if (count($configs)) {
                            foreach ($configs as $item) {
                                ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <?php if (is_superadmin_loggedin()): ?>
                                        <td><?= $item['branch_name'] ?? 'NA' ?></td>
                                        <td><?= $item['provider'] ?></td>
                                    <?php endif; ?>
                                    <td><?= $item['instance_id'] ?></td>
                                    <td><?= $item['access_token'] ?></td>
                                    <td><?= $item['sender_number'] ?></td>
                                    <td><?= $item['alias_name'] ?></td>
                                    <td><?= $item['country_code'] ?></td>
                                    <td><?= $item['status'] ?></td>
                                    <td><?= get_nicetime($item['last_used_at']) ?></td>
                                    <td><?= get_nicetime($item['created_at']) ?></td>
                                    <td><?= get_nicetime($item['updated_at']) ?></td>
                                    <td>
                                        <?php if (get_permission('whatsapp_config', 'is_edit')) {
                                            ?>
                                            <a href="<?php echo base_url('whatsapp/edit/' . $item['id']); ?>"
                                                class="btn btn-circle btn-default icon" title="Edit Whatsapp Configuration">
                                                <i class="fas fa-pen-nib"></i>
                                            </a>
                                            <?php
                                        }

                                        ?>
                                        <?php if (get_permission('whatsapp_config', 'is_delete')) { ?>
                                            <?php echo btn_delete_ajax('whatsapp/config_delete/' . $item['id']); ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
            <?php if (get_permission('whatsapp_config', 'is_add')) { ?>
                <div class="tab-pane <?php echo (!empty(validation_errors()) ? 'active' : ''); ?>" id="create">
                    <?php echo form_open_multipart($this->uri->uri_string(), array('class' => 'form-horizontal form-bordered validate')); ?>
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Branch *</label>
                            <div class="col-md-6">
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' data-width='100%' onchange='getClassByBranch(this.value)'
									data-plugin-selectTwo  data-placeHolder='Select Branch'");
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="branch_id" value="<?= $branch_id ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Instance ID *</label>
                        <div class="col-md-6">
                            <input type="text" name="instance_id" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">Access Token *</label>
                        <div class="col-md-6">
                            <input type="text" name="access_token" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group text-center">
                        <button type="button" id="qr_btn" class="btn btn-primary text-center">Show QR</button>
                    </div>

                    <div class="form-group text-center">
                        <div id="qr_box" style="margin-top:20px;"></div>
                    </div>

                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-2">
                                <button type="submit" name="save" value="1" class="btn btn-default btn-block"><i
                                        class="fas fa-plus-circle"></i> <?= translate('save') ?></button>
                            </div>
                        </div>
                    </footer>
                    <?php echo form_close(); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
    <section class="panel" id='quick_view'></section>
</div>

<script type="text/javascript">
    $(document).ready(function () {

    });


</script>