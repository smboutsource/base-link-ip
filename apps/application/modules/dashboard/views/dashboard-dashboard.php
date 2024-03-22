<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}
?>
<!-- Begin Page Content -->
<div class="container-fluid">
	<!-- Page Heading -->
	<h1 class="h3 mb-4 text-gray-800">Profile</h1>
	<div class="row">
		<div class="col-md-12">
			<div class="card card-primary">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">
						<?= (isset($page_title) ? ucfirst($page_title) : 'Dashboard');?>
					</h6>
				</div>
				<div class="card-body">
					<?php
					if (isset($userdata->user_email)) {
						?>
						<div class="table-responsive">
							<table class="table table-bordered table-hovered tab;e-stripped">
								<thead>
									<tr>
										<th>#</th>
										<th>Properties</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Email</td>
										<td><?= $userdata->user_email;?></td>
									</tr>
								</tbody>
							</table>
						</div>
						<?php
					}
					?>
				</div>
				<div class="card-footer">
					<div class="form-group">
						<a class="btn btn-info btn-sm btn-user" href="<?= base_url('auth/logout');?>">
							<i class="fas fa-power-off"></i> Logout
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /.container-fluid -->