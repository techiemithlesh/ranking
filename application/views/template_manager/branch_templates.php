
<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title">
            <i class="fas fa-bullhorn"></i> <?= translate('marketing_templates') ?>
        </h4>
    </header>

    <div class="panel-body">
        <?php if (empty($templates)): ?>
            <div class="alert alert-warning text-center">
                <?= translate('no_templates_found') ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($templates as $tpl): ?>
                    <div class="col-md-4 col-sm-6 mb-lg">
                        <div class="panel panel-bordered">
                            <div class="panel-body">

                                <!-- Thumbnail -->
                                <div style="border:1px solid #ddd; margin-bottom:10px;">
                                    <img src="<?= base_url($tpl['file_path']) ?>"
                                         style="width:100%; height:220px; object-fit:contain;"
                                         alt="<?= html_escape($tpl['title']) ?>">
                                </div>

                                <h5 style="margin:0 0 10px 0;">
                                    <?= html_escape($tpl['title']) ?>
                                </h5>

                                <div class="text-right">
                                    <a href="<?= base_url('Template_manager/preview/' . $tpl['id']) ?>"
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> <?= translate('preview') ?>
                                    </a>

                                    <a href="<?= base_url('Template_manager/download/' . $tpl['id']) ?>"
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-download"></i> <?= translate('download') ?>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
