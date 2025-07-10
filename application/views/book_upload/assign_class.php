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
                                    <button type="button" class="btn btn-primary mb-4" id="assignClassBtn">Assign
                                        Class</button>
                                </div>
                            </div>
                            <table class="table table-bordered table-hover table-condensed mb-none table-export">
                                <thead>
                                    <tr>
                                        <th><?= translate('sl') ?></th>
                                        <th><?= translate('title') ?></th>
                                        <th><?= translate('Book Cover') ?></th>
                                        <th><?= translate('Assigned Class') ?></th>
                                        <th><?= translate('action') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $count = 1;
                                    foreach ($books as $row): ?>
                                        <tr>
                                            <td><?php echo $count++; ?></td>
                                            <td><?php echo $row['title']; ?></td>
                                            <td>
                                                <img src="<?= base_url($row['book_img']) ?>" width="100" height="120px" />
                                            </td>
                                            <td><?php echo $row['assigned_classes'] ?></td>
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

<div id="classAssignmentModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Assign Class</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <?php if (is_superadmin_loggedin()): ?>
                    <div class="form-group row">
                        <label class="control-label col-md-3"><?= translate('branch') ?> <span
                                class="required">*</span></label>
                        <div class="col-md-6">
                            <?php
                            $arrayBranch = $this->app_lib->getSelectList('branch');
                            echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id' data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
                            ?>
                            <span class="error"></span>
                        </div>

                    </div>
                <?php endif; ?>

                <div class="form-group row">
                    <label class="control-label col-md-3"><?= translate('class') ?> <span
                            class="required">*</span></label>
                    <div class="col-md-6">
                        <?php
                        $session_data = $this->session->all_userdata();
                        $loggedin_branchId = $session_data['loggedin_branch'];

                        $arrayClass = $this->app_lib->getSelectClassByBranch($loggedin_branchId);
                        echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' onchange='getSectionByClass(this.value,0)'
                                                                                                data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
                        ?>
                        <span class="error"></span>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary float-right" id="assignClassModalBtn">Assign
                            Class</button>
                    </div>
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
        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            data: {
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
            }
        });

        $('#assignClassBtn').on('click', function () {
            var selectedBooks = [];
            $('input[name="book_ids[]"]:checked').each(function () {
                selectedBooks.push($(this).val());
            });

            if (selectedBooks.length === 0) {
                alert('Please select at least one book.');
                return;
            }

            $('#classAssignmentModal').modal('show');
        });

        $('#branch_id').on('change', function () {
            var branch_id = $(this).val();
            if (branch_id) {
                $.ajax({
                    url: "<?= base_url('Ajax/getClassByBranch') ?>",
                    type: 'POST',
                    data: { branch_id: branch_id },
                    success: function (response) {
                        $('#class_id').html(response);
                        $('#class_id').select2(); // Re-initialize select2
                    }
                });
            } else {
                $('#class_id').html('<option value="">Select Class</option>');
                $('#class_id').select2();
            }
        });

        $('#assignClassModalBtn').on('click', function () {
            var class_id = $('#class_id').val();
            var selectedBooks = [];

            // Get selected books
            $('input[name="book_ids[]"]:checked').each(function () {
                selectedBooks.push($(this).val());
            });

            var branch_id;
            if ($('#branch_id').length) {
                // If branch dropdown exists (superadmin)
                branch_id = $('#branch_id').val();
                if (!branch_id) {
                    swal({
                        toast: true,
                        position: 'top-end',
                        title: 'Please select a branch.',
                        showConfirmButton: false,
                        timer: 8000
                    });
                    return;
                }
            } else {
                branch_id = '<?php echo $this->session->userdata("loggedin_branch"); ?>';
            }

            // Validate class selection
            if (!class_id) {
                swal({
                    toast: true,
                    position: 'top-end',
                    title: 'Please select a class.',
                    showConfirmButton: false,
                    timer: 8000
                });
                return;
            }

            // Prepare form data
            var formData = {
                class_id: class_id,
                book_ids: selectedBooks,
                branch_id: branch_id
            };

            console.log("formdata", formData);

            // Add CSRF token
            formData['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';

            $.ajax({
                url: "<?= base_url('StudentBookUpload/assign_class_to_books') ?>",
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    if (response.status === 'success') {
                        swal({
                            toast: true,
                            position: 'top-end',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 8000
                        });
                        $('#classAssignmentModal').modal('hide');
                        window.location.reload();
                    } else {
                        swal({
                            toast: true,
                            position: 'top-end',
                            title: response.message || 'Error assigning class to books.',
                            showConfirmButton: false,
                            timer: 8000
                        });
                    }
                },
                error: function (xhr, status, error) {
                    swal({
                        toast: true,
                        position: 'top-end',
                        title: 'An error occurred while assigning class to books.',
                        showConfirmButton: false,
                        timer: 8000
                    });
                }
            });
        });
    });
</script>