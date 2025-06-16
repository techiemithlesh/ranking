<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <div class="tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#bookslist" data-toggle="tab"><i class="fas fa-list-ul"></i>
                            <?= translate('Books List') ?></a>
                    </li>
                    <?php if (get_permission('attachments', 'is_add') && is_superadmin_loggedin()) { ?>
                        <li>
                            <a href="#create" data-toggle="tab"><i class="far fa-edit"></i>
                                <?= translate('create_Book') ?></a>
                        </li>
                    <?php } ?>
                </ul>
                <div class="tab-content">
                    <div id="bookslist" class="tab-pane active">
                        <div class="mb-md">
                            <div class="row my-6">
                                <?php foreach ($attachmentss as $row): ?>
                                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                        <div class="card shadow-sm h-100 d-flex flex-column">

                                            <!-- Card Header (Title) -->
                                            <div class="card-header bg-primary text-white text-center">
                                                <h5 class="card-title mb-2">
                                                    <?php echo $row['title']; ?>
                                                </h5>
                                            </div>

                                            <!-- Card Image -->
                                            <div class="text-center my-3">
                                                <?php
                                                $bookImage = !empty($row['book_img']) ? $row['book_img'] : 'https://via.placeholder.com/200x250?text=No+Image';
                                                ?>
                                                <a href="<?php echo $row['book_url']; ?>" target="_blank"
                                                    data-toggle="tooltip" data-original-title="<?= translate('Open') ?>">
                                                    <img src="<?php echo base_url($bookImage); ?>" alt="Book Image"
                                                        class="img-fluid img-thumbnail"
                                                        style="height: 350px; object-fit: cover;">
                                                </a>
                                            </div>

                                            <!-- Card Footer (Details and Buttons) -->
                                            <div class="card-footer mt-auto">
                                                <div class="d-flex justify-content-between">
                                                    <p><strong><?= translate('Class') ?>:</strong>
                                                        <?php echo (empty($row['class_name']) ? '<span class="text-dark">All</span>' : $row['class_name']); ?>
                                                    </p>
                                                    <p><strong><?= translate('Subject') ?>:</strong>
                                                        <?php echo (empty($row['subject_name']) ? '<span class="text-dark">Unfiltered</span>' : $row['subject_name']); ?>
                                                    </p>
                                                </div>

                                                <div class="d-flex justify-content-between">
                                                    <p><strong><?= translate('Publisher') ?>:</strong>
                                                        <?php echo get_type_name_by_id('staff', $row['uploader_id']); ?>
                                                    </p>

                                                </div>

                                                <div class="btn-group d-flex justify-content-end">
                                                    <a href="<?php echo ($row['book_url']) ?>" target="_blank"
                                                        class="btn btn-sm btn-primary" data-toggle="tooltip"
                                                        data-original-title="<?= translate('Open') ?>">
                                                        <i class="fas fa-external-link-alt"></i> <?= translate('Open') ?>
                                                    </a>
                                                    <?php if (get_permission('book_uploads', 'is_delete')): ?>
                                                        <?php echo btn_delete('StudentBookUpload/deleteBooks/' . $row['id']); ?>
                                                    <?php endif; ?>

                                                    <?php if (get_permission('student_book_upload_edit', 'is_edit')): ?>
                                                        <a href="<?= base_url('StudentBookUpload/bookUploadEdit/' . $row['id']) ?>"
                                                            class="btn btn-default btn-circle icon">
                                                            <i class="fas fa-pen-nib"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane" id="create">
                        <?php echo form_open_multipart('StudentBookUpload/save', array('class' => 'form-bordered form-horizontal frm-submit-data')); ?>
                        <?php if (is_superadmin_loggedin()): ?>
                            <div class="form-group">
                                <label class="col-md-3 control-label"><?= translate('title') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="title"
                                        value="<?= set_value('title') ?>" />
                                    <span class="error"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-3 control-label"><?= translate('Book Url') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6">
                                    <input type="url" class="form-control" name="book_url"
                                        value="<?= set_value('book_url') ?>" />
                                    <span class="error"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-3 control-label"><?= translate('Book Image file') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6 mb-md">
                                    <input type="file" name="img_path" class="dropify" data-height="120"
                                        data-allowed-file-extensions="*" />
                                    <span class="error"></span>
                                </div>
                            </div>

                            <footer class="panel-footer mt-lg">
                                <div class="row">
                                    <div class="col-md-2 col-md-offset-3">
                                        <button type="submit" class="btn btn-default btn-block"
                                            data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                                            <i class="fas fa-plus-circle"></i> <?= translate('save') ?>
                                        </button>
                                    </div>
                                </div>
                            </footer>
                        <?php endif; ?>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#branch_id').on('change', function () {
            var branchID = $(this).val();
            getClassByBranch(this.value);
            $.ajax({
                url: "<?= base_url('ajax/getDataByBranch') ?>",
                type: 'POST',
                data: {
                    branch_id: branchID,
                    table: 'attachments_type'
                },
                success: function (data) {
                    $('#type_id').html(data);
                }
            });
        });
    });
</script>