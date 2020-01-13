<?php
class M_dashboard extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	public function getDataReceiving(){
		$branch_id = $this->session->USER_BRANCH;
		$sql = "SELECT COUNT(DONE) DONE, COUNT(OUTSTANDING) OUTSTANDING, COUNT(DONE) + COUNT(OUTSTANDING) TOTAL FROM (SELECT * FROM (SELECT A.REQUEST_NO REQ, 'Receiving' ACTIVITY, B.REQUEST_DTL_CONT CONT, C.GATE_CREATE_DATE GATE_IN,
		(SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS GATE_OUT, A.REQUEST_RECEIVING_DATE TGL_REQ,
		CASE WHEN C.GATE_CREATE_DATE IS NOT NULL AND (SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) IS NOT NULL
		THEN 'DONE'
		ELSE
		NULL
		END DONE,
		CASE WHEN C.GATE_CREATE_DATE IS NOT NULL AND (SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) IS NOT NULL
		THEN NULL
		ELSE
		'OUTSTANDING'
		END OUTSTANDING, A.REQUEST_STATUS REQ_STATUS, A.REQUEST_BRANCH_ID BRANCH_ID
		FROM TX_REQ_RECEIVING_HDR A
		JOIN TX_REQ_RECEIVING_DTL B ON B.REQUEST_HDR_ID = A.REQUEST_ID
		LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQUEST_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQUEST_NO) T
		WHERE T.BRANCH_ID = $branch_id AND T.GATE_OUT IS NULL OR REQ_STATUS != 2)";
		$data = $this->db->query($sql)->result();
    return $data;
	}

	public function getDataRecOutstanding(){
		$branch_id = $this->session->USER_BRANCH;

		$params = array(
			'BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);

		$end = $_REQUEST['start'] + $_REQUEST['limit'];
		$start = $_REQUEST['start'];

		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;

		//apply filter
		$qWhere = "";
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'TGL_REQ2'){
							$field = 'TGL_REQ';
						}
						else if($field == 'GATE_IN2'){
							$field = 'GATE_IN';
						}
						else if($field == 'GATE_OUT2'){
							$field = 'GATE_OUT';
						}
						else if($field == 'PAID_THRU'){
							$field = 'PAID_THRU_DATE';
						}

					if($operator == 'like'){
						$qs .= " AND UPPER(".$field.") LIKE '%".strtoupper($value)."%'";
					}
					else if($operator == 'lt'){
						$qs .= " AND ".$field." < TO_DATE('".$value."','MM/DD/YYYY')";
					}
					else if($operator == 'gt'){
						$qs .= " AND ".$field." > TO_DATE('".$value."','MM/DD/YYYY')";
					}
					else if($operator == 'eq'){
						$qs .= " AND TO_DATE(TO_CHAR(".$field.",'MM/DD/YYYY'),'MM/DD/YYYY') = TO_DATE('".$value."','MM/DD/YYYY')";
					}

				}
				$qWhere .= $qs;
			}
			// end filter

			$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (
				SELECT * FROM VIEW_REC_OUTSTANDING
			) T
					WHERE BRANCH_ID = $branch_id $qWhere AND ROWNUM <= $end
					ORDER BY TGL_REQ DESC ) F
						WHERE r >= $start + 1";
		$data = $this->db->query($query,$params)->result_array();

		$count = $this->db->query("SELECT COUNT(1) TOTAL FROM VIEW_REC_OUTSTANDING WHERE BRANCH_ID = $branch_id $qWhere")->row_array()['TOTAL'];

		return array (
			'data' => $data,
			'total' => $count
		);

	}

	public function getDataSp2(){
		$branch_id = $this->session->USER_BRANCH;
		$sql = "SELECT COUNT(DONE) DONE, COUNT(OUTSTANDING) OUTSTANDING, COUNT(DONE) + COUNT(OUTSTANDING) TOTAL FROM (SELECT * FROM (SELECT A.REQ_NO REQUEST_NO, 'Delivery' ACTIVITY, B.REQ_DTL_CONT REQUEST_DTL_CONT, C.GATE_TRUCK_NO GATE_TRUCK_NO, C.GATE_ORIGIN, C.GATE_CREATE_DATE GATE_IN,
		(SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS GATE_OUT, A.REQ_DELIVERY_DATE TGL_REQ,
		CASE WHEN C.GATE_CREATE_DATE IS NOT NULL AND (SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) IS NOT NULL
		THEN
		'DONE'
		ELSE
		NULL
		END DONE,
		CASE WHEN C.GATE_CREATE_DATE IS NOT NULL AND (SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) IS NOT NULL
		THEN
		NULL
		ELSE
		'OUTSTANDING'
		END OUTSTANDING, A.REQUEST_STATUS REQ_STATUS, A.REQ_BRANCH_ID BRANCH_ID, A.REQUEST_TO, A.REQ_ID
		FROM TX_REQ_DELIVERY_HDR A
		JOIN TX_REQ_DELIVERY_DTL B ON B.REQ_HDR_ID = A.REQ_ID
		LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQ_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQ_NO) T
		WHERE REQUEST_TO = 'DEPO' AND T.BRANCH_ID = $branch_id AND (T.GATE_OUT IS NULL OR T.REQ_STATUS != 2) AND (T.REQUEST_DTL_CONT,T.REQ_ID) NOT IN
		(SELECT Y.REQ_DTL_CONT, Y.REQ_HDR_ID FROM TX_REQ_DELIVERY_HDR P JOIN TX_REQ_DELIVERY_DTL Y ON Y.REQ_HDR_ID =  P.REQ_ID  WHERE Y.REQ_DTL_ACTIVE = 'T' AND REQ_BRANCH_ID = P.REQ_BRANCH_ID))";
		$data = $this->db->query($sql)->result();
		return $data;
	}

	public function getDataSp2Outstanding(){
		$branch_id = $this->session->USER_BRANCH;

		$params = array(
			'BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);

		$end = $_REQUEST['start'] + $_REQUEST['limit'];
		$start = $_REQUEST['start'];

		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;

		//apply filter
		$qWhere = "";
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'TGL_REQ2'){
							$field = 'TGL_REQ';
						}
						else if($field == 'GATE_IN2'){
							$field = 'GATE_IN';
						}
						else if($field == 'GATE_OUT2'){
							$field = 'GATE_OUT';
						}
						else if($field == 'PAID_THRU2'){
							$field = 'PAID_THRU';
						}

					if($operator == 'like'){
						$qs .= " AND UPPER(".$field.") LIKE '%".strtoupper($value)."%'";
					}
					else if($operator == 'lt'){
						$qs .= " AND ".$field." < TO_DATE('".$value."','MM/DD/YYYY')";
					}
					else if($operator == 'gt'){
						$qs .= " AND ".$field." > TO_DATE('".$value."','MM/DD/YYYY')";
					}
					else if($operator == 'eq'){
						$qs .= " AND TO_DATE(TO_CHAR(".$field.",'MM/DD/YYYY'),'MM/DD/YYYY') = TO_DATE('".$value."','MM/DD/YYYY')";
					}

				}
				$qWhere .= $qs;
			}
			// end filter

			$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (
				SELECT * FROM VIEW_DEL_DEPO_OUTSTANDING
			) T
					WHERE BRANCH_ID = $branch_id $qWhere AND ROWNUM <= $end
					ORDER BY TGL_REQ DESC ) F
						WHERE r >= $start + 1";
		$data = $this->db->query($query,$params)->result_array();

		$count = $this->db->query("SELECT COUNT(1) TOTAL FROM VIEW_DEL_DEPO_OUTSTANDING WHERE BRANCH_ID = $branch_id $qWhere")->row_array()['TOTAL'];

		return array (
			'data' => $data,
			'total' => $count
		);
	}

	public function getDataRepo(){
		$branch_id = $this->session->USER_BRANCH;
		$sql = "SELECT COUNT(DONE) DONE, COUNT(OUTSTANDING) OUTSTANDING, COUNT(DONE) + COUNT(OUTSTANDING) TOTAL FROM (SELECT * FROM (SELECT A.REQ_NO REQUEST_NO, 'Delivery' ACTIVITY, B.REQ_DTL_CONT REQUEST_DTL_CONT, C.GATE_TRUCK_NO GATE_TRUCK_NO, C.GATE_ORIGIN, C.GATE_CREATE_DATE GATE_IN,
		(SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS GATE_OUT, A.REQ_DELIVERY_DATE TGL_REQ,
		CASE WHEN C.GATE_CREATE_DATE IS NOT NULL AND (SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) IS NOT NULL
		THEN
		'DONE'
		ELSE
		NULL
		END DONE,
		CASE WHEN C.GATE_CREATE_DATE IS NOT NULL AND (SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) IS NOT NULL
		THEN
		NULL
		ELSE
		'OUTSTANDING'
		END OUTSTANDING, A.REQUEST_STATUS REQ_STATUS, A.REQ_BRANCH_ID BRANCH_ID, A.REQUEST_TO, A.REQ_ID
		FROM TX_REQ_DELIVERY_HDR A
		JOIN TX_REQ_DELIVERY_DTL B ON B.REQ_HDR_ID = A.REQ_ID
		LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQ_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQ_NO) T
		WHERE REQUEST_TO = 'TPK' AND T.BRANCH_ID = $branch_id AND (T.GATE_OUT IS NULL OR T.REQ_STATUS != 2) AND (T.REQUEST_DTL_CONT,T.REQ_ID) NOT IN
		(SELECT Y.REQ_DTL_CONT, Y.REQ_HDR_ID FROM TX_REQ_DELIVERY_HDR P JOIN TX_REQ_DELIVERY_DTL Y ON Y.REQ_HDR_ID =  P.REQ_ID  WHERE Y.REQ_DTL_ACTIVE = 'T' AND REQ_BRANCH_ID = P.REQ_BRANCH_ID))";
		$data = $this->db->query($sql)->result();
		return $data;
	}

	public function getDataRepoOutstanding(){
		$branch_id = $this->session->USER_BRANCH;

		$params = array(
			'BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);

		$end = $_REQUEST['start'] + $_REQUEST['limit'];
		$start = $_REQUEST['start'];

		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;

		//apply filter
		$qWhere = "";
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'TGL_REQ2'){
							$field = 'TGL_REQ';
						}
						else if($field == 'GATE_IN2'){
							$field = 'GATE_IN';
						}
						else if($field == 'GATE_OUT2'){
							$field = 'GATE_OUT';
						}
						else if($field == 'PAID_THRU2'){
							$field = 'PAID_THRU';
						}

					if($operator == 'like'){
						$qs .= " AND UPPER(".$field.") LIKE '%".strtoupper($value)."%'";
					}
					else if($operator == 'lt'){
						$qs .= " AND ".$field." < TO_DATE('".$value."','MM/DD/YYYY')";
					}
					else if($operator == 'gt'){
						$qs .= " AND ".$field." > TO_DATE('".$value."','MM/DD/YYYY')";
					}
					else if($operator == 'eq'){
						$qs .= " AND TO_DATE(TO_CHAR(".$field.",'MM/DD/YYYY'),'MM/DD/YYYY') = TO_DATE('".$value."','MM/DD/YYYY')";
					}

				}
				$qWhere .= $qs;
			}
			// end filter

			$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (
				SELECT * FROM VIEW_DEL_REPO_OUTSTANDING
			) T
					WHERE BRANCH_ID = $branch_id $qWhere AND ROWNUM <= $end
					ORDER BY TGL_REQ DESC ) F
						WHERE r >= $start + 1";
		$data = $this->db->query($query,$params)->result_array();

		$count = $this->db->query("SELECT COUNT(1) TOTAL FROM VIEW_DEL_REPO_OUTSTANDING WHERE BRANCH_ID = $branch_id $qWhere")->row_array()['TOTAL'];

		return array (
			'data' => $data,
			'total' => $count
		);
	}

	public function getDataStuffing(){
		$branch_id = $this->session->USER_BRANCH;
		$sql = "SELECT COUNT(DONE) DONE, COUNT(OUTSTANDING) OUTSTANDING, COUNT(DONE) + COUNT(OUTSTANDING) TOTAL FROM (SELECT Z.*,
		CASE WHEN Z.DATE_START IS NOT NULL AND Z.DATE_END IS NOT NULL THEN 'DONE' ELSE NULL END DONE,
		CASE WHEN Z.DATE_START IS NOT NULL AND Z.DATE_END IS NOT NULL THEN NULL ELSE 'OUTSTANDING' END OUTSTANDING
		 FROM (SELECT B.STUFF_DTL_CONT CONTAINER_NUMBER, A.STUFF_NO REQUEST_NUMBER,
		TO_CHAR(A.STUFF_CREATE_DATE ,'DD/MM/YYYY HH24:MI:SS') DATE_REQUEST,
		(SELECT TO_CHAR(REAL_STUFF_DATE,'DD/MM/YYYY HH24:MI:SS') FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = B.STUFF_DTL_CONT AND REAL_STUFF_NOREQ = A.STUFF_NO AND REAL_STUFF_BRANCH_ID = A.STUFF_BRANCH_ID AND REAL_STUFF_STATUS = '1' ) DATE_START,
		(SELECT TO_CHAR(REAL_STUFF_DATE,'DD/MM/YYYY HH24:MI:SS') FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = B.STUFF_DTL_CONT AND REAL_STUFF_NOREQ = A.STUFF_NO AND REAL_STUFF_BRANCH_ID = A.STUFF_BRANCH_ID AND REAL_STUFF_STATUS = '2' ) DATE_END
		FROM
		TX_REQ_STUFF_HDR A
		INNER JOIN TX_REQ_STUFF_DTL B ON B.STUFF_DTL_HDR_ID = A.STUFF_ID
		JOIN TM_CONSIGNEE C ON C.CONSIGNEE_ID = A.STUFF_CONSIGNEE_ID
		WHERE A.STUFF_BRANCH_ID = $branch_id AND  A.STUFF_STATUS <> '2' AND B.STUFF_DTL_ACTIVE = 'Y'
		) Z)";
		$data = $this->db->query($sql)->result();
		return $data;
	}

	public function getDataStuffingOutstanding(){
		$branch_id = $this->session->USER_BRANCH;

		$params = array(
			'BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);

		$end = $_REQUEST['start'] + $_REQUEST['limit'];
		$start = $_REQUEST['start'];

		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;

		//apply filter
		$qWhere = "";
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'DATE_REQUEST2'){
							$field = 'DATE_REQUEST';
						}
						else if($field == 'DATE_START2'){
							$field = 'DATE_START';
						}
						else if($field == 'END_START2'){
							$field = 'END_START';
						}
						else if($field == 'PAID_THRU2'){
							$field = 'PAID_THRU';
						}

					if($operator == 'like'){
						$qs .= " AND UPPER(".$field.") LIKE '%".strtoupper($value)."%'";
					}
					else if($operator == 'lt'){
						$qs .= " AND ".$field." < TO_DATE('".$value."','MM/DD/YYYY')";
					}
					else if($operator == 'gt'){
						$qs .= " AND ".$field." > TO_DATE('".$value."','MM/DD/YYYY')";
					}
					else if($operator == 'eq'){
						$qs .= " AND TO_DATE(TO_CHAR(".$field.",'MM/DD/YYYY'),'MM/DD/YYYY') = TO_DATE('".$value."','MM/DD/YYYY')";
					}

				}
				$qWhere .= $qs;
			}
			// end filter

			$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (
				SELECT * FROM VIEW_STUFF_OUTSTANDING
			) T
					WHERE BRANCH_ID = $branch_id $qWhere AND ROWNUM <= $end
					ORDER BY DATE_REQUEST DESC ) F
						WHERE r >= $start + 1";
		$data = $this->db->query($query,$params)->result_array();

		$count = $this->db->query("SELECT COUNT(1) TOTAL FROM VIEW_STUFF_OUTSTANDING WHERE BRANCH_ID = $branch_id $qWhere")->row_array()['TOTAL'];

		return array (
			'data' => $data,
			'total' => $count
		);
	}

	public function getDataStripping(){
		$branch_id = $this->session->USER_BRANCH;
		$sql = "SELECT COUNT(DONE) DONE, COUNT(OUTSTANDING) OUTSTANDING, COUNT(DONE) + COUNT(OUTSTANDING) TOTAL FROM (SELECT Z.*,
		CASE WHEN Z.DATE_START IS NOT NULL AND Z.DATE_END IS NOT NULL THEN 'DONE' ELSE NULL END DONE,
		CASE WHEN Z.DATE_START IS NOT NULL AND Z.DATE_END IS NOT NULL THEN NULL ELSE 'OUTSTANDING' END OUTSTANDING
		FROM (SELECT B.STRIP_DTL_CONT CONTAINER_NUMBER, A.STRIP_NO REQUEST_NUMBER,
		TO_CHAR(A.STRIP_CREATE_DATE,'DD/MM/YYYY HH24:MI:SS') DATE_REQUEST,
		(SELECT TO_CHAR(REAL_STRIP_DATE,'DD/MM/YYYY HH24:MI:SS') FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = B.STRIP_DTL_CONT AND REAL_STRIP_NOREQ = A.STRIP_NO AND REAL_STRIP_BRANCH_ID = A.STRIP_BRANCH_ID AND REAL_STRIP_STATUS = '1' ) DATE_START,
		(SELECT TO_CHAR(REAL_STRIP_DATE,'DD/MM/YYYY HH24:MI:SS') FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = B.STRIP_DTL_CONT AND REAL_STRIP_NOREQ = A.STRIP_NO AND REAL_STRIP_BRANCH_ID = A.STRIP_BRANCH_ID AND REAL_STRIP_STATUS = '2' ) DATE_END
		FROM
		TX_REQ_STRIP_HDR A
		INNER JOIN TX_REQ_STRIP_DTL B ON B.STRIP_DTL_HDR_ID = A.STRIP_ID
		JOIN TM_CONSIGNEE C ON C.CONSIGNEE_ID = A.STRIP_CONSIGNEE_ID
		WHERE A.STRIP_BRANCH_ID = $branch_id AND A.STRIP_STATUS <> 2 AND B.STRIP_DTL_ACTIVE = 'Y'
		) Z)";
		$data = $this->db->query($sql)->result();
		return $data;
	}

	public function getDataStrippingOutstanding(){
		$branch_id = $this->session->USER_BRANCH;

		$params = array(
			'BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);

		$end = $_REQUEST['start'] + $_REQUEST['limit'];
		$start = $_REQUEST['start'];

		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;

		//apply filter
		$qWhere = "";
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'DATE_REQUEST2'){
							$field = 'DATE_REQUEST';
						}
						else if($field == 'DATE_START2'){
							$field = 'DATE_START';
						}
						else if($field == 'END_START2'){
							$field = 'END_START';
						}
						else if($field == 'PAID_THRU2'){
							$field = 'PAID_THRU';
						}

					if($operator == 'like'){
						$qs .= " AND UPPER(".$field.") LIKE '%".strtoupper($value)."%'";
					}
					else if($operator == 'lt'){
						$qs .= " AND ".$field." < TO_DATE('".$value."','MM/DD/YYYY')";
					}
					else if($operator == 'gt'){
						$qs .= " AND ".$field." > TO_DATE('".$value."','MM/DD/YYYY')";
					}
					else if($operator == 'eq'){
						$qs .= " AND TO_DATE(TO_CHAR(".$field.",'MM/DD/YYYY'),'MM/DD/YYYY') = TO_DATE('".$value."','MM/DD/YYYY')";
					}

				}
				$qWhere .= $qs;
			}
			// end filter

			$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (
				SELECT * FROM VIEW_STRIP_OUTSTANDING
			) T
					WHERE BRANCH_ID = $branch_id $qWhere AND ROWNUM <= $end
					ORDER BY DATE_REQUEST DESC ) F
						WHERE r >= $start + 1";
		$data = $this->db->query($query,$params)->result_array();

		$count = $this->db->query("SELECT COUNT(1) TOTAL FROM VIEW_STRIP_OUTSTANDING WHERE BRANCH_ID = $branch_id $qWhere")->row_array()['TOTAL'];

		return array (
			'data' => $data,
			'total' => $count
		);
	}

	public function getDataYor(){
		$branch_id = (int)$this->session->USER_BRANCH;
		$user_id = (int)$this->session->isId;
		$yard = (int)$this->session->YARD_ACTIVE;

		$yard_capacity = (int)$this->db->select_sum('BLOCK_CAPACITY')->where('BLOCK_YARD_ID',$yard)->where('BLOCK_ACTIVE','Y')->where('BLOCK_DUMMY','N')->where('BLOCK_BRANCH_ID',$branch_id)->get('TM_BLOCK')->row_array()['BLOCK_CAPACITY'];
		$disable_cell = $this->db->where('CELL_YARD_ID',$yard)->where('CELL_BRANCH_ID',$branch_id)->from('TX_CELL_DISABLE')->count_all_results();
		$total_yor = $yard_capacity-$disable_cell;

		$stacking = $this->db->query("SELECT COUNT(REAL_YARD_ID) STACKING FROM(SELECT MAX(REAL_YARD_ID) REAL_YARD_ID, REAL_YARD_CONT  FROM(
			SELECT REAL_YARD_ID, REAL_YARD_CONT
			FROM TX_REAL_YARD
			WHERE REAL_YARD_ID IN(
				SELECT X.REAL_YARD_ID  FROM (
				 	SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_BRANCH_ID = ".$branch_id." GROUP BY H.REAL_YARD_CONT
			 	)X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
			) AND REAL_YARD_NO = $yard
		)Z GROUP BY Z.REAL_YARD_CONT)")->row_array()['STACKING'];

		return array('YOR' => (int)$total_yor, 'STACKING' => (int)$stacking);
	}


}
?>
