<?php
/**
 * Branch Admin — Image Template Preview
 * $previewSrc is passed from controller — base64 PNG rendered by Imagick.
 * What you see = what you download. No JS, no font differences.
 */
?>
<style>
#preview-wrap {
    text-align: center;
    padding: 20px;
}
#preview-wrap img.preview-img {
    max-width: 100%;
    height: auto;
    display: inline-block;
    box-shadow: 0 4px 24px rgba(0,0,0,0.18);
    border-radius: 4px;
}
#downloadBar {
    margin-top: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
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

    <div class="panel-body">
        <div id="preview-wrap">

            <?php if (!empty($preview_src)): ?>
                <img src="<?= $preview_src ?>"
                     class="preview-img"
                     alt="<?= html_escape($template['title']) ?>">
            <?php else: ?>
                <div class="alert alert-danger">
                    Preview could not be generated. Please check the template file and branch logo exist.
                </div>
            <?php endif; ?>

            <div id="downloadBar">
                <a href="<?= base_url('Template_manager/download/' . $template['id']) ?>"
                   class="btn btn-success">
                    <i class="fas fa-download"></i> <?= translate('Download') ?>
                </a>
            </div>

        </div>
    </div>
</section>