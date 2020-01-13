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

        $data['block_data'] = $this->m_yard->get_slot_config($id_yard, $block);

        $data['cont_data'] = $this->m_yard->get_container_report($id_yard, $block);
        // print_r($data); die();
        $data['yard'] = $id_yard;
        $data['block'] = $block;

		$this->load->view('yard_report/yard_report', $data);
	}

  public function export(){
    $id_yard = $this->input->get('yard');
    $block = $this->input->get('block');

    $data['branch'] = $this->m_master->get_branch();
    $data['block_data'] = $this->m_yard->get_slot_config($id_yard, $block);
    $data['cont_data'] = $this->m_yard->get_container_report($id_yard, $block);
    $data['yard_name'] = $this->m_yard->get_yard_name($id_yard);
    $data['block_name'] = $this->m_yard->get_block_name($id_yard,$block);
    // print_r($data); die();
    $data['yard'] = $id_yard;
    $data['block'] = $block;

    $name = 'yard_report';
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$name.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $this->load->view('yard_report/yard_report_export', $data);
  }

}
