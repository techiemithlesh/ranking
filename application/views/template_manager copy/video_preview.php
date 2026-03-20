<style>
/* Canvas is sized by JS to exact video pixel dimensions (capped at viewport).
   This matches the original fitCanvasToVideo() behaviour — real 1:1 preview. */
#editor-canvas {
    position: relative;
    margin: auto;
    background: #000;
    display: block;
    /* width + height set by JS */
}

#videoPlayer {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain; /* fills canvas exactly */
}

/* Overlay layer fills the canvas exactly — no letterbox offset needed
   because video fills the canvas via object-fit:contain at the same ratio */
#overlay-layer {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    overflow: hidden;
}

.overlay-item {
    position: absolute;
    box-sizing: border-box;
    overflow: hidden;
    pointer-events: none;
    display: none; /* hidden until in time range */
}

.overlay-content {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    word-break: break-word;
    white-space: normal;
    line-height: 1.2;
    box-sizing: border-box;
}
</style>

<section class="panel">
    <header class="panel-heading">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h4 class="panel-title">
                <?= translate('preview_template') ?> : <?= html_escape($template['title']) ?>
            </h4>
            <div>
                <?php if ($template['type'] === 'video'): ?>
                    <a href="<?= base_url('Video_editor/download/' . $template['id']) ?>"
                       class="btn btn-success btn-sm">
                        <i class="fa fa-download"></i> <?= translate('Download') ?>
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('Template_manager') ?>" class="btn btn-default btn-sm">
                    <i class="fas fa-arrow-left"></i> <?= translate('back') ?>
                </a>
            </div>
        </div>
    </header>

    <div class="panel-body">
        <div class="col-md-12 text-center">

            <div id="editor-canvas">
                <video id="videoPlayer" controls>
                    <source src="<?= base_url($video['file_path']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div id="overlay-layer"></div>
            </div>

        </div>
    </div>
</section>

<script>
const BRANCH_DATA = {
    branch_name:    "<?= addslashes($branch['name'])     ?>",
    branch_address: "<?= addslashes($branch['address'])  ?>",
    branch_contact: "<?= addslashes($branch['mobileno']) ?>",
    branch_logo:    "<?= addslashes($branch_logo)        ?>"
};
const overlays = <?= $overlays_json ?>;

document.addEventListener("DOMContentLoaded", () => {

    const video        = document.getElementById("videoPlayer");
    const overlayLayer = document.getElementById("overlay-layer");
    const canvas       = document.getElementById("editor-canvas");
    const elements     = {};

    /* ═══════════════════════════════════════
       AUTO-FIT TEXT — same as editor
    ═══════════════════════════════════════ */
    function autoFitText(el, boxW, boxH) {
        if (!el || boxW <= 0 || boxH <= 0) return;
        const MIN = 8;
        const MAX = Math.max(MIN, Math.floor(Math.max(boxH * 0.85, boxW * 0.3)));
        let size  = MAX;
        el.style.fontSize   = size + "px";
        el.style.whiteSpace = "normal";
        el.style.wordBreak  = "break-word";
        while (size > MIN && (el.scrollWidth > boxW + 1 || el.scrollHeight > boxH + 1)) {
            size--;
            el.style.fontSize = size + "px";
        }
    }

    function reflowAllText() {
        overlays.forEach(o => {
            if (o.overlay_type === "logo") return;
            const el = elements[o.id];
            if (!el) return;
            const cnt = el.querySelector(".overlay-content");
            if (cnt) requestAnimationFrame(() =>
                autoFitText(cnt, cnt.clientWidth, cnt.clientHeight));
        });
    }

    /* ═══════════════════════════════════════
       FIT CANVAS TO VIDEO — original behaviour
       Canvas is sized to the video's natural pixel
       dimensions, capped at 75% viewport height.
       Video fills the canvas exactly via object-fit:contain
       so overlay layer (100%×100%) aligns perfectly —
       no letterbox offset calculation needed.
    ═══════════════════════════════════════ */
    function syncLayer() {
        const vw = video.videoWidth;
        const vh = video.videoHeight;
        if (!vw || !vh) return;

        const containerWidth = canvas.parentElement.clientWidth;
        const maxHeight      = window.innerHeight * 0.75;

        // Start at natural video size, constrain to container width
        let width  = Math.min(vw, containerWidth);
        let height = width * (vh / vw);

        // If still too tall, constrain to maxHeight
        if (height > maxHeight) {
            height = maxHeight;
            width  = height * (vw / vh);
        }

        canvas.style.width  = Math.round(width)  + "px";
        canvas.style.height = Math.round(height) + "px";
        // Overlay layer is 100%×100% in CSS so it auto-matches canvas
    }

    /* ═══════════════════════════════════════
       APPLY POSITION — ratios → px within layer
    ═══════════════════════════════════════ */
    function applyPosition(el, o) {
        el.style.left   = (parseFloat(o.x)      || 0)   * 100 + "%";
        el.style.top    = (parseFloat(o.y)      || 0)   * 100 + "%";
        el.style.width  = (parseFloat(o.width)  || 0.2) * 100 + "%";
        el.style.height = (parseFloat(o.height) || 0.1) * 100 + "%";
    }

    /* ═══════════════════════════════════════
       APPLY TEXT BOX STYLES
    ═══════════════════════════════════════ */
    function applyTextStyles(el, s) {
        if (!s) return;

        // Background + padding + radius on the BOX element
        if (s.bg && s.bg.enabled === true) {
            el.style.background   = s.bg.color   || "rgba(0,0,0,0.5)";
            el.style.padding      = (s.bg.padding || 0) + "px";
            el.style.borderRadius = (s.bg.radius  || 0) + "px";
        } else {
            el.style.background   = "transparent";
            el.style.padding      = "0px";
            el.style.borderRadius = "0px";
        }

        // Text styles on the CONTENT div inside
        const c = el.querySelector(".overlay-content");
        if (!c) return;
        c.style.color          = s.color       || "#ffffff";
        c.style.display        = "flex";
        c.style.alignItems     = "center";
        c.style.justifyContent = "center";
        c.style.width          = "100%";
        c.style.height         = "100%";
        c.style.whiteSpace     = "normal";
        c.style.wordBreak      = "break-word";
        c.style.overflow       = "hidden";
        // font-size NOT set — autoFitText handles it
    }

    /* ═══════════════════════════════════════
       APPLY LOGO STYLES — mirrors editor
    ═══════════════════════════════════════ */
    function applyLogoStyles(el, s) {
        const img = el.querySelector("img");
        if (!img || !s) return;
        img.style.borderRadius = (s.radius     || 0)   + "px";
        img.style.opacity      =  s.opacity    !== undefined ? s.opacity : 1;
        img.style.objectFit    =  s.objectFit  || "contain";
        img.style.boxShadow    =  s.shadow     || "none";
        img.style.filter       =  s.filter     || "none";
        img.style.border       = (+s.borderWidth > 0)
            ? `${s.borderWidth}px ${s.borderStyle || "solid"} ${s.borderColor || "#ffffff"}`
            : "none";
    }

    /* ═══════════════════════════════════════
       CREATE OVERLAY ELEMENT
    ═══════════════════════════════════════ */
    function createOverlay(o) {
        // Safe settings decode
        if (typeof o.settings === "string") {
            try { o.settings = JSON.parse(o.settings); } catch(e) { o.settings = {}; }
        }
        if (!o.settings) o.settings = {};

        const el = document.createElement("div");
        el.className = "overlay-item";
        el.style.display = "none"; // hidden until in time range

        const content = document.createElement("div");
        content.className = "overlay-content";

        if (o.overlay_type === "logo") {
            const img = document.createElement("img");
            img.src            = BRANCH_DATA.branch_logo;
            img.style.cssText  = "width:100%;height:100%;display:block;";
            content.appendChild(img);
            el.appendChild(content);
            applyPosition(el, o);
            applyLogoStyles(el, o.settings);

        } else {
            const key  = o.settings.text_key || o.variable || "";
            const text = BRANCH_DATA[key] || "";
            content.textContent = text;
            el.appendChild(content);
            applyPosition(el, o);
            applyTextStyles(el, o.settings);

            // Auto-fit after appended to DOM
            // Use content's own clientWidth/Height (excludes padding on el)
            requestAnimationFrame(() =>
                autoFitText(content, content.clientWidth, content.clientHeight));
        }

        overlayLayer.appendChild(el);
        elements[o.id] = el;
    }

    /* ═══════════════════════════════════════
       TIMELINE VISIBILITY
    ═══════════════════════════════════════ */
    function updateVisibility() {
        const t = video.currentTime;
        overlays.forEach(o => {
            const el = elements[o.id];
            if (!el) return;
            el.style.display = (t >= o.start_time && t <= o.end_time) ? "flex" : "none";
        });
    }

    /* ═══════════════════════════════════════
       INIT
    ═══════════════════════════════════════ */
    function init() {
        syncLayer();
        overlays.forEach(o => createOverlay(o));
        // Re-fit all text after layer has real dimensions
        setTimeout(reflowAllText, 100);
    }

    video.addEventListener("loadedmetadata", init);
    if (video.readyState >= 1) init();

    video.addEventListener("timeupdate", updateVisibility);

    // Re-sync on resize + reflow text
    const ro = new ResizeObserver(() => { syncLayer(); reflowAllText(); });
    ro.observe(video);
    window.addEventListener("resize", () => { syncLayer(); reflowAllText(); });

});
</script>