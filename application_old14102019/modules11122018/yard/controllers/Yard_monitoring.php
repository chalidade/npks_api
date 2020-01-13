<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Yard_monitoring extends CI_Controller {
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
			$xml_string = $this->m_yard->extract_yard_monitoring($data['id_yard']);
      // print_r($xml_string); die();
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
      $data['max_row'] 	= explode(",", $data_yard->max_row);
			$tier_cell			= $data_yard->tier;
			$data['tier_'] 		= explode(",", $tier_cell);
			$title_cell			= $data_yard->title;
			$data['block_name'] 		= explode(",", $title_cell);
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
      $data['cont_size'] = explode(",", $data_yard->cont_size);
      $data['block_dummy'] = explode(",", $data_yard->block_dummy);
      // print_r($data['block_dummy']); die();
			$this->load->view('yard_monitoring/viewer_panel_content', $data);
		}
  }

    public function get_yard_stacking($id_yard, $block, $id_slot,$ybc_id){
      $configs = $this->m_yard->get_slot_config($id_yard, $block);
      $raw_data = $this->m_yard->get_yard_stacking($id_yard, $block, $id_slot, $ybc_id);
      $block_data = $this->m_yard->get_slot_list($id_yard);

      $cy_data = Array(); $cy_data_idx = Array($id_slot);
      foreach($raw_data as $datavalue){
          $cy_data[$datavalue['YBC_SLOT']][] = array(
              'YD_ROW' => $datavalue['YBC_ROW'],
              'YD_TIER' => $datavalue['REAL_YARD_TIER'],
              'NO_CONTAINER' => $datavalue['REAL_YARD_CONT'],
              'POINT' => '',
              'ID_CLASS_CODE' => '',
              'ID_VES_VOYAGE' => '',
              'ID_VESSEL' => '',
              'ID_ISO_CODE' => '',
              'CONT_SIZE' => $datavalue['REAL_YARD_CONT_SIZE'],
              'CONT_TYPE' => $datavalue['REAL_YARD_CONT_TYPE'],
              'CONT_STATUS' => $datavalue['REAL_YARD_CONT_STATUS'],
              'CONT_HEIGHT' => '',
              'ID_POD' => '',
              'ID_OPERATOR' => '',
              'WEIGHT' => '',
              'ID_COMMODITY' => $datavalue['REAL_YARD_COMMODITY'],
              'HAZARD' => ''
          );
          if (!in_array($datavalue['YBC_SLOT'], $cy_data_idx)){
              array_push($cy_data_idx, $datavalue['YBC_SLOT']);
          }
      }
      $filter_data = Array();
      foreach($block_data as $datavalue){
          $filter_data[$datavalue['BLOCK_ID']]= array(
              'ID_BLOCK' => $datavalue['BLOCK_ID'],
              'NAME_BLOCK' => $datavalue['BLOCK_NAME'],
              'SLOT' => $datavalue['BLOCK_SLOT']
          );
      }

      header('Content-Type: text/javascript');
      die(json_encode(Array(
              'configs' => $configs,
              'data_idx' => $cy_data_idx,
              'data' => $cy_data,
              'filter_block' => $filter_data
      )));
    }

}
