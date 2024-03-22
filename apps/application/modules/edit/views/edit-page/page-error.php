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
			<div class="card card-primary">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">
						<?= (isset($collect['single_page']->page_purpose) ? ucfirst($collect['single_page']->page_purpose) : 'Page Purpose');?>
					</h6>
				</div>
				<div class="card-body">
					<?php
					if (isset($json_response['errors']) && is_array($json_response['errors'])) {
						?>
						<ul class="list-group">
						<?php
						foreach ($json_response['errors'] as $error_msg) {
							?>
							<li class="list-group-item"><?=$error_msg;?></li>
							<?php
						}
						?>
						</ul>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /.container-fluid -->