<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}
?>
<!-- Begin Page Content -->
<div class="container-fluid">

	<!-- Page Heading -->
	<h1 class="h3 mb-4 text-gray-800">Server Data</h1>
	
	<!-- Content Row -->
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-4">
				<div class="card-header">
					<div class="d-flex flex-wrap justify-content-between align-items-center">
						<h5 class="card-title m-0">Server Upstream</h5>
					</div>
				</div>
				<div class="card-body">
					<table class="table table-bordered table-striped" id="tbl-app-serverdata" style="width: 100%" data-page-perpage="<?= (isset($collect['per_page']) ? $collect['per_page'] : 10);?>">
						<thead>
							<tr>
								<th>#</th>
								<th>Purpose</th>
								<th>Content</th>
								<th>Last Update</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
				<div class="card-footer">
					<div class="form-group">
						<button id="btn-restart-server-service" class="btn btn-sm btn-danger" type="button">
							<i class="fas fa-fw fa-power-off"></i> Restart
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /.container-fluid -->