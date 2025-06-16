<!doctype html>
<html class="fixed sidebar-left-sm <?php echo ($theme_config['dark_skin'] == 'true' ? 'dark' : 'sidebar-light');?>">
<!-- html header -->
<?php $this->load->view('layout/header.php');?>

<body class="loading-overlay-showing" data-loading-overlay>
	<!-- page preloader -->
	<div class="loading-overlay dark">
		<div class="ring-loader">
			Loading <span></span>
		</div>
	</div>
	<section class="body">
		<!-- top navbar-->
		<?php $this->load->view('layout/topbar.php');?>
		<div class="inner-wrapper">
			<!-- sidebar -->
			<?php 
			if (is_student_loggedin() || is_parent_loggedin()) {
				$this->load->view('userrole/sidebar'); 
			} else {
				$this->load->view('layout/sidebar'); 
			} 
			?>
			<!-- page main content -->
			<section role="main" class="content-body">
				<header class="page-header">
					<a class="page-title-icon" href="<?php echo base_url('dashboard');?>"><i class="fas fa-home"></i></a>
					<h2><?php echo $title;?></h2>
				</header>
				<div class="row">
					<div class="col-sm-12">
					<section class="panel">
						<header class="panel-heading">
							<h4 class="panel-title"><?=translate('select_ground')?></h4>
						</header>
						<?php echo form_open($this->uri->uri_string(), array('class' => 'validate'));?>
						<div class="panel-body">
							<div class="row mb-sm">
							<?php if (is_superadmin_loggedin() ): ?>
								<div class="col-md-3">
									<div class="form-group">
										<label class="control-label"><?=translate('branch')?> <span class="required">*</span></label>
										<?php
											$arrayBranch = $this->app_lib->getSelectList('branch');
											echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'), "class='form-control' id='branch_id' onchange='getClassByBranch(this.value)'
											required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity'");
										?>
									</div>
								</div>
							<?php endif; ?>
								<div class="col-md-3 mb-sm">
									<div class="form-group">
										<label class="control-label"><?=translate('class')?></label>
										<?php
											$arrayClass = $this->app_lib->getClass($branch_id);
											echo form_dropdown("class_id", $arrayClass, set_value('class_id'), "class='form-control' id='class_id' 
											data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
										?>
									</div>
								</div>
								<div class="col-md-3 mb-sm">
									<div class="form-group">
										<label class="control-label"><?php echo translate('student'); ?> <span class="required">*</span></label>
										<?php
											$arraySection = $this->app_lib->getSections(set_value('student_id'), false);
											echo form_dropdown("student_id", $arraySection, set_value('student_id'), "class='form-control' id='student_id' 
											data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
										?>
									</div>
								</div>
							</div>
						</div>
						<footer class="panel-footer">
							<div class="row">
								<div class="col-md-offset-10 col-md-2">
									<button type="submit" name="search" value="1" class="btn btn-default btn-block"> <i class="fas fa-filter"></i> Get Report</button>
								</div>
							</div>
						</footer>
						<?php echo form_close();?>
					</section>
					</div>
				</div>
				<?php if ($this->session->has_userdata('reportstudent_id')) {?>
				<div class="row">
                     <div class="col-sm-3">
                        <div class="card mt-20">
                            <img class="card-img-top" src="<?php echo base_url('assets/reports/50b31774-42c8-45fd-a724-afd588e063eqda.png'); ?>" alt="Card image" style="width:100%;height: 415px;">
                                <div class="card-body">
                                    <h4 class="card-title">Exam Based - Matterhorn Report</h4>
                                    <a href="<?= base_url('report/annual_examination_report') ?>" class="btn btn-primary" target="_blank">Visit Report</a>
                                </div>
                        </div>
                    </div>   
                    <div class="col-sm-3">
                        <div class="card mt-20">
                            <img class="card-img-top" src="<?php echo base_url('assets/reports/70d145f2-a29f-4e6c-a43c-90c09f5e742a75.png'); ?>" alt="Card image" style="width:100%;height: 415px;">
                                <div class="card-body">
                                    <h4 class="card-title">Skill Based - Mount Kilimanjaro Report</h4>
                                    <a href="<?= base_url('report/skill_based_report') ?>" class="btn btn-primary" target="_blank">Visit Report</a>
                                </div>
                        </div>
                    </div> 
                    <div class="col-sm-3">
                        <div class="card mt-20">
                            <img class="card-img-top" src="<?php echo base_url('assets/reports/5e2b5355-1w585-4f38-a9e1-ee4fb982fbfe1.png'); ?>" alt="Card image" style="width:100%;height: 415px;">
                                <div class="card-body">
                                    <h4 class="card-title">Subject Based - Mount Etna Report</h4>
                                    <a href="<?= base_url('report/subject_based_report') ?>" class="btn btn-primary" target="_blank">Visit Report</a>
                                </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card mt-20">
                            <img class="card-img-top" src="<?php echo base_url('assets/reports/e4e3c2cd-7149-43d6-92c4-983b821fc1bb.jpg'); ?>" alt="Card image" style="width:100%;height: 415px;">
                                <div class="card-body">
                                    <h4 class="card-title">Exam Type + Skill Based - Mount Nemrut Report</h4>
                                    <a href="<?= base_url('report/type_skill_based_report') ?>" class="btn btn-primary" target="_blank">Visit Report</a>
                                </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card mt-20">
                            <img class="card-img-top" src="<?php echo base_url('assets/reports/5c1f48f7-f611-4315-aaad-9c9157f3e448.png'); ?>" alt="Card image" style="width:100%;height: 415px;">
                                <div class="card-body">
                                    <h4 class="card-title">Term End Report</h4>
                                    <a href="<?= base_url('report/term_end_report') ?>" class="btn btn-primary" target="_blank">Visit Report</a>
                                </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card mt-20">
                            <img class="card-img-top" src="<?php echo base_url('assets/reports/Mount_Miller_Skill_Based_Report_With_Student_Image11.png'); ?>" alt="Card image" style="width:100%;height: 415px;">
                                <div class="card-body">
                                    <h4 class="card-title">Grade Book Report</h4>
                                    <a href="<?= base_url('report/grade_book_report') ?>" class="btn btn-primary" target="_blank">Visit Report</a>
                                </div>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="card mt-20">
                            <img class="card-img-top" src="<?php echo base_url('assets/reports/term_end_report_versin_2.png'); ?>" alt="Card image" style="width:100%;height: 415px;">
                                <div class="card-body">
                                    <h4 class="card-title">Term End Report Version 2</h4>
                                    <a href="<?= base_url('report/term_end_report_versin_2') ?>" class="btn btn-primary" target="_blank">Visit Report</a>
                                </div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="card mt-20">
                            <img class="card-img-top" src="<?php echo base_url('assets/reports/term_end_report_versin_3.png'); ?>" alt="Card image" style="width:100%;height: 415px;">
                                <div class="card-body">
                                    <h4 class="card-title">Term End Report Version 3</h4>
                                    <a href="<?= base_url('report/term_end_report_versin_3') ?>" class="btn btn-primary" target="_blank">Visit Report</a>
                                </div>
                        </div>
                    </div>
                </div>
				<?php } ?>
			</section>
		</div>
	</section>

	<!-- JS Script -->
	<?php $this->load->view('layout/script.php');?>
	
	<?php
	$alertclass = "";
	if($this->session->flashdata('alert-message-success')){
		$alertclass = "success";
	} else if ($this->session->flashdata('alert-message-error')){
		$alertclass = "error";
	} else if ($this->session->flashdata('alert-message-info')){
		$alertclass = "info";
	}
	if($alertclass != ''):
		$alert_message = $this->session->flashdata('alert-message-'. $alertclass);
	?>
		<script type="text/javascript">
			swal({
				toast: true,
				position: 'top-end',
				type: '<?php echo $alertclass?>',
				title: '<?php echo $alert_message?>',
				confirmButtonClass: 'btn btn-default',
				buttonsStyling: false,
				timer: 8000
			})
		</script>
	<?php endif; ?>

	<!-- sweetalert box -->
	<script type="text/javascript">
		function confirm_modal(delete_url) {
			swal({
				title: "<?php echo translate('are_you_sure')?>",
				text: "<?php echo translate('delete_this_information')?>",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn btn-default swal2-btn-default",
				cancelButtonClass: "btn btn-default swal2-btn-default",
				confirmButtonText: "<?php echo translate('yes_continue')?>",
				cancelButtonText: "<?php echo translate('cancel')?>",
				buttonsStyling: false,
				footer: "<?php echo translate('deleted_note')?>"
			}).then((result) => {
				if (result.value) {
					$.ajax({
						url: delete_url,
						type: "POST",
						success:function(data) {
							swal({
							title: "<?php echo translate('deleted')?>",
							text: "<?php echo translate('information_deleted')?>",
							buttonsStyling: false,
							showCloseButton: true,
							focusConfirm: false,
							confirmButtonClass: "btn btn-default swal2-btn-default",
							type: "success"
							}).then((result) => {
								if (result.value) {
									location.reload();
								}
							});
						}
					});
				}
			});
		}
	</script>
	<script type="text/javascript">
	$(document).ready(function () {
        $('#class_id').on('change', function() {
            var class_id = $(this).val();
            var branch_id = ($( "#branch_id" ).length ? $('#branch_id').val() : "");
			$.ajax({
				url: base_url + 'ajax/getStudentByClass',
				type: 'POST',
				data: {
					branch_id: branch_id,
					class_id: class_id
				},
				success: function (data) {
					$('#student_id').html(data);
				}
			});
        });
	});
	</script>
</body>
</html>