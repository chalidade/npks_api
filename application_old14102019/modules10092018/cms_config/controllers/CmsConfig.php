<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;
class CmsConfig extends CI_Controller {
	private $secret = 'this is key secret';
  	public function __construct(){
		parent::__construct();
     	$this->load->model('m_cmsconfig');

	    if(!$this->session->userdata('isLogin')){
			echo '<h2>you are not allowed access to this URL<h2>';
			die();
		}
	}

	public function index(){
	    $data = $this->m_cmsconfig->getCmsConfig();
	    $this->load->view('cms_config',$data,true);
	}

	public function setConfig(){
		$data = $this->m_cmsconfig->setConfig();
		die(json_encode($data));
	}

	public function set_variable_value(){
		

	    if($id = $this->check_token()){
	      	if($id == $this->session->userdata('isId')){
		        $return = $this->m_cmsconfig->setConfigVariables();        
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_variable(){
		$data = $this->m_cmsconfig->getAllVariables();
		die(json_encode($data));
	}

	public function check_token()
	{
		$jwt = $this->input->get_request_header('auth');
		try {
			$decoded = JWT::decode($jwt, $this->secret, array('HS256'));
			return $decoded->id;
		} catch(\Exception $e) {
			return false;
		}
	}

}
