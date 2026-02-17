<section class="panel">
    <header class="panel-heading">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h4 class="panel-title">
                <?= translate('preview_template') ?> : <?= html_escape($template['title']) ?>
            </h4>
            <a href="<?= base_url('Template_manager') ?>" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> <?= translate('back') ?>
            </a>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">
            <div class="col-md-12 text-center">

                <div id="editor-canvas">
                    <video id="videoPlayer" controls>
                        <source src="<?= base_url($video['file_path']) ?>" type="video/mp4">
                    </video>
                    <div id="overlay-layer"></div>
                </div>

            </div>
        </div>
    </div>
</section>

<style>
    #editor-canvas {
        position: relative;
        margin: auto;
        background: #000;
    }

    /* video fills canvas but keeps ratio */
    #videoPlayer {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    #overlay-layer {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .overlay-item {
        position: absolute;
        pointer-events: none;
        color: #fff;
        box-sizing: border-box;

        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;

        white-space: pre-wrap;
        /* allow wrapping */
        word-break: break-word;
        /* prevent overflow */
        overflow: hidden;
        /* simulate real render */
    }



    .overlay-content {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        white-space: pre;
        line-height: 1.2;
        box-sizing: border-box;
        word-break: normal;
    }

    .overlay-item.resizing .overlay-content {
        white-space: pre-wrap;
    }
</style>

<script>
    const BRANCH_DATA = {
        branch_name: "<?= addslashes($branch['name']) ?>",
        branch_address: "<?= addslashes($branch['address']) ?>",
        branch_contact: "<?= addslashes($branch['mobileno']) ?>",
        branch_logo: "<?= $branch_logo ?>"
    };

    const overlays = <?= $overlays_json ?>;

    document.addEventListener("DOMContentLoaded", () => {

        const video = document.getElementById("videoPlayer");
        const overlayLayer = document.getElementById("overlay-layer");
        const canvas = document.getElementById("editor-canvas");

        const elements = {};

        /* ---------------- CANVAS FIT TO VIDEO RATIO ---------------- */

        function fitCanvasToVideo() {

            const containerWidth = canvas.parentElement.clientWidth;
            const vw = video.videoWidth;
            const vh = video.videoHeight;
            if (!vw || !vh) return;

            let width = containerWidth;
            let height = width * (vh / vw);

            const maxHeight = window.innerHeight * 0.75;
            if (height > maxHeight) {
                height = maxHeight;
                width = height * (vw / vh);
            }

            canvas.style.width = width + "px";
            canvas.style.height = height + "px";
        }

        /* ---------------- HELPERS ---------------- */

        function resolveText(key) {
            return BRANCH_DATA[key] || "";
        }

        function applyPosition(el, o) {
            el.style.left = (parseFloat(o.x) || 0) * 100 + "%";
            el.style.top = (parseFloat(o.y) || 0) * 100 + "%";
            el.style.width = (parseFloat(o.width) || 0.2) * 100 + "%";
            el.style.height = (parseFloat(o.height) || 0.1) * 100 + "%";
        }

        function applyStyles(el, s) {
            if (!s) return;
            el.style.color = s.color || "#ffffff";
            el.style.fontSize = (s.font_size || 15) + "px";

            if (s.bg && s.bg.enabled === true) {
                el.style.background = s.bg.color || "rgba(0,0,0,0.5)";
                el.style.padding = (s.bg.padding || 5) + "px";
                el.style.borderRadius = (s.bg.radius || 4) + "px";
            } else {
                el.style.background = "transparent";
                el.style.padding = "0px";
                el.style.borderRadius = "0px";
            }

            el.style.display = "flex";
            el.style.alignItems = "center";
            el.style.justifyContent = "center";
        }

        /* ---------------- CREATE OVERLAYS ---------------- */

        function createOverlay(o) {

            const el = document.createElement("div");
            el.className = "overlay-item";

            const content = document.createElement("div");
            content.className = "overlay-content";

            if (typeof o.settings === "string") {
                try {
                    o.settings = JSON.parse(o.settings);
                } catch (e) {
                    o.settings = {};
                }
            }

            applyPosition(el, o);
            applyStyles(content, o.settings);

            if (o.overlay_type === "logo") {
                const img = document.createElement("img");
                img.src = BRANCH_DATA.branch_logo;
                img.style.width = "100%";
                img.style.height = "100%";
                img.style.objectFit = "contain";
                content.appendChild(img);
            } else {
                const key = o.settings?.text_key || o.variable || "";
                content.innerText = resolveText(key);
            }

            el.appendChild(content);
            el.style.display = "none";

            overlayLayer.appendChild(el);
            elements[o.id] = el;
        }

        /* ---------------- TIMELINE VISIBILITY ---------------- */

        function updateVisibility() {
            const t = video.currentTime;

            overlays.forEach(o => {
                const el = elements[o.id];
                if (!el) return;

                if (t >= o.start_time && t <= o.end_time)
                    el.style.display = "flex";
                else
                    el.style.display = "none";
            });
        }

        /* ---------------- INIT ---------------- */

        video.addEventListener("loadedmetadata", () => {
            fitCanvasToVideo();

            overlays.forEach(o => {
                if (typeof o.settings === "string") {
                    try {
                        o.settings = JSON.parse(o.settings);
                    } catch (e) {}
                }
                createOverlay(o);
            });
        });

        video.addEventListener("timeupdate", updateVisibility);

        window.addEventListener("resize", () => {
            fitCanvasToVideo();
        });

    });
</script>