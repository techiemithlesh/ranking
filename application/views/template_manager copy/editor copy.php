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

            <!-- LEFT : CANVAS -->
            <div class="col-md-8">
                <div style="border:1px solid #ddd;background:#fafafa;padding:10px;overflow:auto;">
                    <div id="stage" style="position:relative;display:inline-block;">
                        <img id="baseImg"
                            src="<?= base_url($template['file_path']) ?>"
                            style="display:block;max-width:100%;">
                        <div id="overlayLayer" style="position:absolute;left:0;top:0;"></div>
                    </div>
                </div>

                <div style="margin-top:10px;">
                    <label style="margin-right:12px;">
                        <input type="checkbox" id="toggleSafeArea" checked> Safe Area
                    </label>

                    <button id="btnAdd" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Placement
                    </button>

                    <button type="button" id="btnAddText" class="btn btn-info btn-sm">
                        <i class="fas fa-font"></i> Add Text
                    </button>


                    <button id="btnSave" class="btn btn-success btn-sm">
                        <i class="fas fa-save"></i> Save All
                    </button>

                    <span id="msg" style="margin-left:10px;"></span>
                </div>

                <p class="text-muted" style="margin-top:8px;">
                    Drag / resize logo placements. Positions are stored safely using ratios.
                </p>
            </div>

            <!-- RIGHT : TOOLS -->
            <div class="col-md-4">
                <div class="panel" style="border:1px solid #eee;">

                    <div id="settingsPanel">
                        <div class="panel-heading"><strong>Placements</strong></div>

                        <div class="panel-body">
                            <div id="list"
                                style="border:1px solid #eee;border-radius:6px;max-height:200px;overflow:auto;"></div>

                            <div style="margin:10px 0;">
                                <button id="btnUp" class="btn btn-default btn-xs">⬆</button>
                                <button id="btnDown" class="btn btn-default btn-xs">⬇</button>
                            </div>

                            <hr>

                            <strong>Selected Placement</strong>

                            <div class="form-group">
                                <label>Background</label>
                                <select id="bgEnabled" class="form-control">
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </div>

                            <div id="textTools" style="display:none;">

                                <hr>
                                <strong>Text Settings</strong>

                                <!-- Text Size -->
                                <div class="form-group">
                                    <label>Font Size</label>
                                    <input type="range" id="fontSize"
                                        min="10" max="80" step="1">
                                    <small id="fontSizeVal">24px</small>
                                </div>

                                <!-- Text Color -->
                                <div class="form-group">
                                    <label>Text Color</label>
                                    <input type="color" id="textColor">
                                </div>

                                <!-- Alignment -->
                                <div class="form-group">
                                    <label>Alignment</label><br>
                                    <button type="button" class="btn btn-default btn-xs" data-align="left">⬅</button>
                                    <button type="button" class="btn btn-default btn-xs" data-align="center">⬆</button>
                                    <button type="button" class="btn btn-default btn-xs" data-align="right">➡</button>
                                </div>

                                <!-- Line height -->
                                <div class="form-group">
                                    <label>Line Height</label>
                                    <input type="range" id="lineHeight"
                                        min="1" max="2.5" step="0.1">
                                </div>

                                <!-- Weight -->
                                <div class="form-group">
                                    <label>Font Weight</label>
                                    <select id="fontWeight" class="form-control">
                                        <option value="normal">Normal</option>
                                        <option value="bold">Bold</option>
                                    </select>
                                </div>

                            </div>


                            <div class="form-group">
                                <label for="bgColor">Background Color</label>
                                <input type="color" id="bgColor" class="form-control" value="#ffffff">
                            </div>

                            <div class="form-group">
                                <label>Padding</label>
                                <input type="number" id="bgPadding" class="form-control" min="0" max="80">
                            </div>

                            <div class="form-group">
                                <label>Radius</label>
                                <input type="number" id="bgRadius" class="form-control" min="0" max="80">
                            </div>

                            <button id="btnDuplicate" class="btn btn-default btn-sm">
                                <i class="fas fa-clone"></i> Duplicate
                            </button>

                            <button id="btnDelete" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>

                    <div id="emptyHint" class="text-muted" style="padding:15px;">
                        Select a placement to edit its settings
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL FOR POP UP OF SETTING -->
    <div id="textPicker"
        style="
        display:none;
        position:absolute;
        z-index:1000;
        background:#fff;
        border:1px solid #ddd;
        border-radius:6px;
        padding:10px;
        box-shadow:0 6px 20px rgba(0,0,0,.15);
        ">
        <div class="form-group" style="margin-bottom:8px;">
            <label style="font-size:12px;">Text type</label>
            <select id="textPickerSelect" class="form-control">
                <option value="branch_name">Branch Name</option>
                <option value="branch_address">Branch Address</option>
                <option value="branch_contact">Branch Contact</option>
            </select>
        </div>
        <button class="btn btn-primary btn-xs" id="textPickerConfirm">Add</button>
    </div>

</section>


<script>
    (function() {

        /* ================== CONFIG ================== */
        const saveUrl = "<?= base_url('Template_manager/saveOverlays/' . $template['id']) ?>";
        const demoLogo = "<?= base_url('assets/images/logo.jpg') ?>";

        /* ================== DOM ================== */
        const overlayLayer = document.getElementById('overlayLayer');
        const baseImg = document.getElementById('baseImg');
        const listEl = document.getElementById('list');
        const msg = document.getElementById('msg');

        const bgEnabled = document.getElementById('bgEnabled');
        const bgColor = document.getElementById('bgColor');
        const bgPadding = document.getElementById('bgPadding');
        const bgRadius = document.getElementById('bgRadius');

        const btnUp = document.getElementById('btnUp');
        const btnDown = document.getElementById('btnDown');
        const btnDuplicate = document.getElementById('btnDuplicate');
        const btnDelete = document.getElementById('btnDelete');
        const btnAdd = document.getElementById('btnAdd');
        const btnAddText = document.getElementById('btnAddText');
        const toggleSafeArea = document.getElementById('toggleSafeArea');

        const settingsPanel = document.getElementById('settingsPanel');
        const emptyHint = document.getElementById('emptyHint');

        const textPicker = document.getElementById('textPicker');
        const textPickerSelect = document.getElementById('textPickerSelect');
        const textPickerConfirm = document.getElementById('textPickerConfirm');

        const textTools = document.getElementById('textTools');
        const fontSize = document.getElementById('fontSize');
        const fontSizeVal = document.getElementById('fontSizeVal');
        const textColor = document.getElementById('textColor');
        const lineHeight = document.getElementById('lineHeight');
        const fontWeight = document.getElementById('fontWeight');

        /* ================== STATE ================== */
        let overlays = <?= json_encode($overlays, JSON_UNESCAPED_UNICODE) ?> || [];
        let selectedIndex = 0;
        let safeAreaEl = null;

        /* ================== UTILS ================== */
        const clamp = (n, min, max) => Math.max(min, Math.min(n, max));
        const pxToRatio = (v, t) => t ? v / t : 0;
        const ratioToPx = (v, t) => v <= 1 ? v * t : v;

        function normalizeSettings(ov) {
            ov.overlay_type = ov.overlay_type || 'logo';
            ov.settings = ov.settings || {};

            ov.settings.bg = Object.assign({
                enabled: true,
                color: '#ffffff',
                padding: 12,
                radius: 16
            }, ov.settings.bg || {});

            if (ov.overlay_type === 'text') {
                ov.settings = Object.assign({
                    text_key: 'branch_name',
                    font_size: 18,
                    color: '#000000',
                    align: 'center',
                    line_height: 1.2,
                    weight: 'normal'
                }, ov.settings);
            }
            return ov;
        }

        function defaultOverlay() {
            return normalizeSettings({
                overlay_type: 'logo',
                x: overlayLayer.clientWidth * 0.05,
                y: overlayLayer.clientHeight * 0.05,
                width: overlayLayer.clientWidth * 0.2,
                height: overlayLayer.clientWidth * 0.2,
                settings: {}
            });
        }

        /* ================== SAFE AREA ================== */
        function renderSafeArea() {
            if (!toggleSafeArea.checked) {
                safeAreaEl?.remove();
                safeAreaEl = null;
                return;
            }
            if (!safeAreaEl) {
                safeAreaEl = document.createElement('div');
                safeAreaEl.className = 'safe-area';
                overlayLayer.appendChild(safeAreaEl);
            }
            const m = .05;
            safeAreaEl.style.left = overlayLayer.clientWidth * m + 'px';
            safeAreaEl.style.top = overlayLayer.clientHeight * m + 'px';
            safeAreaEl.style.width = overlayLayer.clientWidth * (1 - m * 2) + 'px';
            safeAreaEl.style.height = overlayLayer.clientHeight * (1 - m * 2) + 'px';
        }

        /* ================== INIT ================== */
        function initOverlays() {
            overlays = overlays.map(o => {
                let s = {};
                try {
                    s = typeof o.settings === 'string' ? JSON.parse(o.settings) : (o.settings || {});
                } catch (e) {}
                return normalizeSettings({
                    overlay_type: o.overlay_type || 'logo',
                    x: ratioToPx(o.x || .05, overlayLayer.clientWidth),
                    y: ratioToPx(o.y || .05, overlayLayer.clientHeight),
                    width: ratioToPx(o.width || .2, overlayLayer.clientWidth),
                    height: ratioToPx(o.height || .2, overlayLayer.clientHeight),
                    settings: s
                });
            });
            if (!overlays.length) overlays.push(defaultOverlay());
        }

        /* ================== TOOLS ================== */
        function updateToolVisibility() {
            const ov = overlays[selectedIndex];
            settingsPanel.style.display = ov ? 'block' : 'none';
            emptyHint.style.display = ov ? 'none' : 'block';
            textTools.style.display = ov?.overlay_type === 'text' ? 'block' : 'none';
        }

        function loadSettings() {
            const bg = overlays[selectedIndex].settings.bg;
            bgEnabled.value = bg.enabled ? 1 : 0;
            bgColor.value = bg.color;
            bgPadding.value = bg.padding;
            bgRadius.value = bg.radius;

            if (overlays[selectedIndex].overlay_type === 'text') {
                const s = overlays[selectedIndex].settings;
                fontSize.value = s.font_size;
                fontSizeVal.innerText = s.font_size + 'px';
                textColor.value = s.color;
                lineHeight.value = s.line_height;
                fontWeight.value = s.weight;
            }
        }

        function applyBoxStyle(box, ov) {
            box.style.left = ov.x + 'px';
            box.style.top = ov.y + 'px';
            box.style.width = ov.width + 'px';
            box.style.height = ov.height + 'px';
            box.style.background = ov.settings.bg.enabled ? ov.settings.bg.color : 'transparent';
            box.style.borderRadius = ov.settings.bg.radius + 'px';
            box.style.padding = ov.settings.bg.padding + 'px';
            box.style.boxSizing = 'border-box';
        }

        /* ================== RENDER ================== */
        function render() {
            overlayLayer.querySelectorAll('.overlay-box').forEach(el => {
                try {
                    interact(el).unset();
                } catch (e) {}
            });
            overlayLayer.innerHTML = '';
            listEl.innerHTML = '';
            renderSafeArea();

            overlays.forEach((ov, i) => {
                const li = document.createElement('div');
                li.className = 'list-item' + (i === selectedIndex ? ' active' : '');
                li.textContent = `Layer ${i+1} (${ov.overlay_type})`;
                li.onclick = () => {
                    selectedIndex = i;
                    render();
                };
                listEl.appendChild(li);

                const box = document.createElement('div');
                box.className = 'overlay-box' + (i === selectedIndex ? ' selected' : '');
                applyBoxStyle(box, ov);

                if (ov.overlay_type === 'text') {
                    const t = document.createElement('div');
                    Object.assign(t.style, {
                        width: '100%',
                        height: '100%',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: ov.settings.align === 'left' ? 'flex-start' : ov.settings.align === 'right' ? 'flex-end' : 'center',
                        textAlign: ov.settings.align,
                        fontSize: ov.settings.font_size + 'px',
                        color: ov.settings.color,
                        lineHeight: ov.settings.line_height,
                        fontWeight: ov.settings.weight,
                        pointerEvents: 'none'
                    });
                    t.textContent =
                        ov.settings.text_key === 'branch_address' ? '{{BRANCH_ADDRESS}}' :
                        ov.settings.text_key === 'branch_contact' ? '{{BRANCH_CONTACT}}' :
                        '{{BRANCH_NAME}}';
                    box.appendChild(t);
                } else {
                    const img = document.createElement('img');
                    img.src = demoLogo;
                    img.className = 'demo';
                    box.appendChild(img);
                }

                overlayLayer.appendChild(box);

                const minSize = 40 + (ov.settings.bg.padding * 2);

                interact(box)
                    .draggable({
                        listeners: {
                            move(e) {
                                ov.x = clamp(ov.x + e.dx, 0, overlayLayer.clientWidth - ov.width);
                                ov.y = clamp(ov.y + e.dy, 0, overlayLayer.clientHeight - ov.height);
                                applyBoxStyle(box, ov);
                            }
                        }
                    })
                    .resizable({
                        edges: {
                            right: true,
                            bottom: true
                        },
                        listeners: {
                            move(e) {
                                ov.width = clamp(e.rect.width, minSize, overlayLayer.clientWidth - ov.x);
                                ov.height = clamp(e.rect.height, minSize, overlayLayer.clientHeight - ov.y);
                                if (ov.overlay_type === 'text') {
                                    ov.settings.font_size = clamp(Math.round(ov.height * 0.35), 10, 80);
                                }
                                applyBoxStyle(box, ov);
                            }
                        }
                    });
            });

            updateToolVisibility();
            if (overlays[selectedIndex]) loadSettings();
        }

        /* ================== EVENTS ================== */
        btnAdd.onclick = () => {
            overlays.push(defaultOverlay());
            selectedIndex = overlays.length - 1;
            render();
        };
        btnAddText.onclick = e => {
            const r = e.target.getBoundingClientRect();
            textPicker.style.left = r.left + 'px';
            textPicker.style.top = (r.bottom + 6) + 'px';
            textPicker.style.display = 'block';
        };

        textPickerConfirm.onclick = () => {
            overlays.push(normalizeSettings({
                overlay_type: 'text',
                x: overlayLayer.clientWidth * .1,
                y: overlayLayer.clientHeight * .1,
                width: overlayLayer.clientWidth * .4,
                height: 50,
                settings: {
                    text_key: textPickerSelect.value
                }
            }));
            selectedIndex = overlays.length - 1;
            textPicker.style.display = 'none';
            render();
        };

        [bgEnabled, bgColor, bgPadding, bgRadius].forEach(el => el.oninput = () => {
            Object.assign(overlays[selectedIndex].settings.bg, {
                enabled: bgEnabled.value === '1',
                color: bgColor.value,
                padding: +bgPadding.value,
                radius: +bgRadius.value
            });
            render();
        });

        fontSize.oninput = () => {
            overlays[selectedIndex].settings.font_size = +fontSize.value;
            fontSizeVal.innerText = fontSize.value + 'px';
            render();
        };
        textColor.oninput = () => {
            overlays[selectedIndex].settings.color = textColor.value;
            render();
        };
        lineHeight.oninput = () => {
            overlays[selectedIndex].settings.line_height = +lineHeight.value;
            render();
        };
        fontWeight.onchange = () => {
            overlays[selectedIndex].settings.weight = fontWeight.value;
            render();
        };
        document.querySelectorAll('[data-align]').forEach(b => b.onclick = () => {
            overlays[selectedIndex].settings.align = b.dataset.align;
            render();
        });

        document.getElementById('btnSave').onclick = () => {
            const w = overlayLayer.clientWidth,
                h = overlayLayer.clientHeight;
            const payload = overlays.map(o => ({
                overlay_type: o.overlay_type,
                x: pxToRatio(o.x, w),
                y: pxToRatio(o.y, h),
                width: pxToRatio(o.width, w),
                height: pxToRatio(o.height, h),
                settings: o.settings
            }));
            const fd = new FormData();
            fd.append('overlays', JSON.stringify(payload));
            fd.append("<?= $this->security->get_csrf_token_name(); ?>", "<?= $this->security->get_csrf_hash(); ?>");
            fetch(saveUrl, {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(j => msg.innerHTML = j.status === 'success' ? '✅ Saved' : '❌ ' + j.message);
        };

        baseImg.onload = () => {
            overlayLayer.style.width = baseImg.clientWidth + 'px';
            overlayLayer.style.height = baseImg.clientHeight + 'px';
            initOverlays();
            render();
        };
        if (baseImg.complete) baseImg.onload();

    })();
</script>