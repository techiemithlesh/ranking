<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <div class="tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#marketing" data-toggle="tab">
                            <i class="fas fa-list-ul"></i> <?= translate('Education Material List') ?>
                        </a>
                    </li>
                    <?php if (is_superadmin_loggedin()): ?>
                        <li>
                            <a href="#create" data-toggle="tab">
                                <i class="far fa-edit"></i> <?= translate('Create Education Material') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content">
                    <!-- Marketing Material List Tab -->
                    <div id="marketing" class="tab-pane active">
                        <div class="container">

                            <!-- SEARCH ROW START HERE -->
                            <?php $widget = (is_superadmin_loggedin() ? 4 : 6); ?>
                            <section class="panel">

                                <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
                                <div class="panel-body">
                                    <div class="row mb-sm">
                                        <?php if (is_superadmin_loggedin()): ?>
                                            <div class="col-md-4 mb-sm">
                                                <div class="form-group">
                                                    <label class="control-label"><?= translate('branch') ?> <span
                                                            class="required">*</span></label>
                                                    <?php
                                                    $arrayBranch = $this->app_lib->getSelectList('branch');
                                                    echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' required onchange='getClassByBranch(this.value)'
						                                data-width='100%' data-plugin-selectTwo");
                                                    ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="col-md-<?php echo $widget; ?> mb-sm">
                                            <div class="form-group">
                                                <label class="control-label"><?= translate('Material Type') ?> <span
                                                        class="required">*</span></label>
                                                <select name="file_type" class="form-control" required
                                                    data-plugin-selectTwo data-width="100%">
                                                    <option value=""><?= translate('Select Material Type') ?></option>
                                                    <?php foreach ($file_types as $key => $name): ?>
                                                        <option value="<?= $key ?>" <?= set_select('file_type', $key, ($this->input->post('file_type') === $key)) ?>>
                                                            <?= $name ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                                <div class="row py-2">
                                    <div class="col-md-offset-10 col-md-2">
                                        <button type="submit" class="btn btn btn-primary btn-block">
                                            <i class="fas fa-filter"></i> <?= translate('filter') ?>
                                        </button>
                                    </div>
                                </div>

                                <?php echo form_close(); ?>
                            </section>
                            <!-- SEARCH ROW END HERE -->
                            <?php if (!empty($marketing)) { ?>
                                <div class="row">
                                    <?php foreach ($marketing as $row): ?>
                                        <div class="col-lg-4 col-md-6 mb-4">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-header bg-primary text-white text-center">
                                                    <h5 class="card-title mb-0">
                                                        <?= $row['title'] ?>
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <?php

                                                    $filePath = base_url($row['file_path']);
                                                    if ($row['file_type'] === 'image') {
                                                        echo "<img src='$filePath' alt='Image' class='img-fluid img-thumbnail mb-3' style='max-width: 100%; height: auto;'>";
                                                    } elseif ($row['file_type'] === 'video') {
                                                        echo "<video controls class='img-fluid mb-3' style='max-width: 100%;'>
                                                    <source src='$filePath' type='video/mp4'>
                                                            Your browser does not support the video tag.
                                                        </video>";
                                                    } elseif ($row['file_type'] === 'pdf') {
                                                        echo "<embed src='$filePath' type='application/pdf' width='100%' height='400px' class='mb-3'>";
                                                        echo "<p><a href='$filePath' target='_blank' class='btn btn-sm btn-primary'>View Brochure</a></p>";
                                                    } else {
                                                        echo "<p>Unsupported file type.</p>";
                                                    }
                                                    ?>
                                                </div>
                                                <div class="card-footer text-right">


                                                    <!-- Download button (For both Admin & Super Admin) -->
                                                    <?php if (is_superadmin_loggedin() || is_admin_loggedin()): ?>
                                                        <a class="btn btn-default btn-circle icon" href="<?= $filePath ?>" download>
                                                            <i class="fas fa-download"></i>
                                                        </a>

                                                        <!-- Edit and Delete buttons (Only for Super Admin) -->
                                                        <?php if (is_superadmin_loggedin()): ?>

                                                            <span
                                                                class="badge <?= ($row['status'] == 1) ? 'badge-success' : 'badge-danger' ?>"
                                                                style="cursor: pointer;" title="Double-click to toggle status"
                                                                id="toggleBtn" data-id="<?= $row['id'] ?>"
                                                                data-status="<?= $row['status'] ?>">
                                                                <?= ($row['status'] == 1) ? 'Active' : 'Inactive' ?>
                                                            </span>


                                                            <a class="btn btn-default btn-circle icon btn-edit"
                                                                href="javascript:void(0);" data-id="<?= $row['id'] ?>"
                                                                data-title="<?= $row['title'] ?>"
                                                                data-file-type="<?= $row['file_type'] ?>">
                                                                <i class="fas fa-pen-nib"></i>
                                                            </a>
                                                            <?= btn_delete('educationMaterial/delete/' . $row['id']) ?>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php } else { ?>
                                <div class="alert alert-info text-center">
                                    <strong>No records available.</strong>
                                </div>
                            <?php } ?>

                        </div>
                    </div>

                    <!-- Create Marketing Material Tab -->
                    <?php if (is_superadmin_loggedin()): ?>
                        <div id="create" class="tab-pane">
                            <?= form_open_multipart('EducationMaterial/save', ['class' => 'form-bordered form-horizontal frm-submit-data-material', 'enctype' => 'multipart/form-data']) ?>

                            <!-- Branch Selection -->
                            <div id="branch-box" class="form-group d-none">
                                <label class="col-md-3 control-label"><?= translate('Branch') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6">
                                    <?php
                                    $arrayBranch = $this->app_lib->getSelectList('branch');
                                    echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
									data-plugin-selectTwo data-width='100%' data-placeholder='Select a branch'");
                                    ?>
                                    <span class="error"><?php echo form_error('branch_id'); ?></span>

                                </div>
                            </div>

                            <!-- Material Type Dropdown -->
                            <div id="material-box" class="form-group d-none">
                                <label class="col-md-3 control-label"><?= translate('Material Type') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6">
                                    <select name="file_type" class="form-control">
                                        <option value=""><?= translate('Select Material Type') ?></option>
                                        <option value="video"><?= translate('Video') ?></option>
                                        <option value="image"><?= translate('Image') ?></option>
                                        <option value="pdf"><?= translate('Document') ?></option>
                                    </select>
                                    <span class="error"></span>
                                </div>
                            </div>


                            <div id="input-box" class="form-group d-none">
                                <label class="col-md-3 control-label"><?= translate('Title') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="title"
                                        value="<?= set_value('title') ?>" />
                                    <span class="error"></span>
                                </div>
                            </div>
                            <div id="file-box" class="form-group d-none">
                                <label class="col-md-3 control-label"><?= translate('Upload File') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6">
                                    <input type="file" name="file_path" class="dropify" data-height="120" />
                                    <span class="error"></span>
                                </div>
                            </div>

                            <footer class="panel-footer text-right">
                                <button type="submit" class="btn btn-default"
                                    data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                                    <i class="fas fa-plus-circle"></i> <?= translate('Save') ?>
                                </button>
                            </footer>
                            <?= form_close() ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- FOR UPDATE MODEL START HERE -->
<?php if (is_superadmin_loggedin()) {
    ?>
    <div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
        <section class="panel">
            <?php echo form_open('educationMaterial/update', array('class' => 'edit-frm-submit', 'enctype' => 'multipart/form-data')); ?>

            <input type="hidden" name="education_id" id="education_id" value="" />
            <header class="panel-heading">
                <h4 class="panel-title"><i class="far fa-edit"></i>
                    <?= translate('edit') . " " . translate('education_material') ?></h4>
            </header>
            <div class="panel-body">
                <?php if (is_superadmin_loggedin()) {
                    ?>
                    <div class="form-group mb-md">
                        <label class="control-label"><?= translate('Branch') ?> <span class="required">*</span></label>
                        <?php
                        $arrayBranch = $this->app_lib->getSelectList('branch');
                        echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
									data-plugin-selectTwo data-width='100%' data-placeholder='Select a branch'");
                        ?>
                        <span class="error"><?php echo form_error('branch_id'); ?></span>
                    </div>
                    <?php
                } ?>

                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('title') ?> <span class="required">*</span></label>
                    <input type="text" class="form-control" value="" name="title" id="etitle">
                    <span class="error"></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('Material Type') ?> <span class="required">*</span></label>
                    <select name="file_type" id="file_type" class="form-control">
                        <option value=""><?= translate('Select Material Type') ?></option>
                        <option value="video"><?= translate('Video') ?></option>
                        <option value="image"><?= translate('Pamphlets') ?></option>
                        <option value="pdf"><?= translate('Brochures') ?></option>
                    </select>
                    <span class="error"></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('File') ?></label>
                    <input type="file" name="file_path" class="dropify" data-height="120" data-allowed-file-extensions="*"
                        id="efile_path" />

                    <span class="error"></span>
                </div>

            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-default mr-xs"
                            data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                            <i class="fas fa-plus-circle"></i> <?= translate('update') ?>
                        </button>
                        <button class="btn btn-default modal-dismiss"><?= translate('cancel') ?></button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>
    </div>
    <?php
}
?>
<!-- FOR UPDATE MODEL END HERE -->

<script>
    $(document).ready(function () {

        document.querySelector('#toggleBtn').addEventListener('dblclick', function () {
            let id = this.getAttribute('data-id');
            let currentStatus = parseInt(this.getAttribute('data-status')); // Convert to number
            updateStatus(id, currentStatus);
        });

        function updateStatus(id, currentStatus) {
            let newStatus = currentStatus === 1 ? 0 : 1;

            // console.log("Toggling status for ID:", id, "New Status:", newStatus);

            $.ajax({
                url: '<?= base_url("educationMaterial/toggleStatus") ?>',
                type: 'POST',
                data: { id: id, status: newStatus },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Status updated successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message,
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error updating status. Please try again.',
                    });
                }
            });
        }


        function getTrainignEditModal(e) {
            var marketingId = $(e).data('id');
            var marketingTitle = $(e).data('title');
            var marketingFileType = $(e).data('file-type');

            $('#education_id').val(marketingId);
            $('#etitle').val(marketingTitle);
            $('#file_type').val(marketingFileType);

            mfp_modal('#modal');
        }

        $('body').on('click', '.btn-edit', function () {
            getTrainignEditModal(this);
        });


        // EDIT UPDATE 

        $('.edit-frm-submit').on('submit', function (e) {
            e.preventDefault();

            var formData = new FormData(this);

            // AJAX request
            $.ajax({
                url: $(this).attr('action'), // Get form action URL
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $('.edit-frm-submit button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Processing').attr('disabled', true);
                },
                success: function (response) {
                    response = JSON.parse(response);
                    console.log("response", response.status);
                    if (response.status === 'success') {
                        swal({
                            title: 'success!',
                            text: response.message || 'updated successfully!.',
                        });

                        $.magnificPopup.close();
                        location.reload();
                    } else {
                        // Handle failure response
                        swal({
                            title: 'Error!',
                            text: response.message || 'Something went wrong. Please try again.',
                        });
                    }
                },
                error: function (xhr, status, error) {

                    swal({
                        title: 'Error!',
                        text: 'An error occurred: ' + error,
                    });
                },
                complete: function () {
                    // Reset button state
                    $('.edit-frm-submit button[type="submit"]').html('<i class="fas fa-plus-circle"></i> Update').attr('disabled', false);
                }
            });
        });


        $('.frm-submit-data-material').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitButton = $('button[type="submit"]');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    submitButton.html('<i class="fas fa-spinner fa-spin"></i> Processing').attr('disabled', true);
                },
                success: function (response) {
                    const data = JSON.parse(response);
                    if (data.status === 'success') {
                        swal('Success!', data.message || 'Saved successfully!', 'success').then(() => location.reload());
                    } else {
                        swal('Error!', data.message || 'Something went wrong.', 'error');
                    }
                },
                complete: function () {
                    submitButton.html('<i class="fas fa-plus-circle"></i> <?= translate("Save") ?>').attr('disabled', false);
                }
            });
        });
    });

</script>