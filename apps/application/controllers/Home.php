<?php
defined('BASEPATH') OR exit('No direct script access allowed: Insert');

class Home extends MY_Controller {
	function __construct() {
		parent::__construct();

	}
	
	
	
	
	public function index() {
		$json_response = [
			'status'			=> true,
			'errors'			=> false,
			'message'			=> 'Home'
		];
		$this->output->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($json_response));
	}
	public function notfound() {
		$json_response = [
			'status'			=> false,
			'errors'			=> [
				"Page Not Found",
			],
		];
		$this->output->set_content_type('application/json');
		$this->output->set_status_header(404);
		$this->output->set_output(json_encode($json_response));
	}
	
}
