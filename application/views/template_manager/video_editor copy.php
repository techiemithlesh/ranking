<section class="panel">
    <header class="panel-heading">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h4 class="panel-title">
                <?= translate('edit_template') ?> : <?= html_escape($template['title']) ?>
            </h4>
            <a href="<?= base_url('Template_manager') ?>" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> <?= translate('back') ?>
            </a>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">

            <!-- VIDEO AREA -->
            <div class="col-md-8">
                <div id="editor-canvas">
                    <video id="videoPlayer" controls>
                        <source src="<?= base_url($video['file_path']) ?>" type="video/mp4">
                    </video>
                    <div id="overlay-layer"></div>
                </div>
            </div>

            <!-- RIGHT TOOL PANEL -->
            <div class="col-md-4">
                <div class="editor-tools">
                    <h5 class="mb-3"><i class="fas fa-layer-group"></i> Add Overlay</h5>
                    <div class="row no-gutters">
                        <div class="col-6 p-1"><button class="btn btn-sm btn-primary add-overlay" data-type="logo">➕ Logo</button></div>
                        <div class="col-6 p-1"><button class="btn btn-sm btn-primary add-overlay" data-type="branch_name">➕ Name</button></div>
                        <div class="col-6 p-1"><button class="btn btn-sm btn-primary add-overlay" data-type="branch_address">➕ Address</button></div>
                        <div class="col-6 p-1"><button class="btn btn-sm btn-primary add-overlay" data-type="branch_contact">➕ Contact</button></div>
                    </div>

                    <hr>

                    <h5><i class="fas fa-paint-brush"></i> Text Style</h5>
                    <div class="style-group">
                        <div class="row">
                            <div class="col-6">
                                <label>Text Color</label>
                                <input type="color" id="textColor" class="form-control">
                            </div>
                            <div class="col-6">
                                <label>Background</label>
                                <input type="color" id="bgColor" class="form-control">
                            </div>
                        </div>

                        <label class="mt-2">Padding</label>
                        <input type="range" min="0" max="30" id="padding" class="custom-range">

                        <label class="mt-2">Corner Radius</label>
                        <input type="range" min="0" max="30" id="radius" class="custom-range">
                    </div>

                    <div id="overlay-settings" class="style-group">
                        <p class="text-muted text-center">Select an overlay on the video to edit timing</p>
                    </div>

                    <button type="button" class="btn btn-success btn-block shadow-sm" data-template-id="<?= $template['id'] ?>" id="saveTemplateBtn">
                        <i class="fas fa-save"></i> <?= translate('save_template') ?>
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    const overlays = <?= $overlays_json ?>;
    const DEMO_LOGO = "<?= base_url('assets/images/logo.jpg') ?>";
</script>
<script src="<?= base_url('assets/js/video-editor.js') ?>"></script>