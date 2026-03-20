<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <div class="panel-heading-flex" style="display:flex;align-items:center;justify-content:space-between;">
                    <h4 class="panel-title" style="margin:0;"><?= translate('Template_manger') ?></h4>

                    <a href="<?= base_url('Template_manager/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle"></i> <?= translate('Upload_template') ?>
                    </a>
                </div>
            </header>

            <form method="get" action="<?= base_url('Template_manager') ?>" class="validate">
                <div class="panel-body">
                    <div class="row mb-sm">
                        <?php if (is_superadmin_loggedin()): ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"><?= translate('Template_type') ?> <span
                                            class="required">*</span></label>
                                    <?php
                                    $arrayTemplateType = [
                                        ""  => translate('select'),
                                        "1" => "Pamplet",
                                        "2" => "Video"
                                    ];
                                    echo form_dropdown("template_type", $arrayTemplateType, set_value('template_type'), "class='form-control' id='template_type'
                                data-plugin-selectTwo data-width='100%'");
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-<?php echo $widget; ?> mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?= translate('Edit_status') ?> <span
                                        class="required">*</span></label>
                                <?php
                                $arrayEditStatus = [
                                    "" => "Select Edit Status",
                                    "1" => "Yes",
                                    "0" => "No"
                                ];
                                echo form_dropdown("edit_status", $arrayEditStatus, set_value('edit_status'), "class='form-control' id='edit_status'
                            required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                                ?>
                            </div>
                        </div>

                        <div class="col-md-<?php echo $widget; ?> mb-sm">
                            <div class="form-group">
                                <label class="control-label"><?= translate('status') ?> <span class="required">*</span></label>
                                <?php
                                $arrayStatus = [
                                    ""  => translate('select'),
                                    "1" => translate('active'),
                                    "0" => translate('inactive')
                                ];

                                echo form_dropdown(
                                    "status",
                                    $arrayStatus,
                                    set_value('status'),
                                    "class='form-control' id='status' required 
                                data-plugin-selectTwo 
                                data-width='100%' 
                                data-minimum-results-for-search='Infinity'"
                                );
                                ?>
                            </div>
                        </div>

                    </div>
                </div>
                <footer class="panel-footer">
                    <div class="row">
                        <div class="col-md-offset-10 col-md-2">
                            <button type="submit" name="search" value="1" class="btn btn-default btn-block">
                                <i class="fas fa-filter"></i> <?= translate('filter') ?>
                            </button>
                        </div>
                    </div>
                </footer>
            </form>
            
        </section>

        <?php if (isset($templateData) && !empty($templateData)): ?>
            <section class="panel appear-animation" data-appear-animation="<?= $global_config['animations'] ?>"
                data-appear-animation-delay="100">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-images"></i> <?= translate('template_list'); ?></h4>
                </header>
                <div class="panel-body mb-md">
                    <div class="row">
                        <?php foreach ($templateData as $item): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card gallery-item">
                                    <div class="card-body">
                                        <a href="javascript:void(0);" class="template-link" data-template-id="<?= $item['id'] ?>">
                                            <?php
                                            $type = $item['type']; // image/video
                                            $filePath = base_url($item['file_path']);
                                            $mediaStyle = "width: 100%; height: 200px; object-fit: cover;";

                                            if ($type === 'image') {
                                                echo "<img src='$filePath' class='img-fluid img-thumbnail mb-3' style='$mediaStyle'>";
                                            } else {
                                                echo "<video class='img-fluid mb-3' style='$mediaStyle' controls>
                                            <source src='$filePath' type='video/mp4'>
                                            </video>";
                                            }

                                            ?>
                                        </a>
                                        <?php if ((int)$item['overlay_count'] > 0): ?>
                                            <span class="label label-success">Edited</span>
                                        <?php else: ?>
                                            <span class="label label-warning">Raw</span>
                                        <?php endif; ?>


                                    </div>
                                    <div class="card-footer text-right">

                                        <?php if ($item['type'] === 'video') : ?>
                                            <!-- <a href="<?= base_url('Video_editor/download/' . $item['id']) ?>"
                                                class="btn btn-success mt-3">
                                                <i class="fas fa-download"></i> Download
                                            </a> -->
                                        <?php elseif ($item['type'] == 'image') : ?>
                                            <a href="<?= $filePath ?>" class="btn btn-primary text-right" download target="_blank">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        <?php endif; ?>



                                        <?php if ($item['type'] == 'image') : ?>
                                            <a href="<?= base_url('Template_manager/edit/' . $item['id']) ?>" class="btn btn-info ml-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        <?php elseif ($item['type'] == 'video') : ?>
                                            <a href="<?= base_url('Video_editor/index/' . $item['id']) ?>" class="btn btn-info ml-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        <?php endif; ?>


                                        <?php if (is_superadmin_loggedin()): ?>
                                            <?php echo btn_delete_ajax('Template_manager/delete/' . $item['id']); ?>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!empty($pagination_links)): ?>
                            <div class="text-center">
                                <?= $pagination_links ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </section>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-circle"></i> <?= translate('no_items_found'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>



<script type="text/javascript">


</script>