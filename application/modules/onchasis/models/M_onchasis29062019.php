<?php
class M_onchasis extends CI_Model {
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
		 $id = strtoupper($this->input->post('id'));
		 $branch_id = $this->session->USER_BRANCH;
		 $message = 'Success';
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

			// $data = $this->db->query("SELECT * FROM (SELECT A.REAL_YARD_ID, A.REAL_YARD_REQ_NO, A.REAL_YARD_CONT, A.REAL_YARD_CONT_SIZE, A.REAL_YARD_CONT_TYPE, A.REAL_YARD_CONT_STATUS, B.YBC_YARD_ID, B.YBC_BLOCK_ID, B.YBC_SLOT, B.YBC_ROW, A.REAL_YARD_TIER,
			// 				MAX(A.REAL_YARD_ID) over () as MAX_ID
			// 				FROM TX_REAL_YARD A
			// 				JOIN TX_YARD_BLOCK_CELL B ON A.REAL_YARD_YBC_ID = B.YBC_ID
			// 				JOIN TM_YARD C ON B.YBC_YARD_ID = C.YARD_ID
			// 				JOIN TM_BLOCK D ON B.YBC_BLOCK_ID = D.BLOCK_ID
			// 				WHERE B.YBC_ACTIVE = 'Y' AND D.BLOCK_ACTIVE	= 'Y' AND A.REAL_YARD_STATUS = 1 AND A.REAL_YARD_USED = 1 AND A.REAL_YARD_CONT = ? AND A.REAL_YARD_BRANCH_ID = ?) T
							// WHERE T.REAL_YARD_ID = MAX_ID",$params)->row_array();

			// $data = $this->db->query("SELECT B.REQ_NO REAL_YARD_REQ_NO, B.REQUEST_TO, A.REQ_DTL_CONT REAL_YARD_CONT, A.REQ_DTL_CONT_STATUS REAL_YARD_CONT_STATUS, A.REQ_DTL_CONT_SIZE REAL_YARD_CONT_SIZE, A.REQ_DTL_CONT_TYPE REAL_YARD_CONT_TYPE, A.REQ_DTL_CONT_STATUS CONT_STATUS, 4 ACTIVITY, B.REQ_BRANCH_ID BRANCH, A.REQ_DTL_ACTIVE, E.OWNER_NAME OWNER
			// 				FROM TX_REQ_DELIVERY_DTL A
			// 				JOIN TX_REQ_DELIVERY_HDR B ON A.REQ_HDR_ID = B.REQ_ID
			// 				left join TM_CONTAINER D ON D.CONTAINER_NO = A.REQ_DTL_CONT
			// 				left join TM_OWNER E ON E.OWNER_CODE = D.CONTAINER_OWNER
			// 				WHERE A.REQ_DTL_ACTIVE = 'Y' AND A.REQ_DTL_CONT = ? AND B.REQ_BRANCH_ID = ? ",$params)->row_array();

			$data = $this->db->query("SELECT * FROM (SELECT MAX(A.REQ_DTL_ID) over () as MAX_ID, A.REQ_DTL_ID, B.REQ_NO REAL_YARD_REQ_NO, B.REQUEST_TO, A.REQ_DTL_CONT REAL_YARD_CONT, A.REQ_DTL_CONT_STATUS REAL_YARD_CONT_STATUS, A.REQ_DTL_CONT_SIZE REAL_YARD_CONT_SIZE, A.REQ_DTL_CONT_TYPE REAL_YARD_CONT_TYPE, A.REQ_DTL_CONT_STATUS CONT_STATUS, 4 ACTIVITY,
							B.REQ_BRANCH_ID BRANCH, A.REQ_DTL_ACTIVE, E.OWNER_NAME OWNER
							FROM TX_REQ_DELIVERY_DTL A
							JOIN TX_REQ_DELIVERY_HDR B ON A.REQ_HDR_ID = B.REQ_ID
							left join TM_CONTAINER D ON D.CONTAINER_NO = A.REQ_DTL_CONT
							left join TM_OWNER E ON E.OWNER_CODE = D.CONTAINER_OWNER
							WHERE A.REQ_DTL_CONT = ? AND B.REQ_BRANCH_ID = ?) T
							WHERE T.REQ_DTL_ID = MAX_ID",$params)->row_array();


			if(count($data) > 0){
				if($data['REQ_DTL_ACTIVE'] == 'Y'){
					$data = $data;
				}
				else{
					$message = 'Container sudah unstack dari lapangan / Belum ada request delivery baru';
					return array('success' => false, 'message' => $message);
					die();
				}
			}
			else{
				$message = 'Belum ada request delivery';
				return array('success' => false, 'message' => $message);
				die();
			}

	    return array('success' => true, 'message' => $message, 'data' => $data);
	}

	function setOnChasis(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$success = true;

		$block = $this->input->post('BLOCK_ID');
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
		$cont = $this->input->post('NO_CONT');
		$no_req = $this->input->post('NO_REQ');
		$req_to = $this->input->post('REQ_TO');
		$activity = 8;
		$mark = NULL;

		$this->db->trans_start();

		if($req_to == 'TPK'){
			//insert data gateIn gateOut
			$chekDataGateIn = "SELECT COUNT(1) TOTAL FROM TX_GATE WHERE GATE_NOREQ = '".$no_req."' AND GATE_CONT = '".$cont."'";
			$resultCheckGate =  $this->db->query($chekDataGateIn)->row_array()['TOTAL'];
			if($resultCheckGate > 0){
				$this->db->where('GATE_NOREQ',$no_req)->where('GATE_CONT',$cont)->delete('TX_GATE');
			}
			$gate_date_tpk = date('m/d/Y H:i:s');
			$gate_date_tpkh = date('d/m/Y H:i');
			$endTime = date('d/m/Y H:i',strtotime("+1 minutes", strtotime($gate_date_tpk)));
			$insertGATEIN = "INSERT INTO TX_GATE (GATE_NOREQ, GATE_CONT, GATE_CONT_STATUS, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_TRUCK_NO, GATE_ORIGIN, GATE_CREATE_DATE, GATE_STATUS, GATE_ACTIVITY, GATE_STACK_STATUS, GATE_FL_SEND, GATE_BRANCH_ID, GATE_ACTIVE) VALUES
									 ('".$no_req."','".$cont."','".$cont_status."','".$cont_size."','".$cont_type."','PELINDO','".$req_to."', TO_DATE('".$gate_date_tpk."','MM/DD/YYYY HH24:MI:SS'),'1', '4','1', '9', ".$branch_id.", 'T')";
			$resultHDR = $this->db->query($insertGATEIN);

			$insertGATEIN = "INSERT INTO TX_GATE (GATE_NOREQ, GATE_CONT,GATE_CONT_STATUS, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_TRUCK_NO, GATE_ORIGIN, GATE_CREATE_DATE, GATE_STATUS, GATE_ACTIVITY, GATE_STACK_STATUS, GATE_FL_SEND, GATE_BRANCH_ID, GATE_ACTIVE) VALUES
									 ('".$no_req."','".$cont."','".$cont_status."','".$cont_size."','".$cont_type."','PELINDO','".$req_to."', TO_DATE('".$gate_date_tpk."','MM/DD/YYYY HH24:MI:SS'),'3', '4','2', '9', ".$branch_id.", 'T')";
			$resultHDR = $this->db->query($insertGATEIN);

			//get ID hdr and date Delivery
			$sqlIDHDR = "SELECT REQ_ID, TO_CHAR(REQ_DELIVERY_DATE,'MM/DD/YYYY HH24:MI:SS') REQ_DELIVERY_DATE FROM TX_REQ_DELIVERY_HDR  WHERE REQ_NO = '".$no_req."' AND REQ_BRANCH_ID = ".$branch_id;
			$resultIDHDR = $this->db->query($sqlIDHDR)->result_array();
			$HDR_ID = $resultIDHDR[0]['REQ_ID'];
			$REQ_DATE_REQ = $resultIDHDR[0]['REQ_DELIVERY_DATE'];

			//update request delivery dtl
			$updateReqDelDTL = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_STATUS = '2', REQ_DTL_ACTIVE = 'T' WHERE REQ_HDR_ID = '".$HDR_ID."'  AND  REQ_DTL_CONT = '".$cont."'";
			$this->db->query($updateReqDelDTL);

			//update request delivery hdr
			// get count req dtl
			$countReqDelDtl = "SELECT COUNT(1) TOTAL FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = ".$HDR_ID;
			$ResultcountReqDelDtl = $this->db->query($countReqDelDtl)->row_array();
			$total_dtl_del = $ResultcountReqDelDtl['TOTAL'];

			// get count finished on Chasis
			$getDtlFinished = "SELECT COUNT(1) TOTAL FROM TX_REQ_DELIVERY_DTL WHERE REQ_DTL_STATUS = '2' AND REQ_DTL_ACTIVE = 'T' AND REQ_HDR_ID = ".$HDR_ID;
			$ResultcountReqDelDtl = $this->db->query($getDtlFinished)->row_array();
			$total_dtl_fhinished = $ResultcountReqDelDtl['TOTAL'];

			//update status header non active
			if($total_dtl_fhinished == $total_dtl_del){
				$updateReqDelHDR = "UPDATE TX_REQ_DELIVERY_HDR SET REQUEST_STATUS = '2' WHERE REQ_NO = '".$no_req."' AND REQ_BRANCH_ID = ".$branch_id;
				$this->db->query($updateReqDelHDR);
			}

		}
		else{
			//get ID hdr and date Delivery
			$sqlIDHDR = "SELECT REQ_ID, TO_CHAR(REQ_DELIVERY_DATE,'MM/DD/YYYY HH24:MI:SS') REQ_DELIVERY_DATE FROM TX_REQ_DELIVERY_HDR  WHERE REQ_NO = '".$no_req."' AND REQ_BRANCH_ID = ".$branch_id;
			$resultIDHDR = $this->db->query($sqlIDHDR)->result_array();
			$HDR_ID = $resultIDHDR[0]['REQ_ID'];
			$REQ_DATE_REQ = $resultIDHDR[0]['REQ_DELIVERY_DATE'];
		}

		// get data container yg stacking sekarang.
	  $cont_old = $this->db->query("SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS, '0' REAL_YARD_USED, '".$activity."' REAL_YARD_ACTIVITY,
												 REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY
												 FROM TX_REAL_YARD
												 WHERE REAL_YARD_BRANCH_ID = ".$branch_id." AND REAL_YARD_CONT = '".$cont."' AND REAL_YARD_STATUS = 1 AND REAL_YARD_USED = 1");

		if($cont_old->num_rows() > 0){
			foreach ($cont_old->result_array() as $val) {
			 $this->db->insert('TX_REAL_YARD',$val);
			}
			//UPDATE DATA LAMA MENJADI TIDAK AKTIF
			$this->db->query("UPDATE TX_REAL_YARD SET REAL_YARD_USED = '0', REAL_YARD_STATUS = 2 WHERE REAL_YARD_BRANCH_ID = ".$branch_id." AND REAL_YARD_CONT = '".$cont."' AND REAL_YARD_STATUS = 1 AND REAL_YARD_USED = 1");

			//insert history container
				if($req_to == 'TPK'){
					//insert history container
					$this->db->query("CALL ADD_HISTORY_CONTAINER(
								'".$cont."',
								'".$no_req."',
								'".$REQ_DATE_REQ."',
								'".$cont_size."',
								'".$cont_type."',
								'".$cont_status."',
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								4,
								'On Chasis',
							'".$gate_date_tpkh."',
								NULL,
								".$branch_id.",
								".$user_id.")");

					$this->db->query("CALL ADD_HISTORY_CONTAINER(
								'".$cont."',
								'".$no_req."',
								'".$REQ_DATE_REQ."',
								'".$cont_size."',
								'".$cont_type."',
								'".$cont_status."',
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								4,
								'GATE OUT TO TPK',
							'".$endTime."',
								NULL,
								".$branch_id.",
								".$user_id.")");
					}
					else{
						$this->db->query("CALL ADD_HISTORY_CONTAINER(
									'".$cont."',
									'".$no_req."',
									'".$REQ_DATE_REQ."',
									'".$cont_size."',
									'".$cont_type."',
									'".$cont_status."',
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									4,
									'On Chasis',
									NULL,
									NULL,
									".$branch_id.",
									".$user_id.")");
					}


				// update master container menjadi GATO
	 		 $this->db->set('CONTAINER_STATUS','GATO')->where('CONTAINER_NO',$cont)->update('TM_CONTAINER');
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$success = false;
		}

		return $success;
	}

}
