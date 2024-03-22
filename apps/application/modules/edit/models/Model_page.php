<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Model_page extends CI_Model {
	
	private $base_auth, $base_page;
	function __construct() {
		parent::__construct();
		$this->load->config('auth/base_auth');
		$this->base_auth = $this->config->item('base_auth');
		
		$this->DateObject = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new DateTimeZone(Instance_config::$timezone));
	}
	
	
	public function get_single_page_by_firebase_id(Int $firebase_id = 0) {
		try {
			$this->db->select('*')->from($this->base_auth['tables']['pages']);
			$this->db->where('page_firebase_id', $firebase_id);
			$sql_query = $this->db->get();
			return $sql_query->result();
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function get_single_page_by_page_id(Int $page_id = 0) {
		try {
			$this->db->select('*')->from($this->base_auth['tables']['pages']);
			$this->db->where('page_id', $page_id);
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function get_single_page_purpose(String $page_purpose, $firebase_ids) {
		$firebase_id = 0;
		switch (gettype($firebase_ids)) {
			case 'string':
			case 'integer':
				$firebase_id = (is_numeric($firebase_id) ? (int)$firebase_id : 0);
			break;
			case 'array':
			default:
				if (isset($firebase_ids['page_firebase_id'])) {
					$firebase_id = (is_numeric($firebase_ids['page_firebase_id']) ? (int)$firebase_ids['page_firebase_id'] : 0);
				}
			break;
		}
		
		if (empty($page_purpose)) {
			return false;
		}
		try {
			$this->db->select('*')->from($this->base_auth['tables']['pages']);
			$this->db->where('page_purpose', $page_purpose);
			if ($firebase_id > 0) {
				$this->db->where('page_firebase_id', $firebase_id);
			} else {
				$this->db->order_by('page_firebase_id', 'ASC');
			}
			$this->db->limit(1);
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	public function set_single_page(Int $page_id, Array $input_params, Int $firebase_id = 0) {
		$firebase_id = (is_numeric($firebase_id) ? (int)$firebase_id : 0);
		$edit_params = [
			'page_content'		=> (isset($input_params['page_content']) ? $input_params['page_content'] : ''),
		];
		if (!isset($input_params['page_dt_edit'])) {
			$edit_params['page_dt_edit'] = $this->DateObject->format('Y-m-d H:i:s');
		} else {
			$edit_params['page_dt_edit'] = $input_params['page_dt_edit'];
		}
		if (!is_string($edit_params['page_content'])) {
			return [
				'status'		=> false
			];
		}
		try {
			$this->db->where('page_id', $page_id);
			$this->db->update($this->base_auth['tables']['pages'], $edit_params);
			
			return [
				'status'		=> ($this->db->affected_rows() > 0)
			];
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function set_single_page_purpose(String $purpose_code, Array $input_params, Int $firebase_id) {
		$firebase_id = (is_numeric($firebase_id) ? (int)$firebase_id : 0);
		$edit_params = [
			'page_content'		=> (isset($input_params['page_content']) ? $input_params['page_content'] : ''),
		];
		if (!isset($input_params['page_dt_edit'])) {
			$edit_params['page_dt_edit'] = $this->DateObject->format('Y-m-d H:i:s');
		} else {
			$edit_params['page_dt_edit'] = $input_params['page_dt_edit'];
		}
		if (!is_string($edit_params['page_content'])) {
			return [
				'status'		=> false
			];
		}
		if (!in_array($purpose_code, [
			'display',
			'preview',
			'drfat'
		])) {
			return [
				'status'		=> false
			];
		}
		try {
			if ($firebase_id > 0) {
				$this->db->where('page_firebase_id', $firebase_id);
			}
			$this->db->where('page_purpose', $purpose_code);
			$this->db->update($this->base_auth['tables']['pages'], $edit_params);
			
			return [
				'status'		=> ($this->db->affected_rows() > 0)
			];
		} catch (Exception $e) {
			throw $e;
		}
	}
	
}