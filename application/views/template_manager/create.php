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
                        <div id="uploadProgressWrap" style="display:none;margin-top:10px;">
                            <div class="progress">
                                <div id="uploadProgress"
                                    class="progress-bar progress-bar-striped active"
                                    role="progressbar"
                                    style="width:0%">
                                    0%
                                </div>
                            </div>
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

<script>
    $(function() {

        const CHUNK_SIZE = 5 * 1024 * 1024; // 5MB
        const progressWrap = $('#uploadProgressWrap');
        const progressBar = $('#uploadProgress');

        function toast(type, message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 8000
            });
        }

        $('#templateUploadForm').on('submit', function(e) {
            e.preventDefault();

            const type = $('#type').val();
            const file = document.getElementById('template_file').files[0];

            if (!file) {
                toast('error', 'Please select a file');
                return;
            }

            if (type === 'image') {
                normalUpload(this);
            } else {
                startVideoUpload(file);
            }
        });

        /* ---------- IMAGE UPLOAD ---------- */
        function normalUpload(form) {
            $.ajax({
                url: "<?= base_url('Template_manager/storeAssets') ?>",
                type: "POST",
                data: new FormData(form),
                dataType: 'json',
                contentType: false,
                processData: false,
                success(res) {
                    if (res.status === 'success') {
                        toast('success', res.message);
                        setTimeout(() => location.href = res.url, 1200);
                    } else {
                        toast('error', res.message);
                    }
                }
            });
        }

        /* ---------- VIDEO INIT ---------- */
        function startVideoUpload(file) {

            toast('info', 'Initializing video upload…');

            $.ajax({
                url: "<?= base_url('Template_manager/storeAssets') ?>",
                type: "POST",
                dataType: 'json',
                data: {
                    title: $('input[name="title"]').val(),
                    type: 'video',
                    file_size: file.size
                },
                success(res) {
                    if (res.status !== 'success') {
                        toast('error', res.message);
                        return;
                    }

                    progressWrap.show();
                    uploadChunks(file, res.template_id);
                }
            });
        }

        /* ---------- CHUNK UPLOAD ---------- */
        function uploadChunks(file, templateId) {

            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            let currentChunk = 0;

            function sendNextChunk() {

                const start = currentChunk * CHUNK_SIZE;
                const chunk = file.slice(start, start + CHUNK_SIZE);

                const fd = new FormData();
                fd.append('chunk', chunk);
                fd.append('template_id', templateId);
                fd.append('chunk_index', currentChunk);
                fd.append('total_chunks', totalChunks);

                $.ajax({
                    url: "<?= base_url('Template_manager/uploadVideoChunk') ?>",
                    type: "POST",
                    data: fd,
                    contentType: false,
                    processData: false,
                    success() {
                        currentChunk++;

                        const percent = Math.round((currentChunk / totalChunks) * 100);
                        progressBar
                            .css('width', percent + '%')
                            .text(percent + '%');

                        if (currentChunk < totalChunks) {
                            sendNextChunk();
                        } else {
                            toast('success', 'Video uploaded successfully');
                            setTimeout(() => {
                                location.href = "<?= base_url('Template_manager') ?>";
                            }, 1500);
                        }
                    },
                    error() {
                        toast('error', 'Chunk upload failed');
                    }
                });
            }

            sendNextChunk();
        }
    });
</script>