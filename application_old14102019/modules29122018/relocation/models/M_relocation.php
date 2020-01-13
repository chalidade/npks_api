<?php
class M_relocation extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	function getContainer(){
		 $branch_id = $this->session->USER_BRANCH;
		  // $data = $this->db->select('A.REAL_YARD_ID, A.REAL_YARD_CONT')
		 	// 				->from('TX_REAL_YARD A')
			// 				->where('A.REAL_YARD_BRANCH_ID', $branch_id)
			// 				->where('A.REAL_YARD_STATUS',1)
			// 				->order_by('A.REAL_YARD_ID','ASC')
			// 				->get()->result_array();
			// $data = $this->db->query("SELECT REAL_YARD_ID, REAL_YARD_CONT  FROM TX_REAL_YARD WHERE REAL_YARD_CONT IN
			// 				(SELECT REAL_YARD_CONT FROM (SELECT COUNT(*), REAL_YARD_CONT FROM TX_REAL_YARD WHERE REAL_YARD_BRANCH_ID = ".$branch_id." GROUP BY REAL_YARD_CONT HAVING COUNT(*) = 1))")->result_array();

			// $data = $this->db->query("SELECT MAX(REAL_YARD_ID) REAL_YARD_ID, REAL_YARD_CONT  FROM(
			// 					SELECT REAL_YARD_ID, REAL_YARD_CONT
			// 					FROM TX_REAL_YARD
			// 					WHERE REAL_YARD_CONT IN(SELECT REAL_YARD_CONT FROM (SELECT COUNT(*), REAL_YARD_CONT FROM TX_REAL_YARD WHERE REAL_YARD_BRANCH_ID = ".$branch_id." GROUP BY REAL_YARD_CONT, REAL_YARD_YBC_ID HAVING COUNT(*) = 1))
			// 				)Z GROUP BY Z.REAL_YARD_CONT")->result_array();

			// $data = $this->db->query("SELECT MAX(REAL_YARD_ID) REAL_YARD_ID, REAL_YARD_CONT  FROM(
			// 					SELECT REAL_YARD_ID, REAL_YARD_CONT
			// 					FROM TX_REAL_YARD
			// 					WHERE REAL_YARD_ID IN(
			// 						SELECT X.REAL_YARD_ID  FROM (
			// 						 	SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_BRANCH_ID = ".$branch_id." GROUP BY H.REAL_YARD_CONT
			// 					 	)X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
			// 					)
			// 				)Z GROUP BY Z.REAL_YARD_CONT")->result_array();

			$data = $this->db->select('A.CONTAINER_NO REAL_YARD_CONT')
							->from('TM_CONTAINER A')
							->where('A.CONTAINER_STATUS','IN_YARD')
							->where('A.CONTAINER_BRANCH_ID',$branch_id)
							->get()->result_array();

	    return $data;
	}

	function getContainerById(){
		 $id = $this->input->post('id');
		 $branch_id = $this->session->USER_BRANCH;
		 $params = array('CONT' => $id, 'BRANCH' => $branch_id);
	   // $data = $this->db->select('A.REAL_YARD_REQ_NO, A.REAL_YARD_CONT, A.REAL_YARD_CONT_SIZE, A.REAL_YARD_CONT_TYPE, A.REAL_YARD_CONT_STATUS, B.YBC_YARD_ID, B.YBC_BLOCK_ID, B.YBC_SLOT, B.YBC_ROW, A.REAL_YARD_TIER')
		 // 					->from('TX_REAL_YARD A')
			// 				->join('TX_YARD_BLOCK_CELL B','A.REAL_YARD_YBC_ID = B.YBC_ID')
			// 				->join('TM_YARD C','B.YBC_YARD_ID = C.YARD_ID')
			// 				->join('TM_BLOCK D','B.YBC_BLOCK_ID = D.BLOCK_ID')
			// 				->where('A.REAL_YARD_BRANCH_ID', $branch_id)
			// 				->where('B.YBC_ACTIVE','Y')
			// 				->where('D.BLOCK_ACTIVE','Y')
			// 				->where('A.REAL_YARD_ID',$id)
			// 				->get()->row_array();

		  $data = $this->db->query("SELECT * FROM (SELECT A.REAL_YARD_ID, A.REAL_YARD_REQ_NO, A.REAL_YARD_CONT, A.REAL_YARD_CONT_SIZE, A.REAL_YARD_CONT_TYPE, A.REAL_YARD_CONT_STATUS, B.YBC_YARD_ID, B.YBC_BLOCK_ID, B.YBC_SLOT, B.YBC_ROW, A.REAL_YARD_TIER,
							MAX(A.REAL_YARD_ID) over () as MAX_ID
							FROM TX_REAL_YARD A
							JOIN TX_YARD_BLOCK_CELL B ON A.REAL_YARD_YBC_ID = B.YBC_ID
							JOIN TM_YARD C ON B.YBC_YARD_ID = C.YARD_ID
							JOIN TM_BLOCK D ON B.YBC_BLOCK_ID = D.BLOCK_ID
							WHERE B.YBC_ACTIVE = 'Y' AND D.BLOCK_ACTIVE	= 'Y' AND A.REAL_YARD_STATUS = 1 AND A.REAL_YARD_USED = 1 AND A.REAL_YARD_CONT = ? AND A.REAL_YARD_BRANCH_ID = ?) T
							WHERE T.REAL_YARD_ID = MAX_ID",$params)->row_array();

	    return $data;
	}

	function setRelocation(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$success = true;
		$message = 'Success';

		$block = $this->input->post('BLOCK_ID');
		$row = $this->input->post('ROW');
		$slot = $this->input->post('SLOT');
		$cont = $this->input->post('CONT');
		$yard = $this->input->post('YARD_ID');
		$tier = $this->input->post('TIER');
		$tier_old = $this->input->post('TIER_OLD');
		$yard_old = $this->input->post('YARD_OLD');
		$block_old = $this->input->post('BLOCK_OLD');
		$row_old = $this->input->post('ROW_OLD');
		$slot_old = $this->input->post('SLOT_OLD');
		$req_id = $this->input->post('NO_REQ');
		$real_yard_id = $this->input->post('REAL_YARD_ID');
		$cont_size = (int)$this->input->post('CONT_SIZE');
		$cont_type = $this->input->post('CONT_TYPE');
		$cont_status = $this->input->post('CONT_STATUS');
		$reason = $this->input->post('REASON');
		$activity = 7;
		$mark = NULL;

		$this->db->trans_start();

		$ybc_id = $this->db->select('YBC_ID')
						 ->from('TX_YARD_BLOCK_CELL')
						 ->where('YBC_YARD_ID',$yard)
						 ->where('YBC_BLOCK_ID',$block)
						 ->where('YBC_ROW',$row)
						 ->where('YBC_SLOT',$slot)
						 ->where('YBC_BRANCH_ID',$branch_id)
						 ->get()->row()->YBC_ID;

		if($cont_size == 40){
			$ybc_id40 = $this->db->select('YBC_ID')
				 ->from('TX_YARD_BLOCK_CELL')
				 ->where('YBC_YARD_ID',$yard)
				 ->where('YBC_BLOCK_ID',$block)
				 ->where('YBC_ROW',$row)
				 ->where('YBC_SLOT',$slot+1)
				 ->where('YBC_BRANCH_ID',$branch_id)
				 ->get()->row_array()['YBC_ID'];
		}

		// get data container yg stacking sekarang.
	  // $cont_old = $this->db->query("SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS,
		// 										 REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY
		// 										 FROM TX_REAL_YARD
		// 										 WHERE REAL_YARD_BRANCH_ID = ".$branch_id." AND REAL_YARD_ID = ".$real_yard_id);

		$cont_old = $this->db->query("SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS, '0' REAL_YARD_USED,
												 REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY
												 FROM TX_REAL_YARD
												 WHERE REAL_YARD_BRANCH_ID = ".$branch_id." AND REAL_YARD_CONT = '".$cont."' AND REAL_YARD_STATUS = 1 AND REAL_YARD_USED = 1");

		// if($cont_old->num_rows() > 0){
		// 	 $mark = $cont_old->row_array()['REAL_YARD_MARK'];
		// 	 if($_POST['DATE_RELOCATION'] == 0){
		// 	 $this->db->insert('TX_REAL_YARD',$cont_old->row_array());
		// 	 $cont_old = $this->db->query("SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS,
	 	// 											 REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY
	 	// 											 FROM TX_REAL_YARD
	 	// 											 WHERE REAL_YARD_BRANCH_ID = ".$branch_id." AND REAL_YARD_ID = ".$real_yard_id);
		//  	}
		// 	else{
		// 		$date_unstack = $_POST['DATE_RELOCATION'].' '.$_POST['TIME_RELOCATION'];
		// 		$cont_old = $this->db->query("INSERT INTO TX_REAL_YARD (REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, REAL_YARD_CREATE_BY, REAL_YARD_TYPE, REAL_YARD_STATUS,
		// 		REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY, REAL_YARD_CREATE_DATE)
		// 		SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS,
		// 												 REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY,
		// 												 TO_DATE('".$date_unstack."','DD/MM/YYYY HH24:MI') AS REAL_YARD_CREATE_DATE
		// 												 FROM TX_REAL_YARD
		// 												 WHERE REAL_YARD_BRANCH_ID = ".$branch_id." AND REAL_YARD_ID = ".$real_yard_id);
		// 	}
		if($cont_old->num_rows() > 0){
			 $mark = $cont_old->row_array()['REAL_YARD_MARK'];
			 if($_POST['DATE_RELOCATION'] == 0){
				 foreach ($cont_old->result_array() as $val) {
				 	$this->db->insert('TX_REAL_YARD',$val);
				 }
			}
			else{
				$date_unstack = $_POST['DATE_RELOCATION'].' '.$_POST['TIME_RELOCATION'];
				$cont_old = $this->db->query("INSERT INTO TX_REAL_YARD (REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, REAL_YARD_CREATE_BY, REAL_YARD_TYPE, REAL_YARD_STATUS, REAL_YARD_USED,
				REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY, REAL_YARD_CREATE_DATE, REAL_YARD_BACKDATE)
				SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS, '0' AS REAL_YARD_USED,
				 REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY,
				 TO_DATE('".$date_unstack."','DD/MM/YYYY HH24:MI') AS REAL_YARD_CREATE_DATE, 'Y'
				 FROM TX_REAL_YARD
				 WHERE REAL_YARD_BRANCH_ID = ".$branch_id." AND REAL_YARD_CONT = '".$cont."' AND REAL_YARD_STATUS = 1 AND REAL_YARD_USED = 1");
			}
		}

		//UPDATE DATA LAMA MENJADI TIDAK AKTIF
		$this->db->query("UPDATE TX_REAL_YARD SET REAL_YARD_USED = '0' WHERE REAL_YARD_BRANCH_ID = ".$branch_id." AND REAL_YARD_CONT = '".$cont."' AND REAL_YARD_STATUS = 1 AND REAL_YARD_USED = 1");

		$arrData = array(
								'REAL_YARD_BRANCH_ID' => $branch_id,
								'REAL_YARD_CREATE_BY' => $user_id,
								'REAL_YARD_YBC_ID' => $ybc_id,
								'REAL_YARD_TIER' => $tier,
								'REAL_YARD_CONT' => $cont,
								'REAL_YARD_NO' => $yard,
								'REAL_YARD_TYPE' => 2,
								'REAL_YARD_STATUS' => 1,
								'REAL_YARD_REQ_NO'=> $req_id,
								'REAL_YARD_CONT_SIZE' => $cont_size,
								'REAL_YARD_CONT_TYPE' => $cont_type,
								'REAL_YARD_CONT_STATUS' => $cont_status,
								'REAL_YARD_MARK' => $mark,
								'REAL_YARD_ACTIVITY' => $activity
		);

		if($cont_size == 40){
			$arrData40 = array(
									'REAL_YARD_BRANCH_ID' => $branch_id,
									'REAL_YARD_CREATE_BY' => $user_id,
									'REAL_YARD_YBC_ID' => $ybc_id40,
									'REAL_YARD_TIER' => $tier,
									'REAL_YARD_CONT' => $cont,
									'REAL_YARD_NO' => $yard,
									'REAL_YARD_TYPE' => 2,
									'REAL_YARD_STATUS' => 1,
									'REAL_YARD_REQ_NO'=> $req_id,
									'REAL_YARD_CONT_SIZE' => $cont_size,
									'REAL_YARD_CONT_TYPE' => $cont_type,
									'REAL_YARD_CONT_STATUS' => $cont_status,
									'REAL_YARD_MARK' => $mark,
									'REAL_YARD_ACTIVITY' => $activity
			);

			if($ybc_id40 == ''){
				$message = 'Slot tidak tersedia';
				$success = false;
				return array('success' => $success, 'message' => $message);
				die();
			}
			else{
					$tier40 = $this->db->query("SELECT * FROM
					 (SELECT A.REAL_YARD_ID, A.REAL_YARD_CONT, A.REAL_YARD_TIER, MAX(A.REAL_YARD_ID) over () as MAX_ID FROM TX_REAL_YARD A WHERE A.REAL_YARD_YBC_ID = $ybc_id40 AND A.REAL_YARD_USED = 1 AND A.REAL_YARD_STATUS = 1 AND A.REAL_YARD_BRANCH_ID = $branch_id)
					 T WHERE T.REAL_YARD_ID = MAX_ID")->row_array()['REAL_YARD_TIER'];

				 if($tier ==  1 && $tier40 != ''){
					 $message = 'Slot tidak tersedia';
					 $success = false;
					 return array('success' => $success, 'message' => $message);
					 die();
				 }
				 elseif($tier > 1 && (int)$tier40 != $tier - 1){
					 $message = 'Slot tidak tersedia';
					 $success = false;
					 return array('success' => $success, 'message' => $message);
					 die();
				 }
			}
			
		}

		if($_POST['DATE_RELOCATION'] != 0){
			$date_placement = $_POST['DATE_RELOCATION'].' '.$_POST['TIME_RELOCATION'];
			$this->db->set('REAL_YARD_BRANCH_ID', $branch_id);
			$this->db->set('REAL_YARD_CREATE_BY', $user_id);
			$this->db->set('REAL_YARD_YBC_ID', $ybc_id);
			$this->db->set('REAL_YARD_TIER', $tier);
			$this->db->set('REAL_YARD_CONT', $cont);
			$this->db->set('REAL_YARD_NO', $yard);
			$this->db->set('REAL_YARD_TYPE', 2);
			$this->db->set('REAL_YARD_STATUS',1);
			$this->db->set('REAL_YARD_REQ_NO', $req_id);
			$this->db->set('REAL_YARD_CONT_STATUS', $cont_status);
			$this->db->set('REAL_YARD_CONT_SIZE', $cont_size);
			$this->db->set('REAL_YARD_CONT_TYPE', $cont_type);
			$this->db->set('REAL_YARD_MARK', $mark);
			$this->db->set('REAL_YARD_CREATE_DATE',"to_date('$date_placement','DD-MM-YYYY HH24:MI')",false);
			$this->db->set('REAL_YARD_BACKDATE', $reason);
			$this->db->set('REAL_YARD_ACTIVITY', $activity);
			$this->db->insert('TX_REAL_YARD');

			if($cont_size == 40){
				$this->db->set('REAL_YARD_BRANCH_ID', $branch_id);
				$this->db->set('REAL_YARD_CREATE_BY', $user_id);
				$this->db->set('REAL_YARD_YBC_ID', $ybc_id40);
				$this->db->set('REAL_YARD_TIER', $tier);
				$this->db->set('REAL_YARD_CONT', $cont);
				$this->db->set('REAL_YARD_NO', $yard);
				$this->db->set('REAL_YARD_TYPE', 2);
				$this->db->set('REAL_YARD_STATUS',1);
				$this->db->set('REAL_YARD_REQ_NO', $req_id);
				$this->db->set('REAL_YARD_CONT_STATUS', $cont_status);
				$this->db->set('REAL_YARD_CONT_SIZE', $cont_size);
				$this->db->set('REAL_YARD_CONT_TYPE', $cont_type);
				$this->db->set('REAL_YARD_MARK', $mark);
				$this->db->set('REAL_YARD_CREATE_DATE',"to_date('$date_placement','DD-MM-YYYY HH24:MI')",false);
				$this->db->set('REAL_YARD_BACKDATE', $reason);
				$this->db->set('REAL_YARD_ACTIVITY', $activity);
				$this->db->insert('TX_REAL_YARD');
			}
		}
		else{
			$this->db->insert('TX_REAL_YARD',$arrData);
			if($cont_size == 40){
				$this->db->insert('TX_REAL_YARD',$arrData40);
			}
		}

		// $this->db->trans_complete();
		// die();

		//setting for history container
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
		$ext = '';
		if($cont_size == 40){
			$ext = $slot;
			$slot = $slot + 1;
		}
		$this->db->query("CALL ADD_HISTORY_CONTAINER(
					'".$cont."',
					'".$req_id."',
					'".$req_date."',
					'".$cont_size."',
					'".$cont_type."',
					'".$cont_status."',
					'".$getyard."',
					'".$getblock."',
					".$slot.",
					".$row.",
					".$tier.",
					".$activity.",
					'".$getactivity."',
					NULL,
					'".$ext."',
					".$branch_id.")");

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$success = false;
				$message = 'Failed';
		}

		return array('success' => $success, 'message' => $message);
	}

}
