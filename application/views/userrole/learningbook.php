<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-cloud-upload-alt"></i> <?= translate('attachments') ?></h4>
    </header>
    <div class="panel-body">

        <div class="container">
            <div class="row my-6">
                <?php foreach ($booklist as $row): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card shadow-sm h-100 d-flex flex-column">
                            <div class="card-header bg-primary text-white text-center">
                                <h5 class="card-title mb-2">
                                    <?php echo $row['title'] ?>
                                </h5>
                            </div>
                            <div class="text-center my-3">
                                <a href="<?php echo $row['book_url']; ?>" target="_blank" data-toggle="tooltip"
                                    data-original-title="<?= translate('Open') ?>">
                                    <img id="book-image-<?php echo isset($row['id']) ? $row['id'] : ''; ?>"
                                        src="<?php echo base_url($row['book_img']); ?>" alt="Book Image"
                                        class="img-fluid img-thumbnail" style="height: 350px; object-fit: cover;">
                                </a>
                            </div>

                            <div class="card-footer mt-auto">
                                <div class="btn-group d-flex justify-content-end">
                                    <a href="<?php echo $row['book_url'] ?>" target="_blank"
                                        class="btn btn-sm btn-primary text-center" style="margin-top: 12px;"
                                        data-toggle="tooltip" data-original-title="<?= translate('Open') ?>">
                                        <i class="fas fa-external-link-alt"></i> <?= translate('Open') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $(document).ready(function () {
        $.ajax({
            url: '<?= base_url() . '/' . 'userrole/getStudentPhotoAndNameById' ?>',
            type: 'GET',
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    const studentData = response.data;

                    $('img[id^="book-image-"]').each(function (index, imageElement) {
                        const bookImage = new Image();
                        bookImage.onload = function () {
                            const canvas = document.createElement('canvas');
                            canvas.width = this.width;
                            canvas.height = this.height;
                            const ctx = canvas.getContext('2d');

                            // Draw book image
                            ctx.drawImage(bookImage, 0, 0);

                            // Photo position
                            const centerX = this.width * 0.5;
                            const centerY = this.height * 0.5;
                            const photoSize = 450;
                            const photoX = centerX - (photoSize / 2);
                            const photoY = centerY - (photoSize / 2);

                            const studentPhotoImage = new Image();
                            studentPhotoImage.src = `<?= base_url() . '/uploads/images/student/' ?>${studentData.photo}`;
                            studentPhotoImage.onload = function () {
                                // Draw photo in circle
                                ctx.save();
                                ctx.beginPath();
                                ctx.arc(centerX, centerY, photoSize / 2, 0, Math.PI * 2);
                                ctx.clip();
                                ctx.drawImage(studentPhotoImage, photoX, photoY, photoSize, photoSize);
                                ctx.restore();

                                // Draw name
                                ctx.font = "bold 65px Arial";
                                ctx.fillStyle = "#000000";
                                ctx.textAlign = "center";
                                ctx.fillText(`${studentData.first_name} ${studentData.last_name}`,
                                    centerX,  // X center of image
                                    // this.height * 4.7  // Y position at 85% from top
                                    this.height - 25
                                ); this.height * 4.7  // Y position at 85% from top


                                imageElement.src = canvas.toDataURL("image/png");
                            };
                        };
                        bookImage.src = $(imageElement).attr('src');
                    });
                }
            }
        });
    });

</script>