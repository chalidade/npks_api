<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;
class Container extends CI_Controller {
	private $secret = 'this is key secret';
  	public function __construct(){
		parent::__construct();
     	$this->load->model('m_container');

	    if(!$this->session->userdata('isLogin')){
			echo '<h2>you are not allowed access to this URL<h2>';
			die();
		}
		
		
	}

	public function index(){
		$filter = array($this->input->post('CONTAINER_NUMBER'), $this->session->userdata('USER_BRANCH') );
		$dataContainer = $this->m_container->get_data_container_inquiry($filter);

		header('Content-Type: text/javascript');
		
		die(json_encode($dataContainer));
	}

	public function container_history(){
		$filter = array($this->input->post('CONTAINER_NUMBER'), $this->session->userdata('USER_BRANCH') );
		$dataContainer = $this->m_container->get_data_container_history($filter);

		header('Content-Type: text/javascript');
		
		die(json_encode($dataContainer));
	}

	public function container_list(){
		$filter = array($this->session->userdata('USER_BRANCH') );
		$dataContainer = $this->m_container->get_all_container_list($filter);

		header('Content-Type: text/javascript');
		
		die(json_encode($dataContainer));
	}

	public function stuffing_history(){
		$callback = isset($request['callback']) ? $request['callback'] : false;
        
        $filter['BRANCH_ID']		= $this->session->userdata('USER_BRANCH');
        $filter['CONTAINER_NUMBER']	= strtolower($this->input->get('CONTAINER_NUMBER')); 
        $filter['REQUEST_NUMBER'] 	= strtolower($this->input->get('REQUEST_NUMBER'));
        
        $data = $this->m_container->getStuffingHistory($filter);

        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . json_encode($data) . ');';
        } else {
            header('Content-Type: application/x-json');
            echo json_encode($data);
        }
	}

	public function stripping_history(){
		$callback = isset($request['callback']) ? $request['callback'] : false;
        
        $filter['BRANCH_ID']		= $this->session->userdata('USER_BRANCH');
        $filter['CONTAINER_NUMBER']	= strtolower($this->input->get('CONTAINER_NUMBER')); 
        $filter['REQUEST_NUMBER'] 	= strtolower($this->input->get('REQUEST_NUMBER'));
        
        $data = $this->m_container->getStrippingHistory($filter);

        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . json_encode($data) . ');';
        } else {
            header('Content-Type: application/x-json');
            echo json_encode($data);
        }
	}
}