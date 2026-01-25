<section class="panel">
    <header class="panel-heading">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h4 class="panel-title" style="margin:0;">
                <?= translate('edit_template') ?> : <?= html_escape($template['title']) ?>
            </h4>

            <div>
                <a href="<?= base_url('template-manager') ?>" class="btn btn-default btn-sm">
                    <i class="fas fa-arrow-left"></i> <?= translate('back') ?>
                </a>
            </div>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">
            <!-- LEFT: Stage -->
            <div class="col-md-8">
                <div style="border:1px solid #ddd; background:#fafafa; padding:10px; overflow:auto;">
                    <div id="stage" style="position:relative; display:inline-block;">
                        <img id="baseImg" src="<?= base_url($template['file_path']) ?>" style="display:block; max-width:100%;" alt="template">
                        <div id="overlayLayer" style="position:absolute; left:0; top:0;"></div>

                    </div>
                </div>

                <div style="margin-top:10px;">
                    <button type="button" id="btnAdd" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Placement
                    </button>
                    <button type="button" id="btnSave" class="btn btn-success btn-sm">
                        <i class="fas fa-save"></i> Save All
                    </button>
                    <span id="msg" style="margin-left:10px;"></span>
                </div>

                <p class="text-muted" style="margin-top:8px;">
                    Tip: Add multiple logo placements, drag/resize, then save. (Positions are stored in pixels.)
                </p>
            </div>

            <!-- RIGHT: Layers + Settings -->
            <div class="col-md-4">
                <div class="panel" style="border:1px solid #eee;">
                    <div class="panel-heading">
                        <strong>Placements</strong>
                    </div>
                    <div class="panel-body">
                        <div id="list" style="border:1px solid #eee; border-radius:6px; max-height:220px; overflow:auto;"></div>

                        <hr>

                        <strong>Selected Placement Settings</strong>

                        <div class="form-group" style="margin-top:10px;">
                            <label>Background box</label>
                            <select id="bgEnabled" class="form-control">
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Background color</label>
                            <input type="text" id="bgColor" class="form-control" value="#ffffff">
                        </div>

                        <div class="form-group">
                            <label>Padding (px)</label>
                            <input type="number" id="bgPadding" class="form-control" value="12" min="0" max="80">
                        </div>

                        <div class="form-group">
                            <label>Corner radius (px)</label>
                            <input type="number" id="bgRadius" class="form-control" value="16" min="0" max="80">
                        </div>

                        <button type="button" id="btnDelete" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    .overlay-box {
        position: absolute;
        border: 2px dashed #0d6efd;
        background: rgba(13, 110, 253, 0.08);
        box-sizing: border-box;
    }

    .overlay-box.selected {
        border-style: solid;
    }

    .overlay-box .demo {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        pointer-events: none;
        opacity: 0.95;
    }

    .list-item {
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
    }

    .list-item.active {
        background: #eef5ff;
    }

    .small {
        font-size: 12px;
        color: #666;
    }
</style>

<script>
    (function() {
        const saveUrl = "<?= base_url('Template_manager/saveOverlays' . '/' . $template['id']) ?>";

        const demoLogo = "<?= base_url('assets/images/logo.jpg') ?>";

        const overlayLayer = document.getElementById('overlayLayer');
        const baseImg = document.getElementById('baseImg');
        const listEl = document.getElementById('list');
        const msg = document.getElementById('msg');

        const bgEnabled = document.getElementById('bgEnabled');
        const bgColor = document.getElementById('bgColor');
        const bgPadding = document.getElementById('bgPadding');
        const bgRadius = document.getElementById('bgRadius');

        let overlays = <?= json_encode($overlays, JSON_UNESCAPED_UNICODE) ?> || [];
        overlays = overlays.map(o => {
            let settings = {};
            try {
                settings = o.settings ? JSON.parse(o.settings) : {};
            } catch (e) {}
            return {
                x: parseFloat(o.x || 40),
                y: parseFloat(o.y || 40),
                width: parseFloat(o.width || 180),
                height: parseFloat(o.height || 180),
                settings: Object.assign({
                    fit: 'contain',
                    bg: {
                        enabled: true,
                        color: '#ffffff',
                        padding: 12,
                        radius: 16,
                        opacity: 1
                    }
                }, settings)
            };
        });
        if (overlays.length === 0) overlays.push(defaultOverlay());

        let selectedIndex = 0;

        function defaultOverlay() {
            return {
                x: 40,
                y: 40,
                width: 180,
                height: 180,
                settings: {
                    fit: 'contain',
                    bg: {
                        enabled: true,
                        color: '#ffffff',
                        padding: 12,
                        radius: 16,
                        opacity: 1
                    }
                }
            };
        }

        function clamp(n, min, max) {
            return Math.max(min, Math.min(n, max));
        }

        function applySettingsToSelected() {
            const ov = overlays[selectedIndex];
            ov.settings = ov.settings || {};
            ov.settings.bg = ov.settings.bg || {};

            ov.settings.bg.enabled = (bgEnabled.value === '1');
            ov.settings.bg.color = (bgColor.value || '#ffffff').trim();
            ov.settings.bg.padding = parseFloat(bgPadding.value || 0);
            ov.settings.bg.radius = parseFloat(bgRadius.value || 0);
            ov.settings.bg.opacity = 1;
        }

        function loadSettingsForm() {
            const ov = overlays[selectedIndex];
            const bg = ov.settings?.bg || {
                enabled: true,
                color: '#ffffff',
                padding: 12,
                radius: 16,
                opacity: 1
            };
            bgEnabled.value = bg.enabled ? '1' : '0';
            bgColor.value = bg.color || '#ffffff';
            bgPadding.value = bg.padding ?? 12;
            bgRadius.value = bg.radius ?? 16;
        }

        function boxBgStyle(ov) {
            const bg = ov.settings?.bg || {};
            if (bg.enabled) {
                return {
                    background: hexToRgba(bg.color || '#ffffff', bg.opacity ?? 1),
                    borderRadius: (bg.radius || 0) + 'px'
                };
            }
            return {
                background: 'rgba(13,110,253,0.08)',
                borderRadius: '0px'
            };
        }

        function hexToRgba(hex, alpha = 1) {
            let h = (hex || '').replace('#', '').trim();
            if (h.length === 3) h = h.split('').map(c => c + c).join('');
            if (h.length !== 6) return `rgba(255,255,255,${alpha})`;
            const r = parseInt(h.slice(0, 2), 16);
            const g = parseInt(h.slice(2, 4), 16);
            const b = parseInt(h.slice(4, 6), 16);
            return `rgba(${r},${g},${b},${alpha})`;
        }

        function render() {
            // ✅ unset before remove
            overlayLayer.querySelectorAll('.overlay-box').forEach(el => {
                try {
                    interact(el).unset();
                } catch (e) {}
            });

            overlayLayer.innerHTML = '';
            listEl.innerHTML = '';

            overlays.forEach((ov, idx) => {

                // list
                const item = document.createElement('div');
                item.className = 'list-item' + (idx === selectedIndex ? ' active' : '');
                item.innerHTML = `<div>Layer ${idx+1}<div class="small">${Math.round(ov.width)}×${Math.round(ov.height)} @ (${Math.round(ov.x)},${Math.round(ov.y)})</div></div><div class="small">#${idx+1}</div>`;
                item.onclick = () => {
                    selectedIndex = idx;
                    render();
                };
                listEl.appendChild(item);

                // box
                const box = document.createElement('div');
                box.className = 'overlay-box' + (idx === selectedIndex ? ' selected' : '');
                box.dataset.index = idx;
                box.style.left = ov.x + 'px';
                box.style.top = ov.y + 'px';
                box.style.width = ov.width + 'px';
                box.style.height = ov.height + 'px';

                const st = boxBgStyle(ov);
                box.style.background = st.background;
                box.style.borderRadius = st.borderRadius;

                const img = document.createElement('img');
                img.className = 'demo';
                img.src = demoLogo;
                box.appendChild(img);

                overlayLayer.appendChild(box);

                // ✅ DO NOT re-render on mousedown
                box.addEventListener('mousedown', () => {
                    selectedIndex = idx;
                    highlightSelectedOnly(); 
                    loadSettingsForm();
                });

                interact(box)
                    .draggable({
                        listeners: {
                            move(event) {
                                const i = parseInt(event.target.dataset.index, 10);
                                overlays[i].x += event.dx;
                                overlays[i].y += event.dy;

                                const maxX = overlayLayer.clientWidth - overlays[i].width;
                                const maxY = overlayLayer.clientHeight - overlays[i].height;
                                overlays[i].x = clamp(overlays[i].x, 0, maxX);
                                overlays[i].y = clamp(overlays[i].y, 0, maxY);

                                event.target.style.left = overlays[i].x + 'px';
                                event.target.style.top = overlays[i].y + 'px';
                            }
                        }
                    })
                    .resizable({
                        edges: {
                            left: false,
                            right: true,
                            bottom: true,
                            top: false
                        },
                        listeners: {
                            move(event) {
                                const i = parseInt(event.target.dataset.index, 10);

                                overlays[i].width = clamp(event.rect.width, 40, overlayLayer.clientWidth - overlays[i].x);
                                overlays[i].height = clamp(event.rect.height, 40, overlayLayer.clientHeight - overlays[i].y);

                                event.target.style.width = overlays[i].width + 'px';
                                event.target.style.height = overlays[i].height + 'px';
                            }
                        }
                    });
            });

            loadSettingsForm();
        }

        function highlightSelectedOnly() {
            overlayLayer.querySelectorAll('.overlay-box').forEach((el) => {
                const i = parseInt(el.dataset.index, 10);
                if (i === selectedIndex) el.classList.add('selected');
                else el.classList.remove('selected');
            });

            listEl.querySelectorAll('.list-item').forEach((el, i) => {
                if (i === selectedIndex) el.classList.add('active');
                else el.classList.remove('active');
            });
        }


        function renderListOnly() {
            const items = listEl.querySelectorAll('.list-item .small');
            overlays.forEach((ov, idx) => {
                // items structure has 2 .small per item; simplest is rerender fully if you want perfect,
                // but keep it light: just skip
            });
        }

        document.getElementById('btnAdd').addEventListener('click', () => {
            overlays.push(defaultOverlay());
            selectedIndex = overlays.length - 1;
            render();
        });

        document.getElementById('btnDelete').addEventListener('click', () => {
            if (overlays.length <= 1) {
                alert('At least one placement is required.');
                return;
            }
            overlays.splice(selectedIndex, 1);
            selectedIndex = Math.max(0, selectedIndex - 1);
            render();
        });

        [bgEnabled, bgColor, bgPadding, bgRadius].forEach(el => {
            el.addEventListener('input', () => {
                applySettingsToSelected();
                render();
            });
        });

        document.getElementById('btnSave').addEventListener('click', () => {
            msg.innerHTML = 'Saving...';

            applySettingsToSelected();

            const fd = new FormData();
            fd.append('overlays', JSON.stringify(overlays.map(ov => ({
                x: ov.x,
                y: ov.y,
                width: ov.width,
                height: ov.height,
                settings: ov.settings
            }))));

            fd.append("<?= $this->security->get_csrf_token_name(); ?>", "<?= $this->security->get_csrf_hash(); ?>");

            fetch(saveUrl, {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(j => {
                    msg.innerHTML = (j.status === 'success') ? '<span style="color:green;">✅ Saved</span>' : '<span style="color:red;">❌ ' + (j.message || 'Error') + '</span>';
                })
                .catch(() => {
                    msg.innerHTML = '<span style="color:red;">❌ Network error</span>';
                });
        });

        // ensure overlayLayer matches image size
        baseImg.onload = function() {
            overlayLayer.style.width = baseImg.clientWidth + 'px';
            overlayLayer.style.height = baseImg.clientHeight + 'px';
            render();
        };
        if (baseImg.complete) baseImg.onload();

    })();
</script>