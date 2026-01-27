<section class="panel">
    <header class="panel-heading">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h4 class="panel-title">
                <i class="fas fa-eye"></i> <?= translate('preview_template') ?>
            </h4>

            <a href="<?= base_url('Template_manager/branch_templates') ?>"
                class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> <?= translate('back') ?>
            </a>
        </div>
    </header>

    <div class="panel-body text-center">
        <div id="stage" style="display:inline-block; position:relative; border:1px solid #ddd; background:#fafafa;">
            <img src="<?= base_url($template['file_path']) ?>"
                id="baseImg"
                style="display:block; max-width:100%;">

            <?php foreach ($overlays as $ov): ?>
                <?php
                $settings = json_decode($ov['settings'], true) ?? [];
                $bg = $settings['bg'] ?? [];
                ?>
                <div class="overlay-box"
                    data-x="<?= (float)$ov['x'] ?>"
                    data-y="<?= (float)$ov['y'] ?>"
                    data-w="<?= (float)$ov['width'] ?>"
                    data-h="<?= (float)$ov['height'] ?>"
                    data-bg-enabled="<?= !empty($bg['enabled']) ? 1 : 0 ?>"
                    data-bg-color="<?= $bg['color'] ?? '#ffffff' ?>"
                    data-bg-padding="<?= (int)($bg['padding'] ?? 0) ?>"
                    data-bg-radius="<?= (int)($bg['radius'] ?? 0) ?>"
                    style="position:absolute; display:flex; align-items:center; justify-content:center;">
                    <img src="<?= $branch_logo ?>"
                        style="max-width:100%; max-height:100%; object-fit:contain;">
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:15px;">
            <a href="<?= base_url('Template_manager/download/' . $template['id']) ?>"
                class="btn btn-success">
                <i class="fas fa-download"></i> <?= translate('download') ?>
            </a>
        </div>
    </div>
</section>

<script>
    (function() {
        const img = document.getElementById('baseImg');
        const boxes = document.querySelectorAll('.overlay-box');

        function applyPositions() {
            const w = img.clientWidth;
            const h = img.clientHeight;

            boxes.forEach(box => {
                const x = parseFloat(box.dataset.x) * w;
                const y = parseFloat(box.dataset.y) * h;
                const bw = parseFloat(box.dataset.w) * w;
                const bh = parseFloat(box.dataset.h) * h;

                box.style.left = x + 'px';
                box.style.top = y + 'px';
                box.style.width = bw + 'px';
                box.style.height = bh + 'px';

                if (box.dataset.bgEnabled === '1') {
                    box.style.background = box.dataset.bgColor || '#ffffff';
                    box.style.padding = (box.dataset.bgPadding || 0) + 'px';
                    box.style.borderRadius = (box.dataset.bgRadius || 0) + 'px';
                }
            });
        }

        img.onload = applyPositions;
        if (img.complete) applyPositions();

        // Recalculate on resize (responsive safety)
        window.addEventListener('resize', applyPositions);
    })();
</script>