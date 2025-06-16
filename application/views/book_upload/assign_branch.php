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
                                        <th>
                                            <span id="selectAllTooltip" data-toggle="tooltip"
                                                title="Double-click to select all">
                                                <input type="checkbox" id="selectAllCheckbox">
                                            </span>
                                        </th>
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

<div class="zoom-anim-dialog modal-block modal-block-lg mfp-hide" id="modal">
    <section class="panel">
        <header class="panel-heading">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="panel-title"><i class="fas fa-bars"></i> <?php echo translate('Assign Branch'); ?></h4>
                </div>
                <div class="col-md-6">
                    <div class="form-check" style="margin-top: 5px;">
                        <input class="form-check-input" type="checkbox" id="selectAllBranches">
                        <label class="form-check-label" for="selectAllBranches">
                            Select All Branches
                        </label>
                    </div>
                </div>
            </div>
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
        let selectedBranches = [];
        let selectedBooks = [];


        const branchTable = $('#branchTable').DataTable({
            pageLength: 10,
            'drawCallback': function () {
                $('input[name="branch_ids[]"]').each(function () {
                    $(this).prop('checked', selectedBranches.includes($(this).val()));
                });
                updateSelectAllCheckboxState();
            }
        });


        function getSelectedBooks() {
            return $('input[name="book_ids[]"]:checked').map(function () {
                return $(this).val();
            }).get();
        }


        $("#selectAllCheckbox").on("click", function () {
            const isChecked = $(this).prop("checked");
            $('input[name="book_ids[]"]').prop("checked", isChecked);
        });


        $('input[name="book_ids[]"]').on("change", function () {
            $("#selectAllCheckbox").prop(
                "checked",
                $('input[name="book_ids[]"]:checked').length === $('input[name="book_ids[]"]').length
            );
        });

        // Handle modal select all functionality
        $('#selectAllBranches').on('change', function () {
            const isChecked = $(this).prop('checked');
            const totalRows = branchTable.rows().count(); // Good to keep this for informational purposes
            // console.log("Total rows:", totalRows);

            if (isChecked) {
                selectedBranches = [];

                branchTable.rows().every(function (rowIdx, tableLoop, rowLoop) {
                    const data = this.data();
                    // console.log("data", data);
                    // const branchId = data[2].val; 
                    // console.log("brnachid", branchId);

                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data[2]; // Insert the HTML string into a temp div

                    const checkbox = tempDiv.querySelector('input[name="branch_ids[]"]');

                    if (checkbox) {
                        const branchId = checkbox.value; // Get the value attribute (branch ID)
                        console.log("Extracted branch ID:", branchId);
                        selectedBranches.push(branchId);
                        return true;
                    }

                    // selectedBranches.push(branchId);
                    
                });


                $('input[name="branch_ids[]"]').each(function () {
                    $(this).prop('checked', true);
                    console.log("check", $('input[name="branch_ids[]"]'));
                });

            } else {
                selectedBranches = [];
                $('input[name="branch_ids[]"]').prop('checked', false);
            }

            branchTable.draw();
        });

        // Handle individual branch checkbox changes
        $(document).on('change', 'input[name="branch_ids[]"]', function () {
            const branchId = $(this).val();
            const isChecked = $(this).prop('checked');

            if (isChecked && !selectedBranches.includes(branchId)) {
                selectedBranches.push(branchId);
            } else if (!isChecked) {
                selectedBranches = selectedBranches.filter(id => id !== branchId);
            }

            updateSelectAllCheckboxState();
        });

        function updateSelectAllCheckboxState() {
            const visibleCheckboxes = $('input[name="branch_ids[]"]');
            const allSelected = visibleCheckboxes.length > 0 &&
                visibleCheckboxes.length === selectedBranches.length;
            $('#selectAllBranches').prop('checked', allSelected);
        }

        // Assign Branch button click handler
        $('#assignBranchBtn').on('click', function () {
            selectedBooks = getSelectedBooks(); // Update selectedBooks

            if (selectedBooks.length === 0) {
                alert('Please select at least one book.');
                return;
            }

            $.ajax({
                url: base_url + 'StudentBookUpload/get_assigned_branches',
                type: 'POST',
                data: { book_ids: selectedBooks },
                success: function (response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            const { branches, assignedBranchIds } = res;
                            branchTable.clear();

                            if (branches.length > 0) {
                                selectedBranches = assignedBranchIds
                                    .filter(branch => branch.assigned === '1')
                                    .map(branch => branch.id);

                                branches.forEach((branch, index) => {
                                    const isChecked = selectedBranches.includes(branch.id) ? 'checked' : '';

                                    branchTable.row.add([
                                        index + 1,
                                        branch.name,
                                        `<div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                            name="branch_ids[]" value="${branch.id}" 
                                            id="branch_${branch.id}" ${isChecked}>
                                        <label class="form-check-label" for="branch_${branch.id}">
                                            ${branch.name}
                                        </label>
                                    </div>`
                                    ]);
                                });
                            } else {
                                branchTable.row.add(["", "No branches available.", ""]);
                            }
                            branchTable.draw();
                            updateSelectAllCheckboxState();
                            mfp_modal('#modal');
                        } else {
                            alert('Failed to fetch branches. Please try again.');
                        }
                    } catch (e) {
                        alert('Error parsing response.');
                    }
                },
                error: function () {
                    alert('An error occurred while fetching branches.');
                }
            });
        });

        // Assign Branch Modal button click handler
        $('#assignBranchModalBtn').on('click', function () {
            if (selectedBranches.length === 0) {
                alert('Please select at least one branch.');
                return;
            }

            $.ajax({
                url: base_url + 'StudentBookUpload/assign_branches',
                type: 'POST',
                data: {
                    book_ids: selectedBooks,
                    branch_ids: selectedBranches
                },
                success: function (response) {
                    try {
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
                            $.magnificPopup.close();
                            window.location.reload();
                        } else {
                            alert('Failed to assign branches.');
                        }
                    } catch (e) {
                        alert('Error processing response.');
                    }
                },
                error: function () {
                    alert('An error occurred while assigning branches.');
                }
            });
        });
    });
</script>