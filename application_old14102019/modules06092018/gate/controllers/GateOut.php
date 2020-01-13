<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class GateOut extends CI_Controller {

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

  public function getGateOut(){
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

  public function getDelContNo(){
    $filter = isset($_GET['query']) ? $_GET['query'] : 0;
    $data = $this->m_gateContainer->getDelContNo($filter);
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

  public function getNoTruckGateOut(){
    $filter = isset($_GET['query']) ? $_GET['query'] : 0;
    $data = $this->m_gateContainer->getNoTruckGateOut($filter);
    header('Content-Type: text/javascript');
    die(json_encode($data));

  }

  public function getGateOutConById(){
      $data = $this->m_gateContainer->getGateOutConById();
      echo json_encode(array('data' => $data));
  }

  public function setGateOutContainer(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $data = $this->m_gateContainer->setGateOutContainer();
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

  public function getGateOutTruckCardStripping(){
    $filter = isset($_GET['query']) ? $_GET['query'] : 0;
    $data = $this->m_gateTruck->getGateOutTruckCardStripping($filter);
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

  public function getGateOutTruckCardStuffing(){
    $filter = isset($_GET['query']) ? $_GET['query'] : 0;
    $data = $this->m_gateTruck->getGateOutTruckCardStuffing($filter);
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

  public function getGateOutTruckStrippById(){
      $data = $this->m_gateTruck->getGateTruckStrippById();
      echo json_encode(array('data' => $data));
  }

  public function getGateOutTruckStuffById(){
      $data = $this->m_gateTruck->getGateTruckStuffById();
      echo json_encode(array('data' => $data));
  }

  public function setGateOutTruck(){
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

  public function getGateInTruckStrip(){
    $data = $this->m_gateTruck->getGateInTruckStrip(1,2);
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function getGateInTruckStuff(){
    $data = $this->m_gateTruck->getGateInTruckStrip(2,2);
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


}
