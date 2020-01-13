<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class GateReport extends CI_Controller {

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

  public function getGateReport(){
    $data = $this->m_gateContainer->getGateReport();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function getGateExport(){
    $this->load->library('m_pdf');
    $activity = $this->m_gateContainer->getReportActivity($_GET['activity']);
    $data['activity'] = $activity != null?  $activity : 'All';
		$date1 = $_GET['date1'] != null? date('d/m/Y',strtotime($_GET['date1'])) : date('d/m/Y');
		$date2 = $_GET['date2'] != null? date('d/m/Y',strtotime($_GET['date2'])) :  date('d/m/Y');
    $data['branch'] = $this->m_master->get_branch();
    $data['date1'] = $date1;
    $data['date2'] = $date2;
    $name = 'Report_gate_'.$date1.'-'.$date2;
    $data['data'] = $this->m_gateContainer->getGateExport();
    // print_r($data); die();
    $html = $this->load->view('gateExport',$data,true);
    //rite some HTML code:
    $this->m_pdf->export->WriteHTML($html);
    $this->m_pdf->export->Output($name,'I');

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
