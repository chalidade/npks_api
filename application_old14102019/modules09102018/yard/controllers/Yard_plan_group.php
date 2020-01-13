<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Yard_plan_group extends CI_Controller {
  public function __construct(){
		parent::__construct();
		$this->load->model('m_yard');
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
			echo '<h2>you are not allowed access to this URL<h2>';
			die();
		}
    
  }

  public function delete_yard_plan_group(){
		$data	= $this->m_yard->delete_yard_plan_group();
		echo $data;
	}

}
