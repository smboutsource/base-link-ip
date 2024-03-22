<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}

class Lib_signature {
	private $CI;
	private $base_signature;
	function __construct() {
		$this->CI = &get_instance();
		$this->CI->load->config('auth/base_signature');
		$this->base_signature = $this->CI->config->item('base_signature');
		
	}
	public function get_base_signature() {
		return $this->base_signature;
	}
	
	public function encrypt_signature_string(String $input_text, String $key = '') {
		if (empty($key)) {
			$key = md5($this->base_auth['app']['client_secret']);
		}
		if (!isset($input_text)) {
			return false;
		}
		
		$input_text = (is_string($input_text) ? sprintf("%s", $input_text) : '');
		$input_text = trim($input_text);
		if (empty($input_text)) {
			return false;
		}
		
		try {
			$iv_length = openssl_cipher_iv_length($this->base_signature['cipher']);
			$iv = openssl_random_pseudo_bytes($iv_length, $is_strong);
				
			$raw = openssl_encrypt($input_text, $this->base_signature['cipher'], $key, OPENSSL_RAW_DATA, $iv);
			$hmac = hash_hmac($this->base_signature['hash_method'], $raw, $key, $this->base_signature['binary_status']);
			$string = sprintf("%s%s%s", 
				$iv,
				$hmac,
				$raw
			);
			$base64_string = base64_encode($string);
			return $base64_string;
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function decrypt_signature_string(String $input_base64, String $key = '') {
		if (empty($key)) {
			$key = md5($this->base_auth['app']['client_secret']);
		}
		if (!isset($input_base64)) {
			return false;
		}
		
		try {
			$string_text = base64_decode($input_base64);
			$iv_length = openssl_cipher_iv_length($this->base_signature['cipher']);
			$iv = substr($string_text, 0, $iv_length);
			$hmac = substr($string_text, $iv_length, $this->base_signature['key_length']);
			$raw = substr($string_text, ($iv_length + $this->base_signature['key_length']));
			$decrypt_string = openssl_decrypt($raw, $this->base_signature['cipher'], $key, OPENSSL_RAW_DATA, $iv);
			
			$equal_hmac = hash_hmac($this->base_signature['hash_method'], $raw, $key, $this->base_signature['binary_status']);
			if (!hash_equals($hmac, $equal_hmac)) {
				return false;
			}
			return $decrypt_string;
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function create_hashed_password(String $input_text, String $key = '') {
		if (empty($key)) {
			$key = md5(Instance_config::$env_apc['base_auth']['apps']['client_secret']);
		}
		if (!isset($input_text)) {
			return false;
		}
		
		$input_text = (is_string($input_text) ? sprintf("%s", $input_text) : '');
		$input_text = trim($input_text);
		if (empty($input_text)) {
			return false;
		}
		try {
			$iv_length = openssl_cipher_iv_length($this->base_signature['cipher']);
			//$iv = openssl_random_pseudo_bytes($iv_length, $is_strong);
			$iv = substr(md5(uniqid()), 0, $iv_length);
				
			$raw = openssl_encrypt($input_text, $this->base_signature['cipher'], $key, FALSE, $iv);
			$base64_string = base64_encode($raw);
			return [
				'password'		=> $input_text,
				'iv'			=> base64_encode($iv),
				'encrypted'		=> $base64_string,
			];
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function create_signature_string_with_text_base64iv_key(String $input_text, String $base64iv, String $key = '') {
		if (empty($key)) {
			$key = md5(ConstantConfig::$rc4_encrypt_key);
		}
		$input_text = (is_string($input_text) ? sprintf("%s", $input_text) : '');
		if (empty($input_text)) {
			return false;
		}
		try {
			$iv_length = openssl_cipher_iv_length($this->base_signature['cipher']);
			$iv = substr(base64_decode($base64iv), 0, $iv_length);
				
			$raw = openssl_encrypt($input_text, $this->base_signature['cipher'], $key, FALSE, $iv);
			$base64_string = base64_encode($raw);
			return [
				'input_text'	=> $input_text,
				'iv'			=> base64_encode($iv),
				'encrypted'		=> $base64_string,
			];
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	public function create_hashed_string_with_params(String $input_text, String $key = '', String $iv = '') {
		if (empty($key)) {
			$key = md5(Instance_config::$env_apc['base_auth']['apps']['client_secret']);
		}
		if (!isset($input_text)) {
			return false;
		}
		
		$input_text = (is_string($input_text) ? sprintf("%s", $input_text) : '');
		$input_text = trim($input_text);
		if (empty($input_text)) {
			return false;
		}
		try {
			$iv_length = openssl_cipher_iv_length($this->base_signature['cipher']);
			$iv = substr($iv, 0, $iv_length);
				
			$raw = openssl_encrypt($input_text, $this->base_signature['cipher'], $key, FALSE, $iv);
			$base64_string = base64_encode($raw);
			return [
				'password'		=> $input_text,
				'iv'			=> base64_encode($iv),
				'encrypted'		=> $base64_string,
			];
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function decrypt_hashed_string_with_params(String $input_base64, String $key = '', String $iv = '') {
		if (empty($key)) {
			$key = md5(Instance_config::$env_apc['base_auth']['apps']['client_secret']);
		}
		if (!isset($input_base64)) {
			return false;
		}
		
		try {
			$string_text = base64_decode($input_base64);
			$iv_length = openssl_cipher_iv_length($this->base_signature['cipher']);
			$iv = substr($iv, 0, $iv_length);
			$decrypt_string = openssl_decrypt($string_text, $this->base_signature['cipher'], $key, FALSE, $iv);
			return $decrypt_string;
		} catch (Exception $e) {
			throw $e;
		}
	}
}
