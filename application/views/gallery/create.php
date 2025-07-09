<?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <h4 class="panel-title"><?= translate('Student_Gallery_Upload') ?></h4>
            </header>
            <?php echo form_open_multipart('#', array('class' => 'validate', 'id' => 'galleryUploadForm', 'enctype' => 'multipart/form-data')); ?>
            <div class="panel-body">
                <div class="row mb-sm">
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"><?= translate('branch') ?> <span
                                        class="required">*</span></label>
                                <?php
                                $arrayBranch = $this->app_lib->getSelectList('branch');
                                echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id' onchange='getClassByBranch(this.value)'
                                data-plugin-selectTwo data-width='100%' required");
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('class') ?> <span
                                    class="required">*</span></label>
                            <?php if (!is_superadmin_loggedin()) {
                                $branch_id = get_loggedin_branch_id();
                            }

                           $arrayClass = $this->app_lib->getSelectClassByBranch($branch_id);
                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,1)'
                                required data-plugin-selectTwo data-width='100%' ");
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?php echo $widget; ?> mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('section') ?> <span
                                    class="required">*</span></label>
                            <?php
                            $arraySection = $this->app_lib->getSectionsByClass(set_value('class_id'), true);
                            echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id' required
                                data-plugin-selectTwo data-width='100%' onchange='getStudentsBySection(this.value)' ");
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Student List Section (Initially Hidden) -->
                <div class="row mb-sm" id="studentListContainer" style="display: none;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label"><?= translate('Select Students') ?></label>
                            <select name="student_ids[]" id="studentList" class="form-control" multiple
                                data-plugin-selectTwo data-width='100%'>

                            </select>
                        </div>
                    </div>
                </div>

                <!-- File Upload Section -->
                <div class="row mb-sm">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"><?= translate('Upload Photo/Video') ?> <span
                                    class="required">*</span></label>
                            <input type="file" name="file_path[]" id="fileInput" class="dropify form-control"
                                data-height="100" accept="image/*,video/*" multiple>
                            <div id="previewArea" class="row mt-2"></div>

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"><?= translate('Description') ?></label>
                            <textarea name="description" class="form-control" style="height: 110px;" placeholder="Add description"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row mb-sm">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary"><?= translate('Upload') ?></button>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<script type="text/javascript">
    function getStudentsBySection(sectionId) {
        let classId = $('#class_id').val();
        let branchId = $("select[name='branch_id']").val();

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

    let selectedFiles = [];

    $('#fileInput').on('change', function (e) {
        const newFiles = Array.from(e.target.files);
        selectedFiles = selectedFiles.concat(newFiles);

        $('#previewArea').html('');
        selectedFiles.forEach((file, index) => {
            const fileType = file.type;
            const reader = new FileReader();

            reader.onload = function (e) {
                let previewHTML = '';
                if (fileType.startsWith('image/')) {
                    previewHTML = `<div class="col-md-3 mb-2"><img src="${e.target.result}" class="img-thumbnail" style="height: 150px; width: 100%; object-fit: cover;"></div>`;
                } else if (fileType.startsWith('video/')) {
                    previewHTML = `<div class="col-md-3 mb-2"><video controls style="width: 100%; height: 150px;"><source src="${e.target.result}" type="${fileType}">Your browser does not support the video tag.</video></div>`;
                } else {
                    previewHTML = `<div class="col-md-3 mb-2"><p>${file.name}</p></div>`;
                }
                $('#previewArea').append(previewHTML);
            };
            reader.readAsDataURL(file);
        });

        // clear dropify's default preview so we don’t confuse the user
        const dropify = $('#fileInput').data('dropify');
        if (dropify) {
            dropify.resetPreview();
            dropify.clearElement();
        }
        this.value = '';
    });

    $(document).ready(function () {

        $('#galleryUploadForm').on('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Uploading...',
                text: 'Please wait while we process your file',
                allowOutsideClick: false,
                showConfirmButton: false,
            });

            let formData = new FormData(this);
            selectedFiles.forEach(file => {
                formData.append('file_path[]', file);
            });

            $.ajax({
                url: "<?= base_url('Gallery/upload_media_bulk'); ?>",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: 'File uploaded successfully',
                                showConfirmButton: true,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = data.url;
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Upload Failed',
                                text: data.error || 'Something went wrong',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to process server response',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        title: 'Upload Failed',
                        text: 'There was a problem uploading your file. Please try again.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        });
    });
</script>