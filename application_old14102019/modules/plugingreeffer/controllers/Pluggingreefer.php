<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class Pluggingreefer extends CI_Controller {

  private $secret = 'this is key secret';

  public function __construct(){
		parent::__construct();
		$this->load->model('m_pluggingreefer');

    if(!$this->session->userdata('isLogin')){
      echo '<h2>you are not allowed access to this URL<h2>';
      die();
    }

	}

  public function getContainer(){
    $data = $this->m_pluggingreefer->getContainer();
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

  public function getContainerById(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $return = $this->m_pluggingreefer->getContainerById();
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

  public function cekStuffingRun(){
    $return = $this->m_pluggingreefer->cekStuffingRun();
    header('Content-Type: text/javascript');
    die(json_encode($return));
  }

  public function insertStuffing(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $data = $this->m_pluggingreefer->setStuffing();
        $return = array(
          'success' => true,
          'message' => $data
        );
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
