<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <div class="tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#bookslist" data-toggle="tab"><i class="fas fa-list-ul"></i>
                            <?= translate('Books List') ?></a>
                    </li>
                    <?php if (is_superadmin_loggedin()) { ?>
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
                                <?php foreach ($digitalbooks as $row): ?>
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

                                                    <?php if (is_superadmin_loggedin()) {
                                                        ?>
                                                        <?php echo btn_delete('StudentBookUpload/deleteBooks/' . $row['id']); ?>


                                                        <a class="btn btn-default btn-circle icon btn-edit"
                                                            href="javascript:void(0);" data-id="<?= $row['id'] ?>"
                                                            data-title="<?= $row['title'] ?>"
                                                            data-status="<?= $row['status'] ?>" data-book-img="<?= $book_img ?>"
                                                            data-book-url="<?= $row['book_url'] ?>">
                                                            <i class="fas fa-pen-nib"></i>
                                                        </a>


                                                        <?php
                                                    }

                                                    ?>
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


<?php if (is_superadmin_loggedin()) {
    ?>
    <div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
        <section class="panel">
            <?php echo form_open_multipart('StudentBookUpload/update', array('class' => 'edit-frm-submit-book', 'enctype' => 'multipart/form-data')); ?>

            <input type="hidden" name="book_id" id="ebook_id" value="" />
            <header class="panel-heading">
                <h4 class="panel-title"><i class="far fa-edit"></i>
                    <?= translate('edit') . " " . translate('my_interactive_book') ?></h4>
            </header>
            <div class="panel-body">
                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('title') ?> <span class="required">*</span></label>
                    <input type="text" class="form-control" value="" name="title" id="etitle">
                    <span class="error"></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('Book Url') ?> <span class="required">*</span></label>
                    <input type="text" class="form-control" value="" name="book_url" id="ebook_url">
                    <span class="book_url"></span>
                </div>

                <!-- Status Dropdown -->
                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('status') ?> <span class="required">*</span></label>
                    <select class="form-control" name="status" id="estatus">
                        <option value="1"><?= translate('active') ?></option>
                        <option value="0"><?= translate('inactive') ?></option>
                    </select>
                    <span class="status"></span>
                </div>

                <!-- Thumbnail with Old Image Preview -->
                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('thumbnail') ?></label>
                    <input type="file" name="book_img" class="dropify" data-height="120" data-allowed-file-extensions="*"
                        id="ebookImg_path" />
                    <span class="book_img"></span>
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

<script type="text/javascript">
    $(document).ready(function () {

        function getBookEditModal(e) {
            console.log("data", e);
            var bookId = $(e).data('id');
            var bookTitle = $(e).data('title');
            var bookStatus = $(e).data('status');
            var bookImg = $(e).data('book-img');
            var bookUrl = $(e).data('book-url');

            $('#ebook_id').val(bookId);
            $('#etitle').val(bookTitle);
            $('#estatus').val(bookStatus);
            $('#ebook_url').val(bookUrl);

            $("#ebookImg_path").attr("data-default-file", bookImg);
            $(".dropify").dropify();

            mfp_modal('#modal');
        }

        $('body').on('click', '.btn-edit', function () {
            getBookEditModal(this);
        });

        $('.edit-frm-submit-book').on('submit', function (e) {
            e.preventDefault();


            var formData = new FormData(this);
            // AJAX request
            $.ajax({
                url: $(this).attr('action'),
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

    });
</script>