<!-- ============================================================
     STYLES
     ============================================================ -->
<style>
/* ── CANVAS ── */
#stage { position: relative; display: inline-block; user-select: none; }

.overlay-box {
    position: absolute;
    box-sizing: border-box;
    cursor: move;
    background: transparent;
    overflow: visible; /* handles must poke outside the box boundary */
}
/* Edit mode: dashed blue border */
.edit-mode .overlay-box        { border: 2px dashed rgba(13,110,253,0.5); }
.edit-mode .overlay-box.selected {
    border: 2px solid #0d6efd;
    box-shadow: 0 0 0 3px rgba(13,110,253,0.2);
}
/* Logo wrapper: invisible, styles on <img> */
.overlay-box.type-logo {
    background: transparent !important;
    padding: 0 !important;
    border-radius: 0 !important;
}
.overlay-box.type-logo img {
    display: block; width: 100%; height: 100%; pointer-events: none;
}
/* Text inner */
.overlay-box.type-text .text-inner {
    width: 100%; height: 100%;
    display: flex; align-items: center;
    pointer-events: none; overflow: hidden; word-break: break-word;
    white-space: normal;
}
/* Safe area */
.safe-area {
    position: absolute;
    border: 2px dashed rgba(255,0,0,0.4);
    pointer-events: none; z-index: 0;
}
/* ── RESIZE / DRAG HANDLES ──
   Always rendered as real DOM elements (not ::after) so they are
   visible and grabbable even when the box is 1px × 1px.
   Positioned OUTSIDE the box boundary so they never get clipped.
*/
.box-handle {
    position: absolute;
    width: 14px; height: 14px;
    background: #0d6efd;
    border: 2px solid #fff;
    border-radius: 3px;
    z-index: 9999;
    box-shadow: 0 1px 4px rgba(0,0,0,0.35);
    /* keep handles on top of everything */
    pointer-events: none; /* interact.js uses the box itself */
}
/* Corner positions — centered on the corner point */
.box-handle.nw { top:-7px;  left:-7px;  cursor:nw-resize; }
.box-handle.ne { top:-7px;  right:-7px; cursor:ne-resize; }
.box-handle.sw { bottom:-7px; left:-7px;  cursor:sw-resize; }
.box-handle.se { bottom:-7px; right:-7px; cursor:se-resize; }
/* Edge midpoint positions */
.box-handle.n  { top:-7px;    left:50%; transform:translateX(-50%); cursor:n-resize; }
.box-handle.s  { bottom:-7px; left:50%; transform:translateX(-50%); cursor:s-resize; }
.box-handle.w  { left:-7px;   top:50%;  transform:translateY(-50%); cursor:w-resize; }
.box-handle.e  { right:-7px;  top:50%;  transform:translateY(-50%); cursor:e-resize; }
/* Only show handles on selected box */
.overlay-box .box-handle { display: none; }
.overlay-box.selected .box-handle { display: block; }
/* Drag-move cursor indicator — top-center */
.box-handle.move {
    background: #fff;
    border: 2px solid #0d6efd;
    border-radius: 50%;
    cursor: move;
    top: -7px; left: 50%; transform: translateX(-50%);
    width: 16px; height: 16px;
    display: none; /* shown only on selected, overridden below */
    font-size: 9px; line-height: 12px; text-align: center; color: #0d6efd;
}
.overlay-box.selected .box-handle.move { display: block; }

/* ── LAYER LIST ── */
.list-item {
    padding: 7px 10px; border-bottom: 1px solid #eee;
    cursor: pointer; font-size: 13px;
    display: flex; align-items: center; gap: 6px;
}
.list-item:hover  { background: #f5f5f5; }
.list-item.active { background: #eef5ff; font-weight: 600; }
.list-item .layer-name { flex: 1; outline: none; border: none; background: transparent;
    font-size: 13px; cursor: pointer; }
.list-item .layer-name:focus { background: #fff; border-bottom: 1px solid #0d6efd; cursor: text; }

/* ── FLOATING SETTINGS PANEL ── */
#floatPanel {
    display: none;
    position: fixed; top: 80px; right: 20px;
    width: 285px; max-height: calc(100vh - 100px);
    overflow-y: auto; background: #fff;
    border: 1px solid #ddd; border-radius: 8px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.18);
    z-index: 9998;
}
#floatPanel.show { display: block; animation: fpSlide .15s ease; }
@keyframes fpSlide {
    from { opacity:0; transform:translateX(16px); }
    to   { opacity:1; transform:translateX(0); }
}
.fp-header {
    background: #0d6efd; color: #fff;
    padding: 10px 14px; border-radius: 7px 7px 0 0;
    display: flex; align-items: center; justify-content: space-between;
    font-weight: 600; font-size: 13px; position: sticky; top: 0; z-index: 1;
}
.fp-close { cursor:pointer; font-size:16px; color:#fff; background:none; border:none; padding:0; line-height:1; }
.fp-body  { padding: 12px 14px; }
.fp-body .form-group { margin-bottom: 10px; }
.fp-body label { font-size:12px; font-weight:600; color:#555; margin-bottom:3px; display:block; }
.fp-body .form-control { font-size:13px; padding:5px 8px; height:32px; }
.fp-body hr { margin:10px 0; border-color:#eee; }
.section-title { font-size:11px; font-weight:700; text-transform:uppercase;
    color:#999; letter-spacing:.5px; margin:10px 0 6px; }
.range-row { display:flex; align-items:center; gap:8px; }
.range-row input[type="range"] { flex:1; }
.range-row small { min-width:38px; text-align:right; font-size:12px; color:#666; }
.align-btn { min-width:58px !important; }
.align-btn.act { background:#0d6efd !important; color:#fff !important; border-color:#0d6efd !important; }
input[type="color"] { padding:2px; height:32px; width:100%; cursor:pointer; border-radius:4px; }

/* ── UNDO/REDO TOOLBAR ── */
#historyBar { display:flex; gap:4px; align-items:center; }
#historyBar button:disabled { opacity:.4; cursor:not-allowed; }

/* ── KEYBOARD HINT ── */
.kbd-hint { font-size:11px; color:#aaa; margin-top:4px; }
.kbd { display:inline-block; background:#f0f0f0; border:1px solid #ccc;
    border-radius:3px; padding:1px 5px; font-size:11px; font-family:monospace; }

/* ── PREVIEW MODAL ── */
#previewModal {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.75); z-index: 99999;
    align-items: center; justify-content: center;
}
#previewModal.open { display: flex; animation: fadeIn .2s ease; }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
#previewModalInner {
    background: #1a1a1a; border-radius: 10px;
    max-width: 92vw; max-height: 92vh;
    display: flex; flex-direction: column;
    overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.6);
}
#previewModalHeader {
    background: #222; color: #fff; padding: 12px 18px;
    display: flex; align-items: center; justify-content: space-between;
    font-size: 14px; font-weight: 600; flex-shrink: 0;
}
#previewModalBody {
    overflow: auto; padding: 20px;
    display: flex; align-items: center; justify-content: center;
    flex: 1;
}
#previewStage {
    position: relative; display: inline-block;
}
#previewStage img.preview-base {
    display: block; max-width: 100%; height: auto;
}
.preview-box {
    position: absolute; box-sizing: border-box;
    pointer-events: none;
}
.preview-box.type-logo {
    background: transparent !important; padding: 0 !important; border-radius: 0 !important;
}
.preview-box.type-logo img { display:block; width:100%; height:100%; }
.preview-box.type-text .text-inner {
    width:100%; height:100%; display:flex; align-items:center;
    overflow:hidden; word-break:break-word; pointer-events:none;
    white-space: normal;
}
#previewModalFooter {
    background: #222; padding: 10px 18px;
    display: flex; align-items: center; gap: 10px; flex-shrink: 0;
}

/* ── AUTO-FIT hint badge ── */
.autofit-badge {
    display: inline-block;
    font-size: 10px; font-weight: 600;
    background: #e8f4fd; color: #0d6efd;
    border: 1px solid #b8d9f5; border-radius: 10px;
    padding: 2px 8px; margin-top: 4px;
    letter-spacing: .3px;
}
</style>

<!-- ============================================================
     HTML
     ============================================================ -->
<section class="panel">
    <header class="panel-heading">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h4 class="panel-title" style="margin:0;">
                <?= translate('edit_template') ?> : <?= html_escape($template['title']) ?>
            </h4>
            <a href="<?= base_url('Template_manager') ?>" class="btn btn-default btn-sm" id="btnBack">
                <i class="fas fa-arrow-left"></i> <?= translate('back') ?>
            </a>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">

            <!-- LEFT: CANVAS -->
            <div class="col-md-8">

                <!-- Toolbar -->
                <div style="margin-bottom:8px;display:flex;align-items:center;flex-wrap:wrap;gap:6px;">
                    <button id="btnAdd" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Logo
                    </button>
                    <button type="button" id="btnAddText" class="btn btn-info btn-sm">
                        <i class="fas fa-font"></i> Add Text
                    </button>

                    <span style="width:1px;height:24px;background:#ddd;margin:0 2px;"></span>

                    <!-- Undo / Redo -->
                    <div id="historyBar">
                        <button id="btnUndo" class="btn btn-default btn-sm" title="Undo (Ctrl+Z)" disabled>
                            <i class="fas fa-undo"></i>
                        </button>
                        <button id="btnRedo" class="btn btn-default btn-sm" title="Redo (Ctrl+Y)" disabled>
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>

                    <span style="width:1px;height:24px;background:#ddd;margin:0 2px;"></span>

                    <!-- Preview -->
                    <button id="btnPreview" class="btn btn-warning btn-sm">
                        <i class="fas fa-eye"></i> Preview
                    </button>

                    <!-- Save -->
                    <button id="btnSave" class="btn btn-success btn-sm">
                        <i class="fas fa-save"></i> Save
                    </button>

                    <span id="msg"></span>
                </div>

                <!-- Safe area toggle -->
                <div style="margin-bottom:6px;">
                    <label style="margin:0;font-size:13px;">
                        <input type="checkbox" id="toggleSafeArea" checked> Show Safe Area
                    </label>
                </div>

                <!-- Canvas -->
                <div style="border:1px solid #ddd;background:#fafafa;padding:10px;overflow:auto;">
                    <div id="stage" class="edit-mode" style="position:relative;display:inline-block;">
                        <img id="baseImg"
                            src="<?= base_url($template['file_path']) ?>"
                            style="display:block;max-width:100%;height:auto;">
                        <div id="overlayLayer" style="position:absolute;left:0;top:0;overflow:visible;"></div>
                    </div>
                </div>

                <p class="kbd-hint" style="margin-top:6px;">
                    <span class="kbd">Click</span> layer to edit &nbsp;
                    <span class="kbd">Del</span> delete selected &nbsp;
                    <span class="kbd">Ctrl+Z</span> undo &nbsp;
                    <span class="kbd">Ctrl+Y</span> redo &nbsp;
                    <span class="kbd">Esc</span> close panel
                </p>
            </div>

            <!-- RIGHT: LAYER LIST -->
            <div class="col-md-4">
                <div class="panel" style="border:1px solid #eee;">
                    <div class="panel-heading" style="display:flex;align-items:center;justify-content:space-between;">
                        <strong>Layers</strong>
                        <small class="text-muted" style="font-size:11px;">Double-click name to rename</small>
                    </div>
                    <div class="panel-body" style="padding:8px;">
                        <div id="list" style="border:1px solid #eee;border-radius:6px;max-height:300px;overflow:auto;"></div>
                        <div style="margin:8px 0;display:flex;gap:4px;flex-wrap:wrap;">
                            <button id="btnUp"        class="btn btn-default btn-xs">⬆</button>
                            <button id="btnDown"      class="btn btn-default btn-xs">⬇</button>
                            <button id="btnDuplicate" class="btn btn-default btn-xs"><i class="fas fa-clone"></i></button>
                            <button id="btnDelete"    class="btn btn-danger  btn-xs"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ── FLOATING SETTINGS PANEL ── -->
    <div id="floatPanel">
        <div class="fp-header">
            <span id="fpTitle">Layer Settings</span>
            <button class="fp-close" id="fpClose">✕</button>
        </div>
        <div class="fp-body">

            <!-- LOGO TOOLS -->
            <div id="logoTools" style="display:none;">
                <div class="section-title">Image Styling</div>

                <div class="form-group">
                    <label>Border Radius (px)</label>
                    <div class="range-row">
                        <input type="range" id="imgRadius" min="0" max="300" step="1" value="0">
                        <small id="imgRadiusVal">0px</small>
                    </div>
                </div>
                <div class="form-group">
                    <label>Opacity</label>
                    <div class="range-row">
                        <input type="range" id="imgOpacity" min="0.1" max="1" step="0.05" value="1">
                        <small id="imgOpacityVal">100%</small>
                    </div>
                </div>
                <div class="form-group">
                    <label>Object Fit</label>
                    <select id="imgObjectFit" class="form-control">
                        <option value="contain">Contain</option>
                        <option value="cover">Cover</option>
                        <option value="fill">Fill / Stretch</option>
                        <option value="scale-down">Scale Down</option>
                    </select>
                </div>

                <hr>
                <div class="section-title">Border</div>
                <div class="form-group">
                    <label>Width (px)</label>
                    <input type="number" id="imgBorderWidth" class="form-control" min="0" max="30" value="0">
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="color" id="imgBorderColor" value="#ffffff">
                </div>
                <div class="form-group">
                    <label>Style</label>
                    <select id="imgBorderStyle" class="form-control">
                        <option value="solid">Solid</option>
                        <option value="dashed">Dashed</option>
                        <option value="dotted">Dotted</option>
                        <option value="double">Double</option>
                    </select>
                </div>

                <hr>
                <div class="section-title">Effects</div>
                <div class="form-group">
                    <label>Box Shadow</label>
                    <select id="imgShadow" class="form-control">
                        <option value="none">None</option>
                        <option value="0 2px 8px rgba(0,0,0,0.3)">Small</option>
                        <option value="0 4px 16px rgba(0,0,0,0.4)">Medium</option>
                        <option value="0 8px 30px rgba(0,0,0,0.5)">Large</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image Filter</label>
                    <select id="imgFilter" class="form-control">
                        <option value="none">None</option>
                        <option value="grayscale(100%)">Grayscale</option>
                        <option value="sepia(80%)">Sepia</option>
                        <option value="brightness(1.3)">Brighter</option>
                        <option value="brightness(0.7)">Darker</option>
                        <option value="contrast(1.5)">High Contrast</option>
                        <option value="blur(2px)">Blur</option>
                        <option value="drop-shadow(2px 4px 6px black)">Drop Shadow</option>
                    </select>
                </div>
            </div>

            <!-- TEXT TOOLS — font size slider REMOVED, auto-fit handles sizing -->
            <div id="textTools" style="display:none;">
                <div class="section-title">Text</div>

                <!-- AUTO-FIT notice — replaces the old font size slider -->
                <div style="margin-bottom:10px;">
                    <span class="autofit-badge">✦ Font size auto-fits to box</span>
                    <div style="font-size:11px;color:#888;margin-top:4px;">
                        Resize the text box on canvas to control how large the text appears.
                        Shorter text = larger font. Longer text = smaller font. Always fills the box.
                    </div>
                </div>

                <div class="form-group">
                    <label>Text Color</label>
                    <input type="color" id="textColor">
                </div>
                <div class="form-group">
                    <label>Alignment</label>
                    <div style="display:flex;gap:4px;margin-top:4px;">
                        <button type="button" class="btn btn-default btn-xs align-btn" data-align="left">⬅ Left</button>
                        <button type="button" class="btn btn-default btn-xs align-btn" data-align="center">≡ Center</button>
                        <button type="button" class="btn btn-default btn-xs align-btn" data-align="right">➡ Right</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Font Weight</label>
                    <select id="fontWeight" class="form-control">
                        <option value="normal">Normal</option>
                        <option value="bold">Bold</option>
                        <option value="600">Semi Bold</option>
                        <option value="800">Extra Bold</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Line Height</label>
                    <div class="range-row">
                        <input type="range" id="lineHeight" min="1" max="2.5" step="0.1">
                        <small id="lineHeightVal">1.2</small>
                    </div>
                </div>

                <hr>
                <div class="section-title">Background</div>
                <div class="form-group">
                    <label>Background</label>
                    <select id="bgEnabled" class="form-control">
                        <option value="1">Enabled</option>
                        <option value="0">Disabled</option>
                    </select>
                </div>
                <div id="bgOptions">
                    <div class="form-group">
                        <label>Color</label>
                        <input type="color" id="bgColor" value="#ffffff">
                    </div>
                    <div class="form-group">
                        <label>Padding (px)</label>
                        <input type="number" id="bgPadding" class="form-control" min="0" max="80">
                    </div>
                    <div class="form-group">
                        <label>Radius (px)</label>
                        <input type="number" id="bgRadius" class="form-control" min="0" max="80">
                    </div>
                </div>
            </div>

        </div>
    </div>

</section>

<!-- ── TEXT PICKER — outside section/panel to avoid stacking context issues ── -->
<div id="textPicker" style="display:none;position:fixed;z-index:99999;
    background:#fff;border:1px solid #ccc;border-radius:8px;
    padding:14px;box-shadow:0 8px 24px rgba(0,0,0,.2);min-width:200px;">
    <div style="font-size:12px;font-weight:700;color:#555;margin-bottom:8px;">Text type</div>
    <select id="textPickerSelect" class="form-control" style="margin-bottom:10px;">
        <option value="branch_name">Branch Name</option>
        <option value="branch_address">Branch Address</option>
        <option value="branch_contact">Branch Contact</option>
    </select>
    <button class="btn btn-primary btn-xs" id="textPickerConfirm">Add</button>
    <button class="btn btn-default btn-xs" id="textPickerCancel" style="margin-left:6px;">Cancel</button>
</div>

<!-- ── PREVIEW MODAL ── -->
<div id="previewModal">
    <div id="previewModalInner">
        <div id="previewModalHeader">
            <span><i class="fas fa-eye" style="margin-right:8px;"></i>Preview — Clean output (no handles/borders)</span>
            <button id="previewClose" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1;">✕</button>
        </div>
        <div id="previewModalBody">
            <div id="previewStage">
                <img id="previewBaseImg" class="preview-base"
                    src="<?= base_url($template['file_path']) ?>"
                    style="display:block;max-width:80vw;max-height:70vh;">
                <div id="previewOverlayLayer" style="position:absolute;left:0;top:0;pointer-events:none;"></div>
            </div>
        </div>
        <div id="previewModalFooter">
            <span style="color:#aaa;font-size:13px;"><i class="fas fa-info-circle"></i> This is exactly how your template will appear.</span>
            <button id="previewCloseBtn" class="btn btn-default btn-sm" style="margin-left:auto;">Close</button>
        </div>
    </div>
</div>


<!-- ============================================================
     JAVASCRIPT
     ============================================================ -->
<script>
(function () {

    /* ── CONFIG ── */
    const saveUrl  = "<?= base_url('Template_manager/saveOverlays/' . $template['id']) ?>";
    const demoLogo = "<?= base_url('assets/images/logo.jpg') ?>";

    /* ── DOM ── */
    const overlayLayer   = document.getElementById('overlayLayer');
    const baseImg        = document.getElementById('baseImg');
    const listEl         = document.getElementById('list');
    const msg            = document.getElementById('msg');
    const toggleSafeArea = document.getElementById('toggleSafeArea');

    const btnUp          = document.getElementById('btnUp');
    const btnDown        = document.getElementById('btnDown');
    const btnDuplicate   = document.getElementById('btnDuplicate');
    const btnDelete      = document.getElementById('btnDelete');
    const btnAdd         = document.getElementById('btnAdd');
    const btnAddText     = document.getElementById('btnAddText');
    const btnPreview     = document.getElementById('btnPreview');
    const btnUndo        = document.getElementById('btnUndo');
    const btnRedo        = document.getElementById('btnRedo');
    const btnBack        = document.getElementById('btnBack');

    const floatPanel     = document.getElementById('floatPanel');
    const fpTitle        = document.getElementById('fpTitle');
    const fpClose        = document.getElementById('fpClose');
    const logoTools      = document.getElementById('logoTools');
    const textTools      = document.getElementById('textTools');

    // Logo controls
    const imgRadius      = document.getElementById('imgRadius');
    const imgRadiusVal   = document.getElementById('imgRadiusVal');
    const imgOpacity     = document.getElementById('imgOpacity');
    const imgOpacityVal  = document.getElementById('imgOpacityVal');
    const imgObjectFit   = document.getElementById('imgObjectFit');
    const imgBorderWidth = document.getElementById('imgBorderWidth');
    const imgBorderColor = document.getElementById('imgBorderColor');
    const imgBorderStyle = document.getElementById('imgBorderStyle');
    const imgShadow      = document.getElementById('imgShadow');
    const imgFilter      = document.getElementById('imgFilter');

    // Text controls (NO fontSize/fontSizeVal — removed for auto-fit)
    const textColor      = document.getElementById('textColor');
    const lineHeight     = document.getElementById('lineHeight');
    const lineHeightVal  = document.getElementById('lineHeightVal');
    const fontWeight     = document.getElementById('fontWeight');
    const bgEnabled      = document.getElementById('bgEnabled');
    const bgColor        = document.getElementById('bgColor');
    const bgPadding      = document.getElementById('bgPadding');
    const bgRadius       = document.getElementById('bgRadius');
    const bgOptions      = document.getElementById('bgOptions');

    const textPicker        = document.getElementById('textPicker');
    const textPickerSelect  = document.getElementById('textPickerSelect');
    const textPickerConfirm = document.getElementById('textPickerConfirm');
    const textPickerCancel  = document.getElementById('textPickerCancel');

    // Preview
    const previewModal        = document.getElementById('previewModal');
    const previewBaseImg      = document.getElementById('previewBaseImg');
    const previewOverlayLayer = document.getElementById('previewOverlayLayer');
    const previewClose        = document.getElementById('previewClose');
    const previewCloseBtn     = document.getElementById('previewCloseBtn');

    /* ── STATE ──
       ALL x/y/width/height = RATIOS 0.0–1.0
       Converted to px only at render time, NEVER stored back as px.
    */
    let overlays      = <?= json_encode($overlays, JSON_UNESCAPED_UNICODE) ?> || [];
    let selectedIndex = -1;
    let safeAreaEl    = null;
    let panelOpen     = false;
    let isDirty       = false;

    /* ── UNDO/REDO HISTORY ── */
    let history    = [];
    let historyPos = -1;
    const MAX_HISTORY = 50;

    function snapshot() {
        history = history.slice(0, historyPos + 1);
        history.push(JSON.stringify(overlays));
        if (history.length > MAX_HISTORY) history.shift();
        historyPos = history.length - 1;
        isDirty = true;
        updateHistoryBtns();
    }
    function undo() {
        if (historyPos <= 0) return;
        historyPos--;
        overlays = JSON.parse(history[historyPos]);
        render();
        updateHistoryBtns();
    }
    function redo() {
        if (historyPos >= history.length - 1) return;
        historyPos++;
        overlays = JSON.parse(history[historyPos]);
        render();
        updateHistoryBtns();
    }
    function updateHistoryBtns() {
        btnUndo.disabled = historyPos <= 0;
        btnRedo.disabled = historyPos >= history.length - 1;
    }

    /* ── UTILS ── */
    const clamp = (n, lo, hi) => Math.max(lo, Math.min(n, hi));

    /* ── AUTO-FIT TEXT ──────────────────────────────────────────
       Starts at maxFontPx (85% of box height) and decrements 1px
       until the text no longer overflows the box.
       Called via requestAnimationFrame after the element is in DOM.

       @param {HTMLElement} el   - the .text-inner div
       @param {number}      boxW - available width  (box - padding*2)
       @param {number}      boxH - available height (box - padding*2)
    ── */
    function autoFitText(el, boxW, boxH) {
        const MIN = 6;
        const MAX = Math.max(MIN, Math.floor(Math.max(boxH * 0.85, boxW * 0.3)));
        let size  = MAX;
        el.style.width      = '100%';
        el.style.maxWidth   = '';
        el.style.whiteSpace = 'normal';
        el.style.wordBreak  = 'break-word';
        el.style.overflow   = 'hidden';
        el.style.fontSize   = size + 'px';
        while (size > MIN && (el.scrollWidth > boxW + 1 || el.scrollHeight > boxH + 1)) {
            size--;
            el.style.fontSize = size + 'px';
        }
    }

    /* ── NORMALIZE ── */
    function normalizeSettings(ov) {
        ov.overlay_type = ov.overlay_type || 'logo';
        ov.settings     = ov.settings     || {};
        ov.label        = ov.label        || (ov.overlay_type === 'logo' ? 'Logo' : 'Text');

        if (ov.overlay_type === 'logo') {
            ov.settings = Object.assign({
                radius: 0, opacity: 1, objectFit: 'contain',
                borderWidth: 0, borderColor: '#ffffff', borderStyle: 'solid',
                shadow: 'none', filter: 'none'
            }, ov.settings);
        } else {
            const existingBg = ov.settings.bg || {};
            ov.settings.bg = Object.assign({ enabled: true, color: '#ffffff', padding: 0, radius: 0 }, existingBg);
            // font_size intentionally NOT in defaults — auto-fit handles sizing
            ov.settings = Object.assign({
                text_key: 'branch_name', color: '#000000',
                align: 'center', line_height: 1.2, weight: 'normal'
            }, ov.settings);
        }
        return ov;
    }

    function defaultOverlay() {
        return normalizeSettings({
            overlay_type: 'logo',
            x: 0.05, y: 0.05, width: 0.20, height: 0.20, settings: {}
        });
    }

    /* ── SAFE AREA ── */
    function renderSafeArea() {
        if (!toggleSafeArea.checked) { safeAreaEl?.remove(); safeAreaEl = null; return; }
        if (!safeAreaEl) {
            safeAreaEl = document.createElement('div');
            safeAreaEl.className = 'safe-area';
            overlayLayer.appendChild(safeAreaEl);
        }
        const m = 0.05, W = overlayLayer.clientWidth, H = overlayLayer.clientHeight;
        Object.assign(safeAreaEl.style, {
            left: W*m+'px', top: H*m+'px', width: W*(1-m*2)+'px', height: H*(1-m*2)+'px'
        });
    }

    /* ── INIT ── */
    function initOverlays() {
        overlays = overlays.map(o => {
            let s = {};
            try { s = typeof o.settings === 'string' ? JSON.parse(o.settings) : (o.settings || {}); } catch(e) {}
            return normalizeSettings({
                overlay_type: o.overlay_type || 'logo',
                x:      parseFloat(o.x)      || 0.05,
                y:      parseFloat(o.y)      || 0.05,
                width:  parseFloat(o.width)  || 0.20,
                height: parseFloat(o.height) || 0.20,
                label:  o.label || null,
                settings: s
            });
        });
        if (!overlays.length) overlays.push(defaultOverlay());
        if (selectedIndex < 0) selectedIndex = 0;
        snapshot();
        isDirty = false;
    }

    /* ── APPLY IMAGE STYLES on <img> directly ── */
    function applyImgStyles(img, s) {
        img.style.borderRadius  = s.radius + 'px';
        img.style.opacity       = s.opacity;
        img.style.objectFit     = s.objectFit;
        img.style.boxShadow     = s.shadow;
        img.style.filter        = s.filter;
        img.style.border        = (+s.borderWidth > 0)
            ? `${s.borderWidth}px ${s.borderStyle} ${s.borderColor}` : 'none';
        img.style.width         = '100%';
        img.style.height        = '100%';
        img.style.display       = 'block';
        img.style.pointerEvents = 'none';
    }

    /* ── RENDER EDITOR ── */
    function render() {
        overlayLayer.querySelectorAll('.overlay-box').forEach(el => {
            try { interact(el).unset(); } catch(e) {}
        });
        overlayLayer.innerHTML = '';
        listEl.innerHTML       = '';
        renderSafeArea();

        const W = overlayLayer.clientWidth;
        const H = overlayLayer.clientHeight;

        overlays.forEach((ov, i) => {
            const px = ov.x      * W;
            const py = ov.y      * H;
            const pw = ov.width  * W;
            const ph = ov.height * H;

            /* ── sidebar list item ── */
            const li = document.createElement('div');
            li.className = 'list-item' + (i === selectedIndex ? ' active' : '');

            const icon = document.createElement('span');
            icon.textContent = ov.overlay_type === 'logo' ? '🖼️' : '✏️';

            const nameInput = document.createElement('input');
            nameInput.className = 'layer-name';
            nameInput.value     = ov.label;
            nameInput.title     = 'Double-click to rename';
            nameInput.readOnly  = true;
            nameInput.ondblclick = () => { nameInput.readOnly = false; nameInput.focus(); nameInput.select(); };
            nameInput.onblur    = () => {
                nameInput.readOnly = true;
                if (nameInput.value.trim()) { ov.label = nameInput.value.trim(); }
                else nameInput.value = ov.label;
            };
            nameInput.onkeydown = e => { if (e.key === 'Enter') nameInput.blur(); };
            nameInput.onclick   = () => { if (nameInput.readOnly) { selectedIndex = i; openPanel(); render(); } };

            li.appendChild(icon);
            li.appendChild(nameInput);
            listEl.appendChild(li);

            /* ── overlay box ── */
            const box = document.createElement('div');
            box.className = `overlay-box type-${ov.overlay_type}` + (i === selectedIndex ? ' selected' : '');
            // Smaller boxes get higher z-index so pointer events reach them first
            // when a large box overlaps them. Selected box gets an extra boost.
            const boxArea = ov.width * ov.height;
            // Invert: smaller area → higher z. Scale to keep values reasonable.
            const areaZ   = Math.round(1000 / Math.max(boxArea, 0.0001));
            const selectZ = (i === selectedIndex) ? 2000 : 0;
            box.style.zIndex = areaZ + selectZ;
            Object.assign(box.style, { left: px+'px', top: py+'px', width: pw+'px', height: ph+'px' });

            if (ov.overlay_type === 'text') {
                const bg = ov.settings.bg;
                const pad = bg.enabled ? bg.padding : 0;
                Object.assign(box.style, {
                    background:   bg.enabled ? bg.color : 'transparent',
                    borderRadius: bg.radius  + 'px',
                    padding:      pad        + 'px'
                });

                const t = document.createElement('div');
                t.className = 'text-inner';
                Object.assign(t.style, {
                    justifyContent: ov.settings.align === 'left'  ? 'flex-start' :
                                    ov.settings.align === 'right' ? 'flex-end'   : 'center',
                    textAlign:  ov.settings.align,
                    color:      ov.settings.color,
                    lineHeight: String(ov.settings.line_height),
                    fontWeight: String(ov.settings.weight),
                    // font-size intentionally NOT set here — autoFitText() sets it below
                });
                // Use realistic-length sample text so autoFitText finds the
                // same approximate font size that GD will use with real branch data.
                // Short placeholders like '{{BRANCH_NAME}}' auto-fit much larger
                // than real text, making the editor canvas misleading.
                t.textContent =
                    ov.settings.text_key === 'branch_address'
                        ? 'Sample Branch Address, City, State - 000000'
                    : ov.settings.text_key === 'branch_contact'
                        ? '0000000000'
                    : 'Sample Branch Name';
                box.appendChild(t);

                // AUTO-FIT: run after box is in DOM so scrollWidth/Height are measurable
                const innerW = pw - pad * 2;
                const innerH = ph - pad * 2;
                requestAnimationFrame(() => autoFitText(t, innerW, innerH));

            } else {
                // Logo placeholder — show a clear "LOGO" box so super admin
                // can see the exact position/size without needing a real logo file
                const img = document.createElement('img');
                img.src = demoLogo;
                img.onerror = function() {
                    // Fallback: SVG placeholder if logo file missing
                    this.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23e8f0fe' rx='8'/%3E%3Crect x='2' y='2' width='196' height='196' fill='none' stroke='%234285f4' stroke-width='2' stroke-dasharray='6 3' rx='6'/%3E%3Ctext x='100' y='90' text-anchor='middle' font-family='Arial' font-size='28' font-weight='bold' fill='%234285f4'%3ELOGO%3C/text%3E%3Ctext x='100' y='120' text-anchor='middle' font-family='Arial' font-size='13' fill='%23888'%3EBranch logo here%3C/text%3E%3C/svg%3E";
                };
                applyImgStyles(img, ov.settings);
                box.appendChild(img);
            }

            /* ── Inject 8 resize handles + 1 move handle into every box ──
               They are always 14px and positioned outside the box boundary,
               so they remain visible and grabbable even on a tiny box.
               CSS hides them unless box has .selected class.
            */
            ['nw','n','ne','e','se','s','sw','w'].forEach(pos => {
                const h = document.createElement('div');
                h.className = `box-handle ${pos}`;
                box.appendChild(h);
            });

            overlayLayer.appendChild(box);

            /* ── Per-box click: select this box on tap (not drag) ── */
            box.dataset.idx = i; // store index for event delegation
            box.style.pointerEvents = (i === selectedIndex) ? 'auto' : 'none';
            // Handles always receive pointer events for selection click
            box.querySelectorAll('.box-handle').forEach(h => h.style.pointerEvents = 'none');

            interact(box)
                .draggable({
                    inertia: false,
                    listeners: {
                        move(e) {
                            const cW = overlayLayer.clientWidth;
                            const cH = overlayLayer.clientHeight;
                            const newPx = clamp(ov.x*cW + e.dx, 0, cW - ov.width*cW);
                            const newPy = clamp(ov.y*cH + e.dy, 0, cH - ov.height*cH);
                            ov.x = newPx / cW;
                            ov.y = newPy / cH;
                            box.style.left = newPx + 'px';
                            box.style.top  = newPy + 'px';
                        },
                        end() { snapshot(); }
                    }
                })
                .resizable({
                    edges: { left: true, right: true, bottom: true, top: true },
                    listeners: {
                        move(e) {
                            /* Use e.deltaRect (change per edge) NOT e.rect (absolute viewport).
                               e.rect is viewport-relative and breaks when page is scrolled.
                               e.deltaRect gives the delta of each edge this frame — immune
                               to scroll, reflow, zoom, or any layout changes mid-resize. */
                            const cW = overlayLayer.clientWidth;
                            const cH = overlayLayer.clientHeight;
                            const d  = e.deltaRect;

                            let px = ov.x      * cW;
                            let py = ov.y      * cH;
                            let pw = ov.width  * cW;
                            let ph = ov.height * cH;

                            const newPx = clamp(px + d.left,             0, cW - 1);
                            const newPy = clamp(py + d.top,              0, cH - 1);
                            const newPw = clamp(pw - d.left + d.right,   1, cW);
                            const newPh = clamp(ph - d.top  + d.bottom,  1, cH);

                            ov.x = newPx / cW; ov.y = newPy / cH;
                            ov.width = newPw / cW; ov.height = newPh / cH;
                            Object.assign(box.style, {
                                left: newPx+'px', top: newPy+'px', width: newPw+'px', height: newPh+'px'
                            });
                            if (ov.overlay_type === 'text') {
                                const t   = box.querySelector('.text-inner');
                                const pad = ov.settings.bg.enabled ? ov.settings.bg.padding : 0;
                                if (t) autoFitText(t, newPw - pad*2, newPh - pad*2);
                            }
                        },
                        end() { snapshot(); }
                    }
                });
        });

        /* ── Selection via overlayLayer pointerdown ──
           Use pointerdown (not pointerup) so selection happens BEFORE
           interact.js decides what to drag.
           Finds the SMALLEST box under the cursor — always the right target.
        */
        if (overlayLayer._selHandler) {
            overlayLayer.removeEventListener('pointerdown', overlayLayer._selHandler);
        }
        overlayLayer._selHandler = function(e) {
            // Ignore if clicking a handle (those are on the selected box)
            if (e.target.classList.contains('box-handle')) return;
            if (e.target.classList.contains('safe-area'))  return;

            const layerRect = overlayLayer.getBoundingClientRect();
            const cx = e.clientX - layerRect.left;
            const cy = e.clientY - layerRect.top;

            let bestIdx  = -1;
            let bestArea = Infinity;

            overlays.forEach((ov, idx) => {
                const bx = ov.x      * overlayLayer.clientWidth;
                const by = ov.y      * overlayLayer.clientHeight;
                const bw = ov.width  * overlayLayer.clientWidth;
                const bh = ov.height * overlayLayer.clientHeight;
                const margin = 6;
                if (cx >= bx - margin && cx <= bx + bw + margin &&
                    cy >= by - margin && cy <= by + bh + margin) {
                    const area = bw * bh;
                    if (area < bestArea) { bestArea = area; bestIdx = idx; }
                }
            });

            if (bestIdx === -1 || bestIdx === selectedIndex) return;

            selectedIndex = bestIdx;

            // Update UI
            listEl.querySelectorAll('.list-item').forEach((l, idx) =>
                l.classList.toggle('active', idx === bestIdx));

            // KEY: set pointer-events — only selected box gets interact
            overlayLayer.querySelectorAll('.overlay-box').forEach((b, idx) => {
                b.classList.toggle('selected', idx === bestIdx);
                const area = overlays[idx].width * overlays[idx].height;
                b.style.zIndex       = Math.round(1000 / Math.max(area, 0.0001)) + (idx === bestIdx ? 2000 : 0);
                b.style.pointerEvents = idx === bestIdx ? 'auto' : 'none';
            });

            openPanel();
        };
        overlayLayer.addEventListener('pointerdown', overlayLayer._selHandler);

        if (panelOpen && overlays[selectedIndex]) loadSettings();
    }

    /* ── PREVIEW RENDER — clean, no borders/handles, auto-fit text ── */
    function renderPreview() {
        previewOverlayLayer.style.width  = previewBaseImg.clientWidth  + 'px';
        previewOverlayLayer.style.height = previewBaseImg.clientHeight + 'px';
        previewOverlayLayer.innerHTML    = '';

        const W = previewBaseImg.clientWidth;
        const H = previewBaseImg.clientHeight;

        overlays.forEach(ov => {
            const px = ov.x      * W;
            const py = ov.y      * H;
            const pw = ov.width  * W;
            const ph = ov.height * H;

            const box = document.createElement('div');
            box.className = `preview-box type-${ov.overlay_type}`;
            Object.assign(box.style, { left: px+'px', top: py+'px', width: pw+'px', height: ph+'px' });

            if (ov.overlay_type === 'text') {
                const bg  = ov.settings.bg;
                const pad = bg.enabled ? bg.padding : 0;
                Object.assign(box.style, {
                    background:   bg.enabled ? bg.color : 'transparent',
                    borderRadius: bg.radius  + 'px',
                    padding:      pad        + 'px'
                });

                const t = document.createElement('div');
                t.className = 'text-inner';
                Object.assign(t.style, {
                    justifyContent: ov.settings.align === 'left'  ? 'flex-start' :
                                    ov.settings.align === 'right' ? 'flex-end'   : 'center',
                    textAlign:  ov.settings.align,
                    color:      ov.settings.color,
                    lineHeight: String(ov.settings.line_height),
                    fontWeight: String(ov.settings.weight),
                    // font-size set by autoFitText below
                });
                t.textContent =
                    ov.settings.text_key === 'branch_address'
                        ? 'Sample Branch Address, City, State - 000000'
                    : ov.settings.text_key === 'branch_contact'
                        ? '0000000000'
                    : 'Sample Branch Name';
                box.appendChild(t);

                previewOverlayLayer.appendChild(box);

                // AUTO-FIT in preview
                const innerW = pw - pad * 2;
                const innerH = ph - pad * 2;
                requestAnimationFrame(() => autoFitText(t, innerW, innerH));

            } else {
                const img   = document.createElement('img');
                const s     = ov.settings;
                const scale = W / overlayLayer.clientWidth;
                img.src = demoLogo;
                img.onerror = function() {
                    this.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23e8f0fe' rx='8'/%3E%3Crect x='2' y='2' width='196' height='196' fill='none' stroke='%234285f4' stroke-width='2' stroke-dasharray='6 3' rx='6'/%3E%3Ctext x='100' y='90' text-anchor='middle' font-family='Arial' font-size='28' font-weight='bold' fill='%234285f4'%3ELOGO%3C/text%3E%3Ctext x='100' y='120' text-anchor='middle' font-family='Arial' font-size='13' fill='%23888'%3EBranch logo here%3C/text%3E%3C/svg%3E";
                };
                img.style.borderRadius = s.radius + 'px';
                img.style.opacity      = s.opacity;
                img.style.objectFit    = s.objectFit;
                img.style.boxShadow    = s.shadow;
                img.style.filter       = s.filter;
                img.style.border       = (+s.borderWidth > 0)
                    ? `${Math.round(s.borderWidth * scale)}px ${s.borderStyle} ${s.borderColor}` : 'none';
                img.style.width        = '100%';
                img.style.height       = '100%';
                img.style.display      = 'block';
                box.appendChild(img);

                previewOverlayLayer.appendChild(box);
            }
        });
    }

    /* ── RESIZE OBSERVER — ZOOM FIX ──
       Fires on any browser zoom or window resize.
       Ratios survive because we re-render from stored ratios, not px.
    */
    new ResizeObserver(() => {
        overlayLayer.style.width  = baseImg.clientWidth  + 'px';
        overlayLayer.style.height = baseImg.clientHeight + 'px';
        render();
    }).observe(baseImg);

    /* ── FLOAT PANEL ── */
    function openPanel() {
        if (!overlays[selectedIndex]) return;
        panelOpen = true;
        floatPanel.classList.add('show');
        const ov = overlays[selectedIndex];
        fpTitle.textContent     = `${ov.label} (${ov.overlay_type})`;
        logoTools.style.display = ov.overlay_type === 'logo' ? 'block' : 'none';
        textTools.style.display = ov.overlay_type === 'text' ? 'block' : 'none';
        loadSettings();
    }
    function closePanel() { panelOpen = false; floatPanel.classList.remove('show'); }
    fpClose.onclick = closePanel;

    /* ── LOAD SETTINGS ── */
    function loadSettings() {
        const ov = overlays[selectedIndex];
        if (!ov) return;
        if (ov.overlay_type === 'logo') {
            const s = ov.settings;
            imgRadius.value         = s.radius;
            imgRadiusVal.innerText  = s.radius + 'px';
            imgOpacity.value        = s.opacity;
            imgOpacityVal.innerText = Math.round(s.opacity * 100) + '%';
            imgObjectFit.value      = s.objectFit;
            imgBorderWidth.value    = s.borderWidth;
            imgBorderColor.value    = s.borderColor;
            imgBorderStyle.value    = s.borderStyle;
            imgShadow.value         = s.shadow;
            imgFilter.value         = s.filter;
        } else {
            const s = ov.settings, bg = s.bg;
            // NO font_size to load — auto-fit handles it
            textColor.value         = s.color;
            lineHeight.value        = s.line_height;
            lineHeightVal.innerText = s.line_height;
            fontWeight.value        = s.weight;
            bgEnabled.value         = bg.enabled ? '1' : '0';
            bgColor.value           = bg.color;
            bgPadding.value         = bg.padding;
            bgRadius.value          = bg.radius;
            bgOptions.style.display = bg.enabled ? 'block' : 'none';
            document.querySelectorAll('.align-btn').forEach(b =>
                b.classList.toggle('act', b.dataset.align === s.align));
        }
    }

    function getSelectedImg() {
        return overlayLayer.querySelectorAll('.overlay-box')[selectedIndex]?.querySelector('img') || null;
    }

    /* ── LOGO CONTROLS ── */
    function syncLogoSettings() {
        if (!overlays[selectedIndex]) return;
        Object.assign(overlays[selectedIndex].settings, {
            radius:      +imgRadius.value,
            opacity:     +imgOpacity.value,
            objectFit:   imgObjectFit.value,
            borderWidth: +imgBorderWidth.value,
            borderColor: imgBorderColor.value,
            borderStyle: imgBorderStyle.value,
            shadow:      imgShadow.value,
            filter:      imgFilter.value
        });
        const img = getSelectedImg();
        if (img) applyImgStyles(img, overlays[selectedIndex].settings);
    }
    function syncLogoAndSnapshot() { syncLogoSettings(); snapshot(); }

    imgRadius.oninput  = () => { imgRadiusVal.innerText = imgRadius.value + 'px'; syncLogoSettings(); };
    imgRadius.onchange = snapshot;
    imgOpacity.oninput  = () => { imgOpacityVal.innerText = Math.round(+imgOpacity.value * 100) + '%'; syncLogoSettings(); };
    imgOpacity.onchange = snapshot;
    [imgObjectFit, imgBorderWidth, imgBorderColor, imgBorderStyle, imgShadow, imgFilter]
        .forEach(el => { el.oninput = syncLogoSettings; el.onchange = syncLogoAndSnapshot; });

    /* ── TEXT CONTROLS ── */
    textColor.oninput  = () => {
        if (!overlays[selectedIndex]) return;
        overlays[selectedIndex].settings.color = textColor.value;
        render();
    };
    textColor.onchange = snapshot;

    lineHeight.oninput = () => {
        if (!overlays[selectedIndex]) return;
        overlays[selectedIndex].settings.line_height = +lineHeight.value;
        lineHeightVal.innerText = lineHeight.value;
        render();
    };
    lineHeight.onchange = snapshot;

    fontWeight.onchange = () => {
        if (!overlays[selectedIndex]) return;
        overlays[selectedIndex].settings.weight = fontWeight.value;
        render();
        snapshot();
    };

    document.querySelectorAll('.align-btn').forEach(b => b.onclick = () => {
        if (!overlays[selectedIndex]) return;
        overlays[selectedIndex].settings.align = b.dataset.align;
        document.querySelectorAll('.align-btn').forEach(x => x.classList.toggle('act', x === b));
        render();
        snapshot();
    });

    bgEnabled.onchange = () => {
        if (!overlays[selectedIndex]) return;
        overlays[selectedIndex].settings.bg.enabled = bgEnabled.value === '1';
        bgOptions.style.display = bgEnabled.value === '1' ? 'block' : 'none';
        render();
        snapshot();
    };
    bgColor.oninput   = () => { if (overlays[selectedIndex]) { overlays[selectedIndex].settings.bg.color   = bgColor.value;    render(); } };
    bgColor.onchange  = snapshot;
    bgPadding.oninput = () => { if (overlays[selectedIndex]) { overlays[selectedIndex].settings.bg.padding = +bgPadding.value; render(); } };
    bgPadding.onchange = snapshot;
    bgRadius.oninput  = () => { if (overlays[selectedIndex]) { overlays[selectedIndex].settings.bg.radius  = +bgRadius.value;  render(); } };
    bgRadius.onchange = snapshot;

    /* ── LAYER BUTTONS ── */
    btnAdd.onclick = () => {
        overlays.push(defaultOverlay());
        selectedIndex = overlays.length - 1;
        snapshot(); render(); openPanel();
    };
    btnAddText.onclick = e => {
        // Use the button's bounding rect for position.
        // position:fixed so no scroll offset needed, but ensure it doesn't
        // go offscreen by clamping to viewport.
        const r   = e.target.getBoundingClientRect();
        const pW  = 200; // picker min-width
        const top = r.bottom + 6;
        const left = Math.min(r.left, window.innerWidth - pW - 10);
        Object.assign(textPicker.style, {
            display: 'block',
            left: left + 'px',
            top:  top  + 'px'
        });
        e.stopPropagation(); // prevent immediate close from document click handler
    };
    textPickerCancel.onclick  = () => { textPicker.style.display = 'none'; };
    textPickerConfirm.onclick = () => {
        overlays.push(normalizeSettings({
            overlay_type: 'text', x: 0.10, y: 0.10, width: 0.40, height: 0.08,
            settings: { text_key: textPickerSelect.value }
        }));
        selectedIndex = overlays.length - 1;
        textPicker.style.display = 'none';
        snapshot(); render(); openPanel();
    };
    document.addEventListener('click', e => {
        if (!textPicker.contains(e.target) && e.target !== btnAddText)
            textPicker.style.display = 'none';
    });

    btnUp.onclick = () => {
        if (selectedIndex <= 0) return;
        [overlays[selectedIndex-1], overlays[selectedIndex]] = [overlays[selectedIndex], overlays[selectedIndex-1]];
        selectedIndex--;
        snapshot(); render();
    };
    btnDown.onclick = () => {
        if (selectedIndex >= overlays.length - 1) return;
        [overlays[selectedIndex+1], overlays[selectedIndex]] = [overlays[selectedIndex], overlays[selectedIndex+1]];
        selectedIndex++;
        snapshot(); render();
    };
    btnDelete.onclick = () => {
        if (!overlays.length) return;
        overlays.splice(selectedIndex, 1);
        selectedIndex = Math.max(0, selectedIndex - 1);
        if (!overlays.length) { selectedIndex = -1; closePanel(); }
        snapshot(); render();
    };
    btnDuplicate.onclick = () => {
        if (!overlays[selectedIndex]) return;
        const clone = JSON.parse(JSON.stringify(overlays[selectedIndex]));
        clone.x     = Math.min(clone.x + 0.03, 0.92);
        clone.y     = Math.min(clone.y + 0.03, 0.92);
        clone.label = clone.label + ' (copy)';
        overlays.push(clone);
        selectedIndex = overlays.length - 1;
        snapshot(); render(); openPanel();
    };

    btnUndo.onclick = undo;
    btnRedo.onclick = redo;
    toggleSafeArea.onchange = () => render();

    /* ── PREVIEW ── */
    btnPreview.onclick = () => {
        previewModal.classList.add('open');
        requestAnimationFrame(() => {
            previewOverlayLayer.style.width  = previewBaseImg.clientWidth  + 'px';
            previewOverlayLayer.style.height = previewBaseImg.clientHeight + 'px';
            renderPreview();
        });
    };
    [previewClose, previewCloseBtn].forEach(b => b.onclick = () => previewModal.classList.remove('open'));
    previewModal.addEventListener('click', e => {
        if (e.target === previewModal) previewModal.classList.remove('open');
    });
    previewBaseImg.onload = () => {
        previewOverlayLayer.style.width  = previewBaseImg.clientWidth  + 'px';
        previewOverlayLayer.style.height = previewBaseImg.clientHeight + 'px';
    };

    /* ── KEYBOARD SHORTCUTS ── */
    document.addEventListener('keydown', e => {
        if (['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) return;
        if (e.ctrlKey && e.key === 'z') { e.preventDefault(); undo(); }
        if (e.ctrlKey && e.key === 'y') { e.preventDefault(); redo(); }
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (selectedIndex >= 0 && overlays.length) {
                overlays.splice(selectedIndex, 1);
                selectedIndex = Math.max(0, selectedIndex - 1);
                if (!overlays.length) { selectedIndex = -1; closePanel(); }
                snapshot(); render();
            }
        }
        if (e.key === 'Escape') closePanel();
    });

    /* ── UNSAVED CHANGES WARNING ── */
    window.addEventListener('beforeunload', e => {
        if (isDirty) { e.preventDefault(); e.returnValue = 'You have unsaved changes. Leave anyway?'; }
    });
    btnBack.addEventListener('click', e => {
        if (isDirty && !confirm('You have unsaved changes. Leave without saving?')) e.preventDefault();
    });

    /* ── SAVE ── */
    document.getElementById('btnSave').onclick = () => {
        const payload = overlays.map(o => ({
            overlay_type: o.overlay_type,
            x: o.x, y: o.y, width: o.width, height: o.height,
            label: o.label,
            settings: o.settings   // font_size no longer in settings
        }));
        const fd = new FormData();
        fd.append('overlays', JSON.stringify(payload));
        fd.append("<?= $this->security->get_csrf_token_name(); ?>", "<?= $this->security->get_csrf_hash(); ?>");
        fetch(saveUrl, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(j => {
                if (j.status === 'success') {
                    isDirty = false;
                    msg.innerHTML = '<span style="color:green">✅ Saved</span>';
                } else {
                    msg.innerHTML = '<span style="color:red">❌ ' + j.message + '</span>';
                }
                setTimeout(() => msg.innerHTML = '', 3000);
            })
            .catch(() => msg.innerHTML = '<span style="color:red">❌ Save failed</span>');
    };

    /* ── BOOT ── */
    baseImg.onload = () => {
        overlayLayer.style.width  = baseImg.clientWidth  + 'px';
        overlayLayer.style.height = baseImg.clientHeight + 'px';
        initOverlays();
        render();
    };
    if (baseImg.complete) baseImg.onload();

})();
</script>