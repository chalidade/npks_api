<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class GateIn extends CI_Controller {

  private $secret = 'this is key secret';

  public function __construct(){
		parent::__construct();
		$this->load->model('m_gateTruck');
    $this->load->model('m_gateContainer');
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
      echo '<h2>you are not allowed access to this URL<h2>';
      die();
    }

	}

  public function getGateIn(){
    $data = $this->m_gateContainer->getGate();
    if(isset($_REQUEST['callback'])){
      $callback = $_REQUEST['callback'];

      if ($callback) {
      header('Content-Type: text/javascript');
      echo $callback . '(' . json_encode($data) . ');';
      } else {
          header('Content-Type: application/x-json');
          echo json_encode($data);
      }
    }
    else{
      echo json_encode($data);
    }
  }

  public function getRecContNo(){
    $filter = isset($_GET['query']) ? $_GET['query'] : 0;
    $data = $this->m_gateContainer->getRecContNo($filter);
    if(isset($_REQUEST['callback'])){
      $callback = $_REQUEST['callback'];

      if ($callback) {
      header('Content-Type: text/javascript');
      echo $callback . '(' . json_encode($data) . ');';
      } else {
          header('Content-Type: application/x-json');
          echo json_encode($data);
      }
    }
    else{
      echo json_encode($data);
    }
  }

  public function getRecContDoubleNo(){
    $filter = isset($_GET['query']) ? $_GET['query'] : 0;
    $data = $this->m_gateContainer->getRecContDoubleNo($filter);
      header('Content-Type: text/javascript');
      die(json_encode($data));
  }

  public function getGateInConById(){
      $data = $this->m_gateContainer->getGateInConById();
      echo json_encode(array('data' => $data));
  }

  public function setGateInContainer(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $data = $this->m_gateContainer->setGateInContainer();

        $return = $data;
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

  public function getTruckActivities(){
    $data = $this->m_gateTruck->getTruckActivities();
    if(isset($_REQUEST['callback'])){
      $callback = $_REQUEST['callback'];
      if ($callback) {
      header('Content-Type: text/javascript');
      echo $callback . '(' . json_encode($data) . ');';
      } else {
          header('Content-Type: application/x-json');
          echo json_encode($data);
      }
    }
    else{
      echo json_encode($data);
    }
  }

  public function getGateInTruckCardStripping(){
    $filter = isset($_GET['query']) ? $_GET['query'] : 0;
    $data = $this->m_gateTruck->getGateInTruckCardStripping($filter);
    if(isset($_REQUEST['callback'])){
      $callback = $_REQUEST['callback'];

      if ($callback) {
      header('Content-Type: text/javascript');
      echo $callback . '(' . json_encode($data) . ');';
      } else {
          header('Content-Type: application/x-json');
          echo json_encode($data);
      }
    }
    else{
      echo json_encode($data);
    }
  }

  public function getGateInTruckCardStuffing(){
    $filter = isset($_GET['query']) ? $_GET['query'] : 0;
    $data = $this->m_gateTruck->getGateInTruckCardStuffing($filter);
    if(isset($_REQUEST['callback'])){
      $callback = $_REQUEST['callback'];

      if ($callback) {
      header('Content-Type: text/javascript');
      echo $callback . '(' . json_encode($data) . ');';
      } else {
          header('Content-Type: application/x-json');
          echo json_encode($data);
      }
    }
    else{
      echo json_encode($data);
    }
  }

  public function getGateInTruckStrippById(){
      $data = $this->m_gateTruck->getGateTruckStrippById();
      echo json_encode(array('data' => $data));
  }

  public function getGateInTruckStuffById(){
      $data = $this->m_gateTruck->getGateTruckStuffById();
      echo json_encode(array('data' => $data));
  }

  public function setGateInTruck(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $data = $this->m_gateTruck->setGateTruck();

        $return = array('success' => true, 'message' => $data);
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

  public function jobGateManager(){
    $data = $this->m_gateContainer->getGateJobManager();
    header('Content-Type: text/javascript');
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

  public function test1(){
    $data = $this->m_gateContainer->test1();
    header('Content-Type: text/javascript');
    print_r($data);
  }

  public function truck_job_gate_manager(){

    $filter = array(
                    $this->session->userdata('USER_BRANCH'),
                    $this->input->get('start') + $this->input->get('limit'),
                    $this->input->get('start'),
                    $this->input->post('CONTAINER_NUMBER'),
                    $this->input->post('GATE_TRUCK_NO'),
                    $this->input->post('REQUEST_NUMBER')
                  );
    $data = $this->m_gateTruck->getTruckJobGateManager($filter);
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function rePrint(){
    $data = $this->m_gateContainer->rePrintGate();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function ownerCheck(){
    $data = $this->m_gateContainer->ownerCheck();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

}
