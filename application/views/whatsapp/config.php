<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#list" data-toggle="tab">
                    <i class="fas fa-list-ul"></i> <?= translate('whatsapp_config_list') ?>
                </a>
            </li>

            <?php if (get_permission('whatsapp_config', 'is_add')): ?>
                <li>
                    <a href="#create" data-toggle="tab">
                        <i class="far fa-edit"></i> <?= translate('config_Add') ?>
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content">

            <!-- LIST TAB -->
            <div id="list" class="tab-pane active">
                <table class="table table-bordered table-condensed table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <?php if (is_superadmin_loggedin()): ?>
                                <th>Branch</th>
                                <th>Provider</th>
                            <?php endif; ?>

                            <th>Instance ID</th>
                            <th>Sender</th>
                            <th>Alias</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Last Used</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        foreach ($configs as $c): ?>
                            <tr>
                                <td><?= $i++ ?></td>

                                <?php if (is_superadmin_loggedin()): ?>
                                    <td><?= $c['branch_name'] ?></td>
                                    <td><?= $c['provider'] ?></td>
                                <?php endif; ?>

                                <td><?= $c['instance_id'] ?></td>
                                <td><?= $c['sender_number'] ?></td>
                                <td><?= $c['alias_name'] ?></td>
                                <td><?= $c['country_code'] ?></td>
                                <td><?= $c['status'] ? 'Active' : 'Inactive' ?></td>
                                <td><?= get_nicetime($c['last_used_at']) ?></td>
                                <td><?= get_nicetime($c['created_at']) ?></td>
                                <td><?= get_nicetime($c['updated_at']) ?></td>

                                <td>
                                    <?php if (get_permission('whatsapp_config', 'is_edit')): ?>
                                        <a href="<?= base_url('whatsapp/edit/' . $c['id']) ?>" class="btn btn-default btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (get_permission('whatsapp_config', 'is_delete')): ?>
                                        <?= btn_delete_ajax('whatsapp/config_delete/' . $c['id']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- CREATE TAB -->
            <?php if (get_permission('whatsapp_config', 'is_add')): ?>
                <div id="create" class="tab-pane">

                    <?php echo form_open($this->uri->uri_string(), ['class' => 'form-horizontal form-bordered validate']); ?>

                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Branch *</label>
                            <div class="col-md-6">
                                <?php
                                echo form_dropdown(
                                    "branch_id",
                                    $this->app_lib->getSelectList('branch'),
                                    '',
                                    "class='form-control' data-plugin-selectTwo 
                                     onchange='loadBranchInstance(this.value)'"
                                );
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="branch_id" value="<?= $branch_id ?>">
                    <?php endif; ?>

                    <!-- Instance ID -->
                    <div class="form-group">
                        <label class="col-md-3 control-label">Instance ID *</label>
                        <div class="col-md-6">
                            <input type="text" id="instance_id" name="instance_id" class="form-control" readonly required>
                        </div>

                        <div class="col-md-3">
                            <button type="button" id="btn_create_instance" class="btn btn-info">Create Instance</button>
                        </div>
                    </div>

                    <!-- Access Token -->
                    <div class="form-group">
                        <label class="col-md-3 control-label">Access Token *</label>
                        <div class="col-md-6">
                            <input type="text" name="access_token" value="<?= get_global_setting('wp_access_token') ?>"
                                class="form-control" readonly required>
                        </div>
                    </div>

                    <!-- QR Button -->
                    <div class="form-group text-center">
                        <button type="button" id="qr_btn" class="btn btn-primary" style="display:none">Show QR</button>
                    </div>

                    <!-- QR BOX -->
                    <div class="form-group text-center">
                        <div id="qr_box" style="margin-top:20px;"></div>
                    </div>

                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-2">
                                <button type="submit" name="save" value="1" class="btn btn-success btn-block">
                                    Save
                                </button>
                            </div>
                        </div>
                    </footer>

                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>


<script type="text/javascript">
function loadBranchInstance(branchId) {
    $("#instance_id").val("");
    $("#qr_box").html("");
    $("#btn_create_instance").show();
    $("#qr_btn").hide();

    $.post("<?= base_url('whatsapp/get_branch_instance') ?>",
        { branch_id: branchId },
        function (res) {
            let data = JSON.parse(res);

            if (data.status == 1) {
                $("#instance_id").val(data.instance_id);
                $("#btn_create_instance").hide();
                $("#qr_btn").show();

                if (data.connected == 1) {
                    $("#qr_box").html('<span class="badge badge-success">Connected</span>');
                } else {
                    $("#qr_box").html('<span class="badge badge-warning">Not Connected</span>');
                }

            } else {
                $("#qr_box").html('<span class="badge badge-danger">No Instance</span>');
            }
        }
    );
}

$("#btn_create_instance").click(function () {
    $.post("<?= base_url('whatsapp/ajax_create_instance') ?>", {},
        function (res) {
            let data = JSON.parse(res);

            if (data.status == 1) {
                $("#instance_id").val(data.instance_id);
                $("#btn_create_instance").hide();
                $("#qr_btn").show();
            } else {
                alert(data.msg);
            }
        }
    );
});

$("#qr_btn").click(function () {
    let instance_id = $("#instance_id").val();

    $("#qr_box").html("Loading QR...");

    $.post("<?= base_url('whatsapp/ajax_get_qr') ?>",
        { instance_id: instance_id },
        function (res) {
            let data = JSON.parse(res);

            if (data.status == 1) {
                $("#qr_box").html(
                    `<img src="${data.qr}" 
                          style="max-width:300px; background:#fff; padding:10px; border-radius:8px;">`
                );
            } else {
                $("#qr_box").html(`<span style="color:red">${data.msg}</span>`);
            }
        }
    );
});
</script>
