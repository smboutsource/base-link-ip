<?php
defined('INSTANCECONFIG') OR define('INSTANCECONFIG', 'config.php');

class Instance_config {
	public static $envpath;
	public static $conf = [];
	public static $timezone = 'Asia/Bangkok';
	public static $DateExpired = 'PT360S';
	public static $env_apc;
	public static $x_augipt_auth = '';
	public static $env_base_url = 'landing.smbassets.com';
	public static $env_group = [
		'env_env'		=> 'prod', // local|dev|sandbox|prod [only change to "dev", "sandbox", or "prod"]
		'env_group'		=> 'dev',
		'env_server'	=> 'smb',
		'env_location'	=> 'Bangkok',
		'mysql'			=> [],
		'mongodata'		=> [],
		'cdn'			=> [],
		'name'			=> ''
	];
	function __construct() {
		self::$envpath = (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'apps');
		self::$env_apc = [
			'landingpages'	=> (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'landingpages.ini'),
			'ini'			=> (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'main.ini'),
			'json'			=> (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'main.json'),
			'name'			=> 'smb-landingpage',
			'env'			=> [],
			'lpgsenvs'		=> [],
			'base_auth'		=> null,
			'base_json'		=> null,
		];
	}
	private static function set_srvgroup_env(String $srvgroup) {
		self::$env_group['env_group'] = $srvgroup;
	}
	private static function set_environment_env(String $env) {
		self::$env_group['env_env'] = $env;
	}
	protected static function set_apc_app_env() {
		if (!file_exists(self::$env_apc['ini'])) {
			exit("Config not have ini files.");
		}
		if (!function_exists('apc_fetch')) {
			$ini = false;
		} else {
			$ini = apc_fetch(self::$env_apc['name'] . '-ini');
		}
		if (!$ini) {
			$ini = file_get_contents(self::$env_apc['ini']);
			if ($ini) {
				if (function_exists('apc_store')) {
					apc_store(self::$env_apc['name'] . '-ini', $ini);
				}
			}
		}
		self::$env_apc['env'] = parse_ini_string($ini);
		if (isset(self::$env_apc['env']['environment'])) {
			self::set_environment_env(self::$env_apc['env']['environment']);
		}
	}
	public static function get_apc_app_env() {
		return self::$env_apc;
	}
	protected static function set_apc_app_json() {
		$env = self::$env_group['env_env'];
		if (!file_exists(self::$env_apc['json'])) {
			exit("Env not have json files.");
		}
		if (!function_exists('apc_fetch')) {
			$json = false;
		} else {
			$json = apc_fetch(self::$env_apc['name'] . '-json');
		}
		if (!$json) {
			$json = file_get_contents(self::$env_apc['json']);
			if ($json) {
				if (function_exists('apc_store')) {
					apc_store(self::$env_apc['name'] . '-json', $json);
				}
			}
		}
		$env_json = json_decode($json, true);
		self::$env_apc['base_auth'] = $env_json['auth'][$env];
		self::$env_apc['base_json'] = $env_json;
	}
	protected static function set_landingpages_envs() {
		if (!file_exists(self::$env_apc['landingpages'])) {
			exit("Instance landingpages envs not have ini files.");
		}
		if (!function_exists('apc_fetch')) {
			$ini = false;
		} else {
			$ini = apc_fetch(self::$env_apc['name'] . 'landingpages-ini');
		}
		if (!$ini) {
			$ini = file_get_contents(self::$env_apc['landingpages']);
			if ($ini) {
				if (function_exists('apc_store')) {
					apc_store(self::$env_apc['name'] . 'landingpages-ini', $ini);
				}
			}
		}
		self::$env_apc['lpgsenvs'] = parse_ini_string($ini);
		if (isset(self::$env_apc['lpgsenvs']['srvgroup'])) {
			self::set_srvgroup_env(self::$env_apc['lpgsenvs']['srvgroup']);
		}
	}
	public static function set_instance_servers() {
		$is_config = TRUE;
		$error_configs = [];
		$env_hostname = (isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost'));
		
		$env_group = '';
		try {
			$env_query = (isset($_GET['envgroup']) ? $_GET['envgroup'] : '');
			$env_query = (is_string($env_query) ? strtolower($env_query) : '');
			preg_match('/([a-z0-9\-]+)\.?(.*?)$/', $env_hostname, $env_hostnames);
			if (isset($env_hostnames[1])) {
				$env_group = $env_hostnames[1];
			}
			
			if (empty($env_group)) {
				$is_config = FALSE;
				$error_configs[] = "[404] Empty env_group.";
			} else {
				self::$env_group['name'] = $env_group;
			}
			
			self::$env_base_url = NULL;
			self::$env_group['name'] = NULL;
			
			self::$env_group['mysql'] = [
				'host'			=> self::$env_apc['env']['database.'. self::$env_group['env_env'] . '.host'],
				'port'			=> self::$env_apc['env']['database.'. self::$env_group['env_env'] . '.port'],
				'user'			=> self::$env_apc['env']['database.'. self::$env_group['env_env'] . '.user'],
				'pass'			=> self::$env_apc['env']['database.'. self::$env_group['env_env'] . '.pass'],
				'name'			=> self::$env_apc['env']['database.'. self::$env_group['env_env'] . '.name'],
			];
			
			self::$env_group['cdn'] = [
				'endpoint'				=> 'sgp1.digitaloceanspaces.com',
				'schema'					=> 'https',
				'region'					=> 'sgp1',
				'origin'					=> 'https://smbassets.sgp1.digitaloceanspaces.com',
				'key'							=> 'DO00G29HJ38EPJ26VPEP',
				'secret'					=> 'a1Uc+b8tXIH16xUWe13A7eHk0YPK3McXOBJUz0fdMYw',
				'prefix'					=> 'smbassets/firebase',
				'bucket'					=> 'smbassets',
				'url'							=> 'https://cdn.smbassets.com',
			];
			
		} catch (Exception $ex) {
			$is_config = FALSE;
			$error_configs[] = "[500] Exception Errors.";
		}
		
		if ($is_config !== TRUE) {
			header('Content-type: application/json');
			exit(json_encode([
				'status'			=> false,
				'errors'			=> [
					"Not have config for request uri pattern."
				],
				'data'				=> null,
				'errors'			=> $error_configs,
				'querystring'		=> $env_query,
				'env_hostname'		=> $env_hostname
			]));
		}
	}
	
	
	
	
	public static function set_envpath(String $path) {
		self::$envpath = $path;
	}
	public static function get_envpath() {
		return self::$envpath;
	}
	
	
	public function set_app_config() {
		try {
			self::set_apc_app_env();
			self::set_apc_app_json();
			self::set_landingpages_envs();
			// Set Instance Servers
			self::set_instance_servers();
		} catch (Exception $ex) {
			throw $ex;
		}
	}
	
}

// Make config by query-string

try {
	(new Instance_config())->set_app_config();
	

} catch (Exception $errors) {
	header('Content-type: application/json');
	exit(json_encode([
		'status'		=> false,
		'errors'		=> [
			'Cannot set requested environemnt group instance.',
		],
		'messages'		=> $errors->getMessage()
	]));
}
