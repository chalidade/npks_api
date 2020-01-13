<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Yard_editor extends CI_Controller {
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
		$this->load->view('content/yard/yard_editor/yard_list', $data);
	}

  public function yard_editor(){
      $data['id_yard'] = $_POST['id_yard'];
      $data['tab_id'] = $_POST['tab_id'];
      $xmlData = $this->m_yard->extract_yard($data['id_yard']);
      // print_r($xmlData);die();
      $data_yard = simplexml_load_string($xmlData);
  		// print_r($data_yard); die();
  		$data['width']  	= $data_yard->WIDTH;
  		$data['height'] 	= $data_yard->HEIGHT;
  		$data['name'] 		= $data_yard->NAME;
  		$data['index'] 		= explode(",",$data_yard->INDEX);
  		$data['slot_'] 		= explode(",",$data_yard->SLOT);
  		$data['row_'] 		= explode(",",$data_yard->ROW);
  		$data['block'] 		= $data_yard->BLOCK;
  		$data['block_sum'] 	= count($data['block']);
      $data['block_id'] = explode(",",$data_yard->BLOCK_ID);
      $this->load->view('yard_editor/yardEditor',$data);
  }

  public function get_yard_list(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->get_yard_list();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_block_list(){
    $callback = $_REQUEST['callback'];
    $yard_id = $_GET['id_yard'];
    $data = $this->m_yard->get_block_list($yard_id);
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function update_yard(){
    $xml_str = $_POST['xml_'];
    // print_r($xml_str); die();
    $yard_name = $_POST['yard_name'];
    $id_yard = $_GET['id_yard'];
    $data = $this->m_yard->update_yard($xml_str,$yard_name,$id_yard);
    echo $data;
  }

  public function get_yard_config_list(){
    $data['tab_id'] = $_GET['tab_id'];
		$this->load->view('content/yard/yard_editor/yard_config_list', $data);
  }
  public function yard_config(){
    $data['tab_id'] = $_GET['tab_id'];
    $data['id_yard'] = $_GET['id_yard'];
		$this->load->view('content/yard/yard_editor/yard_config', $data);
  }

  public function update_yard_block($yard_id, $block_id){
    $data = $this->m_yard->update_yard_block($yard_id, $block_id);
    echo $data;
  }

  public function getYardBlock(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getYardBlock();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getYardRow(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getYardRow();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getYardSlot(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getYardSlot();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getYardTiers(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getYardTiers();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getYardTier(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getYardTier();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_yard_user(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getYardUser();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

}
