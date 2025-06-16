<?php if (is_superadmin_loggedin() || is_admin_loggedin()) { ?>
    <div class="row">
        <div class="col-md-12">
            <section class="panel">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-school"></i>
                        <?= translate('enquiry') . " " . translate('list') ?>
                    </h4>
                </header>
                <div class="panel-body">
                    <table class="table table-bordered table-hover table-condensed mb-none table_default">
                        <thead>
                            <tr>
                                <th width="50"><?= translate('sl') ?></th>
                                <?php if(is_superadmin_loggedin()) {
                                    ?>
                                <th>Branch</th>
                                    <?php
                                } ?>
                               
                                <th><?= translate('name') ?></th>
                                <th><?= translate('email') ?></th>
                                <th><?= translate('phone') ?></th>
                                <th><?= translate('message') ?></th>
                                <th><?= translate('date') ?></th>
                                <th><?= translate('action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 1;
                            foreach ($enquiry as $row): ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <?php if(is_superadmin_loggedin()){
                                        ?>
                                        <td><?= $row['branch_name'] ?? 'NA' ?></td>
                                        <?php
                                    } ?>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td><?php echo substr($row['message'], 0, 50) . '...'; ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($row['created_at'])); ?></td>
                                    <td class="min-w-c">
                                        <!-- View Message Button -->
                                        <button class="btn btn-primary btn-sm view-message"
                                            data-message="<?php echo htmlspecialchars($row['message']); ?>"
                                            data-name="<?php echo $row['name']; ?>">
                                            View Message
                                        </button>

                                        <!-- Delete Button -->
                                        <?php echo btn_delete('Support/delete/' . $row['id']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
<?php } ?>

<!-- View Message Modal -->
<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide" id="messageModal">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-envelope"></i> Message Details</h4>
        </header>
        <div class="panel-body">
            <h5><strong>From:</strong> <span id="messageSender"></span></h5>
            <p id="messageContent"></p>
        </div>
        <footer class="panel-footer">
            <button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
        </footer>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".view-message").click(function () {
            var message = $(this).data("message");
            var name = $(this).data("name");

            $("#messageSender").text(name);
            $("#messageContent").text(message);
            $.magnificPopup.open({
                items: {
                    src: '#messageModal'
                },
                type: 'inline'
            });
        });
    });
</script>