<?php
class M_gateTruck extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	// IN OUT
  function getGate(){
    $branch_id = $this->session->USER_BRANCH;
    $params = array(
      'BRANCH_ID' => $branch_id,
      'STATUS' => $_REQUEST['status'],
      'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
    );

    $search = '';
		if(!empty($_REQUEST['CONTAINER_NO'])){
			$search .= " AND A.GATE_CONT = ".$this->db->escape($_REQUEST['CONTAINER_NO']);
		}
    if(!empty($_REQUEST['REQUEST_NO'])){
			$search .= " AND A.GATE_NOREQ = ".$this->db->escape($_REQUEST['REQUEST_NO']);
		}
    if(!empty($_REQUEST['TRUCK_NO'])){
			$search .= " AND A.GATE_TRUCK_NO = ".$this->db->escape($_REQUEST['TRUCK_NO']);
		}

    $query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (SELECT A.GATE_ID, A.GATE_NOREQ, A.GATE_CONT, A.GATE_CONT_SIZE, B.REFF_NAME SIZE_NAME,  A.GATE_CONT_TYPE,  C.REFF_NAME TYPE_NAME, A.GATE_CONT_STATUS,  D.REFF_NAME STATUS_NAME, A.GATE_CONSIGNEE_ID,
              A.GATE_CONSIGNEE_NAME, A.GATE_TRUCK_NO, A.GATE_NO_SEAL, A.GATE_MARK,  A.GATE_STATUS, A.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, TO_CHAR(A.GATE_CREATE_DATE,'DD/MM/YYYY HH24:MI') GATE_CREATE_DATE, A.GATE_ACTIVITY, F.REFF_NAME ACTIVITY_NAME
              FROM TX_GATE A
              JOIN TM_REFF B ON A.GATE_CONT_SIZE = B.REFF_ID AND B.REFF_TR_ID = 6
              JOIN TM_REFF C ON A.GATE_CONT_TYPE = C.REFF_ID AND C.REFF_TR_ID = 5
              JOIN TM_REFF D ON A.GATE_CONT_STATUS = D.REFF_ID AND D.REFF_TR_ID = 4
              LEFT JOIN TM_REFF E ON A.GATE_ORIGIN = E.REFF_ID AND E.REFF_TR_ID = 20
							LEFT JOIN TM_REFF F ON A.GATE_ACTIVITY = F.REFF_ID AND F.REFF_TR_ID = 22
              WHERE A.GATE_BRANCH_ID = ? AND A.GATE_STATUS = ? $search
              ORDER BY A.GATE_ID DESC) T
                	WHERE ROWNUM <= ?) F
										WHERE r >= ? + 1";
    $data = $this->db->query($query,$params)->result_array();

    return array (
      'data' => $data,
      'total' => count($data)
    );
  }

	function getTruckActivities(){
		$sql = "SELECT A.REFF_ID, A.REFF_NAME, A.REFF_ORDER FROM TM_REFF A
			JOIN TR_REFF B ON A.REFF_TR_ID = B.REFF_ID
					WHERE B.REFF_ID = 22 AND A.REFF_ID IN (1,2)";
		$data = $this->db->query($sql)->result_array();
		return $data;
	}

	function getContainer($filter){
		$branch_id = $this->session->USER_BRANCH;
		$query = "SELECT * FROM (
		SELECT B.STRIP_NO REQ_NO, A.STRIP_DTL_ID DTL_ID, A.STRIP_DTL_CONT DTL_CONT, A.STRIP_DTL_CONT_SIZE CONT_SIZE, A.STRIP_DTL_CONT_STATUS CONT_STATUS, A.STRIP_DTL_CONT_TYPE CONT_TYPE, 1 ACTIVITY, 'Stripping' ACTIVITY_NAME, C.CONSIGNEE_ID, C.CONSIGNEE_NAME, B.STRIP_NOREQ_RECEIVING REQ_REC
		FROM TX_REQ_STRIP_DTL A
		JOIN TX_REQ_STRIP_HDR B ON A.STRIP_DTL_HDR_ID = B.STRIP_ID
		LEFT JOIN TM_CONSIGNEE C ON C.CONSIGNEE_ID = B.STRIP_CONSIGNEE_ID
		WHERE A.STRIP_DTL_STATUS IN('1','0') AND B.STRIP_STATUS <> 2 AND B.STRIP_BRANCH_ID = $branch_id
		UNION ALL
		SELECT B.STUFF_NO REQ_NO, A.STUFF_DTL_ID DTL_ID, A.STUFF_DTL_CONT DTL_CONT, A.STUFF_DTL_CONT_SIZE CONT_SIZE, A.STUFF_DTL_CONT_STATUS CONT_STATUS, A.STUFF_DTL_CONT_TYPE CONT_TYPE, 2 ACTIVITY, 'Stuffing' ACTIVITY_NAME, C.CONSIGNEE_ID, C.CONSIGNEE_NAME, B.STUFF_NOREQ_RECEIVING REQ_REC
		FROM TX_REQ_STUFF_DTL A
		JOIN TX_REQ_STUFF_HDR B ON A.STUFF_DTL_HDR_ID = B.STUFF_ID
		LEFT JOIN TM_CONSIGNEE C ON C.CONSIGNEE_ID = B.STUFF_CONSIGNEE_ID
		WHERE A.STUFF_DTL_STATUS IN (0,1) AND B.STUFF_STATUS <> 2 AND B.STUFF_BRANCH_ID = $branch_id)
		WHERE LOWER(DTL_CONT) LIKE '%".strtolower($filter)."%'";
		return $this->db->query($query)->result_array();
	}

	function getTruckOut($filter){
		$branch_id = $this->session->USER_BRANCH;
		$query = "SELECT A.GATE_TRUCK_NO TRUCK_NO, A.GATE_CONT DTL_CONT, A.GATE_CONT_SIZE CONT_SIZE, A.GATE_CONT_TYPE CONT_TYPE, A.GATE_CONT_STATUS CONT_STATUS, A.GATE_CONSIGNEE_ID CONSIGNEE_ID, A.GATE_CONSIGNEE_NAME CONSIGNEE_NAME,
		A.GATE_ACTIVITY ACTIVITY, B.REFF_NAME ACTIVITY_NAME, A.GATE_NOREQ REQ_NO
		FROM TX_GATE A
		INNER JOIN TM_REFF B ON B.REFF_ID = A.GATE_ACTIVITY AND B.REFF_TR_ID = 22
		WHERE A.GATE_BRANCH_ID = $branch_id AND A.GATE_STATUS = 1
		AND
		CASE A.GATE_ACTIVITY
		WHEN '1'
		THEN (SELECT COUNT(1) FROM TX_REQ_STRIP_HDR J INNER JOIN TX_REQ_STRIP_DTL K ON K.STRIP_DTL_HDR_ID = J.STRIP_ID WHERE J.STRIP_NO = A.GATE_NOREQ AND K.STRIP_DTL_CONT = A.GATE_CONT AND K.STRIP_DTL_STATUS = 1 )
		WHEN '2'
		THEN (SELECT COUNT(1) FROM TX_REQ_STUFF_HDR L INNER JOIN TX_REQ_STUFF_DTL M ON L.STUFF_ID = M.STUFF_DTL_HDR_ID WHERE L.STUFF_NO = A.GATE_NOREQ AND M.STUFF_DTL_CONT = A.GATE_CONT AND M.STUFF_DTL_STATUS = 1 )
		END > 0
		AND (SELECT COUNT(1) FROM TX_GATE C WHERE C.GATE_CONT = A.GATE_CONT AND C.GATE_TRUCK_NO = A.GATE_TRUCK_NO AND C.GATE_NOREQ = A.GATE_NOREQ) <= 1
		AND (LOWER(A.GATE_CONT) LIKE '%".strtolower($filter)."%' OR LOWER(A.GATE_TRUCK_NO) LIKE '%".strtolower($filter)."%')";
				return $this->db->query($query)->result_array();
	}

  function getGateInTruckCardStripping($filter) {
		$branch_id = $this->session->USER_BRANCH;
    $params = array('BRANCH_ID' => $branch_id);
    $query = "SELECT A.STRIP_DTL_ID DTL_ID, A.STRIP_DTL_CONT DTL_CONT FROM TX_REQ_STRIP_DTL A
              JOIN TX_REQ_STRIP_HDR B ON A.STRIP_DTL_HDR_ID = B.STRIP_ID
              WHERE A.STRIP_DTL_STATUS IN('1','0') AND B.STRIP_BRANCH_ID = ?
              AND LOWER(A.STRIP_DTL_CONT) LIKE '%".strtolower($filter)."%'
          		ORDER BY A.STRIP_DTL_CONT";
    return $this->db->query($query,$params)->result_array();
  }

	function getGateOutTruckCardStripping($filter) {
		$branch_id = $this->session->USER_BRANCH;
    $params = array('BRANCH_ID' => $branch_id);
    $query = "SELECT A.STRIP_DTL_ID DTL_ID, A.STRIP_DTL_CONT DTL_CONT FROM TX_REQ_STRIP_DTL A
              JOIN TX_REQ_STRIP_HDR B ON A.STRIP_DTL_HDR_ID = B.STRIP_ID
              WHERE A.STRIP_DTL_STATUS IN('1','2') AND B.STRIP_BRANCH_ID = ?
              AND LOWER(A.STRIP_DTL_CONT) LIKE '%".strtolower($filter)."%'
          		ORDER BY A.STRIP_DTL_CONT";
    return $this->db->query($query,$params)->result_array();
  }

	function getGateInTruckCardStuffing($filter) {
		$branch_id = $this->session->USER_BRANCH;
    $params = array('BRANCH_ID' => $branch_id);
    $query = "SELECT A.STUFF_DTL_ID DTL_ID, A.STUFF_DTL_CONT DTL_CONT FROM TX_REQ_STUFF_DTL A
              JOIN TX_REQ_STUFF_HDR B ON A.STUFF_DTL_HDR_ID = B.STUFF_ID
              WHERE A.STUFF_DTL_STATUS IN('1','0') AND B.STUFF_BRANCH_ID = ?
              AND LOWER(A.STUFF_DTL_CONT) LIKE '%".strtolower($filter)."%'
          		ORDER BY A.STUFF_DTL_CONT";
    return $this->db->query($query,$params)->result_array();
  }

	function getGateOutTruckCardStuffing($filter) {
		$branch_id = $this->session->USER_BRANCH;
    $params = array('BRANCH_ID' => $branch_id);
    $query = "SELECT A.STUFF_DTL_ID DTL_ID, A.STUFF_DTL_CONT DTL_CONT FROM TX_REQ_STUFF_DTL A
              JOIN TX_REQ_STUFF_HDR B ON A.STUFF_DTL_HDR_ID = B.STUFF_ID
              WHERE A.STUFF_DTL_STATUS IN('1','2') AND B.STUFF_BRANCH_ID = ?
              AND LOWER(A.STUFF_DTL_CONT) LIKE '%".strtolower($filter)."%'
          		ORDER BY A.STUFF_DTL_CONT";
    return $this->db->query($query,$params)->result_array();
  }

	function getGateTruckStrippById(){
    $branch_id = $this->session->USER_BRANCH;
    $params = array('BRANCH_ID' => $branch_id, 'DTL_CONT' => $_POST['id']);
    $query = "SELECT A.STRIP_DTL_ID, A.STRIP_DTL_HDR_ID, A.STRIP_DTL_CONT CONTAINER_NO, A.STRIP_DTL_CONT_SIZE DTL_CONT_SIZE, C.REFF_NAME SIZE_NAME, A.STRIP_DTL_CONT_TYPE DTL_CONT_TYPE, D.REFF_NAME TYPE_NAME,
              A.STRIP_DTL_COMMODITY, A.STRIP_DTL_CONT_STATUS DTL_CONT_STATUS, E.REFF_NAME STATUS_NAME, B.STRIP_NO REQUEST_NO, B.STRIP_CONSIGNEE_ID CONSIGNEE_ID, F.CONSIGNEE_NAME
              FROM TX_REQ_STRIP_DTL A
              JOIN TX_REQ_STRIP_HDR B ON A.STRIP_DTL_HDR_ID = B.STRIP_ID
              JOIN TM_REFF C ON A.STRIP_DTL_CONT_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6
              JOIN TM_REFF D ON A.STRIP_DTL_CONT_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5
              JOIN TM_REFF E ON A.STRIP_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4
              JOIN TM_CONSIGNEE F ON B.STRIP_CONSIGNEE_ID = F.CONSIGNEE_ID
              WHERE B.STRIP_BRANCH_ID = ? AND A.STRIP_DTL_ID = ?";
    return $this->db->query($query,$params)->result_array();
  }

	function getGateTruckStuffById(){
		$branch_id = $this->session->USER_BRANCH;
		$params = array('BRANCH_ID' => $branch_id, 'DTL_CONT' => $_POST['id']);
		$query = "SELECT A.STUFF_DTL_ID, A.STUFF_DTL_HDR_ID, A.STUFF_DTL_CONT CONTAINER_NO, A.STUFF_DTL_CONT_SIZE DTL_CONT_SIZE, C.REFF_NAME SIZE_NAME, A.STUFF_DTL_CONT_TYPE DTL_CONT_TYPE, D.REFF_NAME TYPE_NAME,
							A.STUFF_DTL_CONT_STATUS DTL_CONT_STATUS, E.REFF_NAME STATUS_NAME, B.STUFF_NO REQUEST_NO, B.STUFF_CONSIGNEE_ID CONSIGNEE_ID, F.CONSIGNEE_NAME
							FROM TX_REQ_STUFF_DTL A
							JOIN TX_REQ_STUFF_HDR B ON A.STUFF_DTL_HDR_ID = B.STUFF_ID
							JOIN TM_REFF C ON A.STUFF_DTL_CONT_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6
							JOIN TM_REFF D ON A.STUFF_DTL_CONT_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5
							JOIN TM_REFF E ON A.STUFF_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4
							JOIN TM_CONSIGNEE F ON B.STUFF_CONSIGNEE_ID = F.CONSIGNEE_ID
							WHERE B.STUFF_BRANCH_ID = ? AND A.STUFF_DTL_ID = ?";
		return $this->db->query($query,$params)->result_array();
	}

	function setGateInTruck(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$message =  'SUKSES';
		$sukses = true;
		$arrGate = array();
		$this->db->trans_start();

		// $arrData = array(
		// 	'GATE_CONSIGNEE_NAME' => $this->input->post('CONSIGNEE_NAME'),
		// 	'GATE_CONT' => $this->input->post('CONTAINER_NO'),
		// 	'GATE_TRUCK_NO' => $this->input->post('POLICE_NO'),
		// 	'GATE_CONSIGNEE_ID' => $this->input->post('CONSIGNEE_ID'),
		// 	'GATE_CONT_SIZE' => $this->input->post('DTL_CONT_SIZE'),
		// 	'GATE_CONT_STATUS' => $this->input->post('DTL_CONT_STATUS'),
		// 	'GATE_CONT_TYPE' => $this->input->post('DTL_CONT_TYPE'),
		// 	'GATE_NOREQ' => $this->input->post('REQ'),
		// 	'GATE_ACTIVITY' => $this->input->post('ACTIVITY'),
		// 	'GATE_BRANCH_ID' => $branch_id,
		// 	'GATE_STATUS' => 1,
		// 	'GATE_CREATE_BY' => $user_id
		// );
		//
		// $this->db->insert('TX_GATE',$arrData);

		$this->db->set('GATE_CONSIGNEE_NAME', $this->input->post('CONSIGNEE_NAME'));
		$this->db->set('GATE_CONT', $this->input->post('CONTAINER_NO'));
		$this->db->set('GATE_TRUCK_NO', $this->input->post('POLICE_NO'));
		$this->db->set('GATE_CONSIGNEE_ID', $this->input->post('CONSIGNEE_ID'));
		$this->db->set('GATE_CONT_SIZE', $this->input->post('DTL_CONT_SIZE'));
		$this->db->set('GATE_CONT_STATUS', $this->input->post('DTL_CONT_STATUS'));
		$this->db->set('GATE_CONT_TYPE', $this->input->post('DTL_CONT_TYPE'));
		$this->db->set('GATE_NOREQ', $this->input->post('REQ'));
		$this->db->set('GATE_ACTIVITY', $this->input->post('ACTIVITY'));
		$this->db->set('GATE_BRANCH_ID', $branch_id);
		$this->db->set('GATE_STATUS',1);
		$this->db->set('GATE_CREATE_BY', $user_id);

		if(isset($_POST['GATE_IN_DATE'])){
			if($_POST['GATE_IN_DATE'] != 'Date' && isset($_POST['GATE_IN_TIME'])){
			$gate_back_date = $this->input->post('GATE_IN_DATE').' '.$this->input->post('GATE_IN_TIME');
			$this->db->set('GATE_CREATE_DATE', "to_date('$gate_back_date','DD/MM/YYYY HH24:MI')",false);
			$this->db->set('GATE_BACKDATE', $this->input->post('REASON'));
			}
		}

		//cek jika container sudah gateIn
		$cont_check = $this->db->where('GATE_NOREQ',$this->input->post('REQUEST_NO'))->where('GATE_CONT',$this->input->post('CONTAINER'))->where('GATE_BRANCH_ID',$branch_id)->where('GATE_STATUS',1)->from('TX_GATE')->count_all_results();
		if($cont_check > 0){
			return array('success' => false, 'message' => 'Container already gate in !!');
			die();
		}

		$this->db->insert('TX_GATE');

		if($this->input->post('ACTIVITY') == 1){
			$this->db->query("UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_STATUS = 1, STRIP_DTL_ACTIVE = 'Y' WHERE  STRIP_DTL_CONT = '".$this->input->post('CONTAINER_NO')."' AND STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$this->input->post('REQ')."' AND STRIP_BRANCH_ID = ".$branch_id.")");
		}
		else{
			$this->db->query("UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_STATUS = 1, STUFF_DTL_ACTIVE = 'Y' WHERE  STUFF_DTL_CONT = '".$this->input->post('CONTAINER_NO')."' AND STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$this->input->post('REQ')."' AND STUFF_BRANCH_ID = ".$branch_id.")");
		}

		// print cms
		//get data gate
		$req = $this->input->post('REQ');
		$cont = $this->input->post('CONTAINER_NO');
		$truck = $this->input->post('POLICE_NO');
		$gate = $this->db->select("GATE_CONSIGNEE_NAME, GATE_CONT, NVL(GATE_MARK,'-') GATE_MARK, NVL(GATE_ORIGIN,'-') GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, NVL(GATE_CONT_SIZE,'-') GATE_CONT_SIZE, NVL(GATE_CONT_TYPE,'-') GATE_CONT_TYPE, GATE_NOREQ, NVL(GATE_NO_SEAL,'-') GATE_NO_SEAL, NVL(GATE_CONT_STATUS,'-') GATE_CONT_STATUS,
						GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, GATE_STACK_STATUS, GATE_STATUS, B.REFF_NAME ACTIVITY_NAME, TO_CHAR(GATE_CREATE_DATE,'DD-MM-YYYY HH24:MI:SS') GATE_DATE")
						->from('TX_GATE A')
						->join('TM_REFF B','B.REFF_ID = A.GATE_ACTIVITY AND B.REFF_TR_ID = 22')
						->where('GATE_BRANCH_ID',$branch_id)
						->where('GATE_STATUS',1)
						->where('GATE_NOREQ',$req)
						->where('GATE_CONT',$cont)
						->where('GATE_TRUCK_NO',$truck)
						->get()->row_array();

			// crate array for print delivery
			$cont_in_yard = $this->db->query("SELECT * FROM(SELECT MAX(REAL_YARD_ID) REAL_YARD_ID, REAL_YARD_CONT, YARD, BLOCK_, SLOT_, ROW_, TIER_, BLOCK_ID  FROM(
								SELECT A.REAL_YARD_ID, A.REAL_YARD_CONT, A.REAL_YARD_TIER TIER_, B.YBC_SLOT SLOT_, B.YBC_ROW ROW_, C.BLOCK_NAME BLOCK_, D.YARD_NAME YARD, C.BLOCK_ID
								FROM TX_REAL_YARD A
								JOIN TX_YARD_BLOCK_CELL B ON B.YBC_ID = A.REAL_YARD_YBC_ID
								JOIN TM_BLOCK C ON C.BLOCK_ID = B.YBC_BLOCK_ID
								JOIN TM_YARD D ON D.YARD_ID = C.BLOCK_YARD_ID
								WHERE B.YBC_ACTIVE = 'Y' AND C.BLOCK_ACTIVE = 'Y' AND REAL_YARD_ID IN(
									SELECT X.REAL_YARD_ID  FROM (
										SELECT MAX(H.REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_BRANCH_ID = ".$branch_id." GROUP BY H.REAL_YARD_CONT
									)X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
								)
							)Z GROUP BY Z.REAL_YARD_CONT, Z.YARD, Z.BLOCK_, Z.SLOT_, Z.ROW_, Z.TIER_, Z.BLOCK_ID ) Y
						 WHERE REAL_YARD_CONT = '".$gate['GATE_CONT']."'")->row_array();

									 $arrGate[0]['GATE_TIME'] = $gate['GATE_DATE'];
									 $arrGate[0]['NOREQ'] = $gate['GATE_NOREQ'];
									 $arrGate[0]['TRUCK_NO'] = $this->input->post('POLICE_NO');
									 $arrGate[0]['SEAL_NO'] = $gate['GATE_NO_SEAL'];
									 $arrGate[0]['CONTAINER'] = $gate['GATE_CONT'];
									 $arrGate[0]['ACTIVITY'] = $gate['ACTIVITY_NAME'];
									 $arrGate[0]['ORIGIN'] = $gate['GATE_ORIGIN'];
									 $arrGate[0]['SIZE'] = $gate['GATE_CONT_SIZE'];
									 $arrGate[0]['TYPE'] = $gate['GATE_CONT_TYPE'];
									 $arrGate[0]['STATUS'] = $gate['GATE_CONT_STATUS'];
									 $arrGate[0]['YARD_NAME'] = $cont_in_yard['YARD'];
									 $arrGate[0]['BLOCK_NAME'] = $cont_in_yard['BLOCK_'];
									 $arrGate[0]['SLOT'] = $cont_in_yard['SLOT_'];
									 $arrGate[0]['ROW'] = $cont_in_yard['ROW_'];
									 $arrGate[0]['TIER'] = $cont_in_yard['TIER_'];
									 $arrGate[0]['REMARK'] = $gate['GATE_MARK'];
			// end create print delivery

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
				$sukses = false;
		}

		return array('success' => $sukses, 'message' => $message, 'data' => $arrGate);
	}

	function setGateOutTruck(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$message =  'SUKSES';
		$this->db->trans_start();

		// $arrData = array(
		// 	'GATE_CONSIGNEE_NAME' => $this->input->post('CONSIGNEE_NAME'),
		// 	'GATE_CONT' => $this->input->post('CONTAINER_NO'),
		// 	'GATE_TRUCK_NO' => $this->input->post('POLICE_NO'),
		// 	'GATE_CONSIGNEE_ID' => $this->input->post('CONSIGNEE_ID'),
		// 	'GATE_CONT_SIZE' => $this->input->post('DTL_CONT_SIZE'),
		// 	'GATE_CONT_STATUS' => $this->input->post('DTL_CONT_STATUS'),
		// 	'GATE_CONT_TYPE' => $this->input->post('DTL_CONT_TYPE'),
		// 	'GATE_NOREQ' => $this->input->post('REQ'),
		// 	'GATE_ACTIVITY' => $this->input->post('ACTIVITY'),
		// 	'GATE_BRANCH_ID' => $branch_id,
		// 	'GATE_STATUS' => 3,
		// 	'GATE_CREATE_BY' => $user_id
		// );
		//
		// $this->db->insert('TX_GATE',$arrData);

		$this->db->set('GATE_CONSIGNEE_NAME', $this->input->post('CONSIGNEE_NAME'));
		$this->db->set('GATE_CONT', $this->input->post('CONTAINER_NO'));
		$this->db->set('GATE_TRUCK_NO', $this->input->post('POLICE_NO'));
		$this->db->set('GATE_CONSIGNEE_ID', $this->input->post('CONSIGNEE_ID'));
		$this->db->set('GATE_CONT_SIZE', $this->input->post('DTL_CONT_SIZE'));
		$this->db->set('GATE_CONT_STATUS', $this->input->post('DTL_CONT_STATUS'));
		$this->db->set('GATE_CONT_TYPE', $this->input->post('DTL_CONT_TYPE'));
		$this->db->set('GATE_NOREQ', $this->input->post('REQ'));
		$this->db->set('GATE_ACTIVITY', $this->input->post('ACTIVITY'));
		$this->db->set('GATE_BRANCH_ID', $branch_id);
		$this->db->set('GATE_STATUS',3);
		$this->db->set('GATE_CREATE_BY', $user_id);

		if(isset($_POST['GATE_IN_DATE'])){
			if($_POST['GATE_IN_DATE'] != 'Date' && isset($_POST['GATE_IN_TIME'])){
			$gate_back_date = $this->input->post('GATE_IN_DATE').' '.$this->input->post('GATE_IN_TIME');
			$this->db->set('GATE_CREATE_DATE', "to_date('$gate_back_date','DD/MM/YYYY HH24:MI')",false);
			$this->db->set('GATE_BACKDATE', $this->input->post('REASON'));
			}
		}

		//cek jika container sudah gateIn
		$cont_check = $this->db->where('GATE_NOREQ',$this->input->post('REQUEST_NO'))->where('GATE_CONT',$this->input->post('CONTAINER'))->where('GATE_BRANCH_ID',$branch_id)->where('GATE_STATUS',3)->from('TX_GATE')->count_all_results();
		if($cont_check > 0){
			return array('success' => false, 'message' => 'Container already gate Out !!');
			die();
		}

		$this->db->insert('TX_GATE');

		// if($this->input->post('ACTIVITY') == 1){
		// 	$hdr_id = $this->db->query("SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$this->input->post('REQ')."' AND STRIP_BRANCH_ID = ".$branch_id)->row_array()['STRIP_ID'];
		// 	$this->db->query("UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_STATUS = 2 WHERE  STRIP_DTL_CONT = '".$this->input->post('CONTAINER_NO')."' AND STRIP_DTL_HDR_ID = ".$hdr_id);
		// 	// $cek_jumlah_dtl = $this->db->where('STRIP_DTL_HDR_ID',$data_check->row_array()['STRIP_DTL_HDR_ID'])->from('TX_REQ_STRIP_DTL')->count_all_results();
		// 	// $cek_jumlah_out = $this->db->where('STRIP_DTL_HDR_ID',$data_check->row_array()['STRIP_DTL_HDR_ID'])->where('STRIP_DTL_STATUS','2')->from('TX_REQ_STRIP_DTL')->count_all_results();
		// 	// if($cek_jumlah_out == $cek_jumlah_dtl){
		// 	// 	$this->db->set('STRIP_STATUS',2)->where('STRIP_NO',$this->input->post('REQ'))->update('TX_REQ_STRIP_HDR');
		// 	// }
		// }
		// else{
		// 	$hdr_id = $this->db->query("SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$this->input->post('REQ')."' AND STUFF_BRANCH_ID = ".$branch_id)->row_array()['STUFF_ID'];
		// 	$this->db->query("UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_STATUS = 2 WHERE  STUFF_DTL_CONT = '".$this->input->post('CONTAINER_NO')."' AND STUFF_DTL_HDR_ID = ".$hdr_id);
		// 	// $cek_jumlah_dtl = $this->db->where('STUFF_DTL_HDR_ID',$hdr_id)->from('TX_REQ_STUFF_DTL')->count_all_results();
		// 	// $cek_jumlah_out = $this->db->where('STUFF_DTL_HDR_ID',$hdr_id)->where('STUFF_DTL_STATUS','2')->from('TX_REQ_STUFF_DTL')->count_all_results();
		// 	// if($cek_jumlah_out == $cek_jumlah_dtl){
		// 	// 	$this->db->set('STUFF_STATUS',2)->where('STUFF_NO',$this->input->post('REQ'))->update('TX_REQ_STUFF_HDR');
		// 	// }
		// }

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	function getGateInTruckStrip($activity,$status){
		$arrData = $this->db->select('GATE_TRUCK_NO AS POLICE_NO')
					  ->from('TX_GATE')
						->where('GATE_NOREQ',$_REQUEST['id'])
						->where('GATE_ACTIVITY',$activity)
						->where('GATE_STATUS',$status)
						->get()->result_array();
		return $arrData;
	}

	function getTruckJobGateManager($filter){
		$params 		= array($filter[0],$filter[1],$filter[2]);
		$paramsTotal 	= array($filter[0]);
		// $whereParams = '';
		// if($filter[3]){
		// 	$params[] = '%' . $filter[3] . '%';
		// 	$paramsTotal[] = '%' . strtolower($filter[3]) . '%';
		// 	$whereParams .= ' AND LOWER(CONTAINER_NUMBER) LIKE ?';
		// }
		//
		// if($filter[4]){
		// 	$params[] = '%' . $filter[4] . '%'	;
		// 	$paramsTotal[] = '%' . strtolower($filter[4]). '%'	;
		// 	$whereParams .= ' AND LOWER(GATE_TRUCK_NO)LIKE ?';
		// }
		//
		// if($filter[5]){
		// 	$params[] = '%' . $filter[5] . '%'	;
		// 	$paramsTotal[] = '%' . strtolower($filter[5]) . '%'	;
		// 	$whereParams .= ' AND LOWER(REQUEST_NUMBER)LIKE ?';
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
							$field = 'CREATE_DATE';
						}
						else if($field == 'GATE_IN'){
							$field = 'GATE_IN_DATE';
						}
						else if($field == 'GATE_OUT'){
							$field = 'GATE_OUT_DATE';
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

		$sql = "
			SELECT * FROM
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						(
							SELECT * FROM VIEW_TRUCK_JOB_MANAGER
						)
					) TABLE_1
					WHERE
					BRANCH_ID = ? ".$qWhere."
					AND
					ROWNUM <= ?
					ORDER BY CREATE_DATE DESC
 				)
				WHERE  rnum >= ? + 1 ";


		$data = $this->db->query($sql,$params)->result();

		$sqlTotal = " SELECT * FROM VIEW_TRUCK_JOB_MANAGER WHERE BRANCH_ID = ? " . $qWhere;

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
	}


}
