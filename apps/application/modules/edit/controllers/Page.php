<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Page extends MY_Controller {
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
	
	
	public function index() {
		$collectData = [
			'page'			=> 'edit-page-index',
			'collect'		=> [],
			'userdata'		=> $this->userdata,
		];
		
		return $this->purpose('draft');
	}
	
	public function edit(String $pg_type = 'form', String $page_id = '0') {
		$collectData = [
			'page'			=> 'edit-page-edit',
			'collect'		=> [],
			'userdata'		=> $this->userdata,
			'pg_type'		=> (is_string($pg_type) ? strtolower($pg_type) : 'form'),
			'page_id'		=> (is_numeric($page_id) ? (int)$page_id : 0),
		];
		if (!in_array($collectData['pg_type'], [
			'form',
			'action'
		])) {
			$this->error = true;
			$this->error_msg[] = "Page type not in allowed string.";
		}
		if (!$this->error) {
			try {
				$collectData['collect']['single_page'] = $this->mod_page->get_single_page_by_page_id($collectData['page_id']);
				
				if (!isset($collectData['collect']['single_page']->page_id) || !isset($collectData['collect']['single_page']->page_purpose)) {
					$this->error = true;
					$this->error_msg[] = "Page purpose not exists on database.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get single-page by page-id with exception: {$ex->getMessage()}.";
			}
		}
		
		if ($collectData['pg_type'] === 'action') {
			if (!$this->error) {
				# Load Codeigniter helpers
				$this->load->helper('security');
				$this->load->library('form_validation');
				if (!$this->error) {
					$this->form_validation->set_rules('page_content', 'Page Content', 'required|min_length[1]');
					if ($this->form_validation->run() == FALSE) {
						$this->error = true;
						$this->error_msg[] = validation_errors();
					}
				}
			}
			if (!$this->error) {
				$collectData['collect']['input_params'] = [
					'page_content'		=> $this->input->post('page_content'),
				];
				if (!is_string($collectData['collect']['input_params']['page_content'])) {
					$this->error = true;
					$this->error_msg[] = "Page content should be in string datatype.";
				}
			}
			if (!$this->error) {
				$collectData['collect']['page_uupdate_params'] = [
					'page_content'		=> $collectData['collect']['input_params']['page_content'],
				];
				try {
					$collectData['collect']['edit_page_response'] = $this->mod_page->set_single_page((int)$collectData['collect']['single_page']->page_id, $collectData['collect']['page_uupdate_params']);
					
					if (!isset($collectData['collect']['edit_page_response']['status'])) {
						$this->error = true;
						$this->error_msg[] = "Not have status from edit page response.";
						$this->error_msg[] = $collectData['collect']['edit_page_response'];
					}
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot edit page content with exception: {$ex->getMessage()}.";
				}
			}
			// If Success
			if (!$this->error) {
				redirect(base_url("edit/page/purpose/{$collectData['collect']['single_page']->page_purpose}"));
				exit;
			}
		}
		
		
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'		=> true,
				'errors'		=> false,
			];
		} else {
			$collectData['json_response'] = [
				'status'		=> false,
				'errors'		=> $this->error_msg,
			];
		}
		$this->output->set_content_type('application/json');
		$this->output->set_output(json_encode($collectData['json_response']));
	}
	
	
	public function purpose(String $page_purpose = 'draft', String $firebase_id = '') {
		$collectData = [
			'page'			=> 'edit-page-purpose',
			'collect'		=> [],
			'page_purpose'	=> strtolower($page_purpose),
			'userdata'		=> $this->userdata,
			'page_title'	=> 'Page Purposes',
			'firebase_id'	=> 0,
		];
		if (empty($collectData['page_purpose'])) {
			$collectData['page_purpose'] = 'draft';
		}
		if (!empty($firebase_id)) {
			if (is_numeric($firebase_id)) {
				$collectData['firebase_id'] = (int)$firebase_id;
			}
		}
		try {
			$collectData['collect']['single_page'] = $this->mod_page->get_single_page_purpose($collectData['page_purpose'], $collectData['firebase_id']);
			if (!isset($collectData['collect']['single_page']->page_id) || !isset($collectData['collect']['single_page']->page_purpose)) {
				$this->error = true;
				$this->error_msg[] = "Not have page by page-purpose.";
			}
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot get single page purpose with exception: {$ex->getMessage()}.";
		}
		if (!$this->error) {
			if (!isset($collectData['collect']['single_page']->page_content)) {
				$this->error = true;
				$this->error_msg[] = "Not have page-content.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['single_page']->page_content_string = htmlspecialchars($collectData['collect']['single_page']->page_content,  ENT_QUOTES | ENT_HTML5);
		}
		
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'		=> true,
				'errors'		=> false,
			];
			$collectData['page_title'] .= sprintf(':: %s', $collectData['collect']['single_page']->page_purpose);
		} else {
			$collectData['page'] = 'edit-page-error';
			$collectData['json_response'] = [
				'status'		=> false,
				'errors'		=> $this->error_msg,
			];
		}
		$this->load->view('page/page.php', $collectData);
	}
}










































