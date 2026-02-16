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
                        <div class="panel panel-bordered" style="height: 100%;">
                            <div class="panel-body">

                                <div class="thumbnail-container" style="border:1px solid #ddd; margin-bottom:10px; background: #000; border-radius: 4px; overflow: hidden; position: relative; height: 220px; display: flex; align-items: center; justify-content: center;">

                                    <div style="position: absolute; top: 10px; left: 10px; z-index: 5;">
                                        <?php if ($tpl['type'] === 'video'): ?>
                                            <span class="label label-danger" style="padding: 5px 8px; font-size: 10px; text-transform: uppercase;">
                                                <i class="fas fa-video"></i> Video
                                            </span>
                                        <?php else: ?>
                                            <span class="label label-info" style="padding: 5px 8px; font-size: 10px; text-transform: uppercase;">
                                                <i class="fas fa-image"></i> Image
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($tpl['type'] === 'video'): ?>
                                        <div style="position: absolute; color: rgba(255,255,255,0.7); font-size: 35px; z-index: 2; pointer-events: none;">
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($tpl['type'] === 'image'): ?>
                                        <img src="<?= base_url($tpl['file_path']) ?>"
                                            style="max-width:100%; max-height:100%; object-fit:contain;"
                                            alt="<?= html_escape($tpl['title']) ?>">
                                    <?php elseif ($tpl['type'] === 'video'): ?>
                                        <video style="max-width:100%; max-height:100%; object-fit:contain;">
                                            <source src="<?= base_url($tpl['file_path']) ?>#t=0.1" type="video/mp4">
                                        </video>
                                    <?php endif; ?>
                                </div>

                                <h5 class="text-weight-semibold" style="margin:0 0 15px 0; min-height: 2.4em; line-height: 1.2;">
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
                    </div> <?php endforeach; ?>
            </div> <?php endif; ?>
    </div>
</section>