<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;
class Dashboard extends CI_Controller {
	private $secret = 'this is key secret';
  	public function __construct(){
		parent::__construct();
     	$this->load->model('m_dashboard');
     	$this->load->helper('url');

	    if(!$this->session->userdata('isLogin')){
			echo '<h2>you are not allowed access to this URL<h2>';
			die();
		}


	}
	public function index(){
		$data['dataAllReceiving'] = $this->m_dashboard->getDataReceiving();
		$data['dataAllSp2'] = $this->m_dashboard->getDataSp2();
		//var_dump($data['dataAllReceiving']);
		$this->load->view('receiving_html', $data);
	}

	public function get_data_receiving_json(){
		header("Content-type: text/json");
		$data = $this->m_dashboard->getDataReceiving();
		echo json_encode($data);
	}

	public function getDataSp2(){
		header("Content-type: text/json");
		$data = $this->m_dashboard->getDataSp2();
		echo json_encode($data);
	}

	public function getDataRepo(){
		header("Content-type: text/json");
		$data = $this->m_dashboard->getDataRepo();
		echo json_encode($data);
	}

	public function getDataStuffing(){
		header("Content-type: text/json");
		$data = $this->m_dashboard->getDataStuffing();
		echo json_encode($data);
	}

	public function getDataStripping(){
		header("Content-type: text/json");
		$data = $this->m_dashboard->getDataStripping();
		echo json_encode($data);
	}

	public function getDataYor(){
		header("Content-type: text/json");
		$data = $this->m_dashboard->getDataYor();
		echo json_encode($data);
	}

}
