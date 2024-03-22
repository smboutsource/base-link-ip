<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Dashboard extends MY_Controller {
	private $error = FALSE, $error_msg = [];
	private $base_auth;
	protected $userdata;
	function __construct() {
		parent::__construct();
		
		$this->load->model('auth/Model_auth', 'mod_auth');
		$this->load->model('edit/Model_page', 'mod_page');
		
		$this->base_auth = $this->mod_auth->get_base_auth();
		
		if (!$this->userdata = $this->mod_auth->start_userdata()) {
			return $this->mod_auth->required_login();
		}
		$this->load->model('page/Model_landingpage', 'mod_landingpage');
	}
	
	
	public function index() {
		$collectData = [
			'page'			=> 'dashboard-dashboard',
			'collect'		=> [],
			'userdata'		=> $this->userdata,
		];
		$collectData['page_title'] = 'Manage Dashboard';
		$collectData['collect']['list_pages'] = $this->mod_landingpage->get_base_list_pages();
		
		$this->load->view('page/page.php', $collectData);
	}
	
}