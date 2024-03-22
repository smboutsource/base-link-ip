<?php 
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
/* load the MX_Controller class */
require_once(APPPATH . "third_party/MX/Controller.php");


if (!isset(Instance_config::$env_group['env_env'])) {
	header('Content-type: application/json');
	exit( json_encode([
			'status'			=> false,
			'message'			=> "System env config not yet configured."
		])
	);
}

// Not show Error::Deprecated
//error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

class MY_Controller extends MX_Controller {
	function __construct() {
		parent::__construct();
		// For Debug Purpose
		# $this->output->enable_profiler(TRUE);
	}


	

}
