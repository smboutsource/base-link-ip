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
						<?= (isset($page_title) ? $page_title : 'App Dashboard');?>
					</h6>
				</div>
				<div class="card-body">
					
					<?php
					if (isset($collect['configuration']['env'])) {
						echo json_encode($collect['configuration']['env']);
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /.container-fluid -->