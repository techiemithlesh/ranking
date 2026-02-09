<section class="panel">
    <header class="panel-heading">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h4 class="panel-title" style="margin:0;">
                <?= translate('edit_template') ?> : <?= html_escape($template['title']) ?>
            </h4>
            <a href="<?= base_url('Template_manager') ?>" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> <?= translate('back') ?>
            </a>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">

            <!-- VIDEO EDITOR -->
            <div class="col-md-8">
                <div id="editor-canvas">
                    <video id="videoPlayer" controls>
                        <source src="<?= base_url($video['file_path']) ?>" type="video/mp4">
                    </video>

                    <div id="overlay-layer"></div>
                </div>
            </div>

            <!-- RIGHT PANEL (empty for now) -->
            <div class="col-md-4">
                <div class="alert alert-info">
                    Phase 3 – Step 1<br>
                    Overlay preview engine
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    const overlays = <?= $overlays_json ?>;
</script>
<script src="<?= base_url('assets/js/video-editor.js') ?>"></script>
