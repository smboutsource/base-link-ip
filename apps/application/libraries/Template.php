<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Librari ini dibuat untuk mempermudah layouting view pada -
 * Framework Codeigniter 3.x
 *
 * @package    CodeIgniter
 * @subpackage libraries
 * @category   library
 * @version    1.2
 * @since      2016
 * @author     iancikal <cikaldev@gmail.com>
 * @license    MIT License
 */

class Template
{
  public $data = array();

  public function __get($var)
  {
    return get_instance()->$var;
  }

  public function set($key, $val)
  {
    $this->data[$key] = $val;
  }

  public function load($template, $view, $data=array(), $return=false)
  {
    $this->set('content', $this->load->view($view, $data, true));
    $this->load->view($template, $this->data, $return);
  }
	
	// Add by lanister for footer scripts
	public function display(String $base_view, String $file_view, Array $data = [], Bool $return = FALSE) {
		try {
			$CI = &get_instance();
			if (!isset($data['file_view'])) {
				$data['file_view'] = $file_view;
				$CI->load->view($base_view, $data, FALSE);
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
}

/* End of file Template.php */
/* Location: ./application/libraries/Template.php */
