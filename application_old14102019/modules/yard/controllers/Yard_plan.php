<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Yard_plan extends CI_Controller {
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
    $data['tab_id'] = $_POST['tab_id'];
		$data['id_yard'] = $_POST['id_yard'];
		$data['yard_list'] = $this->m_yard->get_yard_list();
		// $this->load->view('content/yard/yard_plan/viewer_panel', $data);
    //die();
		if ($data['id_yard']){
			$xml_string = $this->m_yard->extract_yard_plan($data['id_yard']);
      $data['xmlData'] = $xml_string;
			$data_yard = simplexml_load_string($xml_string);
			$data['width']		= $data_yard->width;
			$data['height']		= $data_yard->height;
			$stack_cell			= $data_yard->index;
			$data['index'] 		= explode(",", $stack_cell);
			$plan_cell			= $data_yard->plan;
			$data['plan'] 		= explode(",", $plan_cell);
			$taken_cell			= $data_yard->taken;
			$data['taken'] 		= explode(",", $taken_cell);
			$placement_cell		= $data_yard->placement;
			$data['placement'] 	= explode(",", $placement_cell);
			$slot_cell			= $data_yard->slot;
			$data['slot_'] 		= explode(",", $slot_cell);
			$row_cell			= $data_yard->row;
			$data['row_'] 		= explode(",", $row_cell);
			$tier_cell			= $data_yard->tier;
			$data['tier_'] 		= explode(",", $tier_cell);
			$title_cell			= $data_yard->title;
			$data['title'] 		= explode(",", $title_cell);
			$block_id_cell		= $data_yard->block_id;
			$data['block_id']	= explode(",", $block_id_cell);
			$orientation_cell	= $data_yard->orientation;
			$data['orientation']= explode(",", $orientation_cell);
			$position_cell		= $data_yard->position;
			$data['position'] 	= explode(",", $position_cell);
			$label_cell			= $data_yard->label;
			$data['label'] 		= explode(",", $label_cell);
			$label_text_cell	= $data_yard->label_text;
			$data['label_text'] = explode(",", $label_text_cell);
      $data['ybc_id'] = explode(",", $data_yard->ybc_id);
      $data['owner'] = explode(",", $data_yard->owner);
			$this->load->view('yard_plan/viewer_panel_content', $data);
		}
  }

  function insertPaWeightCategory(){
    $insert = $this->m_yard->insertPaWeightCategory();

    // $data = array(
		// 	'success'=>true,
		// 	'message'=> $insert
		// );

    echo json_encode($insert);
  }

  function getPaWeightCategory(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getPaWeightCategory();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  function getPaWeightCategoryHdr(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getPaWeightCategoryHdr();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  function insertYardPlan(){
    $insert = $this->m_yard->setYardCategory();

    $data = array(
			'success'=>true,
			'message'=> $insert['message'],
      'category_id' => $insert['category_id']
		);

    echo json_encode($data);
  }

  public function plan_yard(){
		$id_yard = $_GET['id_yard'];
		$xml_str = $_POST['xml_'];
		// echo $xml_str."<br/>";die;
		$retval = $this->m_yard->insert_plan_yard($id_yard, $xml_str);
		echo $retval;
	}

  //YARD GROUP
  public function getYardPlanGroup(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getYardPlanGroup();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getPlanCategory(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getPlanCategory();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function getPlanCategoryById(){
    $data = $this->m_yard->getPlanCategoryById();
    echo json_encode($data);
  }

  public function getPlanCategoryDetail(){
    $callback = $_REQUEST['callback'];
    $data = $this->m_yard->getPlanCategoryDetail();
    if ($callback) {
    header('Content-Type: text/javascript');
    echo $callback . '(' . json_encode($data) . ');';
    } else {
        header('Content-Type: application/x-json');
        echo json_encode($data);
    }
  }

  public function setNewNameCategory(){
    $insert = $this->m_yard->insertNewNameCategory();
    $data = array(
			'success'=>true,
			'message'=> $insert['message'],
      'id' => $insert['id']
		);
    echo json_encode($data);
  }

  public function delete_pa_weight(){
    $data	= $this->m_yard->delete_yard_plan_group();
    echo $data;
  }

  public function planOwner(){
    $data = $this->m_yard->planOwner();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }
  public function get_print_yard_plan(){
    $data = $this->m_yard->getPrintYardPlan();
    header('Content-Type: text/javascript');
    die(json_encode($data));
  }

  public function print_yard_plan_pdf(){
    $this->load->library('m_pdf');
    $YARD_NAME = $this->m_yard->get_yard_name($_GET['YARD_ID']);
    $data['YARD_NAME'] = $YARD_NAME != null?  $YARD_NAME : 'All';
    $data['branch'] = $this->m_master->get_branch();
    $name = 'Yard_Planning.pdf';
    $data['data'] = $this->m_yard->getPrintYardPlanPdf();

    $html = $this->load->view('yard_plan/print_yard_planning',$data,true);
    //Write some HTML code:
    $this->m_pdf->export->WriteHTML($html);
    $this->m_pdf->export->Output($name,'I');

  }

  public function print_yard_plan_excel(){

    $name = 'Yard_Planning';
    $data['data'] = $this->m_yard->getPrintYardPlanPdf();
    $YARD_NAME = $this->m_yard->get_yard_name($_GET['YARD_ID']);
    $data['YARD_NAME'] = $YARD_NAME != null?  $YARD_NAME : 'All';
    $data['branch'] = $this->m_master->get_branch();
    $html = $this->load->view('yard_plan/print_yard_planning',$data,true);
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=$name.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $html;
  }

  public function delete_yard_plan(){
    $data = $this->m_yard->delete_yard_plan();
    echo json_encode($data);
  }
}
