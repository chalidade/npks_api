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

	function setGateTruck(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'GATE_CONSIGNEE_NAME' => $this->input->post('CONSIGNEE_NAME'),
			'GATE_CONT' => $this->input->post('CONTAINER_NO'),
			'GATE_TRUCK_NO' => $this->input->post('POLICE_NO'),
			'GATE_CONSIGNEE_ID' => $this->input->post('CONSIGNEE_ID'),
			'GATE_CONT_SIZE' => $this->input->post('DTL_CONT_SIZE'),
			'GATE_CONT_STATUS' => $this->input->post('DTL_CONT_STATUS'),
			'GATE_CONT_TYPE' => $this->input->post('DTL_CONT_TYPE'),
			'GATE_NOREQ' => $this->input->post('REQUEST_NO'),
			'GATE_ACTIVITY' => $this->input->post('ACTIVITY'),
			'GATE_BRANCH_ID' => $branch_id,
			'GATE_STATUS' => $this->input->post('status'),
			'GATE_CREATE_BY' => $user_id
		);

		$this->db->insert('TX_GATE',$arrData);

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




}
