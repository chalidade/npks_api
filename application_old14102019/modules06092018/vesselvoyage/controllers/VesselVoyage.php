<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class VesselVoyage extends CI_Controller {

  private $secret = 'this is key secret';

  public function __construct(){
		parent::__construct();
		$this->load->model('m_vesselvoyage');
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
      echo '<h2>you are not allowed access to this URL<h2>';
      die();
    }

	}

  public function getVoyage(){
    $arrData = $this->m_vesselvoyage->getVoyage();
    header('Content-Type: text/javascript');
    die(json_encode($arrData));
  }

  public function setVoyage(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $data = $this->m_vesselvoyage->setVoyage();

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

    header('Content-Type: text/javascript');
    die(json_encode($return));

  }

  public function getVoyById(){
      $arrData = $this->m_vesselvoyage->getVoyById();
      header('Content-Type: text/javascript');
      die(json_encode($arrData));
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
