<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('Edit_Student_Gallery') ?></h4>
            </header>
            <?php echo form_open_multipart(base_url('gallery/update/' . $gallery['id']), array('class' => 'validate', 'id' => 'galleryEditForm', 'enctype' => 'multipart/form-data')); ?>
            <div class="panel-body">
                <div class="row mb-sm">
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span
                                        class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id', $gallery['branch_id']), "class='form-control' id='branch_id'
										data-plugin-selectTwo data-width='100%' data-placeholder='Select a branch'");
                                ?>
                            </div>
                        </div>
                        <?php $branch_id = $gallery['branch_id']; ?>
                    <?php else: ?>
                        <?php $branch_id = get_loggedin_branch_id(); ?>
                    <?php endif; ?>

                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arrayClass = $this->app_lib->getClass($branch_id);
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id', $gallery['class_id']), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,1)' required data-plugin-selectTwo data-width='100%' ");
                            ?>
                        </div>
                    </div>

                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = ['all' => 'All Sections'] + $this->app_lib->getSections($gallery['class_id'], true);
                            echo form_dropdown(
                                "section_id",
                                $arraySection,
                                set_value('section_id', empty($gallery['section_id']) ? 'all' : $gallery['section_id']),
                                "class='form-control' id='section_id' required data-plugin-selectTwo data-width=\"100%\" onchange='getStudentsBySection(this.value)'"
                            );
                            ?>
                        </div>
                    </div>

                </div>

                <div class="row mb-sm" id="studentListContainer"
                    style="<?= $is_for_all_students ? 'display: none;' : '' ?>">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label"><?= translate('Select Students') ?></label>
                            <!-- <div style="background: #f5f5f5; padding: 10px; margin-bottom: 10px; font-size: 12px;">
                                <p>Selected students array: <?= json_encode($selected_students) ?></p>
                                <p>First student data: <?= json_encode(reset($students)) ?></p>
                            </div> -->
                            <select name="student_ids[]" id="studentList" class="form-control" multiple
                                data-plugin-selectTwo data-width='100%'>
                                <?php foreach ($students as $student): ?>
                                    <?php $selected = in_array($student['student_id'], $selected_students) ? 'selected' : ''; ?>
                                    <option value="<?= $student['student_id'] ?>" <?= $selected ?>>
                                        <?= $student['fullname'] ?>(<?= $student['register_no'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>


                <!-- File Upload Section -->
                <div class="row mb-sm">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"><?= translate('Upload Photo/Video') ?></label>
                            <input type="file" name="file_path" class="form-control dropify"
                                data-default-file="<?= base_url($gallery['file_path']) ?>" accept="image/*,video/*">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"><?= translate('Description') ?></label>
                            <textarea name="description"
                                class="form-control"><?= set_value('description', $gallery['description']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row mb-sm">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary"><?= translate('Update') ?></button>
                        <a href="<?= base_url('gallery') ?>" class="btn btn-default"><?= translate('Cancel') ?></a>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        $('.dropify').dropify();
    });

    function getStudentsBySection(sectionId) {
        let classId = $('#class_id').val();
        let branchId = $("select[name='branch_id']").val() || <?= json_encode($branch_id); ?>;

        if (sectionId === "all" || sectionId == '') {
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
                    console.log("response", response);
                    $('#studentList').html(response);
                    $('#studentListContainer').show();
                }
            });
        }
    }


</script>