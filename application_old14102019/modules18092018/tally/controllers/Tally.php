<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tally extends CI_Controller {
  public function __construct(){
		parent::__construct();
		$this->load->model('m_tally');
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
      echo '<h2>you are not allowed access to this URL<h2>';
      die();
    }
    
	}

  public function insertTally(){
    $insert = $this->m_tally->setTally();

    $data = array(
			'success'=>true,
			'errors'=> $insert
		);

    echo json_encode($data);
  }

  public function getReasonStatus(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_tally->getReasonStatus();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function insertTallyHold(){
    $insert = $this->m_tally->setTallyHold();

    $data = array(
      'success'=>true,
      'errors'=> $insert
    );

    echo json_encode($data);
  }

  public function ceTallyRun(){
    $return = $this->m_tally->ceTallyRun();

    $data = array(
      'success'=>true,
      'status' => $return
    );
    echo json_encode($data);
  }

}
