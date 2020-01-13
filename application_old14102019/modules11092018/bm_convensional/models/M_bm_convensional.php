<?php
class M_bm_convensional extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

  function get_branch(){
    $data = $this->db->get('TR_BRANCH')->result_array();
    $total = $this->db->count_all_results('TR_BRANCH');
    return array (
      'data' => $data,
      'total' => $total
    );
  }

	//FUNCTION FOR DISCHARGE PLAN
	function generate_discharge_no(){
		$params = array('BRANCH' => $this->session->USER_BRANCH);
			$sql = "SELECT MAX(DISCH_HDR_NO) MAX_HDR_NO FROM TX_CONV_DISCH_HDR WHERE DISCH_HDR_BRANCH_ID = ?";
			$data = $this->db->query($sql,$params)->row_array();
			return $data;
	}

	function get_arr_discharge_plan_hdr(){
		$branch_id = $this->session->USER_BRANCH;
		$params = array(
			'DISCH_HDR_BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);
		$query = '';
		if(!empty($_REQUEST['NO_DISCHARGE'])){
			$query .= " AND A.DISCH_HDR_NO = ".$this->db->escape($_REQUEST['NO_DISCHARGE']);
		}
		if(!empty($_REQUEST['CONSIGNEE'])){
			$query .= " AND B.CONSIGNEE_NAME LIKE '%" .$this->db->escape_like_str($_REQUEST['CONSIGNEE']). "%' ESCAPE '!'";
		}
		if(!empty($_REQUEST['VESSEL_NAME'])){
			$query .= " AND A.DISCH_HDR_VESSEL_NAME LIKE '%" .$this->db->escape_like_str($_REQUEST['VESSEL_NAME']). "%' ESCAPE '!'";
		}
		if(!empty($_REQUEST['STATUS'])){
			$query .= " AND C.REFF_ID = ".$this->db->escape($_REQUEST['STATUS']);
		}
		$sql = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (SELECT A.DISCH_HDR_ID, A.DISCH_HDR_NO, A.DISCH_HDR_VESSEL_CODE, A.DISCH_HDR_VESSEL_NAME, A.DISCH_HDR_CONSIGNEE_ID, B.CONSIGNEE_NAME, A.DISCH_HDR_VOY,
			to_char(A.DISCH_HDR_START, 'dd/mm/yyyy hh24:mi') DISCH_HDR_START, to_char(A.DISCH_HDR_END,'dd/mm/yyyy hh24:mi') DISCH_HDR_END, A.DISCH_HDR_STATUS, C.REFF_NAME AS STATUS, C.REFF_ID STATUS_ID
				FROM TX_CONV_DISCH_HDR A
					JOIN TM_CONSIGNEE B ON A.DISCH_HDR_CONSIGNEE_ID = B.CONSIGNEE_ID
					JOIN TM_REFF C ON A.DISCH_HDR_STATUS = C.REFF_ID AND REFF_TR_ID = 7
					WHERE DISCH_HDR_BRANCH_ID = ? $query
                ORDER BY A.DISCH_HDR_ID DESC) T
                	WHERE ROWNUM <= ?) F
										WHERE r >= ? + 1";
    $data = $this->db->query($sql,$params)->result_array();

		$sql2 = "SELECT A.DISCH_HDR_ID FROM TX_CONV_DISCH_HDR A
					JOIN TM_CONSIGNEE B ON A.DISCH_HDR_CONSIGNEE_ID = B.CONSIGNEE_ID
					JOIN TM_REFF C ON A.DISCH_HDR_STATUS = C.REFF_ID AND REFF_TR_ID = 7
					WHERE DISCH_HDR_BRANCH_ID = $branch_id $query";
		$total = $this->db->query($sql2)->result_array();

    return array (
      'data' => $data,
      'total' => count($total)
    );
  }

	function get_arr_discharge_plan_hdr_by_id($idHdr){
		$params = array(
			'DISCH_HDR_ID' => $idHdr
		);
		$sql = "SELECT A.DISCH_HDR_ID, A.DISCH_HDR_NO, A.DISCH_HDR_VESSEL_CODE, A.DISCH_HDR_VESSEL_NAME, A.DISCH_HDR_CONSIGNEE_ID, B.CONSIGNEE_NAME, A.DISCH_HDR_VOY, TO_CHAR(A.DISCH_HDR_START,'dd/mm/yyyy') DISCH_HDR_START_DATE, TO_CHAR(A.DISCH_HDR_START,'hh24:mi') DISCH_HDR_START_TIME,
				TO_CHAR(A.DISCH_HDR_END,'dd/mm/yyyy') DISCH_HDR_END_DATE, TO_CHAR(A.DISCH_HDR_END,'hh24:mi') DISCH_HDR_END_TIME, A.DISCH_HDR_STATUS, C.REFF_NAME AS STATUS, TO_CHAR(A.DISCH_HDR_ETA,'dd/mm/yyyy hh24:mi') DISCH_HDR_ETA,
				TO_CHAR(A.DISCH_HDR_ETB,'dd/mm/yyyy hh24:mi') DISCH_HDR_ETB, TO_CHAR(A.DISCH_HDR_ETD,'dd/mm/yyyy hh24:mi') DISCH_HDR_ETD,
				E.REFF_ID EQUIPMENT_AREA, D.EQUIPMENT_JOB_ID EQUIPMENT
				FROM TX_CONV_DISCH_HDR A
					JOIN TM_CONSIGNEE B ON A.DISCH_HDR_CONSIGNEE_ID = B.CONSIGNEE_ID
					JOIN TM_REFF C ON A.DISCH_HDR_STATUS = C.REFF_ID AND C.REFF_TR_ID = 7
					JOIN TX_EQUIPMENT_JOB D ON A.DISCH_HDR_EQUIPMENT_JOB_ID = D.EQUIPMENT_JOB_ID
					JOIN TM_REFF E ON D.EQUIPMENT_JOB_LOCATION = E.REFF_ID AND E.REFF_TR_ID = 8
					JOIN TM_EQUIPMENT F ON D.EQUIPMENT_ID = F.EQUIPMENT_ID
					WHERE A.DISCH_HDR_ID = ?";
		$data = $this->db->query($sql,$params)->result_array();
		return array (
			'data' => $data,
			'total' => count($data)
		);
	}

	function get_arr_dischard_plan_search(){
		$params = array();
	}

function set_dishcharge_plan(){
		$message = 'SUKSES';
		$branch_id = $this->session->USER_BRANCH;
		$user = $this->session->isId;

		// $DISH_START = $this->input->post('DISCH_HDR_START').' '.$this->input->post('DISCH_HDR_START_HOUR').':'.$this->input->post('DISCH_HDR_START_MINUTE');
		$DISH_START = $this->input->post('DISCH_HDR_START_DATE').' '.$this->input->post('DISCH_HDR_START_TIME');
		$DISH_END = $this->input->post('DISCH_HDR_END_DATE').' '.$this->input->post('DISCH_HDR_END_TIME');
		// $DISH_END = $this->input->post('DISCH_HDR_END').' '.$this->input->post('DISCH_HDR_END_HOUR').':'.$this->input->post('DISCH_HDR_END_MINUTE');

		if($_POST['DISCH_HDR_ID'] == ''){
			$params = array(
				'DISCH_HDR_NO' => $_POST['DISCH_HDR_NO'],
				'DISCH_HDR_VESSEL_CODE' => $_POST['DISCH_HDR_VESSEL_CODE'],
				'DISCH_HDR_VESSEL_NAME' => $_POST['DISCH_HDR_VESSEL_NAME'],
				'DISCH_HDR_VOY' => $_POST['DISCH_HDR_VOY'],
				'DISCH_HDR_CONSIGNEE_ID' => $_POST['DISCH_HDR_CONSIGNEE_ID'],
				'DISCH_HDR_EQUIPMENT_JOB_ID' => $_POST['EQUIPMENT'],
				'DISCH_HDR_STATUS' => 0,
				'DISCH_HDR_BRANCH_ID' => $branch_id,
				'LOAD_HDR_CREATE_BY' => $user
			);

			$this->db->trans_start();

			$sql = "INSERT INTO TX_CONV_DISCH_HDR (DISCH_HDR_NO,DISCH_HDR_VESSEL_CODE,DISCH_HDR_VESSEL_NAME,DISCH_HDR_VOY,DISCH_HDR_CONSIGNEE_ID,DISCH_HDR_ETA,DISCH_HDR_ETB,DISCH_HDR_ETD,DISCH_HDR_START,DISCH_HDR_END,DISCH_HDR_EQUIPMENT_JOB_ID,DISCH_HDR_STATUS,DISCH_HDR_BRANCH_ID, DISCH_HDR_CREATE_BY)
							VALUES(?,?,?,?,?,TO_DATE('".$_POST['DISCH_HDR_ETA']."','dd-mm-yyyy hh24:mi'),TO_DATE('".$_POST['DISCH_HDR_ETB']."','dd-mm-yyyy hh24:mi'),TO_DATE('".$_POST['DISCH_HDR_ETD']."','dd-mm-yyyy hh24:mi'),TO_DATE('".$DISH_START."','dd-mm-yyyy hh24:mi'),TO_DATE('".$DISH_END."','dd-mm-yyyy hh24:mi'),?,?,?,?)";
			$this->db->query($sql,$params);

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE)
			{
			    $message = 'ERROR';
			}

		}
		else{
			$params = array(
				'DISCH_HDR_VESSEL_CODE' => $_POST['DISCH_HDR_VESSEL_CODE'],
				'DISCH_HDR_VESSEL_NAME' => $_POST['DISCH_HDR_VESSEL_NAME'],
				'DISCH_HDR_VOY' => $_POST['DISCH_HDR_VOY'],
				'DISCH_HDR_CONSIGNEE_ID' => $_POST['DISCH_HDR_CONSIGNEE_ID'],
				'DISCH_HDR_EQUIPMENT_JOB_ID' => $_POST['EQUIPMENT'],
				'DISCH_HDR_STATUS' => 0,
				'DISCH_HDR_ID' => $_POST['DISCH_HDR_ID']
			);

			$this->db->trans_start();

			$sql = "UPDATE TX_CONV_DISCH_HDR SET DISCH_HDR_VESSEL_CODE = ?, DISCH_HDR_VESSEL_NAME = ?, DISCH_HDR_VOY = ?, DISCH_HDR_CONSIGNEE_ID = ?, DISCH_HDR_ETA = TO_DATE('".$_POST['DISCH_HDR_ETA']."','dd-mm-yyyy hh24:mi'),
							DISCH_HDR_ETB = TO_DATE('".$_POST['DISCH_HDR_ETB']."','dd-mm-yyyy hh24:mi'), DISCH_HDR_ETD = TO_DATE('".$_POST['DISCH_HDR_ETD']."','dd-mm-yyyy hh24:mi'), DISCH_HDR_START = TO_DATE('".$DISH_START."','dd-mm-yyyy hh24:mi'),
							DISCH_HDR_END = TO_DATE('".$DISH_END."','dd-mm-yyyy hh24:mi'), DISCH_HDR_EQUIPMENT_JOB_ID = ?, DISCH_HDR_STATUS = ?
							WHERE DISCH_HDR_ID = ?";
			$this->db->query($sql,$params);

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE)
			{
			    $message = 'ERROR';
			}
		}

		return $message;
	}

	//function dishcharge detail
	function get_detail_discharge(){
		if(isset($_REQUEST['IdDischargeHdr'])){
			$this->session->set_userdata('IdDischargeHdr',$_REQUEST['IdDischargeHdr']);
		}
		$params = array(
				'BRANCH_ID' => $this->session->USER_BRANCH,
				'DISCH_DTL_HDR_ID' => $this->session->userdata('IdDischargeHdr'),
				'END' => $_REQUEST['start'] + $_REQUEST['limit'],
				'START' => $_REQUEST['start']
		);

		$sql = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (SELECT A.DISCH_DTL_ID, A.DISCH_DTL_HDR_ID, A.DISCH_DTL_CONT, A.DISCH_DTL_SIZE, C.REFF_ID SIZE_ID, C.REFF_NAME SIZE_NAME, A.DISCH_DTL_TYPE, D.REFF_ID TYPE_ID, D.REFF_NAME TYPE_NAME,
						A.DISCH_DTL_DANGER, A.DISCH_DTL_COMMODITY, A.DISCH_DTL_CONT_STATUS, E.REFF_ID STATUS_ID, E.REFF_NAME STATUS_NAME
				    FROM TX_CONV_DISCH_DTL A
				    JOIN TX_CONV_DISCH_HDR B ON A.DISCH_DTL_HDR_ID = B.DISCH_HDR_ID
				    JOIN TM_REFF C ON A.DISCH_DTL_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6
				    JOIN TM_REFF D ON A.DISCH_DTL_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5
				    JOIN TM_REFF E ON A.DISCH_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4
						WHERE DISCH_HDR_BRANCH_ID = ? AND DISCH_DTL_HDR_ID = ?
						ORDER BY A.DISCH_DTL_ID ASC) T
						WHERE ROWNUM <= ?) F
						WHERE r > = ? + 1";
		$data = $this->db->query($sql,$params)->result_array();

		$this->db->from('TX_CONV_DISCH_DTL A');
		$this->db->join('TX_CONV_DISCH_HDR B','A.DISCH_DTL_HDR_ID = B.DISCH_HDR_ID');
		$this->db->where('DISCH_HDR_BRANCH_ID',3);
		$this->db->where('DISCH_DTL_HDR_ID',$this->session->userdata('IdDischargeHdr'));
		$total = $this->db->count_all_results();

    return array (
      'data' => $data,
      'total' => $total
    );
	}

	function get_arr_discharge_container($idCont){
		$params = array('DISCH_DTL_ID' => $idCont);
		$sql = "SELECT * FROM TX_CONV_DISCH_DTL WHERE DISCH_DTL_ID = ?";
		$data = $this->db->query($sql,$params)->result_array();
		return $data;
	}

	function set_discharge_detail(){
			$message = 'SUKSES';

			if($_POST['DISCH_DTL_ID'] == ''){
				$params = array(
					'DISCH_DTL_HDR_ID' => $_POST['DISCH_DTL_HDR_ID'],
					'DISCH_DTL_CONT' => $_POST['DISCH_DTL_CONT'],
					'DISCH_DTL_DANGER' => $_POST['DISCH_DTL_DANGER'],
					'DISCH_DTL_SIZE' => $_POST['DISCH_DTL_SIZE'],
					'DISCH_DTL_COMMODITY' => $_POST['DISCH_DTL_COMMODITY'],
					'DISCH_DTL_TYPE' => $_POST['DISCH_DTL_TYPE'],
					'DISCH_DTL_CONT_STATUS' => $_POST['DISCH_DTL_CONT_STATUS'],
				);

				$this->db->trans_start();

				$sql = "INSERT INTO TX_CONV_DISCH_DTL (DISCH_DTL_HDR_ID, DISCH_DTL_CONT, DISCH_DTL_DANGER, DISCH_DTL_SIZE, DISCH_DTL_COMMODITY, DISCH_DTL_TYPE, DISCH_DTL_CONT_STATUS)
								VALUES(?,?,?,?,?,?,?)";
				$this->db->query($sql,$params);

				$this->db->trans_complete();

				if ($this->db->trans_status() === FALSE)
				{
				    $message = 'ERROR';
				}

			}
			else{
				$params = array(
					'DISCH_DTL_CONT' => $_POST['DISCH_DTL_CONT'],
					'DISCH_DTL_DANGER' => $_POST['DISCH_DTL_DANGER'],
					'DISCH_DTL_SIZE' => $_POST['DISCH_DTL_SIZE'],
					'DISCH_DTL_COMMODITY' => $_POST['DISCH_DTL_COMMODITY'],
					'DISCH_DTL_TYPE' => $_POST['DISCH_DTL_TYPE'],
					'DISCH_DTL_CONT_STATUS' => $_POST['DISCH_DTL_CONT_STATUS'],
					'DISCH_DTL_ID' => $_POST['DISCH_DTL_ID']
				);
				$this->db->trans_start();

				$sql = "UPDATE TX_CONV_DISCH_DTL SET
									DISCH_DTL_CONT = ?, DISCH_DTL_DANGER = ?, DISCH_DTL_SIZE = ?, DISCH_DTL_COMMODITY = ?, DISCH_DTL_TYPE = ?, DISCH_DTL_CONT_STATUS = ?
										WHERE DISCH_DTL_ID = ?";
				$this->db->query($sql,$params);

				$this->db->trans_complete();

				if ($this->db->trans_status() === FALSE)
				{
				    $message = 'ERROR';
				}
			}
			return $message;
	}

	function exec_discharge_detail($arrIdCont){
		$message = 'SUKSES';
		$this->db->trans_start();
		$this->db->where_in('DISCH_DTL_ID',$arrIdCont);
		$this->db->delete('TX_CONV_DISCH_DTL');
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}
		return $message;
	}

	function getPlanNoDisc(){
		$branch_id = $this->session->USER_BRANCH;
		$params = array('BRANCH_ID' => $branch_id);
		$data = $this->db->select('A.DISCH_HDR_ID ID, A.DISCH_HDR_NO NO, C.EQUIPMENT_ID, C.EQUIPMENT_NAME')
		->from('TX_CONV_DISCH_HDR A')
		->join('TX_EQUIPMENT_JOB B','A.DISCH_HDR_EQUIPMENT_JOB_ID = B.EQUIPMENT_JOB_ID')
		->join('TM_EQUIPMENT C','B.EQUIPMENT_ID = C.EQUIPMENT_ID')
		->where('A.DISCH_HDR_BRANCH_ID',$branch_id)
		->order_by('A.DISCH_HDR_NO')
		->get()->result_array();
		return $data;
	}

	function getEquipmentDisc(){
		$branch_id = $this->session->USER_BRANCH;
		$params = array('BRANCH_ID' => $branch_id);
		$data = $this->db->select('A.DISCH_HDR_ID ID, A.DISCH_HDR_NO NO, A.DISCH_HDR_VESSEL_NAME, A.DISCH_HDR_VOY, B.EQUIPMENT_JOB_ID, C.EQUIPMENT_ID, C.EQUIPMENT_NAME')
		->from('TX_CONV_DISCH_HDR A')
		->join('TX_EQUIPMENT_JOB B','A.DISCH_HDR_EQUIPMENT_JOB_ID = B.EQUIPMENT_JOB_ID')
		->join('TM_EQUIPMENT C','B.EQUIPMENT_ID = C.EQUIPMENT_ID')
		->where('A.DISCH_HDR_BRANCH_ID',$branch_id)
		->where('A.DISCH_HDR_ID',$_POST['id'])
		->get()->row_array();
		return $data;
	}

	//FUNCTION FOR LAADING PLAN
	function getPlanNoLoad(){
		$branch_id = $this->session->USER_BRANCH;
		$params = array('BRANCH_ID' => $branch_id);
		$data = $this->db->select('A.LOAD_HDR_ID ID, A.LOAD_HDR_NO NO, A.LOAD_HDR_VESSEL_NAME, A.LOAD_HDR_VOY, C.EQUIPMENT_ID, C.EQUIPMENT_NAME')
		->from('TX_CONV_LOAD_HDR A')
		->join('TX_EQUIPMENT_JOB B','A.LOAD_HDR_EQUIPMENT_JOB_ID = B.EQUIPMENT_JOB_ID')
		->join('TM_EQUIPMENT C','B.EQUIPMENT_ID = C.EQUIPMENT_ID')
		->where('A.LOAD_HDR_BRANCH_ID',$branch_id)
		->order_by('A.LOAD_HDR_NO')
		->get()->result_array();
		return $data;
	}

	function getEquipmentLoad(){
		$branch_id = $this->session->USER_BRANCH;
		$params = array('BRANCH_ID' => $branch_id);
		$data = $this->db->select('A.LOAD_HDR_ID ID, A.LOAD_HDR_NO NO, A.LOAD_HDR_VESSEL_NAME, A.LOAD_HDR_VOY, B.EQUIPMENT_JOB_ID, C.EQUIPMENT_ID, C.EQUIPMENT_NAME')
		->from('TX_CONV_LOAD_HDR A')
		->join('TX_EQUIPMENT_JOB B','A.LOAD_HDR_EQUIPMENT_JOB_ID = B.EQUIPMENT_JOB_ID')
		->join('TM_EQUIPMENT C','B.EQUIPMENT_ID = C.EQUIPMENT_ID')
		->where('A.LOAD_HDR_BRANCH_ID',$branch_id)
		->where('A.LOAD_HDR_ID',$_POST['id'])
		->get()->row_array();
		return $data;
	}

		function generate_loading_no(){
			$params = array('BRANCH' => $this->session->USER_BRANCH);
				$sql = "SELECT MAX(LOAD_HDR_NO) MAX_HDR_NO FROM TX_CONV_LOAD_HDR WHERE LOAD_HDR_BRANCH_ID = ?";
				$data = $this->db->query($sql,$params)->row_array();
				return $data;
		}

		function get_arr_loading_plan_hdr(){
			$branch_id = $this->session->USER_BRANCH;
			$params = array(
				'LOAD_HDR_BRANCH_ID' => $branch_id,
				'END' => $_REQUEST['start'] + $_REQUEST['limit'],
				'START' => $_REQUEST['start']
			);
			$query = '';
			if(!empty($_REQUEST['NO_LOADING'])){
				$query .= " AND A.LOAD_HDR_NO = ".$this->db->escape($_REQUEST['NO_LOADING']);
			}
			if(!empty($_REQUEST['CONSIGNEE'])){
				$query .= " AND B.CONSIGNEE_NAME LIKE '%" .$this->db->escape_like_str($_REQUEST['CONSIGNEE']). "%' ESCAPE '!'";
			}
			if(!empty($_REQUEST['VESSEL_NAME'])){
				$query .= " AND A.LOAD_HDR_VESSEL_NAME LIKE '%" .$this->db->escape_like_str($_REQUEST['VESSEL_NAME']). "%' ESCAPE '!'";
			}
			if(!empty($_REQUEST['STATUS'])){
				$query .= " AND C.REFF_ID = ".$this->db->escape($_REQUEST['STATUS']);
			}
			$sql = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (SELECT A.LOAD_HDR_ID, A.LOAD_HDR_NO, A.LOAD_HDR_VESSEL_CODE, A.LOAD_HDR_VESSEL_NAME, A.LOAD_HDR_CONSIGNEE_ID, B.CONSIGNEE_NAME, A.LOAD_HDR_VOY,
				to_char(A.LOAD_HDR_START, 'dd/mm/yyyy hh24:mi') LOAD_HDR_START, to_char(A.LOAD_HDR_END, 'dd/mm/yyyy hh24:mi') LOAD_HDR_END, A.LOAD_HDR_STATUS, C.REFF_NAME AS STATUS, C.REFF_ID STATUS_ID
					FROM TX_CONV_LOAD_HDR A
						JOIN TM_CONSIGNEE B ON A.LOAD_HDR_CONSIGNEE_ID = B.CONSIGNEE_ID
						JOIN TM_REFF C ON A.LOAD_HDR_STATUS = C.REFF_ID AND REFF_TR_ID = 7
						WHERE LOAD_HDR_BRANCH_ID = ? $query
									ORDER BY A.LOAD_HDR_ID DESC) T
										WHERE ROWNUM <= ?) F
											WHERE r >= ? + 1";
			$data = $this->db->query($sql,$params)->result_array();

			$sql2 = "SELECT A.LOAD_HDR_ID FROM TX_CONV_LOAD_HDR A
						JOIN TM_CONSIGNEE B ON A.LOAD_HDR_CONSIGNEE_ID = B.CONSIGNEE_ID
						JOIN TM_REFF C ON A.LOAD_HDR_STATUS = C.REFF_ID AND REFF_TR_ID = 7
						WHERE LOAD_HDR_BRANCH_ID = $branch_id $query";
			$total = $this->db->query($sql2)->result_array();

			return array (
				'data' => $data,
				'total' => count($total)
			);
		}

		function get_arr_loading_plan_hdr_by_id($idHdr){
			$params = array(
				'LOAD_HDR_ID' => $idHdr
			);
			$sql = "SELECT A.LOAD_HDR_ID, A.LOAD_HDR_NO, A.LOAD_HDR_VESSEL_CODE, A.LOAD_HDR_VESSEL_NAME, A.LOAD_HDR_CONSIGNEE_ID, B.CONSIGNEE_NAME, A.LOAD_HDR_VOY, TO_CHAR(A.LOAD_HDR_START,'dd/mm/yyyy') LOAD_HDR_START_DATE, TO_CHAR(A.LOAD_HDR_START,'hh24:mi') LOAD_HDR_START_TIME,
					TO_CHAR(A.LOAD_HDR_END,'dd/mm/yyyy') LOAD_HDR_END_DATE, TO_CHAR(A.LOAD_HDR_END,'hh24:mi') LOAD_HDR_END_TIME, A.LOAD_HDR_STATUS, C.REFF_NAME AS STATUS, TO_CHAR(A.LOAD_HDR_ETA,'dd/mm/yyyy') LOAD_HDR_ETA, TO_CHAR(A.LOAD_HDR_ETB,'dd/mm/yyyy') LOAD_HDR_ETB,
					TO_CHAR(A.LOAD_HDR_ETD,'dd/mm/yyyy') LOAD_HDR_ETD, E.REFF_ID EQUIPMENT_AREA, D.EQUIPMENT_JOB_ID EQUIPMENT
					FROM TX_CONV_LOAD_HDR A
						JOIN TM_CONSIGNEE B ON A.LOAD_HDR_CONSIGNEE_ID = B.CONSIGNEE_ID
						JOIN TM_REFF C ON A.LOAD_HDR_STATUS = C.REFF_ID AND C.REFF_TR_ID = 7
						JOIN TX_EQUIPMENT_JOB D ON A.LOAD_HDR_EQUIPMENT_JOB_ID = D.EQUIPMENT_JOB_ID
						JOIN TM_REFF E ON D.EQUIPMENT_JOB_LOCATION = E.REFF_ID AND E.REFF_TR_ID = 8
						JOIN TM_EQUIPMENT F ON D.EQUIPMENT_ID = F.EQUIPMENT_ID
						WHERE A.LOAD_HDR_ID = ?";
			$data = $this->db->query($sql,$params)->result_array();
			return array (
				'data' => $data,
				'total' => count($data)
			);
		}

		function get_arr_loading_plan_search(){
			$params = array();
		}

	function set_loading_plan(){
			$message = 'SUKSES';
			$branch_id = $this->session->USER_BRANCH;
			$user = $this->session->isId;

			$LOAD_START = $this->input->post('LOAD_HDR_START_DATE').' '.$this->input->post('LOAD_HDR_START_TIME');
			$LOAD_END = $this->input->post('LOAD_HDR_END_DATE').' '.$this->input->post('LOAD_HDR_END_TIME');

			if($_POST['LOAD_HDR_ID'] == ''){
				$params = array(
					'LOAD_HDR_NO' => $_POST['LOAD_HDR_NO'],
					'LOAD_HDR_VESSEL_CODE' => $_POST['LOAD_HDR_VESSEL_CODE'],
					'LOAD_HDR_VESSEL_NAME' => $_POST['LOAD_HDR_VESSEL_NAME'],
					'LOAD_HDR_VOY' => $_POST['LOAD_HDR_VOY'],
					'LOAD_HDR_CONSIGNEE_ID' => $_POST['LOAD_HDR_CONSIGNEE_ID'],
					'LOAD_HDR_EQUIPMENT_JOB_ID' => $_POST['EQUIPMENT'],
					'LOAD_HDR_STATUS' => 0,
					'LOAD_HDR_BRANCH_ID' => $branch_id,
					'LOAD_HDR_CREATE_BY' => $user
				);

				$this->db->trans_start();

				$sql = "INSERT INTO TX_CONV_LOAD_HDR (LOAD_HDR_NO,LOAD_HDR_VESSEL_CODE,LOAD_HDR_VESSEL_NAME,LOAD_HDR_VOY,LOAD_HDR_CONSIGNEE_ID,LOAD_HDR_ETA,LOAD_HDR_ETB,LOAD_HDR_ETD,LOAD_HDR_START,LOAD_HDR_END,LOAD_HDR_EQUIPMENT_JOB_ID,LOAD_HDR_STATUS,LOAD_HDR_BRANCH_ID, LOAD_HDR_CREATE_BY)
								VALUES(?,?,?,?,?,TO_DATE('".$_POST['LOAD_HDR_ETA']."','dd-mm-yyyy hh24:mi'),TO_DATE('".$_POST['LOAD_HDR_ETB']."','dd-mm-yyyy hh24:mi'),TO_DATE('".$_POST['LOAD_HDR_ETD']."','dd-mm-yyyy hh24:mi'),TO_DATE('".$LOAD_START."','dd-mm-yyyy hh24:mi'),TO_DATE('".$LOAD_END."','dd-mm-yyyy hh24:mi'),?,?,?,?)";
				$this->db->query($sql,$params);

				$this->db->trans_complete();

				if ($this->db->trans_status() === FALSE)
				{
				    $message = 'ERROR';
				}

			}
			else{
				$params = array(
					'LOAD_HDR_VESSEL_CODE' => $_POST['LOAD_HDR_VESSEL_CODE'],
					'LOAD_HDR_VESSEL_NAME' => $_POST['LOAD_HDR_VESSEL_NAME'],
					'LOAD_HDR_VOY' => $_POST['LOAD_HDR_VOY'],
					'LOAD_HDR_CONSIGNEE_ID' => $_POST['LOAD_HDR_CONSIGNEE_ID'],
					'LOAD_HDR_EQUIPMENT_JOB_ID' => $_POST['EQUIPMENT'],
					'LOAD_HDR_STATUS' => 0,
					'LOAD_HDR_ID' => $_POST['LOAD_HDR_ID']
				);

				$this->db->trans_start();

				$sql = "UPDATE TX_CONV_LOAD_HDR SET LOAD_HDR_VESSEL_CODE = ?, LOAD_HDR_VESSEL_NAME = ?, LOAD_HDR_VOY = ?, LOAD_HDR_CONSIGNEE_ID = ?, LOAD_HDR_ETA = TO_DATE('".$_POST['LOAD_HDR_ETA']."','dd-mm-yyyy'),
								LOAD_HDR_ETB = TO_DATE('".$_POST['LOAD_HDR_ETB']."','dd-mm-yyyy hh24:mi'), LOAD_HDR_ETD = TO_DATE('".$_POST['LOAD_HDR_ETD']."','dd-mm-yyyy hh24:mi'), LOAD_HDR_START = TO_DATE('".$LOAD_START."','dd-mm-yyyy hh24:mi'),
								LOAD_HDR_END = TO_DATE('".$LOAD_END."','dd-mm-yyyy hh24:mi'), LOAD_HDR_EQUIPMENT_JOB_ID = ?, LOAD_HDR_STATUS = ?
								WHERE LOAD_HDR_ID = ?";
				$this->db->query($sql,$params);

				$this->db->trans_complete();

				if ($this->db->trans_status() === FALSE)
				{
				    $message = 'ERROR';
				}
			}

			return $message;
		}

		//function loading detail
		function get_detail_loading(){
			if(isset($_REQUEST['IdLoadingHdr'])){
							$this->session->set_userdata('IdLoadingHdr',$_REQUEST['IdLoadingHdr']);
						}
						$params = array(
								'BRANCH_ID' => $this->session->USER_BRANCH,
								'LOAD_DTL_HDR_ID' => $this->session->userdata('IdLoadingHdr'),
								'END' => $_REQUEST['start'] + $_REQUEST['limit'],
								'START' => $_REQUEST['start']
						);

						$sql = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (SELECT A.LOAD_DTL_ID, A.LOAD_DTL_HDR_ID, A.LOAD_DTL_CONT, A.LOAD_DTL_SIZE, C.REFF_ID SIZE_ID, C.REFF_NAME SIZE_NAME, A.LOAD_DTL_TYPE, D.REFF_ID TYPE_ID, D.REFF_NAME TYPE_NAME,
										A.LOAD_DTL_DANGER, A.LOAD_DTL_COMMODITY, A.LOAD_DTL_CONT_STATUS, E.REFF_ID STATUS_ID, E.REFF_NAME STATUS_NAME
								    FROM TX_CONV_LOAD_DTL A
								    JOIN TX_CONV_LOAD_HDR B ON A.LOAD_DTL_HDR_ID = B.LOAD_HDR_ID
								    JOIN TM_REFF C ON A.LOAD_DTL_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6
								    JOIN TM_REFF D ON A.LOAD_DTL_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5
								    JOIN TM_REFF E ON A.LOAD_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4
										WHERE LOAD_HDR_BRANCH_ID = ? AND LOAD_DTL_HDR_ID = ?
										ORDER BY A.LOAD_DTL_ID ASC) T
										WHERE ROWNUM <= ?) F
										WHERE r > = ? + 1";
						$data = $this->db->query($sql,$params)->result_array();

						$this->db->from('TX_CONV_LOAD_DTL A');
						$this->db->join('TX_CONV_LOAD_HDR B','A.LOAD_DTL_HDR_ID = B.LOAD_HDR_ID');
						$this->db->where('LOAD_HDR_BRANCH_ID',3);
						$this->db->where('LOAD_DTL_HDR_ID',$this->session->userdata('IdLoadHdr'));
						$total = $this->db->count_all_results();

				    return array (
				      'data' => $data,
				      'total' => $total
				    );
		}

		function get_arr_loading_container($idCont){
			$params = array('LOAD_DTL_ID' => $idCont);
			$sql = "SELECT * FROM TX_CONV_LOAD_DTL WHERE LOAD_DTL_ID = ?";
			$data = $this->db->query($sql,$params)->result_array();
			return $data;
		}

		function set_loading_detail(){
				$message = 'SUKSES';

				if($_POST['LOAD_DTL_ID'] == ''){
					$params = array(
						'LOAD_DTL_HDR_ID' => $_POST['LOAD_DTL_HDR_ID'],
						'LOAD_DTL_CONT' => $_POST['LOAD_DTL_CONT'],
						'LOAD_DTL_DANGER' => $_POST['LOAD_DTL_DANGER'],
						'LOAD_DTL_SIZE' => $_POST['LOAD_DTL_SIZE'],
						'LOAD_DTL_COMMODITY' => $_POST['LOAD_DTL_COMMODITY'],
						'LOAD_DTL_TYPE' => $_POST['LOAD_DTL_TYPE'],
						'LOAD_DTL_CONT_STATUS' => $_POST['LOAD_DTL_CONT_STATUS'],
					);

					$this->db->trans_start();

					$sql = "INSERT INTO TX_CONV_LOAD_DTL (LOAD_DTL_HDR_ID, LOAD_DTL_CONT, LOAD_DTL_DANGER, LOAD_DTL_SIZE, LOAD_DTL_COMMODITY, LOAD_DTL_TYPE, LOAD_DTL_CONT_STATUS)
									VALUES(?,?,?,?,?,?,?)";
					$this->db->query($sql,$params);

					$this->db->trans_complete();

					if ($this->db->trans_status() === FALSE)
					{
					    $message = 'ERROR';
					}

				}
				else{
					$params = array(
						'LOAD_DTL_CONT' => $_POST['LOAD_DTL_CONT'],
						'LOAD_DTL_DANGER' => $_POST['LOAD_DTL_DANGER'],
						'LOAD_DTL_SIZE' => $_POST['LOAD_DTL_SIZE'],
						'LOAD_DTL_COMMODITY' => $_POST['LOAD_DTL_COMMODITY'],
						'LOAD_DTL_TYPE' => $_POST['LOAD_DTL_TYPE'],
						'LOAD_DTL_CONT_STATUS' => $_POST['LOAD_DTL_CONT_STATUS'],
						'LOAD_DTL_ID' => $_POST['LOAD_DTL_ID']
					);
					$this->db->trans_start();

					$sql = "UPDATE TX_CONV_LOAD_DTL SET
										LOAD_DTL_CONT = ?, LOAD_DTL_DANGER = ?, LOAD_DTL_SIZE = ?, LOAD_DTL_COMMODITY = ?, LOAD_DTL_TYPE = ?, LOAD_DTL_CONT_STATUS = ?
											WHERE LOAD_DTL_ID = ?";
					$this->db->query($sql,$params);

					$this->db->trans_complete();

					if ($this->db->trans_status() === FALSE)
					{
					    $message = 'ERROR';
					}
				}
				return $message;
		}

		function exec_loading_detail($arrIdCont){
			$message = 'SUKSES';
			$this->db->trans_start();
			$this->db->where_in('LOAD_DTL_ID',$arrIdCont);
			$this->db->delete('TX_CONV_LOAD_DTL');
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE)
			{
					$message = 'ERROR';
			}
			return $message;
		}

		//TALLY
		function getTallyDiscContainer(){
			$branch_id = $this->session->USER_BRANCH;
			$query = $this->db->select('A.DISCH_DTL_HDR_ID DTL_HDR_ID, A.DISCH_DTL_ID DTL_ID, A.DISCH_DTL_CONT DTL_CONT, A.DISCH_DTL_SIZE DTL_SIZE, C.REFF_NAME SIZE_NAME, A.DISCH_DTL_TYPE DTL_TYPE,
								D.REFF_NAME TYPE_NAME, A.DISCH_DTL_DANGER DTL_DANGER, A.DISCH_DTL_COMMODITY DTL_COMMODITY, A.DISCH_DTL_CONT_STATUS DTL_CONT_STATUS, E.REFF_NAME STATUS_NAME')
								->from('TX_CONV_DISCH_DTL A')
								->join('TX_CONV_DISCH_HDR B','A.DISCH_DTL_HDR_ID = B.DISCH_HDR_ID')
								->join('TM_REFF C','A.DISCH_DTL_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6')
								->join('TM_REFF D','A.DISCH_DTL_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5')
								->join('TM_REFF E','A.DISCH_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4')
								->where('B.DISCH_HDR_ID',$_REQUEST['id'])
								->where('B.DISCH_HDR_BRANCH_ID',$branch_id)
								->order_by('A.DISCH_DTL_CONT')
								->get()->result_array();
			return $query;
		}

		function getTallyLoadContainer(){
			$branch_id = $this->session->USER_BRANCH;
			$query = $this->db->select('A.LOAD_DTL_HDR_ID DTL_HDR_ID, A.LOAD_DTL_ID DTL_ID, A.LOAD_DTL_CONT DTL_CONT, A.LOAD_DTL_SIZE DTL_SIZE, C.REFF_NAME SIZE_NAME, A.LOAD_DTL_TYPE DTL_TYPE, D.REFF_NAME TYPE_NAME,
								A.LOAD_DTL_DANGER DTL_DANGER, A.LOAD_DTL_COMMODITY DTL_COMMODITY, A.LOAD_DTL_CONT_STATUS DTL_CONT_STATUS, E.REFF_NAME STATUS_NAME')
								->from('TX_CONV_LOAD_DTL A')
								->join('TX_CONV_LOAD_HDR B','A.LOAD_DTL_HDR_ID = B.LOAD_HDR_ID')
								->join('TM_REFF C','A.LOAD_DTL_SIZE = C.REFF_ID AND C.REFF_TR_ID = 6')
								->join('TM_REFF D','A.LOAD_DTL_TYPE = D.REFF_ID AND D.REFF_TR_ID = 5')
								->join('TM_REFF E','A.LOAD_DTL_CONT_STATUS = E.REFF_ID AND E.REFF_TR_ID = 4')
								->where('B.LOAD_HDR_ID',$_REQUEST['id'])
								->where('B.LOAD_HDR_BRANCH_ID',$branch_id)
								->order_by('A.LOAD_DTL_CONT')
								->get()->result_array();
			return $query;
		}




}
