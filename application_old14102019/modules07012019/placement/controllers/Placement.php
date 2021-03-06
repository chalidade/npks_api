<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class Placement extends CI_Controller {

  private $secret = 'this is key secret';

  public function __construct(){
		parent::__construct();
		$this->load->model('m_placement');
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
      echo '<h2>you are not allowed access to this URL<h2>';
      die();
    }

	}

  public function insertPlacement(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $insert = $this->m_placement->setPlacement();
        $return = $insert;
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

  public function getContainer(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_placement->getContainer();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getContainerById(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $return = $this->m_placement->getContainerById();
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

  public function ceTallyRun(){
    $return = $this->m_placement->ceTallyRun();

    $data = array(
      'success'=>true,
      'status' => $return
    );
    echo json_encode($data);
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

  public function getYbcId(){
    $data = $this->m_placement->getYbcId();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function getLastPlacement(){
    $data = $this->m_placement->getLastPlacement();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function generateCopyyard(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $insert = $this->m_placement->generateCopyyard();
        $return = $insert;
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

}
