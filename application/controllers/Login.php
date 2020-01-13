<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class Login extends CI_Controller {

  private $secret = 'this is key secret';
  private $captcha = '';

  public function __construct(){
		parent::__construct();
    $this->load->model('m_login');
	}

  public function auth() {
    $date = new DateTime();
    if(strtolower($this->session->userdata('random_number')) == strtolower($this->input->post('captcha'))){
            if($this->m_login->do_login()->num_rows()==1){
              $arrData = $this->m_login->do_login()->row_array();
              if(password_verify($this->input->post('password'),$arrData['USER_PASSWD'])){
                $arrSession = array(
                  'isLogin'=>TRUE,
                  'isId'=>$arrData['USER_ID'],
                  'USER_NAME'=>$arrData['USER_NAME'],
                  'FULL_NAME'=>$arrData['FULL_NAME'],
                  'USER_ROLE'=>$arrData['USER_ROLE'],
                  'USER_NIK'=>$arrData['USER_NIK'],
                  'USER_BRANCH'=>$arrData['USER_BRANCH_ID'],
                  'YARD_ACTIVE'=>$arrData['USER_YARD']
                );
                $this->session->set_userdata($arrSession);

                $payload['id']    = $arrData['USER_ID'];
                $payload['iat']   = $date->getTimestamp();
                $payload['exp']   = $date->getTimestamp() + 60*60*24;
                $token = JWT::encode($payload, $this->secret);

                $arrData = array(
                  'success' => true,
                  'message' => 'Login Sukses..',
                  'token' => $token,
                  'roleID' => $arrData['USER_ROLE'],
                  'NAME'=>$arrData['FULL_NAME'],
                  'branch'=>$arrData['USER_BRANCH_ID']
                );
                echo json_encode($arrData);
              }else {
                 $arrData = array(
                   'success' => false,
                   'message' => 'Password salah',
                 );
                 echo json_encode($arrData);
              }
            }
            else{
              $arrData = array(
                'success' => false,
                'message' => 'Username tidak terdaftar',
              );
              echo json_encode($arrData);
            }
        }
        else {
          $arrData = array(
            'success' => false,
            'message' => 'Wrong captcha',
          );
          echo json_encode($arrData);
        }

  }

  public function password_hash($password) {
    $options = array(
    'cost' => 12,
    );
    $hash = password_hash($password, PASSWORD_BCRYPT, $options);
    echo $hash;
  }

  public function loginCheck(){
    if($this->session->userdata('isLogin')){
      $arrData = array(
        'LOGIN' => true,
      );
    }
    else{
      $arrData = array('LOGIN' => false);
    }
    echo json_encode($arrData);
  }

  public function logout(){
    $this->session->sess_destroy();
    echo json_encode(array('success' => true));
  }

  public function testAjax(){
    echo $this->load->view('textAjax');
  }


  // public function recapcha(){
  //   $recaptcha = $this->input->get_request_header('recaptcha');
  //   $key = '6LeTy1sUAAAAAE6T444bRDQP_R4tkPlexOTC5sGl';
  //   $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$key."&response=".$recaptcha);
  //   $arrData = json_decode($response);
  //   $this->session->set_flashdata('captcha',$arrData->success);
  //   echo $response;
  // }

}
