<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">

	<!-- Page Heading -->
	<h1 class="h3 mb-4 text-gray-800">Edit User</h1>
	
	<!-- Content Row -->
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-4">
				<div class="card-header">
					<div class="d-flex flex-wrap justify-content-between align-items-center">
						<h5 class="card-title m-0">Manage Users</h5>
						<button id="btn-add-app-userdata" class="btn btn-sm btn-primary" type="button"><i class="fa fa-plus"></i> Add User</button>
					</div>
				</div>
				<div class="card-body">
					<table class="table table-bordered table-striped" id="tbl-app-userdata" style="width: 100%" data-page-perpage="<?= (isset($collect['per_page']) ? $collect['per_page'] : 10);?>">
						<thead>
							<tr>
								<th>#</th>
								<th>Email</th>
								<th>Active</th>
								<th>Role</th>
								<th>Created</th>
								<th>Updated</th>
								<th>Edit</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /.container-fluid -->
