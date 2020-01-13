<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Yard_report extends CI_Controller {
  public function __construct(){
		parent::__construct();
		$this->load->model('m_yard');
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
			echo '<h2>you are not allowed access to this URL<h2>';
			die();
		}

  }

    public function index(){
        $id_yard = $this->input->post('yard');
        $block = $this->input->post('block');
        $slot = $this->input->post('slot');

        $data['block_data'] = $this->m_yard->get_slot_config($id_yard, $block, $slot);

        $data['cont_data'] = $this->m_yard->get_container_report($id_yard, $block, $slot);
        // print_r($data); die();
        $data['yard'] = $id_yard;
        $data['block'] = $block;
        $data['slot'] = $slot;

		$this->load->view('yard_report/yard_report', $data);
	}

  public function export(){
    $id_yard = $this->input->get('yard');
    $block = $this->input->get('block');
    $slot = $this->input->get('slot');

    $data['branch'] = $this->m_master->get_branch();
    $data['block_data'] = $this->m_yard->get_slot_config($id_yard, $block);
    $data['cont_data'] = $this->m_yard->get_container_report($id_yard, $block);
    $data['yard_name'] = $this->m_yard->get_yard_name($id_yard);
    $data['block_name'] = $this->m_yard->get_block_name($id_yard,$block);
    // print_r($data); die();
    $data['yard'] = $id_yard;
    $data['block'] = $block;
    $data['slot'] = $slot;

    $date = date('dmY');
    $name = 'yard_report_'.$date;
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$name.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $this->load->view('yard_report/yard_report_export', $data);
  }

  public function reportYor(){
    $data = $this->m_yard->reportYor();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function reportYorExport(){
    $bulan = array(
      '01' => 'JANUARI',
      '02' => 'FEBRUARI',
      '03' => 'MARET',
      '04' => 'APRIL',
      '05' => 'MEI',
      '06' => 'JUNI',
      '07' => 'JULI',
      '08' => 'AGUSTUS',
      '09' => 'SEPTEMBER',
      '10' => 'OKTOBER',
      '11' => 'NOVEMBER',
      '12' => 'DESEMBER',
    );

    $data['branch'] = $this->m_master->get_branch();
    $data['date1'] = $_GET['date'] != null? date('d/m/Y',strtotime($_GET['date'])) : date('d/m/Y');
    $month = $_GET['date'] != null? date('m',strtotime($_GET['date'])) : date('m');
    $year = $_GET['date'] != null? date('Y',strtotime($_GET['date'])) : date('Y');
    $data['date2'] = 'BULAN '.strtoupper($bulan[$month]).' '.$year;
    $data['data'] = $this->m_yard->reportYorExport();
    $date = $_GET['date'] != null? date('d-m-Y',strtotime($_GET['date'])) : date('d-m-Y');
    $name = 'report_yor_'.$date;

    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$name.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $this->load->view('yard_report/yor_report',$data);
  }

  public function reportYorExportPdf(){
    $this->load->library('m_pdf');
    $bulan = array(
      '01' => 'JANUARI',
      '02' => 'FEBRUARI',
      '03' => 'MARET',
      '04' => 'APRIL',
      '05' => 'MEI',
      '06' => 'JUNI',
      '07' => 'JULI',
      '08' => 'AGUSTUS',
      '09' => 'SEPTEMBER',
      '10' => 'OKTOBER',
      '11' => 'NOVEMBER',
      '12' => 'DESEMBER',
    );

    $data['branch'] = $this->m_master->get_branch();
    $data['date1'] = $_GET['date'] != null? date('d/m/Y',strtotime($_GET['date'])) : date('d/m/Y');
    $month = $_GET['date'] != null? date('m',strtotime($_GET['date'])) : date('m');
    $year = $_GET['date'] != null? date('Y',strtotime($_GET['date'])) : date('Y');
    $data['date2'] = 'BULAN '.strtoupper($bulan[$month]).' '.$year;
    $data['data'] = $this->m_yard->reportYorExport();
    $date = $_GET['date'] != null? date('d-m-Y',strtotime($_GET['date'])) : date('d-m-Y');
    $name = 'Report_yor_'.$date.'.pdf';

    $html = $this->load->view('yard_report/yor_report_pdf',$data,true);
    $this->m_pdf->export->WriteHTML($html);
    $this->m_pdf->export->Output($name,'I');
  }

}
