<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class Bm_convensional extends CI_Controller {

  private $secret = 'this is key secret';

  public function __construct(){
		parent::__construct();
		$this->load->model('m_bm_convensional');
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
      echo '<h2>you are not allowed access to this URL<h2>';
      die();
    }

	}

  public function data_branch(){
      $data = $this->m_bm_convensional->get_branch();
      echo json_encode($data);
  }

  //discharge function hdr
  public function discharge_plan(){
    $data['tab_id'] = $_GET['tab_id'];
    $this->load->view('content/bm_convensional/discharge_plan/dischard_plan',$data);
  }

  public function add_discharge_plan(){
    $data['tab_id'] = $_GET['tab_id'];
    $data['sNew_hdr_no'] = $this->generate_discharge_no();
    $this->load->view('content/bm_convensional/discharge_plan/add_discharge_plan',$data);
  }

  public function add_discharge_container(){
    $data['tab_id'] = $_GET['tab_id'];
    $this->load->view('content/bm_convensional/discharge_plan/add_container',$data);
  }

  public function get_discharge_plan_hdr(){
    $data = $this->m_bm_convensional->get_arr_discharge_plan_hdr();
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

  public function get_discharge_plan_hdr_by_id($idHdr = NULL){
    $data = $this->m_bm_convensional->get_arr_discharge_plan_hdr_by_id($idHdr);
    header('Content-Type: text/javascript');
    echo json_encode($data);
  }

  public function get_discharge_container($idCont){
    $data = $this->m_bm_convensional->get_arr_discharge_container($idCont);
    header('Content-Type: text/javascript');
    echo json_encode($data);
  }

  public function generate_discharge_no(){
    $data = $this->m_bm_convensional->generate_discharge_no();
    $data['MAX_HDR_NO'];
    $branch_code = '03';
    if($data['MAX_HDR_NO'] == null){
      $noUrut = 1;
    }else{
      $noUrut = (int) substr($data['MAX_HDR_NO'], 7, 6);
      $noUrut++;
    }
    $sNew_hdr_no = "DC-" .$branch_code. "-" . sprintf("%06s", $noUrut);

    echo json_encode($sNew_hdr_no);
  }

  public function insert_discharge_plan(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $insert = $this->m_bm_convensional->set_dishcharge_plan();
        $data = array(
    			'success' => true,
    			'errors' => $insert
    		);
      }
      else{
        $data = array(
    			'success' => false,
    			'errors' => 'error authentication'
    		);
      }
    }
    else{
      $data = array(
  			'success' => false,
  			'errors' => 'error authentication'
  		);
    }
    echo json_encode($data);
  }

  //dishcrge function detail
  public function get_detail_discharge(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_bm_convensional->get_detail_discharge();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function insert_discharge_detail(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $insert = $this->m_bm_convensional->set_discharge_detail();
        $data = array(
    			'success'=>true,
    			'message'=> $insert
    		);
      }
      else{
        $data = array(
          'success' => false,
          'errors' => 'error authentication'
        );
      }
    }
    else{
      $data = array(
        'success' => false,
        'errors' => 'error authentication'
      );
    }
    echo json_encode($data);
  }

  public function delete_discharge_detail(){
    $arrIdCont = explode(",",$_POST['id']);
    $delete = $this->m_bm_convensional->exec_discharge_detail($arrIdCont);
    echo $delete;
  }

  public function getPlanNoDisc(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_bm_convensional->getPlanNoDisc();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getEquipmentDisc(){
    $data = $this->m_bm_convensional->getEquipmentDisc();
    echo json_encode($data);
  }

  //FUNCTION FOR LAODING PLAN
  public function getPlanNoLoad(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_bm_convensional->getPlanNoLoad();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getEquipmentLoad(){
    $data = $this->m_bm_convensional->getEquipmentLoad();
    echo json_encode($data);
  }

  public function loading_plan(){
    $data['tab_id'] = $_GET['tab_id'];
    $this->load->view('content/bm_convensional/loading_plan/loading_plan',$data);
  }

  public function add_loading_plan(){
    $data['tab_id'] = $_GET['tab_id'];
    $data['sNew_hdr_no'] = $this->generate_loading_no();
    $this->load->view('content/bm_convensional/loading_plan/add_loading_plan',$data);
  }

  public function add_loading_container(){
    $data['tab_id'] = $_GET['tab_id'];
    $this->load->view('content/bm_convensional/loading_plan/add_loading_container',$data);
  }

  public function get_loading_plan_hdr(){
    $data = $this->m_bm_convensional->get_arr_loading_plan_hdr();
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

  public function get_loading_plan_hdr_by_id($idHdr = NULL){
    $data = $this->m_bm_convensional->get_arr_loading_plan_hdr_by_id($idHdr);
    echo json_encode($data);
  }

  public function get_loading_container($idCont){
    $data = $this->m_bm_convensional->get_arr_loading_container($idCont);
    header('Content-Type: text/javascript');
    echo json_encode($data);
  }

  public function generate_loading_no(){
    $data = $this->m_bm_convensional->generate_loading_no();
    $data['MAX_HDR_NO'];
    $branch_code = '03';
    if($data['MAX_HDR_NO'] == null){
      $noUrut = 1;
    }else{
      $noUrut = (int) substr($data['MAX_HDR_NO'], 7, 6);
      $noUrut++;
    }
    $sNew_hdr_no = "LO-" .$branch_code. "-" . sprintf("%06s", $noUrut);
    echo json_encode($sNew_hdr_no);
  }

  public function insert_loading_plan(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $insert = $this->m_bm_convensional->set_loading_plan();

        $data = array(
    			'success'=>true,
    			'errors'=> $insert
    		);
      }
      else{
        $data = array(
          'success' => false,
          'errors' => 'error authentication'
        );
      }
    }
    else{
      $data = array(
        'success' => false,
        'errors' => 'error authentication'
      );
    }
    echo json_encode($data);
  }

  //loading function detail
  public function get_detail_loading(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_bm_convensional->get_detail_loading();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function insert_loading_detail(){
    if($id = $this->check_token()){
      if($id == $this->session->userdata('isId')){
        $insert = $this->m_bm_convensional->set_loading_detail();

        $data = array(
    			'success'=>true,
    			'message'=> $insert
    		);
      }
      else{
        $data = array(
          'success' => false,
          'message' => 'error authentication'
        );
      }
    }
    else{
      $data = array(
        'success' => false,
        'message' => 'error authentication'
      );
    }
    echo json_encode($data);
  }

  public function delete_loading_detail(){
    $arrIdCont = explode(",",$_POST['id']);
    $delete = $this->m_bm_convensional->exec_loading_detail($arrIdCont);
    echo $delete;
  }

  //TALLY
  public function getTallyDiscContainer(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_bm_convensional->getTallyDiscContainer();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getTallyLoadContainer(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_bm_convensional->getTallyLoadContainer();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function cekLogin(){
    if(!$this->session->userdata('isLogin')){
      return false;
    }
    return true;
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
