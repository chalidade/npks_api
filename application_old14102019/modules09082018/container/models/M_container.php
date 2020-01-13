<?php
class M_container extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	public function get_data_container_inquiry($filter){
		$params = $filter;		
			
		$sql = "
			SELECT 
			    A.REAL_YARD_CONT,
			    D.YARD_NAME, 
			    C.BLOCK_NAME, 
			    B.YBC_ROW, 
			    B.YBC_SLOT, 
			    A.REAL_YARD_TIER,
			    E.REFF_NAME YARD_TYPE, 
			    F.REFF_NAME YARD_STATUS
			FROM TX_REAL_YARD A
			JOIN TX_YARD_BLOCK_CELL B ON B.YBC_ID = A.REAL_YARD_YBC_ID
			JOIN TM_BLOCK C ON C.BLOCK_ID = B.YBC_BLOCK_ID
			JOIN TM_YARD D ON D.YARD_ID = C.BLOCK_YARD_ID
			JOIN TM_REFF E ON A.REAL_YARD_TYPE =  E.REFF_ID AND E.REFF_TR_ID = 25
			JOIN TM_REFF F ON A.REAL_YARD_TYPE =  F.REFF_ID AND F.REFF_TR_ID = 26
			WHERE 
				A.REAL_YARD_CONT = ? 
				AND  A.REAL_YARD_BRANCH_ID = ?
				AND ROWNUM = 1
			ORDER BY A.REAL_YARD_CREATE_DATE DESC";
		
		
		$data = $this->db->query($sql,$params)->row();
    	return $data;

	}

	public function get_data_container_history($filter){
		$params = $filter;		
			
		$sql = "
			SELECT 
			    A.REAL_YARD_CONT,
			    D.YARD_NAME, 
			    C.BLOCK_NAME, 
			    B.YBC_ROW, 
			    B.YBC_SLOT, 
			    A.REAL_YARD_TIER,
			    E.REFF_NAME YARD_TYPE, 
			    F.REFF_NAME YARD_STATUS,
			    to_char(A.REAL_YARD_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') REAL_YARD_CREATE_DATE
			FROM TX_REAL_YARD A
			JOIN TX_YARD_BLOCK_CELL B ON B.YBC_ID = A.REAL_YARD_YBC_ID
			JOIN TM_BLOCK C ON C.BLOCK_ID = B.YBC_BLOCK_ID
			JOIN TM_YARD D ON D.YARD_ID = C.BLOCK_YARD_ID
			JOIN TM_REFF E ON A.REAL_YARD_TYPE =  E.REFF_ID AND E.REFF_TR_ID = 25
			JOIN TM_REFF F ON A.REAL_YARD_TYPE =  F.REFF_ID AND F.REFF_TR_ID = 26
			WHERE 
				A.REAL_YARD_CONT = ? 
				AND  A.REAL_YARD_BRANCH_ID = ?
			ORDER BY A.REAL_YARD_CREATE_DATE DESC";
		
		
		$data = $this->db->query($sql,$params)->result();
    	return $data;

	}

	public function get_all_container_list($filter){
		$params = $filter;		
			
		$sql = "
			SELECT 
			    DISTINCT(A.REAL_YARD_CONT) REAL_YARD_CONT
			FROM TX_REAL_YARD A
			WHERE A.REAL_YARD_BRANCH_ID = ?";		
		
		$data = $this->db->query($sql,$params)->result();
    	return $data;

	}

	public function getStuffingHistory($filter){
		$params = array($filter["BRANCH_ID"]);

		$whereParams = '';
		
		if($filter['CONTAINER_NUMBER']){
			$params[] = '%' . $filter['CONTAINER_NUMBER'] . '%';
			$whereParams .= ' AND LOWER(TRS_START.REAL_STUFF_CONT) LIKE ?';
		}
		if($filter['REQUEST_NUMBER']){
			$params[] = '%' . $filter['REQUEST_NUMBER'] . '%'	;
			$whereParams .= ' AND LOWER(HDR_START.STUFF_NO)LIKE ?';
		}

		$sql = "SELECT 
			    TRS_START.REAL_STUFF_CONT CONTAINER_NUMBER, 
			    HDR_START.STUFF_NO REQUEST_NUMBER, 
			    HDR_START.STUFF_CREATE_DATE DATE_REQUEST,    
			    TRS_START.REAL_STUFF_DATE DATE_START,     
			    TRS_END.REAL_STUFF_DATE DATE_END,
			    (
			            SELECT MAX(GATE_CREATE_DATE) 
			            FROM TX_GATE
			            WHERE ROWNUM = 1
			            AND TX_GATE.GATE_CONT = TRS_START.REAL_STUFF_CONT
			            AND TX_GATE.GATE_BRANCH_ID = TRS_START.REAL_STUFF_BRANCH_ID
			            AND TX_GATE.GATE_CREATE_DATE < HDR_START.STUFF_CREATE_DATE
			            AND TX_GATE.GATE_STATUS = 1
			            AND TX_GATE.GATE_ACTIVITY = 3
			    )
			    DATE_GATEIN 
				FROM TX_REAL_STUFF TRS_START
				JOIN TX_REQ_STUFF_HDR HDR_START
				ON 
				    TRS_START.REAL_STUFF_BRANCH_ID = HDR_START.STUFF_BRANCH_ID 
				    AND 
				    TRS_START.REAL_STUFF_HDR_ID = HDR_START.STUFF_ID
				LEFT JOIN TX_REAL_STUFF TRS_END
				ON 
				    TRS_END.REAL_STUFF_BRANCH_ID = TRS_START.REAL_STUFF_BRANCH_ID 
				    AND 
				    TRS_END.REAL_STUFF_CONT = TRS_START.REAL_STUFF_CONT
				    AND 
				    TRS_END.REAL_STUFF_HDR_ID = TRS_START.REAL_STUFF_HDR_ID
				    AND 
				    TRS_END.REAL_STUFF_STATUS = 2
				WHERE 
				    TRS_START.REAL_STUFF_BRANCH_ID = ?
			    	AND TRS_START.REAL_STUFF_STATUS = 1 ".$whereParams;
		$data = $this->db->query($sql,$params)->result();
    	return $data;
	}

	public function getStrippingHistory($filter){
		$params = array($filter["BRANCH_ID"]);

		$whereParams = '';
		
		if($filter['CONTAINER_NUMBER']){
			$params[] = '%' . $filter['CONTAINER_NUMBER'] . '%';
			$whereParams .= ' AND LOWER(TRS_START.REAL_STRIP_CONT) LIKE ?';
		}
		if($filter['REQUEST_NUMBER']){
			$params[] = '%' . $filter['REQUEST_NUMBER'] . '%'	;
			$whereParams .= ' AND LOWER(HDR_START.STRIP_NO)LIKE ?';
		}

		$sql = "SELECT 
			    TRS_START.REAL_STRIP_CONT CONTAINER_NUMBER, 
			    HDR_START.STRIP_NO REQUEST_NUMBER, 
			    HDR_START.STRIP_CREATE_DATE DATE_REQUEST,    
			    TRS_START.REAL_STRIP_START DATE_START,     
			    TRS_START.REAL_STRIP_END DATE_END,
			    (
			            SELECT MAX(GATE_CREATE_DATE) 
			            FROM TX_GATE
			            WHERE ROWNUM = 1
			            AND TX_GATE.GATE_CONT = TRS_START.REAL_STRIP_CONT
			            AND TX_GATE.GATE_BRANCH_ID = TRS_START.REAL_STRIP_BRANCH_ID
			            AND TX_GATE.GATE_CREATE_DATE < HDR_START.STRIP_CREATE_DATE
			            AND TX_GATE.GATE_STATUS = 1
			            AND TX_GATE.GATE_ACTIVITY = 3
			    )
			    DATE_GATEIN 
				FROM TX_REAL_STRIP TRS_START
				JOIN TX_REQ_STRIP_HDR HDR_START
				ON 
				    TRS_START.REAL_STRIP_BRANCH_ID = HDR_START.STRIP_BRANCH_ID 
				    AND 
				    TRS_START.REAL_STRIP_HDR_ID = HDR_START.STRIP_ID				
				WHERE 
				    TRS_START.REAL_STRIP_BRANCH_ID = ?".$whereParams;
		$data = $this->db->query($sql,$params)->result();
    	return $data;
	}
}