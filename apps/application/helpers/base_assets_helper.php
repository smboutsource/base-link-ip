<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}
if (!function_exists('base_assets')) {
	function base_assets($asset_path) {
		$base_url = 'https://static.augipt.com/apps/landingpages/assets';
		return sprintf('%s/%s',
			$base_url,
			$asset_path
		);
	}
}

if (!function_exists('base_list_pages')) {
	function base_list_pages() {
		try {
			$CI = &get_instance();
			$CI->load->model('page/Model_landingpage', 'mod_landingpage');
			$list_pages = $CI->mod_landingpage->get_base_list_pages();
			return $list_pages;
		} catch (Exception $e) {
			throw $e;
		}
	}
}
if (!function_exists('base_notification')) {
	function base_notification($type, $message) {
		get_instance()->session->set_flashdata('base_notification', '<script type="text/javascript">toastr.' . $type . '("' . $message . '")</script>');
	}
}