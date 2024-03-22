<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
	
}

if (isset($collect['validation_data']->server_site_content) && is_string($collect['validation_data']->server_site_content)) {
	echo "upstream app {\r\nserver          {$collect['validation_data']->server_site_content}:443;\r\n}\r\nupstream webapp {\r\nserver          127.0.0.1:8888;\r\n}\r\n";
}
