<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('my_gallery') ?></h4>
            </header>
            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
            <div class="panel-body">
                <div class="row mb-sm">
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span
                                        class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' onchange='getClassByBranch(this.value)'
								data-plugin-selectTwo data-width='100%'");
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span
                                    class="required">*</span></label>
                            <?php 
                            if (!is_superadmin_loggedin()) {
                                $branch_id = get_loggedin_branch_id();
                            }
                            $arrayClass = $this->app_lib->getClass($branch_id);
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,1)'
								required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSections(set_value('class_id'), true);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
								data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                            ?>
                        </div>
                    </div>


                    <!-- Student List Section (Initially Hidden) -->

                    <?php
                    if (is_superadmin_loggedin() || is_admin_loggedin() || is_teacher_loggedin()) {
                        ?>
                        <div class="col-md-12" id="studentListContainer" style="display: none;">
                            <div class="form-group">
                                <label class="control-label"><?= translate('Select Students') ?></label>
                                <select name="student_ids[]" id="studentList" class="form-control" multiple
                                    data-plugin-selectTwo data-width='100%'>

                                </select>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="submit" name="search" value="1" class="btn btn-default btn-block"> <i
                                class="fas fa-filter"></i> <?= translate('filter') ?></button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <?php if (isset($gallery) && !empty($gallery)): ?>
            <section class="panel appear-animation" data-appear-animation="<?= $global_config['animations'] ?>"
                data-appear-animation-delay="100">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-images"></i> <?= translate('gallery_list'); ?></h4>
                </header>
                <div class="panel-body mb-md">
                    <div class="row">
                        <?php foreach ($gallery as $item): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <?php
                                        $filePath = base_url($item['file_path']);
                                        $mediaStyle = "width: 100%; height: 250px; object-fit: cover;";

                                        if ($item['file_type'] === 'image') {
                                            echo "<img src='$filePath' alt='Image' class='img-fluid img-thumbnail mb-3' style='$mediaStyle'>";
                                        } else {
                                            echo "<video controls class='img-fluid mb-3' style='$mediaStyle'>
                                    <source src='$filePath' type='video/mp4'>
                                     Your browser does not support the video tag.
                                    </video>";
                                        }
                                        ?>

                                        <p class="card-text"><?= htmlspecialchars($item['description']); ?></p>
                                        <small class="text-muted"><?= translate('uploaded_on'); ?>:
                                            <?= date('d M Y, H:i', strtotime($item['created_at'])); ?></small>
                                    </div>

                                    <?php if (is_superadmin_loggedin()): ?>
                                        <div class="card-footer text-right">
                                            <?= btn_delete('Gallery/delete/' . $item['id']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-circle"></i> <?= translate('no_gallery_items_found'); ?>
            </div>
        <?php endif; ?>


    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {

        function getStudentsBySection(sectionId) {
            let classId = $('#class_id').val();
            let branchId = $("select[name='branch_id']").val();

            // console.log("section", sectionId);

            if (sectionId === "all") {
                $('#studentListContainer').hide();
                $('#studentList').html('');
            } else {
                $.ajax({
                    url: "<?= base_url('Gallery/get_students_by_section'); ?>",
                    type: "POST",
                    data: {
                        section_id: sectionId,
                        class_id: classId,
                        branch_id: branchId
                    },
                    success: function (response) {
                        $('#studentList').html(response);
                        $('#studentListContainer').show();
                    }
                });
            }
        }

        $('#section_id').on('change', function () {
            getStudentsBySection($(this).val());
        });

       
    });
</script>