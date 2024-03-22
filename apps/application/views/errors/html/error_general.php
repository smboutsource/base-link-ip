<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$json_response = [
	'status'			=> false,
	'errors'			=> [
		'title'		=> $heading,
		'message'	=> $message,
	]
];
echo json_encode($json_response);	
	
?>