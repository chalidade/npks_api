<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Yard_builder extends CI_Controller {
  public function __construct(){
		parent::__construct();
		$this->load->model('m_yard');
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
      echo '<h2>you are not allowed access to this URL<h2>';
      die();
    }

	}

  public function index(){
		$data['tab_id'] = $_GET['tab_id'];
		$this->load->view('content/yard/yard_builder/builder_config', $data);
	}

  public function yard_config() {
    $data['tab_id'] = $_GET['tab_id'];
    $data['width']  = $_POST["width"];
    $data['height'] = $_POST["height"];
    $this->load->view('content/yard/yard_builder/yard_builder',$data);
  }

  public function yard_editor(){
      $data['tab_id'] = $_GET['tab_id'];
      $this->load->view('content/yard/yard_builder/yard_builder',$data);
  }

  public function create_yard(){
		$xmlData = $_POST['xml_'];
		$sName = $_POST['yard_name'];
		$retval = $this->insert_yard($xmlData, $sName);
		echo $retval;
	}

  public function insert_yard($xml_str, $yard_name){
		$data = $this->m_yard->set_yard($xml_str,$yard_name);
    return $data;
  }

  public function builder(){
    $this->load->view('yard_builder/builder');
  }

}
