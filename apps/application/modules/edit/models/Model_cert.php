<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Model_cert extends CI_Model {
	private $error = FALSE, $error_msg = [];
	private $base_auth, $base_page;
	protected $DateObject;
	function __construct() {
		parent::__construct();
		$this->load->config('auth/base_auth');
		$this->base_auth = $this->config->item('base_auth');
		
		$this->DateObject = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new DateTimeZone(Instance_config::$timezone));
		$this->load->library('Lib_datatables', null, 'datatables');
	}
	
	public function get_server_cert_validation() {
		try {
			$this->db->select('*')->from($this->base_auth['tables']['servers']);
			$this->db->where('server_site_purpose', 'validation');
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function get_single_server_data(String $server_purpose = '') {
		if (empty($server_purpose)) {
			return false;
		}
		$server_purpose = trim($server_purpose);
		try {
			$this->db->select('*')->from($this->base_auth['tables']['servers']);
			$this->db->where('server_site_purpose', $server_purpose);
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	
	public function get_datatables() {
		try {
			$response = $this->datatables
				->select('server_site_purpose, server_site_id, server_site_code, server_site_content, server_site_datetime')
				->from($this->base_auth['tables']['servers'])
				->where('server_site_code', 'landingpage')
				->where_in('server_site_purpose', [
					'certificate',
					'validation'
				])->generate();
			
			return $response;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	// CRUD
	public function set_server_purpose_data(String $server_purpose, Array $input_params) {
		try {
			$server_purpose = trim($server_purpose);
			$server_purpose = strtolower($server_purpose);
			
			$update_params = [
				'server_site_datetime'		=> $this->DateObject->format('Y-m-d H:i:s'),
			];
			if (isset($input_params['server_site_content']) && is_string($input_params['server_site_content'])) {
				$update_params['server_site_content'] = $input_params['server_site_content'];
			}
			$this->db->where('server_site_purpose', $server_purpose);
			$this->db->update($this->base_auth['tables']['servers'], $update_params);
			return $this->db->affected_rows();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	// Certificate Upload
	public function get_certificate_mimetypes() {
		return [
			'application/pem-certificate-chain',
			'text/plain',
		];
	}
	public function get_certificate_upload_config(String $basepath) {
		if (!is_dir($basepath)) {
			return false;
		}
		return [
			'upload_path'							=> ($basepath . DIRECTORY_SEPARATOR),
			'allowed_types'						=> '*',
			'max_size'								=> 2000000,
			'file_ext_tolower'				=> TRUE,
			'max_filename_increment'	=> 1000,
			'encrypt_name'						=> TRUE,
			'max_filename'						=> 102400
		];
	}
	public function pem_certificate_verify(Array $file_paths) {
		$pem_params = [
			'check_private_key_initial'		=> FALSE
		];
		if (!isset($file_paths['fullchain'])) {
			$this->error = true;
			$this->error_msg[] = "Require fullchain file path.";
		}
		if (!isset($file_paths['private'])) {
			$this->error = true;
			$this->error_msg[] = "Require private file path.";
		}
		if (!$this->error) {
			if (!is_string($file_paths['fullchain'])) {
				$this->error = true;
				$this->error_msg[] = "Pem fullchain file path must be in string datatype.";
			}
			if (!is_string($file_paths['private'])) {
				$this->error = true;
				$this->error_msg[] = "Pem private file path must be in string datatype.";
			}
		}
		if (!$this->error) {
			if (!file_exists($file_paths['fullchain'])) {
				$this->error = true;
				$this->error_msg[] = "Pem fullchain path file not exists.";
			}
			if (!file_exists($file_paths['private'])) {
				$this->error = true;
				$this->error_msg[] = "Pem private path file not exists.";
			}
		}
		if (!$this->error) {
			try {
				$fp = fopen($file_paths['private'], 'r');
				$pem_params['private_key'] = fread($fp, 8192);
				fclose($fp);
				$fp = fopen($file_paths['fullchain'], 'r');
				$pem_params['fullchain_data'] = fread($fp, 8192);
				fclose($fp);
				/*
				if (!empty($pem_params['private_key'])) {
					$pem_params['pkey_id'] = openssl_get_privatekey($pem_params['private_key']);
					if (openssl_open($pem_params['fullchain_data'], $pem_params['open'], '', $pem_params['pkey_id'])) {
						$pem_params['cert_validate_status'] = TRUE;
					} else {
						$pem_params['cert_validate_status'] = FALSE;
					}
					openssl_free_key($pem_params['pkey_id']);
				} else {
					$this->error = true;
					$this->error_msg[] = "Empty private-key after read.";
				}
				*/
				$pem_params['check_private_key'] = openssl_x509_check_private_key($pem_params['fullchain_data'], [
					$pem_params['private_key'],
					NULL
				]);
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot generate private-key pem for validate ssl certificate with exception:  {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			$pem_params['errors'] = FALSE;
		} else {
			$pem_params['errors'] = $this->error_msg;
		}
		
		return $pem_params;
	}
	
	
	
	
	// Service Restart
	public function restart_server_service(String $service_address, Array $service_params) {
		try {
			$post_params = [
				'app_name'			=> (isset($service_params['app_name']) ? $service_params['app_name'] : '')
			];
			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER 		=> true,
				CURLOPT_ENCODING 			=> '',
				CURLOPT_MAXREDIRS 			=> 10,
				CURLOPT_TIMEOUT 			=> 45,
				CURLOPT_FOLLOWLOCATION 		=> true,
				CURLOPT_HTTP_VERSION 		=> CURL_HTTP_VERSION_1_1,
				CURLOPT_HEADER				=> FALSE,
			]);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			# Set Header
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-Type: application/x-www-form-urlencoded',
				'Accept: application/json'
			]);
			
			curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_params));
			
			curl_setopt($ch, CURLOPT_URL, $service_address);
			$curl_response = curl_exec($ch);
			$curl_info = [
				'header_response'		=> curl_getinfo($ch, CURLINFO_HEADER_OUT),
				'request_body'			=> $post_params,
				'mixing'				=> curl_getinfo($ch),
			];
			$curl_response = json_decode($curl_response);
			curl_close($ch);
			
			if (isset($curl_response->status)) {
				$results = [
					'status'		=> true,
					'data'			=> $curl_response
				];
			} else {
				$results = [
					'status'		=> false,
					'data'			=> $curl_response
				];
			}
			return $results;
		} catch (Exception $e) {
			throw $e;
		}
	}
}
