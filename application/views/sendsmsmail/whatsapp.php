<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<section class="panel">
    <div class="tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="<?= base_url('sendsmsmail/sms') ?>"> <i class="far fa-envelope"></i> SMS</a></li>
            <li><a href="<?= base_url('sendsmsmail/email') ?>"> <i class="far fa-envelope"></i> Email</a></li>
            <li class="active"><a href="#whatsapp" data-toggle="tab"> <i class="fab fa-whatsapp"></i> WhatsApp</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane box active" id="whatsapp">
                <form id="whatsappForm" class="frm-submit">

                    <div class="row">
                        <div class="col-md-6 mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?= translate('campaign_name') ?> <span
                                        class="required">*</span></label>
                                <input type="text" class="form-control" name="campaign_name" required />
                            </div>
                        </div>
                        <div class="col-md-6 mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?= translate('recipient_number') ?> <span
                                        class="required">*</span></label>
                                <input type="number" class="form-control" name="recipient_number" required />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?= translate('user_name') ?> <span
                                        class="required">*</span></label>
                                <input type="text" class="form-control" name="user_name" required />
                            </div>
                        </div>

                        <div class="col-md-6 mb-sm">
                            <div class="form-group">
                                <label><?= translate('template') ?> <span class="required">*</span></label>
                                <select class="form-control" name="template" required>
                                    <option value="welcome">Template 1 - Greeting</option>
                                    <option value="template2">Template 2 - Reminder</option>
                                    <option value="template3">Template 3 - Thank You</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <footer class="panel-footer">
                        <div class="row">
                            <div class="col-md-offset-10 col-md-2">
                                <button type="submit" class="btn btn-default btn-block"
                                    data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                                    <i class="far fa-share-square"></i> <?= translate('send') ?>
                                </button>
                            </div>
                        </div>
                    </footer>
                </form>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $(document).ready(function () {
        $('#whatsappForm').on('submit', function (e) {
            e.preventDefault();
            var formData = $(this).serialize();

            console.log("Formdata", formData);


            var $submitButton = $(this).find('button[type="submit"]');
            $submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing');

            $.ajax({
                url: '<?= base_url('sendsmsmail/sendWhatsappMessage') ?>',
                type: 'POST',
                data: formData,
                success: function (response) {
                    response = JSON.parse(response);
                    if (response.status === 'success') {
                        alert(response.message);
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert('Failed to send WhatsApp message.');
                },
                complete: function () {
                    // Reset the button text and state
                    $submitButton.prop('disabled', false).html('<i class="far fa-share-square"></i> <?= translate('send') ?>');
                }
            });
        });
    });
</script>