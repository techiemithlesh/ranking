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

                    <?php if (is_superadmin_loggedin() || is_admin_loggedin() || is_teacher_loggedin()): ?>
                        <div class="col-md-12" id="studentListContainer" style="display: none;">
                            <div class="form-group">
                                <label class="control-label"><?= translate('Select Students') ?></label>
                                <select name="student_ids[]" id="studentList" class="form-control" multiple
                                    data-plugin-selectTwo data-width='100%'>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="submit" name="search" value="1" class="btn btn-default btn-block">
                            <i class="fas fa-filter"></i> <?= translate('filter') ?>
                        </button>
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
                                <div class="card gallery-item">
                                    <div class="card-body">
                                        <a href="javascript:void(0);" class="gallery-link" data-gallery-id="<?= $item['id'] ?>"
                                            data-type="<?= $item['file_type'] ?>"
                                            data-title="<?= htmlspecialchars($item['title']) ?>">
                                            <?php
                                            $filePath = base_url($item['file_path']);
                                            $mediaStyle = "width: 100%; height: 200px; object-fit: cover;";

                                            if ($item['file_type'] === 'image') {
                                                echo "<img src='$filePath' alt='Gallery Image' class='img-fluid img-thumbnail mb-3' style='$mediaStyle'>";
                                            } else {
                                                echo "<video class='img-fluid mb-3' style='$mediaStyle'>
                                                        <source src='$filePath' type='video/mp4'>
                                                        Your browser does not support the video tag.
                                                      </video>";
                                            }
                                            ?>
                                        </a>
                                        <h5 class="card-title"><?= htmlspecialchars($item['title']); ?></h5>
                                        <p class="card-text"><?= htmlspecialchars($item['description']); ?></p>
                                        <small class="text-muted">
                                            <?= translate('uploaded_on'); ?>:
                                            <?= date('d M Y, H:i', strtotime($item['created_at'])); ?>
                                        </small>

                                    </div>
                                    <div class="card-footer text-right">
                                        <a href="<?= $filePath ?>" class="btn btn-primary text-right" download target="_blank">
                                            <i class="fas fa-download"></i> Download
                                        </a>

                                        <?php if (get_permission('digital_gallery', 'is_edit')): ?>

                                            <a class="btn btn-default btn-circle icon btn-edit" href="<?=base_url('Gallery/edit/' .$item['id']) ?>">
                                                <i class="fas fa-pen-nib"></i>
                                            </a>
                                        <?php endif;
                                        if (get_permission('digital_gallery', 'is_delete')): ?>
                                            <?= btn_delete('Gallery/delete/' . $item['id']); ?>
                                        <?php endif; ?>

                                    </div>

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

<!-- Gallery POP UP Modal -->
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

            $('.modal-title').text(item.title || 'Gallery View');

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
            <div class="content-details p-3">
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
                if (e.keyCode === 37 && currentIndex > 0) {
                    $('.prev-item').click();
                } else if (e.keyCode === 39 && currentIndex < galleryItems.length - 1) {
                    $('.next-item').click();
                }
            }
        });

        // Student section handling
        function getStudentsBySection(sectionId) {
            let classId = $('#class_id').val();
            let branchId = $("select[name='branch_id']").val();

            if (sectionId === "all") {
                $('#studentListContainer').hide();
                $('#studentList').html('');
            } else {
                $.ajax({
                    url: base_url + 'Gallery/get_students_by_section',
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