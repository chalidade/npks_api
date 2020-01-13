<?php
class M_yard extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	function get_yard_list(){
		$branch_id = $this->session->USER_BRANCH;
		$this->db->select('A.YARD_ID, A.YARD_NAME, A.YARD_STATUS, A.YARD_WIDTH, A.YARD_HEIGHT, COUNT(B.BLOCK_ID) BLOCK');
		$this->db->from('TM_YARD A');
		$this->db->join('TM_BLOCK B','A.YARD_ID = B.BLOCK_YARD_ID');
		$this->db->where('YARD_BRANCH_ID',$branch_id)->where('BLOCK_ACTIVE','Y');
		$this->db->group_by('A.YARD_ID, A.YARD_NAME, A.YARD_STATUS, A.YARD_WIDTH, A.YARD_HEIGHT');
		return $this->db->get()->result_array();
	}

	function get_block_list($yard_id){
		$branch_id = $this->session->USER_BRANCH;
		$this->db->where('BLOCK_BRANCH_ID',$branch_id);
		$this->db->where('BLOCK_YARD_ID',$yard_id);
		$this->db->where('BLOCK_ACTIVE','Y');
		return $this->db->get('TM_BLOCK')->result_array();
	}

	function extract_yard($id_yard){
		$xml_str = "";
		$branch_id = $this->session->USER_BRANCH;

		$this->db->where('YARD_BRANCH_ID',$branch_id);
		$this->db->where('YARD_ID',$id_yard);
		$row = $this->db->get('TM_YARD')->row_array();

		$width_str = "<WIDTH>".$row['YARD_WIDTH']."</WIDTH>";
		$height_str = "<HEIGHT>".$row['YARD_HEIGHT']."</HEIGHT>";
		$yard_name = "<NAME>".$row['YARD_NAME']."</NAME>";

		$this->db->where('YBC_BRANCH_ID',$branch_id);
		$this->db->where('YBC_YARD_ID',$id_yard);
		$this->db->order_by('YBC_CELL_ID','ASC');
		$data = $this->db->where('YBC_ACTIVE','Y')->get('TX_YARD_BLOCK_CELL')->result_array();

		$index_cell = array();
		$index_slot = array();
		$index_row = array();

		foreach ($data as $row) {
			$index_cell[] = $row['YBC_CELL_ID'];
			$index_slot[] = $row['YBC_SLOT'];
			$index_row[] = $row['YBC_ROW'];
			$index_block_id[] = $row['YBC_BLOCK_ID'];
		}

		$cell_ = implode(",",$index_cell);
		$slot_ = implode(",",$index_slot);
		$row_ = implode(",",$index_row);
		$block_id_ = implode(",",$index_block_id);
		$stack_str = "<INDEX>".$cell_."</INDEX>";
		$slot_str = "<SLOT>".$slot_."</SLOT>";
		$row_str = "<ROW>".$row_."</ROW>";
		$block_id_str = "<BLOCK_ID>".$block_id_."</BLOCK_ID>";

		// BLOCK
		$this->db->where('BLOCK_BRANCH_ID',$branch_id);
		$this->db->where('BLOCK_YARD_ID',$id_yard);
		$this->db->where('BLOCK_ACTIVE','Y');
		$data_block = $this->db->get('TM_BLOCK')->result_array();

		$index_block = array();
		$i = 0;
		foreach ($data_block as $row) {
			$index_block[$i]['BLOCK_ID'] = $row['BLOCK_ID'];
			$index_block[$i]['BLOCK_NAME'] = $row['BLOCK_NAME'];
			$index_block[$i]['TIER'] = $row['BLOCK_TIER'];
			$index_block[$i]['POSITION'] = $row['BLOCK_POSITION'];
			$index_block[$i]['ORIENTATION'] = $row['BLOCK_ORIENTATION'];
			$index_block[$i]['COLOR'] = 'BLACK';
			if($row['BLOCK_POSITION'] == 'H'){
				$index_block[$i]['WIDTH'] = $row['BLOCK_SLOT'];
				$index_block[$i]['HEIGHT'] = $row['BLOCK_ROW'];
			}
			elseif($row['BLOCK_POSITION'] == 'V'){
				$index_block[$i]['WIDTH'] = $row['BLOCK_ROW'];
				$index_block[$i]['HEIGHT'] = $row['BLOCK_SLOT'];
			}

			$YBC_BLOCK_ID = $row['BLOCK_ID'];
			$this->db->where('YBC_BRANCH_ID',$branch_id);
			$this->db->where('YBC_YARD_ID',$id_yard);
			$this->db->where('YBC_BLOCK_ID',$YBC_BLOCK_ID);
			$this->db->where('YBC_ACTIVE','Y');
			$this->db->order_by('YBC_CELL_ID','ASC');
			$data_cell = $this->db->get('TX_YARD_BLOCK_CELL')->result_array();

			$arrCell = array();
			$arrBlock_id = array();
			foreach ($data_cell as $row) {
				$arrCell[] = $row['YBC_CELL_ID'];
				$arrBlock_id[] = $row['YBC_BLOCK_ID'];
			}
			$strCell_ = implode(",",$arrCell);
			// $strBlock_id_ = implode(",",$arrBlock_id);
			$strBlock_id_ = $arrBlock_id[0];

			$index_block[$i]['CELL'] = $strCell_;
			$index_block[$i]['BLOCK_ID'] = $strBlock_id_;

			$i++;
		}

		$strBlock = '';
		foreach ($index_block as $row) {
				$strBlock .= '<BLOCK><BLOCK_ID>'.$row['BLOCK_ID'].'</BLOCK_ID><BLOCK_NAME>'.$row['BLOCK_NAME'].'</BLOCK_NAME><TIER>'.$row['TIER'].'</TIER><POSITION>'.$row['POSITION'].'</POSITION><ORIENTATION>'
				.$row['ORIENTATION'].'</ORIENTATION><CELL>'.$row['CELL'].'</CELL><WIDTH>'.$row['WIDTH'].'</WIDTH><HEIGHT>'.$row['HEIGHT'].'</HEIGHT><COLOR>'.$row['COLOR'].'</COLOR></BLOCK>';
		}

		$xmlData =  '<YARD>'.$width_str.''.$height_str.''.$yard_name.''.$stack_str.''.$slot_str.''.$row_str.''.$block_id_str.''.$strBlock.'</YARD>';
		return $xmlData;
	}

	public function set_yard($xml_str, $yard_name){
		$branch_id = $this->session->USER_BRANCH;
		$xml = simplexml_load_string($xml_str);
     // print_r($xml); die();
		$width  	= $xml->width;
		$height 	= $xml->height;
	  $this->db->trans_start();

    $query_yard = "INSERT INTO TM_YARD (YARD_NAME,YARD_BRANCH_ID,YARD_STATUS,YARD_WIDTH,YARD_HEIGHT) VALUES('$yard_name',$branch_id,1,$width,$height)";
    $this->db->query($query_yard);

		$query = "SELECT MAX(YARD_ID) AS MAX_ID FROM TM_YARD";
		$rs = $this->db->query($query);
		$row = $rs->row_array();
		$yard_id =  $row['MAX_ID'];

		$total = $width * $height;

    $block 			= $xml->block;
		$block_sum	 	= count($block);

    foreach ($block as $block_){

      if($block_->position == 'H'){
        $block_slot = $block_->width;
        $block_row = $block_->height;
      }
      elseif($block_->position == 'V'){
        $block_slot = $block_->height;
        $block_row = $block_->width;
      }

			$block_capacity = $block_->tier*$block_->height*$block_->width;

      $query_yard_block = "INSERT INTO TM_BLOCK(BLOCK_YARD_ID, BLOCK_NAME, BLOCK_ROW, BLOCK_SLOT, BLOCK_TIER, BLOCK_POSITION, BLOCK_ORIENTATION, BLOCK_BRANCH_ID, BLOCK_CAPACITY)
                           VALUES($yard_id, '$block_->name', $block_row, $block_slot, $block_->tier, '$block_->position', '$block_->orientation', $branch_id, $block_capacity)";
      $this->db->query($query_yard_block);

      $query = "SELECT MAX(BLOCK_ID) AS MAX_ID FROM TM_BLOCK";
      $rs = $this->db->query($query);
      $row = $rs->row_array();
      $block_id =  $row['MAX_ID'];

      $arrCell	= explode(",",$block_->cell);
      $cell_sum	= count($block_->cell);

      if($block_->position == 'H'){
        if($block_->orientation == 'TL'){
          $this->set_block_tl_h($yard_id, $block_id, $arrCell, $block_row, $block_slot);
        }
        elseif($block_->orientation == 'TR'){
          $this->set_block_tr_h($yard_id, $block_id, $arrCell, $block_row, $block_slot);
        }
        elseif($block_->orientation == 'BR'){
          $this->set_block_br_h($yard_id, $block_id, $arrCell, $block_row, $block_slot);
        }
        elseif($block_->orientation == 'BL'){
          $this->set_block_bl_h($yard_id, $block_id, $arrCell, $block_row, $block_slot);
        }
      }elseif($block_->position == 'V'){
        if($block_->orientation == 'TL'){
          $this->set_block_tl_v($yard_id, $block_id, $arrCell, $block_row, $block_slot);
        }
        elseif($block_->orientation == 'TR'){
          $this->set_block_tr_v($yard_id, $block_id, $arrCell, $block_row, $block_slot);
        }
        elseif($block_->orientation == 'BR'){
          $this->set_block_br_v($yard_id, $block_id, $arrCell, $block_row, $block_slot);
        }
        elseif($block_->orientation == 'BL'){
          $this->set_block_bl_v($yard_id, $block_id, $arrCell, $block_row, $block_slot);
        }
      }

    }

		if ($this->db->trans_complete()){
			return 1;
		}else{
			return 0;
		}
	}

  public function set_block_tl_h($yard_id, $block_id, $arrCell, $row, $slot){
    $branch_id = $this->session->USER_BRANCH;
		$user = $this->session->isId;
    $xSlot = 1;
    $xRow = 1;

    foreach ($arrCell as $cell) {
      if($xSlot > $slot){
        $xRow++;
        $xSlot = 1;
      }

      $query_yard_block_cell = "INSERT INTO TX_YARD_BLOCK_CELL (YBC_YARD_ID, YBC_BLOCK_ID, YBC_CELL_ID, YBC_SLOT, YBC_ROW, YBC_BRANCH_ID, YBC_CREATE_BY) VALUES($yard_id, $block_id, $cell, $xSlot, $xRow, $branch_id, $user)";
      $this->db->query($query_yard_block_cell);
      $xSlot++;
    }

  }

  public function set_block_tr_h($yard_id, $block_id, $arrCell, $row, $slot){
    $branch_id = $this->session->USER_BRANCH;
		$user = $this->session->isId;
    $xSlot = $slot;
    $xRow = 1;

    foreach ($arrCell as $cell) {
      if($xSlot < 1){
        $xRow++;
        $xSlot = $slot;
      }

      $query_yard_block_cell = "INSERT INTO TX_YARD_BLOCK_CELL (YBC_YARD_ID, YBC_BLOCK_ID, YBC_CELL_ID, YBC_SLOT, YBC_ROW, YBC_BRANCH_ID, YBC_CREATE_BY) VALUES($yard_id, $block_id, $cell, $xSlot, $xRow, $branch_id, $user)";
      $this->db->query($query_yard_block_cell);
      $xSlot = $xSlot - 1;
    }
  }

  public function set_block_br_h($yard_id, $block_id, $arrCell, $row, $slot){
    $branch_id = $this->session->USER_BRANCH;
		$user = $this->session->isId;
    $xSlot = $slot;
    $xRow = $row;

    foreach ($arrCell as $cell) {
      if($xSlot < 1){
        $xRow = $xRow - 1;
        $xSlot = $slot;
      }

      $query_yard_block_cell = "INSERT INTO TX_YARD_BLOCK_CELL (YBC_YARD_ID, YBC_BLOCK_ID, YBC_CELL_ID, YBC_SLOT, YBC_ROW, YBC_BRANCH_ID, YBC_CREATE_BY) VALUES($yard_id, $block_id, $cell, $xSlot, $xRow, $branch_id, $user)";
      $this->db->query($query_yard_block_cell);
      $xSlot = $xSlot - 1;
    }
  }

  public function set_block_bl_h($yard_id, $block_id, $arrCell, $row, $slot){
    $branch_id = $this->session->USER_BRANCH;
		$user = $this->session->isId;
    $xSlot = 1;
    $xRow = $row;

    foreach ($arrCell as $cell) {
      if($xSlot > $slot){
        $xRow = $xRow - 1;
        $xSlot = 1;
      }

      $query_yard_block_cell = "INSERT INTO TX_YARD_BLOCK_CELL (YBC_YARD_ID, YBC_BLOCK_ID, YBC_CELL_ID, YBC_SLOT, YBC_ROW, YBC_BRANCH_ID, YBC_CREATE_BY) VALUES($yard_id, $block_id, $cell, $xSlot, $xRow, $branch_id, $user)";
      $this->db->query($query_yard_block_cell);
      $xSlot = $xSlot + 1;
    }
  }

  public function set_block_tl_v($yard_id, $block_id, $arrCell, $row, $slot){
    $branch_id = $this->session->USER_BRANCH;
		$user = $this->session->isId;
    $xSlot = 1;
    $xRow = 1;

    foreach ($arrCell as $cell) {
      if($xRow > $row){
        $xSlot = $xSlot + 1;
        $xRow = 1;
      }

      $query_yard_block_cell = "INSERT INTO TX_YARD_BLOCK_CELL (YBC_YARD_ID, YBC_BLOCK_ID, YBC_CELL_ID, YBC_SLOT, YBC_ROW, YBC_BRANCH_ID, YBC_CREATE_BY) VALUES($yard_id, $block_id, $cell, $xSlot, $xRow, $branch_id, $user)";
      $this->db->query($query_yard_block_cell);
      $xRow = $xRow + 1;
    }
  }

  public function set_block_tr_v($yard_id, $block_id, $arrCell, $row, $slot){
    $branch_id = $this->session->USER_BRANCH;
		$user = $this->session->isId;
    $xSlot = 1;
    $xRow = $row;

    foreach ($arrCell as $cell) {
      if($xRow < 1){
        $xSlot++;
        $xRow = $row;
      }

      $query_yard_block_cell = "INSERT INTO TX_YARD_BLOCK_CELL (YBC_YARD_ID, YBC_BLOCK_ID, YBC_CELL_ID, YBC_SLOT, YBC_ROW, YBC_BRANCH_ID, YBC_CREATE_BY) VALUES($yard_id, $block_id, $cell, $xSlot, $xRow, $branch_id, $user)";
      $this->db->query($query_yard_block_cell);
      $xRow = $xRow - 1;
    }
  }

  public function set_block_br_v($yard_id, $block_id, $arrCell, $row, $slot){
    $branch_id = $this->session->USER_BRANCH;
		$user = $this->session->isId;
    $xSlot = $slot;
    $xRow = $row;

    foreach ($arrCell as $cell) {
      if($xRow < 1){
        $xSlot = $xSlot - 1;
        $xRow = $row;
      }

      $query_yard_block_cell = "INSERT INTO TX_YARD_BLOCK_CELL (YBC_YARD_ID, YBC_BLOCK_ID, YBC_CELL_ID, YBC_SLOT, YBC_ROW, YBC_BRANCH_ID, YBC_CREATE_BY) VALUES($yard_id, $block_id, $cell, $xSlot, $xRow, $branch_id, $user)";
      $this->db->query($query_yard_block_cell);
      $xRow = $xRow - 1;
    }
  }

  public function set_block_bl_v($yard_id, $block_id, $arrCell, $row, $slot){
    $branch_id = $this->session->USER_BRANCH;
		$user = $this->session->isId;
    $xSlot = $slot;
    $xRow = 1;

    foreach ($arrCell as $cell) {
      if($xRow > $row){
        $xSlot = $xSlot - 1;
        $xRow = 1;
      }

      $query_yard_block_cell = "INSERT INTO TX_YARD_BLOCK_CELL (YBC_YARD_ID, YBC_BLOCK_ID, YBC_CELL_ID, YBC_SLOT, YBC_ROW, YBC_BRANCH_ID, YBC_CREATE_BY) VALUES($yard_id, $block_id, $cell, $xSlot, $xRow, $branch_id, $user)";
      $this->db->query($query_yard_block_cell);
      $xRow = $xRow + 1;
    }
  }

	//update yard
	public function update_yard($xml_str, $yard_name, $id_yard){
		$branch_id = $this->session->USER_BRANCH;
		$xml = simplexml_load_string($xml_str);
		$width  	= $xml->width;
		$height 	= $xml->height;
		$unset = $xml->unset;
		$unset_ = explode(",",$unset);
		// print_r($unset_);die();
		// print_r(sizeof($unset_));die();
	  $this->db->trans_start();

		$arrYard = array(
			'YARD_NAME' => $yard_name,
			'YARD_STATUS' => 1,
			'YARD_WIDTH' => $width,
			'YARD_HEIGHT' => $height
		);

		//$this->db->where('YARD_ID', $id_yard);
		//$this->db->update('TM_YARD',$arrYard);

		foreach ($unset_ as $val) {
			if($val != ''){
				$this->db->set('BLOCK_ACTIVE','T')->where('BLOCK_ID',$val)->update('TM_BLOCK');
				$this->db->set('YBC_ACTIVE','T')->where('YBC_BLOCK_ID',$val)->update('TX_YARD_BLOCK_CELL');
			}
		}
		// $this->db->delete('TX_YARD_BLOCK_CELL', array('YBC_YARD_ID' => $id_yard));
		// $this->db->delete('TM_BLOCK', array('BLOCK_YARD_ID' => $id_yard));

		// $query = "SELECT MAX(YARD_ID) AS MAX_ID FROM TM_YARD";
		// $rs = $this->db->query($query);
		// $row = $rs->row_array();
		$yard_id =  $id_yard;

		$total = $width * $height;

    $block 			= $xml->block;
		$block_sum	 	= count($block);
    foreach ($block as $block_){
		 if($block_->status == 'ADD' || $block_->status == 'EDIT'){
	      if($block_->position == 'H'){
	        $block_slot = $block_->width;
	        $block_row = $block_->height;
	      }
	      elseif($block_->position == 'V'){
	        $block_slot = $block_->height;
	        $block_row = $block_->width;
	      }
				$block_capacity = $block_->tier*$block_->height*$block_->width;
	      $query_yard_block = "INSERT INTO TM_BLOCK(BLOCK_YARD_ID, BLOCK_NAME, BLOCK_ROW, BLOCK_SLOT, BLOCK_TIER, BLOCK_POSITION, BLOCK_ORIENTATION, BLOCK_BRANCH_ID, BLOCK_CAPACITY)
	                           VALUES($yard_id, '$block_->name', $block_row, $block_slot, $block_->tier, '$block_->position', '$block_->orientation', $branch_id, $block_capacity)";
	      $this->db->query($query_yard_block);

	      $query = "SELECT MAX(BLOCK_ID) AS MAX_ID FROM TM_BLOCK";
	      $rs = $this->db->query($query);
	      $row = $rs->row_array();
	      $block_id =  $row['MAX_ID'];

	      $arrCell	= explode(",",$block_->cell);
	      $cell_sum	= count($block_->cell);

				if($block_->position == 'H'){
	        if($block_->orientation == 'TL'){
	          $this->set_block_tl_h($yard_id, $block_id, $arrCell, $block_row, $block_slot);
	        }
	        elseif($block_->orientation == 'TR'){
	          $this->set_block_tr_h($yard_id, $block_id, $arrCell, $block_row, $block_slot);
	        }
	        elseif($block_->orientation == 'BR'){
	          $this->set_block_br_h($yard_id, $block_id, $arrCell, $block_row, $block_slot);
	        }
	        elseif($block_->orientation == 'BL'){
	          $this->set_block_bl_h($yard_id, $block_id, $arrCell, $block_row, $block_slot);
	        }
	      }elseif($block_->position == 'V'){
	        if($block_->orientation == 'TL'){
	          $this->set_block_tl_v($yard_id, $block_id, $arrCell, $block_row, $block_slot);
	        }
	        elseif($block_->orientation == 'TR'){
	          $this->set_block_tr_v($yard_id, $block_id, $arrCell, $block_row, $block_slot);
	        }
	        elseif($block_->orientation == 'BR'){
	          $this->set_block_br_v($yard_id, $block_id, $arrCell, $block_row, $block_slot);
	        }
	        elseif($block_->orientation == 'BL'){
	          $this->set_block_bl_v($yard_id, $block_id, $arrCell, $block_row, $block_slot);
	        }
	      }
			}
    }

		if ($this->db->trans_complete()){
			return 1;
		}else{
			return 0;
		}

	}

	function update_yard_block($yard_id, $block_id){
		$arrData = array(
			'BLOCK_TIER' => $this->input->post('BLOCK_TIER'),
			'BLOCK_ORIENTATION' => $this->input->post('BLOCK_ORIENTATION')
		);
		$this->db->trans_start();
		$this->db->where('BLOCK_YARD_ID',$yard_id);
		$this->db->where('BLOCK_ACTIVE','Y');
		$this->db->where('BLOCK_ID',$block_id);
		$this->db->update('TM_BLOCK',$arrData);
		if ($this->db->trans_complete()){
			return 1;
		}else{
			return 0;
		}
	}

	public function extract_yard_plan($id_yard){
	$params = array(
		'YARD_ID' => $id_yard,
		'BRANCH_ID' => $this->session->USER_BRANCH
	);
	$xml_str = "";
	$query 		= "SELECT * FROM TM_YARD WHERE YARD_ID='$id_yard'";
	$rs 		= $this->db->query($query);
	$row 		= $rs->row_array();

	$width_str = "<width>".$row['YARD_WIDTH']."</width>";
	$height_str = "<height>".$row['YARD_HEIGHT']."</height>";

	$query 		= "SELECT A.YBC_YARD_ID ,A.YBC_CELL_ID, B.BLOCK_ID, B.BLOCK_NAME, B.BLOCK_POSITION, B.BLOCK_ORIENTATION, B.BLOCK_TIER, A.YBC_ROW, A.YBC_SLOT,
							MAX(C.YP_STATUS) FLAG_STATUS,SUM(DECODE(NVL(C.YP_STATUS,0),0,0,1)) JML_TAKEN, 0 PLACEMENT,0 JML_PLACEMENT, B.BLOCK_ROW, B.BLOCK_SLOT, D.YARD_HEIGHT, D.YARD_WIDTH, A.YBC_ID
							FROM TX_YARD_BLOCK_CELL A
							JOIN TM_BLOCK B ON A.YBC_BLOCK_ID = B.BLOCK_ID
							LEFT JOIN TX_YARD_PLAN C ON A.YBC_ID = C.YP_YBC_ID
              JOIN TM_YARD D ON B.BLOCK_YARD_ID = D.YARD_ID
							WHERE A.YBC_ACTIVE = 'Y' AND B.BLOCK_ACTIVE = 'Y' AND A.YBC_YARD_ID = ? AND A.YBC_BRANCH_ID = ?
							GROUP BY A.YBC_YARD_ID ,A.YBC_CELL_ID, B.BLOCK_ID, B.BLOCK_NAME, B.BLOCK_POSITION, B.BLOCK_ORIENTATION, B.BLOCK_TIER, A.YBC_ROW, A.YBC_SLOT, B.BLOCK_ROW, B.BLOCK_SLOT, D.YARD_HEIGHT, D.YARD_WIDTH, A.YBC_ID
							ORDER BY A.YBC_CELL_ID";
	$rs 		  = $this->db->query($query,$params);
	$data 		= $rs->result_array();

	$index_stack = array();
	$index_plan = array();
	$index_taken = array();
	$index_placement = array();
	$index_slot = array();
	$index_row = array();
	$index_tier = array();
	$index_title = array();
	$index_block_id = array();
	$index_orientation = array();
	$index_position = array();
	$index_label = array();
	$index_label_text = array();
	$index_ybc_id = array();

	$y = 1;
	$z = 0;
	$i = 1;

	$total_cell = $row['YARD_WIDTH'] * $row['YARD_HEIGHT'];

	for ($e=0; $e < $total_cell; $e++) {

	  foreach($data as $row){

	    if($row['YBC_CELL_ID'] == $e){
				$index_ybc_id[] = $row['YBC_ID'];
	      $index_stack[] = $row['YBC_CELL_ID'];
	      $index_slot[] = $row['YBC_SLOT'];
	      $index_row[] = $row['YBC_ROW'];
	      $index_tier[] = $row['BLOCK_TIER'];
	      $index_title[] = $row['BLOCK_NAME'];
	      $index_block_id[] = $row['BLOCK_ID'];
	      $index_orientation[] = $row['BLOCK_ORIENTATION'];
	      $index_position[] = $row['BLOCK_POSITION'];
	      $index_placement[] = $row['JML_PLACEMENT'];
	      $delta = $row['JML_TAKEN']-$row['JML_PLACEMENT'];
	      $index_taken[] = $delta;

	      if ($row['FLAG_STATUS'] != ""){
	        $index_plan[] = $row['YBC_CELL_ID'];
	      }

	      if(($row['BLOCK_ORIENTATION'] == 'TR') && ($row['BLOCK_POSITION'] == 'H')){ // Top right posisi horizontal
	        if(($row['YBC_SLOT'] >1) && ($row['YBC_ROW'] ==1)){
	          $index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
	          $index_label_text[] = $row['YBC_SLOT'];
	        }else{
	          if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] ==1)){
	            $index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
	            $index_label_text[] = $row['YBC_SLOT'];

	            $index_label[] = $row['YBC_CELL_ID']+1;
	            $index_label_text[] = $row['YBC_ROW'];

	          }else if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] >1)){
	            $index_label[] = $row['YBC_CELL_ID']+1;
	            $index_label_text[] = $row['YBC_ROW'];
	          }
	        }
	      }else if(($row['BLOCK_ORIENTATION'] == 'TR') && ($row['BLOCK_POSITION'] == 'V')){ // Top right posisi vertical
	        if(($row['YBC_ROW'] >1) && ($row['YBC_SLOT'] ==1)){
	          $index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
	          $index_label_text[] = $row['YBC_ROW'];
	        }else{
	          if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] ==1)){
	            $index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
	            $index_label_text[] = $row['YBC_ROW'];

	            $index_label[] = $row['YBC_CELL_ID']+1;
	            $index_label_text[] = $row['YBC_SLOT'];

	          }else if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] >1)){
	            $index_label[] = $row['YBC_CELL_ID']+1;
	            $index_label_text[] = $row['YBC_SLOT'];
	          }
	        }
	      }
				else if(($row['BLOCK_ORIENTATION'] == 'TL') && ($row['BLOCK_POSITION'] == 'H')){ // Top left posisi horizontal
					if(($row['YBC_SLOT'] >1) && ($row['YBC_ROW'] ==1)){
					 	 $index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
					 	 $index_label_text[] = $row['YBC_SLOT'];
				  }else{
				 	 if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] ==1)){
					 		 $index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
					 		 $index_label_text[] = $row['YBC_SLOT'];

					 		 $index_label[] = $row['YBC_CELL_ID']-1;
					 		 $index_label_text[] = $row['YBC_ROW'];

				 	 }else if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] >1)){
					 		 $index_label[] = $row['YBC_CELL_ID']-1;
					 		 $index_label_text[] = $row['YBC_ROW'];
				 	 }
				  }
				}
				else if(($row['BLOCK_ORIENTATION'] == 'TL') && ($row['BLOCK_POSITION'] == 'V')){ // Top left posisi vertical
					if(($row['YBC_ROW'] >1) && ($row['YBC_SLOT'] ==1)){
							$index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
							$index_label_text[] = $row['YBC_ROW'];
					}else{
						if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] ==1)){
							$index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
							$index_label_text[] = $row['YBC_ROW'];

							$index_label[] = $row['YBC_CELL_ID']-1;
							$index_label_text[] = $row['YBC_SLOT'];

						}else if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] >1)){
							$index_label[] = $row['YBC_CELL_ID']-1;
							$index_label_text[] = $row['YBC_SLOT'];
						}
					}
				}
				else if(($row['BLOCK_ORIENTATION'] == 'BR') && ($row['BLOCK_POSITION'] == 'H')){ // button right posisi horizontal
					if(($row['YBC_SLOT'] >1) && ($row['YBC_ROW'] ==1)){
					 $index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
					 $index_label_text[] = $row['YBC_SLOT'];
				 }else{
					 if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] ==1)){
						 $index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
						 $index_label_text[] = $row['YBC_SLOT'];

						 $index_label[] = $row['YBC_CELL_ID']+1;
						 $index_label_text[] = $row['YBC_ROW'];

					 }else if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] >1)){
						 $index_label[] = $row['YBC_CELL_ID']+1;
						 $index_label_text[] = $row['YBC_ROW'];
					 }
				 }
				}
				else if(($row['BLOCK_ORIENTATION'] == 'BR') && ($row['BLOCK_POSITION'] == 'V')){ // button right posisi vertical
					if(($row['YBC_ROW'] >1) && ($row['YBC_SLOT'] ==1)){
						$index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
						$index_label_text[] = $row['YBC_ROW'];
					}else{
						if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] ==1)){
							$index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
							$index_label_text[] = $row['YBC_ROW'];

							$index_label[] = $row['YBC_CELL_ID']+1;
							$index_label_text[] = $row['YBC_SLOT'];

						}else if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] >1)){
							$index_label[] = $row['YBC_CELL_ID']+1;
							$index_label_text[] = $row['YBC_SLOT'];
						}
					}
				}
				else if(($row['BLOCK_ORIENTATION'] == 'BL') && ($row['BLOCK_POSITION'] == 'H')){ // Top left posisi horizontal
					if(($row['YBC_SLOT'] >1) && ($row['YBC_ROW'] ==1)){
						 $index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
						 $index_label_text[] = $row['YBC_SLOT'];
					}else{
					 if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] ==1)){
							 $index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
							 $index_label_text[] = $row['YBC_SLOT'];

							 $index_label[] = $row['YBC_CELL_ID']-1;
							 $index_label_text[] = $row['YBC_ROW'];

					 }else if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] >1)){
							 $index_label[] = $row['YBC_CELL_ID']-1;
							 $index_label_text[] = $row['YBC_ROW'];
					 }
					}
				}
				else if(($row['BLOCK_ORIENTATION'] == 'BL') && ($row['BLOCK_POSITION'] == 'V')){ // Top left posisi vertical
					if(($row['YBC_ROW'] >1) && ($row['YBC_SLOT'] ==1)){
							$index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
							$index_label_text[] = $row['YBC_ROW'];
					}else{
						if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] ==1)){
							$index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
							$index_label_text[] = $row['YBC_ROW'];

							$index_label[] = $row['YBC_CELL_ID']-1;
							$index_label_text[] = $row['YBC_SLOT'];

						}else if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] >1)){
							$index_label[] = $row['YBC_CELL_ID']-1;
							$index_label_text[] = $row['YBC_SLOT'];
						}
					}
				}
	    }
	  }
	}

	$ybc_id_ = implode(",",$index_ybc_id);
	$stack_ = implode(",",$index_stack);
	$plan_ = implode(",",$index_plan);
	$taken_ = implode(",",$index_taken);
	$placement_ = implode(",",$index_placement);
	$slot_ = implode(",",$index_slot);
	$row_ = implode(",",$index_row);
	$tier_ = implode(",",$index_tier);
	$title = implode(",",$index_title);
	$block_id = implode(",",$index_block_id);
	$orientation = implode(",",$index_orientation);
	$position = implode(",",$index_position);
	$label_ = implode(",",$index_label);
	$label_text_ = implode(",",$index_label_text);

	$ybc_id_str = "<ybc_id>".$ybc_id_."</ybc_id>";
	$stack_str = "<index>".$stack_."</index>";
	$plan_str = "<plan>".$plan_."</plan>";
	$taken_str = "<taken>".$taken_."</taken>";
	$placement_str = "<placement>".$placement_."</placement>";
	$slot_str = "<slot>".$slot_."</slot>";
	$row_str = "<row>".$row_."</row>";
	$tier_str = "<tier>".$tier_."</tier>";
	$title_str = "<title>".$title."</title>";
	$block_id_str = "<block_id>".$block_id."</block_id>";
	$orientation_str = "<orientation>".$orientation."</orientation>";
	$position_str = "<position>".$position."</position>";
	$label_str = "<label>".$label_."</label>";
	$label_text_str = "<label_text>".$label_text_."</label_text>";

	$xml_str = "<yard>".$width_str.$height_str.$stack_str.$plan_str.$taken_str.$placement_str.$slot_str.$row_str.$tier_str.$title_str.$block_id_str.$orientation_str.$position_str.$label_str.$label_text_str.$ybc_id_str."</yard>";

	return $xml_str;
}

function insertPaWeightCategory(){
	$branch_id = $this->session->USER_BRANCH;
	$message = 'SUKSES';
	$arrDataHDR = array(
		'PAWEIGHT_NAME' => $this->input->post('PAWEIGHT_NAME')
	);

	$this->db->trans_start();

	$pwgName = '';

	if($this->input->post('PAWEIGHT_NAME_EXIST') != ''){
		$pwgName = $this->db->select('PAWEIGHT_NAME')
								->from('TX_PAWEIGHT_HDR')
								->where('PAWEIGHT_ID',$this->input->post('PAWEIGHT_NAME_EXIST'))
								->get()->row()->PAWEIGHT_NAME;
	}

	if($this->input->post('PAWEIGHT_NAME_EXIST') == '' || $this->input->post('PAWEIGHT_NAME') != '' && $this->input->post('PAWEIGHT_NAME') != $pwgName){

		$data = $this->db->where('PAWEIGHT_NAME',$this->input->post('PAWEIGHT_NAME'))
						->from('TX_PAWEIGHT_HDR')
						->count_all_results();

		if($data > 0){

			return array('success' => false,
										'message' => 'Name already taken',
									 	'id' => '');
			die();
		}

		$this->db->insert('TX_PAWEIGHT_HDR',$arrDataHDR);

		$this->db->select_max('PAWEIGHT_ID');
		$query = $this->db->get('TX_PAWEIGHT_HDR')->row_array();
		$iNewID = $query['PAWEIGHT_ID'];
	}
	else{
		$iNewID = $this->input->post('PAWEIGHT_NAME_EXIST');
	}
		$arrDataDTL = array(
			'PAWEIGHT_HDR_ID' => $iNewID,
			'PAWEIGHT_DTL_CONT_SIZE' => $this->input->post('PAWEIGHT_DTL_CONT_SIZE'),
			'PAWEIGHT_DTL_MIN_WEIGHT' => $this->input->post('PAWEIGHT_DTL_MIN_WEIGHT'),
			'PAWEIGHT_DTL_MAX_WEIGHT' => $this->input->post('PAWEIGHT_DTL_MAX_WEIGHT'),
			'PAWEIGHT_DTL_NAME' => $this->input->post('PAWEIGHT_TDL_NAME'),
		);

		$this->db->insert('TX_PAWEIGHT_DTL',$arrDataDTL);

	$this->db->trans_complete();

	if ($this->db->trans_status() === FALSE)
	{
			$message = 'ERROR';
	}

	return array('success' => true,
							 'message' => $message,
							 'id' => $iNewID);
}

function getPaWeightCategoryHdr(){
	$branch_id = $this->session->USER_BRANCH;

	$params  = array();

	$sql = "SELECT A.PAWEIGHT_ID, A.PAWEIGHT_NAME FROM TX_PAWEIGHT_HDR A ORDER BY A.PAWEIGHT_ID ASC";
	$data = $this->db->query($sql)->result_array();
	return array('data' => $data);
}

function getPaWeightCategory(){
	$branch_id = $this->session->USER_BRANCH;

	$params  = array('PAWEIGHT_ID' => $_REQUEST['id']);

	$sql = "SELECT A.PAWEIGHT_ID, A.PAWEIGHT_NAME, B.PAWEIGHT_DTL_ID, B.PAWEIGHT_DTL_CONT_SIZE, B.PAWEIGHT_DTL_MIN_WEIGHT,
						B.PAWEIGHT_DTL_MAX_WEIGHT, B.PAWEIGHT_DTL_NAME, C.REFF_NAME CONT_SIZE_NAME, C.REFF_NAME||' - '||B.PAWEIGHT_DTL_NAME CATEGNAME_PAWEIGHT
						FROM TX_PAWEIGHT_HDR A
						JOIN TX_PAWEIGHT_DTL B ON A.PAWEIGHT_ID = B.PAWEIGHT_HDR_ID
						JOIN TM_REFF C ON B.PAWEIGHT_DTL_CONT_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6
						WHERE A.PAWEIGHT_ID = ?
						ORDER BY B.PAWEIGHT_DTL_ID";
	$data = $this->db->query($sql,$params)->result_array();
	return array('data' => $data);
}

function setYardCategory(){
	$branch_id = $this->session->USER_BRANCH;
	$message = 'SUKSES';

	$category_name = $_POST['category_name'];
	$category_detail = json_decode($_POST['category_detail']);
	$newIdHDR = '';

	$this->db->trans_start();

	if(isset($_POST['category_id'])){
		$this->db->delete('TX_CATEGORY_DTL', array('CAT_HDR_ID' => $_POST['category_id']));
		$newIdHDR = $_POST['category_id'];
	}
	else{
		$arrDataHDR = array('CAT_HDR_NAME' => $category_name, 'CAT_BRANCH_ID' => $branch_id);
		$this->db->insert('TX_CATEGORY_HDR',$arrDataHDR);

		$this->db->select_max('CAT_HDR_ID');
		$newIdHDR = $this->db->get('TX_CATEGORY_HDR')->row()->CAT_HDR_ID;
	}

	for($i=0;$i<sizeof($category_detail);$i++){
		$detail = $category_detail[$i];

		$q_fields = "";
		$q_values = "";

		foreach($detail as $key=>$value){


			$array_detail[$key] = $value;

			if($key != 'id'){
				if ($q_fields!=''){
					$q_fields .= ",";
				}
				$q_fields .= $key;
				if ($q_values!=''){
					$q_values .= ",";
				}

				if ($key=='CAT_HDR_ID'){
					$q_values .= "'".$newIdHDR."'";
				}
				else{
					$q_values .= "'".$value."'";
				}
			}
		}

		$query 	= "INSERT INTO TX_CATEGORY_DTL ($q_fields) VALUES($q_values)";
		$rs 	= $this->db->query($query);
	}

	$this->db->trans_complete();

	if ($this->db->trans_status() === FALSE)
	{
			$message = 'ERROR';
	}

	return array('message' => $message, 'category_id' => $newIdHDR);

}

public function insert_plan_yard($id_yard, $xml_str){
	$branch_id = $this->session->USER_BRANCH;
	$user = $this->session->isId;
	$xml = simplexml_load_string($xml_str);

	$block = $xml->block;
	$block_id = $block->block_id;
	$category_id = $xml->category_id;
	$index = $block->index;
	$index_arr = explode(",",$index);
	$ybc_id = explode(",",$block->ybc_id);

	// $query = "SELECT MAX(ID_YARD_PLAN) AS MAX_ID FROM YARD_PLAN_GROUP";
	// $rs 		= $this->db->query($query);
	// $row 		= $rs->row_array();
	// $id = 1;
	// if ($row['MAX_ID']){
	// 	$id = $row['MAX_ID']+1;
	// }

	$max_slot=0;
	$min_slot=0;
	$max_row=0;
	$min_row=0;

	$query = "SELECT BLOCK_TIER TIER_ FROM TM_BLOCK
				WHERE BLOCK_ACTIVE = 'Y' AND BLOCK_YARD_ID='$id_yard' AND BLOCK_ID='$block_id'";
	$rs 				= $this->db->query($query);
	$data_tier = $rs->row_array();
	$tier = $data_tier['TIER_'];

	foreach($index_arr as $cell){
		$query = "SELECT  YBC_SLOT SLOT_, YBC_ROW ROW_ FROM TX_YARD_BLOCK_CELL
					WHERE YBC_ACTIVE = 'Y' AND YBC_YARD_ID='$id_yard' AND YBC_BLOCK_ID='$block_id' AND YBC_CELL_ID='$cell'
					ORDER BY YBC_SLOT,YBC_ROW";
		$rs = $this->db->query($query);
		$data_slot_row_tier = $rs->result_array();

		if ($min_slot==0){
			$min_slot = $data_slot_row_tier[0]['SLOT_'];
			$max_slot = $data_slot_row_tier[0]['SLOT_'];
			$min_row = $data_slot_row_tier[0]['ROW_'];
			$max_row = $data_slot_row_tier[0]['ROW_'];
		}else{
			if ($data_slot_row_tier[0]['SLOT_']>$max_slot){
				$max_slot = $data_slot_row_tier[0]['SLOT_'];
			}else if ($data_slot_row_tier[0]['SLOT_']<$min_slot){
				$min_slot = $data_slot_row_tier[0]['SLOT_'];
			}
			if ($data_slot_row_tier[0]['ROW_']>$max_row){
				$max_row = $data_slot_row_tier[0]['ROW_'];
			}else if ($data_slot_row_tier[0]['ROW_']<$min_row){
				$min_row = $data_slot_row_tier[0]['ROW_'];
			}
		}
	}

	$capacity = ($max_slot-$min_slot+1)*($max_row-$min_row+1)*$tier;

	// $query 	= "INSERT INTO YARD_PLAN_GROUP
	// 			(ID_YARD_PLAN, ID_YARD, ID_BLOCK, START_SLOT, END_SLOT, START_ROW, END_ROW, ID_CATEGORY, CAPACITY) VALUES('$id', '$id_yard', '$block_id', '$min_slot', '$max_slot', '$min_row', '$max_row', '$category_id', '$capacity')";
	// $rs 	= $this->db->query($query);

	$insert_yard_plan_group = "INSERT INTO TX_YARD_PLAN_GROUP (YPG_YARD_ID, YPG_BLOCK_ID, YPG_STAR_ROW, YPG_START_SLOT, YPG_END_ROW, YPG_END_SLOT, YPG_CAPACITY, YPG_CAT_HDR_ID, YPG_BRANCH_ID, YPG_CREATE_BY)
															VALUES ('$id_yard','$block_id','$min_row','$min_slot','$max_row','$max_slot','$capacity','$category_id','$branch_id', '$user')";
	$this->db->query($insert_yard_plan_group);

	$this->db->select_max('YPG_ID');
	$newIdYPG = $this->db->get('TX_YARD_PLAN_GROUP')->row()->YPG_ID;

	$yp_status = 0;

	foreach ($ybc_id as $ybc_id) {
		$insert_yard_plan = "INSERT INTO TX_YARD_PLAN (YP_YBC_ID, YP_YPG_ID, YP_CAT_HDR_ID, YP_STATUS, YP_BRANCH_ID, YP_CREATE_BY)
													VALUES('$ybc_id','$newIdYPG','$category_id', '$yp_status', '$branch_id', '$user')";
		$this->db->query($insert_yard_plan);
	}


	return 1;


}

function getYardPlanGroup(){
	$branch_id = $this->session->USER_BRANCH;
	$params = array('BRANCH_ID' => $branch_id);
	$query = "SELECT A.YPG_ID, B.YARD_NAME, C.BLOCK_NAME, A.YPG_START_SLOT||'-'||A.YPG_END_SLOT AS SLOT_RANGE, A.YPG_STAR_ROW||'-'||A.YPG_END_ROW AS ROW_RANGE, A.YPG_CAPACITY, D.CAT_HDR_NAME, A.YPG_CAT_HDR_ID
						FROM TX_YARD_PLAN_GROUP A
						JOIN TM_YARD B ON A.YPG_YARD_ID = B.YARD_ID
						JOIN TM_BLOCK C ON A.YPG_BLOCK_ID = C.BLOCK_ID
						JOIN TX_CATEGORY_HDR D ON A.YPG_CAT_HDR_ID = D.CAT_HDR_ID
						WHERE C.BLOCK_ACTIVE = 'Y' AND A.YPG_BRANCH_ID = ?
						ORDER BY A.YPG_ID";
	return $this->db->query($query,$params)->result_array();
}

function getPlanCategory(){
	$branch_id = $this->session->USER_BRANCH;
	$params = array('BRANCH_ID' => $branch_id);
	$query = "SELECT CAT_HDR_ID, CAT_HDR_NAME FROM TX_CATEGORY_HDR WHERE CAT_BRANCH_ID = ?";
	return $this->db->query($query,$params)->result_array();
}

function getPlanCategoryDetail(){
	$branch_id = $this->session->USER_BRANCH;
	$params = array('BRANCH_ID' => $branch_id, 'CAT_HDR_ID' => $_REQUEST['id']);
	$query = "SELECT A.CAT_HDR_ID, B.CAT_DTL_CONT_SIZE, B.CAT_DTL_CONT_TYPE, B.CAT_DTL_CONT_STATUS, B.CAT_DTL_PORT, B.CAT_DTL_SHIPPING_ID, B.CAT_DTL_CONT_HEIGHT, B.CAT_PAWEIGHT_HDR_ID,
						B.CAT_PAWEIGHT_DTL_ID, B.CAT_DTL_CONT_HAZARD, B.CAT_DTL_EXIM, B.CAT_DTL_ACTIVITY, B.CAT_DTL_OWNER
						FROM TX_CATEGORY_HDR A
						JOIN TX_CATEGORY_DTL B ON A.CAT_HDR_ID = B.CAT_HDR_ID
						WHERE A.CAT_BRANCH_ID = ? AND A.CAT_HDR_ID = ?";
	return $this->db->query($query,$params)->result_array();
}

function getPlanCategoryById(){
	$branch_id = $this->session->USER_BRANCH;
	$params = array('BRANCH_ID' => $branch_id, 'ID' => $_POST['id']);
	$query = "SELECT CAT_HDR_ID, CAT_HDR_NAME FROM TX_CATEGORY_HDR WHERE CAT_BRANCH_ID = ? AND CAT_HDR_ID = ?";
	return $this->db->query($query,$params)->result_array();
}

function insertNewNameCategory(){
		$message = 'Category renamed';
	$this->db->trans_start();

	$this->db->set('CAT_HDR_NAME', $_POST['CATEGORY_NAME']);
	$this->db->where('CAT_HDR_ID', $_POST['CATEGORY_ID']);
	$this->db->update('TX_CATEGORY_HDR');

	$this->db->trans_complete();

	if ($this->db->trans_status() === FALSE)
	{
			$message = 'ERROR';
	}

	return array('message' => $message, 'id' => $_POST['CATEGORY_ID']);
}

function delete_yard_plan_group(){
	$ypg_id = $this->input->post('ypg_id');
	$success = 1;
	$this->db->trans_start();

	$this->db->delete('TX_YARD_PLAN', array('YP_YPG_ID' => $ypg_id));
	$this->db->delete('TX_YARD_PLAN_GROUP', array('YPG_ID' => $ypg_id));

	$this->db->trans_complete();

	if ($this->db->trans_status() === FALSE)
	{
			$success = 0;
	}
	return $success;
}

function delete_pa_weight(){
	$id = $this->input->post('id');
	$success = 1;
	$this->db->trans_start();

	$this->db->delete('TX_YARD_PLAN', array('YP_YPG_ID' => $id));
	$this->db->delete('TX_YARD_PLAN_GROUP', array('YPG_ID' => $id));

	$this->db->trans_complete();

	if ($this->db->trans_status() === FALSE)
	{
			$success = 0;
	}
	return $success;
}

function getYardBlock(){
	$branch_id = $this->session->USER_BRANCH;
	$yard_id = $this->input->get('yard_id');
	$where = '';

	if($yard_id != 0){
		$where = ' AND BLOCK_YARD_ID = '.$yard_id;
	}

	$params = array('BLOCK_BRANCH_ID' => $branch_id);
	$query = "SELECT * FROM TM_BLOCK WHERE BLOCK_ACTIVE = 'Y' AND BLOCK_BRANCH_ID = ? $where ";
	return $this->db->query($query,$params)->result_array();
}

function getYardRow(){
	$branch_id = $this->session->USER_BRANCH;
	$block_id = $this->input->get('block_id');
	$where = '';
	if($block_id != 0){
		$where = ' AND YBC_BLOCK_ID = '.$block_id;
	}
	$params = array('YBC_BRANCH_ID' => $branch_id);
	$query = "SELECT DISTINCT YBC_ROW FROM TX_YARD_BLOCK_CELL WHERE YBC_ACTIVE = 'Y' AND YBC_BRANCH_ID = ? $where ORDER BY YBC_ROW";
	return $this->db->query($query,$params)->result_array();
}

function getYardSlot(){
	$branch_id = $this->session->USER_BRANCH;
	$block_id = $this->input->get('block_id');
	$where = '';
	if($block_id != 0){
		$where = ' AND YBC_BLOCK_ID = '.$block_id;
	}
	$params = array('YBC_BRANCH_ID' => $branch_id);
	$query = "SELECT DISTINCT YBC_SLOT FROM TX_YARD_BLOCK_CELL WHERE YBC_ACTIVE = 'Y' AND YBC_BRANCH_ID = ? $where ORDER BY YBC_SLOT";
	return $this->db->query($query,$params)->result_array();
}

function getYardTiers(){
	$branch_id = $this->session->USER_BRANCH;
	$real_yard_id = $this->input->get('real_yard_id');
	$block_id = $this->input->get('block_id');
	$ybc_id = $this->input->get('ybc_id');

	$real_tier = $this->db->select('REAL_YARD_TIER')
												->from('TX_REAL_YARD')
												->where('REAL_YARD_ID',$real_yard_id)
												->where('REAL_YARD_STATUS',1)
												->get()->result_array();
	return $real_tier;
}

function getYardTier(){
	$branch_id = $this->session->USER_BRANCH;
	$block_id = $this->input->get('block_id');
	$ybc_id = $this->input->get('ybc_id');
	$tier = $this->db->select('BLOCK_TIER')
					 ->from('TM_BLOCK')
					 ->where('BLOCK_ACTIVE','Y')
					 ->where('BLOCK_BRANCH_ID',$branch_id)
					 ->where('BLOCK_ID',$block_id)
					 ->get()->row()->BLOCK_TIER;
	$count = array();
	$arrTier = array();

	// $real_tier = $this->db->select('REAL_YARD_TIER')
	// 											->from('TX_REAL_YARD')
	// 											->where('REAL_YARD_YBC_ID', $ybc_id)
	// 											->where('REAL_YARD_STATUS',1)
	// 											->get()->result_array();

	$real_tier = $this->db->query("SELECT MAX(Z.REAL_YARD_TIER) LAST_TIER FROM (SELECT I.REAL_YARD_TIER  FROM (
										 SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_YBC_ID = ".$ybc_id." AND H.REAL_YARD_BRANCH_ID = ".$branch_id."  GROUP BY H.REAL_YARD_CONT
									 )X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
							)Z")->row_array()['LAST_TIER'];

  if($real_tier == 0){
		$real_tier = 1;
	}
	else{
		$real_tier = $real_tier + 1;
	}

// foreach ($real_tier as $value) {
// 	$arrTier[] = $value['REAL_YARD_TIER'];
// }
//
// 	for ($i=1; $i <= (int)$tier; $i++) {
// 		if(!in_array($i,$arrTier)){
// 		$count[] = $i;
// 		}
// 	}

	return array(array('BLOCK_TIER' => $real_tier));
}

public function extract_yard_monitoring($id_yard){
$params = array(
	'YARD_ID' => $id_yard,
	'BRANCH_ID' => $this->session->USER_BRANCH
);
$xml_str = "";
$query 		= "SELECT * FROM TM_YARD WHERE YARD_ID='$id_yard'";
$rs 		= $this->db->query($query);
$row 		= $rs->row_array();

$width_str = "<width>".$row['YARD_WIDTH']."</width>";
$height_str = "<height>".$row['YARD_HEIGHT']."</height>";

$query 		= "SELECT A.YBC_YARD_ID ,A.YBC_CELL_ID, B.BLOCK_ID, B.BLOCK_NAME, B.BLOCK_POSITION, B.BLOCK_ORIENTATION, B.BLOCK_TIER, A.YBC_ROW, A.YBC_SLOT, B.BLOCK_ROW MAX_ROW, B.BLOCK_SLOT MAX_SLOT,
						MAX(C.YP_STATUS) FLAG_STATUS,SUM(DECODE(NVL(C.YP_STATUS,0),0,0,1)) JML_TAKEN, 0 PLACEMENT, NVL(FUNC_GET_TOT_STACK(A.YBC_ID),0) JML_PLACEMENT, B.BLOCK_ROW, B.BLOCK_SLOT, D.YARD_HEIGHT, D.YARD_WIDTH, A.YBC_ID
						FROM TX_YARD_BLOCK_CELL A
						JOIN TM_BLOCK B ON A.YBC_BLOCK_ID = B.BLOCK_ID
						LEFT JOIN TX_YARD_PLAN C ON A.YBC_ID = C.YP_YBC_ID
						JOIN TM_YARD D ON B.BLOCK_YARD_ID = D.YARD_ID
						WHERE A.YBC_ACTIVE = 'Y' AND B.BLOCK_ACTIVE = 'Y' AND A.YBC_YARD_ID = ? AND A.YBC_BRANCH_ID = ?
						GROUP BY A.YBC_YARD_ID ,A.YBC_CELL_ID, B.BLOCK_ID, B.BLOCK_NAME, B.BLOCK_POSITION, B.BLOCK_ORIENTATION, B.BLOCK_TIER, A.YBC_ROW, A.YBC_SLOT, B.BLOCK_ROW, B.BLOCK_SLOT, D.YARD_HEIGHT, D.YARD_WIDTH, A.YBC_ID
						ORDER BY A.YBC_CELL_ID";
$rs 		  = $this->db->query($query,$params);
$data 		= $rs->result_array();

$index_stack = array();
$index_plan = array();
$index_taken = array();
$index_placement = array();
$index_slot = array();
$index_row = array();
$index_tier = array();
$index_title = array();
$index_block_id = array();
$index_orientation = array();
$index_position = array();
$index_label = array();
$index_label_text = array();
$index_ybc_id = array();
$max_row = array();
$max_slot = array();

$y = 1;
$z = 0;
$i = 1;

$total_cell = $row['YARD_WIDTH'] * $row['YARD_HEIGHT'];

for ($e=0; $e < $total_cell; $e++) {

	foreach($data as $row){

		if($row['YBC_CELL_ID'] == $e){
			$index_ybc_id[] = $row['YBC_ID'];
			$index_stack[] = $row['YBC_CELL_ID'];
			$index_slot[] = $row['YBC_SLOT'];
			$index_row[] = $row['YBC_ROW'];
			$max_slot[] = $row['MAX_SLOT'];
			$max_row[] = $row['MAX_ROW'];
			$index_tier[] = $row['BLOCK_TIER'];
			$index_title[] = $row['BLOCK_NAME'];
			$index_block_id[] = $row['BLOCK_ID'];
			$index_orientation[] = $row['BLOCK_ORIENTATION'];
			$index_position[] = $row['BLOCK_POSITION'];
			$index_placement[] = $row['JML_PLACEMENT'];
			$delta = $row['JML_TAKEN']-$row['JML_PLACEMENT'];
			$index_taken[] = $delta;

			if ($row['FLAG_STATUS'] != ""){
				$index_plan[] = $row['YBC_CELL_ID'];
			}

			if(($row['BLOCK_ORIENTATION'] == 'TR') && ($row['BLOCK_POSITION'] == 'H')){ // Top right posisi horizontal
				if(($row['YBC_SLOT'] >1) && ($row['YBC_ROW'] ==1)){
					$index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
					$index_label_text[] = $row['YBC_SLOT'];
				}else{
					if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] ==1)){
						$index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
						$index_label_text[] = $row['YBC_SLOT'];

						$index_label[] = $row['YBC_CELL_ID']+1;
						$index_label_text[] = $row['YBC_ROW'];

					}else if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] >1)){
						$index_label[] = $row['YBC_CELL_ID']+1;
						$index_label_text[] = $row['YBC_ROW'];
					}
				}
			}else if(($row['BLOCK_ORIENTATION'] == 'TR') && ($row['BLOCK_POSITION'] == 'V')){ // Top right posisi vertical
				if(($row['YBC_ROW'] >1) && ($row['YBC_SLOT'] ==1)){
					$index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
					$index_label_text[] = $row['YBC_ROW'];
				}else{
					if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] ==1)){
						$index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
						$index_label_text[] = $row['YBC_ROW'];

						$index_label[] = $row['YBC_CELL_ID']+1;
						$index_label_text[] = $row['YBC_SLOT'];

					}else if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] >1)){
						$index_label[] = $row['YBC_CELL_ID']+1;
						$index_label_text[] = $row['YBC_SLOT'];
					}
				}
			}
			else if(($row['BLOCK_ORIENTATION'] == 'TL') && ($row['BLOCK_POSITION'] == 'H')){ // Top left posisi horizontal
				if(($row['YBC_SLOT'] >1) && ($row['YBC_ROW'] ==1)){
					 $index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
					 $index_label_text[] = $row['YBC_SLOT'];
				}else{
				 if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] ==1)){
						 $index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
						 $index_label_text[] = $row['YBC_SLOT'];

						 $index_label[] = $row['YBC_CELL_ID']-1;
						 $index_label_text[] = $row['YBC_ROW'];

				 }else if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] >1)){
						 $index_label[] = $row['YBC_CELL_ID']-1;
						 $index_label_text[] = $row['YBC_ROW'];
				 }
				}
			}
			else if(($row['BLOCK_ORIENTATION'] == 'TL') && ($row['BLOCK_POSITION'] == 'V')){ // Top left posisi vertical
				if(($row['YBC_ROW'] >1) && ($row['YBC_SLOT'] ==1)){
						$index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
						$index_label_text[] = $row['YBC_ROW'];
				}else{
					if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] ==1)){
						$index_label[] = $row['YBC_CELL_ID']-$row['YARD_WIDTH'];
						$index_label_text[] = $row['YBC_ROW'];

						$index_label[] = $row['YBC_CELL_ID']-1;
						$index_label_text[] = $row['YBC_SLOT'];

					}else if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] >1)){
						$index_label[] = $row['YBC_CELL_ID']-1;
						$index_label_text[] = $row['YBC_SLOT'];
					}
				}
			}
			else if(($row['BLOCK_ORIENTATION'] == 'BR') && ($row['BLOCK_POSITION'] == 'H')){ // button right posisi horizontal
				if(($row['YBC_SLOT'] >1) && ($row['YBC_ROW'] ==1)){
				 $index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
				 $index_label_text[] = $row['YBC_SLOT'];
			 }else{
				 if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] ==1)){
					 $index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
					 $index_label_text[] = $row['YBC_SLOT'];

					 $index_label[] = $row['YBC_CELL_ID']+1;
					 $index_label_text[] = $row['YBC_ROW'];

				 }else if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] >1)){
					 $index_label[] = $row['YBC_CELL_ID']+1;
					 $index_label_text[] = $row['YBC_ROW'];
				 }
			 }
			}
			else if(($row['BLOCK_ORIENTATION'] == 'BR') && ($row['BLOCK_POSITION'] == 'V')){ // button right posisi vertical
				if(($row['YBC_ROW'] >1) && ($row['YBC_SLOT'] ==1)){
					$index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
					$index_label_text[] = $row['YBC_ROW'];
				}else{
					if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] ==1)){
						$index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
						$index_label_text[] = $row['YBC_ROW'];

						$index_label[] = $row['YBC_CELL_ID']+1;
						$index_label_text[] = $row['YBC_SLOT'];

					}else if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] >1)){
						$index_label[] = $row['YBC_CELL_ID']+1;
						$index_label_text[] = $row['YBC_SLOT'];
					}
				}
			}
			else if(($row['BLOCK_ORIENTATION'] == 'BL') && ($row['BLOCK_POSITION'] == 'H')){ // Top left posisi horizontal
				if(($row['YBC_SLOT'] >1) && ($row['YBC_ROW'] ==1)){
					 $index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
					 $index_label_text[] = $row['YBC_SLOT'];
				}else{
				 if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] ==1)){
						 $index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
						 $index_label_text[] = $row['YBC_SLOT'];

						 $index_label[] = $row['YBC_CELL_ID']-1;
						 $index_label_text[] = $row['YBC_ROW'];

				 }else if(($row['YBC_SLOT'] ==1) && ($row['YBC_ROW'] >1)){
						 $index_label[] = $row['YBC_CELL_ID']-1;
						 $index_label_text[] = $row['YBC_ROW'];
				 }
				}
			}
			else if(($row['BLOCK_ORIENTATION'] == 'BL') && ($row['BLOCK_POSITION'] == 'V')){ // Top left posisi vertical
				if(($row['YBC_ROW'] >1) && ($row['YBC_SLOT'] ==1)){
						$index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
						$index_label_text[] = $row['YBC_ROW'];
				}else{
					if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] ==1)){
						$index_label[] = $row['YBC_CELL_ID']+$row['YARD_WIDTH'];
						$index_label_text[] = $row['YBC_ROW'];

						$index_label[] = $row['YBC_CELL_ID']-1;
						$index_label_text[] = $row['YBC_SLOT'];

					}else if(($row['YBC_ROW'] ==1) && ($row['YBC_SLOT'] >1)){
						$index_label[] = $row['YBC_CELL_ID']-1;
						$index_label_text[] = $row['YBC_SLOT'];
					}
				}
			}
		}
	}
}

$ybc_id_ = implode(",",$index_ybc_id);
$stack_ = implode(",",$index_stack);
$plan_ = implode(",",$index_plan);
$taken_ = implode(",",$index_taken);
$placement_ = implode(",",$index_placement);
$slot_ = implode(",",$index_slot);
$row_ = implode(",",$index_row);
$max_slot_ = implode(",",$max_slot);
$max_row_ = implode(",",$max_row);
$tier_ = implode(",",$index_tier);
$title = implode(",",$index_title);
$block_id = implode(",",$index_block_id);
$orientation = implode(",",$index_orientation);
$position = implode(",",$index_position);
$label_ = implode(",",$index_label);
$label_text_ = implode(",",$index_label_text);

$ybc_id_str = "<ybc_id>".$ybc_id_."</ybc_id>";
$stack_str = "<index>".$stack_."</index>";
$plan_str = "<plan>".$plan_."</plan>";
$taken_str = "<taken>".$taken_."</taken>";
$placement_str = "<placement>".$placement_."</placement>";
$slot_str = "<slot>".$slot_."</slot>";
$row_str = "<row>".$row_."</row>";
$max_slot_str = "<max_slot>".$max_slot_."</max_slot>";
$max_row_str = "<max_row>".$max_row_."</max_row>";
$tier_str = "<tier>".$tier_."</tier>";
$title_str = "<title>".$title."</title>";
$block_id_str = "<block_id>".$block_id."</block_id>";
$orientation_str = "<orientation>".$orientation."</orientation>";
$position_str = "<position>".$position."</position>";
$label_str = "<label>".$label_."</label>";
$label_text_str = "<label_text>".$label_text_."</label_text>";

$xml_str = "<yard>".$width_str.$height_str.$stack_str.$plan_str.$taken_str.$placement_str.$slot_str.$max_slot_str.$row_str.$max_row_str.$tier_str.$title_str.$block_id_str.$orientation_str.$position_str.$label_str.$label_text_str.$ybc_id_str."</yard>";

return $xml_str;
}

  public function get_slot_configuration($id_yard, $block){
    $query = "select row_ max_row, tier_ max_tier
      from m_yardblock
      where id_yard = $id_yard and block_name = '$block'";
    $rs     = $this->db->query($query);
    $data     = $rs->row();
    return $data;
  }

  public function get_container_in_yard_data($id_yard, $block, $id_slot, $size){
    $query = "SELECT yb.block_name,
         c.yd_slot,
         c.yd_row,
         c.yd_tier,
         c.no_container,
         c.point,
         case c.id_class_code
           when 'E' then 'EXPORT'
           when 'I' then 'IMPORT'
         end id_class_code,
         c.id_ves_voyage,
         vv.id_vessel,
         c.id_iso_code,
         c.cont_size,
         c.cont_type,
         c.cont_status,
         c.cont_height,
         c.id_pod,
         c.id_operator,
         (c.weight / 1000) weight,
         c.id_commodity,
         c.hazard,
         c.id_spec_hand,
         case when c.imdg is null then '0'
         else c.imdg end IMDG
      FROM con_listcont c
      LEFT JOIN m_yardblock yb
        ON (c.yd_block = yb.id_block)
      LEFT JOIN ves_voyage vv
        ON (c.id_ves_voyage = vv.id_ves_voyage)
      WHERE c.yd_yard = $id_yard
      and yb.block_name = '$block'
      and (
        (c.yd_slot = $id_slot and '$size' = '20') or
        (c.yd_slot in ($id_slot-1, $id_slot) and '$size' = '40')
      )
      and c.id_op_status in ('YYY', 'YGY', 'YSY')
      ORDER BY c.yd_slot, c.yd_row, c.yd_tier";
      // echo $query;
    $rs     = $this->db->query($query);
    $data     = $rs->result_array();

    return $data;
  }

  public function get_list_block_slot($id_yard){
    $query    = "SELECT id_block, block_name, slot_ slot
        FROM m_yardblock
         WHERE id_yard = $id_yard
      ORDER BY block_name";
    $rs     = $this->db->query($query);
    $data     = $rs->result_array();

    return $data;
  }
//END

	public function get_yard_stacking($id_yard, $block, $slot, $ybc_id){
		$branch_id = $this->session->USER_BRANCH;
		$query = $this->db->select('C.BLOCK_NAME, B.YBC_ROW, B.YBC_SLOT, A.REAL_YARD_TIER, A.REAL_YARD_CONT, A.REAL_YARD_CONT_SIZE, A.REAL_YARD_CONT_TYPE, A.REAL_YARD_COMMODITY')
		->from('TX_REAL_YARD A')
		->join('TX_YARD_BLOCK_CELL B','B.YBC_ID = A.REAL_YARD_YBC_ID')
		->join('TM_BLOCK C','C.BLOCK_ID = B.YBC_BLOCK_ID')
		->where('REAL_YARD_BRANCH_ID',$branch_id)
		->where('A.REAL_YARD_NO',$id_yard)
		->where('B.YBC_BLOCK_ID',$block)
		->where('A.REAL_YARD_STATUS',1)
		->where('B.YBC_SLOT',$slot)
		->where('B.YBC_ACTIVE','Y')
		->where('C.BLOCK_ACTIVE','Y')
		->get()->result_array();

		$query2 = $this->db->query("SELECT * FROM (
    SELECT C.BLOCK_NAME, B.YBC_ROW, B.YBC_SLOT, A.REAL_YARD_TIER, A.REAL_YARD_CONT, A.REAL_YARD_CONT_SIZE, A.REAL_YARD_CONT_TYPE, A.REAL_YARD_COMMODITY, A.REAL_YARD_STATUS, A.REAL_YARD_CREATE_DATE, A.REAL_YARD_YBC_ID
    FROM TX_REAL_YARD A
    JOIN TX_YARD_BLOCK_CELL B ON B.YBC_ID = A.REAL_YARD_YBC_ID
    JOIN TM_BLOCK C ON C.BLOCK_ID = B.YBC_BLOCK_ID
    WHERE A. REAL_YARD_BRANCH_ID = ".$branch_id." AND B.YBC_ACTIVE = 'Y' AND C.BLOCK_ACTIVE = 'Y'
    AND A.REAL_YARD_ID IN (
    	SELECT X.REAL_YARD_ID  FROM (
			 SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_YBC_ID IN(SELECT YBC_ID FROM TX_YARD_BLOCK_CELL WHERE YBC_BLOCK_ID = ".$block." AND YBC_SLOT = ".$slot." AND YBC_ACTIVE = 'Y') GROUP BY H.REAL_YARD_CONT
		 )X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
    	)
		)Z")->result_array();

		return $query2;
	}

	public function get_slot_config($id_yard, $block){
		$branch_id = $this->session->USER_BRANCH;
		$query = $this->db->select('BLOCK_ROW MAX_ROW, BLOCK_SLOT MAX_SLOT, BLOCK_TIER MAX_TIER')
		->from('TM_BLOCK')
		->where('BLOCK_BRANCH_ID',$branch_id)
		->where('BLOCK_YARD_ID',$id_yard)
		->where('BLOCK_ID',$block)
		->where('BLOCK_ACTIVE','Y')
		->get()->row_array();

		return $query;
	}

	public function get_slot_list($id_yard){
		$branch_id = $this->session->USER_BRANCH;
		$query = $this->db->select('BLOCK_ID, BLOCK_NAME, BLOCK_SLOT')
		->from('TM_BLOCK')
		->where('BLOCK_BRANCH_ID',$branch_id)
		->where('BLOCK_YARD_ID',$id_yard)
		->where('BLOCK_ACTIVE','Y')
		->get()->result_array();

		return $query;
	}

	public function get_container_report($id_yard, $block){
		$branch_id = $this->session->USER_BRANCH;

    $params = array($branch_id, $id_yard, $block);

    $sql = "SELECT *  FROM YARD_REPORT_VIEW
            WHERE REAL_YARD_BRANCH_ID = ?
            AND REAL_YARD_NO = ?
            AND YBC_BLOCK_ID = ?
            AND REAL_YARD_STATUS = 1
            ORDER BY YBC_SLOT ASC,  YBC_ROW ASC,REAL_YARD_TIER ASC";

    $data = $this->db->query($sql,$params)->result_array();

		return $data;
	}

	function get_yard_name($id_yard){
		$branch_id = $this->session->USER_BRANCH;
		$this->db->select('A.YARD_ID, A.YARD_NAME');
		$this->db->from('TM_YARD A');
		$this->db->where('YARD_BRANCH_ID',$branch_id)->where('YARD_ID',$id_yard);
		return $this->db->get()->row_array()['YARD_NAME'];
	}

	function get_block_name($yard_id,$idblock){
		$branch_id = $this->session->USER_BRANCH;
		$this->db->where('BLOCK_BRANCH_ID',$branch_id);
		$this->db->where('BLOCK_YARD_ID',$yard_id);
		$this->db->where('BLOCK_ACTIVE','Y');
		$this->db->where('BLOCK_ID',$idblock);
		return $this->db->get('TM_BLOCK')->row_array()['BLOCK_NAME'];
	}

	function planOwner(){
		$branch_id = $this->session->USER_BRANCH;
		$data = $this->db->where('OWNER_BRANCH_ID',$branch_id)->get('TM_OWNER')->result_array();
		return $data;
	}

	public function getYardUser(){
		$branch_id = $this->input->get('BRANCH_ID');
		$this->db->select('A.YARD_ID, A.YARD_NAME');
		$this->db->from('TM_YARD A');
		$this->db->where('YARD_BRANCH_ID',$branch_id);
		return $this->db->get()->result_array();
	}

	public function getPrintYardPlan(){
		$branch_id = $this->session->USER_BRANCH;
		$params = array(
			'BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);

		$paramsTotal = array($this->session->USER_BRANCH);

		$qWhere = "";
		$YARD_ID = $_REQUEST['YARD_ID'] != null? $_REQUEST['YARD_ID'] : false;

		$qw = '';
		if($YARD_ID != false && $YARD_ID != 0){
			$qWhere .= " AND YPG_YARD_ID = ".$YARD_ID;
		}

		$qs = '';
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
		if ($filters != false){
			for ($i=0;$i<count($filters);$i++){
				$filter = $filters[$i];
					$field = $filter->property;
					$value = $filter->value;
				$qs .= " AND LOWER(".$field.") LIKE '%".strtolower($value)."%'";

			}
			$qWhere .= $qs;
		}

		$sql = "SELECT * FROM
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						SELECT
							D.CAT_HDR_NAME GROUP_NAME,
							B.YARD_NAME,
							C.BLOCK_NAME,
							A.YPG_START_SLOT||'-'||A.YPG_END_SLOT AS SLOT_RANGE,
							A.YPG_STAR_ROW||'-'||A.YPG_END_ROW AS ROW_RANGE,
							E.CAT_DTL_CONT_SIZE,
							E.CAT_DTL_CONT_TYPE,
							E.CAT_DTL_OWNER|| ' - ' || F.OWNER_NAME OWNER,
							A.YPG_YARD_ID
						FROM TX_YARD_PLAN_GROUP A
						JOIN TM_YARD B ON A.YPG_YARD_ID = B.YARD_ID
						JOIN TM_BLOCK C ON A.YPG_BLOCK_ID = C.BLOCK_ID
						JOIN TX_CATEGORY_HDR D ON A.YPG_CAT_HDR_ID = D.CAT_HDR_ID
						JOIN TX_CATEGORY_DTL E ON E.CAT_HDR_ID = D.CAT_HDR_ID
						LEFT JOIN TM_OWNER F ON F.OWNER_CODE = E.CAT_DTL_OWNER AND F.OWNER_BRANCH_ID = A.YPG_BRANCH_ID
						WHERE C.BLOCK_ACTIVE = 'Y' AND A.YPG_BRANCH_ID = ?
						ORDER BY A.YPG_YARD_ID ,C.BLOCK_NAME, E.CAT_DTL_OWNER, E.CAT_DTL_CONT_SIZE
					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1" .$qWhere;

		$data = $this->db->query($sql,$params)->result_array();

		$sqlTotal = "SELECT * FROM(
    					SELECT
							D.CAT_HDR_NAME GROUP_NAME,
							B.YARD_NAME,
							C.BLOCK_NAME,
							A.YPG_START_SLOT||'-'||A.YPG_END_SLOT AS SLOT_RANGE,
							A.YPG_STAR_ROW||'-'||A.YPG_END_ROW AS ROW_RANGE,
							E.CAT_DTL_CONT_SIZE,
							E.CAT_DTL_CONT_TYPE,
							E.CAT_DTL_OWNER|| ' - ' || F.OWNER_NAME OWNER,
							A.YPG_YARD_ID
						FROM TX_YARD_PLAN_GROUP A
						JOIN TM_YARD B ON A.YPG_YARD_ID = B.YARD_ID
						JOIN TM_BLOCK C ON A.YPG_BLOCK_ID = C.BLOCK_ID
						JOIN TX_CATEGORY_HDR D ON A.YPG_CAT_HDR_ID = D.CAT_HDR_ID
						JOIN TX_CATEGORY_DTL E ON E.CAT_HDR_ID = D.CAT_HDR_ID
						LEFT JOIN TM_OWNER F ON F.OWNER_CODE = E.CAT_DTL_OWNER AND F.OWNER_BRANCH_ID = A.YPG_BRANCH_ID
						WHERE C.BLOCK_ACTIVE = 'Y' AND A.YPG_BRANCH_ID = ?
						ORDER BY A.YPG_YARD_ID ,C.BLOCK_NAME, E.CAT_DTL_OWNER, E.CAT_DTL_CONT_SIZE) WHERE 1=1 " . $qWhere;

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
	}

	public function getPrintYardPlanPdf(){
		$branch_id = $this->session->USER_BRANCH;

		$qWhere = "";
		$YARD_ID = $_GET['YARD_ID'] != null? $_GET['YARD_ID'] : false;

		$qw = '';
		if($YARD_ID != false && $YARD_ID != 0){
			$qWhere .= " AND YPG_YARD_ID =".$YARD_ID;
		}

		$query = $this->db->query("SELECT * FROM(
					SELECT
						D.CAT_HDR_NAME GROUP_NAME,
						B.YARD_NAME,
						C.BLOCK_NAME,
						A.YPG_START_SLOT||'-'||A.YPG_END_SLOT AS SLOT_RANGE,
						A.YPG_STAR_ROW||'-'||A.YPG_END_ROW AS ROW_RANGE,
						E.CAT_DTL_CONT_SIZE,
						E.CAT_DTL_CONT_TYPE,
						E.CAT_DTL_OWNER|| ' - ' || F.OWNER_NAME OWNER,
						A.YPG_YARD_ID
					FROM TX_YARD_PLAN_GROUP A
					JOIN TM_YARD B ON A.YPG_YARD_ID = B.YARD_ID
					JOIN TM_BLOCK C ON A.YPG_BLOCK_ID = C.BLOCK_ID
					JOIN TX_CATEGORY_HDR D ON A.YPG_CAT_HDR_ID = D.CAT_HDR_ID
					JOIN TX_CATEGORY_DTL E ON E.CAT_HDR_ID = D.CAT_HDR_ID
					LEFT JOIN TM_OWNER F ON F.OWNER_CODE = E.CAT_DTL_OWNER AND F.OWNER_BRANCH_ID = A.YPG_BRANCH_ID
					WHERE C.BLOCK_ACTIVE = 'Y' AND A.YPG_BRANCH_ID = $branch_id
					ORDER BY A.YPG_YARD_ID ,C.BLOCK_NAME, E.CAT_DTL_OWNER, E.CAT_DTL_CONT_SIZE) WHERE 1=1 " . $qWhere)->result_array();

		return $query;
	}
}
