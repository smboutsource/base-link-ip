<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Landingpage extends MY_Controller {
	
	private $error = false, $error_msg = [];
	function __construct() {
		parent::__construct();
		
		$this->load->model('auth/Model_auth', 'mod_auth');
		$this->base_auth = $this->mod_auth->get_base_auth();
		
		/*
		if (!$this->userdata = $this->mod_auth->start_userdata()) {
			return $this->mod_auth->required_login();
		}
		 */
		
		$this->load->model('page/Model_landingpage', 'mod_landingpage');
		
	}
	
	public function index() {
		$collectData = [
			'page'			=> 'landingpage-landingpage',
			'collect'		=> [],
		];
		try {
			$collectData['collect']['landingpage_data'] = $this->mod_landingpage->get_page_landingpage('display');
			
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot get landingpage display data with exception: {$ex->getMessage()}.";
		}
		
		if (!$this->error) {
			$collectData['collect']['json_response'] = [
				'status'		=> true,
				'errors'		=> false,
			];
		} else {
			$collectData['collect']['json_response'] = [
				'status'		=> false,
				'errors'		=> $this->error_msg,
			];
		}
		$this->load->view('landingpage/landingpage.php', $collectData);
	}
}
