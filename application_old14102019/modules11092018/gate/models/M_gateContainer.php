<?php
class M_gateContainer extends CI_Model {
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

	//IN
	function getRecContNo($filter){
    $branch_id = $this->session->USER_BRANCH;
    $params = array('BRANCH_ID' => $branch_id);

		$query = "SELECT A.*, B.REFF_NAME ACTIVITY_NAME FROM(
				SELECT A.REQUEST_DTL_ID ID, B.REQUEST_NO REQ, A.REQUEST_DTL_CONT CONT, A.REQUEST_DTL_STATUS STATUS, 3 ACTIVITY, B.REQUEST_BRANCH_ID BRANCH,
				A.REQUEST_DTL_ID, A.REQUEST_HDR_ID, A.REQUEST_DTL_CONT, A.REQUEST_DTL_CONT_SIZE, C.REFF_NAME SIZE_NAME, A.REQUEST_DTL_CONT_TYPE, D.REFF_NAME TYPE_NAME, B.REQUEST_DI DI,
		    A.REQUEST_DTL_COMMODITY, A.REQUEST_DTL_CONT_STATUS, E.REFF_NAME STATUS_NAME, B.REQUEST_NO, B.REQUEST_NOTA, B.REQUEST_CONSIGNEE_ID, F.CONSIGNEE_NAME, B.REQUEST_FROM ORIGIN, A.REQUEST_DTL_OWNER_CODE, A.REQUEST_DTL_OWNER_NAME
				 FROM TX_REQ_RECEIVING_DTL A
      		JOIN TX_REQ_RECEIVING_HDR B ON A.REQUEST_HDR_ID = B.REQUEST_ID
					LEFT JOIN TM_REFF C ON A.REQUEST_DTL_CONT_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6
					LEFT JOIN TM_REFF D ON A.REQUEST_DTL_CONT_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5
					LEFT JOIN TM_REFF E ON A.REQUEST_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4
					LEFT JOIN TM_CONSIGNEE F ON B.REQUEST_CONSIGNEE_ID = F.CONSIGNEE_ID
					WHERE A.REQUEST_DTL_STATUS = 0 AND B.REQUEST_BRANCH_ID = $branch_id AND B.REQUEST_STATUS <> '2'
				UNION ALL
				-- SELECT A.REQ_DTL_ID ID, B.REQ_NO REQ, A.REQ_DTL_CONT CONT, A.REQ_DTL_STATUS STATUS, 4 ACTIVITY, B.REQ_BRANCH_ID BRANCH FROM TX_REQ_DELIVERY_DTL A
      	-- 	JOIN TX_REQ_DELIVERY_HDR B ON A.REQ_HDR_ID = B.REQ_ID
				-- 	WHERE A.REQ_DTL_STATUS = 1
				SELECT A.REQ_DTL_ID ID, B.REQ_NO REQ, A.REQ_DTL_CONT CONT, A.REQ_DTL_STATUS STATUS, 4 ACTIVITY, B.REQ_BRANCH_ID BRANCH,
				A.REQ_DTL_ID REQUEST_DTL_ID, A.REQ_HDR_ID REQUEST_HDR_ID, A.REQ_DTL_CONT REQUEST_DTL_CONT, A.REQ_DTL_CONT_SIZE REQUEST_DTL_CONT_SIZE, C.REFF_NAME SIZE_NAME, A.REQ_DTL_CONT_TYPE REQUEST_DTL_CONT_TYPE, D.REFF_NAME TYPE_NAME, '-' DI,
				A.REQ_DTL_COMMODITY REQUEST_DTL_COMMODITY, A.REQ_DTL_CONT_STATUS REQUEST_DTL_CONT_STATUS, E.REFF_NAME STATUS_NAME, B.REQ_NO REQUEST_NO, '-' REQUEST_NOTA, B.REQ_CONSIGNEE_ID REQUEST_CONSIGNEE_ID, F.CONSIGNEE_NAME, B.REQUEST_TO ORIGIN, '-' REQUEST_DTL_OWNER_CODE, '-' REQUEST_DTL_OWNER_NAME
				 FROM TX_REQ_DELIVERY_DTL A
		       JOIN TX_REQ_DELIVERY_HDR B ON A.REQ_HDR_ID = B.REQ_ID
					 LEFT JOIN TM_REFF C ON A.REQ_DTL_CONT_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6
					 LEFT JOIN TM_REFF D ON A.REQ_DTL_CONT_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5
					 LEFT JOIN TM_REFF E ON A.REQ_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4
					 JOIN TM_CONSIGNEE F ON B.REQ_CONSIGNEE_ID = F.CONSIGNEE_ID
				WHERE B.REQ_BRANCH_ID = $branch_id AND A.REQ_DTL_STATUS = 1 AND A.REQ_DTL_ACTIVE = 'Y' AND B.REQUEST_STATUS <> '2' AND (SELECT COUNT(*) FROM TX_GATE WHERE GATE_BRANCH_ID =$branch_id AND GATE_CONT = A.REQ_DTL_CONT AND GATE_NOREQ = B.REQ_NO AND GATE_STATUS = 1 AND GATE_ACTIVITY = 4) <= 0
			) A
			JOIN TM_REFF B ON B.REFF_ID = A.ACTIVITY AND B.REFF_TR_ID = 22
			WHERE A.BRANCH = $branch_id AND LOWER(A.CONT) LIKE '%".strtolower($filter)."%'
			ORDER BY A.ID";

    return $this->db->query($query,$params)->result_array();
  }

	function getRecContDoubleNo($filter){
		$cont = $_REQUEST['cont'];
		$branch_id = $this->session->USER_BRANCH;
    $params = array('BRANCH_ID' => $branch_id);
    $query = "SELECT A.REQ_DTL_ID ID, B.REQ_NO REQ, A.REQ_DTL_CONT CONT, A.REQ_DTL_STATUS STATUS, 4 ACTIVITY, C.REFF_NAME ACTIVITY_NAME, B.REQ_BRANCH_ID BRANCH FROM TX_REQ_DELIVERY_DTL A
              		JOIN TX_REQ_DELIVERY_HDR B ON A.REQ_HDR_ID = B.REQ_ID
							JOIN TM_REFF C ON C.REFF_ID = 4 AND C.REFF_TR_ID = 22
							WHERE B.REQ_BRANCH_ID = 3 AND A.REQ_DTL_STATUS = 1 AND (SELECT COUNT(*) FROM TX_GATE WHERE GATE_CONT = A.REQ_DTL_CONT AND GATE_NOREQ = B.REQ_NO AND GATE_STATUS = 1 AND GATE_ACTIVITY = 4) <= 0 AND LOWER(A.REQ_DTL_CONT) LIKE '%".strtolower($filter)."%'
  						ORDER BY A.REQ_DTL_ID";
    return $this->db->query($query,$params)->result_array();
	}

	//OUT
  function getDelContNo($filter){
    $branch_id = $this->session->USER_BRANCH;
    $params = array('BRANCH_ID' => $branch_id);
    $query = "SELECT A.REQ_DTL_ID, A.REQ_DTL_CONT FROM TX_REQ_DELIVERY_DTL A
              JOIN TX_REQ_DELIVERY_HDR B ON A.REQ_HDR_ID = B.REQ_ID
              WHERE B.REQ_BRANCH_ID = ? AND A.REQ_DTL_STATUS = 0
              AND LOWER(A.REQ_DTL_CONT) LIKE '%".strtolower($filter)."%'
          		ORDER BY A.REQ_DTL_CONT";
    return $this->db->query($query,$params)->result_array();
  }

	function getNoTruckGateOut($filter){
		$branch_id = $this->session->USER_BRANCH;
		// $data = $this->db->select('A.GATE_TRUCK_NO TRUCK_NO, A.GATE_TRUCK_NO TRUCK, A.GATE_CONT CONT_NO, A.GATE_CONT CONT, A.GATE_CONT_SIZE CONT_SIZE, A.GATE_CONT_TYPE CONT_TYPE, A.GATE_CONT_STATUS CONT_STATUS, A.GATE_CONSIGNEE_ID CONSIGNEE_ID, A.GATE_CONSIGNEE_NAME CONSIGNEE_NAME,
		// 				A.GATE_NO_SEAL SEAL_NO, A.GATE_MARK MARK, A.GATE_ACTIVITY ACTIVITY, B.REFF_NAME ACTIVITY_NAME, A.GATE_NOREQ REQ_NO, A.GATE_NOTA NOTA_NO, A.GATE_ORIGIN ORIGIN')
		// 				->from('TX_GATE A')
		// 				->join('TM_REFF B','B.REFF_ID = A.GATE_ACTIVITY AND B.REFF_TR_ID = 22')
		// 				->where('A.GATE_BRANCH_ID',$branch_id)
		// 				->where('A.GATE_STATUS',1)
		// 				->like('LOWER(A.GATE_TRUCK_NO)',strtolower($filter))
		// 				->or_like('LOWER(A.GATE_CONT)',strtolower($filter))
		// 				->get()->result_array();
		$data = $this->db->query("SELECT A.GATE_TRUCK_NO TRUCK_NO, A.GATE_TRUCK_NO TRUCK, A.GATE_CONT CONT_NO, A.GATE_CONT CONT, A.GATE_CONT_SIZE CONT_SIZE, A.GATE_CONT_TYPE CONT_TYPE, A.GATE_CONT_STATUS CONT_STATUS, A.GATE_CONSIGNEE_ID CONSIGNEE_ID, A.GATE_CONSIGNEE_NAME CONSIGNEE_NAME,
						A.GATE_NO_SEAL SEAL_NO, A.GATE_MARK MARK, A.GATE_ACTIVITY ACTIVITY, B.REFF_NAME ACTIVITY_NAME, A.GATE_NOREQ REQ_NO, A.GATE_NOTA NOTA_NO, A.GATE_ORIGIN ORIGIN,
						CASE A.GATE_ACTIVITY
						WHEN '4'
						THEN (SELECT X.REQUEST_TO FROM TX_REQ_DELIVERY_HDR X WHERE X.REQ_NO = A.GATE_NOREQ AND X.REQ_BRANCH_ID = A.GATE_BRANCH_ID)
						WHEN '3'
						THEN (SELECT Y.REQUEST_FROM FROM TX_REQ_RECEIVING_HDR Y WHERE Y.REQUEST_NO = A.GATE_NOREQ AND Y.REQUEST_BRANCH_ID = A.GATE_BRANCH_ID)
						END DESTINATION
						FROM TX_GATE A
						INNER JOIN TM_REFF B ON B.REFF_ID = A.GATE_ACTIVITY AND B.REFF_TR_ID = 22
						WHERE A.GATE_BRANCH_ID = ".$branch_id." AND A.GATE_STATUS = 1
						AND
						CASE A.GATE_ACTIVITY
						WHEN '4'
						THEN (SELECT COUNT(*) FROM TX_REQ_DELIVERY_HDR J INNER JOIN TX_REQ_DELIVERY_DTL K ON K.REQ_HDR_ID = J.REQ_ID WHERE J.REQ_NO = A.GATE_NOREQ AND K.REQ_DTL_CONT = A.GATE_CONT AND K.REQ_DTL_STATUS = 1 )
						WHEN '3'
						THEN (SELECT COUNT(*) FROM TX_REQ_RECEIVING_HDR L INNER JOIN TX_REQ_RECEIVING_DTL M ON L.REQUEST_ID = M.REQUEST_HDR_ID WHERE L.REQUEST_NO = A.GATE_NOREQ AND M.REQUEST_DTL_CONT = A.GATE_CONT AND M.REQUEST_DTL_STATUS = 1 )
						END > 0
						AND (SELECT COUNT(*) FROM TX_GATE C WHERE C.GATE_CONT = A.GATE_CONT AND C.GATE_TRUCK_NO = A.GATE_TRUCK_NO AND C.GATE_NOREQ = A.GATE_NOREQ) <= 1
						AND (LOWER(A.GATE_CONT) LIKE '%".strtolower($filter)."%' OR LOWER(A.GATE_TRUCK_NO) LIKE '%".strtolower($filter)."%')")->result_array();
		return $data;
	}

  function getGateInConById(){
    $branch_id = $this->session->USER_BRANCH;
		$activity = $this->input->post('activity');
		$cont = $this->input->post('id');
		$params = array('BRANCH_ID' => $branch_id, 'REQUEST_DTL_CONT' => $cont, 'REQUEST_STATUS' =>'2');
		if($activity == 3){
	    $query = "SELECT A.REQUEST_DTL_ID, A.REQUEST_HDR_ID, A.REQUEST_DTL_CONT, A.REQUEST_DTL_CONT_SIZE, C.REFF_NAME SIZE_NAME, A.REQUEST_DTL_CONT_TYPE, D.REFF_NAME TYPE_NAME, B.REQUEST_DI DI,
	              A.REQUEST_DTL_COMMODITY, A.REQUEST_DTL_CONT_STATUS, E.REFF_NAME STATUS_NAME, B.REQUEST_NO, B.REQUEST_NOTA, B.REQUEST_CONSIGNEE_ID, F.CONSIGNEE_NAME, B.REQUEST_FROM ORIGIN, A.REQUEST_DTL_OWNER_CODE, A.REQUEST_DTL_OWNER_NAME
	              FROM TX_REQ_RECEIVING_DTL A
	              JOIN TX_REQ_RECEIVING_HDR B ON A.REQUEST_HDR_ID = B.REQUEST_ID
	              LEFT JOIN TM_REFF C ON A.REQUEST_DTL_CONT_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6
	              LEFT JOIN TM_REFF D ON A.REQUEST_DTL_CONT_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5
	              LEFT JOIN TM_REFF E ON A.REQUEST_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4
	              LEFT JOIN TM_CONSIGNEE F ON B.REQUEST_CONSIGNEE_ID = F.CONSIGNEE_ID
	              WHERE B.REQUEST_BRANCH_ID = ? AND A.REQUEST_DTL_CONT = ? AND B.REQUEST_STATUS <> ?";
	    return $this->db->query($query,$params)->row_array();
		}
		else if($activity == 4){
			$query = "SELECT A.REQ_DTL_ID REQUEST_DTL_ID, A.REQ_HDR_ID REQUEST_HDR_ID, A.REQ_DTL_CONT REQUEST_DTL_CONT, A.REQ_DTL_CONT_SIZE REQUEST_DTL_CONT_SIZE, C.REFF_NAME SIZE_NAME, A.REQ_DTL_CONT_TYPE REQUEST_DTL_CONT_TYPE, D.REFF_NAME TYPE_NAME, '' DI,
								A.REQ_DTL_COMMODITY REQUEST_DTL_COMMODITY, A.REQ_DTL_CONT_STATUS REQUEST_DTL_CONT_STATUS, E.REFF_NAME STATUS_NAME, B.REQ_NO REQUEST_NO, '-' REQUEST_NOTA, B.REQ_CONSIGNEE_ID REQUEST_CONSIGNEE_ID, F.CONSIGNEE_NAME, B.REQUEST_TO ORIGIN
								FROM TX_REQ_DELIVERY_DTL A
								JOIN TX_REQ_DELIVERY_HDR B ON A.REQ_HDR_ID = B.REQ_ID
								LEFT JOIN TM_REFF C ON A.REQ_DTL_CONT_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6
								LEFT JOIN TM_REFF D ON A.REQ_DTL_CONT_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5
								LEFT JOIN TM_REFF E ON A.REQ_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4
								JOIN TM_CONSIGNEE F ON B.REQ_CONSIGNEE_ID = F.CONSIGNEE_ID
								WHERE B.REQ_BRANCH_ID = ? AND A.REQ_DTL_CONT = ? AND B.REQUEST_STATUS <> ? AND A.REQ_DTL_ACTIVE ='Y' ";
			return $this->db->query($query,$params)->row_array();
		}
  }

	function getGateOutConById(){
    $branch_id = $this->session->USER_BRANCH;
    $params = array('BRANCH_ID' => $branch_id, 'REQUEST_DTL_CONT' => $_POST['id']);
    $query = "SELECT A.REQ_DTL_ID, A.REQ_HDR_ID, A.REQ_DTL_CONT, A.REQ_DTL_CONT_SIZE, C.REFF_NAME SIZE_NAME, A.REQ_DTL_CONT_TYPE, D.REFF_NAME TYPE_NAME,
              A.REQ_DTL_COMMODITY, A.REQ_DTL_CONT_STATUS, E.REFF_NAME STATUS_NAME, B.REQ_NO, B.REQ_CONSIGNEE_ID, F.CONSIGNEE_NAME
              FROM TX_REQ_DELIVERY_DTL A
              JOIN TX_REQ_DELIVERY_HDR B ON A.REQ_HDR_ID = B.REQ_ID
              LEFT JOIN TM_REFF C ON A.REQ_DTL_CONT_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6
              LEFT JOIN TM_REFF D ON A.REQ_DTL_CONT_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5
              LEFT JOIN TM_REFF E ON A.REQ_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4
              JOIN TM_CONSIGNEE F ON B.REQ_CONSIGNEE_ID = F.CONSIGNEE_ID
              WHERE B.REQ_BRANCH_ID = ? AND A.REQ_DTL_ID = ?";
    return $this->db->query($query,$params)->result_array();
  }

	function ownerCheck(){
		// check planning existing
		$message = true;
		$branch_id = $this->session->USER_BRANCH;
		$owner = $this->input->post('REQUEST_DTL_OWNER_CODE');
		$planningOwner = $this->db->select('A.CAT_DTL_OWNER')->from('TX_CATEGORY_DTL A')->join('TX_CATEGORY_HDR B','B.CAT_HDR_ID = A.CAT_HDR_ID')->where('CAT_BRANCH_ID',$branch_id)->where('CAT_DTL_OWNER',$owner)->count_all_results();
		if($planningOwner <= 0){
			$message =  false;
		}
		return array('success' => true, 'message' => $message);
	}

  function setGateInContainer(){
    $branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
    $message =  'SUKSES';
		$sukses = true;
    $this->db->trans_start();
		$date1 = date('Y-m-d H:i:s');
		$arrGate = array();
		$gate_back_date = null;

    $arrData = array(
      'GATE_CONSIGNEE_NAME' => $this->input->post('CONSIGNEE_NAME'),
      'GATE_CONT' => $this->input->post('CONTAINER'),
      'GATE_MARK' => $this->input->post('MARK'),
      'GATE_ORIGIN' => $this->input->post('ORIGIN'),
      'GATE_TRUCK_NO' => $this->input->post('POLICE_NO'),
      'GATE_CONSIGNEE_ID' => $this->input->post('REQUEST_CONSIGNEE_ID'),
      'GATE_CONT_SIZE' => $this->input->post('REQUEST_DTL_CONT_SIZE'),
      'GATE_CONT_STATUS' => $this->input->post('REQUEST_DTL_CONT_STATUS'),
      'GATE_CONT_TYPE' => $this->input->post('REQUEST_DTL_CONT_TYPE'),
      'GATE_NOREQ' => $this->input->post('REQUEST_NO'),
      'GATE_NOTA' => $this->input->post('REQUEST_NOTA'),
      'GATE_NO_SEAL' => $this->input->post('SEAL_NO'),
      'GATE_ACTIVITY' => $this->input->post('ACTIVITY'),
      'GATE_BRANCH_ID' => $branch_id,
      'GATE_STATUS' => $this->input->post('status'),
			'GATE_CREATE_BY' => $user_id
    );

		$this->db->set('GATE_CONSIGNEE_NAME', $this->input->post('CONSIGNEE_NAME'));
		$this->db->set('GATE_CONT', $this->input->post('CONTAINER'));
		$this->db->set('GATE_MARK', $this->input->post('MARK'));
		$this->db->set('GATE_ORIGIN', $this->input->post('ORIGIN'));
		$this->db->set('GATE_TRUCK_NO', $this->input->post('POLICE_NO'));
		$this->db->set('GATE_CONSIGNEE_ID', $this->input->post('REQUEST_CONSIGNEE_ID'));
		$this->db->set('GATE_CONT_SIZE', $this->input->post('REQUEST_DTL_CONT_SIZE'));
		$this->db->set('GATE_CONT_STATUS', $this->input->post('REQUEST_DTL_CONT_STATUS'));
		$this->db->set('GATE_CONT_TYPE', $this->input->post('REQUEST_DTL_CONT_TYPE'));
		$this->db->set('GATE_NOREQ', $this->input->post('REQUEST_NO'));
		$this->db->set('GATE_NOTA', $this->input->post('REQUEST_NOTA'));
		$this->db->set('GATE_NO_SEAL', $this->input->post('SEAL_NO'));
		$this->db->set('GATE_ACTIVITY', $this->input->post('ACTIVITY'));
		$this->db->set('GATE_BRANCH_ID', $branch_id);
		$this->db->set('GATE_STATUS', $this->input->post('status'));
		$this->db->set('GATE_CREATE_BY', $user_id);

		if(isset($_POST['GATE_IN_DATE'])){
			if($_POST['GATE_IN_DATE'] != 'Date' && isset($_POST['GATE_IN_TIME'])){
			$gate_back_date = $this->input->post('GATE_IN_DATE').' '.$this->input->post('GATE_IN_TIME');
			$this->db->set('GATE_CREATE_DATE', "to_date('$gate_back_date','DD/MM/YYYY HH24:MI')",false);
			$this->db->set('GATE_BACKDATE', $this->input->post('REASON'));
			}
		}
		$this->db->insert('TX_GATE');
    // $this->db->insert('TX_GATE',$arrData);


    	if($this->input->post('ACTIVITY') == 4 || ($this->input->post('CHECK_CONT') == true AND $this->input->post('DEL_CONT') != '' AND $this->input->post('DEL_REQ') != '') ){
    		$paramsTimeThru[] = $this->input->post('CONTAINER');

    		if($this->input->post('CHECK_CONT') == true AND $this->input->post('DEL_CONT') != '' AND $this->input->post('DEL_REQ') != ''){
    			$paramsTimeThru[] = $this->input->post('DEL_REQ');
    		}
    		else{
    			$paramsTimeThru[] = $this->input->post('REQUEST_NO');
    		}
    		$sql = "SELECT to_char(A.REQ_DTL_DEL_DATE, 'YYYY-MM-DD HH24:MI:SS') TIME_THRU
    					FROM TX_REQ_DELIVERY_DTL A
    					INNER JOIN TX_REQ_DELIVERY_HDR B
    					ON B.REQ_ID = A.REQ_HDR_ID
    					WHERE A.REQ_DTL_CONT = ?
    					AND B.REQ_NO = ?
    					AND REQ_DTL_ACTIVE = 'Y'";

    		$dataTime = $this->db->query($sql,$paramsTimeThru)->row();
    		if(count($dataTime) > 0){
    			if(strtotime($dataTime->TIME_THRU) < strtotime($date1)){
	    			return array('success' => false, 'message' => 'Silahkan Melakukan Perpanjangan Paid Delivery');
	    			die();
	    		}
    		}
    		else{
    			return array('success' => false, 'message' => 'Container Tidak ditemukan');
	    		die();
    		}


    	}
		if($this->input->post('ACTIVITY') == 3){
			$this->db->set('REQUEST_DTL_STATUS',1);
			$this->db->where('REQUEST_DTL_ID',$this->input->post('REQUEST_DTL_ID'));
			$this->db->update('TX_REQ_RECEIVING_DTL');

			// gate request date
			$req_id = $this->input->post('REQUEST_NO');
			$req_date = $this->db->query("SELECT TO_CHAR(REQUEST_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') REQ_DATE FROM TX_REQ_RECEIVING_HDR
										WHERE REQUEST_BRANCH_ID = ".$branch_id." AND REQUEST_NO = '".$req_id."'")->row_array()['REQ_DATE'];

			//insert history container
			//ADD_HISTORY_CONTAINER(CONT_NO, REQ_NO, REQ_DATE, CONT_SIZE, CONT_YARD, CONT_BLOCK, CONT_SLOT, CONT_ROW, CONT_TIER, CONT_ACTIVITY_ID, CONT_ACTIVITY_NAME, BRANCH)
			if(isset($_POST['GATE_IN_DATE']) && $_POST['GATE_IN_DATE'] != 'Date' && isset($_POST['GATE_IN_TIME'])) {
				$gate_back_date = $this->input->post('GATE_IN_DATE').' '.$this->input->post('GATE_IN_TIME');
				$this->db->query("CALL ADD_HISTORY_CONTAINER(
						'".$this->input->post('CONTAINER')."',
						'".$req_id."',
						'".$req_date."',
						'".$this->input->post('REQUEST_DTL_CONT_SIZE')."',
						'".$this->input->post('REQUEST_DTL_CONT_TYPE')."',
						'".$this->input->post('REQUEST_DTL_CONT_STATUS')."',
						NULL,
						NULL,
						NULL,
						NULL,
						NULL,
						1,
						'GATE IN',
						'".$gate_back_date."',
						".$branch_id.")");
			 }
			 else{
				 $this->db->query("CALL ADD_HISTORY_CONTAINER(
 						'".$this->input->post('CONTAINER')."',
 						'".$req_id."',
 						'".$req_date."',
 						'".$this->input->post('REQUEST_DTL_CONT_SIZE')."',
 						'".$this->input->post('REQUEST_DTL_CONT_TYPE')."',
 						'".$this->input->post('REQUEST_DTL_CONT_STATUS')."',
 						NULL,
 						NULL,
 						NULL,
 						NULL,
 						NULL,
 						1,
 						'GATE IN',
 						NULL,
 						".$branch_id.")");
			 }

				//crate array for print
				$cont_size = $this->input->post('REQUEST_DTL_CONT_SIZE');
				$cont_status = $this->input->post('REQUEST_DTL_CONT_STATUS');
				$cont_type = $this->input->post('REQUEST_DTL_CONT_TYPE');
				$di = $this->input->post('DI');
				$owner = $this->input->post('REQUEST_DTL_OWNER_CODE');
				$yard_active = $this->session->YARD_ACTIVE;

				// cek owner existing in planning
				$ownerExist = $this->db->select('A.CAT_DTL_OWNER')->from('TX_CATEGORY_DTL A')->join('TX_CATEGORY_HDR B','B.CAT_HDR_ID = A.CAT_HDR_ID')->where('CAT_BRANCH_ID',$branch_id)->where('CAT_DTL_OWNER',$owner)->count_all_results();
				$arrConfing = $this->db->select('CONFIG, STATUS, DETAIL')->from('TM_CMS_CONFIG')->where('BRANCH',$branch_id)->where('STATUS','Y')->get()->result_array();
				$where = '1=1 AND B.CAT_BRANCH_ID = '.$branch_id.' AND C.YPG_YARD_ID = '.$yard_active;
				foreach ($arrConfing as $key => $val) {
						if($val['DETAIL'] == 'SIZE'){
							$where .= " AND A.".$val['CONFIG']."='".$cont_size."'";
						}
						else if($val['DETAIL'] == 'TYPE'){
							 $where .= " AND A.".$val['CONFIG']."='".$cont_type."'";
						}
						else if($val['DETAIL'] == 'STATUS'){
							 $where .= " AND A.".$val['CONFIG']."='".$cont_status."'";
						}
						// else if($val['DETAIL'] == 'D/I'){
						// 	 $where .= " AND A.".$val['CONFIG']."='".$di."'";
						// }
							else if($val['DETAIL'] == 'OWNER'){
								if($ownerExist > 0){
								 	$where .= " AND A.".$val['CONFIG']."='".$owner."'";
								}
						}
				}

					$gate_time = $this->db->select("TO_CHAR(GATE_CREATE_DATE,'DD-MM-YYYY HH24:MI:SS') GATE_DATE")->from('TX_GATE')
											 ->where('GATE_NOREQ',$this->input->post('REQUEST_NO'))
											 ->where('GATE_CONT',$this->input->post('CONTAINER'))
											 ->where('GATE_TRUCK_NO',$this->input->post('POLICE_NO'))
											 ->where('GATE_STATUS',1)->get()->row_array()['GATE_DATE'];

					$gate_activity = $this->db->select('REFF_NAME')->where('REFF_TR_ID',22)->where('REFF_ID',$this->input->post('ACTIVITY'))->get('TM_REFF')->row_array()['REFF_NAME'];

					$lokasi = $this->db->query("SELECT B.CAT_HDR_ID, A.CAT_DTL_CONT_SIZE CONT, A.CAT_DTL_CONT_STATUS, A.CAT_DTL_CONT_TYPE, A.CAT_DTL_EXIM, C.YPG_YARD_ID, C.YPG_BLOCK_ID, C.YPG_STAR_ROW, C.YPG_END_ROW, C.YPG_START_SLOT, C.YPG_END_SLOT, C.YPG_CAPACITY, '' DI, D.BLOCK_NAME, E.YARD_NAME
												FROM TX_CATEGORY_DTL A
												JOIN TX_CATEGORY_HDR B ON B.CAT_HDR_ID = A.CAT_HDR_ID
												JOIN TX_YARD_PLAN_GROUP C ON C.YPG_CAT_HDR_ID = B.CAT_HDR_ID
												JOIN TM_BLOCK D ON D.BLOCK_ID = C.YPG_BLOCK_ID AND D.BLOCK_ACTIVE = 'Y'
												JOIN TM_YARD E ON E.YARD_ID = C.YPG_YARD_ID
												WHERE ".$where)->result_array();

					foreach ($lokasi as $loc) {
						// cek capacity block
						$stack_capacity = $this->db->query("SELECT COUNT(*) STACK_CAPACITY  FROM ( SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H  GROUP BY H.REAL_YARD_CONT) X
									 INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID
									 INNER JOIN TX_YARD_BLOCK_CELL J ON J.YBC_ID = I.REAL_YARD_YBC_ID
									 INNER JOIN TM_BLOCK K ON K.BLOCK_ID = J.YBC_BLOCK_ID
									 WHERE J.YBC_ACTIVE = 'Y' AND K.BLOCK_ACTIVE = 'Y' AND I.REAL_YARD_STATUS = 1 AND J.YBC_BLOCK_ID = '".$loc['YPG_BLOCK_ID']."'")->row_array()['STACK_CAPACITY'];

						// if($stack_capacity < $loc['YPG_CAPACITY']){
							$slot = $loc['YPG_START_SLOT'].' - '.$loc['YPG_END_SLOT'];
							$row = $loc['YPG_STAR_ROW'].' - '.$loc['YPG_END_ROW'];
							$arrGate[0]['GATE_TIME'] = $gate_time;
							$arrGate[0]['NOREQ'] = $this->input->post('REQUEST_NO');
							$arrGate[0]['TRUCK_NO'] = $this->input->post('POLICE_NO');
							$arrGate[0]['SEAL_NO'] = $this->input->post('SEAL_NO');
							$arrGate[0]['CONTAINER'] = $this->input->post('CONTAINER');
							$arrGate[0]['ACTIVITY'] = $gate_activity;
							$arrGate[0]['ORIGIN'] = $this->input->post('ORIGIN');
							$arrGate[0]['SIZE'] = $this->input->post('REQUEST_DTL_CONT_SIZE');
							$arrGate[0]['TYPE'] = $this->input->post('REQUEST_DTL_CONT_TYPE');
							$arrGate[0]['STATUS'] = $this->input->post('REQUEST_DTL_CONT_STATUS');
							$arrGate[0]['YARD_NAME'] = $loc['YARD_NAME'];
							$arrGate[0]['BLOCK_NAME'] = $loc['BLOCK_NAME'];
							$arrGate[0]['SLOT'] = $slot;
							$arrGate[0]['ROW'] = '-';
							$arrGate[0]['TIER'] = '-';
							$arrGate[0]['REMARK'] = $this->input->post('MARK');
							break;
						// }
					}
		}

		if($this->input->post('CHECK_CONT') == true AND $this->input->post('DEL_CONT') != '' AND $this->input->post('DEL_REQ') != ''){
			$params = array('BRANCH_ID' => $branch_id, 'REQUEST_DTL_CONT' => $this->input->post('DEL_CONT'), 'REQ_NO' => $this->input->post('DEL_REQ'));
			if(isset($_POST['GATE_IN_DATE']) && $_POST['GATE_IN_DATE'] != 'Date' && isset($_POST['GATE_IN_TIME'])) {
				$gate_back_date = $this->input->post('GATE_IN_DATE').' '.$this->input->post('GATE_IN_TIME');
				$query = "SELECT B.REQ_NO GATE_NOREQ, A.REQ_DTL_CONT GATE_CONT, A.REQ_DTL_CONT_SIZE GATE_CONT_SIZE, A.REQ_DTL_CONT_TYPE GATE_CONT_TYPE,
									'".$this->input->post('POLICE_NO')."' GATE_TRUCK_NO, A.REQ_DTL_NO_SEAL GATE_NO_SEAL, 4 GATE_ACTIVITY, ".$branch_id." GATE_BRANCH_ID, 1 GATE_STATUS, ".$user_id." GATE_CREATE_BY, TO_DATE('".$gate_back_date."','DD/MM/YYYY HH24:MI') GATE_CREATE_DATE,
									A.REQ_DTL_CONT_STATUS GATE_CONT_STATUS, B.REQ_NO GATE_NOREQ, '-' GATE_NOTA, B.REQ_CONSIGNEE_ID GATE_CONSIGNEE_ID, '".$this->input->post('CONSIGNEE_NAME')."' GATE_CONSIGNEE_NAME, B.REQUEST_TO GATE_ORIGIN, '".$_POST['REASON']."' AS GATE_BACKDATE
									FROM TX_REQ_DELIVERY_DTL A
									JOIN TX_REQ_DELIVERY_HDR B ON A.REQ_HDR_ID = B.REQ_ID
									WHERE B.REQ_BRANCH_ID = ? AND A.REQ_DTL_CONT = ? AND B.REQ_NO = ?";
			}
			else{
				$query = "SELECT B.REQ_NO GATE_NOREQ, A.REQ_DTL_CONT GATE_CONT, A.REQ_DTL_CONT_SIZE GATE_CONT_SIZE, A.REQ_DTL_CONT_TYPE GATE_CONT_TYPE,
									'".$this->input->post('POLICE_NO')."' GATE_TRUCK_NO, A.REQ_DTL_NO_SEAL GATE_NO_SEAL, 4 GATE_ACTIVITY, ".$branch_id." GATE_BRANCH_ID, 1 GATE_STATUS, ".$user_id." GATE_CREATE_BY,
									A.REQ_DTL_CONT_STATUS GATE_CONT_STATUS, B.REQ_NO GATE_NOREQ, '-' GATE_NOTA, B.REQ_CONSIGNEE_ID GATE_CONSIGNEE_ID, '".$this->input->post('CONSIGNEE_NAME')."' GATE_CONSIGNEE_NAME, B.REQUEST_TO GATE_ORIGIN
									FROM TX_REQ_DELIVERY_DTL A
									JOIN TX_REQ_DELIVERY_HDR B ON A.REQ_HDR_ID = B.REQ_ID
									WHERE B.REQ_BRANCH_ID = ? AND A.REQ_DTL_CONT = ? AND B.REQ_NO = ?";
			}
			$del_cont = $this->db->query($query,$params);
			if($del_cont->num_rows() > 0){
				$this->db->insert('TX_GATE',$del_cont->row_array());
				$this->db->query("UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_STATUS = 1 WHERE  REQ_DTL_CONT = '".$this->input->post('DEL_CONT')."' AND REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$this->input->post('DEL_REQ')."' AND REQ_BRANCH_ID = ".$branch_id.")");

				// crate array for print delivery
				$cont_double = $del_cont->row_array()['GATE_CONT'];
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
							 WHERE REAL_YARD_CONT = '".$cont_double."'")->row_array();

								 $gate_time = $this->db->select("TO_CHAR(GATE_CREATE_DATE,'DD-MM-YYYY HH24:MI:SS') GATE_DATE")->from('TX_GATE')
															->where('GATE_NOREQ',$this->input->post('DEL_REQ'))
															->where('GATE_CONT',$this->input->post('DEL_CONT'))
															->where('GATE_TRUCK_NO',$this->input->post('POLICE_NO'))
															->where('GATE_STATUS',1)->get()->row_array()['GATE_DATE'];

								 $gate_activity = $this->db->select('REFF_NAME')->where('REFF_TR_ID',22)->where('REFF_ID',4)->get('TM_REFF')->row_array()['REFF_NAME'];

										 $arrGate[1]['GATE_TIME'] = $gate_time;
										 $arrGate[1]['NOREQ'] = $del_cont->row_array()['GATE_NOREQ'];
										 $arrGate[1]['TRUCK_NO'] = $this->input->post('POLICE_NO');
										 $arrGate[1]['SEAL_NO'] = $del_cont->row_array()['GATE_NO_SEAL'] == null? '-' : $del_cont->row_array()['GATE_NO_SEAL'];
										 $arrGate[1]['CONTAINER'] = $del_cont->row_array()['GATE_CONT'];
										 $arrGate[1]['ACTIVITY'] = $gate_activity;
										 $arrGate[1]['ORIGIN'] = $del_cont->row_array()['GATE_ORIGIN'];
										 $arrGate[1]['SIZE'] = $del_cont->row_array()['GATE_CONT_SIZE'];
										 $arrGate[1]['TYPE'] = $del_cont->row_array()['GATE_CONT_TYPE'];
										 $arrGate[1]['STATUS'] = $del_cont->row_array()['GATE_CONT_STATUS'];
										 $arrGate[1]['YARD_NAME'] = $cont_in_yard['YARD'];
										 $arrGate[1]['BLOCK_NAME'] = $cont_in_yard['BLOCK_'];
										 $arrGate[1]['SLOT'] = $cont_in_yard['SLOT_'];
										 $arrGate[1]['ROW'] = $cont_in_yard['ROW_'];
										 $arrGate[1]['TIER'] = $cont_in_yard['TIER_'];
										 $arrGate[1]['REMARK'] = '-';
				// end create print delivery
			}

		}

		if($this->input->post('ACTIVITY') == 4 AND $this->input->post('DEL_REQ') == ''){
			$this->db->set('REQ_DTL_STATUS',1);
			$this->db->where('REQ_DTL_ID',$this->input->post('REQUEST_DTL_ID'));
			$this->db->update('TX_REQ_DELIVERY_DTL');

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
						 WHERE REAL_YARD_CONT = '".$this->input->post('CONTAINER')."'")->row_array();

							 $gate_time = $this->db->select("TO_CHAR(GATE_CREATE_DATE,'DD-MM-YYYY HH24:MI:SS') GATE_DATE")->from('TX_GATE')
														->where('GATE_NOREQ',$this->input->post('REQUEST_NO'))
														->where('GATE_CONT',$this->input->post('CONTAINER'))
														->where('GATE_TRUCK_NO',$this->input->post('POLICE_NO'))
														->where('GATE_STATUS',1)->get()->row_array()['GATE_DATE'];

							 $gate_activity = $this->db->select('REFF_NAME')->where('REFF_TR_ID',22)->where('REFF_ID',4)->get('TM_REFF')->row_array()['REFF_NAME'];

									 $arrGate[0]['GATE_TIME'] = $gate_time;
									 $arrGate[0]['NOREQ'] = $this->input->post('REQUEST_NO');
									 $arrGate[0]['TRUCK_NO'] = $this->input->post('POLICE_NO');
									 $arrGate[0]['SEAL_NO'] = $this->input->post('SEAL_NO');
									 $arrGate[0]['CONTAINER'] = $this->input->post('CONTAINER');
									 $arrGate[0]['ACTIVITY'] = $gate_activity;
									 $arrGate[0]['ORIGIN'] = $this->input->post('ORIGIN');
									 $arrGate[0]['SIZE'] = $this->input->post('REQUEST_DTL_CONT_SIZE');
									 $arrGate[0]['TYPE'] = $this->input->post('REQUEST_DTL_CONT_TYPE');
									 $arrGate[0]['STATUS'] = $this->input->post('REQUEST_DTL_CONT_STATUS');
									 $arrGate[0]['YARD_NAME'] = $cont_in_yard['YARD'];
									 $arrGate[0]['BLOCK_NAME'] = $cont_in_yard['BLOCK_'];
									 $arrGate[0]['SLOT'] = $cont_in_yard['SLOT_'];
									 $arrGate[0]['ROW'] = $cont_in_yard['ROW_'];
									 $arrGate[0]['TIER'] = $cont_in_yard['TIER_'];
									 $arrGate[0]['REMARK'] = $this->input->post('MARK');
			// end create print delivery

		}

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE)
    {
        $message = 'FAILED';
				$sukses = false;
    }

    return array('success' => $sukses, 'message' => $message, 'data' => $arrGate);
  }

	function setGateOutContainer(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$message =  'SUKSES';
		$gate_back_date = null;
		$this->db->trans_start();

		$arrData = array(
			'GATE_CONSIGNEE_NAME' => $this->input->post('CONSIGNEE_NAME'),
			'GATE_CONT' => $this->input->post('CONT_NO'),
			'GATE_MARK' => $this->input->post('MARK'),
			'GATE_ORIGIN' => $this->input->post('DESTINATION'),
			'GATE_TRUCK_NO' => $this->input->post('TRUCK_NO'),
			'GATE_CONSIGNEE_ID' => $this->input->post('CONSIGNEE_ID'),
			'GATE_CONT_SIZE' => $this->input->post('CONT_SIZE'),
			'GATE_CONT_STATUS' => $this->input->post('CONT_STATUS'),
			'GATE_CONT_TYPE' => $this->input->post('CONT_TYPE'),
			'GATE_NOREQ' => $this->input->post('REQ_NO'),
			'GATE_NO_SEAL' => $this->input->post('SEAL_NO'),
			'GATE_ACTIVITY' => $this->input->post('ACTIVITY'),
			'GATE_NOTA' => $this->input->post('NOTA_NO'),
			'GATE_BRANCH_ID' => $branch_id,
			'GATE_STATUS' => $this->input->post('status'),
			'GATE_STACK_STATUS' => 2,
			'GATE_CREATE_BY' => $user_id
		);

		$CONT = $this->input->post('CONT_NO');
		$REQ = $this->input->post('REQ_NO');
		$MARK = $this->input->post('MARK');
		$stack = '';
		if($this->input->post('ACTIVITY') == 3){
			$stack = 1;
		}
		else if($this->input->post('ACTIVITY') == 4){
			$stack = 2;
		}

			$data1 = $this->db->select("GATE_CONSIGNEE_NAME, GATE_CONT, '".$MARK."' AS GATE_MARK, GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_NOREQ, GATE_NO_SEAL, GATE_CONT_STATUS,
			   		  GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, ".$stack." GATE_STACK_STATUS, 3 GATE_STATUS, ".$user_id." GATE_CREATE_BY")
							->from('TX_GATE')
							->where('GATE_BRANCH_ID',$branch_id)
							->where('GATE_STATUS',1)
							->where('GATE_CONT',$this->input->post('CONT_NO'))
							->where('GATE_NOREQ',$this->input->post('REQ_NO'))
							->get();

		if($data1->num_rows() > 0){
			if(isset($_POST['GATE_IN_DATE']) && $_POST['GATE_IN_DATE'] != 'Date' && isset($_POST['GATE_IN_TIME'])) {
				$gate_back_date = $this->input->post('GATE_IN_DATE').' '.$this->input->post('GATE_IN_TIME');
			$this->db->query("INSERT INTO TX_GATE (GATE_CONSIGNEE_NAME,GATE_CONT,GATE_MARK,GATE_ORIGIN,GATE_TRUCK_NO,GATE_CONSIGNEE_ID,GATE_CONT_SIZE,GATE_CONT_TYPE,GATE_NOREQ,GATE_NO_SEAL,GATE_CONT_STATUS, GATE_ACTIVITY,GATE_NOTA,GATE_BRANCH_ID,GATE_STACK_STATUS,GATE_STATUS,GATE_CREATE_BY,GATE_CREATE_DATE, GATE_BACKDATE)
			SELECT GATE_CONSIGNEE_NAME, GATE_CONT, '".$MARK."' AS GATE_MARK, GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_NOREQ, GATE_NO_SEAL, GATE_CONT_STATUS,
			   		  GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, ".$stack." GATE_STACK_STATUS, 3 GATE_STATUS, ".$user_id." GATE_CREATE_BY, TO_DATE('".$gate_back_date."','DD/MM/YYYY HH24:MI') AS GATE_CREATE_DATE, '".$_POST['REASON']."' AS GATE_BACKDATE
							FROM TX_GATE
							WHERE GATE_BRANCH_ID = ".$branch_id." AND
							GATE_STATUS = 1 AND
							GATE_CONT = '".$this->input->post('CONT_NO')."' AND
							GATE_NOREQ = '".$this->input->post('REQ_NO')."'");
			}
			else{
				$this->db->query("INSERT INTO TX_GATE (GATE_CONSIGNEE_NAME,GATE_CONT,GATE_MARK,GATE_ORIGIN,GATE_TRUCK_NO,GATE_CONSIGNEE_ID,GATE_CONT_SIZE,GATE_CONT_TYPE,GATE_NOREQ,GATE_NO_SEAL,GATE_CONT_STATUS, GATE_ACTIVITY,GATE_NOTA,GATE_BRANCH_ID,GATE_STACK_STATUS,GATE_STATUS,GATE_CREATE_BY)
				SELECT GATE_CONSIGNEE_NAME, GATE_CONT, '".$MARK."' AS GATE_MARK, GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_NOREQ, GATE_NO_SEAL, GATE_CONT_STATUS,
				   		  GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, ".$stack." GATE_STACK_STATUS, 3 GATE_STATUS, ".$user_id." GATE_CREATE_BY
								FROM TX_GATE
								WHERE GATE_BRANCH_ID = ".$branch_id." AND
								GATE_STATUS = 1 AND
								GATE_CONT = '".$this->input->post('CONT_NO')."' AND
								GATE_NOREQ = '".$this->input->post('REQ_NO')."'");
			}

			// $this->db->insert('TX_GATE',$data1->row_array());
			if($this->input->post('ACTIVITY') == 3){
				// $this->db->query('UPDATE TX_REQ_RECEIVING_DTL SET REQUEST_DTL_STATUS = 1 WHERE  REQUEST_DTL_CONT = "$CONT" AND REQUEST_HDR_ID = (SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO = "$REQ" AND REQUEST_BRANCH_ID = "$branch_id")');
			}
			else if($this->input->post('ACTIVITY') == 4){
				$this->db->query("UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_STATUS = 2 WHERE REQ_DTL_CONT = '".$CONT."' AND REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$REQ."' AND REQ_BRANCH_ID = ".$branch_id.")");
				//check detail request delivery telah gateout
				$req_id = $this->db->query("SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$REQ."' AND REQ_BRANCH_ID = '".$branch_id."'")->row_array();
				$cek_jumlah_dtl = $this->db->where('REQ_HDR_ID',$req_id['REQ_ID'])->from('TX_REQ_DELIVERY_DTL')->count_all_results();
				$cek_jumlah_out = $this->db->where('REQ_HDR_ID',$req_id['REQ_ID'])->where('REQ_DTL_STATUS','2')->from('TX_REQ_DELIVERY_DTL')->count_all_results();
				if($cek_jumlah_out == $cek_jumlah_dtl){
					$this->db->set('REQUEST_STATUS',2)->where('REQ_ID',$req_id['REQ_ID'])->update('TX_REQ_DELIVERY_HDR');
				}
				// update yard stacking
				$cek_yard = $this->db->query("SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS,
										'".$this->input->post('REQ_NO')."' AS REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY
                    FROM TX_REAL_YARD
                    WHERE REAL_YARD_CONT = '".$this->input->post('CONT_NO')."' AND REAL_YARD_BRANCH_ID = ".$branch_id."
                    AND REAL_YARD_CREATE_DATE = (SELECT MAX(A.REAL_YARD_CREATE_DATE) FROM TX_REAL_YARD A WHERE A.REAL_YARD_CONT = '".$this->input->post('CONT_NO')."' ) ")->row_array();

				$this->db->insert('TX_REAL_YARD',$cek_yard);

				// gate request date
				$req_date = $this->db->query("SELECT TO_CHAR(REQ_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') REQ_DATE FROM TX_REQ_DELIVERY_HDR
											WHERE REQ_BRANCH_ID = ".$branch_id." AND REQ_NO = '".$REQ."'")->row_array()['REQ_DATE'];

				// insert history container
				//ADD_HISTORY_CONTAINER(CONT_NO, REQ_NO, REQ_DATE, CONT_SIZE, CONT_YARD, CONT_BLOCK, CONT_SLOT, CONT_ROW, CONT_TIER, CONT_ACTIVITY_ID, CONT_ACTIVITY_NAME, BRANCH)
				if(isset($_POST['GATE_IN_DATE']) && $_POST['GATE_IN_DATE'] != 'Date' && isset($_POST['GATE_IN_TIME'])) {
					$gate_back_date = $this->input->post('GATE_IN_DATE').' '.$this->input->post('GATE_IN_TIME');
					$this->db->query("CALL ADD_HISTORY_CONTAINER(
							'".$CONT."',
							'".$REQ."',
							'".$req_date."',
							'".$this->input->post('CONT_SIZE')."',
							'".$this->input->post('CONT_TYPE')."',
							'".$this->input->post('CONT_STATUS')."',
							NULL,
							NULL,
							NULL,
							NULL,
							NULL,
							3,
							'GATE OUT',
							'".$gate_back_date."',
							".$branch_id.")");
				}
				else{
					$this->db->query("CALL ADD_HISTORY_CONTAINER(
							'".$CONT."',
							'".$REQ."',
							'".$req_date."',
							'".$this->input->post('CONT_SIZE')."',
							'".$this->input->post('CONT_TYPE')."',
							'".$this->input->post('CONT_STATUS')."',
							NULL,
							NULL,
							NULL,
							NULL,
							NULL,
							3,
							'GATE OUT',
							NULL,
							".$branch_id.")");
				}
			}
		}

		// check truk jika masih ada job lain.
		//start check
		$data2 = $this->db->select('GATE_CONT, GATE_NOREQ, GATE_ACTIVITY')
						->where('GATE_BRANCH_ID',$branch_id)
						->where('GATE_TRUCK_NO',$this->input->post('TRUCK_NO'))
						->where('GATE_NOREQ',$this->input->post('REQ_NO'))
						->where('GATE_STATUS',1)
						->where('GATE_ACTIVITY !=',$this->input->post('ACTIVITY'))
						->get('TX_GATE');

	if($data2->num_rows() > 0){
		foreach ($data2->result_array() as $val) {
			// job receiving
			if($val['GATE_ACTIVITY'] == 3){
					$data3 = $this->db->from('TX_GATE A')
								->join('TX_REQ_RECEIVING_HDR B','B.REQUEST_NO = A.GATE_NOREQ')
								->join('TX_REQ_RECEIVING_DTL C','C.REQUEST_HDR_ID = B.REQUEST_ID')
								->where('C.REQUEST_DTL_CONT',$val['GATE_CONT'])
								->where('B.REQUEST_NO',$val['GATE_NOREQ'])
								->where('A.GATE_BRANCH_ID',$branch_id)
								->count_all_results();
				if($data3 > 0){

					//input data gate out
						$data4 = $this->db->select('GATE_CONSIGNEE_NAME, GATE_CONT, GATE_MARK, GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_NOREQ, GATE_NO_SEAL, GATE_CONT_STATUS,
										GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, '.$stack.' GATE_STACK_STATUS, 3 GATE_STATUS, '.$user_id.' GATE_CREATE_BY')
										->from('TX_GATE')
										->where('GATE_BRANCH_ID',$branch_id)
										->where('GATE_STATUS',1)
										->where('GATE_CONT',$val['GATE_CONT'])
										->where('GATE_NOREQ',$val['GATE_NOREQ'])
										->get();

					if($data4->num_rows() > 0){
						if(isset($_POST['GATE_IN_DATE']) && $_POST['GATE_IN_DATE'] != 'Date' && isset($_POST['GATE_IN_TIME'])) {
							$gate_back_date = $this->input->post('GATE_IN_DATE').' '.$this->input->post('GATE_IN_TIME');
							$this->db->query("INSERT INTO TX_GATE (GATE_CONSIGNEE_NAME,GATE_CONT,GATE_MARK,GATE_ORIGIN,GATE_TRUCK_NO,GATE_CONSIGNEE_ID,GATE_CONT_SIZE,GATE_CONT_TYPE,GATE_NOREQ,GATE_NO_SEAL,GATE_CONT_STATUS, GATE_ACTIVITY,GATE_NOTA,GATE_BRANCH_ID,GATE_STACK_STATUS,GATE_STATUS,GATE_CREATE_BY,GATE_CREATE_DATE,GATE_BACKDATE)
							SELECT GATE_CONSIGNEE_NAME, GATE_CONT, GATE_MARK, GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_NOREQ, GATE_NO_SEAL, GATE_CONT_STATUS,
											GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, ".$stack." GATE_STACK_STATUS, 3 GATE_STATUS, ".$user_id." GATE_CREATE_BY, TO_DATE('".$gate_back_date."','DD/MM/YYYY HH24:MI') AS GATE_CREATE_DATE, '".$_POST['REASON']."' AS GATE_BACKDATE
											FROM TX_GATE
											WHERE GATE_BRANCH_ID = ".$branch_id." AND
											GATE_STATUS = 1 AND
											GATE_CONT = '".$val['GATE_CONT']."' AND
											GATE_NOREQ = '".$val['GATE_NOREQ']."'");
						}
						else{
							$this->db->query("INSERT INTO TX_GATE (GATE_CONSIGNEE_NAME,GATE_CONT,GATE_MARK,GATE_ORIGIN,GATE_TRUCK_NO,GATE_CONSIGNEE_ID,GATE_CONT_SIZE,GATE_CONT_TYPE,GATE_NOREQ,GATE_NO_SEAL,GATE_CONT_STATUS, GATE_ACTIVITY,GATE_NOTA,GATE_BRANCH_ID,GATE_STACK_STATUS,GATE_STATUS,GATE_CREATE_BY)
							SELECT GATE_CONSIGNEE_NAME, GATE_CONT, GATE_MARK, GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_NOREQ, GATE_NO_SEAL, GATE_CONT_STATUS,
											GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, ".$stack." GATE_STACK_STATUS, 3 GATE_STATUS, ".$user_id." GATE_CREATE_BY
											FROM TX_GATE
											WHERE GATE_BRANCH_ID = ".$branch_id." AND
											GATE_STATUS = 1 AND
											GATE_CONT = '".$val['GATE_CONT']."' AND
											GATE_NOREQ = '".$val['GATE_NOREQ']."'");
						}
						// $this->db->insert('TX_GATE',$data4->row_array());
						// $this->db->query('UPDATE TX_REQ_RECEIVING_DTL SET REQUEST_DTL_STATUS = 1 WHERE  REQUEST_DTL_CONT = "$CONT" AND REQUEST_HDR_ID = (SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO = "$REQ" AND REQUEST_BRANCH_ID = "$branch_id")');
					}
				}
			}
			//job delivery
			else if(($val['GATE_ACTIVITY'] == 4)){
				$data_check = $this->db->select('A.GATE_CONT, B.REQ_ID, C.REQ_DTL_ID')
							->from('TX_GATE A')
							->join('TX_REQ_DELIVERY_HDR B','B.REQ_NO = A.GATE_NOREQ')
							->join('TX_REQ_DELIVERY_DTL C','C.REQ_HDR_ID = B.REQ_ID')
							->where('C.REQ_DTL_CONT',$val['GATE_CONT'])
							->where('B.REQ_NO',$val['GATE_NOREQ'])
							->where('A.GATE_BRANCH_ID',$branch_id)
							->where('C.REQ_DTL_STATUS',1)
							->get();
			if($data_check->num_rows() > 0){

					$data_get = $this->db->select("GATE_CONSIGNEE_NAME, GATE_CONT, '".$MARK."' AS GATE_MARK, GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_CONT_STATUS, GATE_NOREQ, GATE_NO_SEAL,
								GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, ".$stack." GATE_STACK_STATUS, 3 GATE_STATUS, ".$user_id." GATE_CREATE_BY")
								->from('TX_GATE')
								->where('GATE_BRANCH_ID',$branch_id)
								->where('GATE_STATUS',1)
								->where('GATE_CONT',$val['GATE_CONT'])
								->where('GATE_NOREQ',$val['GATE_NOREQ'])
								->get();

				if($data_get->num_rows() > 0){
					if(isset($_POST['GATE_IN_DATE']) && $_POST['GATE_IN_DATE'] != 'Date' && isset($_POST['GATE_IN_TIME'])) {
						$gate_back_date = $this->input->post('GATE_IN_DATE').' '.$this->input->post('GATE_IN_TIME');
						$this->db->query("INSERT INTO TX_GATE (GATE_CONSIGNEE_NAME,GATE_CONT,GATE_MARK,GATE_ORIGIN,GATE_TRUCK_NO,GATE_CONSIGNEE_ID,GATE_CONT_SIZE,GATE_CONT_TYPE,GATE_NOREQ,GATE_NO_SEAL,GATE_CONT_STATUS, GATE_ACTIVITY,GATE_NOTA,GATE_BRANCH_ID,GATE_STACK_STATUS,GATE_STATUS,GATE_CREATE_BY,GATE_CREATE_DATE,GATE_BACKDATE)
						SELECT GATE_CONSIGNEE_NAME, GATE_CONT, '".$MARK."' AS GATE_MARK, GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_CONT_STATUS, GATE_NOREQ, GATE_NO_SEAL,
									GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, ".$stack." GATE_STACK_STATUS, 3 GATE_STATUS, ".$user_id." GATE_CREATE_BY, TO_DATE('".$gate_back_date."','DD/MM/YYYY HH24:MI') AS GATE_CREATE_DATE, '".$_POST['REASON']."' AS GATE_BACKDATE
										FROM TX_GATE
										WHERE GATE_BRANCH_ID = ".$branch_id." AND
										GATE_STATUS = 1 AND
										GATE_CONT = '".$val['GATE_CONT']."' AND
										GATE_NOREQ = '".$val['GATE_NOREQ']."'");
					}
					else{
						$this->db->query("INSERT INTO TX_GATE (GATE_CONSIGNEE_NAME,GATE_CONT,GATE_MARK,GATE_ORIGIN,GATE_TRUCK_NO,GATE_CONSIGNEE_ID,GATE_CONT_SIZE,GATE_CONT_TYPE,GATE_NOREQ,GATE_NO_SEAL,GATE_CONT_STATUS, GATE_ACTIVITY,GATE_NOTA,GATE_BRANCH_ID,GATE_STACK_STATUS,GATE_STATUS,GATE_CREATE_BY)
						SELECT GATE_CONSIGNEE_NAME, GATE_CONT, '".$MARK."' AS GATE_MARK, GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_CONT_STATUS, GATE_NOREQ, GATE_NO_SEAL,
									GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, ".$stack." GATE_STACK_STATUS, 3 GATE_STATUS, ".$user_id." GATE_CREATE_BY
										FROM TX_GATE
										WHERE GATE_BRANCH_ID = ".$branch_id." AND
										GATE_STATUS = 1 AND
										GATE_CONT = '".$val['GATE_CONT']."' AND
										GATE_NOREQ = '".$val['GATE_NOREQ']."'");
					}

					// $this->db->insert('TX_GATE',$data_get->row_array());
					$this->db->query("UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_STATUS = 2 WHERE  REQ_DTL_CONT = '".$val['GATE_CONT']."' AND REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$val['GATE_NOREQ']."' AND REQ_BRANCH_ID = ".$branch_id.")");
					//check detail request delivery telah gateout
					$cek_jumlah_dtl = $this->db->where('REQ_HDR_ID',$data_check->row_array()['REQ_ID'])->from('TX_REQ_DELIVERY_DTL')->count_all_results();
					$cek_jumlah_out = $this->db->where('REQ_HDR_ID',$data_check->row_array()['REQ_ID'])->where('REQ_DTL_STATUS','2')->from('TX_REQ_DELIVERY_DTL')->count_all_results();
					if($cek_jumlah_out == $cek_jumlah_dtl){
						$this->db->set('REQUEST_STATUS',2)->where('REQ_ID',$data_check->row_array()['REQ_ID'])->update('TX_REQ_DELIVERY_HDR');
					}
					//update yard stacking
					$cek_yard1 = $this->db->query("SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, ".$user_id." AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS,
											'".$this->input->post('REQ_NO')."' AS REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY
											FROM TX_REAL_YARD
											WHERE REAL_YARD_CONT = '".$val['GATE_CONT']."' AND REAL_YARD_BRANCH_ID = ".$branch_id."
											AND REAL_YARD_CREATE_DATE = (SELECT MAX(A.REAL_YARD_CREATE_DATE) FROM TX_REAL_YARD A WHERE A.REAL_YARD_CONT = '".$val['GATE_CONT']."' ) ")->row_array();

					$this->db->insert('TX_REAL_YARD',$cek_yard1);

					// gate request date
					$req_date = $this->db->query("SELECT TO_CHAR(REQ_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') REQ_DATE FROM TX_REQ_DELIVERY_HDR
												WHERE REQ_BRANCH_ID = ".$branch_id." AND REQ_NO = '".$val['GATE_NOREQ']."'")->row_array()['REQ_DATE'];

					// insert history container
					$cont_size =  $data_get->row_array()['GATE_CONT_SIZE'];
					$cont_type =  $data_get->row_array()['GATE_CONT_TYPE'];
					$cont_status =  $data_get->row_array()['GATE_CONT_STATUS'];
					//ADD_HISTORY_CONTAINER(CONT_NO, REQ_NO, REQ_DATE, CONT_SIZE, CONT_YARD, CONT_BLOCK, CONT_SLOT, CONT_ROW, CONT_TIER, CONT_ACTIVITY_ID, CONT_ACTIVITY_NAME, BRANCH)
					if(isset($_POST['GATE_IN_DATE']) && $_POST['GATE_IN_DATE'] != 'Date' && isset($_POST['GATE_IN_TIME'])) {
						$gate_back_date = $this->input->post('GATE_IN_DATE').' '.$this->input->post('GATE_IN_TIME');
						$this->db->query("CALL ADD_HISTORY_CONTAINER(
								'".$val['GATE_CONT']."',
								'".$val['GATE_NOREQ']."',
								'".$req_date."',
								'".$cont_size."',
								'".$cont_type."',
								'".$cont_status."',
								NULL,
								NULL,
								NULL,
								NULL,
								NULL,
								3,
								'GATE OUT',
								'".$gate_back_date."',
								".$branch_id.")");
					}
					else {
						$this->db->query("CALL ADD_HISTORY_CONTAINER(
									'".$val['GATE_CONT']."',
									'".$val['GATE_NOREQ']."',
									'".$req_date."',
									'".$cont_size."',
									'".$cont_type."',
									'".$cont_status."',
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									3,
									'GATE OUT',
									NULL,
									".$branch_id.")");
					}
				}
			}
			}
		}
	}
	//end cek

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	function test1(){
			$user_id = $this->session->isId;
		$cek_yard1 = $this->db->select('REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, '.$user_id.' AS REAL_YARD_CREATE_BY,  REAL_YARD_TYPE, 2 AS "REAL_YARD_STATUS",
								REAL_YARD_REQ_NO, REAL_YARD_FL_SEND, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) AS REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY')
								->where('REAL_YARD_CONT','DFSU111111')
								->where('REAL_YARD_STATUS',1)
								->order_by('REAL_YARD_CREATE_DATE','DESC')
								->limit(1)
								->get('TX_REAL_YARD')->row_array();
		unset($cek_yard1['RNUM']);
		return $cek_yard1;
	}

	function getGateJobManager(){
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
					$qs .= " AND ".$field." LIKE '%".strtoupper($value)."%'";

				}
				$qWhere .= $qs;
			}
			// end filter

		$search = '';
		if(!empty($_REQUEST['CONTAINER_NO'])){
			$search .= " AND S.REQUEST_DTL_CONT = ".$this->db->escape($_REQUEST['CONTAINER_NO']);
		}
		if(!empty($_REQUEST['REQUEST_NO'])){
			$search .= " AND S.REQUEST_NO = ".$this->db->escape($_REQUEST['REQUEST_NO']);
		}
		if(!empty($_REQUEST['TRUCK_NO'])){
			$search .= " AND S.GATE_TRUCK_NO = ".$this->db->escape($_REQUEST['TRUCK_NO']);
		}

			$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (SELECT* FROM(SELECT * FROM(SELECT A.REQUEST_NO, 'Receiving' ACTIVITY, B.REQUEST_DTL_CONT, A.REQUEST_CONSIGNEE_ID, D.CONSIGNEE_NAME, C.GATE_TRUCK_NO, C.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, NVL(TO_CHAR(C.GATE_CREATE_DATE,'DD/MM/YYYY HH24:MI'),'Belum Gate In') GATE_IN,
			NVL((SELECT to_char(X.GATE_CREATE_DATE,'DD/MM/YYYY HH24:MI') FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3),'Belum Gate Out') AS GATE_OUT, TO_CHAR(A.REQUEST_CREATE_DATE,'DD/MM/YYYY HH24:MI') TGL_REQ, '-' PAID_THRU
			FROM TX_REQ_RECEIVING_HDR A
			JOIN TX_REQ_RECEIVING_DTL B ON B.REQUEST_HDR_ID = A.REQUEST_ID
			LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQUEST_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQUEST_NO
			LEFT JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = A.REQUEST_CONSIGNEE_ID
			LEFT JOIN TM_REFF E ON E.REFF_ID = C.GATE_ORIGIN AND E.REFF_TR_ID = 20
			WHERE A.REQUEST_STATUS !=2 AND A.REQUEST_BRANCH_ID = $branch_id
			ORDER BY A.REQUEST_ID DESC)
			UNION ALL
			SELECT * FROM(SELECT A.REQ_NO REQUEST_NO, 'Delivery' ACTIVITY, B.REQ_DTL_CONT REQUEST_DTL_CONT, A.REQ_CONSIGNEE_ID REQUEST_CONSIGNEE_ID, D.CONSIGNEE_NAME CONSIGNEE_NAME, C.GATE_TRUCK_NO GATE_TRUCK_NO, C.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, NVL(TO_CHAR(C.GATE_CREATE_DATE,'DD/MM/YYYY HH24:MI'),'Belum Gate In') GATE_IN,
			NVL((SELECT to_char(X.GATE_CREATE_DATE,'DD/MM/YYYY HH24:MI') FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3),'Belum Gate Out') AS GATE_OUT, TO_CHAR(A.REQ_CREATE_DATE,'DD/MM/YYYY HH24:MI') TGL_REQ, TO_CHAR(B.REQ_DTL_DEL_DATE,'DD/MM/YYYY') PAID_THRU
						FROM TX_REQ_DELIVERY_HDR A
						JOIN TX_REQ_DELIVERY_DTL B ON B.REQ_HDR_ID = A.REQ_ID
						LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQ_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQ_NO
						LEFT JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = A.REQ_CONSIGNEE_ID
						LEFT JOIN TM_REFF E ON E.REFF_ID = C.GATE_ORIGIN AND E.REFF_TR_ID = 20
						WHERE A.REQUEST_STATUS !=2 AND A.REQ_BRANCH_ID = $branch_id
			ORDER BY A.REQ_ID DESC)) S WHERE 1=1 $qWhere) T
									WHERE ROWNUM <= $end) F
										WHERE r >= $start + 1";
		$data = $this->db->query($query,$params)->result_array();

		$count = $this->db->query("SELECT * FROM(SELECT * FROM(SELECT A.REQUEST_NO, 'Receiving' ACTIVITY, B.REQUEST_DTL_CONT, A.REQUEST_CONSIGNEE_ID, D.CONSIGNEE_NAME, C.GATE_TRUCK_NO, C.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, NVL(TO_CHAR(C.GATE_CREATE_DATE,'DD/MM/YYYY HH24:MI'),'Belum Gate In') GATE_IN,
			NVL((SELECT to_char(X.GATE_CREATE_DATE,'DD/MM/YYYY HH24:MI') FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3),'Belum Gate Out') AS GATE_OUT
			FROM TX_REQ_RECEIVING_HDR A
			JOIN TX_REQ_RECEIVING_DTL B ON B.REQUEST_HDR_ID = A.REQUEST_ID
			LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQUEST_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQUEST_NO
			LEFT JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = A.REQUEST_CONSIGNEE_ID
			LEFT JOIN TM_REFF E ON E.REFF_ID = C.GATE_ORIGIN AND E.REFF_TR_ID = 20
			WHERE A.REQUEST_STATUS !=2 AND A.REQUEST_BRANCH_ID = $branch_id
			ORDER BY A.REQUEST_ID DESC)
			UNION ALL
			SELECT * FROM(SELECT A.REQ_NO REQUEST_NO, 'Delivery' ACTIVITY, B.REQ_DTL_CONT REQUEST_DTL_CONT, A.REQ_CONSIGNEE_ID REQUEST_CONSIGNEE_ID, D.CONSIGNEE_NAME CONSIGNEE_NAME, C.GATE_TRUCK_NO GATE_TRUCK_NO, C.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, NVL(TO_CHAR(C.GATE_CREATE_DATE,'DD/MM/YYYY HH24:MI'),'Belum Gate In') GATE_IN,
			NVL((SELECT to_char(X.GATE_CREATE_DATE,'DD/MM/YYYY HH24:MI') FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3),'Belum Gate Out') AS GATE_OUT
						FROM TX_REQ_DELIVERY_HDR A
						JOIN TX_REQ_DELIVERY_DTL B ON B.REQ_HDR_ID = A.REQ_ID
						LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQ_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQ_NO
						LEFT JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = A.REQ_CONSIGNEE_ID
						LEFT JOIN TM_REFF E ON E.REFF_ID = C.GATE_ORIGIN AND E.REFF_TR_ID = 20
						WHERE A.REQUEST_STATUS !=2 AND A.REQ_BRANCH_ID = $branch_id
			ORDER BY A.REQ_ID DESC)) S WHERE 1=1 $qWhere")->result_array();

		return array (
			'data' => $data,
			'total' => count($count)
		);
	}

	function getGateReport(){
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

		$qWhere .=" AND TO_DATE(TO_CHAR(GATE_IN,'YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN TO_DATE('".$tgl1."','YYYY-MM-DD') AND TO_DATE('".$tgl2."','YYYY-MM-DD')";

		//apply filter
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

			$query = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (SELECT REQUEST_NO, ACTIVITY_CODE, ACTIVITY, REQUEST_DTL_CONT, REQUEST_CONSIGNEE_ID, CONSIGNEE_NAME, GATE_TRUCK_NO, GATE_ORIGIN, ORIGIN_NAME, TO_CHAR(GATE_IN,'DD/MM/YYYY HH24:MI') GATE_IN, TO_CHAR(GATE_OUT,'DD/MM/YYYY HH24:MI') GATE_OUT, MARK_IN, MARK_OUT
			FROM(SELECT * FROM(SELECT A.REQUEST_NO, 3 ACTIVITY_CODE, 'Receiving' ACTIVITY, B.REQUEST_DTL_CONT, A.REQUEST_CONSIGNEE_ID, D.CONSIGNEE_NAME, C.GATE_TRUCK_NO, C.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, C.GATE_CREATE_DATE GATE_IN, C.GATE_MARK MARK_IN,
			(SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS GATE_OUT, (SELECT X.GATE_MARK FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS MARK_OUT
			FROM TX_REQ_RECEIVING_HDR A
			JOIN TX_REQ_RECEIVING_DTL B ON B.REQUEST_HDR_ID = A.REQUEST_ID
			LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQUEST_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQUEST_NO
			LEFT JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = A.REQUEST_CONSIGNEE_ID
			LEFT JOIN TM_REFF E ON E.REFF_ID = C.GATE_ORIGIN AND E.REFF_TR_ID = 20
			WHERE A.REQUEST_BRANCH_ID = $branch_id
			ORDER BY A.REQUEST_ID DESC)
			UNION ALL
			SELECT * FROM(SELECT A.REQ_NO REQUEST_NO, 4 ACTIVITY_CODE, 'Delivery' ACTIVITY, B.REQ_DTL_CONT REQUEST_DTL_CONT, A.REQ_CONSIGNEE_ID REQUEST_CONSIGNEE_ID, D.CONSIGNEE_NAME CONSIGNEE_NAME, C.GATE_TRUCK_NO GATE_TRUCK_NO, C.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, C.GATE_CREATE_DATE GATE_IN, C.GATE_MARK MARK_IN,
			(SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS GATE_OUT, (SELECT X.GATE_MARK FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS MARK_OUT
						FROM TX_REQ_DELIVERY_HDR A
						JOIN TX_REQ_DELIVERY_DTL B ON B.REQ_HDR_ID = A.REQ_ID
						LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQ_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQ_NO
						LEFT JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = A.REQ_CONSIGNEE_ID
						LEFT JOIN TM_REFF E ON E.REFF_ID = C.GATE_ORIGIN AND E.REFF_TR_ID = 20
						WHERE A.REQ_BRANCH_ID = $branch_id
			ORDER BY A.REQ_ID DESC)) S WHERE 1=1 $qWhere) T
									WHERE ROWNUM <= $end) F
										WHERE r >= $start + 1";
		$data = $this->db->query($query,$params)->result_array();

		$count = $this->db->query("SELECT * FROM(SELECT * FROM(SELECT A.REQUEST_NO, 1 ACTIVITY_CODE, 'Receiving' ACTIVITY, B.REQUEST_DTL_CONT, A.REQUEST_CONSIGNEE_ID, D.CONSIGNEE_NAME, C.GATE_TRUCK_NO, C.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, C.GATE_CREATE_DATE GATE_IN,
		(SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS GATE_OUT
		FROM TX_REQ_RECEIVING_HDR A
		JOIN TX_REQ_RECEIVING_DTL B ON B.REQUEST_HDR_ID = A.REQUEST_ID
		LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQUEST_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQUEST_NO
		LEFT JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = A.REQUEST_CONSIGNEE_ID
		LEFT JOIN TM_REFF E ON E.REFF_ID = C.GATE_ORIGIN AND E.REFF_TR_ID = 20
		WHERE A.REQUEST_BRANCH_ID = $branch_id
		ORDER BY A.REQUEST_ID DESC)
		UNION ALL
		SELECT * FROM(SELECT A.REQ_NO REQUEST_NO, 2 ACTIVITY_CODE, 'Delivery' ACTIVITY, B.REQ_DTL_CONT REQUEST_DTL_CONT, A.REQ_CONSIGNEE_ID REQUEST_CONSIGNEE_ID, D.CONSIGNEE_NAME CONSIGNEE_NAME, C.GATE_TRUCK_NO GATE_TRUCK_NO, C.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, C.GATE_CREATE_DATE GATE_IN,
		(SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS GATE_OUT
					FROM TX_REQ_DELIVERY_HDR A
					JOIN TX_REQ_DELIVERY_DTL B ON B.REQ_HDR_ID = A.REQ_ID
					LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQ_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQ_NO
					LEFT JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = A.REQ_CONSIGNEE_ID
					LEFT JOIN TM_REFF E ON E.REFF_ID = C.GATE_ORIGIN AND E.REFF_TR_ID = 20
					WHERE A.REQ_BRANCH_ID = $branch_id
		ORDER BY A.REQ_ID DESC)) S WHERE 1=1 $qWhere")->result_array();

		return array (
			'data' => $data,
			'total' => count($count)
		);
	}

	function getGateExport(){
		$branch_id = $this->session->USER_BRANCH;

		$qWhere = "";
		$activity = $_GET['activity'] != null? $_GET['activity'] : false;
		$tgl1 = $_GET['date1'] != null? $_GET['date1'] : date('Y-m-j');
		$tgl2 = $_GET['date2'] != null? $_GET['date2'] : date('Y-m-j');

		$qw = '';
		if($activity != false && $activity != 0){
			$qWhere .= " AND ACTIVITY_CODE =".$activity;
		}

		$qWhere .=" AND TO_DATE(TO_CHAR(GATE_IN,'YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN TO_DATE('".$tgl1."','YYYY-MM-DD') AND TO_DATE('".$tgl2."','YYYY-MM-DD')";


		$query = $this->db->query("SELECT REQUEST_NO, ACTIVITY_CODE, ACTIVITY, REQUEST_DTL_CONT, REQUEST_CONSIGNEE_ID, CONSIGNEE_NAME, GATE_TRUCK_NO, GATE_ORIGIN, ORIGIN_NAME, TO_CHAR(GATE_IN,'DD/MM/YYYY HH24:MI') GATE_IN, TO_CHAR(GATE_OUT,'DD/MM/YYYY HH24:MI') GATE_OUT, MARK_IN, MARK_OUT
		FROM(SELECT * FROM(SELECT A.REQUEST_NO, 3 ACTIVITY_CODE, 'Receiving' ACTIVITY, B.REQUEST_DTL_CONT, A.REQUEST_CONSIGNEE_ID, D.CONSIGNEE_NAME, C.GATE_TRUCK_NO, C.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, C.GATE_CREATE_DATE GATE_IN, C.GATE_MARK MARK_IN,
		(SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS GATE_OUT, (SELECT X.GATE_MARK FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS MARK_OUT
		FROM TX_REQ_RECEIVING_HDR A
		JOIN TX_REQ_RECEIVING_DTL B ON B.REQUEST_HDR_ID = A.REQUEST_ID
		LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQUEST_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQUEST_NO
		LEFT JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = A.REQUEST_CONSIGNEE_ID
		LEFT JOIN TM_REFF E ON E.REFF_ID = C.GATE_ORIGIN AND E.REFF_TR_ID = 20
		WHERE A.REQUEST_BRANCH_ID = $branch_id
		ORDER BY A.REQUEST_ID DESC)
		UNION ALL
		SELECT * FROM(SELECT A.REQ_NO REQUEST_NO, 4 ACTIVITY_CODE, 'Delivery' ACTIVITY, B.REQ_DTL_CONT REQUEST_DTL_CONT, A.REQ_CONSIGNEE_ID REQUEST_CONSIGNEE_ID, D.CONSIGNEE_NAME CONSIGNEE_NAME, C.GATE_TRUCK_NO GATE_TRUCK_NO, C.GATE_ORIGIN, E.REFF_NAME ORIGIN_NAME, C.GATE_CREATE_DATE GATE_IN, C.GATE_MARK MARK_IN,
		(SELECT X.GATE_CREATE_DATE FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS GATE_OUT, (SELECT X.GATE_MARK FROM TX_GATE X WHERE X.GATE_NOREQ = C.GATE_NOREQ AND X.GATE_CONT = C.GATE_CONT AND X.GATE_STATUS = 3) AS MARK_OUT
					FROM TX_REQ_DELIVERY_HDR A
					JOIN TX_REQ_DELIVERY_DTL B ON B.REQ_HDR_ID = A.REQ_ID
					LEFT JOIN TX_GATE C ON C.GATE_CONT = B.REQ_DTL_CONT AND C.GATE_STATUS = 1 AND C.GATE_NOREQ = A.REQ_NO
					LEFT JOIN TM_CONSIGNEE D ON D.CONSIGNEE_ID = A.REQ_CONSIGNEE_ID
					LEFT JOIN TM_REFF E ON E.REFF_ID = C.GATE_ORIGIN AND E.REFF_TR_ID = 20
					WHERE A.REQ_BRANCH_ID = $branch_id
		ORDER BY A.REQ_ID DESC)) S WHERE 1=1 $qWhere")->result_array();

		return $query;
	}

	function getReportActivity($activity){
		$sql = "SELECT A.REFF_ID, A.REFF_NAME, A.REFF_ORDER FROM TM_REFF A
      JOIN TR_REFF B ON A.REFF_TR_ID = B.REFF_ID
          WHERE B.REFF_ID = 22 AND A.REFF_ID = ".$activity;
    $data = $this->db->query($sql)->row_array()['REFF_NAME'];
    return $data;
	}

	function rePrintGate(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$date1 = date('Y-m-d H:i:s');
		$arrGate = array();
		$req = $this->input->post('req');
		$cont = $this->input->post('cont');
		$truck = $this->input->post('truck');

		//get data gate
		$gate = $this->db->select("GATE_CONSIGNEE_NAME, GATE_CONT, NVL(GATE_MARK,'-') GATE_MARK, GATE_ORIGIN, GATE_TRUCK_NO, GATE_CONSIGNEE_ID, GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_NOREQ, NVL(GATE_NO_SEAL,'-') GATE_NO_SEAL, GATE_CONT_STATUS,
						GATE_ACTIVITY, GATE_NOTA, GATE_BRANCH_ID, GATE_STACK_STATUS, GATE_STATUS, B.REFF_NAME ACTIVITY_NAME, TO_CHAR(GATE_CREATE_DATE,'DD-MM-YYYY HH24:MI:SS') GATE_DATE")
						->from('TX_GATE A')
						->join('TM_REFF B','B.REFF_ID = A.GATE_ACTIVITY AND B.REFF_TR_ID = 22')
						->where('GATE_BRANCH_ID',$branch_id)
						->where('GATE_STATUS',1)
						->where('GATE_NOREQ',$req)
						->where('GATE_CONT',$cont)
						->get()->row_array();

		if($gate['GATE_ACTIVITY'] == 3){

			$cont_rec = $this->db->select("A.REQUEST_DTL_CONT, A.REQUEST_DTL_CONT_SIZE, A.REQUEST_DTL_CONT_TYPE, B.REQUEST_DI DI, A.REQUEST_DTL_CONT_STATUS, A.REQUEST_DTL_OWNER_CODE, A.REQUEST_DTL_OWNER_NAME")
									->from('TX_REQ_RECEIVING_DTL A')
									->join('TX_REQ_RECEIVING_HDR B','A.REQUEST_HDR_ID = B.REQUEST_ID')
									->where('B.REQUEST_BRANCH_ID',$branch_id)
									->where('B.REQUEST_NO',$gate['GATE_NOREQ'])
									->get()->row_array();

				//crate array for print
				$cont_size = $cont_rec['REQUEST_DTL_CONT_SIZE'];
				$cont_status = $cont_rec['REQUEST_DTL_CONT_STATUS'];
				$cont_type = $cont_rec['REQUEST_DTL_CONT_TYPE'];
				$di = $cont_rec['DI'];
				$owner = $cont_rec['REQUEST_DTL_OWNER_CODE'];
				$yard_active = $this->session->YARD_ACTIVE;

				$ownerExist = $this->db->select('A.CAT_DTL_OWNER')->from('TX_CATEGORY_DTL A')->join('TX_CATEGORY_HDR B','B.CAT_HDR_ID = A.CAT_HDR_ID')->where('CAT_BRANCH_ID',$branch_id)->where('CAT_DTL_OWNER',$owner)->count_all_results();
				$arrConfing = $this->db->select('CONFIG, STATUS, DETAIL')->from('TM_CMS_CONFIG')->where('BRANCH',$branch_id)->where('STATUS','Y')->get()->result_array();
				$where = '1=1 AND B.CAT_BRANCH_ID = '.$branch_id.' AND C.YPG_YARD_ID = '.$yard_active;
				foreach ($arrConfing as $key => $val) {
						if($val['DETAIL'] == 'SIZE'){
							$where .= " AND A.".$val['CONFIG']."='".$cont_size."'";
						}
						else if($val['DETAIL'] == 'TYPE'){
							 $where .= " AND A.".$val['CONFIG']."='".$cont_type."'";
						}
						else if($val['DETAIL'] == 'STATUS'){
							 $where .= " AND A.".$val['CONFIG']."='".$cont_status."'";
						}
						// else if($val['DETAIL'] == 'D/I'){
						// 	 $where .= " AND A.".$val['CONFIG']."='".$di."'";
						// }
						else if($val['DETAIL'] == 'OWNER'){
							if($ownerExist > 0){
							 $where .= " AND A.".$val['CONFIG']."='".$owner."'";
						 	}
						}
				}

					$gate_time = $gate['GATE_DATE'];

					$gate_activity = $gate['ACTIVITY_NAME'];

					$lokasi = $this->db->query("SELECT B.CAT_HDR_ID, A.CAT_DTL_CONT_SIZE CONT, A.CAT_DTL_CONT_STATUS, A.CAT_DTL_CONT_TYPE, A.CAT_DTL_EXIM, C.YPG_YARD_ID, C.YPG_BLOCK_ID, C.YPG_STAR_ROW, C.YPG_END_ROW, C.YPG_START_SLOT, C.YPG_END_SLOT, C.YPG_CAPACITY, '' DI, D.BLOCK_NAME, E.YARD_NAME
												FROM TX_CATEGORY_DTL A
												JOIN TX_CATEGORY_HDR B ON B.CAT_HDR_ID = A.CAT_HDR_ID
												JOIN TX_YARD_PLAN_GROUP C ON C.YPG_CAT_HDR_ID = B.CAT_HDR_ID
												JOIN TM_BLOCK D ON D.BLOCK_ID = C.YPG_BLOCK_ID AND D.BLOCK_ACTIVE = 'Y'
												JOIN TM_YARD E ON E.YARD_ID = C.YPG_YARD_ID
												WHERE ".$where)->result_array();

					foreach ($lokasi as $loc) {
						// cek capacity block
						$stack_capacity = $this->db->query("SELECT COUNT(*) STACK_CAPACITY  FROM ( SELECT MAX(REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H  GROUP BY H.REAL_YARD_CONT) X
									 INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID
									 INNER JOIN TX_YARD_BLOCK_CELL J ON J.YBC_ID = I.REAL_YARD_YBC_ID
									 INNER JOIN TM_BLOCK K ON K.BLOCK_ID = J.YBC_BLOCK_ID
									 WHERE J.YBC_ACTIVE = 'Y' AND K.BLOCK_ACTIVE = 'Y' AND I.REAL_YARD_STATUS = 1 AND J.YBC_BLOCK_ID = '".$loc['YPG_BLOCK_ID']."'")->row_array()['STACK_CAPACITY'];

						if($stack_capacity < $loc['YPG_CAPACITY']){
							$slot = $loc['YPG_START_SLOT'].' - '.$loc['YPG_END_SLOT'];
							$row = $loc['YPG_STAR_ROW'].' - '.$loc['YPG_END_ROW'];
							$arrGate[0]['GATE_TIME'] = $gate_time;
							$arrGate[0]['NOREQ'] = $gate['GATE_NOREQ'];
							$arrGate[0]['TRUCK_NO'] = $gate['GATE_TRUCK_NO'];
							$arrGate[0]['SEAL_NO'] = $gate['GATE_NO_SEAL'];
							$arrGate[0]['CONTAINER'] = $gate['GATE_CONT'];
							$arrGate[0]['ACTIVITY'] = $gate_activity;
							$arrGate[0]['ORIGIN'] = $gate['GATE_ORIGIN'];
							$arrGate[0]['SIZE'] = $gate['GATE_CONT_SIZE'];
							$arrGate[0]['TYPE'] = $gate['GATE_CONT_TYPE'];
							$arrGate[0]['STATUS'] = $gate['GATE_CONT_STATUS'];
							$arrGate[0]['YARD_NAME'] = $loc['YARD_NAME'];
							$arrGate[0]['BLOCK_NAME'] = $loc['BLOCK_NAME'];
							$arrGate[0]['SLOT'] = $slot;
							$arrGate[0]['ROW'] = '-';
							$arrGate[0]['TIER'] = '-';
							$arrGate[0]['REMARK'] = $gate['GATE_MARK'];
							break;
						}
					}
		}

		if($gate['GATE_ACTIVITY'] == 4){

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
									 $arrGate[0]['TRUCK_NO'] = $gate['GATE_TRUCK_NO'];
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

		}

    return array('data' => $arrGate);
	}

	}
