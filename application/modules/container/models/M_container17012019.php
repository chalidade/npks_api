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
		SELECT A.HIST_CONT, CASE WHEN A.HIST_SLOT_EXT IS NOT NULL THEN (A.HIST_SLOT_EXT || ' - ' || A.HIST_SLOT) ELSE A.HIST_SLOT END HIST_SLOT, NVL(A.HIST_ROW,'-') HIST_ROW, NVL(A.HIST_TIER,'-') HIST_TIER, NVL(A.HIST_BLOCK,'-') HIST_BLOCK, NVL(A.HIST_YARD,'-') HIST_YARD,
		A.HIST_ACTIVITY, A.HIST_CONT_STATUS, A.HIST_COUNTER, B.CONTAINER_SIZE CONTAINER_SIZE, NVL(A.HIST_SLOT_EXT,'-') HIST_SLOT_EXT, NVL(C.FULL_NAME,'System') HIST_USER
		FROM TH_HISTORY_CONTAINER A
		LEFT JOIN TM_CONTAINER B
		ON A.HIST_CONT = B.CONTAINER_NO
		LEFT JOIN TM_USER C ON C.USER_ID = A.HIST_USER
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
				CASE WHEN HIST_SLOT_EXT IS NOT NULL THEN (HIST_SLOT_EXT || ' - ' || HIST_SLOT) ELSE HIST_SLOT END HIST_SLOT,
				NVL(HIST_TIER,'-') HIST_TIER,
				HIST_ACTIVITY,
				HIST_COUNTER,
				TM_CONTAINER.CONTAINER_SIZE CONTAINER_SIZE,
				to_char(HIST_DATE,'DD/MM/YYYY HH24:MI:SS') HIST_DATE,
				HIST_DATE HIST_DATE2,
				HIST_CONT_STATUS,
				NVL(HIST_SLOT_EXT,'-') HIST_SLOT_EXT, NVL(C.FULL_NAME,'System') HIST_USER
			FROM TH_HISTORY_CONTAINER
			LEFT JOIN TM_CONTAINER
			ON TH_HISTORY_CONTAINER.HIST_CONT = TM_CONTAINER.CONTAINER_NO
			LEFT JOIN TM_USER C ON C.USER_ID = TH_HISTORY_CONTAINER.HIST_USER
			WHERE
				HIST_CONT = ?
			AND HIST_BRANCH_ID = ?
			". $whereParams ."
			ORDER BY HIST_DATE2 DESC
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
			$whereParams .= ' AND LOWER(CONTAINER_NO) LIKE ?';
		}


		$sql = "
			SELECT DISTINCT CONTAINER_NO AS REAL_YARD_CONT FROM TM_CONTAINER WHERE CONTAINER_BRANCH_ID = ? " . $whereParams;

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
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
		$qWhere = "";
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'DATE_REQUEST'){
							$field = 'DATE_REQUEST2';
						}
						else if($field == 'DATE_START'){
							$field = 'DATE_START2';
						}
						else if($field == 'DATE_END'){
							$field = 'DATE_END2';
						}
						else if($field == 'PAID_TRUE'){
							$field = 'PAID_TRUE2';
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


			$sql = "SELECT * FROM ( SELECT * FROM (
						SELECT TABLE_1.*, TO_CHAR(TABLE_1.DATE_START2,'DD/MM/YYYY HH24:MI') DATE_START, TO_CHAR(TABLE_1.DATE_END2,'DD/MM/YYYY HH24:MI') DATE_END, rownum AS rnum FROM (
							SELECT B.STUFF_DTL_CONT CONTAINER_NUMBER, A.STUFF_NO REQUEST_NUMBER,
						TO_CHAR(A.STUFF_CREATE_DATE,'DD/MM/YYYY HH24:MI') DATE_REQUEST, A.STUFF_CREATE_DATE DATE_REQUEST2,
						(SELECT REAL_STUFF_DATE FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = B.STUFF_DTL_CONT AND REAL_STUFF_NOREQ = A.STUFF_NO AND REAL_STUFF_BRANCH_ID = A.STUFF_BRANCH_ID AND REAL_STUFF_STATUS = '1' ) DATE_START2,
						(SELECT REAL_STUFF_DATE FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = B.STUFF_DTL_CONT AND REAL_STUFF_NOREQ = A.STUFF_NO AND REAL_STUFF_BRANCH_ID = A.STUFF_BRANCH_ID AND REAL_STUFF_STATUS = '2' ) DATE_END2,
						TO_CHAR(B.STUFF_DTL_END_STUFF_PLAN,'DD/MM/YYYY') PAID_TRUE, B.STUFF_DTL_END_STUFF_PLAN PAID_TRUE2,
	          C.CONSIGNEE_NAME EMKL
						FROM
						TX_REQ_STUFF_HDR A
						INNER JOIN TX_REQ_STUFF_DTL B ON B.STUFF_DTL_HDR_ID = A.STUFF_ID
	                    JOIN TM_CONSIGNEE C ON C.CONSIGNEE_ID = A.STUFF_CONSIGNEE_ID
						WHERE A.STUFF_STATUS <> 2 AND A.STUFF_BRANCH_ID = ? AND B.STUFF_DTL_ACTIVE = 'Y'
					) TABLE_1 WHERE 1=1 $qWhere) T
						WHERE ROWNUM <= ?
					)
					WHERE  rnum >= ? + 1";
			$data = $this->db->query($sql,$params)->result();

			$sqlTotal = "SELECT COUNT(1) TOTAL FROM (SELECT T.*, TO_CHAR(T.DATE_START2,'DD/MM/YYYY HH24:MI') DATE_START, TO_CHAR(T.DATE_END2,'DD/MM/YYYY HH24:MI') DATE_END FROM (SELECT B.STUFF_DTL_CONT CONTAINER_NUMBER, A.STUFF_NO REQUEST_NUMBER,
			TO_CHAR(A.STUFF_CREATE_DATE,'DD/MM/YYYY HH24:MI') DATE_REQUEST, A.STUFF_CREATE_DATE DATE_REQUEST2,
			(SELECT REAL_STUFF_DATE FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = B.STUFF_DTL_CONT AND REAL_STUFF_NOREQ = A.STUFF_NO AND REAL_STUFF_BRANCH_ID = A.STUFF_BRANCH_ID AND REAL_STUFF_STATUS = '1' ) DATE_START2,
			(SELECT REAL_STUFF_DATE FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = B.STUFF_DTL_CONT AND REAL_STUFF_NOREQ = A.STUFF_NO AND REAL_STUFF_BRANCH_ID = A.STUFF_BRANCH_ID AND REAL_STUFF_STATUS = '2' ) DATE_END2,
			TO_CHAR(B.STUFF_DTL_END_STUFF_PLAN,'DD/MM/YYYY') PAID_TRUE, B.STUFF_DTL_END_STUFF_PLAN PAID_TRUE2
			FROM
			TX_REQ_STUFF_HDR A
			INNER JOIN TX_REQ_STUFF_DTL B ON B.STUFF_DTL_HDR_ID = A.STUFF_ID
			WHERE A.STUFF_STATUS <> 2 AND A.STUFF_BRANCH_ID = 3 AND B.STUFF_DTL_ACTIVE = 'Y'
			) T ) WHERE 1=1 $qWhere ";

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->row_array()['TOTAL'];
		return array (
	      'data' => $data,
	      'total' => $dataTotal
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
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
		$qWhere = "";
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'DATE_REQUEST'){
							$field = 'DATE_REQUEST2';
						}
						else if($field == 'DATE_START'){
							$field = 'DATE_START2';
						}
						else if($field == 'DATE_END'){
							$field = 'DATE_END2';
						}
						else if($field == 'PAID_TRUE'){
							$field = 'PAID_TRUE2';
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


		$sql = "SELECT * FROM ( SELECT * FROM (
					SELECT TABLE_1.*, TO_CHAR(TABLE_1.DATE_START2,'DD/MM/YYYY HH24:MI') DATE_START, TO_CHAR(TABLE_1.DATE_END2,'DD/MM/YYYY HH24:MI') DATE_END, rownum AS rnum FROM (
						SELECT B.STRIP_DTL_CONT CONTAINER_NUMBER, A.STRIP_NO REQUEST_NUMBER,
					TO_CHAR(A.STRIP_CREATE_DATE,'DD/MM/YYYY HH24:MI') DATE_REQUEST, A.STRIP_CREATE_DATE DATE_REQUEST2,
					(SELECT REAL_STRIP_DATE FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = B.STRIP_DTL_CONT AND REAL_STRIP_NOREQ = A.STRIP_NO AND REAL_STRIP_BRANCH_ID = A.STRIP_BRANCH_ID AND REAL_STRIP_STATUS = '1' ) DATE_START2,
					(SELECT REAL_STRIP_DATE FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = B.STRIP_DTL_CONT AND REAL_STRIP_NOREQ = A.STRIP_NO AND REAL_STRIP_BRANCH_ID = A.STRIP_BRANCH_ID AND REAL_STRIP_STATUS = '2' ) DATE_END2,
					TO_CHAR(B.STRIP_DTL_END_STRIP_PLAN,'DD/MM/YYYY') PAID_TRUE, B.STRIP_DTL_END_STRIP_PLAN PAID_TRUE2,
          C.CONSIGNEE_NAME EMKL
					FROM
					TX_REQ_STRIP_HDR A
					INNER JOIN TX_REQ_STRIP_DTL B ON B.STRIP_DTL_HDR_ID = A.STRIP_ID
                    JOIN TM_CONSIGNEE C ON C.CONSIGNEE_ID = A.STRIP_CONSIGNEE_ID
					WHERE A.STRIP_STATUS <> 2 AND A.STRIP_BRANCH_ID = ? AND B.STRIP_DTL_ACTIVE = 'Y'
				) TABLE_1 WHERE 1=1 $qWhere) T
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1";
		$data = $this->db->query($sql,$params)->result();

		$sqlTotal = "SELECT COUNT(1) TOTAL FROM (SELECT T.*, TO_CHAR(T.DATE_START2,'DD/MM/YYYY HH24:MI') DATE_START, TO_CHAR(T.DATE_END2,'DD/MM/YYYY HH24:MI') DATE_END FROM (SELECT B.STRIP_DTL_CONT CONTAINER_NUMBER, A.STRIP_NO REQUEST_NUMBER,
		TO_CHAR(A.STRIP_CREATE_DATE,'DD/MM/YYYY HH24:MI') DATE_REQUEST, A.STRIP_CREATE_DATE DATE_REQUEST2,
		(SELECT REAL_STRIP_DATE FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = B.STRIP_DTL_CONT AND REAL_STRIP_NOREQ = A.STRIP_NO AND REAL_STRIP_BRANCH_ID = A.STRIP_BRANCH_ID AND REAL_STRIP_STATUS = '1' ) DATE_START2,
		(SELECT REAL_STRIP_DATE FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = B.STRIP_DTL_CONT AND REAL_STRIP_NOREQ = A.STRIP_NO AND REAL_STRIP_BRANCH_ID = A.STRIP_BRANCH_ID AND REAL_STRIP_STATUS = '2' ) DATE_END2,
		TO_CHAR(B.STRIP_DTL_END_STRIP_PLAN,'DD/MM/YYYY') PAID_TRUE, B.STRIP_DTL_END_STRIP_PLAN PAID_TRUE2
		FROM
		TX_REQ_STRIP_HDR A
		INNER JOIN TX_REQ_STRIP_DTL B ON B.STRIP_DTL_HDR_ID = A.STRIP_ID
		WHERE A.STRIP_STATUS <> 2 AND A.STRIP_BRANCH_ID = 3 AND B.STRIP_DTL_ACTIVE = 'Y'
		) T ) WHERE 1=1 $qWhere ";

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->row_array()['TOTAL'];
		return array (
	      'data' => $data,
	      'total' => $dataTotal
	    );
	}

	public function getStayPeriodContainer(){
		$branch_id = $this->session->USER_BRANCH;
		$yard_id = ($_REQUEST['YARD_ID'])? $_REQUEST['YARD_ID'] : 0;
		$params = array(
			'BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);

		$paramsTotal = array($this->session->USER_BRANCH);

		$qWhere = " AND YARD_ID = $yard_id ";
		//$YARD_ID = $_REQUEST['YARD_ID'] != null? $_REQUEST['YARD_ID'] : false;

		$qw = '';
		// if($YARD_ID != false && $YARD_ID != 0){
		// 	$qWhere .= " AND YPG_YARD_ID = ".$YARD_ID;
		// }

		$qs = '';
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
		// if ($filters != false){
		// 	for ($i=0;$i<count($filters);$i++){
		// 		$filter = $filters[$i];
		// 			$field = $filter->property;
		// 			$value = $filter->value;
		// 		$qs .= " AND LOWER(".$field.") LIKE '%".strtolower($value)."%'";
		//
		// 	}
		// 	$qWhere .= $qs;
		// }

		//apply filter
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

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

		$sql = "SELECT NO_CONT, PEMILIK, KAPAL, CONTAINER_SIZE, CONTAINER_TYPE, STATUS, LOKASI, KEGIATAN, TO_CHAR(START_STACK,'DD/MM/YYYY HH24:MI') START_STACK, START_STACK AS START_STACK2, DURASI_STACKING, TO_CHAR(MAX_DATE,'MM/DD/YYYY HH24:MI:SS') MAX_DATE FROM
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						SELECT * FROM VIEW_LONG_STAY_CONTAINER WHERE BRANCH = ? $qWhere
					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1";

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
		$yard_id = $this->input->get('yard_id');
		$qWhere = " AND YARD_ID = $yard_id ";

		$qw = '';

		$qs = '';
		// $filters = isset($_REQUEST)? $_REQUEST : false;
		// if ($filters != false){
		//
		// 	foreach ($filters as $key => $value){
		// 			$field = $key;
		// 			$value = $value;
		// 		$qs .= " AND LOWER(".$field.") LIKE '%".strtolower($value)."%'";
		//
		// 	}
		// 	$qWhere .= $qs;
		// }

		//apply filter
			$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($operator == 'like'){
							$qs .= " AND UPPER(".$field.") LIKE '%".strtoupper($value)."%'";
						}
						else if($operator == 'lt'){
							$qs .= " AND ".$field." < TO_DATE('".$value."','YYYY-MM-DD')";
						}
						else if($operator == 'gt'){
							$qs .= " AND ".$field." > TO_DATE('".$value."','YYYY-MM-DD')";
						}
						else if($operator == 'eq'){
							$qs .= " AND TO_DATE(TO_CHAR(".$field.",'MM/DD/YYYY'),'MM/DD/YYYY') = TO_DATE('".$value."','YYYY-MM-DD')";
						}

				}
				$qWhere .= $qs;
			}
			// end filter

		$query = $this->db->query("SELECT NO_CONT, PEMILIK, KAPAL, CONTAINER_SIZE, CONTAINER_TYPE, STATUS, LOKASI, KEGIATAN, TO_CHAR(START_STACK,'DD/MM/YYYY HH24:MI') START_STACK, DURASI_STACKING, TO_CHAR(MAX_DATE,'MM/DD/YYYY HH24:MI:SS') MAX_DATE
					FROM VIEW_LONG_STAY_CONTAINER WHERE BRANCH = $branch_id $qWhere")->result_array();

		return $query;
	}

	public function getMasterContainer($filter){
		$branch_id = $this->session->USER_BRANCH;
		$params[] = $filter[0];
		$whereParams = "";

		if($filter[1]){
			$params[] = '%' . strtolower($filter[1]) . '%';
			$whereParams .= ' AND LOWER(A.CONTAINER_NO) LIKE ?';
		}

		$sql = "SELECT A.*, B.OWNER_NAME FROM TM_CONTAINER A LEFT JOIN TM_OWNER B ON B.OWNER_CODE = A.CONTAINER_OWNER WHERE A.CONTAINER_BRANCH_ID = ? " . $whereParams;
		return $this->db->query($sql,$params)->result_array();
	}

	public function setContainerRename(){
		$this->load->library("Nusoap_library");
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$cont_old = $this->input->post('CONTAINER_NUMBER_OLD');
		$size_old = $this->input->post('CONTAINER_SIZE');
		$type_old = $this->input->post('CONTAINER_TYPE');
		$cont_new = $this->input->post('CONTAINER_NUMBER_NEW');
		$size_new = $this->input->post('SIZE_NEW');
		$type_new = $this->input->post('TYPE_NEW');

		$arrParam = json_encode(array('CONT_OLD' => $cont_old, 'CONT_NEW' => $cont_new, 'SIZE_NEW' => $size_new, 'TYPE_NEW' => $type_new));
		// echo $arrParam;


		//service
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";
    $method = 'renameContainer'; //method

    $params = array('string0'=>'npks','string1'=>'12345','string2'=> $arrParam);
    $response = $this->call_service($client, $method,$params);
		$response = json_decode($response);
		$rec = '';

		if($response->respon == 1){
				$this->db->trans_start();

				$cek_new_cont = $this->db->where('CONTAINER_NO',$cont_new)->where('CONTAINER_BRANCH_ID',$branch_id)->from('TM_CONTAINER')->count_all_results();

				if($cek_new_cont > 0){
					return array('success' => false, 'message' => 'Nomor container sudah tersedia');
					die();
				}

				// $get_max_counter = $this->db->select_max('HIST_COUNTER')->where('HIST_CONT',$cont_old)->where('HIST_BRANCH_ID',$branch_id)->get('TH_HISTORY_CONTAINER');
				$get_max_counter = $this->db->query("SELECT MAX(HIST_COUNTER) HIST_COUNTER FROM TH_HISTORY_CONTAINER WHERE HIST_CONT = '$cont_old' AND HIST_BRANCH_ID = $branch_id")->row()->HIST_COUNTER;
				$cek_hist_cont = $this->db->where('HIST_CONT',$cont_old)->where('HIST_BRANCH_ID',$branch_id)->where('HIST_COUNTER',$get_max_counter)->from('TH_HISTORY_CONTAINER')->get()->result_array();

				if(count($cek_hist_cont) > 0){
					foreach ($cek_hist_cont as $val) {
						$activity = $val['HIST_ACTIVITY_ID'];
						$activity_name = $val['HIST_ACTIVITY'];
						$rec = $val['HIST_NOREQ'];
						$cont = $val['HIST_CONT'];

						if($activity == 3 AND $activity_name == 'Request Receiving'){
								$this->db->query("UPDATE TX_REQ_RECEIVING_DTL SET REQUEST_DTL_CONT ='".$cont_new."' WHERE REQUEST_DTL_CONT = '".$cont_old."'
								AND REQUEST_HDR_ID = (SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO = '".$rec."')");
						}

						if($activity == 4 AND $activity_name == 'Request Delivery'){
								$this->db->query("UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_CONT ='".$cont_new."' WHERE REQ_DTL_CONT = '".$cont_old."'
								AND REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$rec."')");
						}

						if($activity == 1 AND $activity_name == 'GATE IN'){
								$this->db->query("UPDATE TX_GATE SET GATE_CONT ='".$cont_new."' WHERE GATE_CONT = '".$cont_old."' AND GATE_NOREQ = '".$rec."'");
								//CEK TX_CMS
								$checkCMS = $this->db->where('CMS_NOREQ',$rec)->where('CMS_CONT',$cont_new)->where('CMS_BRANCH_ID',$branch_id)->from('TX_CMS')->count_all_results();
								if($checkCMS > 0){
									$this->db->query("UPDATE TX_CMS SET CMS_CONT ='".$cont_new."' WHERE CMS_CONT = '".$cont_old."' AND CMS_NOREQ = '".$rec."' AND CMS_BRANCH_ID =".$branch_id);
								}
						}

						if($activity == 3 AND $activity_name == 'Receiving'){
								$this->db->query("UPDATE TX_REAL_YARD SET REAL_YARD_CONT ='".$cont_new."' WHERE REAL_YARD_CONT = '".$cont_old."' AND REAL_YARD_REQ_NO = '".$rec."'");
						}

						if($activity == 2 AND $activity_name == 'Request Stuffing'){
								$this->db->query("UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_CONT ='".$cont_new."' WHERE STUFF_DTL_CONT = '".$cont_old."'
								AND STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$rec."')");
						}

						if($activity == 1 AND $activity_name == 'Realisasi Stuffing'){
								$this->db->query("UPDATE TX_REAL_YARD SET REAL_YARD_CONT ='".$cont_new."' WHERE REAL_YARD_CONT = '".$cont_old."' AND REAL_YARD_REQ_NO = '".$rec."'");
						}

						if($activity == 1 AND $activity_name == 'Request Stripping'){
								$this->db->query("UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_CONT ='".$cont_new."' WHERE STRIP_DTL_CONT = '".$cont_old."'
								AND STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$rec."')");
						}

						if($activity == 2 AND $activity_name == 'Realisasi Stripping'){
								$this->db->query("UPDATE TX_REAL_YARD SET REAL_YARD_CONT ='".$cont_new."' WHERE REAL_YARD_CONT = '".$cont_old."' AND REAL_YARD_REQ_NO = '".$rec."'");
						}

						if($activity == 2 AND $activity_name == 'Relocation'){
								$this->db->query("UPDATE TX_REAL_YARD SET REAL_YARD_CONT ='".$cont_new."' WHERE REAL_YARD_CONT = '".$cont_old."' AND REAL_YARD_REQ_NO = '".$rec."'");
						}

						if($activity == 3 AND $activity_name == 'Copy Yard'){
								$this->db->query("UPDATE TX_REAL_YARD SET REAL_YARD_CONT ='".$cont_new."' WHERE REAL_YARD_CONT = '".$cont_old."' AND REAL_YARD_REQ_NO = '".$rec."'");
						}
					}

					//END LOOP
					$this->db->query("UPDATE TH_HISTORY_CONTAINER SET HIST_CONT ='".$cont_new."' WHERE HIST_CONT = '".$cont_old."' AND HIST_BRANCH_ID=".$branch_id);

					$this->db->query("UPDATE TM_CONTAINER SET CONTAINER_NO ='".$cont_new."' WHERE CONTAINER_NO = '".$cont_old."' AND CONTAINER_BRANCH_ID=".$branch_id);

					$arrCont = array(
						'RENAMED_NOREQ' => $rec,
						'RENAMED_CONT_OLD' => $cont_old,
						'RENAMED_CONT_SIZE_OLD' => $size_old,
						'RENAMED_CONT_TYPE_OLD' => $type_old,
						'RENAMED_CONT' => $cont_new,
						'RENAMED_CONT_SIZE' => $size_new,
						'RENAMED_CONT_TYPE' => $type_new,
						'RENAMED_CREATE_BY' => $user_id,
						'RENAMED_BRANCH_ID' => $branch_id
					);
					$this->db->insert('TH_RENAMED',$arrCont);

					$success = true;
					$message = $response->URresponse;
				}

				$this->db->trans_complete();

		    if ($this->db->trans_status() === FALSE)
		    {
						$success = false;
						$message = $response->URresponse;
		    }
			}
			else{
				$success = false;
				$message = $response->URresponse;
			}

			return array('success' => $success, 'message' => $message);
	}

	function call_service($url, $method, $params){
		$client = new nusoap_client($url);//alamat web service
		$error = $client->getError();//respon web service error

		if ($error) {
            return "<h2>Constructor error</h2><pre>" . $error . "</pre>";
        }

        if ($client->fault) {//web service client fault
            return "error";
        }else {
            $error = $client->getError();//web service client error
            if ($error) {
                return "<h2>Error</h2><pre>" . $error . "</pre>";
            }else {
                $result = $client->call($method, $params);//respon web service
                return $result;
            }
        }
	}

	public function getPluggingReeferHistory($filter){
		$params 		= array($filter["BRANCH_ID"]);
		$paramsTotal 	= array($filter["BRANCH_ID"]);

		$params[] 	= $_REQUEST['start'] + $_REQUEST['limit'];
		$params[] 	= $_REQUEST['start'];

		$qWhere = "";
		$whereParams = '';
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;

		//apply filter
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'DATE_REQUEST'){
							$field = 'DATE_REQUEST2';
						}
						else if($field == 'DATE_START'){
							$field = 'DATE_START2';
						}
						else if($field == 'DATE_END'){
							$field = 'DATE_END2';
						}
						else if($field == 'PAID_TRUE'){
							$field = 'PAID_TRUE2';
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
		$sql = "SELECT * FROM
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						SELECT B.PLUG_DTL_CONT CONTAINER_NUMBER, A.PLUG_NO REQUEST_NUMBER, A.PLUG_CREATE_DATE DATE_REQUEST2, TO_CHAR(A.PLUG_CREATE_DATE,'DD/MM/YYYY') DATE_REQUEST,
						STR_.REAL_PLUG_DATE AS DATE_START2, TO_CHAR(STR_.REAL_PLUG_DATE,'DD/MM/YYYY HH24:MI') AS DATE_START, END_.REAL_PLUG_DATE AS DATE_END2, TO_CHAR(END_.REAL_PLUG_DATE,'DD/MM/YYYY HH24:MI') AS DATE_END,B.PLUG_DTL_END_PLUG_PLAN PAID_TRUE2,  TO_CHAR(B.PLUG_DTL_END_PLUG_PLAN,'DD/MM/YYYY') PAID_TRUE, C.CONSIGNEE_NAME EMKL
						FROM TX_REQ_PLUG_HDR A
						INNER JOIN TX_REQ_PLUG_DTL B ON B.PLUG_DTL_HDR_ID = A.PLUG_ID
						JOIN TM_CONSIGNEE C ON C.CONSIGNEE_ID = A.PLUG_CONSIGNEE_ID
						LEFT JOIN TX_REAL_PLUG STR_ ON STR_.REAL_PLUG_CONT = B.PLUG_DTL_CONT AND STR_.REAL_PLUG_NOREQ = A.PLUG_NO AND STR_.REAL_PLUG_BRANCH_ID = A.PLUG_BRANCH_ID AND STR_.REAL_PLUG_STATUS	= '1'
						LEFT JOIN TX_REAL_PLUG END_ ON END_.REAL_PLUG_CONT = B.PLUG_DTL_CONT AND END_.REAL_PLUG_NOREQ = A.PLUG_NO AND END_.REAL_PLUG_BRANCH_ID = A.PLUG_BRANCH_ID AND END_.REAL_PLUG_STATUS	= '2'
						WHERE A.PLUG_STATUS <> '2' AND A.PLUG_BRANCH_ID = ? AND B.PLUG_DTL_ACTIVE = 'Y'
					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1" .$qWhere;

		$data = $this->db->query($sql,$params)->result();

			$sqlTotal = "SELECT * FROM(SELECT B.PLUG_DTL_CONT CONTAINER_NUMBER, A.PLUG_NO REQUEST_NUMBER, A.PLUG_CREATE_DATE DATE_REQUEST, TO_CHAR(A.PLUG_CREATE_DATE,'DD/MM/YYYY') DATE_REQUEST2,
					STR_.REAL_PLUG_DATE AS DATE_START, TO_CHAR(STR_.REAL_PLUG_DATE,'DD/MM/YYYY HH24:MI') AS DATE_START2, END_.REAL_PLUG_DATE AS DATE_END, TO_CHAR(END_.REAL_PLUG_DATE,'DD/MM/YYYY HH24:MI') AS DATE_END2,B.PLUG_DTL_END_PLUG_PLAN PAID_TRUE,  TO_CHAR(B.PLUG_DTL_END_PLUG_PLAN,'DD/MM/YYYY') PAID_TRUE2, C.CONSIGNEE_NAME EMKL
					FROM TX_REQ_PLUG_HDR A
					INNER JOIN TX_REQ_PLUG_DTL B ON B.PLUG_DTL_HDR_ID = A.PLUG_ID
					JOIN TM_CONSIGNEE C ON C.CONSIGNEE_ID = A.PLUG_CONSIGNEE_ID
					LEFT JOIN TX_REAL_PLUG STR_ ON STR_.REAL_PLUG_CONT = B.PLUG_DTL_CONT AND STR_.REAL_PLUG_NOREQ = A.PLUG_NO AND STR_.REAL_PLUG_BRANCH_ID = A.PLUG_BRANCH_ID AND STR_.REAL_PLUG_STATUS	= '1'
					LEFT JOIN TX_REAL_PLUG END_ ON END_.REAL_PLUG_CONT = B.PLUG_DTL_CONT AND END_.REAL_PLUG_NOREQ = A.PLUG_NO AND END_.REAL_PLUG_BRANCH_ID = A.PLUG_BRANCH_ID AND END_.REAL_PLUG_STATUS	= '2'
					WHERE A.PLUG_STATUS <> '2' AND A.PLUG_BRANCH_ID = ? AND B.PLUG_DTL_ACTIVE = 'Y'
					) WHERE 1=1 " . $qWhere;

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->result();
		return array (
				'data' => $data,
				'total' => count($dataTotal)
			);
	}

}
