<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Model_server extends CI_Model {
	private $error = FALSE, $error_msg = [];
	private $base_auth, $base_page;
	protected $DateObject;
	function __construct() {
		parent::__construct();
		$this->load->config('auth/base_auth');
		$this->base_auth = $this->config->item('base_auth');
		
		$this->DateObject = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new DateTimeZone(Instance_config::$timezone));
		$this->load->library('Lib_datatables', null, 'datatables');
		// Helper File
		$this->load->helper('file');
	}
	
	public function get_upstream_data() {
		try {
			$response = $this->datatables
				->select('server_site_purpose, server_site_id, server_site_code, server_site_content, server_site_datetime')
				->from($this->base_auth['tables']['servers'])
				->where('server_site_code', 'app')
				->where_in('server_site_purpose', [
					'upstream',
					'redirect',
					'mapped'
				])->generate();
			
			return $response;
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
	
	public function write_config_file(String $server_purpose, Array $config_paths, String $config_data) {
		try {
			$file_path = $config_paths[$server_purpose];
			if (!file_exists($file_path)) {
				return false;
			}
			$result = write_file($file_path, $config_data);
			return $result;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	
	
	
	
	
	
	
	
}