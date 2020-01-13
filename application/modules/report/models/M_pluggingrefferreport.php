<?php
class M_pluggingrefferreport extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

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
		// $activity = $_REQUEST['activity'] != null? $_REQUEST['activity'] : false;
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
		$tgl1 = $_REQUEST['date1'] != null? substr($_REQUEST['date1'],0,-9) : date('Y-m-d');
		$tgl2 = $_REQUEST['date2'] != null? substr($_REQUEST['date2'],0,-9) : date('Y-m-d');

		$qw = '';
		// if($activity != false && $activity != 0){
		// 	$qWhere .= " AND ACTIVITY_CODE =".$activity;
		// }

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

		$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM ( SELECT * FROM VIEW_PLUGGING_REPORT ) T
		WHERE BRANCH_ID = $branch_id $qWhere AND ROWNUM <= $end ORDER BY TGL_REQ DESC) F WHERE r >= $start + 1";
		$data = $this->db->query($query,$params)->result_array();

		$count = $this->db->query("SELECT COUNT(1) TOTAL FROM VIEW_PLUGGING_REPORT WHERE BRANCH_ID = $branch_id $qWhere")->row_array()['TOTAL'];

		return array (
			'data' => $data,
			'total' => $count
		);
	}

  function getExport(){
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

		$query = $this->db->query("SELECT * FROM VIEW_PLUGGING_REPORT WHERE BRANCH_ID = $branch_id $qWhere")->result_array();

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
