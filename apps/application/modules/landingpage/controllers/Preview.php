<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Preview extends MY_Controller {
	private $error = false, $error_msg = [];
	protected $userdata;
	function __construct() {
		parent::__construct();
		
		$this->load->model('auth/Model_auth', 'mod_auth');
		if (!$this->userdata = $this->mod_auth->start_userdata()) {
			return $this->mod_auth->required_login();
		}
		
		
		$this->load->model('page/Model_landingpage', 'mod_landingpage');
	}
	
	public function index() {
		$collectData = [
			'page'			=> 'landingpage-preview',
			'collect'		=> [],
			'userdata'		=> $this->userdata,
		];
		try {
			$collectData['collect']['landingpage_data'] = $this->mod_landingpage->get_page_landingpage('preview');
			
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot get landingpage preview data with exception: {$ex->getMessage()}.";
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