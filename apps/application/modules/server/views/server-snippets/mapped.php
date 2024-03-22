<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
	
}

if (isset($collect['validation_data']->server_site_content) && is_string($collect['validation_data']->server_site_content)) {
	echo "map \$cookie_smbSrv \$proxied_site_app_code {\r\n\"webapp\"		\"webapp\";\r\n\"app\"   		\"{$collect['validation_data']->server_site_content}\";\r\ndefault    		\"{$collect['validation_data']->server_site_content}\";\r\n}\r\nmap \$cookie_smbSrv \$proxied_hostname_locator {\r\n\"webapp\"		localhost;\r\n\"app\"  			{$collect['validation_data']->server_site_content};\r\ndefault    		{$collect['validation_data']->server_site_content};\r\n}\r\nmap \$cookie_smbSrv \$upstreamsrv {\r\n\"webapp\"		\"http://webapp\";\r\n\"app\"       \"https://app\";\r\ndefault      	\"https://app\";\r\n}\r\n";
}


