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

                    <h5>Add Overlay</h5>

                    <button class="btn btn-sm btn-primary add-overlay" data-type="logo">
                        ➕ Logo
                    </button>

                    <button class="btn btn-sm btn-primary add-overlay" data-type="branch_name">
                        ➕ Branch Name
                    </button>

                    <button class="btn btn-sm btn-primary add-overlay" data-type="branch_address">
                        ➕ Branch Address
                    </button>

                    <button class="btn btn-sm btn-primary add-overlay" data-type="branch_contact">
                        ➕ Branch Contact
                    </button>

                    <hr>

                    <hr>
                    <h5>Text Style</h5>

                    <label>Text Color</label>
                    <input type="color" id="textColor">

                    <label>Background</label>
                    <input type="color" id="bgColor">

                    <label>Padding</label>
                    <input type="range" min="0" max="30" id="padding">

                    <label>Radius</label>
                    <input type="range" min="0" max="30" id="radius">


                    <div id="overlay-settings">
                        <p class="text-muted">Select an overlay</p>
                    </div>

                    <button type="button" class="btn btn-success btn-block" id="saveTemplateBtn">
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