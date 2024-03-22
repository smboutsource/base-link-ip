<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$json_response = [
	'title'		=> $heading,
	'message'	=> $message,
];
echo json_encode($json_response);	
	
?>