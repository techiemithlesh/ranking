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
                <div id="editor-wrapper" style="position:relative;background:#222;border-radius:8px;overflow:hidden;">

                    <div id="editor-loader" style="height:70vh;display:flex;flex-direction:column;align-items:center;justify-content:center;color:white;">
                        <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                        <p>Initialising Video Editor...</p>
                    </div>

                    <div id="editor-container-main" style="display:none;position:relative;">
                        <div id="editor-canvas" style="position:relative;display:flex;justify-content:center;align-items:center;">
                            <video id="videoPlayer" controls style="max-width:100%;height:auto;display:block;">
                                <source src="<?= base_url($video['file_path']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div id="overlay-layer" style="position:absolute;top:0;left:0;z-index:10;"></div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT TOOL PANEL -->
            <div class="col-md-4">
                <div class="editor-tools">
                    <h5 class="mb-2"><i class="fas fa-layer-group"></i> Add Overlay</h5>
                    <div class="row no-gutters mb-2">
                        <div class="col-6 p-1"><button class="btn btn-sm btn-primary btn-block add-overlay" data-type="logo">➕ Logo</button></div>
                        <div class="col-6 p-1"><button class="btn btn-sm btn-primary btn-block add-overlay" data-type="branch_name">➕ Name</button></div>
                        <div class="col-6 p-1"><button class="btn btn-sm btn-primary btn-block add-overlay" data-type="branch_address">➕ Address</button></div>
                        <div class="col-6 p-1"><button class="btn btn-sm btn-primary btn-block add-overlay" data-type="branch_contact">➕ Contact</button></div>
                    </div>

                    <hr class="my-2">

                    <!-- ── LOGO STYLE SECTION ── -->
                    <div id="logoStyleSection" style="display:none;">
                        <h5 class="mb-2"><i class="fas fa-image"></i> Logo Style</h5>

                        <div class="form-group">
                            <label style="font-size:12px;">Border Radius (px)</label>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <input type="range" id="logoRadius" min="0" max="300" step="1" value="0" class="custom-range" style="flex:1;">
                                <small id="logoRadiusVal" style="min-width:32px;">0px</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-size:12px;">Opacity</label>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <input type="range" id="logoOpacity" min="0.1" max="1" step="0.05" value="1" class="custom-range" style="flex:1;">
                                <small id="logoOpacityVal" style="min-width:32px;">100%</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-size:12px;">Object Fit</label>
                            <select id="logoObjectFit" class="form-control form-control-sm">
                                <option value="contain">Contain</option>
                                <option value="cover">Cover</option>
                                <option value="fill">Fill / Stretch</option>
                                <option value="scale-down">Scale Down</option>
                            </select>
                        </div>

                        <hr class="my-2">
                        <strong style="font-size:12px;">Border</strong>
                        <div class="form-group mt-1">
                            <label style="font-size:12px;">Width (px)</label>
                            <input type="number" id="logoBorderWidth" class="form-control form-control-sm" min="0" max="30" value="0">
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label style="font-size:12px;">Color</label>
                                <input type="color" id="logoBorderColor" value="#ffffff" class="form-control form-control-sm">
                            </div>
                            <!-- <div class="col-6">
                                <label style="font-size:12px;">Style</label>
                                <select id="logoBorderStyle" class="form-control form-control-sm">
                                    <option value="solid">Solid</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="dotted">Dotted</option>
                                    <option value="double">Double</option>
                                </select>
                            </div> -->
                        </div>

                        <hr class="my-2">
                        <!-- <strong style="font-size:12px;">Effects</strong>
                        <div class="form-group mt-1">
                            <label style="font-size:12px;">Box Shadow</label>
                            <select id="logoShadow" class="form-control form-control-sm">
                                <option value="none">None</option>
                                <option value="0 2px 8px rgba(0,0,0,0.3)">Small</option>
                                <option value="0 4px 16px rgba(0,0,0,0.4)">Medium</option>
                                <option value="0 8px 30px rgba(0,0,0,0.5)">Large</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="font-size:12px;">Image Filter</label>
                            <select id="logoFilter" class="form-control form-control-sm">
                                <option value="none">None</option>
                                <option value="grayscale(100%)">Grayscale</option>
                                <option value="sepia(80%)">Sepia</option>
                                <option value="brightness(1.3)">Brighter</option>
                                <option value="brightness(0.7)">Darker</option>
                                <option value="contrast(1.5)">High Contrast</option>
                                <option value="blur(2px)">Blur</option>
                                <option value="drop-shadow(2px 4px 6px black)">Drop Shadow</option>
                            </select>
                        </div> -->
                    </div>

                    <!-- ── TEXT STYLE SECTION ── -->
                    <div id="textStyleSection" style="display:none;">
                        <h5 class="mb-2"><i class="fas fa-font"></i> Text Style</h5>
                        <div class="mb-2">
                            <label style="display:flex;align-items:center;cursor:pointer;font-size:13px;">
                                <input type="checkbox" id="bgEnabled" style="width:16px;height:16px;margin-right:8px;">
                                <strong>Enable Background Box</strong>
                            </label>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label style="font-size:12px;">Text Color</label>
                                <input type="color" id="textColor" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label style="font-size:12px;">BG Color</label>
                                <input type="color" id="bgColor" class="form-control form-control-sm">
                            </div>
                        </div>
                        <label class="mt-2" style="font-size:12px;">Padding</label>
                        <input type="range" min="0" max="30" id="padding" class="custom-range">
                        <label class="mt-1" style="font-size:12px;">Corner Radius</label>
                        <input type="range" min="0" max="30" id="radius" class="custom-range">
                        <p style="font-size:11px;color:#999;margin-top:6px;">
                            ✦ Font size auto-fits to box size
                        </p>
                    </div>

                    <!-- Timing + per-overlay settings injected here -->
                    <div id="overlay-settings">
                        <p class="text-muted text-center mt-2" style="font-size:13px;">
                            Select an overlay on the video to edit its timing
                        </p>
                    </div>

                    <button type="button" class="btn btn-success btn-block shadow-sm mt-2"
                            data-template-id="<?= $template['id'] ?>" id="saveTemplateBtn">
                        <i class="fas fa-save"></i> <?= translate('save_template') ?>
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    const overlays  = <?= $overlays_json ?>;
    const DEMO_LOGO = "<?= base_url('assets/images/logo.jpg') ?>";
</script>
<script src="<?= base_url('assets/js/video-editor.js') ?>"></script>