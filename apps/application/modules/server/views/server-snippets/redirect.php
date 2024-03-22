<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
	
}

if (isset($collect['validation_data']->server_site_content) && is_string($collect['validation_data']->server_site_content)) {
	if (!empty($collect['validation_data']->server_site_content)) {
		$collect['validation_data']->server_site_content = trim($collect['validation_data']->server_site_content);
		echo "# Proxy Intercept For Redirector\r\nreturn 301 {$collect['validation_data']->server_site_content};\r\n";
	} else {
		echo "# Proxy Intercept For Redirector\r\n";
	}
}
