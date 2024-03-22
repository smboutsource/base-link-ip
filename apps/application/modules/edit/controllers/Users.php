<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Users extends MY_Controller {
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
		// Model Users
		$this->load->model('edit/Model_users', 'mod_users');
	}
	
	
	public function index(String $pg = '0', String $pg_content = 'html') {
		$collectData = [
			'page'			=> 'edit-users-index',
			'collect'		=> [],
			'userdata'		=> $this->userdata,
			'page_title'	=> 'App Userdata',
			'pg'			=> (is_numeric($pg) ? (int)$pg : 0),
			'pg_content'	=> strtolower($pg_content),
		];
		
		if (!in_array($collectData['pg_content'], [
			'html',
			'json'
		])) {
			$collectData['pg_content'] = 'html';
		}
		
		if ($collectData['pg'] > 0) {
			$collectData['pg'] = (int)$collectData['pg'];
		} else {
			$collectData['pg'] = 0;
		}
		$collectData['collect']['per_page'] = $this->base_auth['app_userdata_offset']['limit'];
		if (!isset($collectData['userdata']->user_email) || !isset($collectData['userdata']->user_email)) {
			$this->error = true;
			$this->error_msg[] = "Not have userdata session user-email or user-role.";
		}
		// Count App Userdata
		if (!$this->error) {
			try {
				$collectData['collect']['count_userdatas'] = $this->mod_users->get_count_app_userdatas();
				if (!isset($collectData['collect']['count_userdatas']->count_value)) {
					$this->error = true;
					$this->error_msg[] = "Not have count of app userdata.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot count app userdatas.";
			}
		}
		
		// Data App Userdata
		if (!$this->error) {
			try {
				$collectData['collect']['count_userdatas']->count_value = (int)$collectData['collect']['count_userdatas']->count_value;
				$collectData['collect']['app_userdatas'] = $this->mod_users->get_app_userdatas($collectData['collect']['count_userdatas']->count_value, $collectData['pg']);
				if (!is_array($collectData['collect']['app_userdatas'])) {
					$this->error = true;
					$this->error_msg[] = "Result of app-userdatas should be in array datatype.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get app userdatas with exception: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			if (empty($collectData['collect']['app_userdatas'])) {
				$this->error = true;
				$this->error_msg[] = "Empty app-userdatas.";
			}
		}
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'			=> true,
				'errors'			=> false,
				'data'				=> $collectData['collect']['app_userdatas'],
				'recordsTotal' 		=> $collectData['collect']['count_userdatas']->count_value,
				'recordsFiltered'	=> $collectData['collect']['count_userdatas']->count_value,
			];
		} else {
			$collectData['json_response'] = [
				'status'			=> false,
				'errors'			=> $this->error_msg,
				'data'				=> null,
				'recordsTotal'		=> 0,
				'recordsFiltered'	=> 0,
			];
			$collectData['page'] = 'edit-users-error';
		}
		
		
		if ($collectData['pg_content'] === 'json') {
			$this->output->set_content_type('application/json');
			$this->output->set_output(json_encode($collectData['json_response']));
		} else {
			$this->load->view('page/page.php', $collectData);
		}
	}
	public function get() {
		try {
			$response = $this->mod_users->get_datatables();
			
			$this->output->set_content_type('application/json');
			$this->output->set_output($response);
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function get_single_userdata(String $user_id = '') {
		$collectData = [
			'page'			=> 'edit-users-edit',
			'collect'		=> [],
			'userdata'		=> $this->userdata,
			'page_title'	=> 'App Userdata',
			'user_id'		=> (is_numeric($user_id) ? (int)$user_id : 0),
		];
		$collectData['collect']['per_page'] = Instance_config::$env_apc['env']['userdata.' . Instance_config::$env_group['env_env'] . '.limit_start'];
		
		if ($collectData['user_id'] < 1) {
			$this->error = true;
			$this->error_msg[] = "Input user-id cannot be zero value.";
		}
		if (!isset($collectData['userdata']->user_id) || !isset($collectData['userdata']->user_role)) {
			$this->error = true;
			$this->error_msg[] = "Logged in users not have userdata role.";
		}
		if (!$this->error) {
			if (!in_array($collectData['userdata']->user_role, $this->base_auth['userdata_roles']['admin'])) {
				$this->error = true;
				$this->error_msg[] = "Logged-in app userdata role not allowed to edit app userdatas.";
			}
		}
		if (!$this->error) {
			try {
				$collectData['collect']['app_userdata'] = $this->mod_users->get_single_userdata_by_userid($collectData['user_id']);
				if (!isset($collectData['collect']['app_userdata']->user_id)) {
					$this->error = true;
					$this->error_msg[] = "App userdata not exists.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get single app userdata with exception: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['app_userdata']->app_user_roles = $this->base_auth['app_userdaata_roles'];
		}
		
		if (!$this->error) {
			$this->load->view('edit/edit-users-modals/edit-userdata.php', $collectData);
		} else {
			$collectData['json_response'] = [
				'status'		=> false,
				'data'			=> null,
				'errors'		=> $this->error_msg
			];
			$this->load->view('page/page-errors/error-modal.php', $collectData);
		}
	}
	public function add(String $page_type = 'form') {
		$collectData = [
			'page'			=> 'edit-users-edit',
			'collect'		=> [],
			'userdata'		=> $this->userdata,
			'page_title'	=> 'App Userdata',
			'page_type'		=> strtolower($page_type)
		];
		if (!in_array($collectData['page_type'], [
			'form',
			'action'
		])) {
			$collectData['page_type'] = 'form';
		}
		if (!isset($collectData['userdata']->user_id) || !isset($collectData['userdata']->user_role)) {
			$this->error = true;
			$this->error_msg[] = "Logged in users not have userdata role.";
		}
		if (!$this->error) {
			if (!in_array($collectData['userdata']->user_role, $this->base_auth['userdata_roles']['admin'])) {
				$this->error = true;
				$this->error_msg[] = "Logged-in app userdata role not allowed to add app userdatas.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['app_userdaata_roles'] = $this->base_auth['app_userdaata_roles'];
		}
		//--------------------------
		// Action
		//--------------------------
		if ($collectData['page_type'] === 'action') {
			# Load Codeigniter helpers
			$this->load->helper('security');
			$this->load->library('form_validation');
			if (!$this->error) {
				$this->form_validation->set_rules('user_email', 'Account Email', 'required|max_length[128]|trim|xss_clean');
				$this->form_validation->set_rules('user_role', 'Account Role', 'required|numeric|max_length[1]');
				$this->form_validation->set_rules('user_active', 'Account Active', 'required|max_length[1]');
				if ($this->form_validation->run() == FALSE) {
					$this->error = true;
					$this->error_msg[] = validation_errors();
				}
			}
			if (!$this->error) {
				$collectData['collect']['input_params'] = [
					'user_email'		=> $this->input->post('user_email'),
					'user_role'			=> $this->input->post('user_role'),
					'user_active'		=> $this->input->post('user_active'),
				];
				if (!filter_var($collectData['collect']['input_params']['user_email'], FILTER_VALIDATE_EMAIL)) {
					$this->error = true;
					$this->error_msg[] = "Not a valid email format.";
				}
			}
			if (!$this->error) {
				if (!in_array($collectData['collect']['input_params']['user_active'], [
					'Y',
					'N'
				])) {
					$this->error = true;
					$this->error_msg[] = "Only allow Y or N for user active.";
				}
			}
			if (!$this->error) {
				$collectData['collect']['insert_params'] = [
					'user_email'		=> trim($collectData['collect']['input_params']['user_email']),
					'user_role'			=> (int)$collectData['collect']['input_params']['user_role'],
					'user_active'		=> strtoupper($collectData['collect']['input_params']['user_active']),
				];
				try {
					$collectData['collect']['single_userdata'] = $this->mod_users->get_single_userdata_by_email($collectData['collect']['insert_params']['user_email']);
					if (isset($collectData['collect']['single_userdata']->user_id)) {
						$this->error = true;
						$this->error_msg[] = "App userdata already exists.";
					}
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot check whatever app userdata already exists or not.";
				}
			}
			if (!$this->error) {
				try {
					$collectData['collect']['insert_response'] = $this->mod_users->insert_app_userdata($collectData['collect']['insert_params']);
					if (!$collectData['collect']['insert_response']) {
						$this->error = true;
						$this->error_msg[] = "Failed insert new app userdata.";
					}
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot insert new app userdata with exception: {$ex->getMessage()}.";
				}
			}
		}
		//--------------------------
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'		=> true,
				'data'			=> $collectData['collect']['app_userdaata_roles'],
				'errors'		=> false
			];
			if ($collectData['page_type'] === 'action') {
				$collectData['json_response']['inserted_id'] = $collectData['collect']['insert_response'];
				
				$this->output->set_content_type('application/json');
				$this->output->set_output(json_encode($collectData['json_response']));
			} else {
				$this->load->view('edit/edit-users-modals/add-userdata.php', $collectData);
			}
		} else {
			$collectData['json_response'] = [
				'status'		=> false,
				'data'			=> null,
				'errors'		=> $this->error_msg
			];
			if ($collectData['page_type'] === 'action') {
				$this->output->set_content_type('application/json');
				$this->output->set_output(json_encode($collectData['json_response']));
			} else {
				$this->load->view('page/page-errors/error-modal.php', $collectData);
			}
		}
	}
	public function edit(String $page_type = 'change', String $user_id = '0') {
		$collectData = [
			'page'			=> 'edit-users-edit',
			'collect'		=> [],
			'userdata'		=> $this->userdata,
			'page_title'	=> 'App Userdata',
			'user_id'		=> (is_numeric($user_id) ? (int)$user_id : 0),
			'page_type'		=> (is_string($page_type) ? strtolower($page_type) : 'change'),
		];
		if (!in_array($collectData['page_type'], [
			'change',
			'delete'
		])) {
			$collectData['page_type'] = 'change';
		}
		if (!isset($collectData['userdata']->user_id) || !isset($collectData['userdata']->user_role)) {
			$this->error = true;
			$this->error_msg[] = "Logged in users not have userdata role.";
		}
		if (!$this->error) {
			if (!in_array($collectData['userdata']->user_role, $this->base_auth['userdata_roles']['admin'])) {
				$this->error = true;
				$this->error_msg[] = "Logged-in app userdata role not allowed to add app userdatas.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['app_userdaata_roles'] = $this->base_auth['app_userdaata_roles'];
		}
		if (!$this->error) {
			if (!in_array($collectData['userdata']->user_role, $this->base_auth['userdata_roles']['admin'])) {
				$this->error = true;
				$this->error_msg[] = "Logged-in app userdata role not allowed to edit app userdatas.";
			}
		}
		if (!$this->error) {
			try {
				$collectData['collect']['app_userdata'] = $this->mod_users->get_single_userdata_by_userid($collectData['user_id']);
				if (!isset($collectData['collect']['app_userdata']->user_id)) {
					$this->error = true;
					$this->error_msg[] = "App userdata not exists.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get single app userdata with exception: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			if ($collectData['userdata']->user_id == $collectData['collect']['app_userdata']->user_id) {
				$this->error = true;
				$this->error_msg[] = "Dump decison to {$collectData['page_type']} self account.";
			}
		}
		//--------------------------
		// Action
		//--------------------------
		if ($collectData['page_type'] === 'delete') {
			if (!$this->error) {
				try {
					$collectData['collect']['delete_response'] = $this->mod_users->delete_app_userdata($collectData['collect']['app_userdata']);
					
					if (!isset($collectData['collect']['delete_response']['status'])) {
						$this->error = true;
						$this->error_msg[] = "Not have delete response status.";
					}
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot delete app userdata with exception: {$ex->Message()}.";
				}
			}
			// Is True or False
			if (!$this->error) {
				if ($collectData['collect']['delete_response']['status'] !== TRUE) {
					$this->error = true;
					$this->error_msg[] = "Response status not true while delete app userdata.";
				}
			}
		} else {
			# Load Codeigniter helpers
			$this->load->helper('security');
			$this->load->library('form_validation');
			if (!$this->error) {
				$this->form_validation->set_rules('user_role', 'Account Role', 'required|numeric|max_length[10]');
				$this->form_validation->set_rules('user_active', 'Account Active', 'required|max_length[1]');
				if ($this->form_validation->run() == FALSE) {
					$this->error = true;
					$this->error_msg[] = validation_errors();
				}
			}
			if (!$this->error) {
				$collectData['collect']['input_params'] = [
					'user_role'			=> $this->input->post('user_role'),
					'user_active'		=> $this->input->post('user_active'),
				];
			}
			if (!$this->error) {
				if (!in_array($collectData['collect']['input_params']['user_active'], [
					'Y',
					'N'
				])) {
					$this->error = true;
					$this->error_msg[] = "Only allow Y or N for user active.";
				}
			}
			if (!$this->error) {
				$collectData['collect']['update_params'] = [
					'user_role'			=> (int)$collectData['collect']['input_params']['user_role'],
					'user_active'		=> strtoupper($collectData['collect']['input_params']['user_active']),
				];
			}
			if (!$this->error) {
				try {
					$collectData['collect']['update_response'] = $this->mod_users->set_app_userdata($collectData['collect']['app_userdata']->user_id, $collectData['collect']['update_params']);
					if (!isset($collectData['collect']['update_response']['status'])) {
						$this->error = true;
						$this->error_msg[] = "Failed update app userdata.";
					}
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot update app userdata with exception: {$ex->getMessage()}.";
				}
			}
			// Is True or False
			if (!$this->error) {
				if ($collectData['collect']['update_response']['status'] !== TRUE) {
					$this->error = true;
					$this->error_msg[] = "Response status not true.";
				}
			}
		}
		//--------------------------
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'		=> true,
				'data'			=> $collectData['collect']['app_userdaata_roles'],
				'errors'		=> false
			];
			if ($collectData['page_type'] === 'delete') {
				$collectData['json_response']['data'] = $collectData['collect']['delete_response'];
			} else {
				$collectData['json_response']['data'] = $collectData['collect']['update_response'];
			}
		} else {
			$collectData['json_response'] = [
				'status'		=> false,
				'data'			=> null,
				'errors'		=> $this->error_msg
			];
		}
		$this->output->set_content_type('application/json');
		$this->output->set_output(json_encode($collectData['json_response']));
	}
	
	
	
	public function scripts(String $path_dir = 'edit-users-javascripts', String $path_file = 'users-index.js') {
		$collectData = [
			'page'			=> 'edit-users-script',
			'collect'		=> [],
		];
		$collectData['collect']['paths'] = [
			'dir'		=> strtolower($path_dir),
			'file'		=> strtolower($path_file),
		];
		$collectData['collect']['paths']['dir'] = str_replace('_', '-', $collectData['collect']['paths']['dir']);
		$collectData['collect']['paths']['file'] = str_replace('_', '-', $collectData['collect']['paths']['file']);
		$collectData['collect']['paths']['paths'] = [
			'load'		=> sprintf("edit/%s/%s", 
				$collectData['collect']['paths']['dir'],
				$collectData['collect']['paths']['file']
			),
			'real'		=> sprintf("views/%s/%s", 
				$collectData['collect']['paths']['dir'],
				$collectData['collect']['paths']['file']
			),
		];
		$collectData['collect']['paths']['paths']['script'] = (dirname(__DIR__) . DIRECTORY_SEPARATOR . $collectData['collect']['paths']['paths']['real']);
		if (!file_exists($collectData['collect']['paths']['paths']['script'])) {
			$this->error = true;
			$this->error_msg[] = "Script login not exists.";
			$this->error_msg[] = $collectData['collect']['paths']['paths']['script'];
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
		
		$this->output->set_content_type('text/javascript');
		if (!$this->error) {
			$this->load->view($collectData['collect']['paths']['paths']['load'], $collectData);
		} else {
			$this->output->set_output(json_encode($collectData['collect']['json_response']));
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
	