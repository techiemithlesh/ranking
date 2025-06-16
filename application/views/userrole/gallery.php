<section class="panel">
    <header class="panel-heading">
        <h4 class="panel-title"><i class="fas fa-images"></i> <?= translate('my_gallery') ?></h4>
    </header>
    <div class="panel-body">

        <div class="container">
            <div class="row my-6">
                <?php foreach ($gallery as $row): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <a href="javascript:void(0);" class="gallery-link" data-gallery-id="<?= $row['id'] ?>"
                                    data-type="<?= $row['file_type'] ?>"
                                    data-title="<?= htmlspecialchars($row['title']) ?>">
                                    <?php
                                    $filePath = base_url($row['file_path']);

                                    $mediaStyle = "width: 100%; height: 100%; object-fit: contain;";

                                    if ($row['file_type'] === 'image') {
                                        echo "<img src='$filePath' alt='Image' class='img-fluid img-thumbnail mb-3' style='$mediaStyle'>";
                                    } else {
                                        echo "<video controls class='img-fluid mb-3' style='$mediaStyle'>
                                    <source src='$filePath' type='video/mp4'>
                                     Your browser does not support the video tag.
                                    </video>";
                                    }
                                    ?>

                                </a>

                                <p class="card-text"><?= htmlspecialchars($row['description']); ?></p>
                                <small class="text-muted"><?= translate('uploaded_on'); ?>:
                                    <?= date('d M Y, H:i', strtotime($row['created_at'])); ?></small>
                            </div>

                            <div class="card-footer text-right">
                                <a href="<?=$filePath ?>" class="btn btn-primary text-right" download target="_blank">
                                    <i class="fas fa-download"></i> Download
                                </a>

                            </div>

                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Modal -->
<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide" id="galleryModal">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-image"></i> <span class="modal-title"></span></h4>
        </header>
        <div class="panel-body p-0">
            <div class="modal-content-wrapper">
                <!-- Content will be dynamically inserted here -->
            </div>
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12">
                    <div class="navigation-controls text-center mb-3">
                        <button class="btn btn-default prev-item">
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button class="btn btn-default next-item">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class="text-right">
                        <button class="btn btn-default modal-dismiss"><?= translate('close'); ?></button>
                    </div>
                </div>
            </div>
        </footer>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        let currentIndex = 0;
        let galleryItems = [];

        // Initialize gallery items
        function initializeGallery() {
            galleryItems = [];
            $('.gallery-link').each(function () {
                const $card = $(this).closest('.card-body');
                const mediaElement = $(this).find('img, video').first();

                galleryItems.push({
                    id: $(this).data('gallery-id'),
                    type: $(this).data('type'),
                    title: $(this).data('title'),
                    src: mediaElement.is('img') ? mediaElement.attr('src') : mediaElement.find('source').attr('src'),
                    description: $card.find('.card-text').text().trim()
                });
            });
        }

        // Update modal content
        function updateModalContent(index) {
            const item = galleryItems[index];
            let mediaContent = '';

            // Set modal title
            $('.modal-title').text(item.title || 'Gallery View');

            // Create media content
            if (item.type === 'image') {
                mediaContent = `
                <div class="media-container" style="background-color: #000; text-align: center; min-height: 400px; display: flex; align-items: center; justify-content: center;">
                    <img src="${item.src}" class="img-fluid" style="max-height: 70vh; max-width: 100%; object-fit: contain;">
                </div>`;
            } else {
                mediaContent = `
                <div class="media-container" style="background-color: #000; text-align: center; min-height: 400px; display: flex; align-items: center; justify-content: center;">
                    <video id="modalVideo" controls class="img-fluid" style="max-height: 70vh; max-width: 100%;">
                        <source src="${item.src}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>`;
            }

            // Add description
            mediaContent += `
            <div class="content-details p-3 d-flex">
                <div class="description text-center">
                    <p class="mb-0">${item.description || ''}</p>
                </div>
                 <div class="download-btn text-center">
                <a href="${item.src}" class="btn btn-primary" download target="_blank">
                    <i class="fas fa-download"></i> Download ${item.type === 'image' ? 'Image' : 'Video'}
                </a>
            </div>
            </div>`;

            // Update content and navigation buttons
            $('.modal-content-wrapper').html(mediaContent);
            $('.prev-item').prop('disabled', index === 0);
            $('.next-item').prop('disabled', index === galleryItems.length - 1);

            currentIndex = index;
        }

        // Gallery item click handler
        $(document).on('click', '.gallery-link', function (e) {
            e.preventDefault();
            initializeGallery();

            const clickedId = $(this).data('gallery-id');
            currentIndex = galleryItems.findIndex(item => item.id === clickedId);

            updateModalContent(currentIndex);
            $.magnificPopup.open({
                items: {
                    src: '#galleryModal',
                    type: 'inline'
                },
                callbacks: {
                    close: function () {
                        const video = document.getElementById('modalVideo');
                        if (video) {
                            video.pause();
                        }
                    }
                }
            });
        });

        // Navigation handlers
        $('.prev-item').click(function () {
            if (currentIndex > 0) {
                updateModalContent(currentIndex - 1);
            }
        });

        $('.next-item').click(function () {
            if (currentIndex < galleryItems.length - 1) {
                updateModalContent(currentIndex + 1);
            }
        });

        // Keyboard navigation
        $(document).on('keydown', function (e) {
            if ($('#galleryModal').is(':visible')) {
                if (e.keyCode === 37 && currentIndex > 0) { // Left arrow
                    $('.prev-item').click();
                } else if (e.keyCode === 39 && currentIndex < galleryItems.length - 1) { // Right arrow
                    $('.next-item').click();
                }
            }
        });

    });
</script>