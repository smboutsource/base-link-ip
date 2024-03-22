<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class View extends MY_Controller {
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
	}
	
	
	
	public function configuration() {
		$collectData = [
			'page'			=> 'edit-view-configuration',
			'collect'		=> [],
			'userdata'		=> $this->userdata,
			'page_title'	=> 'App Configuration',
		];
		

		$collectData['collect']['configuration'] = [
			'env'			=> Instance_config::$env_group,
			'configs'		=> Instance_config::$env_apc,
		];
		
		$this->load->view('page/page.php', $collectData);
	}
}