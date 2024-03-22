<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Server extends MY_Controller {
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
		// Model Cert
		$this->load->model('edit/Model_cert', 'mod_cert');
	}
	
	// Get List ServerData List
	public function get() {
		try {
			$response = $this->mod_cert->get_datatables();
			
			$this->output->set_content_type('application/json');
			$this->output->set_output($response);
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function cert(String $pg_action = 'form', String $server_purpose = 'validation') {
		$collectData = [
			'page'						=> 'edit-server-cert',
			'collect'					=> [],
			'userdata'				=> $this->userdata,
			'page_title'			=> 'SSL Cert',
			'page_action'			=> (is_string($pg_action) ? strtolower($pg_action) : 'form'),
			'server_purpose'	=> (is_string($server_purpose) ? strtolower($server_purpose) : 'validation'),
		];
		$collectData['collect']['per_page'] = Instance_config::$env_apc['env']['userdata.' . Instance_config::$env_group['env_env'] . '.limit_start'];
		if (!in_array($collectData['page_action'], [
			'form',
			'action',
		])) {
			$collectData['page_action'] = 'form';
		}
		if (!in_array($collectData['server_purpose'], [
			'validation',
			'create',
			'certificate'
		])) {
			$this->error = true;
			$this->error_msg[] = "Server purpose not allowed.";
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
			try {
				$collectData['collect']['validation_data'] = $this->mod_cert->get_single_server_data($collectData['server_purpose']);
				if (!isset($collectData['collect']['validation_data']->server_site_purpose) || !isset($collectData['collect']['validation_data']->server_site_content)) {
					$this->error = true;
					$this->error_msg[] = "Not have server_site_content after fetching from model.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get validation data with exception: {$ex->getMessage()}.";
			}
		}
		//--------------------------
		// Action
		//--------------------------
		if ($collectData['page_action'] === 'action') {
			# Load Codeigniter helpers
			$this->load->helper('security');
			$this->load->library('form_validation');
			if (!$this->error) {
				$this->form_validation->set_rules('server_site_purpose', 'Server Purpose', 'required|max_length[128]|trim|xss_clean');
				$this->form_validation->set_rules('server_site_content', 'Purpose Content', 'required');
				if ($this->form_validation->run() == FALSE) {
					$this->error = true;
					$this->error_msg[] = validation_errors();
				}
			}
			if (!$this->error) {
				$collectData['collect']['input_params'] = [
					'server_site_purpose'		=> $this->input->post('server_site_purpose'),
					'server_site_content'		=> $this->input->post('server_site_content'),
				];
				if ($collectData['collect']['input_params']['server_site_purpose'] !== $collectData['collect']['validation_data']->server_site_purpose) {
					$this->error = true;
					$this->error_msg[] = "Server purpose not as expected defined purposes.";
				}
			}
			if (!$this->error) {
				if (empty($collectData['collect']['input_params']['server_site_content'])) {
					$this->error = true;
					$this->error_msg[] = "Empty server site content not allowed.";
				}
			}
			if (!$this->error) {
				try {
					$collectData['collect']['server_update_response'] = $this->mod_cert->set_server_purpose_data($collectData['collect']['validation_data']->server_site_purpose, [
						'server_site_content'		=> $collectData['collect']['input_params']['server_site_content']
					]);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot update server data wirh exception: {$ex->getMessage()}.";
				}
			}
		}
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'		=> true,
				'errors'		=> false,
				'data'			=> $collectData['collect']['validation_data']
			];
		} else {
			$collectData['json_response'] = [
				'status'		=> false,
				'errors'		=> $this->error_msg,
				'data'			=> null
			];
			$collectData['page'] = 'dashboard-error';
		}
		if ($collectData['page_action'] === 'action') {
			$this->output->set_content_type('application/json');
			$this->output->set_output(json_encode($collectData['json_response']));
		} else {
			$this->load->view('page/page.php', $collectData);
		}
	}
	
	public function server_cert(String $server_purpose = 'validation') {
		$collectData = [
			'page'						=> 'edit-server-cert',
			'collect'					=> [],
			'userdata'				=> $this->userdata,
			'page_title'			=> 'SSL Cert',
			'server_purpose'	=> (is_string($server_purpose) ? strtolower($server_purpose) : 'validation'),
		];
		$collectData['collect']['per_page'] = Instance_config::$env_apc['env']['userdata.' . Instance_config::$env_group['env_env'] . '.limit_start'];
		if (!in_array($collectData['server_purpose'], [
			'validation',
			'create'
		])) {
			$this->error = true;
			$this->error_msg[] = "Server purpose not allowed.";
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
			try {
				$collectData['collect']['validation_data'] = $this->mod_cert->get_single_server_data($collectData['server_purpose']);
				if (!isset($collectData['collect']['validation_data']->server_site_purpose) || !isset($collectData['collect']['validation_data']->server_site_content)) {
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
				'errors'		=> false,
				'data'			=> $collectData['collect']['validation_data']
			];
			$this->load->view('edit/edit-cert-modals/cert-edit.php', $collectData);
		} else {
			$collectData['json_response'] = [
				'status'		=> false,
				'errors'		=> $this->error_msg,
				'data'			=> null
			];
			$collectData['page'] = 'dashboard-error';
			$this->load->view('page/page-errors/error-modal.php', $collectData);
		}
	}
	
	
	
	// PEM File Upload
	public function pem(String $page_type = 'form') {
		$collectData = [
			'page'				=> 'edit-server-pem',
			'collect'			=> [],
			'userdata'			=> $this->userdata,
			'page_title'		=> 'SSL Pem File',
			'page_type'			=> (is_string($page_type) ? strtolower($page_type) : 'form'),
		];
		$collectData['collect']['per_page'] = Instance_config::$env_apc['env']['userdata.' . Instance_config::$env_group['env_env'] . '.limit_start'];
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
		//----------------
		if (!$this->error) {
			$collectData['collect']['pem_paths'] = [
				'basepath'		=> (dirname(Instance_config::$envpath) . DIRECTORY_SEPARATOR . Instance_config::$env_apc['env']['serverdata.' . Instance_config::$env_group['env_env'] . '.certificate.path'] . DIRECTORY_SEPARATOR . 'ssl'),
			];
			if (!is_dir($collectData['collect']['pem_paths']['basepath'])) {
				$this->error = true;
				$this->error_msg[] = "Certificate pem basepath should be a directory.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['pem_paths']['fullchain'] = ($collectData['collect']['pem_paths']['basepath'] . DIRECTORY_SEPARATOR . Instance_config::$env_apc['env']['serverdata.' . Instance_config::$env_group['env_env'] . '.certificate.fullchain']);
			$collectData['collect']['pem_paths']['private'] = ($collectData['collect']['pem_paths']['basepath'] . DIRECTORY_SEPARATOR . Instance_config::$env_apc['env']['serverdata.' . Instance_config::$env_group['env_env'] . '.certificate.private']);
			if (!file_exists($collectData['collect']['pem_paths']['fullchain'])) {
				$this->error = true;
				$this->error_msg[] = "Certificate pem fullchain not exists.";
			}
			if (!file_exists($collectData['collect']['pem_paths']['private'])) {
				$this->error = true;
				$this->error_msg[] = "Certificate pem private key not exists.";
			}
		}
		//
		// Action Upload Pem Certificate
		//
		if ($collectData['page_type'] === 'action') {
			# Load Codeigniter helpers
			$this->load->helper('security');
			$this->load->library('form_validation');
			if (!$this->error) {
				try {
					$collectData['collect']['pem_paths']['serverdata_purposes_data'] = $this->mod_cert->get_single_server_data('certificate');
					if (!isset($collectData['collect']['pem_paths']['serverdata_purposes_data']->server_site_purpose)) {
						$this->error = true;
						$this->error_msg[] = "Not have serverdata pem certificate by purpose.";
					}
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot get serverdata purpose for pem-certificate with exception: {$ex->getMessage()}.";
				}
			}
			/*
			if (!$this->error) {
				$this->form_validation->set_rules('pem_fullchain', 'PEM Certificate Fullchain', 'required|sanitize_filename');
				$this->form_validation->set_rules('pem_private', 'PEM Certificate Private Key', 'required|sanitize_filename');
				if ($this->form_validation->run() == FALSE) {
					$this->error = true;
					$this->error_msg[] = validation_errors();
				}
			*/
			if (!$this->error) {
				if (!isset($_FILES['pem_fullchain']) || $_FILES['pem_fullchain']['size'] == 0) {
					$this->error = true;
					$this->error_msg[] = "Required pem fullchain.";
				}
				if (!isset($_FILES['pem_private']) || $_FILES['pem_private']['size'] == 0) {
					$this->error = true;
					$this->error_msg[] = "Required pem private key.";
				}
			}
			if (!$this->error) {
				if (($_FILES['pem_fullchain']['size'] != 0) && ($_FILES['pem_private']['size'] != 0)) {
					$finfo = new finfo(FILEINFO_MIME_TYPE);
					
					$collectData['collect']['finfo'] = [
						'pem_fullchain'		=> $finfo->file($_FILES['pem_fullchain']['tmp_name']),
						'pem_private'		=> $finfo->file($_FILES['pem_private']['tmp_name']),
					];
				} else {
					$this->error = true;
					$this->error_msg[] = "Uploaded files not have size.";
				}
			}
			if (!$this->error) {
				$collectData['collect']['allowed_mime_types'] = $this->mod_cert->get_certificate_mimetypes();
				if (!in_array($collectData['collect']['finfo']['pem_fullchain'], $collectData['collect']['allowed_mime_types'])) {
					$this->error = true;
					$this->error_msg[] = "Uploaded fullchain cert not allowed mime type.";
				}
				if (!in_array($collectData['collect']['finfo']['pem_private'], $collectData['collect']['allowed_mime_types'])) {
					$this->error = true;
					$this->error_msg[] = "Uploaded private key not allowed mime type.";
				}
			}
			// Do Upload
			if (!$this->error) {
				$collectData['collect']['upload_datas'] = [];
				$collectData['collect']['upload_config'] = $this->mod_cert->get_certificate_upload_config($collectData['collect']['pem_paths']['basepath'], $collectData['collect']['allowed_mime_types']);
				if (!is_array($collectData['collect']['upload_config'])) {
					$this->error = true;
					$this->error_msg[] = "Upload config should be in array datatype.";
				} else {
					$this->load->library('upload', $collectData['collect']['upload_config']);
					$this->load->initialize($collectData['collect']['upload_config']);
					
					foreach ($collectData['collect']['finfo'] as $info_key => $info_val) {
						/*
						 if ($this->mod_cert->pem_certificate_verify($_FILES, $info_key) === TRUE) {
							if ($this->upload->do_upload($info_key)) {
								$collectData['collect']['upload_datas'][$info_key] = $this->upload->data();
							} else {
								$this->error = true;
								$this->error_msg[] = "Error while uploading {$info_key} data.";
								$this->error_msg[] = $this->upload->display_errors();
							}
						} else {
							$this->error = true;
							$this->error_msg[] = "Validate file {$info_key} data return false.";
						}
						 */
						if ($this->upload->do_upload($info_key)) {
							$collectData['collect']['upload_datas'][$info_key] = $this->upload->data();
						} else {
							$this->error = true;
							$this->error_msg[] = "Error while uploading {$info_key} data.";
							$this->error_msg[] = $this->upload->display_errors();
																									                               }
					}
				}
			}
			if (!$this->error) {
				if (isset($collectData['collect']['upload_datas']['pem_fullchain']['full_path']) && isset($collectData['collect']['upload_datas']['pem_private']['full_path'])) {
					try {
						$collectData['collect']['pem_paths']['verify_data'] = $this->mod_cert->pem_certificate_verify([
							'fullchain'		=> $collectData['collect']['upload_datas']['pem_fullchain']['full_path'],
							'private'		=> $collectData['collect']['upload_datas']['pem_private']['full_path']
						]);
						if (!isset($collectData['collect']['pem_paths']['verify_data']['check_private_key'])) {
							$this->error = true;
							$this->error_msg[] = "Not have expected verify certificate key result as check_private_key.";
						}
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Cannot make pem certificate verify with exception: {$ex->getMessage()}.";
					}
				}
			}
			if (!$this->error) {
				if ($collectData['collect']['pem_paths']['verify_data']['check_private_key'] !== TRUE) {
					$this->error = true;
					$this->error_msg[] = "Invalid certificate chain and key, please using a valid certificate for your server.";
					unlink($collectData['collect']['upload_datas']['pem_fullchain']['full_path']);
					unlink($collectData['collect']['upload_datas']['pem_private']['full_path']);
				}
			}
			//
			// If Success Verify Certificate [rename_to_file]
			//
			if (!$this->error) {
				try {
					$collectData['collect']['rename_certificate_files'] = [
						'fullchain'		=> rename($collectData['collect']['upload_datas']['pem_fullchain']['full_path'], $collectData['collect']['pem_paths']['fullchain']),
						'private'			=> rename($collectData['collect']['upload_datas']['pem_private']['full_path'], $collectData['collect']['pem_paths']['private']),
					];
					if ($collectData['collect']['rename_certificate_files']['fullchain'] !== TRUE) {
						$this->error = true;
						$this->error_msg[] = "Renaming certificate [fullchain] into current certificate have problem, not all return true.";
						$this->error_msg[] = unlink($collectData['collect']['upload_datas']['pem_fullchain']['full_path']);
					}
					if ($collectData['collect']['rename_certificate_files']['private'] !== TRUE) {
						$this->error = true;
						$this->error_msg[] = "Renaming certificate [private] into current certificate have problem, not all return true.";
						$this->error_msg[] = unlink($collectData['collect']['upload_datas']['pem_private']['full_path']);
					}
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot rename new uploaded file into current certificate file with exception: {$ex->getMessage()}.";
				}
			}
			// Update serverdata purpose::certificate
			if (!$this->error) {
				try {
					$collectData['collect']['update_serverdata_response'] = $this->mod_cert->set_server_purpose_data($collectData['collect']['pem_paths']['serverdata_purposes_data']->server_site_purpose, [
						'server_site_content'		=> json_encode($collectData['collect']['rename_certificate_files']),
					]);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot update serverdata pem-certificate purpose with exception: {$ex->getMessage()}.";
				}
			}
		}
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'		=> true,
				'errors'		=> false,
				'data'			=> $collectData['collect']['pem_paths']
			];
			if ($collectData['page_type'] === 'action') {
				$collectData['json_response']['data'] = $collectData['collect']['rename_certificate_files'];
				
				$this->output->set_content_type('application/json');
				$this->output->set_output(json_encode($collectData['json_response']));
			} else {
				$this->load->view('edit/edit-cert-modals/cert-upload.php', $collectData);
			}
		} else {
			$collectData['json_response'] = [
				'status'		=> false,
				'errors'		=> $this->error_msg,
				'data'			=> null
			];
			$collectData['page'] = 'dashboard-error';
			
			if ($collectData['page_type'] === 'action') {
				$this->output->set_content_type('application/json');
				$this->output->set_output(json_encode($collectData['json_response']));
			} else {
				$this->load->view('page/page-errors/error-modal.php', $collectData);
			}
		}
	}
	
	public function restart($service_name = 'nginx') {
		$collectData = [
			'page'				=> 'edit-server-pem',
			'collect'			=> [],
			'userdata'			=> $this->userdata,
			'page_title'		=> 'Server Service',
			'service_name'		=> (is_string($service_name) ? strtolower($service_name) : 'nginx'),
		];
		if (!in_array($collectData['service_name'], [
			'nginx',
			'machine',
			'pm2'
		])) {
			$collectData['service_name'] = 'nginx';
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
		//----------------
		if (!$this->error) {
			$collectData['collect']['restart_params'] = [
				'app_name'			=> 'smb-landingpage',
				'post_params'		=> [],
				'path'					=> ''
			];
			switch($collectData['service_name']) {
				case 'machine':
					$collectData['collect']['restart_params']['path'] = 'reboot';
					$collectData['collect']['restart_params']['post_params']['app_name'] = $collectData['collect']['restart_params']['app_name'];
				break;
				case 'pm2':
					$collectData['collect']['restart_params']['path'] = 'refresh';
					$collectData['collect']['restart_params']['post_params']['app_name'] = $collectData['collect']['restart_params']['app_name'];
				break;
				case 'nginx':
				default:
					$collectData['collect']['restart_params']['path'] = '';
				break;
			}				
					
			$collectData['collect']['restart_params']['server_address'] = sprintf("http://127.0.0.1:%d/%s", 
				Instance_config::$env_apc['env']['servicesrestart.' . Instance_config::$env_group['env_env'] . '.port'],
				$collectData['collect']['restart_params']['path']
			);
		}
		if (!$this->error) {
			try {
				$collectData['collect']['restart_service_response'] = $this->mod_cert->restart_server_service($collectData['collect']['restart_params']['server_address'], $collectData['collect']['restart_params']['post_params']);
				if (!isset($collectData['collect']['restart_service_response']['status'])) {
					$this->error = true;
					$this->error_msg[] = "Not have expected status from model.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot doing restart service with exception: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'		=> true,
				'data'			=> $collectData['collect']['restart_service_response'],
				'errors'		=> false,
			];
		} else {
			$collectData['json_response'] = [
				'status'		=> false,
				'data'			=> null,
				'errors'		=> $this->error_msg,
			];
		}
		$this->output->set_content_type('application/json');
		$this->output->set_output(json_encode($collectData['json_response']));
	}
	//-----------------------------------------------------
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//-----------------------------------------------------
	public function scripts(String $path_dir = 'edit-cert-javascripts', String $path_file = 'cert-edit.js') {
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
