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
    // $data['tab_id'] = $_POST['tab_id'];
		// $data['id_yard'] = $_POST['id_yard'];


        $id_yard = $this->input->post('yard');
        $block = $this->input->post('block');

        // $id_yard = 203;
        // $block = 362;
        $slot = 1;
        $row = 1;

        $data['block_data'] = $this->m_yard->get_slot_config($id_yard, $block);

        $data['cont_data'] = $this->m_yard->get_container_report($id_yard, $block);
        // print_r($data); die();
        $data['yard'] = $id_yard;
        $data['block'] = $block;

		$this->load->view('yard_report/yard_report', $data);
	}

}
