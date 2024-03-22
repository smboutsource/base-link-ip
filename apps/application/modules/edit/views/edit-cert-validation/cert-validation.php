<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}

$txt_content = '';
if (isset($json_response['status'])) {
	if ($json_response['status'] === TRUE) {
		$txt_content = $json_response['data']->server_site_content;
	} else {
		$txt_content = (isset($json_response['errors']) ? json_encode($json_response['errors']) : '');
	}
}
$this->output->set_content_type('text/plain');
$this->output->set_output($txt_content);