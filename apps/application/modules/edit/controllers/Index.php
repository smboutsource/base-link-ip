<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Index extends MY_Controller {
	private $base_auth;
	function __construct() {
		parent::__construct();
		
		$this->load->model('auth/Model_auth', 'mod_auth');
		
		$this->base_auth = $this->mod_auth->get_base_auth();
	}
	
	
	public function index() {

		redirect(base_url('edit/page'));
		exit;
	}
}