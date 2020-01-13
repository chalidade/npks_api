<?php
class M_shufflingReport extends CI_Model {
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
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
		$tgl1 = $_REQUEST['date1'] != null? substr($_REQUEST['date1'],0,-9) : date('Y-m-d');
		$tgl2 = $_REQUEST['date2'] != null? substr($_REQUEST['date2'],0,-9) : date('Y-m-d');

		$qw = '';
		$qWhere .=" AND TO_DATE(TO_CHAR(DATE_,'YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN TO_DATE('".$tgl1."','YYYY-MM-DD') AND TO_DATE('".$tgl2."','YYYY-MM-DD')";

		//apply filter
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'DATE2'){
							$field = 'DATE_';
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

		$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM VIEW_SHUFFLING_HISTORY T
		WHERE BRANCH = $branch_id $qWhere AND ROWNUM <= $end ORDER BY HIST_DATE DESC) F WHERE r >= $start + 1";
		$data = $this->db->query($query,$params)->result_array();

		$count = $this->db->query("SELECT COUNT(1) TOTAL FROM VIEW_SHUFFLING_HISTORY WHERE BRANCH = $branch_id $qWhere")->row_array()['TOTAL'];

		return array (
			'data' => $data,
			'total' => $count
		);
	}

  function getReportExport(){
		$branch_id = $this->session->USER_BRANCH;

		$qWhere = "";
		$tgl1 = $_GET['date1'] != null? $_GET['date1'] : date('Y-m-j');
		$tgl2 = $_GET['date2'] != null? $_GET['date2'] : date('Y-m-j');
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;

		$qw = '';

		$qWhere .=" AND TO_DATE(TO_CHAR(HIST_DATE,'YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN TO_DATE('".$tgl1."','YYYY-MM-DD') AND TO_DATE('".$tgl2."','YYYY-MM-DD')";

		//apply filter
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'HIST_DATE_FORMAT'){
							$field = 'HIST_DATE';
						}
						if($field == 'CONT_BEFORE'){
							$field = 'HIST_CONT';
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

		$data = $this->db->query("SELECT ROWNUM ID, A.* FROM (SELECT B.HIST_ACTIVITY_ID, B.HIST_ACTIVITY, HIST_CONT, C.CONTAINER_SIZE CONT_SIZE, C.CONTAINER_TYPE CONT_TYPE, B.HIST_CONT_STATUS CONT_STATUS, HIST_BRANCH_ID, HIST_COUNTER, HIST_NOREQ,  HIST_BLOCK || '/' || HIST_SLOT || '/' || HIST_ROW || '/' || HIST_TIER AS LOCATION, HIST_DATE, TO_CHAR(HIST_DATE,'DD/MM/YYYY HH24:MI') HIST_DATE_FORMAT, D.FULL_NAME USER_NAME
				FROM TH_HISTORY_CONTAINER B 
				JOIN TM_CONTAINER C ON C.CONTAINER_NO = B.HIST_CONT
				JOIN TM_USER D ON D.USER_ID = B.HIST_USER
				WHERE HIST_BRANCH_ID = $branch_id AND HIST_ACTIVITY IN ('Placement','Shuffling') $qWhere
				ORDER BY HIST_CONT, HIST_COUNTER, HIST_DATE) A")->result_array();
		
	$arrData = array();
    $z = 0;
    $t = 0;
    $total = count($data);
    for ($i=0; $i < $total+1; $i++) {
      
      if((int)@$data[$i]['ID'] % 2 != 0 ){
          if($i == 0){
            $arrData[$z]['ID_BEFORE'] = $data[$i]['ID'];
            $arrData[$z]['CONT_BEFORE'] = $data[$i]['HIST_CONT'];
            $arrData[$z]['CONT_SIZE'] = $data[$i]['CONT_SIZE'];
            $arrData[$z]['CONT_TYPE'] = $data[$i]['CONT_TYPE'];
            $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i]['CONT_STATUS'];
            $arrData[$z]['LOCATION_BEFORE'] = $data[$i]['LOCATION'];
            $arrData[$z]['COUNTER_BEFORE'] = $data[$i]['HIST_COUNTER'];
            $arrData[$z]['DATE_BEFORE'] = $data[$i]['HIST_DATE_FORMAT'];
          }
          else{
              if($data[$t-1]['HIST_CONT'] == $data[$i]['HIST_CONT']){
                $arrData[$z]['ID_BEFORE'] = $data[$i-1]['ID'];
                $arrData[$z]['CONT_BEFORE'] = $data[$i-1]['HIST_CONT'];
                $arrData[$z]['CONT_SIZE'] = $data[$i-1]['CONT_SIZE'];
                $arrData[$z]['CONT_TYPE'] = $data[$i-1]['CONT_TYPE'];
                $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i-1]['CONT_STATUS'];
                $arrData[$z]['LOCATION_BEFORE'] = $data[$i-1]['LOCATION'];
                $arrData[$z]['COUNTER_BEFORE'] = $data[$i-1]['HIST_COUNTER'];
                $arrData[$z]['DATE_BEFORE'] = $data[$i-1]['HIST_DATE_FORMAT'];

                if($z == $total-1){
                  $arrData[$z]['ID_AFTER'] = $data[$i]['ID'];
                  $arrData[$z]['CONT_AFTER'] = $data[$i]['HIST_CONT'];
                  $arrData[$z]['CONT_STATUS_AFTER'] = $data[$i]['CONT_STATUS'];
                  $arrData[$z]['LOCATION_AFTER'] = $data[$i]['LOCATION'];
                  $arrData[$z]['COUNTER_AFTER'] = $data[$i]['HIST_COUNTER'];
                  $arrData[$z]['DATE_AFTER'] = $data[$i]['HIST_DATE_FORMAT'];
                  $arrData[$z]['USER'] = $data[$i]['USER_NAME'];
                }

              }
              else{
                $arrData[$z]['ID_BEFORE'] = $data[$i]['ID'];
                $arrData[$z]['CONT_BEFORE'] = $data[$i]['HIST_CONT'];
                $arrData[$z]['CONT_SIZE'] = $data[$i]['CONT_SIZE'];
                $arrData[$z]['CONT_TYPE'] = $data[$i]['CONT_TYPE'];
                $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i]['CONT_STATUS'];
                $arrData[$z]['LOCATION_BEFORE'] = $data[$i]['LOCATION'];
                $arrData[$z]['COUNTER_BEFORE'] = $data[$i]['HIST_COUNTER'];
                $arrData[$z]['DATE_BEFORE'] = $data[$i]['HIST_DATE_FORMAT'];
              }
          }
      }
      else{
        if(@$data[$t-1]['HIST_CONT'] == @$data[$i]['HIST_CONT']){
  
          $arrData[$z]['ID_AFTER'] = $data[$i-1]['ID'];
          $arrData[$z]['CONT_AFTER'] = $data[$i-1]['HIST_CONT'];
          $arrData[$z]['CONT_STATUS_AFTER'] = $data[$i-1]['CONT_STATUS'];
          $arrData[$z]['LOCATION_AFTER'] = $data[$i-1]['LOCATION'];
          $arrData[$z]['COUNTER_AFTER'] = $data[$i-1]['HIST_COUNTER'];
          $arrData[$z]['DATE_AFTER'] = $data[$i-1]['HIST_DATE_FORMAT'];
          $arrData[$z]['USER'] = $data[$i-1]['USER_NAME'];
          $z++;

          $arrData[$z]['ID_BEFORE'] = $data[$i-1]['ID'];
          $arrData[$z]['CONT_BEFORE'] = $data[$i-1]['HIST_CONT'];
          $arrData[$z]['CONT_SIZE'] = $data[$i-1]['CONT_SIZE'];
          $arrData[$z]['CONT_TYPE'] = $data[$i-1]['CONT_TYPE'];
          $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i-1]['CONT_STATUS'];
          $arrData[$z]['LOCATION_BEFORE'] = $data[$i-1]['LOCATION'];
          $arrData[$z]['COUNTER_BEFORE'] = $data[$i-1]['HIST_COUNTER'];
          $arrData[$z]['DATE_BEFORE'] = $data[$i-1]['HIST_DATE_FORMAT'];
          
          $arrData[$z]['ID_AFTER'] = $data[$i]['ID'];
          $arrData[$z]['CONT_AFTER'] = $data[$i]['HIST_CONT'];
          $arrData[$z]['CONT_STATUS_AFTER'] = $data[$i]['CONT_STATUS'];
          $arrData[$z]['LOCATION_AFTER'] = $data[$i]['LOCATION'];
          $arrData[$z]['COUNTER_AFTER'] = $data[$i]['HIST_COUNTER'];
          $arrData[$z]['DATE_AFTER'] = $data[$i]['HIST_DATE_FORMAT'];
          $arrData[$z]['USER'] = $data[$i]['USER_NAME'];
          $z++;

        }
        else{

          if($i < $total){
              if($z == $total){
                $arrData[$z]['ID_BEFORE'] = $data[$i-1]['ID'];
                $arrData[$z]['CONT_BEFORE'] = $data[$i-1]['HIST_CONT'];
                $arrData[$z]['CONT_SIZE'] = $data[$i-1]['CONT_SIZE'];
                $arrData[$z]['CONT_TYPE'] = $data[$i-1]['CONT_TYPE'];
                $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i-1]['CONT_STATUS'];
                $arrData[$z]['LOCATION_BEFORE'] = $data[$i-1]['LOCATION'];
                $arrData[$z]['COUNTER_BEFORE'] = $data[$i-1]['HIST_COUNTER'];
                $arrData[$z]['DATE_BEFORE'] = $data[$i-1]['HIST_DATE_FORMAT'];
              }

            $arrData[$z]['ID_AFTER'] = $data[$i-1]['ID'];
            $arrData[$z]['CONT_AFTER'] = $data[$i-1]['HIST_CONT'];
            $arrData[$z]['CONT_STATUS_AFTER'] = $data[$i-1]['CONT_STATUS'];
            $arrData[$z]['LOCATION_AFTER'] = $data[$i-1]['LOCATION'];
            $arrData[$z]['COUNTER_AFTER'] = $data[$i-1]['HIST_COUNTER'];
            $arrData[$z]['DATE_AFTER'] = $data[$i-1]['HIST_DATE_FORMAT'];
            $arrData[$z]['USER'] = $data[$i-1]['USER_NAME'];
            $z++;
            
      
            $arrData[$z]['ID_BEFORE'] = $data[$i]['ID'];
            $arrData[$z]['CONT_BEFORE'] = $data[$i]['HIST_CONT'];
            $arrData[$z]['CONT_SIZE'] = $data[$i]['CONT_SIZE'];
            $arrData[$z]['CONT_TYPE'] = $data[$i]['CONT_TYPE'];
            $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i]['CONT_STATUS'];
            $arrData[$z]['LOCATION_BEFORE'] = $data[$i]['LOCATION'];
            $arrData[$z]['COUNTER_BEFORE'] = $data[$i]['HIST_COUNTER'];
            $arrData[$z]['DATE_BEFORE'] = $data[$i]['HIST_DATE_FORMAT'];
            
            $arrData[$z]['ID_AFTER'] = $data[$i]['ID'];
            $arrData[$z]['CONT_AFTER'] = $data[$i]['HIST_CONT'];
            $arrData[$z]['CONT_STATUS_AFTER'] = $data[$i]['CONT_STATUS'];
            $arrData[$z]['LOCATION_AFTER'] = $data[$i]['LOCATION'];
            $arrData[$z]['COUNTER_AFTER'] = $data[$i]['HIST_COUNTER'];
            $arrData[$z]['DATE_AFTER'] = $data[$i]['HIST_DATE_FORMAT'];
            $arrData[$z]['USER'] = $data[$i]['USER_NAME'];
            $z++;
          }
          
        }
      }
      $t++;

	}
		return $arrData;
	}

function report(){
	$branch_id = $this->session->USER_BRANCH;
		$params = array(
			'BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);

		$end = $_REQUEST['start'] + $_REQUEST['limit'];
		$start = $_REQUEST['start'];

		$qWhere = "";
		$filters = isset($_REQUEST['filter'])? json_decode($_REQUEST['filter']) : false;
		$tgl1 = $_REQUEST['date1'] != null? substr($_REQUEST['date1'],0,-9) : date('Y-m-d');
		$tgl2 = $_REQUEST['date2'] != null? substr($_REQUEST['date2'],0,-9) : date('Y-m-d');

		$qw = '';
		$qWhere .=" AND TO_DATE(TO_CHAR(HIST_DATE,'YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN TO_DATE('".$tgl1."','YYYY-MM-DD') AND TO_DATE('".$tgl2."','YYYY-MM-DD')";

		//apply filter
			$qs = '';
			if ($filters != false){
				for ($i=0;$i<count($filters);$i++){
					$filter = $filters[$i];
						$field = $filter->property;
						$value = $filter->value;
						$operator = $filter->operator;

						if($field == 'HIST_DATE_FORMAT'){
							$field = 'HIST_DATE';
						}
						if($field == 'CONT_BEFORE'){
							$field = 'HIST_CONT';
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

    $data = $this->db->query("SELECT ROWNUM ID, F.* FROM (SELECT ROWNUM R, A.* FROM (SELECT B.HIST_ACTIVITY, HIST_CONT, C.CONTAINER_SIZE CONT_SIZE, C.CONTAINER_TYPE CONT_TYPE, B.HIST_CONT_STATUS CONT_STATUS, HIST_BRANCH_ID, HIST_COUNTER, HIST_NOREQ,  HIST_BLOCK || '/' || HIST_SLOT || '/' || HIST_ROW || '/' || HIST_TIER AS LOCATION, HIST_DATE, TO_CHAR(HIST_DATE,'DD/MM/YYYY HH24:MI') HIST_DATE_FORMAT, D.FULL_NAME USER_NAME
			FROM TH_HISTORY_CONTAINER B 
			JOIN TM_CONTAINER C ON C.CONTAINER_NO = B.HIST_CONT
			JOIN TM_USER D ON D.USER_ID = B.HIST_USER
			WHERE HIST_BRANCH_ID = $branch_id AND HIST_ACTIVITY IN ('Placement','Shuffling') $qWhere
			ORDER BY HIST_CONT, HIST_COUNTER, HIST_DATE) A WHERE ROWNUM <= $end) F WHERE R >= $start + 1")->result_array();

	$tot_data = $this->db->query("SELECT COUNT(1) TOTAL FROM TH_HISTORY_CONTAINER B 
			JOIN TM_CONTAINER C ON C.CONTAINER_NO = B.HIST_CONT
			JOIN TM_USER D ON D.USER_ID = B.HIST_USER
			WHERE HIST_BRANCH_ID = $branch_id AND HIST_ACTIVITY IN ('Placement','Shuffling') $qWhere
			ORDER BY HIST_CONT, HIST_COUNTER, HIST_DATE")->row_array()['TOTAL'];

    $arrData = array();
    $z = 0;
    $t = 0;
    $total = count($data);
    for ($i=0; $i < $total+1; $i++) {
      
      if((int)@$data[$i]['ID'] % 2 != 0 ){
          if($i == 0){
            $arrData[$z]['ID_BEFORE'] = $data[$i]['ID'];
            $arrData[$z]['CONT_BEFORE'] = $data[$i]['HIST_CONT'];
            $arrData[$z]['CONT_SIZE'] = $data[$i]['CONT_SIZE'];
            $arrData[$z]['CONT_TYPE'] = $data[$i]['CONT_TYPE'];
            $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i]['CONT_STATUS'];
            $arrData[$z]['LOCATION_BEFORE'] = $data[$i]['LOCATION'];
            $arrData[$z]['COUNTER_BEFORE'] = $data[$i]['HIST_COUNTER'];
            $arrData[$z]['DATE_BEFORE'] = $data[$i]['HIST_DATE_FORMAT'];
          }
          else{
              if($data[$t-1]['HIST_CONT'] == $data[$i]['HIST_CONT']){
                $arrData[$z]['ID_BEFORE'] = $data[$i-1]['ID'];
                $arrData[$z]['CONT_BEFORE'] = $data[$i-1]['HIST_CONT'];
                $arrData[$z]['CONT_SIZE'] = $data[$i-1]['CONT_SIZE'];
                $arrData[$z]['CONT_TYPE'] = $data[$i-1]['CONT_TYPE'];
                $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i-1]['CONT_STATUS'];
                $arrData[$z]['LOCATION_BEFORE'] = $data[$i-1]['LOCATION'];
                $arrData[$z]['COUNTER_BEFORE'] = $data[$i-1]['HIST_COUNTER'];
                $arrData[$z]['DATE_BEFORE'] = $data[$i-1]['HIST_DATE_FORMAT'];

                if($z == $total-1){
                  $arrData[$z]['ID_AFTER'] = $data[$i]['ID'];
                  $arrData[$z]['CONT_AFTER'] = $data[$i]['HIST_CONT'];
                  $arrData[$z]['CONT_STATUS_AFTER'] = $data[$i]['CONT_STATUS'];
                  $arrData[$z]['LOCATION_AFTER'] = $data[$i]['LOCATION'];
                  $arrData[$z]['COUNTER_AFTER'] = $data[$i]['HIST_COUNTER'];
                  $arrData[$z]['DATE_AFTER'] = $data[$i]['HIST_DATE_FORMAT'];
                  $arrData[$z]['USER'] = $data[$i]['USER_NAME'];
                }

              }
              else{
                $arrData[$z]['ID_BEFORE'] = $data[$i]['ID'];
                $arrData[$z]['CONT_BEFORE'] = $data[$i]['HIST_CONT'];
                $arrData[$z]['CONT_SIZE'] = $data[$i]['CONT_SIZE'];
                $arrData[$z]['CONT_TYPE'] = $data[$i]['CONT_TYPE'];
                $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i]['CONT_STATUS'];
                $arrData[$z]['LOCATION_BEFORE'] = $data[$i]['LOCATION'];
                $arrData[$z]['COUNTER_BEFORE'] = $data[$i]['HIST_COUNTER'];
                $arrData[$z]['DATE_BEFORE'] = $data[$i]['HIST_DATE_FORMAT'];
              }
          }
      }
      else{
        if(@$data[$t-1]['HIST_CONT'] == @$data[$i]['HIST_CONT']){
  
          $arrData[$z]['ID_AFTER'] = $data[$i-1]['ID'];
          $arrData[$z]['CONT_AFTER'] = $data[$i-1]['HIST_CONT'];
          $arrData[$z]['CONT_STATUS_AFTER'] = $data[$i-1]['CONT_STATUS'];
          $arrData[$z]['LOCATION_AFTER'] = $data[$i-1]['LOCATION'];
          $arrData[$z]['COUNTER_AFTER'] = $data[$i-1]['HIST_COUNTER'];
          $arrData[$z]['DATE_AFTER'] = $data[$i-1]['HIST_DATE_FORMAT'];
          $arrData[$z]['USER'] = $data[$i-1]['USER_NAME'];
          $z++;

          $arrData[$z]['ID_BEFORE'] = $data[$i-1]['ID'];
          $arrData[$z]['CONT_BEFORE'] = $data[$i-1]['HIST_CONT'];
          $arrData[$z]['CONT_SIZE'] = $data[$i-1]['CONT_SIZE'];
          $arrData[$z]['CONT_TYPE'] = $data[$i-1]['CONT_TYPE'];
          $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i-1]['CONT_STATUS'];
          $arrData[$z]['LOCATION_BEFORE'] = $data[$i-1]['LOCATION'];
          $arrData[$z]['COUNTER_BEFORE'] = $data[$i-1]['HIST_COUNTER'];
          $arrData[$z]['DATE_BEFORE'] = $data[$i-1]['HIST_DATE_FORMAT'];
          
          $arrData[$z]['ID_AFTER'] = $data[$i]['ID'];
          $arrData[$z]['CONT_AFTER'] = $data[$i]['HIST_CONT'];
          $arrData[$z]['CONT_STATUS_AFTER'] = $data[$i]['CONT_STATUS'];
          $arrData[$z]['LOCATION_AFTER'] = $data[$i]['LOCATION'];
          $arrData[$z]['COUNTER_AFTER'] = $data[$i]['HIST_COUNTER'];
          $arrData[$z]['DATE_AFTER'] = $data[$i]['HIST_DATE_FORMAT'];
          $arrData[$z]['USER'] = $data[$i]['USER_NAME'];
          $z++;

        }
        else{

          if($i < $total){
              if($z == $total){
                $arrData[$z]['ID_BEFORE'] = $data[$i-1]['ID'];
                $arrData[$z]['CONT_BEFORE'] = $data[$i-1]['HIST_CONT'];
                $arrData[$z]['CONT_SIZE'] = $data[$i-1]['CONT_SIZE'];
                $arrData[$z]['CONT_TYPE'] = $data[$i-1]['CONT_TYPE'];
                $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i-1]['CONT_STATUS'];
                $arrData[$z]['LOCATION_BEFORE'] = $data[$i-1]['LOCATION'];
                $arrData[$z]['COUNTER_BEFORE'] = $data[$i-1]['HIST_COUNTER'];
                $arrData[$z]['DATE_BEFORE'] = $data[$i-1]['HIST_DATE_FORMAT'];
              }

            $arrData[$z]['ID_AFTER'] = $data[$i-1]['ID'];
            $arrData[$z]['CONT_AFTER'] = $data[$i-1]['HIST_CONT'];
            $arrData[$z]['CONT_STATUS_AFTER'] = $data[$i-1]['CONT_STATUS'];
            $arrData[$z]['LOCATION_AFTER'] = $data[$i-1]['LOCATION'];
            $arrData[$z]['COUNTER_AFTER'] = $data[$i-1]['HIST_COUNTER'];
            $arrData[$z]['DATE_AFTER'] = $data[$i-1]['HIST_DATE_FORMAT'];
            $arrData[$z]['USER'] = $data[$i-1]['USER_NAME'];
            $z++;
            
      
            $arrData[$z]['ID_BEFORE'] = $data[$i]['ID'];
            $arrData[$z]['CONT_BEFORE'] = $data[$i]['HIST_CONT'];
            $arrData[$z]['CONT_SIZE'] = $data[$i]['CONT_SIZE'];
            $arrData[$z]['CONT_TYPE'] = $data[$i]['CONT_TYPE'];
            $arrData[$z]['CONT_STATUS_BEFORE'] = $data[$i]['CONT_STATUS'];
            $arrData[$z]['LOCATION_BEFORE'] = $data[$i]['LOCATION'];
            $arrData[$z]['COUNTER_BEFORE'] = $data[$i]['HIST_COUNTER'];
            $arrData[$z]['DATE_BEFORE'] = $data[$i]['HIST_DATE_FORMAT'];
            
            $arrData[$z]['ID_AFTER'] = $data[$i]['ID'];
            $arrData[$z]['CONT_AFTER'] = $data[$i]['HIST_CONT'];
            $arrData[$z]['CONT_STATUS_AFTER'] = $data[$i]['CONT_STATUS'];
            $arrData[$z]['LOCATION_AFTER'] = $data[$i]['LOCATION'];
            $arrData[$z]['COUNTER_AFTER'] = $data[$i]['HIST_COUNTER'];
            $arrData[$z]['DATE_AFTER'] = $data[$i]['HIST_DATE_FORMAT'];
            $arrData[$z]['USER'] = $data[$i]['USER_NAME'];
            $z++;
          }
          
        }
      }
      $t++;

	}
	return array (
			'data' => $arrData,
			'total' => $tot_data
	);
  }

}
