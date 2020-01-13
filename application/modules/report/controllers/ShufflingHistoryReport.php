<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class ShufflingHistoryReport extends CI_Controller {

  private $secret = 'this is key secret';

  public function __construct(){
		parent::__construct();
		$this->load->model('m_shufflingReport');
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
      echo '<h2>you are not allowed access to this URL<h2>';
      die();
    }

	}

  public function index(){
    $data = $this->m_shufflingReport->getReport();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function reportExport(){
    $this->load->library('m_pdf');
		$date1 = $_GET['date1'] != null? date('d/m/Y',strtotime($_GET['date1'])) : date('d/m/Y');
		$date2 = $_GET['date2'] != null? date('d/m/Y',strtotime($_GET['date2'])) :  date('d/m/Y');
    $data['branch'] = $this->m_master->get_branch();
    $data['date1'] = $date1;
    $data['date2'] = $date2;
    $name = 'Report_Shuffling_'.$date1.'-'.$date2;
    $data['data'] = $this->m_shufflingReport->getReportExport();
    // print_r($data); die();
    $html = $this->load->view('shuffling/pdf',$data,true);
    //rite some HTML code:
    $this->m_pdf->export->WriteHTML($html);
    $this->m_pdf->export->Output($name,'I');

  }

  public function reportExcel(){
    $date1 = $_GET['date1'] != null? date('d/m/Y',strtotime($_GET['date1'])) : date('d/m/Y');
    $date2 = $_GET['date2'] != null? date('d/m/Y',strtotime($_GET['date2'])) :  date('d/m/Y');
    $data['branch'] = $this->m_master->get_branch();
    $data['date1'] = $date1;
    $data['date2'] = $date2;
    $name = 'Report_Shuffling_'.$date1.'-'.$date2;
    $data['data'] = $this->m_shufflingReport->getReportExport();
    // print_r($data); die();
    $html = $this->load->view('shuffling/excel',$data,true);
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$name.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $html;
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
