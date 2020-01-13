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
		$cont = $this->input->post('CONT');
		$activity = 8;
		$mark = NULL;

		$this->db->trans_start();

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
			$this->db->query("UPDATE TX_REAL_YARD SET REAL_YARD_USED = '0' WHERE REAL_YARD_BRANCH_ID = ".$branch_id." AND REAL_YARD_CONT = '".$cont."' AND REAL_YARD_STATUS = 1 AND REAL_YARD_USED = 1");

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
						'".$cont."',
						'".$req_id."',
						'".$req_date."',
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
						".$branch_id.")");

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
