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
			SELECT A.HIST_CONT, NVL(A.HIST_SLOT,'-') HIST_SLOT, NVL(A.HIST_ROW,'-') HIST_ROW, NVL(A.HIST_TIER,'-') HIST_TIER, NVL(A.HIST_BLOCK,'-') HIST_BLOCK, NVL(A.HIST_YARD,'-') HIST_YARD,
			A.HIST_ACTIVITY, A.HIST_CONT_STATUS, A.HIST_COUNTER, B.CONTAINER_SIZE CONTAINER_SIZE
			FROM TH_HISTORY_CONTAINER A
			LEFT JOIN TM_CONTAINER B
			ON A.HIST_CONT = B.CONTAINER_NO
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
			FROM TH_HISTORY_CONTAINER
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
				NVL(HIST_YARD,'-') HIST_YARD,
				NVL(HIST_BLOCK,'-') HIST_BLOCK,
				NVL(HIST_ROW,'-') HIST_ROW,
				NVL(HIST_SLOT,'-') HIST_SLOT,
				NVL(HIST_TIER,'-') HIST_TIER,
				HIST_ACTIVITY,
				HIST_COUNTER,
				TM_CONTAINER.CONTAINER_SIZE CONTAINER_SIZE,
				to_char(HIST_DATE,'MM/DD/YYYY HH24:MI:SS') HIST_DATE,
				HIST_CONT_STATUS
			FROM TH_HISTORY_CONTAINER
			LEFT JOIN TM_CONTAINER
			ON TH_HISTORY_CONTAINER.HIST_CONT = TM_CONTAINER.CONTAINER_NO
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
			$whereParams .= ' AND LOWER(A.REQUEST_DTL_CONT) LIKE ?';
		}


		$sql = "
			SELECT
			    DISTINCT(A.REQUEST_DTL_CONT) REAL_YARD_CONT
			FROM TX_REQ_RECEIVING_DTL A
			JOIN TX_REQ_RECEIVING_HDR B ON B.REQUEST_ID = A.REQUEST_HDR_ID
			WHERE B.REQUEST_BRANCH_ID = ? " . $whereParams;

		$data = $this->db->query($sql,$params)->result();
    	return $data;

	}

	public function getStuffingHistory($filter){
		$params 		= array($filter["BRANCH_ID"]);
		$paramsTotal 	= array($filter["BRANCH_ID"]);

		$params[] 	= $_REQUEST['start'] + $_REQUEST['limit'];
		$params[] 	= $_REQUEST['start'];

		$whereParams = '';

		// if($filter['CONTAINER_NUMBER']){
		// 	$params[] = '%' . $filter['CONTAINER_NUMBER'] . '%';
		// 	$paramsTotal[] = '%' . $filter['CONTAINER_NUMBER'] . '%';
		// 	$whereParams .= ' AND LOWER(TRS_START.REAL_STUFF_CONT) LIKE ?';
		// }
		// if($filter['REQUEST_NUMBER']){
		// 	$params[] = '%' . $filter['REQUEST_NUMBER'] . '%'	;
		// 	$paramsTotal[] = '%' . $filter['REQUEST_NUMBER'] . '%'	;
		// 	$whereParams .= ' AND LOWER(HDR_START.STUFF_NO)LIKE ?';
		// }

		//apply filter
		$qWhere = "";
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
					$qs .= " AND ".$field." LIKE '%".strtoupper($value)."%'";

				}
				$qWhere .= $qs;
			}
			// end filter

		$sql = "
			SELECT * FROM
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						SELECT
						    DTL_START.STUFF_DTL_CONT CONTAINER_NUMBER,
						    HDR_START.STUFF_NO REQUEST_NUMBER,
						    to_char(HDR_START.STUFF_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') DATE_REQUEST,
						    to_char(TRS_START.REAL_STUFF_DATE,'MM/DD/YYYY HH24:MI:SS') DATE_START,
						    to_char(TRS_END.REAL_STUFF_DATE,'MM/DD/YYYY HH24:MI:SS') DATE_END
						FROM TX_REQ_STUFF_HDR HDR_START
						JOIN TX_REQ_STUFF_DTL DTL_START
						ON
							DTL_START.STUFF_DTL_HDR_ID = HDR_START.STUFF_ID
						LEFT JOIN TX_REAL_STUFF TRS_START
						ON
						    TRS_START.REAL_STUFF_BRANCH_ID = HDR_START.STUFF_BRANCH_ID
						    AND
						    TRS_START.REAL_STUFF_HDR_ID = HDR_START.STUFF_ID
						    AND
						    TRS_START.REAL_STUFF_STATUS = 1
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
						    HDR_START.STUFF_BRANCH_ID = ?

					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1" .$qWhere;

		$data = $this->db->query($sql,$params)->result();

    	$sqlTotal = "SELECT * FROM(
    					SELECT
						    DTL_START.STUFF_DTL_CONT CONTAINER_NUMBER
						FROM TX_REQ_STUFF_HDR HDR_START
						JOIN TX_REQ_STUFF_DTL DTL_START
						ON
							DTL_START.STUFF_DTL_HDR_ID = HDR_START.STUFF_ID
						LEFT JOIN TX_REAL_STUFF TRS_START
						ON
						    TRS_START.REAL_STUFF_BRANCH_ID = HDR_START.STUFF_BRANCH_ID
						    AND
						    TRS_START.REAL_STUFF_HDR_ID = HDR_START.STUFF_ID
						    AND
						    TRS_START.REAL_STUFF_STATUS = 1
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
						    HDR_START.STUFF_BRANCH_ID = ?) WHERE 1=1 " . $qWhere;

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
	}

	public function getStrippingHistory($filter){
		$params 		= array($filter["BRANCH_ID"]);
		$paramsTotal 	= array($filter["BRANCH_ID"]);

		$params[] 	= $_REQUEST['start'] + $_REQUEST['limit'];
		$params[] 	= $_REQUEST['start'];

		$whereParams = '';
		// if($filter['CONTAINER_NUMBER']){
		// 	$params[] = '%' . $filter['CONTAINER_NUMBER'] . '%';
		// 	$paramsTotal[] = '%' . $filter['CONTAINER_NUMBER'] . '%';
		// 	$whereParams .= ' AND LOWER(TRS_START.REAL_STRIP_CONT) LIKE ?';
		// }
		// if($filter['REQUEST_NUMBER']){
		// 	$params[] = '%' . $filter['REQUEST_NUMBER'] . '%'	;
		// 	$paramsTotal[] = '%' . $filter['REQUEST_NUMBER'] . '%'	;
		// 	$whereParams .= ' AND LOWER(HDR_START.STRIP_NO)LIKE ?';
		// }

		//apply filter
		$qWhere = "";
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
					$qs .= " AND ".$field." LIKE '%".strtoupper($value)."%'";

				}
				$qWhere .= $qs;
			}
			// end filter


		$sql = "
			SELECT * FROM
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						SELECT
						    DTL_START.STRIP_DTL_CONT CONTAINER_NUMBER,
						    HDR_START.STRIP_NO REQUEST_NUMBER,
						    TO_CHAR(HDR_START.STRIP_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') DATE_REQUEST,
						    TO_CHAR(TRS_START.REAL_STRIP_DATE,'MM/DD/YYYY HH24:MI:SS') DATE_START,
                            TO_CHAR(TRS_END.REAL_STRIP_DATE,'MM/DD/YYYY HH24:MI:SS') DATE_END
						FROM TX_REQ_STRIP_HDR HDR_START
						JOIN TX_REQ_STRIP_DTL DTL_START
						ON
							DTL_START.STRIP_DTL_HDR_ID = HDR_START.STRIP_ID
						LEFT JOIN TX_REAL_STRIP TRS_START
						ON
						    TRS_START.REAL_STRIP_BRANCH_ID = HDR_START.STRIP_BRANCH_ID
						    AND
						    TRS_START.REAL_STRIP_HDR_ID = HDR_START.STRIP_ID
						    AND
						    TRS_START.REAL_STRIP_STATUS = 1
						LEFT JOIN TX_REAL_STRIP TRS_END
						ON
						    TRS_END.REAL_STRIP_BRANCH_ID = HDR_START.STRIP_BRANCH_ID
						    AND
						    TRS_END.REAL_STRIP_HDR_ID = HDR_START.STRIP_ID
						    AND
						    TRS_END.REAL_STRIP_STATUS = 2

						WHERE
						    HDR_START.STRIP_BRANCH_ID = ?
					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1" .$qWhere;
		$data = $this->db->query($sql,$params)->result();

		$sqlTotal = "SELECT * FROM( SELECT
						    DTL_START.STRIP_DTL_CONT CONTAINER_NUMBER
						FROM TX_REQ_STRIP_HDR HDR_START
						JOIN TX_REQ_STRIP_DTL DTL_START
						ON
							DTL_START.STRIP_DTL_HDR_ID = HDR_START.STRIP_ID
						LEFT JOIN TX_REAL_STRIP TRS_START
						ON
						    TRS_START.REAL_STRIP_BRANCH_ID = HDR_START.STRIP_BRANCH_ID
						    AND
						    TRS_START.REAL_STRIP_HDR_ID = HDR_START.STRIP_ID
						    AND
						    TRS_START.REAL_STRIP_STATUS = 1
						LEFT JOIN TX_REAL_STRIP TRS_END
						ON
						    TRS_END.REAL_STRIP_BRANCH_ID = HDR_START.STRIP_BRANCH_ID
						    AND
						    TRS_END.REAL_STRIP_HDR_ID = HDR_START.STRIP_ID
						    AND
						    TRS_END.REAL_STRIP_STATUS = 2

						WHERE
						    HDR_START.STRIP_BRANCH_ID =  ?) WHERE 1=1 " . $qWhere;

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
	}

	public function getStayPeriodContainer(){
		$branch_id = $this->session->USER_BRANCH;
		$params = array(
			'BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);

		$paramsTotal = array($this->session->USER_BRANCH);

		$qWhere = "";
		//$YARD_ID = $_REQUEST['YARD_ID'] != null? $_REQUEST['YARD_ID'] : false;

		$qw = '';
		// if($YARD_ID != false && $YARD_ID != 0){
		// 	$qWhere .= " AND YPG_YARD_ID = ".$YARD_ID;
		// }

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

		$sql = "SELECT NO_CONT, PEMILIK, KAPAL, CONTAINER_SIZE, CONTAINER_TYPE, STATUS, LOKASI, KEGIATAN, TO_CHAR(START_STACK,'MM/DD/YYYY HH24:MI:SS') START_STACK, DURASI_STACKING, TO_CHAR(MAX_DATE,'MM/DD/YYYY HH24:MI:SS') MAX_DATE FROM
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						SELECT * FROM VIEW_LONG_STAY_CONTAINER WHERE BRANCH = ? 
					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1" .$qWhere;

		$data = $this->db->query($sql,$params)->result_array();

		$sqlTotal = "SELECT * FROM(
    					SELECT * FROM VIEW_LONG_STAY_CONTAINER WHERE BRANCH = ? 
    				) WHERE 1=1 " . $qWhere;

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
	}

	public function getPrintStayPeriodContainer(){
		$branch_id = $this->session->USER_BRANCH;
		
		$qWhere = "";		

		$qw = '';
		
		$qs = '';
		$filters = isset($_REQUEST)? $_REQUEST : false;
		if ($filters != false){
			
			foreach ($filters as $key => $value){
					$field = $key;
					$value = $value;
				$qs .= " AND LOWER(".$field.") LIKE '%".strtolower($value)."%'";

			}
			$qWhere .= $qs;
		}

		$query = $this->db->query("SELECT * FROM(
					SELECT NO_CONT, PEMILIK, KAPAL, CONTAINER_SIZE, CONTAINER_TYPE, STATUS, LOKASI, KEGIATAN, TO_CHAR(START_STACK,'MM/DD/YYYY HH24:MI:SS') START_STACK, DURASI_STACKING, TO_CHAR(MAX_DATE,'MM/DD/YYYY HH24:MI:SS') MAX_DATE FROM VIEW_LONG_STAY_CONTAINER WHERE BRANCH = ".$branch_id." ) WHERE 1=1 " . $qWhere)->result_array();

		return $query;
	}
}
