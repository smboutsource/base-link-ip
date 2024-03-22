<?php
if (!defined('BASEPATH')) {
	exit('Basepath not yet defined.');
}

class Model_landingpage extends CI_Model {
	
	private $base_auth, $base_page;
	function __construct() {
		parent::__construct();
		$this->load->config('auth/base_auth');
		$this->base_auth = $this->config->item('base_auth');
		
		$this->load->database();
	}
	
	private function get_scheme() {
		$this->config->config['base_url'] = str_replace('http://', 'https://', $this->config->config['base_url']);
		if (isset($_SERVER['SERVER_PORT'])) {
			if ($_SERVER['SERVER_PORT'] != 443) {
				redirect($this->uri->uri_string());
			}
		}
	}
	
	public function get_page_landingpage(String $page_purpose = 'display', Int $firebase_id = 1) {
		$page_purpose = strtolower($page_purpose);
		if (empty($page_purpose)) {
			$page_purpose = 'display';
		}
		if (!in_array($page_purpose, [
			'display',
			'preview',
		])) {
			$page_purpose = 'display';
		}
		
		try {
			$this->db->select('*')->from($this->base_auth['tables']['pages']);
			$this->db->where('page_status', 'Y');
			$this->db->where('page_purpose', $page_purpose);
			if ((int)$firebase_id > 1) {
				$this->db->where('page_firebase_id', $firebase_id);
			}
			$this->db->order_by('page_id', 'ASC')->limit(1);
			$sql_query = $this->db->get();
			return $sql_query->row();
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function get_base_list_pages() {
		try {
			$this->db->select('h.*')->from("{$this->base_auth['tables']['hostnames']} AS h");
			$this->db->where("h.hostname != ''", NULL, FALSE);
			$this->db->order_by('h.hostname_id', 'ASC');
			$this->db->limit(1);
			$sql_query = $this->db->get();
			$row = $sql_query->row();
			if (isset($row->hostname_id)) {
				$this->db->select('p.page_id, p.page_status, p.page_purpose, p.page_dt_add, p.page_dt_edit, p.page_firebase_id, h.hostname_id, h.hostname');
				$this->db->from("{$this->base_auth['tables']['pages']} AS p");
				$this->db->join("{$this->base_auth['tables']['hostnames']} AS h", 'h.hostname_id = p.page_firebase_id', 'LEFT');
				$this->db->where('p.page_firebase_id', $row->hostname_id);
				$this->db->group_by('h.hostname_id, h.hostname, p.page_purpose');
				$this->db->order_by('p.page_purpose_name', 'ASC');
				$sql_query = $this->db->get();
				return $sql_query->result();
			} else {
				return [
					(object)[
						'page_purpose'		=> 'display',
						'page_purpose_name'	=> 'Dsiplay',
					],
					(object)[
						'page_purpose'		=> 'draft',
						'page_purpose_name'	=> 'Draft',
					],
					(object)[
						'page_purpose'		=> 'preview',
						'page_purpose_name'	=> 'Preview',
					],
				
				];
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
}



