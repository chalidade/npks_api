<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class Disable_cell extends CI_Controller {
  private $secret = 'this is key secret';
  public function __construct(){
		parent::__construct();
		$this->load->model('m_disablecell');
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
      echo '<h2>you are not allowed access to this URL<h2>';
      die();
    }

	}

  public function index(){
	   $data = $this->m_disablecell->getTier();
     die(json_encode($data));
     // print_r($data);
	}

  public function setDisable(){

      if($id = $this->check_token()){
          if($id == $this->session->userdata('isId')){
            $return = $this->m_disablecell->setDisable();
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
