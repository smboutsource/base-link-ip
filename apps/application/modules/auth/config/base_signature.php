<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}
$config['base_signature'] = [
	'base_path'		=> 'auth',
];
$config['base_signature']['cipher'] = 'AES-256-CBC';
$config['base_signature']['hash_method'] = 'sha256';
$config['base_signature']['binary_status'] = FALSE;
$config['base_signature']['key_length'] = 32;
