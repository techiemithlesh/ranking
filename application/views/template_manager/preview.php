<style>
/* Load OpenSans to match GD renderer font */
@import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap');

/* ── PREVIEW STAGE ── */
#stage {
    display: inline-block;
    position: relative;
    border: 1px solid #ddd;
    background: #fafafa;
    max-width: 100%;
}
#stage img#baseImg {
    display: block;
    max-width: 100%;
    height: auto;
}

/* ── OVERLAY BOXES — clean, no borders, no dashes ── */
.overlay-box {
    position: absolute;
    box-sizing: border-box;
    overflow: hidden;
}

/* Logo wrapper: transparent, all styles on inner img */
.overlay-box.type-logo {
    background: transparent !important;
    padding: 0 !important;
    border-radius: 0 !important;
}
.overlay-box.type-logo img.logo-img {
    display: block;
    width: 100%;
    height: 100%;
    /* object-fit set via inline style from settings */
}

/* Text box inner */
.overlay-box.type-text .text-inner {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    overflow: hidden;
    word-break: break-word;
    white-space: normal;
    font-family: 'Open Sans', sans-serif;
    /* font-size set by autoFitText() in JS — not in PHP */
}

/* Download bar */
#downloadBar {
    margin-top: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}
</style>

<section class="panel">
    <header class="panel-heading">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h4 class="panel-title" style="margin:0;">
                <i class="fas fa-eye"></i> <?= translate('preview_template') ?>
                <small class="text-muted" style="font-size:13px;font-weight:normal;margin-left:8px;">
                    — <?= html_escape($template['title']) ?>
                </small>
            </h4>
            <a href="<?= base_url('Template_manager/branch_templates') ?>"
                class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> <?= translate('back') ?>
            </a>
        </div>
    </header>

    <div class="panel-body text-center">

        <div id="stage">
            <img src="<?= base_url($template['file_path']) ?>"
                id="baseImg"
                style="display:block;max-width:100%;height:auto;">

            <?php foreach ($overlays as $idx => $ov): ?>
                <?php
                /* ── decode settings ── */
                $settings = [];
                if (!empty($ov['settings'])) {
                    $settings = is_array($ov['settings'])
                        ? $ov['settings']
                        : (json_decode($ov['settings'], true) ?? []);
                }

                $type    = $ov['overlay_type'] ?? 'logo';
                $x       = (float)($ov['x']      ?? 0.05);
                $y       = (float)($ov['y']      ?? 0.05);
                $w       = (float)($ov['width']  ?? 0.20);
                $h       = (float)($ov['height'] ?? 0.20);

                /* ── text settings (font_size removed — JS autoFitText handles sizing) ── */
                $color      = $settings['color']      ?? '#000000';
                $align      = $settings['align']      ?? 'center';
                $lineHeight = (float)($settings['line_height'] ?? 1.2);
                $weight     = $settings['weight']     ?? 'normal';
                $textKey    = $settings['text_key']   ?? 'branch_name';

                /* ── text background ── */
                $bg         = $settings['bg']         ?? [];
                $bgEnabled  = !empty($bg['enabled']);
                $bgColor    = $bg['color']   ?? '#ffffff';
                $bgPadding  = (int)($bg['padding'] ?? 0);
                $bgRadius   = (int)($bg['radius']  ?? 0);

                /* ── logo image settings ── */
                $imgRadius      = (int)($settings['radius']      ?? 0);
                $imgOpacity     = (float)($settings['opacity']   ?? 1);
                $imgObjectFit   = $settings['objectFit']  ?? 'contain';
                $imgBorderWidth = (int)($settings['borderWidth'] ?? 0);
                $imgBorderColor = $settings['borderColor'] ?? '#ffffff';
                $imgBorderStyle = $settings['borderStyle'] ?? 'solid';
                $imgShadow      = $settings['shadow'] ?? 'none';
                $imgFilter      = $settings['filter'] ?? 'none';

                /* ── justify-content from align ── */
                $justifyMap = [
                    'left'   => 'flex-start',
                    'right'  => 'flex-end',
                    'center' => 'center',
                ];
                $justifyContent = $justifyMap[$align] ?? 'center';

                /* ── build box inline style (positions set by JS) ── */
                $boxStyle = 'position:absolute;box-sizing:border-box;overflow:hidden;';
                if ($type === 'text' && $bgEnabled) {
                    $boxStyle .= "background:{$bgColor};padding:{$bgPadding}px;border-radius:{$bgRadius}px;";
                } else {
                    $boxStyle .= 'background:transparent;padding:0;border-radius:0;';
                }

                /* ── logo img inline style ── */
                $imgBorderCss = $imgBorderWidth > 0
                    ? "border:{$imgBorderWidth}px {$imgBorderStyle} {$imgBorderColor};"
                    : 'border:none;';
                $imgStyle = "display:block;width:100%;height:100%;"
                    . "object-fit:{$imgObjectFit};"
                    . "object-position:center;"
                    . "border-radius:{$imgRadius}px;"
                    . "opacity:{$imgOpacity};"
                    . "box-shadow:{$imgShadow};"
                    . "filter:{$imgFilter};"
                    . "background:transparent;"
                    . $imgBorderCss;

                /* ── text inner style — font-size NOT set here, autoFitText() sets it after layout ── */
                $fontFamily = ($weight === 'bold' || $weight === '600' || $weight === '800')
                    ? "'Open Sans', sans-serif" : "'Open Sans', sans-serif";
                $textInnerStyle = "justify-content:{$justifyContent};"
                    . "text-align:{$align};"
                    . "color:{$color};"
                    . "line-height:{$lineHeight};"
                    . "font-weight:{$weight};"
                    . "font-family:{$fontFamily};"
                    . "white-space:normal;";
                ?>

                <div class="overlay-box type-<?= $type ?>"
                    data-x="<?= $x ?>"
                    data-y="<?= $y ?>"
                    data-w="<?= $w ?>"
                    data-h="<?= $h ?>"
                    data-idx="<?= $idx ?>"
                    data-type="<?= $type ?>"
                    data-padding="<?= ($type === 'text' && $bgEnabled) ? $bgPadding : 0 ?>"
                    style="<?= $boxStyle ?>">

                    <?php if ($type === 'logo'): ?>
                        <img src="<?= html_escape($branch_logo) ?>"
                            class="logo-img"
                            style="<?= $imgStyle ?>">

                    <?php elseif ($type === 'text'): ?>
                        <div class="text-inner" style="<?= $textInnerStyle ?>">
                            <?= html_escape($branch_text_map[$textKey] ?? '') ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>

        <div id="downloadBar">
            <a href="<?= base_url('Template_manager/download/' . $template['id']) ?>"
                class="btn btn-success">
                <i class="fas fa-download"></i> <?= translate('download') ?>
            </a>
        </div>

    </div>
</section>


<script>
(function () {

    const baseImg = document.getElementById('baseImg');
    const boxes   = document.querySelectorAll('.overlay-box');

    /* ── AUTO-FIT TEXT ─────────────────────────────────────────────
       Finds the largest font size where text fits inside boxW × boxH.
       Starts from max(boxH*0.85, boxW*0.3) to correctly handle both
       tall-narrow boxes AND wide-short boxes (like a branch name bar).

       @param {HTMLElement} el   - the .text-inner div
       @param {number}      boxW - inner width  (box width  - padding*2)
       @param {number}      boxH - inner height (box height - padding*2)
    ── */
    function autoFitText(el, boxW, boxH) {
        const MIN = 6;
        const MAX = Math.max(MIN, Math.floor(Math.max(boxH * 0.85, boxW * 0.3)));
        let size  = MAX;
        // Set explicit px width so scrollWidth measures exactly boxW
        // (width:100% inherits the padded parent width and breaks measurement)
        el.style.width      = boxW + 'px';
        el.style.maxWidth   = boxW + 'px';
        el.style.whiteSpace = 'normal';
        el.style.wordBreak  = 'break-word';
        el.style.overflow   = 'hidden';
        el.style.fontSize   = size + 'px';
        while (size > MIN && (el.scrollWidth > boxW + 1 || el.scrollHeight > boxH + 1)) {
            size--;
            el.style.fontSize = size + 'px';
        }
    }

    /* Apply ratio-based positions AND trigger auto-fit for text boxes */
    function applyPositions() {
        const W = baseImg.clientWidth;
        const H = baseImg.clientHeight;

        if (!W || !H) return;

        boxes.forEach(box => {
            const bw  = parseFloat(box.dataset.w) * W;
            const bh  = parseFloat(box.dataset.h) * H;
            const x   = parseFloat(box.dataset.x) * W;
            const y   = parseFloat(box.dataset.y) * H;
            const pad = parseInt(box.dataset.padding) || 0;

            box.style.left   = x  + 'px';
            box.style.top    = y  + 'px';
            box.style.width  = bw + 'px';
            box.style.height = bh + 'px';

            // Auto-fit text after box dimensions are set
            if (box.dataset.type === 'text') {
                const t = box.querySelector('.text-inner');
                if (t) {
                    // Use rAF so browser has committed the new width/height
                    // before we measure scrollWidth/scrollHeight
                    requestAnimationFrame(() => autoFitText(t, bw - pad * 2, bh - pad * 2));
                }
            }
        });
    }

    /* Boot */
    baseImg.onload = applyPositions;
    if (baseImg.complete) applyPositions();

    /* Zoom fix: ResizeObserver fires on Ctrl+/- and responsive resize */
    new ResizeObserver(applyPositions).observe(baseImg);

})();
</script>