<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Login extends MY_Controller {
	private $base_auth;
	private $error = false, $error_msg = [];
	function __construct() {
		parent::__construct();
		
		$this->load->model('auth/Model_auth', 'mod_auth');
		$this->load->model('edit/Model_page', 'mod_page');
		
		$this->base_auth = $this->mod_auth->get_base_auth();
		# Load Codeigniter helpers
		$this->load->helper('security');
		$this->load->library('form_validation');
	}
	
	
	public function index() {
		$collectData = [
			'page'			=> 'auth-login-index',
			'collect'		=> [],
		];
		try {
			$collectData['userdata'] = $this->mod_auth->start_userdata();
			if (isset($collectData['userdata']->user_id)) {
				$this->error = true;
				$this->error_msg[] = "User already logged-in.";
			}
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot validate if user already logged-in or not with exception: {$ex->getMessage()}.";
		}
		if (!$this->error) {
			$this->form_validation->set_rules('account_email', 'Account Email', 'required|max_length[128]|trim|xss_clean');
			$this->form_validation->set_rules('account_password', 'Account Password', 'required|max_length[512]');
			if ($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = validation_errors();
			}
		}
		if (!$this->error) {
			$colectData['collect']['input_params'] = [
				'account_email'		=> $this->input->post('account_email'),
				'account_password'	=> $this->input->post('account_password'),
			];
			if (!filter_var($colectData['collect']['input_params']['account_email'], FILTER_VALIDATE_EMAIL)) {
				$this->error = true;
				$this->error_msg[] = "Not using a valid email format.";
			}
		}
		if (!$this->error) {
			if (empty($colectData['collect']['input_params']['account_email']) || empty($colectData['collect']['input_params']['account_password'])) {
				$this->error = true;
				$this->error_msg[] = "Account email or password cannot be empty.";
			}
			if (!is_string($colectData['collect']['input_params']['account_email']) || !is_string($colectData['collect']['input_params']['account_password'])) {
				$this->error = true;
				$this->error_msg[] = "Account email and password should be in string datatype.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['login_params'] = [
				'user_email'		=> $colectData['collect']['input_params']['account_email'],
				'user_password'		=> $colectData['collect']['input_params']['account_password'],
			];
			try {
				$collectData['collect']['login_response'] = $this->mod_auth->login_auth_center_with_username_and_password($collectData['collect']['login_params']);
				if (!isset($collectData['collect']['login_response']['status'])) {
					$this->error = true;
					$this->error_msg[] = "Not have status from login model.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot make login to auth center with exception: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			if ($collectData['collect']['login_response']['status'] !== TRUE) {
				$this->error = true;
				if (isset($collectData['collect']['login_response']['errors'])) {
					switch (gettype($collectData['collect']['login_response']['errors'])) {
						case 'array':
						case 'object':
							foreach ($collectData['collect']['login_response']['errors'] as $error_msg) {
								$this->error_msg[] = $error_msg;
							}
						break;
						case 'string':
							$this->error_msg[] = $collectData['collect']['login_response']['errors'];
						break;
						default:
							$this->error_msg[] = "Unknown errors response.";
						break;
					}
				} else {
					$this->error_msg[] = "Response login from auth-center not success.";
				}
			}
		}
		
		if (!$this->error) {
			$collectData['collect']['json_response'] = [
				'status'		=> true,
				'data'			=> $collectData['collect']['login_response'],
				'errors'		=> false,
			];
		} else {
			$collectData['collect']['json_response'] = [
				'status'		=> false,
				'data'			=> null,
				'errors'		=> $this->error_msg,
			];
		}
		
		$this->output->set_content_type('application/json');
		$this->output->set_output(json_encode($collectData['collect']['json_response']));
	}
	
	
	public function form() {
		$collectData = [
			'page'			=> 'auth-login-form',
			'collect'		=> [],
		];
		try {
			$collectData['userdata'] = $this->mod_auth->start_userdata();
			if (isset($collectData['userdata']->user_id)) {
				$this->error = true;
				$this->error_msg[] = "User already logged-in.";
			}
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot validate if user already logged-in or not with exception: {$ex->getMessage()}.";
		}
		
		if (!$this->error) {
			$this->load->view('auth/login/index.php', $collectData);
		} else {
			redirect(base_url('dashboard/dashboard'));
		}
	}
	
	
	
	
	
	
	public function scripts(String $path_dir = 'login-javascripts', String $path_file = 'login.js') {
		$collectData = [
			'page'			=> 'auth-login-script',
			'collect'		=> [],
		];
		$collectData['collect']['paths'] = [
			'dir'		=> strtolower($path_dir),
			'file'		=> strtolower($path_file),
		];
		$collectData['collect']['paths']['dir'] = str_replace('_', '-', $collectData['collect']['paths']['dir']);
		$collectData['collect']['paths']['file'] = str_replace('_', '-', $collectData['collect']['paths']['file']);
		$collectData['collect']['paths']['paths'] = [
			'load'		=> sprintf("auth/%s/%s", 
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
			try {
				$collectData['userdata'] = $this->mod_auth->start_userdata();
				if (isset($collectData['userdata']->user_id)) {
					$this->error = true;
					$this->error_msg[] = "User already logged-in.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot validate if user already logged-in or not with exception: {$ex->getMessage()}.";
			}
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