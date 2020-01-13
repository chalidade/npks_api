<?php
class M_stuffingstrippingreport extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

  // public function getReport(){
  //   $branch_id = $this->session->USER_BRANCH;
  //   $params = array('BRANCH_ID' => $branch_id);
  //   $sql = "SELECT * FROM(
  //           SELECT A.REAL_STUFF_CONT CONT, C.STUFF_DTL_CONT_SIZE SIZE_, C.STUFF_DTL_CONT_TYPE TYPE_, A.REAL_STUFF_NOREQ REQ, TO_CHAR(B.STUFF_CREATE_DATE,'DD/MM/YYYY HH24:MI:SS') TGL_REQ, TO_CHAR(A.REAL_STUFF_DATE,'DD/MM/YYYY HH24:MI:SS') AS STARTNYA,
  //           TO_CHAR((SELECT REAL_STUFF_DATE FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = A.REAL_STUFF_CONT AND REAL_STUFF_NOREQ = A.REAL_STUFF_NOREQ AND REAL_STUFF_STATUS = 2 ),'DD/MM/YYYY HH24:MI:SS') ENDNYA,
  //           ROUND(((SELECT REAL_STUFF_DATE FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = A.REAL_STUFF_CONT AND REAL_STUFF_NOREQ = A.REAL_STUFF_NOREQ AND REAL_STUFF_STATUS = 2 ) - A.REAL_STUFF_DATE),2) DURASI,
  //           B.STUFF_NOREQ_RECEIVING REQ_RECEIVING, D.CONSIGNEE_NAME CONSIGNEE,
  //           (CASE
  //           WHEN
  //           	C.STUFF_DTL_ORIGIN = 'TPK'
  //           THEN
  //           	(SELECT AA.REAL_YARD_YBC_ID FROM TX_REAL_YARD AA WHERE AA.REAL_YARD_CONT = A.REAL_STUFF_CONT AND AA.REAL_YARD_REQ_NO = B.STUFF_NOREQ_RECEIVING )
  //           ELSE
  //           	(
  //           	SELECT BB.YBC_BLOCK_ID
  //           	FROM TX_REAL_YARD AA
  //           	INNER JOIN TX_YARD_BLOCK_CELL BB ON BB.YBC_ID = AA.REAL_YARD_YBC_ID
  //           	WHERE AA.REAL_YARD_CONT = A.REAL_STUFF_CONT
  //           	AND AA.REAL_YARD_REQ_NO = A.REAL_STUFF_NOREQ
  //           	AND AA.REAL_YARD_ACTIVITY = 2
  //           	)
  //           END) BLOCK_ID,
  //           (CASE
  //           WHEN
  //           	C.STUFF_DTL_ORIGIN = 'TPK'
  //           THEN
  //           	(SELECT AA.REAL_YARD_YBC_ID FROM TX_REAL_YARD AA WHERE AA.REAL_YARD_CONT = A.REAL_STUFF_CONT AND AA.REAL_YARD_REQ_NO = B.STUFF_NOREQ_RECEIVING )
  //           ELSE
  //           	(
  //           	SELECT BB.YBC_SLOT
  //           	FROM TX_REAL_YARD AA
  //           	INNER JOIN TX_YARD_BLOCK_CELL BB ON BB.YBC_ID = AA.REAL_YARD_YBC_ID
  //           	WHERE AA.REAL_YARD_CONT = A.REAL_STUFF_CONT
  //           	AND AA.REAL_YARD_REQ_NO = A.REAL_STUFF_NOREQ
  //           	AND AA.REAL_YARD_ACTIVITY = 2
  //           	)
  //           END) SLOT_ID,
  //           (CASE
  //           WHEN
  //           	C.STUFF_DTL_ORIGIN = 'TPK'
  //           THEN
  //           	(SELECT AA.REAL_YARD_YBC_ID FROM TX_REAL_YARD AA WHERE AA.REAL_YARD_CONT = A.REAL_STUFF_CONT AND AA.REAL_YARD_REQ_NO = B.STUFF_NOREQ_RECEIVING )
  //           ELSE
  //           	(
  //           	SELECT BB.YBC_ROW
  //           	FROM TX_REAL_YARD AA
  //           	INNER JOIN TX_YARD_BLOCK_CELL BB ON BB.YBC_ID = AA.REAL_YARD_YBC_ID
  //           	WHERE AA.REAL_YARD_CONT = A.REAL_STUFF_CONT
  //           	AND AA.REAL_YARD_REQ_NO = A.REAL_STUFF_NOREQ
  //           	AND AA.REAL_YARD_ACTIVITY = 2
  //           	)
  //           END) ROW_ID,
  //           (CASE
  //           WHEN
  //           	C.STUFF_DTL_ORIGIN = 'TPK'
  //           THEN
  //           	(SELECT AA.REAL_YARD_YBC_ID FROM TX_REAL_YARD AA WHERE AA.REAL_YARD_CONT = A.REAL_STUFF_CONT AND AA.REAL_YARD_REQ_NO = B.STUFF_NOREQ_RECEIVING )
  //           ELSE
  //           	(
  //           	SELECT AA.REAL_YARD_TIER
  //           	FROM TX_REAL_YARD AA
  //           	INNER JOIN TX_YARD_BLOCK_CELL BB ON BB.YBC_ID = AA.REAL_YARD_YBC_ID
  //           	WHERE AA.REAL_YARD_CONT = A.REAL_STUFF_CONT
  //           	AND AA.REAL_YARD_REQ_NO = A.REAL_STUFF_NOREQ
  //           	AND AA.REAL_YARD_ACTIVITY = 2
  //           	)
  //           END) TIER_ID, A.REAL_STUFF_BRANCH_ID BRANCH_ID, 'STUFFING' ACTIVITY
  //           FROM TX_REAL_STUFF A
  //           INNER JOIN TX_REQ_STUFF_HDR B ON B.STUFF_NO = A.REAL_STUFF_NOREQ
  //           INNER JOIN TX_REQ_STUFF_DTL C ON C.STUFF_DTL_HDR_ID = B.STUFF_ID
  //           JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = B.STUFF_CONSIGNEE_ID
  //           WHERE A.REAL_STUFF_STATUS = 1
  //
  //           UNION ALL
  //
  //           SELECT A.REAL_STRIP_CONT CONT, C.STRIP_DTL_CONT_SIZE SIZE_, C.STRIP_DTL_CONT_TYPE TYPE_, A.REAL_STRIP_NOREQ REQ, TO_CHAR(B.STRIP_CREATE_DATE,'DD/MM/YYYY HH24:MI:SS') TGL_REQ, TO_CHAR(A.REAL_STRIP_DATE,'DD/MM/YYYY HH24:MI:SS') AS STARTNYA,
  //           TO_CHAR((SELECT REAL_STRIP_DATE FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = A.REAL_STRIP_CONT AND REAL_STRIP_NOREQ = A.REAL_STRIP_NOREQ AND REAL_STRIP_STATUS = 2 ),'DD/MM/YYYY HH24:MI:SS') ENDNYA,
  //           ROUND(((SELECT REAL_STRIP_DATE FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = A.REAL_STRIP_CONT AND REAL_STRIP_NOREQ = A.REAL_STRIP_NOREQ AND REAL_STRIP_STATUS = 2 ) - A.REAL_STRIP_DATE),2) DURASI,
  //           B.STRIP_NOREQ_RECEIVING REQ_RECEIVING, D.CONSIGNEE_NAME CONSIGNEE,
  //           (CASE
  //           WHEN
  //           	C.STRIP_DTL_ORIGIN = 'TPK'
  //           THEN
  //           	(SELECT AA.REAL_YARD_YBC_ID FROM TX_REAL_YARD AA WHERE AA.REAL_YARD_CONT = A.REAL_STRIP_CONT AND AA.REAL_YARD_REQ_NO = B.STRIP_NOREQ_RECEIVING )
  //           ELSE
  //           	(
  //           	SELECT BB.YBC_BLOCK_ID
  //           	FROM TX_REAL_YARD AA
  //           	INNER JOIN TX_YARD_BLOCK_CELL BB ON BB.YBC_ID = AA.REAL_YARD_YBC_ID
  //           	WHERE AA.REAL_YARD_CONT = A.REAL_STRIP_CONT
  //           	AND AA.REAL_YARD_REQ_NO = A.REAL_STRIP_NOREQ
  //           	AND AA.REAL_YARD_ACTIVITY = 2
  //           	)
  //           END) BLOCK_ID,
  //           (CASE
  //           WHEN
  //           	C.STRIP_DTL_ORIGIN = 'TPK'
  //           THEN
  //           	(SELECT AA.REAL_YARD_YBC_ID FROM TX_REAL_YARD AA WHERE AA.REAL_YARD_CONT = A.REAL_STRIP_CONT AND AA.REAL_YARD_REQ_NO = B.STRIP_NOREQ_RECEIVING )
  //           ELSE
  //           	(
  //           	SELECT BB.YBC_SLOT
  //           	FROM TX_REAL_YARD AA
  //           	INNER JOIN TX_YARD_BLOCK_CELL BB ON BB.YBC_ID = AA.REAL_YARD_YBC_ID
  //           	WHERE AA.REAL_YARD_CONT = A.REAL_STRIP_CONT
  //           	AND AA.REAL_YARD_REQ_NO = A.REAL_STRIP_NOREQ
  //           	AND AA.REAL_YARD_ACTIVITY = 2
  //           	)
  //           END) SLOT_ID,
  //           (CASE
  //           WHEN
  //           	C.STRIP_DTL_ORIGIN = 'TPK'
  //           THEN
  //           	(SELECT AA.REAL_YARD_YBC_ID FROM TX_REAL_YARD AA WHERE AA.REAL_YARD_CONT = A.REAL_STRIP_CONT AND AA.REAL_YARD_REQ_NO = B.STRIP_NOREQ_RECEIVING )
  //           ELSE
  //           	(
  //           	SELECT BB.YBC_ROW
  //           	FROM TX_REAL_YARD AA
  //           	INNER JOIN TX_YARD_BLOCK_CELL BB ON BB.YBC_ID = AA.REAL_YARD_YBC_ID
  //           	WHERE AA.REAL_YARD_CONT = A.REAL_STRIP_CONT
  //           	AND AA.REAL_YARD_REQ_NO = A.REAL_STRIP_NOREQ
  //           	AND AA.REAL_YARD_ACTIVITY = 2
  //           	)
  //           END) ROW_ID,
  //           (CASE
  //           WHEN
  //           	C.STRIP_DTL_ORIGIN = 'TPK'
  //           THEN
  //           	(SELECT AA.REAL_YARD_YBC_ID FROM TX_REAL_YARD AA WHERE AA.REAL_YARD_CONT = A.REAL_STRIP_CONT AND AA.REAL_YARD_REQ_NO = B.STRIP_NOREQ_RECEIVING )
  //           ELSE
  //           	(
  //           	SELECT AA.REAL_YARD_TIER
  //           	FROM TX_REAL_YARD AA
  //           	INNER JOIN TX_YARD_BLOCK_CELL BB ON BB.YBC_ID = AA.REAL_YARD_YBC_ID
  //           	WHERE AA.REAL_YARD_CONT = A.REAL_STRIP_CONT
  //           	AND AA.REAL_YARD_REQ_NO = A.REAL_STRIP_NOREQ
  //           	AND AA.REAL_YARD_ACTIVITY = 2
  //           	)
  //           END) TIER_ID, A.REAL_STRIP_BRANCH_ID BRANCH_ID, 'STRIPPING' ACTIVITY
  //           FROM TX_REAL_STRIP A
  //           INNER JOIN TX_REQ_STRIP_HDR B ON B.STRIP_NO = A.REAL_STRIP_NOREQ
  //           INNER JOIN TX_REQ_STRIP_DTL C ON C.STRIP_DTL_HDR_ID = B.STRIP_ID
  //           JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = B.STRIP_CONSIGNEE_ID
  //           WHERE A.REAL_STRIP_STATUS = 1
  //           ) R
  //           WHERE R.BRANCH_ID = ?
  //           ORDER BY R.TGL_REQ DESC";
  //   $data = $this->db->query($sql,$params)->result_array();
  //   return $data;
  // }

  function getReport(){
		$branch_id = $this->session->USER_BRANCH;
		$params = array(
			'BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);

		$end = $_REQUEST['start'] + $_REQUEST['limit'];
		$start = $_REQUEST['start'];

		$qWhere = "";
		$activity = $_REQUEST['activity'] != null? $_REQUEST['activity'] : false;
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
		$tgl1 = $_REQUEST['date1'] != null? substr($_REQUEST['date1'],0,-9) : date('Y-m-d');
		$tgl2 = $_REQUEST['date2'] != null? substr($_REQUEST['date2'],0,-9) : date('Y-m-d');

		$qw = '';
		if($activity != false && $activity != 0){
			$qWhere .= " AND ACTIVITY_CODE =".$activity;
		}

		$qWhere .=" AND TO_DATE(TO_CHAR(START_DATE,'YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN TO_DATE('".$tgl1."','YYYY-MM-DD') AND TO_DATE('".$tgl2."','YYYY-MM-DD')";

		//apply filter
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
						else if($field == 'STARTNYA2'){
							$field = 'STARTNYA';
						}
						else if($field == 'ENDNYA2'){
							$field = 'ENDNYA';
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

		// 	$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (SELECT * FROM (SELECT R.*,
		// 					FUNC_GET_LOC_STAFF_STRIPP(R.REQ,R.CONT,R.BRANCH_ID,1,'Realisasi Stuffing') LOKASI
		// 					FROM (SELECT DISTINCT A.REAL_STUFF_CONT CONT, C.STUFF_DTL_CONT_SIZE SIZE_, C.STUFF_DTL_CONT_TYPE TYPE_, A.REAL_STUFF_NOREQ REQ, TO_CHAR(B.STUFF_CREATE_DATE,'DD/MM/YYYY HH24:MI:SS') TGL_REQ, TO_CHAR(A.REAL_STUFF_DATE,'DD/MM/YYYY HH24:MI:SS') AS STARTNYA,
		// 					TO_CHAR((SELECT REAL_STUFF_DATE FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = A.REAL_STUFF_CONT AND REAL_STUFF_NOREQ = A.REAL_STUFF_NOREQ AND REAL_STUFF_STATUS = 2 ),'DD/MM/YYYY HH24:MI:SS') ENDNYA,
		// 					CEIL(((SELECT REAL_STUFF_DATE FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = A.REAL_STUFF_CONT AND REAL_STUFF_NOREQ = A.REAL_STUFF_NOREQ AND REAL_STUFF_STATUS = 2 ) - A.REAL_STUFF_DATE)) DURASI,
		// 					B.STUFF_NOREQ_RECEIVING REQ_RECEIVING, D.CONSIGNEE_NAME CONSIGNEE, A.REAL_STUFF_DATE START_DATE,
		// 					A.REAL_STUFF_BRANCH_ID BRANCH_ID, 'STUFFING' ACTIVITY, 2 ACTIVITY_CODE
		// 					FROM TX_REAL_STUFF A
		// 					INNER JOIN TX_REQ_STUFF_HDR B ON B.STUFF_NO = A.REAL_STUFF_NOREQ
		// 					INNER JOIN TX_REQ_STUFF_DTL C ON C.STUFF_DTL_HDR_ID = B.STUFF_ID
		// 					JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = B.STUFF_CONSIGNEE_ID
		// 					WHERE A.REAL_STUFF_STATUS = 1) R
		//
		// 					UNION ALL
		//
		// 					SELECT Z.*,
		// 					FUNC_GET_LOC_STAFF_STRIPP(Z.REQ,Z.CONT,Z.BRANCH_ID,2,'Realisasi Stripping') LOKASI
		// 					FROM (SELECT A.REAL_STRIP_CONT CONT, C.STRIP_DTL_CONT_SIZE SIZE_, C.STRIP_DTL_CONT_TYPE TYPE_, A.REAL_STRIP_NOREQ REQ, TO_CHAR(B.STRIP_CREATE_DATE,'DD/MM/YYYY HH24:MI:SS') TGL_REQ, TO_CHAR(A.REAL_STRIP_DATE,'DD/MM/YYYY HH24:MI:SS') AS STARTNYA,
		// 					TO_CHAR((SELECT REAL_STRIP_DATE FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = A.REAL_STRIP_CONT AND REAL_STRIP_NOREQ = A.REAL_STRIP_NOREQ AND REAL_STRIP_STATUS = 2 ),'DD/MM/YYYY HH24:MI:SS') ENDNYA,
		// 					CEIL(((SELECT REAL_STRIP_DATE FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = A.REAL_STRIP_CONT AND REAL_STRIP_NOREQ = A.REAL_STRIP_NOREQ AND REAL_STRIP_STATUS = 2 ) - A.REAL_STRIP_DATE)) DURASI,
		// 					B.STRIP_NOREQ_RECEIVING REQ_RECEIVING, D.CONSIGNEE_NAME CONSIGNEE, A.REAL_STRIP_DATE START_DATE,
		// 					A.REAL_STRIP_BRANCH_ID BRANCH_ID, 'STRIPPING' ACTIVITY, 1 ACTIVITY_CODE
		// 					FROM TX_REAL_STRIP A
		// 					INNER JOIN TX_REQ_STRIP_HDR B ON B.STRIP_NO = A.REAL_STRIP_NOREQ
		// 					INNER JOIN TX_REQ_STRIP_DTL C ON C.STRIP_DTL_HDR_ID = B.STRIP_ID
		// 					JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = B.STRIP_CONSIGNEE_ID
		// 					WHERE A.REAL_STRIP_STATUS = 1) Z
		// 					) X
    //           WHERE X.BRANCH_ID = $branch_id $qWhere
    //           ORDER BY X.TGL_REQ DESC) T
		// 							WHERE ROWNUM <= $end) F
		// 								WHERE r >= $start + 1";
		// $data = $this->db->query($query,$params)->result_array();

		$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM ( SELECT * FROM VIEW_STUFF_STRIP_REPORT ) T
		WHERE BRANCH_ID = $branch_id $qWhere AND ROWNUM <= $end ORDER BY TGL_REQ DESC) F WHERE r >= $start + 1";
		$data = $this->db->query($query,$params)->result_array();

		$count = $this->db->query("SELECT COUNT(1) TOTAL FROM VIEW_STUFF_STRIP_REPORT WHERE BRANCH_ID = $branch_id $qWhere")->row_array()['TOTAL'];

		return array (
			'data' => $data,
			'total' => $count
		);
	}

  function getStuffStrippExport(){
		$branch_id = $this->session->USER_BRANCH;

		$qWhere = "";
		$activity = $_GET['activity'] != null? $_GET['activity'] : false;
		$tgl1 = $_GET['date1'] != null? $_GET['date1'] : date('Y-m-j');
		$tgl2 = $_GET['date2'] != null? $_GET['date2'] : date('Y-m-j');
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;

		$qw = '';
		if($activity != false && $activity != 0){
			$qWhere .= " AND ACTIVITY_CODE =".$activity;
		}

		$qWhere .=" AND TO_DATE(TO_CHAR(START_DATE,'YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN TO_DATE('".$tgl1."','YYYY-MM-DD') AND TO_DATE('".$tgl2."','YYYY-MM-DD')";

    // $qs = '';
    // $filters = isset($_REQUEST)? $_REQUEST : false;
    // if ($filters != false){
		//
    //   foreach ($filters as $key => $value){
    //       $field = $key;
    //       $value = $value;
		//
    //     if($field != 'activity' && $field != 'date1'  && $field != 'date2'){
    //       $qs .= " AND LOWER(".$field.") LIKE '%".strtolower($value)."%'";
    //     }
		//
		//
    //   }
    //   $qWhere .= $qs;
    // }

		//apply filter
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
						else if($field == 'STARTNYA2'){
							$field = 'STARTNYA';
						}
						else if($field == 'ENDNYA2'){
							$field = 'ENDNYA';
						}

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

		// $query = $this->db->query("SELECT * FROM (SELECT R.*,
		// 				FUNC_GET_LOC_STAFF_STRIPP(R.REQ,R.CONT,R.BRANCH_ID,1,'Realisasi Stuffing') LOKASI
		// 				 FROM (SELECT DISTINCT A.REAL_STUFF_CONT CONT, C.STUFF_DTL_CONT_SIZE SIZE_, C.STUFF_DTL_CONT_TYPE TYPE_, A.REAL_STUFF_NOREQ REQ, TO_CHAR(B.STUFF_CREATE_DATE,'DD/MM/YYYY HH24:MI:SS') TGL_REQ, TO_CHAR(A.REAL_STUFF_DATE,'DD/MM/YYYY HH24:MI:SS') AS STARTNYA,
		// 				TO_CHAR((SELECT REAL_STUFF_DATE FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = A.REAL_STUFF_CONT AND REAL_STUFF_NOREQ = A.REAL_STUFF_NOREQ AND REAL_STUFF_STATUS = 2 ),'DD/MM/YYYY HH24:MI:SS') ENDNYA,
		// 				CEIL(((SELECT REAL_STUFF_DATE FROM TX_REAL_STUFF WHERE REAL_STUFF_CONT = A.REAL_STUFF_CONT AND REAL_STUFF_NOREQ = A.REAL_STUFF_NOREQ AND REAL_STUFF_STATUS = 2 ) - A.REAL_STUFF_DATE)) DURASI,
		// 				B.STUFF_NOREQ_RECEIVING REQ_RECEIVING, D.CONSIGNEE_NAME CONSIGNEE, A.REAL_STUFF_DATE START_DATE,
		// 				A.REAL_STUFF_BRANCH_ID BRANCH_ID, 'STUFFING' ACTIVITY, 2 ACTIVITY_CODE
		// 				FROM TX_REAL_STUFF A
		// 				INNER JOIN TX_REQ_STUFF_HDR B ON B.STUFF_NO = A.REAL_STUFF_NOREQ
		// 				INNER JOIN TX_REQ_STUFF_DTL C ON C.STUFF_DTL_HDR_ID = B.STUFF_ID
		// 				JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = B.STUFF_CONSIGNEE_ID
		// 				WHERE A.REAL_STUFF_STATUS = 1) R
		//
		// 				UNION ALL
		//
		// 				SELECT Z.*,
		// 				FUNC_GET_LOC_STAFF_STRIPP(Z.REQ,Z.CONT,Z.BRANCH_ID,2,'Realisasi Stripping') LOKASI
		// 				 FROM (SELECT A.REAL_STRIP_CONT CONT, C.STRIP_DTL_CONT_SIZE SIZE_, C.STRIP_DTL_CONT_TYPE TYPE_, A.REAL_STRIP_NOREQ REQ, TO_CHAR(B.STRIP_CREATE_DATE,'DD/MM/YYYY HH24:MI:SS') TGL_REQ, TO_CHAR(A.REAL_STRIP_DATE,'DD/MM/YYYY HH24:MI:SS') AS STARTNYA,
		// 				TO_CHAR((SELECT REAL_STRIP_DATE FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = A.REAL_STRIP_CONT AND REAL_STRIP_NOREQ = A.REAL_STRIP_NOREQ AND REAL_STRIP_STATUS = 2 ),'DD/MM/YYYY HH24:MI:SS') ENDNYA,
		// 				CEIL(((SELECT REAL_STRIP_DATE FROM TX_REAL_STRIP WHERE REAL_STRIP_CONT = A.REAL_STRIP_CONT AND REAL_STRIP_NOREQ = A.REAL_STRIP_NOREQ AND REAL_STRIP_STATUS = 2 ) - A.REAL_STRIP_DATE)) DURASI,
		// 				B.STRIP_NOREQ_RECEIVING REQ_RECEIVING, D.CONSIGNEE_NAME CONSIGNEE, A.REAL_STRIP_DATE START_DATE,
		// 				A.REAL_STRIP_BRANCH_ID BRANCH_ID, 'STRIPPING' ACTIVITY, 1 ACTIVITY_CODE
		// 				FROM TX_REAL_STRIP A
		// 				INNER JOIN TX_REQ_STRIP_HDR B ON B.STRIP_NO = A.REAL_STRIP_NOREQ
		// 				INNER JOIN TX_REQ_STRIP_DTL C ON C.STRIP_DTL_HDR_ID = B.STRIP_ID
		// 				JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = B.STRIP_CONSIGNEE_ID
		// 				WHERE A.REAL_STRIP_STATUS = 1) Z
		// 				) X
    //         WHERE X.BRANCH_ID = $branch_id $qWhere
    //         ORDER BY X.TGL_REQ DESC")->result_array();

		$query = $this->db->query("SELECT * FROM VIEW_STUFF_STRIP_REPORT WHERE BRANCH_ID = $branch_id $qWhere")->result_array();

		return $query;
	}

  function getReportActivity($activity){
    $sql = "SELECT A.REFF_ID, A.REFF_NAME, A.REFF_ORDER FROM TM_REFF A
      JOIN TR_REFF B ON A.REFF_TR_ID = B.REFF_ID
          WHERE B.REFF_ID = 22 AND A.REFF_ID = ".$activity;
    $data = $this->db->query($sql)->row_array()['REFF_NAME'];
    return $data;
  }

}
