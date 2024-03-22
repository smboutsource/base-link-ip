<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
	
}

if (isset($collect['validation_data']->server_site_content) && is_string($collect['validation_data']->server_site_content)) {
	$hostname_domains = explode('.', $collect['validation_data']->server_site_content);
	if (isset($hostname_domains[0]) && !empty($hostname_domains[0])) {
		echo "set \$proxied_hostname_address \"{$collect['validation_data']->server_site_content}\";\r\nset \$proxied_domain_title \"{$hostname_domains[0]}</b>\";\r\nset \$proxied_domain_href \"{$hostname_domains[0]}</a>\";\r\nset \$proxied_lite_title \"{$hostname_domains[0]}\";\r\nset \$proxied_domain_footer \"{$hostname_domains[0]}. All Rights Reserved\";\r\n";
	}
}


