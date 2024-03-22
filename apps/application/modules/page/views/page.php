<?php
if (!defined("BASEPATH")) {
	exit("Cannot load script directly.");
}

$page = (isset($page) && is_string($page)) ? strtolower($page) : 'default';

$this->load->view('page/page-headers/header-begin.php');
$this->load->view('page/page-headers/header-wrapper.php');
$this->load->view('page/page-headers/header-sidebar.php');

$this->load->view('page/page-headers/header-content.php');
switch ($page) {
	
	case 'dashboard-dashboard':
		$page_includes = 'dashboard/dashboard-dashboard.php';
	break;
	case 'dashboard-error':
		$page_includes = 'page/page-errors/error-dashboard.php';
	break;
	
	case 'edit-page-purpose':
		$page_includes = 'edit/edit-page/page-purpose.php';
	break;
	case 'edit-page-error':
		$page_includes = 'edit/edit-page/page-error.php';
	break;
	
	case 'edit-users-index':
		$page_includes = 'edit/edit-users/users-index.php';
	break;
	case 'edit-users-error':
		$page_includes = 'edit/edit-users/users-error.php';
	break;
	
	case 'edit-view-configuration':
		$page_includes = 'edit/edit-configuration/configuration-view.php';
	break;
	case 'edit-server-cert':
		$page_includes = 'edit/edit-cert/cert-edit.php';
	break;
	case 'server-upstream-server':
	case 'server-upstream-index':
		$page_includes = 'server/server/server-index.php';
	break;
	case 'upload-asset-edit':
	case 'upload-asset-index':
	case 'upload-asset-upload':
		$page_includes = 'upload/asset-upload/upload-form.php';
	break;
	case 'upload-asset-error':
		$page_includes = 'upload/asset-error.php';
	break;
	case 'auth-logout-index':
	case 'auth-login-index':
		$page_includes = 'auth/login/index.php';
	break;
	case 'default':
	default:
		$page_includes = 'page/blank.php';
	break;
}
# Load page_includes
$this->load->view($page_includes);

$this->load->view('page/page-footers/footer-content.php');
$this->load->view('page/page-footers/footer-wrapper.php');
$this->load->view('page/page-footers/footer-begin.php');
switch ($page) {
	
	case 'edit-page-purpose':
		$this->load->view('edit/edit-page-includes/page-purpose.php');
	break;
	
	case 'edit-users-index':
		$this->load->view('edit/edit-users-includes/users-index.php');
	break;
	case 'edit-server-cert':
		$this->load->view('edit/edit-cert-includes/cert-edit.php');
	break;
	case 'server-upstream-server':
	case 'server-upstream-index':
		$this->load->view('server/server-includes/index.php');
	break;
	case 'upload-asset-index':
		$this->load->view('upload/asset-upload-includes/form.php');
	break;
	case 'default':
	default:
		
	break;
}
$this->load->view('page/page-footers/footer-end.php');
