<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <div class="tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#bookslist" data-toggle="tab"><i class="fas fa-list-ul"></i>
                            <?= translate('Books List') ?></a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div id="bookslist" class="tab-pane active">
                        <div class="mb-md">
                            <div class="row">
                                <div class="col-md-12 text-right" style="margin-bottom: 10px;">
                                    <button type="button" class="btn btn-primary mb-4" id="assignBranchBtn">Assign
                                        Branch</button>
                                </div>
                            </div>
                            <table class="table table-bordered table-hover table-condensed mb-none table-export">
                                <thead>
                                    <tr>
                                        <th><?= translate('sl') ?></th>
                                        <th><?= translate('title') ?></th>
                                        <th><?= translate('Book Cover') ?></th>
                                        <th><?= translate('Assigned Branches') ?></th>
                                        <th><?= translate('action') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $count = 1;
                                    foreach ($attachmentss as $row): ?>
                                        <tr>
                                            <td><?php echo $count++; ?></td>
                                            <td><?php echo $row['title']; ?></td>
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
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div id="branchAssignmentModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Assign Branch</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="assignBranchForm">
                <div class="form-group row">
                    <label class="control-label col-md-3"><?= translate('branch') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-9">
                        <div id="branchCheckboxContainer">

                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-primary" id="assignBranchModalBtn">Assign Branch</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        // Open modal and load branch checkboxes dynamically
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



                                const isChecked = assignedBranchIds.includes(branch.id) ? 'checked' : '';


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

                        $('#branchCheckboxContainer').html(branchHTML);
                        $('#branchAssignmentModal').modal('show');
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
    });

</script>