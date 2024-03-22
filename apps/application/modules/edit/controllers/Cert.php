<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Cert extends MY_Controller {
	private $error = FALSE, $error_msg = [];
	private $base_auth;
	function __construct() {
		parent::__construct();
		
		$this->load->model('auth/Model_auth', 'mod_auth');
		$this->base_auth = $this->mod_auth->get_base_auth();
		# Load Model
		$this->load->model('edit/Model_cert', 'mod_cert');
	}
	
	
	
	
	public function validation(String $file_name = '') {
		$collectData = [
			'page'			=> 'edit-cert-validation',
			'collect'		=> [],
			'page_title'	=> 'SSL Cert',
			'file_name'		=> (is_string($file_name) ? trim($file_name) : ''),
		];
		if (empty($collectData['file_name'])) {
			$this->error = true;
			$this->error_msg[] = "File name for validation cannot be empty.";
		}
		if (!$this->error) {
			$collectData['file_name'] = str_replace('_', '-', $collectData['file_name']);
			try {
				$collectData['collect']['validation_data'] = $this->mod_cert->get_server_cert_validation();
				if (!isset($collectData['collect']['validation_data']->server_site_content)) {
					$this->error = true;
					$this->error_msg[] = "Not have server_site_content after fetching from model.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get validation data with exception: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'		=> true,
				'data'			=> $collectData['collect']['validation_data'],
				'errors'		=> false,
			];
		} else {
			$collectData['json_response'] = [
				'status'		=> fale,
				'data'			=> $collectData['collect']['validation_data'],
				'errors'		=> $this->error_msg,
			];
		}
		$this->load->view('edit/edit-cert-validation/cert-validation.php', $collectData);
	}
	
}