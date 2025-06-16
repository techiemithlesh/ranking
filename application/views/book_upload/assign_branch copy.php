<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <?php echo form_open($this->uri->uri_string(), array('class' => 'validate')); ?>
            <div class="panel-body">
                <div class="row mb-sm">

                    <div class="col-md-6 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('Class Category') ?> <span
                                    class="required">*</span></label>
                            <select name="class_category_id" class="form-control" id="class_category_id">
                                <option value=""><?= translate('Select Class Type') ?></option>
                                <?php foreach ($class_types as $class_type): ?>
                                    <option value="<?= $class_type['id'] ?>"><?= $class_type['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 mb-sm">
                        <div class="form-group">
                            <label class="control-label"><?= translate('Book Category') ?> <span
                                    class="required">*</span></label>
                            <select name="book_type_id" class="form-control" id="book_type_id">
                                <option value=""><?= translate('Select Type Category') ?></option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
            <footer class="panel-footer">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <button type="submit" name="search" value="1" class="btn btn btn-default btn-block">
                            <i class="fas fa-filter"></i> <?= translate('filter') ?></button>
                    </div>
                </div>
            </footer>
            <?php echo form_close(); ?>
        </section>

        <?php if (isset($booklist)): ?>
            <section class="panel appear-animation" data-appear-animation="<?php echo $global_config['animations']; ?>"
                data-appear-animation-delay="100">
                <header class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-list"></i> <?= translate('Assign Branch') ?></h4>
                    <div class="panel-btn">
                        <button type="button" class="btn btn-primary mb-4" id="assignBranchBtn">Assign
                            Branch</button>
                    </div>
                </header>
                <div class="panel-body">
                    <table class="table table-bordered table-export">
                        <thead>
                            <tr>
                                <th><?= translate('sl') ?></th>
                                <th><?= translate('title') ?></th>
                                <th><?= translate('class_category') ?></th>
                                <th><?= translate('book_category') ?></th>
                                <th><?= translate('Book Cover') ?></th>
                                <th><?= translate('Assigned Branches') ?></th>
                                <th><?= translate('action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1;
                            foreach ($booklist as $row): ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo $row['title']; ?></td>
                                    <td><?php echo $row['class_category_name']; ?></td>
                                    <td><?php echo $row['book_type_name']; ?></td>
                                    <td>
                                        <img src="<?= base_url($row['book_img']) ?>" width="100" height="120px" />
                                    </td>
                                    <td>
                                        <?php echo $row['assigned_branches'] ?>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="book_ids[]" value="<?= $row['id'] ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php echo form_close(); ?>
            </section>
        <?php endif; ?>
    </div>
</div>

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide" id="modal">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-bars"></i> <?php echo translate('Assign Branch'); ?></h4>
        </header>
        <div class="panel-body">
            <div id='quick_view'></div>
           
        </div>
        <footer class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-default modal-dismiss"><?php echo translate('close'); ?></button>
                    <button type="button" class="btn btn-primary" id="assignBranchModalBtn">Assign Branch</button>
                </div>
            </div>
        </footer>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        $('#assignBranchBtn').on('click', function () {
            const selectedBooks = [];
            $('input[name="book_ids[]"]:checked').each(function () {
                selectedBooks.push($(this).val());
            });

            if (selectedBooks.length === 0) {
                alert('Please select at least one book.');
                return;
            }

            $.ajax({
                url: "<?= base_url('StudentBookUpload/get_assigned_branches') ?>",
                type: 'POST',
                data: { book_ids: selectedBooks },
                success: function (response) {
                    const res = JSON.parse(response);
                    console.log("Res", res);
                    if (res.status === 'success') {
                        const branches = res.branches;
                        const assignedBranchIds = res.assignedBranchIds;
                        let branchHTML = '';

                        if (branches.length > 0) {
                            branches.forEach(branch => {

                                const assignedBranch = assignedBranchIds.find(
                                    assigned => assigned.id === branch.id
                                );

                                const isChecked = assignedBranch && assignedBranch.assigned === '1' ? 'checked' : '';

                                branchHTML += `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="branch_ids[]" value="${branch.id}" id="branch_${branch.id}" ${isChecked}>
                                    <label class="form-check-label" for="branch_${branch.id}">
                                        ${branch.name}
                                    </label>
                                </div>
                            `;
                            });
                        } else {
                            branchHTML = `<p>No branches available.</p>`;
                        }

                        $('#quick_view').html(branchHTML);
	                    mfp_modal('#modal');
                    } else {
                        alert('Failed to fetch branches. Please try again.');
                    }
                },

            });
        });

        // Submit the assigned branches
        $('#assignBranchModalBtn').on('click', function (e) {
            e.preventDefault();
            const selectedBranches = [];
            $('input[name="branch_ids[]"]:checked').each(function () {
                selectedBranches.push($(this).val());
            });

            if (selectedBranches.length === 0) {
                alert('Please select at least one branch.');
                return;
            }

            

            const bookIds = [];
            $('input[name="book_ids[]"]:checked').each(function () {
                bookIds.push($(this).val());
            });

            $.ajax({
                url: "<?= base_url('StudentBookUpload/assign_branches') ?>",
                type: 'POST',
                data: { book_ids: bookIds, branch_ids: selectedBranches },
                success: function (response) {
                    const res = JSON.parse(response);
                    console.log("res", res);
                    if (res.status === 'success') {
                        // alert('Branches assigned successfully!');
                        swal({
                            toast: true,
                            position: 'top-end',
                            icon: 'success', //
                            title: res.message,
                            showConfirmButton: false,
                            timer: 8000
                        });
                        $('#assignBranchModal').modal('hide');
                        window.location.reload();
                    } else {
                        // alert('Failed to assign branches. Please try again.');
                        swal({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Failed to assign branches. Please try again.',
                            showConfirmButton: false,
                            timer: 8000
                        });
                    }
                },
                error: function () {
                    // alert('An error occurred while assigning branches.');
                    swal({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'An error occurred while assigning branches.',
                        showConfirmButton: false,
                        timer: 8000
                    });
                }
            });
        });

        // BOOK CATEGORY OPEN ON SELECTE CLASS CATEGORY
        $('#class_category_id').change(function () {
            var classTypeId = $(this).val();
            var $bookTypeDropdown = $('#book_type_id');
            $bookTypeDropdown.empty();

            if (classTypeId) {
                $.ajax({
                    url: '<?= base_url('StudentBookUpload/getBookTypeCategories') ?>',
                    type: 'POST',
                    data: { class_type_id: classTypeId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status) {
                            $bookTypeDropdown.append('<option value=""><?= translate("Select Type Category") ?></option>');
                            $.each(response.categories, function (index, category) {
                                $bookTypeDropdown.append('<option value="' + category.id + '">' + category.name + '</option>');
                            });
                        } else {
                            $bookTypeDropdown.append('<option value=""><?= translate("No categories available") ?></option>');
                        }
                    },
                    error: function () {
                        alert('<?= translate("An error occurred while fetching categories.") ?>');
                    }
                });
            } else {
                $bookTypeDropdown.append('<option value=""><?= translate("Select Type Category") ?></option>');
            }
        });

    });


</script>