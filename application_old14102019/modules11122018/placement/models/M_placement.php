<?php
class M_placement extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	function getContainer(){
		  $branch_id = $this->session->USER_BRANCH;
		  $activity = $_REQUEST['id'];
			$origin = $_REQUEST['origin'];
			$data = null;
			if($_REQUEST['id'] == 3){
			  $data = $this->db->select('A.GATE_ID ID, A.GATE_CONT CONT')
			 					->from('TX_GATE A')
								->where('A.GATE_BRANCH_ID', $branch_id)
								->where('A.GATE_STATUS',1)
								->where('A.GATE_ACTIVITY',$activity)
								->where('A.GATE_STACK_STATUS',0)
								->where('A.GATE_ORIGIN',$origin)
								->order_by('A.GATE_CONT','ASC')
								->get()->result_array();
			}
			else if($_REQUEST['id'] == 2){
				// $data = $this->db->select('A.REAL_YARD_ID ID, A.REAL_YARD_CONT CONT')
				// 				->from('TX_REAL_YARD A')
				// 				->join('TX_REQ_STUFF_DTL B','B.STUFF_DTL_CONT = A.REAL_YARD_CONT')
				// 				->join('TX_REQ_STUFF_HDR C','C.STUFF_ID = B.STUFF_DTL_HDR_ID AND C.STUFF_STATUS !=2')
				// 				->where('A.REAL_YARD_BRANCH_ID',$branch_id)
				// 				->where('A.REAL_YARD_STATUS',1)
				// 				->get()->result_array();
			  $data = $this->db->query("SELECT A.REAL_YARD_ID ID, A.REAL_YARD_CONT CONT
								FROM TX_REAL_YARD A
								JOIN TX_REQ_STUFF_DTL B ON B.STUFF_DTL_CONT = A.REAL_YARD_CONT
								JOIN TX_REQ_STUFF_HDR C ON C.STUFF_ID = B.STUFF_DTL_HDR_ID AND C.STUFF_STATUS !=2
								WHERE A.REAL_YARD_BRANCH_ID = ".$branch_id."
								AND A.REAL_YARD_STATUS = 1
								AND A.REAL_YARD_ID IN (
														SELECT X.REAL_YARD_ID FROM (
															SELECT MAX(H.REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_BRANCH_ID = ".$branch_id." GROUP BY H.REAL_YARD_CONT
														)X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
								)")->result_array();
			}
			else if($_REQUEST['id'] == 1){
				// $data = $this->db->select('A.REAL_YARD_ID ID, A.REAL_YARD_CONT CONT')
				// 				->from('TX_REAL_YARD A')
				// 				->join('TX_REQ_STRIP_DTL B','B.STRIP_DTL_CONT = A.REAL_YARD_CONT')
				// 				->join('TX_REQ_STRIP_HDR C','C.STRIP_ID = B.STRIP_DTL_HDR_ID AND C.STRIP_STATUS !=2')
				// 				->where('A.REAL_YARD_BRANCH_ID',$branch_id)
				// 				->where('A.REAL_YARD_STATUS',1)
				// 				->get()->result_array();
						$data = $this->db->query("SELECT A.REAL_YARD_ID ID, A.REAL_YARD_CONT CONT
										FROM TX_REAL_YARD A
										JOIN TX_REQ_STRIP_DTL B ON B.STRIP_DTL_CONT = A.REAL_YARD_CONT
										JOIN TX_REQ_STRIP_HDR C ON C.STRIP_ID = B.STRIP_DTL_HDR_ID AND C.STRIP_STATUS !=2
										WHERE A.REAL_YARD_BRANCH_ID = ".$branch_id."
										AND A.REAL_YARD_STATUS = 1
										AND A.REAL_YARD_ID IN (
																SELECT X.REAL_YARD_ID FROM (
																	SELECT MAX(H.REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_BRANCH_ID = ".$branch_id." GROUP BY H.REAL_YARD_CONT
																)X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
										)")->result_array();
			}
			else{
				$data = $this->db->select('A.REAL_YARD_ID ID, A.REAL_YARD_CONT CONT')
								->from('TX_REAL_YARD A')
								->where('A.REAL_YARD_BRANCH_ID',$branch_id)
								->where('A.REAL_YARD_STATUS',1)
								->get()->result_array();
			}
	    return $data;
	}

	function getContainerById(){
		 $id = $this->input->post('id');
		 $activity = $this->input->post('activity');
		 $branch_id = $this->session->USER_BRANCH;
		 $data = null;
		 if($activity == 3){
	   $data = $this->db->select('A.GATE_ID ID, A.GATE_NOREQ REQ, A.GATE_CONT CONT, A.GATE_CONT_SIZE SIZE, A.GATE_CONT_TYPE TYPE, A.GATE_CONT_STATUS STATUS')
		 					->from('TX_GATE A')
							->where('A.GATE_BRANCH_ID', $branch_id)
							->where('A.GATE_STATUS',1)
							->where('A.GATE_STACK_STATUS',0)
							->where('A.GATE_ID',$id)
							->get()->row_array();
			}
			else if ($activity == 2){
				$data = $this->db->select('C.STUFF_NO ID, C.STUFF_NO REQ, A.REAL_YARD_CONT CONT, A.REAL_YARD_CONT_SIZE SIZE, A.REAL_YARD_CONT_TYPE TYPE, A.REAL_YARD_CONT_STATUS STATUS, A.REAL_YARD_YBC_ID YBC_ID')
								->from('TX_REAL_YARD A')
								->join('TX_REQ_STUFF_DTL B','B.STUFF_DTL_CONT = A.REAL_YARD_CONT')
								->join('TX_REQ_STUFF_HDR C','C.STUFF_ID = B.STUFF_DTL_HDR_ID AND C.STUFF_STATUS !=2')
								->where('A.REAL_YARD_BRANCH_ID',$branch_id)
								->where('A.REAL_YARD_STATUS',1)
								->where('A.REAL_YARD_ID',$id)
								->get()->row_array();
			}
			else if($activity == 1){
				$data = $this->db->select('C.STRIP_NO ID, C.STRIP_NO REQ, A.REAL_YARD_CONT CONT, A.REAL_YARD_CONT_SIZE SIZE, A.REAL_YARD_CONT_TYPE TYPE, A.REAL_YARD_CONT_STATUS STATUS, A.REAL_YARD_YBC_ID YBC_ID')
								->from('TX_REAL_YARD A')
								->join('TX_REQ_STRIP_DTL B','B.STRIP_DTL_CONT = A.REAL_YARD_CONT')
								->join('TX_REQ_STRIP_HDR C','C.STRIP_ID = B.STRIP_DTL_HDR_ID AND C.STRIP_STATUS !=2')
								->where('A.REAL_YARD_BRANCH_ID',$branch_id)
								->where('A.REAL_YARD_STATUS',1)
								->where('A.REAL_YARD_ID',$id)
								->get()->row_array();
			}
			else{
				$data = $this->db->select('A.REAL_YARD_REQ_NO ID, A.REAL_YARD_REQ_NO REQ, A.REAL_YARD_CONT CONT, A.REAL_YARD_CONT_SIZE SIZE, A.REAL_YARD_CONT_TYPE TYPE, A.REAL_YARD_CONT_STATUS STATUS, A.REAL_YARD_YBC_ID YBC_ID')
								->from('TX_REAL_YARD A')
								->where('A.REAL_YARD_BRANCH_ID',$branch_id)
								->where('A.REAL_YARD_STATUS',1)
								->where('A.REAL_YARD_ID',$id)
								->get()->row_array();
			}

			// SET LOCATION
			$loc_req_id = $data['REQ'];
			$loc_block = '';
			$loc_slot = '';
			$loc_yard = '';
			$cont_rec = $this->db->select("A.REQUEST_DTL_CONT, A.REQUEST_DTL_CONT_SIZE, A.REQUEST_DTL_CONT_TYPE, B.REQUEST_DI DI, A.REQUEST_DTL_CONT_STATUS, A.REQUEST_DTL_OWNER_CODE, A.REQUEST_DTL_OWNER_NAME")
									->from('TX_REQ_RECEIVING_DTL A')
									->join('TX_REQ_RECEIVING_HDR B','A.REQUEST_HDR_ID = B.REQUEST_ID')
									->where('B.REQUEST_BRANCH_ID',$branch_id)
									->where('B.REQUEST_NO',$loc_req_id)
									->get()->row_array();

				//crate array for print
				$cont_size = $cont_rec['REQUEST_DTL_CONT_SIZE'];
				$cont_status = $cont_rec['REQUEST_DTL_CONT_STATUS'];
				$cont_type = $cont_rec['REQUEST_DTL_CONT_TYPE'];
				$di = $cont_rec['DI'];
				$owner = $cont_rec['REQUEST_DTL_OWNER_CODE'];
				$yard_active = $this->session->YARD_ACTIVE;

				$ownerExist = $this->db->select('A.CAT_DTL_OWNER')->from('TX_CATEGORY_DTL A')->join('TX_CATEGORY_HDR B','B.CAT_HDR_ID = A.CAT_HDR_ID')->where('CAT_BRANCH_ID',$branch_id)->where('CAT_DTL_OWNER',$owner)->count_all_results();
				$arrConfing = $this->db->select('CONFIG, STATUS, DETAIL')->from('TM_CMS_CONFIG')->where('BRANCH',$branch_id)->where('STATUS','Y')->get()->result_array();
				$where = '1=1 AND B.CAT_BRANCH_ID = '.$branch_id.' AND C.YPG_YARD_ID = '.$yard_active;
				foreach ($arrConfing as $key => $val) {
						if($val['DETAIL'] == 'SIZE'){
							$where .= " AND A.".$val['CONFIG']."='".$cont_size."'";
						}
						else if($val['DETAIL'] == 'TYPE'){
							 $where .= " AND A.".$val['CONFIG']."='".$cont_type."'";
						}
						else if($val['DETAIL'] == 'STATUS'){
							 $where .= " AND A.".$val['CONFIG']."='".$cont_status."'";
						}
						else if($val['DETAIL'] == 'D/I'){
							 $where .= " AND A.".$val['CONFIG']."='".$di."'";
						}
						else if($val['DETAIL'] == 'OWNER'){
							if($ownerExist > 0){
							 $where .= " AND A.".$val['CONFIG']."='".$owner."'";
							}
						}
				}

					$lokasi = $this->db->query("SELECT B.CAT_HDR_ID, A.CAT_DTL_CONT_SIZE CONT, A.CAT_DTL_CONT_STATUS, A.CAT_DTL_CONT_TYPE, A.CAT_DTL_EXIM, C.YPG_YARD_ID, C.YPG_BLOCK_ID, C.YPG_STAR_ROW, C.YPG_END_ROW, C.YPG_START_SLOT, C.YPG_END_SLOT, C.YPG_CAPACITY, '' DI, D.BLOCK_NAME, E.YARD_NAME
												FROM TX_CATEGORY_DTL A
												JOIN TX_CATEGORY_HDR B ON B.CAT_HDR_ID = A.CAT_HDR_ID
												JOIN TX_YARD_PLAN_GROUP C ON C.YPG_CAT_HDR_ID = B.CAT_HDR_ID
												JOIN TM_BLOCK D ON D.BLOCK_ID = C.YPG_BLOCK_ID AND D.BLOCK_ACTIVE = 'Y'
												JOIN TM_YARD E ON E.YARD_ID = C.YPG_YARD_ID
												WHERE ".$where)->result_array();

					foreach ($lokasi as $loc) {
						// cek capacity block
						$stack_capacity = $this->db->query("SELECT COUNT(*) STACK_CAPACITY  FROM ( SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H  GROUP BY H.REAL_YARD_CONT) X
									 INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID
									 INNER JOIN TX_YARD_BLOCK_CELL J ON J.YBC_ID = I.REAL_YARD_YBC_ID
									 INNER JOIN TM_BLOCK K ON K.BLOCK_ID = J.YBC_BLOCK_ID
									 WHERE J.YBC_ACTIVE = 'Y' AND K.BLOCK_ACTIVE = 'Y' AND I.REAL_YARD_STATUS = 1 AND J.YBC_BLOCK_ID = '".$loc['YPG_BLOCK_ID']."'")->row_array()['STACK_CAPACITY'];

						// if($stack_capacity < $loc['YPG_CAPACITY']){
							//$slot = $loc['YPG_START_SLOT'].' - '.$loc['YPG_END_SLOT'];
							$loc_yard .= $loc['YPG_YARD_ID'];
							$loc_block .= $loc['YPG_BLOCK_ID'];
							$loc_slot .= $loc['YPG_START_SLOT'];
							break;
						// }
					}

	    return array('DATA' => $data, 'YARD' => array('YARD' => $loc_yard, 'BLOCK' => $loc_block, 'SLOT' => $loc_slot));
	}

	function setPlacement(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$success = true;
		$message = "Success";

		$block = $this->input->post('BLOCK_ID');
		$row = $this->input->post('ROW');
		$slot = $this->input->post('SLOT');
		$cont = $this->input->post('NO_CONT');
		$yard = $this->input->post('YARD_ID');
		$tier = $this->input->post('TIER');
		$gate_id = $this->input->post('GATE_ID');
		$req_id = $this->input->post('NO_REQ');
		$cont_status = $this->input->post('CONT_STATUS');
		$activity = $this->input->post('ACTIVITY');
		$size = $this->input->post('CONT_SIZE');
		$type = $this->input->post('CONT_TYPE');
		$ybc = $this->input->post('YBC_ID');
		$reason = $this->input->post('REASON');
		$req_date = '';

		$this->db->trans_start();

		$ybc_id = $this->db->select('YBC_ID')
						 ->from('TX_YARD_BLOCK_CELL')
						 ->where('YBC_YARD_ID',$yard)
						 ->where('YBC_BLOCK_ID',$block)
						 ->where('YBC_ROW',$row)
						 ->where('YBC_SLOT',$slot)
						 ->where('YBC_BRANCH_ID',$branch_id)
						 ->get()->row()->YBC_ID;

		$arrData = array(
								'REAL_YARD_BRANCH_ID' => $branch_id,
								'REAL_YARD_CREATE_BY' => $user_id,
								'REAL_YARD_YBC_ID' => $ybc_id,
								'REAL_YARD_TIER' => $tier,
								'REAL_YARD_CONT' => $cont,
								'REAL_YARD_NO' => $yard,
								'REAL_YARD_TYPE' => 1,
								'REAL_YARD_STATUS' => 1,
								'REAL_YARD_REQ_NO' => $req_id,
								'REAL_YARD_CONT_STATUS' => $cont_status,
								'REAL_YARD_CONT_SIZE' => $size,
								'REAL_YARD_CONT_TYPE' => $type,
								'REAL_YARD_ACTIVITY' => $activity
		);

		$cont_check = $this->db->where('REAL_YARD_CONT',$cont)->where('REAL_YARD_STATUS',1)->from('TX_REAL_YARD')->count_all_results();
		if($cont_check > 0){
			// $this->db->set('REAL_YARD_STATUS',2)->where('REAL_YARD_CONT',$cont)->update('TX_REAL_YARD');
		}

		if($activity != 3){
				$cont_old = $this->db->query("SELECT * FROM (
									SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS, REAL_YARD_ACTIVITY,
									REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY
									FROM TX_REAL_YARD A
									WHERE A. REAL_YARD_BRANCH_ID = ".$branch_id."
									AND A.REAL_YARD_YBC_ID = ".$ybc."
									AND A.REAL_YARD_ID IN (
										SELECT X.REAL_YARD_ID  FROM (
										 SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_YBC_ID = ".$ybc." AND H.REAL_YARD_BRANCH_ID = ".$branch_id." GROUP BY H.REAL_YARD_CONT
									 )X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
										)
									)Z WHERE Z.REAL_YARD_CONT = '".$cont."'")->row_array();
				$test = "SELECT * FROM (
									SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS,
									REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY
									FROM TX_REAL_YARD A
									WHERE A. REAL_YARD_BRANCH_ID = ".$branch_id."
									AND A.REAL_YARD_YBC_ID = ".$ybc."
									AND A.REAL_YARD_ID IN (
										SELECT X.REAL_YARD_ID  FROM (
										 SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_YBC_ID = ".$ybc." AND H.REAL_YARD_BRANCH_ID = ".$branch_id." GROUP BY H.REAL_YARD_CONT
									 )X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
										)
									)Z WHERE Z.REAL_YARD_CONT = '".$cont."'";
				// print_r($cont_old);die();
				// insert new record for old container to unstacking
				$this->db->insert('TX_REAL_YARD',$cont_old);
		}

		if($_POST['DATE_PLACEMENT'] != 0){
			$date_placement = $_POST['DATE_PLACEMENT'].' '.$_POST['TIME_PLACEMENT'];
			$this->db->set('REAL_YARD_BRANCH_ID', $branch_id);
			$this->db->set('REAL_YARD_CREATE_BY', $user_id);
			$this->db->set('REAL_YARD_YBC_ID', $ybc_id);
			$this->db->set('REAL_YARD_TIER', $tier);
			$this->db->set('REAL_YARD_CONT', $cont);
			$this->db->set('REAL_YARD_NO', $yard);
			$this->db->set('REAL_YARD_TYPE', 1);
			$this->db->set('REAL_YARD_STATUS',1);
			$this->db->set('REAL_YARD_REQ_NO', $req_id);
			$this->db->set('REAL_YARD_CONT_STATUS', $cont_status);
			$this->db->set('REAL_YARD_CONT_SIZE', $size);
			$this->db->set('REAL_YARD_CONT_TYPE', $type);
			$this->db->set('REAL_YARD_ACTIVITY', $activity);
			$this->db->set('REAL_YARD_CREATE_DATE',"to_date('$date_placement','DD-MM-YYYY HH24:MI')",false);
			$this->db->set('REAL_YARD_BACKDATE', $reason);
			$this->db->insert('TX_REAL_YARD');
		}
		else{
			$this->db->insert('TX_REAL_YARD',$arrData);
		}

		if($activity == 3){
			$this->db->set('GATE_STACK_STATUS',1)->where('GATE_ID',$gate_id)->update('TX_GATE');

			$this->db->query("UPDATE TX_REQ_RECEIVING_DTL SET REQUEST_DTL_STATUS = 1 WHERE REQUEST_DTL_CONT = '".$cont."' AND REQUEST_HDR_ID = (SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO = '".$req_id."' AND REQUEST_BRANCH_ID = ".$branch_id.")");

			//check detail request receiving telah gateout
			 $REQ_HDR_ID = $this->db->select('REQUEST_ID')
			 							->from('TX_REQ_RECEIVING_HDR')
			 							 ->where('REQUEST_NO',$req_id)
										 ->where('REQUEST_BRANCH_ID',$branch_id)
			 						 	 ->get()->row_array()['REQUEST_ID'];

			$cek_jumlah_dtl = $this->db->where('REQUEST_HDR_ID',$REQ_HDR_ID)->from('TX_REQ_RECEIVING_DTL')->count_all_results();
			$cek_jumlah_out = $this->db->where('REQUEST_HDR_ID',$REQ_HDR_ID)->where('REQUEST_DTL_STATUS','1')->from('TX_REQ_RECEIVING_DTL')->count_all_results();
			if($cek_jumlah_out == $cek_jumlah_dtl){
				$this->db->set('REQUEST_STATUS',2)->where('REQUEST_NO',$req_id)->update('TX_REQ_RECEIVING_HDR');
			}
		}

		// ADD HISTORY PLACEMENT
		$hist_data = $this->db->where('HP_YARD_ID',$yard)->where('HP_BLOCK_ID',$block)->where('HP_SLOT_ID',$slot)->where('HP_BRANCH_ID',$branch_id)->from('TX_HIST_PLACEMENT');
		if($hist_data->count_all_results() > 0){
			$this->db->query('UPDATE TX_HIST_PLACEMENT SET HP_ROW_ID='.$row.' WHERE HP_YARD_ID='.$yard.' AND HP_BLOCK_ID='.$block.' AND HP_SLOT_ID='.$slot.' AND HP_BRANCH_ID='.$branch_id);
		}
		else{
			$arrData = array('HP_YARD_ID' => $yard, 'HP_BLOCK_ID' => $block, 'HP_SLOT_ID' => $slot, 'HP_ROW_ID' => $row, 'HP_BRANCH_ID' => $branch_id);
			$this->db->insert('TX_HIST_PLACEMENT',$arrData);
		}
		// END ADD HISTORY PLACEMENT

		//ADD_HISTORY_CONTAINER(CONT_NO, CONT_SIZE, CONT_YARD, CONT_BLOCK, CONT_SLOT, CONT_ROW, CONT_TIER, CONT_ACTIVITY_ID, CONT_ACTIVITY_NAME, BRANCH)
		$getyard = $this->db->select('YARD_NAME')->where('YARD_ID',$yard)->get('TM_YARD')->row_array()['YARD_NAME'];
		$getblock = $this->db->select('BLOCK_NAME')->where('BLOCK_ID',$block)->get('TM_BLOCK')->row_array()['BLOCK_NAME'];
		$getactivity = $this->db->select('REFF_NAME')->where('REFF_ID',$activity)->where('REFF_TR_ID',22)->get('TM_REFF')->row_array()['REFF_NAME'];

		// gate request date
		$req_date = $this->db->query("SELECT TO_CHAR(REQ_DATE,'MM/DD/YYYY HH24:MI:SS') REQ_DATE FROM (SELECT STUFF_NO REQ_NO, STUFF_CREATE_DATE REQ_DATE, STUFF_BRANCH_ID BRANCH FROM TX_REQ_STUFF_HDR
									UNION
									SELECT STRIP_NO REQ_NO, STRIP_CREATE_DATE REQ_DATE, STRIP_BRANCH_ID BRANCH FROM TX_REQ_STRIP_HDR
									UNION
									SELECT REQUEST_NO REQ_NO, REQUEST_CREATE_DATE REQ_DATE, REQUEST_BRANCH_ID BRANCH FROM TX_REQ_RECEIVING_HDR
									UNION
									SELECT REQ_NO REQ_NO, REQ_CREATE_DATE REQ_DATE, REQ_BRANCH_ID BRANCH FROM TX_REQ_DELIVERY_HDR)
									WHERE BRANCH = ".$branch_id." AND REQ_NO = '".$req_id."'")->row_array()['REQ_DATE'];

		//insert history container
		$this->db->query("CALL ADD_HISTORY_CONTAINER(
					'".$this->input->post('NO_CONT')."',
					'".$req_id."',
					'".$req_date."',
					'".$size."',
					'".$type."',
					'".$cont_status."',
					'".$getyard."',
					'".$getblock."',
					".$slot.",
					".$row.",
					".$tier.",
					".$activity.",
					'".$getactivity."',
					NULL,
					".$branch_id.")");

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$success = false;
				$message = 'Failed';
		}

		return array('success' => $success, 'message' => $message);
	}

	function getYbcId(){
		$branch_id = $this->session->USER_BRANCH;
		$block_id = $this->input->post('BLOCK_ID');
		$cont = $this->input->post('CONT');
		$req = $this->input->post('REQ');
		$ybc_id = $this->db->select('YBC_ID')
											 ->from('TX_YARD_BLOCK_CELL')
											 ->where('YBC_BRANCH_ID',$branch_id)
											 ->where('YBC_YARD_ID',$this->input->post('YARD_ID'))
											 ->where('YBC_BLOCK_ID',$this->input->post('BLOCK_ID'))
											 ->where('YBC_ROW',$this->input->post('ROW_ID'))
											 ->where('YBC_SLOT',$this->input->post('SLOT_ID'))
											 ->get()->row_array()['YBC_ID'];

		 $real_tier = $this->db->query("SELECT MAX(Z.REAL_YARD_TIER) LAST_TIER FROM (SELECT I.REAL_YARD_TIER  FROM (
 											 SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_YBC_ID = ".$ybc_id." AND H.REAL_YARD_BRANCH_ID = ".$branch_id."  GROUP BY H.REAL_YARD_CONT
 										 )X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
 								)Z")->row_array()['LAST_TIER'];

 		// get max tier form block
 			$maxTier = $this->db->query("SELECT BLOCK_NAME, BLOCK_TIER FROM TM_BLOCK WHERE BLOCK_ID = $block_id AND BLOCK_ACTIVE = 'Y' AND BLOCK_BRANCH_ID = $branch_id")->row_array()['BLOCK_TIER'];

 	  // get tier disabled
 			$tierDisabled = $this->db->query("SELECT A.* FROM TX_CELL_DISABLE A LEFT JOIN TX_YARD_BLOCK_CELL B ON B.YBC_ID = A.CELL_YCB_ID WHERE B.YBC_ID = $ybc_id AND A.CELL_BRANCH_ID = $branch_id")->result_array();
 			$arrTierDisabled = array();
 			foreach ($tierDisabled as $val) {
 				$arrTierDisabled[] = (int)$val['CELL_TIER'];
 			}

 		 // get cell current active
 			$tierActive = array();
 			for ($i=1; $i <= (int)$maxTier; $i++) {
 					if (!in_array($i, $arrTierDisabled)){
 						$tierActive[] = $i;
 					}
 			}


 	  if($real_tier == 0){
 			$real_tier = 1;
 		}
 		else{
 			$real_tier = $real_tier + 1;
 		}

		//check jika container sudah placemnet
		$placement = 0;
		$cont_check = $this->db->where('REAL_YARD_CONT',$cont)->where('REAL_YARD_REQ_NO',$req)->where('REAL_YARD_BRANCH_ID',$branch_id)->from('TX_REAL_YARD')->count_all_results();
		if($cont_check > 0){
			$placement = 1;
		}

		// return $ybc_id;
		return array('YBC_ID' => $ybc_id, 'BLOCK_TIER' => $real_tier, 'MAX_TIER' => max($tierActive), 'PLACEMENT' => $placement);
	}

	public function getLastPlacement(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;

		$yard = $this->input->post('yard');
		$block = $this->input->post('block');
		$slot = $this->input->post('slot');

		$hist_data = $this->db->where('HP_YARD_ID',$yard)->where('HP_BLOCK_ID',$block)->where('HP_SLOT_ID',$slot)->where('HP_BRANCH_ID',$branch_id)->from('TX_HIST_PLACEMENT')->count_all_results();
		if($hist_data > 0){
			$row = (int)$this->db->where('HP_YARD_ID',$yard)->where('HP_BLOCK_ID',$block)->where('HP_SLOT_ID',$slot)->where('HP_BRANCH_ID',$branch_id)->get('TX_HIST_PLACEMENT')->row_array()['HP_ROW_ID'];
		}
		else{
			$row = 1;
		}

		return $row;
		// $arrData = array('HP_YARD_ID' => $yard, 'HP_BLOCK_ID' => $block, 'HP_SLOT_ID' => $slot, 'HP_BRANCH_ID' => $branch_id);
		// $this->db->insert('TX_HIST_PLACEMENT',$arrData);
	}

	public function generateCopyyard(){
		$branch_id = $this->input->post('BRANCH_ID');
		$yard_id = $this->input->post('YARD_ID');
		$success = true;
		$message = 'Success';

		$this->db->trans_start();

		$this->db->query("CALL PROC_INSERT_COPY_YARD(".$branch_id.", ".$yard_id.")");

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$success = false;
				$message = 'Failed';
		}

		return array('success' => $success, 'message' => $message);
	}

}
