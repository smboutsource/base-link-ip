<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}
?>
<!-- Begin Page Content -->
<div class="container-fluid">

	<!-- Page Heading -->
	<h1 class="h3 mb-4 text-gray-800">Edit Page</h1>
	
	<!-- Content Row -->
	<div class="row">
		<div class="col-md-12">
			<form id="frm-edit-page-purpose" method="post" action="<?= base_url("edit/page/edit/action/{$collect['single_page']->page_id}");?>" data-page-id="<?= $collect['single_page']->page_id;?>">
				<div class="card card-primary">
					<div class="card-header">
						<h6 class="m-0 font-weight-bold text-primary">
							<?= (isset($collect['single_page']->page_purpose) ? ucfirst($collect['single_page']->page_purpose) : 'Page Purpose');?>
						</h6>
					</div>
					<div class="card-body">
						<?php
						if (isset($collect['single_page']->page_id) && isset($collect['single_page']->page_purpose)) {
							if (isset($collect['single_page']->page_content_string)) {
								/*
								<div class="form-group">
									<label for="inp-page-content">Page Content</label>
									<textarea id="inp-page-content" class="form-control" cols="64" rows="64"><?= $collect['single_page']->page_content_string;?></textarea>
								</div>
								*/
								?>
								<label for="inp-page-content">Page Content</label>
								<div id="inp-page-content" class="inner jumbotron"><?= $collect['single_page']->page_content_string;?></div>
								<?php
							}
						}
						?>
					</div>
					<div class="card-footer">
						<div class="form-group">
							<button id="btn-submit-save" class="btn btn-info btn-user btn-block" type="submit">
								<i class="fa fa-save"></i> Save
							</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- /.container-fluid -->