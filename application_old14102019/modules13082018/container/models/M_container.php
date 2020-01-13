<?php
class M_container extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	public function get_data_container_inquiry($filter){
		$params[] = $filter[0];
		$params[] = $filter[1];		
			
		$whereParams = '';
		
		if($filter[2]){
			$params[] = $filter[2];
			$whereParams .= ' AND HIST_COUNTER = ? ';
		}
		$sql = "
			SELECT * 
			FROM TX_HISTORY_CONTAINER 
			WHERE 
				HIST_CONT = ? 
			AND HIST_BRANCH_ID = ? 
			". $whereParams ."
			ORDER BY HIST_DATE DESC
		";		
		
		$data = $this->db->query($sql,$params)->row();
    	return $data;

	}

	public function get_cycle_container($filter){
		
		$params[] = $filter[0];
		$params[] = $filter[1];	
		$sql = "
			SELECT HIST_COUNTER
			FROM TX_HISTORY_CONTAINER 
			WHERE 
				HIST_CONT = ? 
			AND HIST_BRANCH_ID = ?
			GROUP BY HIST_COUNTER
			ORDER BY HIST_COUNTER DESC
		";		
		
		$data = $this->db->query($sql,$params)->result();
    	return $data;
	}

	public function get_data_container_history($filter){
		$params[] = $filter[0];
		$params[] = $filter[1];
		
		$whereParams = '';

		if($filter[2]){
			$params[] = $filter[2];
			$whereParams .= ' AND HIST_COUNTER = ? ';
		}
		
		$sql = "
			SELECT 
				HIST_CONT, 
				HIST_YARD, 
				HIST_BLOCK, 
				HIST_ROW, 
				HIST_SLOT, 
				HIST_TIER, 
				HIST_ACTIVITY,
				HIST_COUNTER,
				to_char(HIST_DATE,'MM/DD/YYYY HH24:MI:SS') HIST_DATE
			FROM TX_HISTORY_CONTAINER 
			WHERE 
				HIST_CONT = ? 
			AND HIST_BRANCH_ID = ? 
			". $whereParams ."
			ORDER BY HIST_DATE DESC
		";		

		//die($sql);
		
		$data = $this->db->query($sql,$params)->result();
    	return $data;

	}

	public function get_all_container_list($filter){
		$params[] = $filter[0];		
		$whereParams = "";
		
		if($filter[1]){
			$params[] = '%' . strtolower($filter[1]) . '%';
			$whereParams .= ' AND LOWER(A.REAL_YARD_CONT) LIKE ?';
		}
		
		$sql = "
			SELECT 
			    DISTINCT(A.REAL_YARD_CONT) REAL_YARD_CONT
			FROM TX_REAL_YARD A
			WHERE A.REAL_YARD_BRANCH_ID = ? " . $whereParams;		
		
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
			    to_char(HDR_START.STUFF_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') DATE_REQUEST,    
			    to_char(TRS_START.REAL_STUFF_DATE,'MM/DD/YYYY HH24:MI:SS') DATE_START,     
			    to_char(TRS_END.REAL_STUFF_DATE,'MM/DD/YYYY HH24:MI:SS') DATE_END
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
			    to_char(HDR_START.STRIP_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') DATE_REQUEST,    
			    to_char(TRS_START.REAL_STRIP_START,'MM/DD/YYYY HH24:MI:SS') DATE_START,     
			    to_char(TRS_START.REAL_STRIP_END,'MM/DD/YYYY HH24:MI:SS') DATE_END
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