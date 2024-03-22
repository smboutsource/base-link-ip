<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Model_users extends CI_Model {
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
	public function get_single_userdata_by_userid(Int $user_id) {
		try {
			$this->db->select('*')->from($this->base_auth['tables']['users']);
			$this->db->where('user_id', $user_id);
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function get_single_userdata_by_email(String $user_email) {
		try {
			$this->db->select('*')->from($this->base_auth['tables']['users']);
			$this->db->where('user_email', $user_email);
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function get_datatables() {
		
		$response = $this->datatables
			->select('user_id, user_email, user_active, user_role, user_dt_add, user_dt_edit')
			->from($this->base_auth['tables']['users'])
			->generate();
			
		return $response;
	}
	
	public function get_count_app_userdatas() {
		try {
			$this->db->select('COUNT(1) AS count_value')->from($this->base_auth['tables']['users']);
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function get_app_userdatas(Int $count_value, $pg = 0) {
		$offset = [
			'limit'			=> $this->base_auth['app_userdata_offset']['limit'],
			'offset'		=> $this->base_auth['app_userdata_offset']['offset'],
		];
		if ($count_value > 0) {
			$offset['pg_total'] = ceil($count_value / $offset['limit']);
			$offset['pg_num'] = (int)$pg;
		} else {
			$offset['pg_total'] = 1;
			$offset['pg_num'] = 1;
		}
		if ($offset['pg_num'] > 0) {
			$offset['offset'] = ($offset['pg_num'] * $offset['limit']);
		}
		try {
			$this->db->select('*')->from($this->base_auth['tables']['users']);
			$this->db->order_by('user_email', 'ASC');
			
			$this->db->limit($offset['limit'], $offset['offset']);
			$sql_query = $this->db->get();
			return $sql_query->result();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	public function set_app_userdata(Int $user_id, Array $input_params) {
		$result_params = [
			'affected_rows'		=> 0,
		];
		$edit_params = [
			'user_dt_edit'		=> $this->DateObject->format('Y-m-d H:i:s'),
		];
		try {
			$app_userdata = $this->get_single_userdata_by_userid($user_id);
			if (!isset($app_userdata->user_id) || !isset($app_userdata->user_email)) {
				$this->error = true;
				$this->error_msg[] = "App userdata not exists.";
			}
		} catch (Exception $ex) {
			$this->error = true;
			$this->error_msg[] = "Cannot get single userdata from app users database with exception: {$ex->getMessage()}.";
		}
		if (!$this->error) {
			if (isset($input_params['user_role'])) {
				$edit_params['user_role'] = $input_params['user_role'];
				if (!is_numeric($edit_params['user_role'])) {
					$this->error = true;
					$this->error_msg[] = "User role should be in numeric datatype.";
				} else {
					$edit_params['user_role'] = (int)$edit_params['user_role'];
				}
			}
			if (isset($input_params['user_active'])) {
				$edit_params['user_active'] = $input_params['user_active'];
				if (!is_string($edit_params['user_active'])) {
					$this->error = true;
					$this->error_msg[] = "User active should be in string datatype.";
				} else {
					$edit_params['user_active'] = strtoupper($edit_params['user_active']);
					if (!in_array($edit_params['user_active'], [
						'Y',
						'N'
					])) {
						$this->error = true;
						$this->error_msg[] = "User active only allow Y or N.";
					}
				}
			}
		}
		if (!$this->error) {
			try {
				$this->db->where('user_id', $app_userdata->user_id);
				$this->db->update($this->base_auth['tables']['users'], $edit_params);
				
				$result_params['affected_rows'] = $this->db->affected_rows();
			} catch (Exception $e) {
				$this->error = true;
				$this->error_msg[] = "Cannot update app userdata with exception {$ex->getMessage()}.";
			}
		}
		if (!$this->error) {
			$result_params['status'] = true;
			$result_params['errors'] = false;
		} else {
			$result_params['status'] = false;
			$result_params['errors'] = $this->error_msg;
		}
		return $result_params;
	}
	public function insert_app_userdata($input_params) {
		$insert_params = [
			'user_email'		=> (isset($input_params['user_email']) ? $input_params['user_email'] : ''),
			'user_role'			=> (isset($input_params['user_role']) ? (int)$input_params['user_role'] : 0),
			'user_active'		=> (isset($input_params['user_active']) ? $input_params['user_active'] : 'N'),
		];
		$insert_params['user_dt_add'] = $this->DateObject->format('Y-m-d H:i:s');
		try {
			$this->db->insert($this->base_auth['tables']['users'], $insert_params);
			return $this->db->insert_id();
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function delete_app_userdata(Object $app_userdata) {
		if (!isset($app_userdata->user_id)) {
			return false;
		}
		$delete_response = [
			'status'		=> false,
		];
		try {
			$this->db->where('user_id', $app_userdata->user_id);
			$this->db->delete($this->base_auth['tables']['users']);
			$affected_rows = $this->db->affected_rows();
			
			if ((int)$affected_rows > 0) {
				$delete_response['status'] = true;
			}
			return $delete_response;
		} catch (Exception $e) {
			return $e;
		}
	}
}