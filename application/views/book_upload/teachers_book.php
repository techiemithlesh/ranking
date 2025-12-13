<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <div class="tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#attachments" data-toggle="tab"><i class="fas fa-list-ul"></i>
                            <?= translate('Books List') ?></a>
                    </li>
                    <?php if (get_permission('attachments', 'is_add')): ?>

                        <?php if (!is_teacher_loggedin()): ?>
                            <li>
                                <a href="#create" data-toggle="tab"><i class="far fa-edit"></i>
                                    <?= translate('create_Book') ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <div class="tab-content">
                    <div id="attachments" class="tab-pane active">
                        <div class="container">
                            <div class="row">
                                <?php foreach ($bookList as $row): ?>
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <div class="card shadow-sm h-100">
                                            <!-- Card Header -->
                                            <div class="card-header bg-primary text-white text-center">
                                                <h5 class="card-title mb-0">
                                                    <?php echo $row['title']; ?>
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <?php

                                                $bookImage = !empty($row['book_img']) ? $row['book_img'] : 'https://schoolexcel.tech/uploads/book_images/e4122eb39bbff81c6aeee61493b0dc5a.png';
                                                ?>
                                                <a href="<?php echo $row['book_url']; ?>" target="_blank"
                                                    data-toggle="tooltip" data-original-title="<?= translate('Open') ?>">
                                                    <img src="<?php echo base_url($bookImage); ?>" alt="Book Image"
                                                        class="img-fluid img-thumbnail mb-3"
                                                        style="widht: 300px; height: 350px;">
                                                </a>
                                            </div>
                                            <div class="card-footer">
                                                <?php if (is_superadmin_loggedin()) { ?>
                                                    <p><?php echo $row['branch_name']; ?></p>
                                                <?php } ?>
                                                <p>
                                                    <strong><?= translate('publisher') ?>:</strong>
                                                    <span
                                                        style="font-weight: normal; margin-left: 5px; float:right;"><?= $row['uploader_name'] ?></span>

                                                    <span
                                                        style="color: #777; font-size: 0.9em;"><?= _d($row['book_upload_date']); ?></span>
                                                </p>


                                                <p><strong><?= translate('school_name') ?>:</strong>
                                                    <?php echo (empty($row['school_name']) ? '<span class="text-dark">All</span>' : $row['school_name']); ?>
                                                </p>
                                                <p><strong><?= translate('staff_name') ?>:</strong>
                                                    <?php echo $row['staff_name'] ?>
                                                </p>
                                                <p><strong><?= translate('class_name') ?>:</strong>
                                                    <?php echo (empty($row['class_name']) ? '<span class="text-dark">All</span>' : $row['class_name']); ?>
                                                </p>
                                                <p><strong><?= translate('section_name') ?>:</strong>
                                                    <?php echo $row['section_name'] ?>
                                                </p>
                                                <p><strong><?= translate('Subject') ?>:</strong>
                                                    <?php echo (empty($row['subject_name']) ? '<span class="text-dark">Unfiltered</span>' : $row['subject_name']); ?>
                                                </p>
                                                <div class="btn-group d-flex" style="float:right">
                                                    <a href="<?php echo ($row['book_url']) ?>" target="_blank"
                                                        class="btn btn-sm btn-primary" data-toggle="tooltip"
                                                        data-original-title="<?= translate('Open') ?>">
                                                        <i class="fas fa-external-link-alt"></i> <?= translate('Open') ?>
                                                    </a>
                                                    <?php if (get_permission('book_uploads', 'is_delete')): ?>
                                                        <?php echo btn_delete('TeacherBooks/delete/' . $row['books_id']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            </div>
                        </div>

                    </div>
                    <?php if (!is_teacher_loggedin()): ?>
                        <div class="tab-pane" id="create">
                            <?php echo form_open_multipart('TeacherBooks/save', array('class' => 'form-bordered form-horizontal frm-submit-data')); ?>
                            <?php if (is_superadmin_loggedin()): ?>
                                <div class="form-group">
                                    <label class="control-label col-md-3"><?= translate('branch') ?> <span
                                            class="required">*</span></label>
                                    <div class="col-md-6">
                                        <?php
                                        $arrayBranch = $this->app_lib->getSelectList('branch');
                                        echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                                        ?>
                                        <span class="error"></span>
                                    </div>
                                </div>
                            <?php endif; ?>
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
                                <div class="col-md-offset-3">
                                    <div class="ml-md checkbox-replace">
                                        <label class="i-checks"><input type="checkbox" name="all_class_set"
                                                id="all_class_set"><i></i> Available
                                            For All Classes</label>
                                    </div>
                                </div>
                                <div id="class_div">
                                    <div class="mt-sm">
                                        <label class="control-label col-md-3"><?= translate('class') ?> <span
                                                class="required">*</span></label>
                                        <div class="col-md-6">
                                            <?php
                                           $arrayClass = $this->app_lib->getSelectClassByBranch($branch_id);
                                            echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSubjectByClass(this.value); getSectionByClass(this.value, 0);'
												data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                                            ?>
                                            <span class="error"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION -->
                            <div class="form-group">
                                <label class="control-label col-md-3"><?= translate('section') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6">
                                    <?php
                                    $arraySection = $this->app_lib->getSections(set_value('class_id'));

                                    echo form_dropdown("section_id", $arraySection, set_value('section_id'), "class='form-control' id='section_id'
										data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                                    ?>
                                    <span class="error"></span>
                                </div>
                            </div>

                            <div class="form-group" id="sub_div">
                                <div class="col-md-offset-3">
                                    <div class="ml-md checkbox-replace">
                                        <label class="i-checks"><input type="checkbox" name="subject_wise"
                                                id="subject_wise"><i></i> Not
                                            According Subject</label>
                                    </div>
                                </div>
                                <div id="subject_div">
                                    <div class="mt-sm">
                                        <label class="control-label col-md-3"><?= translate('subject') ?> <span
                                                class="required">*</span></label>
                                        <div class="col-md-6">
                                            <?php
                                            $arraySubject = array("" => translate('select_class_first'));
                                            echo form_dropdown("subject_id", $arraySubject, set_value('subject_id'), "class='form-control' id='subject_id'
												data-plugin-selectTwo data-width='100%' ");
                                            ?>
                                            <span class="error"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3"><?= translate('class_teacher') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6">
                                    <?php
                                    $arrayTeacher = $this->app_lib->getStaffList($branch_id, 3);

                                    echo form_dropdown("staff_id", $arrayTeacher, set_value('staff_id'), "class='form-control' id='staff_id'
							data-plugin-selectTwo data-width='100%' ");
                                    ?>
                                    <span class="error"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3"><?= translate('Book Url') ?> <span
                                        class="required">*</span></label>
                                <div class="col-md-6">
                                    <input type="url" class="form-control" name="book_url"
                                        value="<?= set_value('book_url') ?>" />

                                    <span class="error"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3"><?= translate('Book Image file') ?> <span
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
                            <?php echo form_close(); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </section>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#branch_id').on('change', function () {
            var branchID = $(this).val();
            console.log("branchId", branchID);
            getClassByBranch(this.value);
            getStaffListRole(branchID, 3);

        });

        $('#all_class_set').on('change', function () {
            if ($(this).is(':checked')) {
                $('#class_div').hide(); // Hide the class div
                $('#section_id').closest('.form-group').hide(); // Hide the section field
            } else {
                $('#class_div').show(); // Show the class div
                $('#section_id').closest('.form-group').show(); // Show the section field
            }
        });

        // Initialize visibility on page load based on checkbox status
        if ($('#all_class_set').is(':checked')) {
            $('#class_div').hide();
            $('#section_id').closest('.form-group').hide();
        }
    });

</script>