<section class="panel">
    <div class="tabs-custom">

        <div class="tab-content">
            <div class="tab-pane active">
                <div class="row">

                    <div class="col-md-5 pr-xs">
                        <section class="panel panel-custom">
                            <div class="panel-heading panel-heading-custom">
                                <h4 class="panel-title"><i class="far fa-edit"></i>
                                    <?= translate('create_skill_category') ?>
                                </h4>
                            </div>
                            <?php echo form_open($this->uri->uri_string(), array('class' => 'frm-submit')); ?>
                            <div class="panel-body panel-body-custom">
                                <?php if (is_superadmin_loggedin()): ?>
                                    <div class="form-group">
                                        <label class="control-label"><?= translate('branch') ?> <span
                                                class="required">*</span></label>
                                        <?php
                                        $arrayBranch = $this->app_lib->getSelectList('branch');
                                        echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id'
                                            data-plugin-selectTwo data-width='100%' data-placeHolder='Select Branch'");
                                        ?>
                                        <span class="branch"></span>
                                    </div>
                                <?php else: ?>
                                    <input type="hidden" name="branch_id" id="branch_id"
                                        value="<?= get_loggedin_branch_id(); ?>" />
                                <?php endif; ?>


                                <div class="form-group">
                                    <label class="control-label"><?= translate('subject') ?> <span
                                            class="required">*</span></label>
                                    <select data-plugin-selectTwo class="form-control" name="subject_id"
                                        id="subject_id">

                                    </select>
                                    <span class="subject"></span>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?= translate('category_name') ?> <span
                                            class="required">*</span></label>
                                   
                                    <div id="category-container">
                                        <div class="category-group">
                                            <textarea class="form-control" name="category_name[]" rows="3"
                                              placeholder="Enter Category name"  required></textarea>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary mt-sm"
                                        onclick="addMoreCategory()">
                                        <i class="fas fa-plus"></i> Add More
                                    </button>

                                    <span class="category_name"></span>
                                </div>


                            </div>
                            <footer class="panel-footer panel-footer-custom">
                                <div class="text-right">
                                    <button type="submit" class="btn btn-default"
                                        data-loading-text="<i class='fas fa-spinner fa-spin'></i> Processing">
                                        <i class="fas fa-plus-circle"></i> <?= translate('save') ?>
                                    </button>
                                </div>
                            </footer>
                            <?php echo form_close(); ?>
                        </section>
                    </div>

                    <div class="col-md-7">
                        <section class="panel panel-custom">
                            <header class="panel-heading panel-heading-custom">
                                <h4 class="panel-title"><i class="fas fa-list-ul"></i>
                                    <?= translate('skill_category_list') ?>
                                </h4>
                            </header>
                            <div class="panel-body panel-body-custom">
                                <div class="table-responsive">
                                    <table
                                        class="table table-bordered table-hover table-condensed tbr-top mb-none dataTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th><?= translate('branch') ?></th>
                                                <th><?= translate('subject') ?></th>
                                                <th><?= translate('category') ?></th>
                                                <th><?= translate('action') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 1;
                                            if (count($skill_category)) {
                                                foreach ($skill_category as $row):
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $count++; ?></td>
                                                        <td><?php echo $row['branch_name']; ?></td>
                                                        <td><?php echo $row['subject_name']; ?></td>
                                                        <td><?php echo $row['category_name']; ?></td>

                                                        <td>
                                                            <?php if (is_superadmin_loggedin() || is_admin_loggedin()): ?>
                                                                <!-- update link -->
                                                                <a class="btn btn-default btn-circle icon btn-edit"
                                                                    href="javascript:void(0);" data-id="<?= $row['id'] ?>"
                                                                    data-branch-name="<?= $row['branch_name'] ?>"
                                                                    data-branchId="<?= $row['branch_id'] ?>"
                                                                    data-subject-name="<?= $row['subject_name'] ?>"
                                                                    data-subject-id="<?= $row['subject_id'] ?>"
                                                                    data-category-name="<?= $row['category_name'] ?>"
                                                                    onclick="getEditModal(this)">
                                                                    <i class="fas fa-pen-nib"></i>
                                                                </a>

                                                                <!-- delete link -->
                                                                <?php echo btn_delete('skillReport/delete/' . $row['id']); ?>

                                                            <?php endif; ?>
                                                        </td>

                                                    </tr>
                                                    <?php
                                                endforeach;
                                            } else {
                                                echo '<tr><td colspan="6"><h5 class="text-danger text-center">' . translate('no_skill_status_available') . '</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SKILL STATUS EDIT MODAL START HERE-->

<?php if (is_superadmin_loggedin() || is_admin_loggedin()) {
    ?>
    <div class="zoom-anim-dialog modal-block modal-block-primary mfp-hide" id="modal">
        <section class="panel">
            <?php echo form_open('skillReport/update', array('class' => 'edit-frm-submit')); ?>

            <input type="hidden" name="skillstatus_id" id="eskillstatus_id" value="" />
            <header class="panel-heading">
                <h4 class="panel-title"><i class="far fa-edit"></i>
                    <?= translate('edit') . " " . translate('skill_category') ?></h4>
            </header>
            <div class="panel-body">
                <?php if (is_superadmin_loggedin()) { ?>
                    <div class="form-group mb-md">
                        <label class="control-label"><?= translate('Branch') ?> <span class="required">*</span></label>
                        <?php
                        $arrayBranch = $this->app_lib->getSelectList('branch');
                        echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='edit_branch_id'
                        data-plugin-selectTwo data-width='100%' data-placeholder='Select a branch'");
                        ?>
                        <span class="error"></span>
                    </div>
                <?php } ?>

                <div class="form-group">
                    <label class="control-label"><?= translate('Subject') ?> <span class="required">*</span></label>
                    <select class="form-control" name="subject_id" id="edit_subject_id">
                        <option value=""><?= translate('select') ?></option>
                    </select>
                    <span class="error"></span>
                </div>

                <div class="form-group mb-md">
                    <label class="control-label"><?= translate('Category Name') ?> <span class="required">*</span></label>
                    <input type="text" class="form-control" name="category_name" id="ecategory_name">
                    <span class="error"></span>
                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-default mr-xs">
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

<!-- SKILL STATUS EDIT MODAL END HERE -->

<script type="text/javascript">

    function addMoreCategory() {
        const container = document.getElementById('category-container');
        const newGroup = document.createElement('div');
        newGroup.classList.add('category-group', 'mt-sm');
        newGroup.innerHTML = `
        <textarea class="form-control" name="category_name[]" rows="3" placeholder="Enter Category name" required></textarea>
        <button type="button" class="btn btn-sm btn-danger mt-sm" onclick="this.parentNode.remove()">
            <i class="fas fa-minus"></i> Remove
        </button>
    `;
        container.appendChild(newGroup);
    }

    $(document).ready(function () {

        function fetchSubjects(branchID, subjectDropdownID, selectedSubjectID = null) {
            if (branchID) {
                $.ajax({
                    url: "<?= base_url('ajax/getSubjectsByBranch') ?>",
                    method: 'POST',
                    data: { branch_id: branchID },
                    success: function (data) {
                        $(subjectDropdownID).html(data);
                        if (selectedSubjectID) {
                            $(subjectDropdownID).val(selectedSubjectID).trigger("change");
                        }
                    }
                });
            } else {
                $(subjectDropdownID).html('<option value=""><?= translate("select") ?></option>');
            }
        }

        var branchID = $('#branch_id').val();
        if (branchID) {
            fetchSubjects(branchID, '#subject_id');
        }

        $('select[name="branch_id"]').on('change', function () {

            var branchID = $(this).val();
            fetchSubjects(branchID, '#subject_id');
        });

        $('#modal').on('change', 'select[name="branch_id"]', function () {
            var branchID = $(this).val();
            fetchSubjects(branchID, '#edit_subject_id');
        });

        window.getEditModal = function (el) {
            var id = $(el).data("id");
            var categoryName = $(el).data("category-name");
            var branchId = $(el).data("branchid");
            var subjectId = $(el).data("subject-id");

            $("#eskillstatus_id").val(id);
            $("#ecategory_name").val(categoryName);
            $("#edit_subject_id").val(subjectId);
            $("#modal select[name='branch_id']").val(branchId).trigger("change");

            fetchSubjects(branchId, '#edit_subject_id', subjectId);

            mfp_modal("#modal");
        };

        $(".edit-frm-submit").submit(function (e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr("action");

            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                dataType: "json",
                success: function (response) {
                    if (response.status == "success") {

                        location.reload();
                    } else {

                        $(".error").html("");
                        $.each(response.errors, function (key, value) {
                            $("#" + key).next(".error").html(value);
                        });
                    }
                }
            });
        });

        // Initialize DataTables
        $('.dataTable').DataTable({
            "pageLength": 10
        });
    });

</script>