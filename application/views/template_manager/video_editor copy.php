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
                <div id="editor-wrapper" style="position: relative; background: #222; border-radius: 8px; overflow: hidden;">

                    <div id="editor-loader" style="height: 70vh; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white;">
                        <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                        <p>Initialising Video Editor...</p>
                    </div>

                    <div id="editor-container-main" style="display: none; position: relative;">
                        <div id="editor-canvas" style="position: relative; display: flex; justify-content: center; align-items: center;">
                            <video id="videoPlayer" controls style="max-width: 100%; height: auto; display: block;">
                                <source src="<?= base_url($video['file_path']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div id="overlay-layer" style="position: absolute; top: 0; left: 0; z-index: 10;"></div>
                        </div>
                    </div>

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