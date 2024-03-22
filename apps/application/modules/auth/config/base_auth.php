<?php
if (!defined("BASEPATH")) {
	exit("Cannot load script directly.");
}

$config['base_auth'] = [
	'base_path'				=> 'auth',
];
$config['base_auth']['app'] = [
	'client_id'		=> Instance_config::$env_apc['base_auth']['apps']['client_id'],
	'client_secret'	=> Instance_config::$env_apc['base_auth']['apps']['client_secret'],
];
$config['base_auth']['api_endpoints'] = [
	'host'			=> Instance_config::$env_apc['base_auth']['host']
];
$config['base_auth']['api_paths'] = Instance_config::$env_apc['base_auth']['paths'];


$config['base_auth']['check_ip_address_while_login'] = FALSE;
$config['base_auth']['client'] = array(
	'ip' => (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :
							(isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] :
								(getenv('HTTP_X_FORWARDED_FOR') ? getenv('HTTP_X_FORWARDED_FOR') :
									(isset($_ENV['HTTP_X_FORWARDED_FOR']) ? $_ENV['HTTP_X_FORWARDED_FOR'] :
										(getenv('HTTP_CLIENT_IP') ? getenv('HTTP_CLIENT_IP') :
											(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] :
												(getenv('REMOTE_ADDR') ? getenv('REMOTE_ADDR') :
													(isset($_ENV['REMOTE_ADDR']) ? $_ENV['REMOTE_ADDR'] :
														'0.0.0.0')))))))),
	'proxy' => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : (getenv('REMOTE_ADDR') ? getenv('REMOTE_ADDR') : (isset($_ENV['REMOTE_ADDR']) ? $_ENV['REMOTE_ADDR'] : '0.0.0.0'))),
	'ua' => ((isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown.Browser.UA'),
	'host' => ((isset($_SERVER['SERVER_NAME']) && (!empty($_SERVER['SERVER_NAME']))) ? $_SERVER['SERVER_NAME'] : 'rw.gg.in.th'),
	'uri' => ((isset($_SERVER['REQUEST_URI']) && (!empty($_SERVER['REQUEST_URI']))) ? strtolower(preg_replace('/\&/', '&amp;', $_SERVER['REQUEST_URI'])) : '/index.php'),
);
$config['base_auth']['admin_roles'] = [
	'edit',
	'insert'
];
$config['base_auth']['userdata_roles'] = [
	'admin'		=> [
		1,
		2,
		3
	],
	'user'		=> [
		0,
	]
];
$config['base_auth']['app_userdaata_roles'] = [
	'admin'		=> [
		'code'		=> 'admin',
		'value'		=> 1,
		'name'		=> 'Admin',
	],
	'user'		=> [
		'code'		=> 'user',
		'value'		=> 0,
		'name'		=> 'User',
	]
];
// Database Tables
$config['base_auth']['tables'] = [
	'users'					=> 'landingpage_users',
	'pages'					=> 'landingpage_pages',
	'servers'				=> 'landingpage_servers',
	'assets'				=> 'landingpage_assets',
	'firebase'				=> 'landingpage_firebase',
	'hostnames'				=> 'landingpage_firebase_hostnames'
];
$config['base_auth']['app_userdata_offset'] = [
	'limit'		=> Instance_config::$env_apc['env']['userdata.' . Instance_config::$env_group['env_env'] . '.limit_start'],
	'offset'	=> Instance_config::$env_apc['env']['userdata.' . Instance_config::$env_group['env_env'] . '.limit_off'],
];