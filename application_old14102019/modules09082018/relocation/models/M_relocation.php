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

			$data = $this->db->query("SELECT MAX(REAL_YARD_ID) REAL_YARD_ID, REAL_YARD_CONT  FROM(
								SELECT REAL_YARD_ID, REAL_YARD_CONT
								FROM TX_REAL_YARD
								WHERE REAL_YARD_ID IN(
									SELECT X.REAL_YARD_ID  FROM (
									 	SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_BRANCH_ID = ".$branch_id." GROUP BY H.REAL_YARD_CONT
								 	)X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
								)
							)Z GROUP BY Z.REAL_YARD_CONT")->result_array();

	    return $data;
	}

	function getContainerById(){
		 $id = $this->input->post('id');
		 $branch_id = $this->session->USER_BRANCH;
	   $data = $this->db->select('A.REAL_YARD_REQ_NO, A.REAL_YARD_CONT, A.REAL_YARD_CONT_SIZE, A.REAL_YARD_CONT_TYPE, A.REAL_YARD_CONT_STATUS, B.YBC_YARD_ID, B.YBC_BLOCK_ID, B.YBC_SLOT, B.YBC_ROW, A.REAL_YARD_TIER')
		 					->from('TX_REAL_YARD A')
							->join('TX_YARD_BLOCK_CELL B','A.REAL_YARD_YBC_ID = B.YBC_ID')
							->join('TM_YARD C','B.YBC_YARD_ID = C.YARD_ID')
							->join('TM_BLOCK D','B.YBC_BLOCK_ID = D.BLOCK_ID')
							->where('A.REAL_YARD_BRANCH_ID', $branch_id)
							->where('A.REAL_YARD_ID',$id)
							->get()->row_array();
	    return $data;
	}

	function setRelocation(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$success = true;

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
		$cont_size = $this->input->post('CONT_SIZE');
		$cont_type = $this->input->post('CONT_TYPE');
		$cont_status = $this->input->post('CONT_STATUS');


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
								'REAL_YARD_TYPE' => 2,
								'REAL_YARD_STATUS' => 1,
								'REAL_YARD_REQ_NO'=> $req_id,
								'REAL_YARD_CONT_SIZE' => $cont_size,
								'REAL_YARD_CONT_TYPE' => $cont_type,
								'REAL_YARD_CONT_STATUS' => $cont_status
		);

		// get data container yg stacking sekarang.
	  $cont_old = $this->db->query("SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS,
												 REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY
												 FROM TX_REAL_YARD
												 WHERE REAL_YARD_BRANCH_ID = ".$branch_id." AND REAL_YARD_ID = ".$real_yard_id);

		if($cont_old->num_rows() > 0){
			 $this->db->insert('TX_REAL_YARD',$cont_old->row_array());
		}

		$this->db->insert('TX_REAL_YARD',$arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$success = false;
		}

		return $success;
	}

}
