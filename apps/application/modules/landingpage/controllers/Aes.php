<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Aes extends MY_Controller {
	
	private $error = false, $error_msg = [];
	function __construct() {
		parent::__construct();
		$this->load->model('auth/Model_auth', 'mod_auth');
		$this->base_auth = $this->mod_auth->get_base_auth();
		
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}