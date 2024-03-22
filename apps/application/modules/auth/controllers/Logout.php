<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Logout extends MY_Controller {
	private $base_auth;
	private $error = false, $error_msg = [];
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
	
	
	public function index() {
		$collectData = [
			'page'			=> 'auth-logout-index',
			'collect'		=> [],
			'userdata'		=> $this->userdata,
		];
		
		if (!isset($collectData['userdata']->user_email)) {
			$this->error = true;
			$this->error_msg[] = "Not a valid logged-in app userdata.";
		}
		
		if (!$this->error) {
			try {
				$collectData['collect']['logout_response'] = $this->mod_auth->set_logged_user($collectData['userdata']->user_email);
				if (!isset($collectData['collect']['logout_response']['status'])) {
					$this->error = true;
					$this->error_msg[] = "Not have response status while logged-out app userdata.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot make login to auth center with exception: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'		=> true,
				'redirect_url'	=> base_url(),
				'errors'		=> false,
				'data'			=> $collectData['collect']['logout_response'],
			];
		} else {
			$collectData['json_response'] = [
				'status'		=> false,
				'redirect_url'	=> base_url(),
				'errors'		=> $this->error_msg,
				'data'			=> null,
			];
		}
		/*
		$this->output->set_content_type('application/json');
		$this->output->set_output(json_encode($collectData['json_response']));
		*/
		redirect($collectData['json_response']['redirect_url']);
		exit;
	}
	
	
	
}