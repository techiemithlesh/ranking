<section class="panel">
    <header class="panel-heading">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h4 class="panel-title" style="margin:0;">
                <?= translate('edit_template') ?> : <?= html_escape($template['title']) ?>
            </h4>
            <a href="<?= base_url('template-manager') ?>" class="btn btn-default btn-sm">
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

                            <div class="form-group">
                                <label>Color</label>
                                <input type="text" id="bgColor" class="form-control" value="#ffffff">
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
            </select>
        </div>
        <button class="btn btn-primary btn-xs" id="textPickerConfirm">Add</button>
    </div>

</section>

<style>
    .overlay-box {
        position: absolute;
        border: 2px dashed #0d6efd;
        background: rgba(13, 110, 253, .08);
        box-sizing: border-box;
        cursor: move;
    }

    .overlay-box.selected {
        border-style: solid;
    }

    .overlay-box .demo {
        width: 100%;
        height: 100%;
        object-fit: contain;
        pointer-events: none;
    }

    .safe-area {
        position: absolute;
        border: 2px dashed rgba(255, 0, 0, .4);
        pointer-events: none;
    }

    .list-item {
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .list-item.active {
        background: #eef5ff;
    }
</style>

<script>
    (function() {

        const saveUrl = "<?= base_url('Template_manager/saveOverlays/' . $template['id']) ?>";
        const demoLogo = "<?= base_url('assets/images/logo.jpg') ?>";

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
        const toggleSafeArea = document.getElementById('toggleSafeArea');

        const settingsPanel = document.getElementById('settingsPanel');
        const emptyHint = document.getElementById('emptyHint');

        let overlays = <?= json_encode($overlays, JSON_UNESCAPED_UNICODE) ?> || [];
        let selectedIndex = 0;
        let safeAreaEl = null;

        const btnAddText = document.getElementById('btnAddText');
        const textPicker = document.getElementById('textPicker');
        const textPickerSelect = document.getElementById('textPickerSelect');
        const textPickerConfirm = document.getElementById('textPickerConfirm');

        /* ---------- utils ---------- */
        const clamp = (n, min, max) => Math.max(min, Math.min(n, max));
        const pxToRatio = (v, t) => t ? v / t : 0;
        const ratioToPx = (v, t) => v <= 1 ? v * t : v;

        function defaultOverlay() {
            return {
                overlay_type: 'logo',
                x: overlayLayer.clientWidth * 0.05,
                y: overlayLayer.clientHeight * 0.05,
                width: overlayLayer.clientWidth * 0.2,
                height: overlayLayer.clientWidth * 0.2,
                settings: {
                    bg: {
                        enabled: true,
                        color: '#fff',
                        padding: 12,
                        radius: 16
                    }
                }
            };
        }

        /* ---------- safe area ---------- */
        function renderSafeArea() {
            if (!toggleSafeArea.checked) {
                if (safeAreaEl) safeAreaEl.remove();
                safeAreaEl = null;
                return;
            }
            if (!safeAreaEl) {
                safeAreaEl = document.createElement('div');
                safeAreaEl.className = 'safe-area';
                overlayLayer.appendChild(safeAreaEl);
            }
            const m = .05;
            safeAreaEl.style.left = (overlayLayer.clientWidth * m) + 'px';
            safeAreaEl.style.top = (overlayLayer.clientHeight * m) + 'px';
            safeAreaEl.style.width = (overlayLayer.clientWidth * (1 - m * 2)) + 'px';
            safeAreaEl.style.height = (overlayLayer.clientHeight * (1 - m * 2)) + 'px';
        }

        /* ---------- init ---------- */
        function initOverlays() {
            overlays = overlays.map(o => {
                let s = {};
                try {
                    s = o.settings ? JSON.parse(o.settings) : {}
                } catch (e) {}
                return {
                    overlay_type: o.overlay_type || 'logo',
                    x: ratioToPx(o.x || .05, overlayLayer.clientWidth),
                    y: ratioToPx(o.y || .05, overlayLayer.clientHeight),
                    width: ratioToPx(o.width || .2, overlayLayer.clientWidth),
                    height: ratioToPx(o.height || .2, overlayLayer.clientHeight),
                    settings: Object.assign({
                        bg: {
                            enabled: true,
                            color: '#fff',
                            padding: 12,
                            radius: 16
                        }
                    }, s)
                };
            });
            if (!overlays.length) overlays.push(defaultOverlay());
        }

        /* ---------- tools ---------- */
        function updateToolVisibility() {
            const has = overlays[selectedIndex];
            settingsPanel.style.display = has ? 'block' : 'none';
            emptyHint.style.display = has ? 'none' : 'block';
        }

        function loadSettings() {
            const bg = overlays[selectedIndex].settings.bg;
            bgEnabled.value = bg.enabled ? '1' : '0';
            bgColor.value = bg.color;
            bgPadding.value = bg.padding;
            bgRadius.value = bg.radius;
        }

        function applySettings() {
            const bg = overlays[selectedIndex].settings.bg;
            bg.enabled = bgEnabled.value === '1';
            bg.color = bgColor.value;
            bg.padding = +bgPadding.value;
            bg.radius = +bgRadius.value;
        }

        /* ---------- render ---------- */
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

                /* ---------- LIST ---------- */
                const li = document.createElement('div');
                li.className = 'list-item' + (i === selectedIndex ? ' active' : '');
                li.textContent = `Layer ${i + 1} (${ov.overlay_type || 'logo'})`;
                li.onclick = () => {
                    selectedIndex = i;
                    render();
                };
                listEl.appendChild(li);

                /* ---------- BOX ---------- */
                const box = document.createElement('div');
                box.className = 'overlay-box' + (i === selectedIndex ? ' selected' : '');
                box.dataset.index = i;

                Object.assign(box.style, {
                    left: ov.x + 'px',
                    top: ov.y + 'px',
                    width: ov.width + 'px',
                    height: ov.height + 'px',
                    background: ov.settings?.bg?.enabled ?
                        ov.settings.bg.color : 'rgba(13,110,253,.08)',
                    borderRadius: (ov.settings?.bg?.radius || 0) + 'px'
                });

                /* ---------- CONTENT ---------- */
                if (ov.overlay_type === 'text') {
                    const text = document.createElement('div');
                    text.style.width = '100%';
                    text.style.height = '100%';
                    text.style.display = 'flex';
                    text.style.alignItems = 'center';
                    text.style.justifyContent = 'center';
                    text.style.fontSize = (ov.settings.font_size || 18) + 'px';
                    text.style.color = ov.settings.color || '#000';
                    text.style.pointerEvents = 'none';

                    text.textContent =
                        ov.settings.text_key === 'branch_address' ?
                        '{{BRANCH_ADDRESS}}' :
                        '{{BRANCH_NAME}}';

                    box.appendChild(text);
                } else {
                    const img = document.createElement('img');
                    img.src = demoLogo;
                    img.className = 'demo';
                    box.appendChild(img);
                }

                overlayLayer.appendChild(box);

                /* ---------- INTERACT ---------- */
                interact(box)
                    .draggable({
                        listeners: {
                            move(e) {
                                ov.x = clamp(ov.x + e.dx, 0, overlayLayer.clientWidth - ov.width);
                                ov.y = clamp(ov.y + e.dy, 0, overlayLayer.clientHeight - ov.height);
                                box.style.left = ov.x + 'px';
                                box.style.top = ov.y + 'px';
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
                                ov.width = clamp(e.rect.width, 40, overlayLayer.clientWidth - ov.x);
                                ov.height = clamp(e.rect.height, 40, overlayLayer.clientHeight - ov.y);
                                box.style.width = ov.width + 'px';
                                box.style.height = ov.height + 'px';
                            }
                        }
                    });
            });

            loadSettings();
            updateToolVisibility();
        }


        /* ---------- buttons ---------- */

        btnAddText.onclick = function(e) {
            const rect = e.target.getBoundingClientRect();

            textPicker.style.left = rect.left + 'px';
            textPicker.style.top = (rect.bottom + 6) + 'px';
            textPicker.style.display = 'block';
        };


        btnAdd.onclick = () => {
            overlays.push(defaultOverlay());
            selectedIndex = overlays.length - 1;
            render();
        };

        btnDuplicate.onclick = () => {
            const c = JSON.parse(JSON.stringify(overlays[selectedIndex]));
            c.x += 10;
            c.y += 10;
            overlays.push(c);
            selectedIndex = overlays.length - 1;
            render();
        };
        btnDelete.onclick = () => {
            if (overlays.length <= 1) return alert('At least one placement required');
            overlays.splice(selectedIndex, 1);
            selectedIndex = Math.max(0, selectedIndex - 1);
            render();
        };
        btnUp.onclick = () => {
            if (selectedIndex <= 0) return;
            [overlays[selectedIndex], overlays[selectedIndex - 1]] = [overlays[selectedIndex - 1], overlays[selectedIndex]];
            selectedIndex--;
            render();
        };
        btnDown.onclick = () => {
            if (selectedIndex >= overlays.length - 1) return;
            [overlays[selectedIndex], overlays[selectedIndex + 1]] = [overlays[selectedIndex + 1], overlays[selectedIndex]];
            selectedIndex++;
            render();
        };
        [bgEnabled, bgColor, bgPadding, bgRadius].forEach(el => el.oninput = () => {
            applySettings();
            render();
        });

        textPickerConfirm.onclick = function() {
            const type = textPickerSelect.value;

            const ov = {
                overlay_type: 'text',
                x: overlayLayer.clientWidth * 0.1,
                y: overlayLayer.clientHeight * 0.1,
                width: overlayLayer.clientWidth * 0.4,
                height: 50,
                settings: {
                    text_key: type, // branch_name | branch_address
                    font_size: 18,
                    color: '#000000',
                    align: 'center'
                }
            };

            overlays.push(ov);
            selectedIndex = overlays.length - 1;

            textPicker.style.display = 'none';
            render();
        };


        document.getElementById('btnSave').onclick = () => {
            const w = overlayLayer.clientWidth,
                h = overlayLayer.clientHeight;
            const payload = overlays.map(o => ({
                overlay_type: o.overlay_type || 'logo',
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
                    body: fd,
                })
                .then(r => r.json())
                .then(j => msg.innerHTML = j.status === 'success' ? '✅ Saved' : '❌ ' + j.message);
        };

        /* ---------- boot ---------- */
        baseImg.onload = () => {
            overlayLayer.style.width = baseImg.clientWidth + 'px';
            overlayLayer.style.height = baseImg.clientHeight + 'px';
            initOverlays();
            render();
        };
        if (baseImg.complete) baseImg.onload();

    })();
</script>