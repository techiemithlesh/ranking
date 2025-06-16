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
