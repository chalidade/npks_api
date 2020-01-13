<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master extends CI_Controller {

  public function __construct(){
		parent::__construct();
    $this->load->model('m_master');

    if(!$this->session->userdata('isLogin')){
			echo '<h2>you are not allowed access to this URL<h2>';
			die();
		}

	}

  public function get_reff_master(){
    $data = $this->m_master->get_arr_reff_master();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function get_consignee(){
    $filter = isset($_GET['query']) ? $_GET['query'] : 0;
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_consignee($filter);
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_equipment_job($reffID = null){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_equipment_job($reffID);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_type_bm_conv(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(3);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_equipment_area(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(8);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_container_type(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(5);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_container_size(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(6);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_container_height(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(16);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_container_status(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(4);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_status_hdr_bm_conv(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(7);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_hazard(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(17);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_import_export(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(18);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_container_origin(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(20);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_container_origin_plac(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_origin_placement();

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_activities(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(22);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_container_demage(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_master_status(14);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function get_vessel(){
    $filter = isset($_GET['query']) ? $_GET['query'] : 0;
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->get_arr_vessel($filter);

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getUsers(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_master->getUsers();

    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }

  }

  public function getPort(){
    $callback = $_REQUEST['callback'];
		$filter = isset($_GET['query']) ? $_GET['query'] : 0;
		$data	= $this->m_master->get_port_list($filter);
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
	}

  public function getShipping(){
    $callback = $_REQUEST['callback'];
		$filter = isset($_GET['query']) ? $_GET['query'] : 0;
		$data	= $this->m_master->get_Shipping_list($filter);
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
	}

  public function getEquipment(){
    $data	= $this->m_master->getEquipment();
    echo json_encode($data);
  }

  public function getVoyage(){
    $data = $this->m_master->getVoyage();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function get_placement_activity(){
    $data = $this->m_master->get_placement_activity();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function get_cmsConfig(){
    $data = $this->m_master->get_cmsConfig();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function get_yard_activity(){
    $data = $this->m_master->get_yard_activity();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function getTime(){
    $start=strtotime('00:00');
    $end=strtotime('23:59');
    $time = array();
    for ($halfhour=$start;$halfhour<=$end;$halfhour=$halfhour+60*60) {
      $time[]['TIME'] = date('H:i',$halfhour);
    }
    header('Content-Type: text/javascript');
    die(json_encode($time));
  }

  public function getInterval(){
    $data = $this->m_master->getInterval();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function setInterval(){
    $data = $this->m_master->setInterval();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

}
