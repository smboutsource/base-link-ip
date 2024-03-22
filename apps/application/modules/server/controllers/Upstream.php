<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Upstream extends MY_Controller {
	private $error = FALSE, $error_msg = [];
	private $base_auth;
	protected $userdata;
	function __construct() {
		parent::__construct();
		
		$this->load->model('auth/Model_auth', 'mod_auth');
		
		$this->base_auth = $this->mod_auth->get_base_auth();
		
		if (!$this->userdata = $this->mod_auth->start_userdata()) {
			return $this->mod_auth->required_login();
		}
		// Model Server
		$this->load->model('server/Model_server', 'mod_server');
	}
	// Get List ServerData List
	public function get_serverdata() {
		try {
			$response = $this->mod_server->get_upstream_data();
			
			$this->output->set_content_type('application/json');
			$this->output->set_output($response);
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	
	public function server(String $pg_action = 'form', String $server_purpose = 'upstream') {
		$collectData = [
			'page'						=> 'server-upstream-server',
			'collect'					=> [],
			'userdata'				=> $this->userdata,
			'page_title'			=> 'Server Upstream',
			'page_action'			=> (is_string($pg_action) ? strtolower($pg_action) : 'form'),
			'server_purpose'	=> (is_string($server_purpose) ? strtolower($server_purpose) : 'upstream'),
		];
		$collectData['collect']['per_page'] = Instance_config::$env_apc['env']['userdata.' . Instance_config::$env_group['env_env'] . '.limit_start'];
		if (!in_array($collectData['page_action'], [
			'form',
			'action',
		])) {
			$collectData['page_action'] = 'form';
		}
		if (!in_array($collectData['server_purpose'], [
			'upstream',
			'mapped',
			'redirect'
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
				$collectData['collect']['validation_data'] = $this->mod_server->get_single_server_data($collectData['server_purpose']);
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
					$collectData['collect']['server_update_response'] = $this->mod_server->set_server_purpose_data($collectData['collect']['validation_data']->server_site_purpose, [
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
	
	
	// Set Upstream File Upload
	public function set_upstream(String $page_type = 'form', String $server_purpose = 'upstream') {
		$collectData = [
			'page'							=> 'server-upstream-edit',
			'collect'						=> [],
			'userdata'					=> $this->userdata,
			'page_title'				=> 'Server Upstream Location',
			'page_type'					=> (is_string($page_type) ? strtolower($page_type) : 'form'),
			'server_purpose'		=> (is_string($server_purpose) ? strtolower($server_purpose) : 'form'),
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
		if (!$this->error) {
			if (!in_array($collectData['server_purpose'], [
				'mapped',
				'redirect',
				'upstream'
			])) {
				$this->error = true;
				$this->error_msg[] = "Set server data purpose not allowed.";
			}
		}
		//----------------
		if (!$this->error) {
			$collectData['collect']['snippets_paths'] = [
				'basepath'		=> (dirname(Instance_config::$envpath) . DIRECTORY_SEPARATOR . Instance_config::$env_apc['env']['serverdata.' . Instance_config::$env_group['env_env'] . '.certificate.path'] . DIRECTORY_SEPARATOR . Instance_config::$env_apc['env']['serverdata.' . Instance_config::$env_group['env_env'] . '.snippets.path']),
			];
			if (!is_dir($collectData['collect']['snippets_paths']['basepath'])) {
				$this->error = true;
				$this->error_msg[] = "Certificate pem basepath should be a directory.";
			}
		}
		if (!$this->error) {
			$collectData['collect']['snippets_paths']['upstream'] = ($collectData['collect']['snippets_paths']['basepath'] . DIRECTORY_SEPARATOR . Instance_config::$env_apc['env']['serverdata.' . Instance_config::$env_group['env_env'] . '.snippets.upstream']);
			$collectData['collect']['snippets_paths']['redirect'] = ($collectData['collect']['snippets_paths']['basepath'] . DIRECTORY_SEPARATOR . Instance_config::$env_apc['env']['serverdata.' . Instance_config::$env_group['env_env'] . '.snippets.redirect']);
			$collectData['collect']['snippets_paths']['mapped'] = ($collectData['collect']['snippets_paths']['basepath'] . DIRECTORY_SEPARATOR . Instance_config::$env_apc['env']['serverdata.' . Instance_config::$env_group['env_env'] . '.snippets.mapped']);
			$collectData['collect']['snippets_paths']['proxies'] = ($collectData['collect']['snippets_paths']['basepath'] . DIRECTORY_SEPARATOR . 'mapped-proxies.conf');
			if (!file_exists($collectData['collect']['snippets_paths']['upstream'])) {
				$this->error = true;
				$this->error_msg[] = "Upstream upstream not exists.";
			}
			if (!file_exists($collectData['collect']['snippets_paths']['redirect'])) {
				$this->error = true;
				$this->error_msg[] = "Upstream redirect key not exists.";
			}
			if (!file_exists($collectData['collect']['snippets_paths']['mapped'])) {
				$this->error = true;
				$this->error_msg[] = "Upstream mapped key not exists.";
			}
		}
		if (!$this->error) {
			try {
				$collectData['collect']['snippets_datas'] = [
					'mapped'						=> $this->mod_server->get_single_server_data('mapped'),
					'redirect'					=> $this->mod_server->get_single_server_data('redirect'),
					'upstream'					=> $this->mod_server->get_single_server_data('upstream'),
				];
				if (!isset($collectData['collect']['snippets_datas']['mapped']->server_site_purpose)) {
					$this->error = true;
					$this->error_msg[] = "Not have serverdata mapped by purpose.";
				}
				if (!isset($collectData['collect']['snippets_datas']['redirect']->server_site_purpose)) {
					$this->error = true;
					$this->error_msg[] = "Not have serverdata redirect by purpose.";
				}
				if (!isset($collectData['collect']['snippets_datas']['upstream']->server_site_purpose)) {
					$this->error = true;
					$this->error_msg[] = "Not have serverdata upstream by purpose.";
				}
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get serverdata purpose for pem-certificate with exception: {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			$seerver_purpose_selected = $collectData['server_purpose'];
			if (!$collectData['collect']['validation_data'] = $collectData['collect']['snippets_datas'][$seerver_purpose_selected]) {
				$this->error = true;
				$this->error_msg[] = "Not have server purpose data requested.";
			}
		}
		//
		// Action Edit Upstream
		//
		if ($collectData['page_type'] === 'action') {
			# Load Codeigniter helpers
			$this->load->helper('security');
			$this->load->library('form_validation');
			// Do Create File
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
			// Filter for valid URL with HTTPS
			if (!$this->error) {
				if (!preg_match('/([a-z0-9A-Z\-\_\:\/]+)/', $collectData['collect']['input_params']['server_site_content'])) {
					$this->error = true;
					$this->error_msg[] = "Invalid link-website, allowing character only a-z, 0-9, A-Z, with dash and hypens.";
				}
			}
			if (!$this->error) {
				if (!filter_var($collectData['collect']['input_params']['server_site_content'], FILTER_VALIDATE_URL)) {
					$this->error = true;
					$this->error_msg[] = "Not a valid URL given as Link-website, please input a valid URL include protocol like.";
				}
			}
			if (!$this->error) {
				try {
					$collectData['collect']['url_addresses'] = parse_url($collectData['collect']['input_params']['server_site_content']);
					if (!isset($collectData['collect']['url_addresses']['scheme'])) {
						$this->error = true;
						$this->error_msg[] = "Given URL not have scheme or protocol for link-website.";
					}
					if (!isset($collectData['collect']['url_addresses']['host'])) {
						$this->error = true;
						$this->error_msg[] = "Given URL not have host for link-website.";
					}
					
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot parsing given url with exception: {$ex->getMessage()}.";
				}
			}
			if (!$this->error) {
				$collectData['collect']['filtered_siteurl'] = [
					'full'			=> sprintf("{$collectData['collect']['url_addresses']['scheme']}://{$collectData['collect']['url_addresses']['host']}%s",
						(isset($collectData['collect']['url_addresses']['port']) ? (in_array($collectData['collect']['url_addresses']['port'], [80, 443]) ? '' : ":{$collectData['collect']['url_addresses']['port']}") : '')
					),
					'host'			=> sprintf("{$collectData['collect']['url_addresses']['host']}%s",
						(isset($collectData['collect']['url_addresses']['port']) ? (in_array($collectData['collect']['url_addresses']['port'], [80, 443]) ? '' : ":{$collectData['collect']['url_addresses']['port']}") : '')
					),
					'redirect'	=> sprintf("{$collectData['collect']['url_addresses']['scheme']}://{$collectData['collect']['url_addresses']['host']}%s%s",
						(isset($collectData['collect']['url_addresses']['port']) ? (in_array($collectData['collect']['url_addresses']['port'], [80, 443]) ? '' : ":{$collectData['collect']['url_addresses']['port']}") : ''),
						(isset($collectData['collect']['url_addresses']['path']) ? $collectData['collect']['url_addresses']['path'] : '')
					),
				];
				if (empty($collectData['collect']['filtered_siteurl']['host'])) {
					$this->error = true;
					$this->error_msg[] = "Empty filtered site url.";
				}
			}
			//
			// Send Update to Model
			//
			if (!$this->error) {
				try {
					if (strtolower($collectData['collect']['validation_data']->server_site_purpose) === 'redirect') {
						$collectData['collect']['site_purpose_update_params'] = [
							'server_site_content'			=> $collectData['collect']['filtered_siteurl']['redirect']
						];
					} else {
						$collectData['collect']['site_purpose_update_params'] = [
							'server_site_content'			=> $collectData['collect']['filtered_siteurl']['host']
						];
					}
					$collectData['collect']['server_update_response'] = $this->mod_server->set_server_purpose_data($collectData['collect']['validation_data']->server_site_purpose, $collectData['collect']['site_purpose_update_params']);
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot update server data with exception: {$ex->getMessage()}.";
				}
			}
			if (!$this->error) {
				if ((int)$collectData['collect']['server_update_response'] > 0) {
					try {
						$collectData['collect']['validation_data'] = $this->mod_server->get_single_server_data($collectData['collect']['validation_data']->server_site_purpose);
						if (!isset($collectData['collect']['validation_data']->server_site_content)) {
							$this->error = true;
							$this->error_msg[] = "Not have data of server-data purpose.";
						}
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Cannot get new server-purpose-data after make server update with exception: {$ex->getMessage()}.";
					}
				} else {
					$this->error = true;
					$this->error_msg[] = "Server update response not have affected rows, this process is skipped.";
				}
			}
			if (!$this->error) {
				$collectData['collect']['validation_data']->server_site_content = trim($collectData['collect']['validation_data']->server_site_content);
				if (empty($collectData['collect']['validation_data']->server_site_content)) {
					$this->error = true;
					$this->error_msg[] = "Empty server-site-content after editing.";
				}
			}
			//
			// If Success Create File [rename_to_file]
			//
			if (!$this->error) {
				try {
					$this->output->set_content_type('text/plain');
					$collectData['generated_config'] = $this->load->view("server/server-snippets/{$collectData['collect']['validation_data']->server_site_purpose}.php", $collectData, TRUE);
					if (empty($collectData['generated_config'])) {
						$this->error = true;
						$this->error_msg[] = "Empty generated config.";
					}
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot make config file *.conf with exception: {$ex->getMessage()}.";
				}
			}
			if (!$this->error) {
				try {
					$collectData['collect']['write_file_response'] = $this->mod_server->write_config_file($collectData['collect']['validation_data']->server_site_purpose, $collectData['collect']['snippets_paths'], $collectData['generated_config']);
					if (strtolower($collectData['collect']['validation_data']->server_site_purpose) === 'mapped') {
						$collectData['generated_mapped'] = $this->load->view("server/server-snippets/proxies.php", $collectData, TRUE);
						$this->mod_server->write_config_file('proxies', $collectData['collect']['snippets_paths'], $collectData['generated_mapped']);
					}
					if ($collectData['collect']['write_file_response'] !== TRUE) {
						$this->error = true;
						$this->error_msg[] = "Response while write config file not true.";
					}
				} catch (Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot create write file with exception: {$ex->getMessage()}.";
				}
			}
		}
		if (!$this->error) {
			$collectData['json_response'] = [
				'status'				=> true,
				'errors'				=> false,
				'data'					=> $collectData['collect']['snippets_paths'],
			];
			if ($collectData['page_type'] === 'action') {
				$collectData['json_response']['data'] = $collectData['collect']['validation_data'];
				
				$this->output->set_content_type('application/json');
				$this->output->set_output(json_encode($collectData['json_response']));
			} else {
				$this->output->set_content_type('text/plain');
				$this->load->view("server/server-snippets/{$collectData['collect']['validation_data']->server_site_purpose}.php", $collectData);
			}
		} else {
			$collectData['json_response'] = [
				'status'				=> false,
				'errors'				=> $this->error_msg,
				'data'					=> null,
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
	public function server_purposes(String $server_purpose = 'upstream') {
		$collectData = [
			'page'						=> 'server-upstream-purpose',
			'collect'					=> [],
			'userdata'				=> $this->userdata,
			'page_title'			=> 'Server Upstream',
			'server_purpose'	=> (is_string($server_purpose) ? strtolower($server_purpose) : 'upstream'),
		];
		$collectData['collect']['per_page'] = Instance_config::$env_apc['env']['userdata.' . Instance_config::$env_group['env_env'] . '.limit_start'];
		if (!in_array($collectData['server_purpose'], [
			'upstream',
			'mapped',
			'redirect'
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
				$collectData['collect']['validation_data'] = $this->mod_server->get_single_server_data($collectData['server_purpose']);
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
			$this->load->view("server/server-modals/server-{$collectData['server_purpose']}.php", $collectData);
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
	
	
	
	
	//-----------------------------------------------------
	public function scripts(String $path_dir = 'server-javascripts', String $path_file = 'server.js') {
		$collectData = [
			'page'			=> 'server-script',
			'collect'		=> [],
		];
		$collectData['collect']['paths'] = [
			'dir'		=> strtolower($path_dir),
			'file'		=> strtolower($path_file),
		];
		$collectData['collect']['paths']['dir'] = str_replace('_', '-', $collectData['collect']['paths']['dir']);
		$collectData['collect']['paths']['file'] = str_replace('_', '-', $collectData['collect']['paths']['file']);
		$collectData['collect']['paths']['paths'] = [
			'load'		=> sprintf("server/%s/%s", 
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