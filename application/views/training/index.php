<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <div class="tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#training" data-toggle="tab"><i class="fas fa-list-ul"></i>
                            <?= translate('Training Material List') ?></a>
                    </li>

                    <?php if (is_superadmin_loggedin()): ?>
                        <li>
                            <a href="#create" data-toggle="tab"><i class="far fa-edit"></i>
                                <?= translate('create_training_material') ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content">
                    <div id="training" class="tab-pane active">
                        <div class="container">
                            <div class="row">
                                <?php foreach ($training as $row): ?>
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <div class="card shadow-sm h-100">
                                            <div class="card-header bg-primary text-white text-center">
                                                <h5 class="card-title mb-0">
                                                    <?php echo $row['title']; ?>
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <?php

                                                $bookImage = !empty($row['thumbnail_path']) ? $row['thumbnail_path'] : 'https://schoolexcel.tech/uploads/book_images/e4122eb39bbff81c6aeee61493b0dc5a.png';
                                                ?>
                                                <a href="<?php echo $row['video_url']; ?>" target="_blank"
                                                    data-toggle="tooltip" data-original-title="<?= translate('Open') ?>">
                                                    <img src="<?php echo base_url($bookImage); ?>" alt="Book Image"
                                                        class="img-fluid img-thumbnail mb-3"
                                                        style="widht: 450px; height: 253.125px;">
                                                </a>
                                            </div>
                                            <div class="card-footer">
                                                <div class="btn-group d-flex" style="float:right">
                                                    <a href="<?php echo ($row['video_url']) ?>" target="_blank"
                                                        class="btn btn-sm btn-primary" data-toggle="tooltip"
                                                        data-original-title="<?= translate('Open') ?>">
                                                        <i class="fas fa-external-link-alt"></i> <?= translate('Open') ?>
                                                    </a>

                                                    <?php if (is_superadmin_loggedin()) {
                                                        ?>
                                                        <a class="btn btn-default btn-circle icon btn-edit"
                                                            href="javascript:void(0);" data-id="<?= $row['id'] ?>"
                                                            data-title="<?= $row['title'] ?>"
                                                            data-video-url="<?= $row['video_url'] ?>"
                                                            data-thumbnail-path="<?= $row['thumbnail_path'] ?>">
                                                            <i class="fas fa-pen-nib"></i>
                                                        </a>

                                                        <!-- delete -->

                                                        <?php echo btn_delete('training/delete/' . $row['id']); ?>

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
                    <?php if (is_superadmin_loggedin()): ?>
                        <div class="tab-pane" id="create">
                            <?php echo form_open_multipart('training/save', array('class' => 'form-bordered form-horizontal frm-submit-data')); ?>
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
                                <label class="control-label col-md-3"><?= translate('Video Url') ?> <span
                                        class="required">Youtube*</span></label>
                                <div class="col-md-6">
                                    <input type="url" class="form-control" name="video_url"
                                        value="<?= set_value('video_url') ?>" />

                                    <span class="error"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3"><?= translate('Thumbnail') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6 mb-md">
                                    <input type="file" name="thumbnail_path" class="dropify" data-height="120"
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
                            <?php echo form_close(); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </section>
    </div>
</div>

<!-- MODAL FOR EDIT UPDATE -->

<?php if (is_superadmin_loggedin()) {
    ?>
    <div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
        <section class="panel">
            <?php echo form_open('training/update', array('class' => 'edit-frm-submit', 'enctype' => 'multipart/form-data')); ?>

            <input type="hidden" name="training_id" id="ecategory_id" value="" />
            <header class="panel-heading">
                <h4 class="panel-title"><i class="far fa-edit"></i>
                    <?= translate('edit') . " " . translate('training_material') ?></h4>
            </header>
            <div class="panel-body">
                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('title') ?> <span class="required">*</span></label>
                    <input type="text" class="form-control" value="" name="title" id="etitle">
                    <span class="error"></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('video_url') ?> <span class="required">*</span></label>
                    <input type="text" class="form-control" value="" name="video_url" id="evideo_url">
                    <span class="error"></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('thumbnail') ?></label>
                    <input type="file" name="thumbnail_path" class="dropify" data-height="120"
                        data-allowed-file-extensions="*" id="ethumbnail_path" />

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

<script type="text/javascript">
    $(document).ready(function () {
        // Function to open the edit modal
        function getTrainignEditModal(e) {
            var trainingId = $(e).data('id');
            var trainingTitle = $(e).data('title');
            var trainingVideoUrl = $(e).data('video-url');

            $('#ecategory_id').val(trainingId);
            $('#etitle').val(trainingTitle);
            $('#evideo_url').val(trainingVideoUrl);

            // Open the modal
            mfp_modal('#modal'); // Ensure `mfp_modal` is defined correctly
        }

        // Attach the modal opening to the edit button
        $('body').on('click', '.btn-edit', function () {
            getTrainignEditModal(this);
        });

        // AJAX form submission for edit/update
        $('.edit-frm-submit').on('submit', function (e) {
            e.preventDefault();

            // Collect form data
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

        // YT THUMBANIL GET
        function getYoutubeThumbnailUrl($youtubeUrl) {
            // Extract video ID from YouTube URL
            preg_match('/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/]+\/)?(?:watch\?v=|embed\/))|(?:youtu\.be\/))([\w-]{11}).*/', $youtubeUrl, $matches);
            if (empty($matches[1])) {
                return false;
            }
            $videoId = $matches[1];

            // Construct thumbnail URL
            $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";

            return $thumbnailUrl;
        }
    });

</script>