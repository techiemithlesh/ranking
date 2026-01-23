<?php $widget = (is_superadmin_loggedin() ? 6 : 6); ?>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <h4 class="panel-title" style="margin:0;"><?= translate('create_template') ?></h4>
                    <a href="<?= base_url('TemplateManager') ?>" class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left"></i> <?= translate('back') ?>
                    </a>
                </div>
            </header>

            <?php echo form_open_multipart('#', array('class' => 'validate', 'id' => 'templateUploadForm')); ?>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('title') ?> <span class="required">*</span></label>
                            <input type="text" class="form-control" name="title" value="<?= set_value('title') ?>" required>
                        </div>
                    </div>

                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('template_type') ?> <span class="required">*</span></label>
                            <?php
                            $typeOptions = [
                                "image" => "Pamphlet (Image)",
                                "video" => "Video",
                            ];
                            echo form_dropdown(
                                "type",
                                $typeOptions,
                                set_value('type', 'image'),
                                "class='form-control' id='type' required data-plugin-selectTwo data-width='100%'"
                            );
                            ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">
                                <?= translate('template_file') ?> <span class="required">*</span>
                            </label>
                            <input type="file" name="template_file" id="template_file" class="form-control" required>
                            <small class="text-muted" id="fileHint">
                                Upload JPG or PNG.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-4 col-md-offset-8 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> <?= translate('upload') ?>
                        </button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#type').on('change', function() {
            var type = $(this).val();
            $('#template_file').val('');

            if (type === 'image') {
                $('#template_file').attr('accept', 'image/*');
                $('#fileHint').text('Upload JPG or PNG.');
            } else {
                $('#template_file').attr('accept', 'video/mp4');
                $('#fileHint').text('Upload MP4 video.');
            }
        });


        $('#type').trigger('change');

        // AJAX Submission
        $('#templateUploadForm').on('submit', function(e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');

            $.ajax({
                url: "<?= base_url('Template_manager/storeAssets') ?>",
                type: "POST",
                data: new FormData(this),
                dataType: "json",
                contentType: false,
                processData: false,
                beforeSend: function() {
                    btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <?= translate("processing") ?>...');
                },
                success: function(data) {
                    // console.log("data", data);
                    if (data.status == 'success') {
                        $('#templateUploadForm')[0].reset();
                        Swal.fire({
                            title: 'Success!',
                            text: data.message || "Template Uploaded Sucessfully !",
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6'
                        }).then((result) => {
                            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                                window.location.replace(data.url);
                            }
                        });

                        setTimeout(function() {
                            window.location.href = data.url;
                        }, 3000);
                    
                    } else {

                        swal("<?= translate('error') ?>", data.message, "error");
                        btn.attr('disabled', false).html('<i class="fas fa-upload"></i> <?= translate("upload") ?>');
                    }
                },
                error: function(xhr) {
                    console.log('Upload error:', xhr.status, xhr.responseText);
                    swal("<?= translate('error') ?>", "Server connection failed", "error");
                    btn.attr('disabled', false).html('<i class="fas fa-upload"></i> <?= translate("upload") ?>');
                }
            });
        });
    });
</script>