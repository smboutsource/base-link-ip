<?php
if (!defined("BASEPATH")) {
	exit("Cannot load script directly.");
}

$active_sidebars = [
	'section'			=> 'dashboard',
];
$page = (isset($page) && is_string($page)) ? strtolower($page) : 'default';
switch ($page) {
	case 'dashboard-dashboard':
	case 'dashboard-error':
		$active_sidebars['section'] = 'dashboard';
	break;
	
	case 'edit-page-purpose':
	case 'edit-page-error':
		$active_sidebars['section'] = 'pages';
	break;
	
	case 'edit-users':
	case 'page-page-preview':
	case 'page-page-draft':
	case 'page-page-display':
	case 'edit-users-index':
	case 'edit-users-error':
		$active_sidebars['section'] = 'utilities';
	break;

	case 'edit-server-cert':
	case 'edit-server-app':
	case 'edit-server-git':
		$active_sidebars['section'] = 'addons';
	break;
	
	case 'edit-view-configuration':
	case 'edit-firebase-utilities-edit':
	case 'edit-firebase-utilities-push':
	case 'edit-firebase-utilities-config':
	case 'upload-asset-upload':
	case 'upload-asset-edit':
	case 'upload-asset-index':
		$active_sidebars['section'] = 'addons';
	break;
	
	case 'default':
	default:
		$active_sidebars['section'] = 'dashboard';
	break;
}
?>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

	<!-- Sidebar - Brand -->
	<a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= base_url('dashboard');?>">
		<div class="sidebar-brand-icon rotate-n-15">
			<i class="fas fa-laugh-wink"></i>
		</div>
		<div class="sidebar-brand-text mx-3">SMB <sup>Link IP</sup></div>
	</a>

	<!-- Divider -->
	<hr class="sidebar-divider my-0">

	<!-- Nav Item - Dashboard -->
	<li class="nav-item <?= (($active_sidebars['section'] == 'dashboard') ? 'active' : 'collapsed');?>">
		<a class="nav-link <?= (($active_sidebars['section'] == 'dashboard') ? 'show' : '');?>" href="<?= base_url('dashboard');?>">
			<i class="fas fa-fw fa-tachometer-alt"></i>
			<span>Dashboard</span>
		</a>
	</li>

	<!-- Divider -->
	<hr class="sidebar-divider">

	<!-- Heading -->
	<div class="sidebar-heading">
		Interface
	</div>

	<!-- Nav Item - Pages Collapse Menu -->
	<li class="nav-item">
		<a class="nav-link <?= (($active_sidebars['section'] == 'pages') ? 'active' : 'collapsed');?>" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
			<i class="fas fa-fw fa-folder"></i>
			<span>Pages</span>
		</a>
		<div id="collapsePages" class="collapse <?= (($active_sidebars['section'] == 'pages') ? 'show' : '');?>" aria-labelledby="headingPages" data-parent="#accordionSidebar">
			<div class="bg-white py-2 collapse-inner rounded">
				<h6 class="collapse-header">List Pages:</h6>
				<?php
				$list_pages = base_list_pages();
				if (is_array($list_pages) && !empty($list_pages)) {
					foreach ($list_pages as $lp) {
						$sidebar_pages_purpose_collapeses = [
							'class_name'		=> 'collapse-item'
						];
						if (isset($collect['single_page']->page_purpose)) {
							if ($collect['single_page']->page_purpose == $lp->page_purpose) {
								$sidebar_pages_purpose_collapeses['class_name'] = 'collapse-item active';
							} else {
								$sidebar_pages_purpose_collapeses['class_name'] = 'collapse-item';
							}
						}
						?>
						<a class="<?= $sidebar_pages_purpose_collapeses['class_name'];?>" href="<?= base_url("edit/page/purpose/{$lp->page_purpose}");?>">
							<?= ucfirst($lp->page_purpose);?>
						</a>
						<?php
					}
				}
				?>
			</div>
		</div>
	</li>

	<!-- Nav Item - Utilities Collapse Menu -->
	<li class="nav-item">
		<a class="nav-link <?= (($active_sidebars['section'] == 'utilities') ? 'active' : 'collapsed');?>" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
			<i class="fas fa-fw fa-wrench"></i>
			<span>Utilities</span>
		</a>
		<div id="collapseUtilities" class="collapse <?= (($active_sidebars['section'] == 'utilities') ? 'show' : '');?>" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
			<div class="bg-white py-2 collapse-inner rounded">
				<h6 class="collapse-header">Custom Utilities:</h6>
				<a class="collapse-item <?= (($page == 'edit-users-index') ? 'active' : '');?>" href="<?= base_url('edit/users');?>">
					Users
				</a>
				<a class="collapse-item <?= (($page == 'page-page-preview') ? 'active' : '');?>" href="<?= base_url('landingpage/preview');?>" target="_blank">
					Preview
				</a>
			</div>
		</div>
	</li>

	<!-- Divider -->
	<hr class="sidebar-divider">

	<!-- Heading -->
	<div class="sidebar-heading">
		Addons
	</div>
	<!-- Nav Item - Config -->
	<li class="nav-item <?= (($page == 'edit-server-cert') ? 'show active' : '');?>">
		<a class="nav-link <?= (($page == 'edit-server-cert') ? 'active' : '');?>" href="<?= base_url('edit/server/cert');?>">
			<i class="fas fa-fw fa-cog"></i>
			<span>SSL Cert</span>
		</a>
	</li>
	<li class="nav-item <?= (($page == 'server-upstream-server') ? 'show active' : '');?>">
		<a class="nav-link <?= (($page == 'server-upstream-server') ? 'active' : '');?>" href="<?= base_url('server/upstream/server');?>">
			<i class="fas fa-fw fa-cog"></i>
			<span>Upstream</span>
		</a>
	</li>
	<!-- Divider -->
	<hr class="sidebar-divider d-none d-md-block">

	<!-- Sidebar Toggler (Sidebar) -->
	<div class="text-center d-none d-md-inline">
		<button class="rounded-circle border-0" id="sidebarToggle"></button>
	</div>

</ul>
<!-- End of Sidebar -->