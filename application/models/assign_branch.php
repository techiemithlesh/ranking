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
                                        <th><input type="checkbox" id="selectAllBooks"></th>
                                        <th><?= translate('sl') ?></th>
                                        <th><?= translate('title') ?></th>
                                        <th><?= translate('Book Cover') ?></th>
                                        <th><?= translate('Assigned Branches') ?></th>
                                        <th><?= translate('action') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $count = 1;
                                    foreach ($booklists as $row): ?>
                                        <tr>
                                            <td><input type="checkbox" class="bookCheckbox" name="book_ids[]"
                                                    value="<?= $row['id'] ?>"></td>
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

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide" id="modal">
    <section class="panel">
        <header class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-bars"></i> <?php echo translate('Assign Branch'); ?></h4>
        </header>
        <div class="panel-body">
            <table id="branchTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Branch Name</th>
                        <th>Assign</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamic rows will be appended here -->
                </tbody>
            </table>

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
        let selectedBranches = []; // Global array to store selected branch IDs

        // Select All Books
        $('#selectAllBooks').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.bookCheckbox').prop('checked', isChecked);
        });

        // Initialize DataTable
        const branchTable = $('#branchTable').DataTable({
            pageLength: 5, // Number of rows per page
        });

        // Event listener for checkbox toggling
        $('#branchTable').on('change', '.form-check-input', function () {
            const branchId = $(this).val();

            if ($(this).is(':checked')) {
                // Add to selectedBranches array if not already present
                if (!selectedBranches.includes(branchId)) {
                    selectedBranches.push(branchId);
                }
            } else {
                // Remove from selectedBranches array
                selectedBranches = selectedBranches.filter(id => id !== branchId);
            }
        });

        // Pre-check checkboxes based on selectedBranches array when page changes
        $('#branchTable').on('draw.dt', function () {
            $('.form-check-input').each(function () {
                const branchId = $(this).val();
                if (selectedBranches.includes(branchId)) {
                    $(this).prop('checked', true);
                }
            });
        });

        // Open modal and populate branches dynamically
        $('#assignBranchBtn').on('click', function () {
            // Fetch branches dynamically
            $.ajax({
                url: "<?= base_url('Teacher_Books/get_assigned_branches') ?>",
                type: 'POST',
                data: { book_ids: getSelectedBooks() },
                success: function (response) {
                    const res = JSON.parse(response);

                    if (res.status === 'success') {
                        const branches = res.branches;

                       
                        branchTable.clear();

                        branches.forEach((branch, index) => {
                            const isChecked = selectedBranches.includes(branch.id) ? 'checked' : '';
                            branchTable.row.add([
                                index + 1,
                                branch.name,
                                `<div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="branch_ids[]" value="${branch.id}" id="branch_${branch.id}" ${isChecked}>
                                     <label class="form-check-label" for="branch_${branch.id}">${branch.name}</label>
                                </div>`
                            ]);
                        });

                        branchTable.draw();
                        mfp_modal('#modal');
                    } else {
                        alert('Failed to fetch branches. Please try again.');
                    }
                }
            });
        });

        // Submit selected branches
        $('#assignBranchModalBtn').on('click', function () {
            if (selectedBranches.length === 0) {
                alert('Please select at least one branch.');
                return;
            }

            const bookIds = getSelectedBooks();

            $.ajax({
                url: "<?= base_url('Teacher_Books/assign_branches') ?>",
                type: 'POST',
                data: { book_ids: bookIds, branch_ids: selectedBranches },
                success: function (response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        swal({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: res.message,
                            showConfirmButton: false,
                            timer: 8000
                        });
                        $('#assignBranchModal').modal('hide');
                        window.location.reload();
                    } else {
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

        // Utility function to get selected book IDs
        function getSelectedBooks() {
            const selectedBooks = [];
            $('input[name="book_ids[]"]:checked').each(function () {
                selectedBooks.push($(this).val());
            });
            return selectedBooks;
        }
    });


</script>