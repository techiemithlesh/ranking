<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <form id="csvUploadForm" method="post" enctype="multipart/form-data">
                <header class="panel-heading">
                    <h4 class="panel-title">
                        <i class="fas fa-file-archive"></i> Multiple Branch Import
                    </h4>
                </header>
                <div class="panel-body">
                    <div id="error-alert" class="alert alert-danger" style="display: none;"></div>
                    <div id="success-alert" class="alert alert-success" style="display: none;"></div>
                    
                    <div class="form-group mt-md">
                        <div class="col-md-12 mb-md">
                            <a class="btn btn-default pull-right" href="<?= base_url('branch/csv_Sampledownloader') ?>">
                                <i class='fas fa-file-download'></i> Download Sample Import File
                            </a>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Instructions:</strong><br />
                                1. Download the sample file.<br />
                                2. Fill in the CSV file as per the sample format.<br />
                                3. Required fields: branch_name, school_name, email, mobileno.<br />
                                4. Optional fields: , currency, currency_symbol, city, state, address.<br />
                                5. Make sure email addresses are unique and valid.
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label col-md-3">Select CSV File <span class="required">*</span></label>
                        <div class="col-md-6 mb-lg">
                            <input type="file" name="branchfile" id="branchfile" class="form-control" accept=".csv"/>
                        </div>
                    </div>
                </div>
                <footer class="panel-footer">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-2">
                            <button type="button" id="uploadCsvButton" class="btn btn-default btn-block">
                                <i class="fas fa-plus-circle"></i> Import
                            </button>
                        </div>
                    </div>
                </footer>
            </form>
        </section>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#uploadCsvButton').click(function(e) {
        e.preventDefault();
        
        // Hide previous alerts
        $('#error-alert, #success-alert').hide();
        
        // Get form data
        let formData = new FormData($('#csvUploadForm')[0]);
        formData.append('save', 'true');
        
        // Validate file
        let fileInput = $('#branchfile')[0];
        if (!fileInput.files.length) {
            $('#error-alert').text('Please select a CSV file.').show();
            return;
        }
        
        // Check file extension
        let fileName = fileInput.files[0].name;
        let fileExt = fileName.split('.').pop().toLowerCase();
        if (fileExt !== 'csv') {
            $('#error-alert').text('Please select a valid CSV file.').show();
            return;
        }

        // Show loading state
        $('#uploadCsvButton').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
        
        $.ajax({
            url: '<?= base_url("branch/csv_upload") ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log("Response:", response);
                
                if (response.status) {
                    $('#success-alert').text(response.message).show();
                    if (response.errors) {
                        $('#error-alert').text(response.errors).show();
                    }
                    // Reset form on success
                    $('#csvUploadForm')[0].reset();
                } else {
                    let errorMsg = response.message;
                    if (response.validation_errors) {
                        errorMsg += '\n' + JSON.stringify(response.validation_errors);
                    }
                    $('#error-alert').text(errorMsg).show();
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An unexpected error occurred.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    errorMessage = xhr.responseText || errorMessage;
                }
                $('#error-alert').text(errorMessage).show();
            },
            complete: function() {
                // Reset button state
                $('#uploadCsvButton').prop('disabled', false).html('<i class="fas fa-plus-circle"></i> Import');
            }
        });
    });
});
</script>