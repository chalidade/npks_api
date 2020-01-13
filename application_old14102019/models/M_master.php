<?php
class M_master extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	function get_arr_reff_master(){
		$params = array('REFF_ID' => $_REQUEST['reff_id']);
		$sql = "SELECT A.REFF_ID, A.REFF_NAME, A.REFF_ORDER FROM TM_REFF A
      JOIN TR_REFF B ON A.REFF_TR_ID = B.REFF_ID
          WHERE B.REFF_ID = ?";
    $data = $this->db->query($sql,$params)->result_array();
    return $data;
	}

  function get_arr_master_status($id){
    $params = array('REFF_ID' => $id);
    $sql = "SELECT A.REFF_ID, A.REFF_NAME, A.REFF_ORDER FROM TM_REFF A
      JOIN TR_REFF B ON A.REFF_TR_ID = B.REFF_ID
          WHERE B.REFF_ID = ?";
    $data = $this->db->query($sql,$params)->result_array();
    return $data;
  }

  function get_arr_consignee($filter){
    $params = array();
    $sql = "SELECT * FROM TM_CONSIGNEE
						WHERE LOWER(CONSIGNEE_NAME) LIKE '%".strtolower($filter)."%'
						ORDER BY CONSIGNEE_NAME";
    $data = $this->db->query($sql)->result_array();
    return $data;
  }

  function get_arr_equipment_job($reffID){
    $params = array(
        'EQUIPMENT_JOB_LOCATION' => $reffID,
        'BRANCH' => $this->session->USER_BRANCH
    );
    $sql = "SELECT A.EQUIPMENT_JOB_ID, A.EQUIPMENT_JOB_LOCATION, B.REFF_ID, B.REFF_NAME, C.EQUIPMENT_NAME
              FROM TX_EQUIPMENT_JOB A
              JOIN TM_REFF B ON A.EQUIPMENT_JOB_LOCATION = B.REFF_ID
              JOIN TM_EQUIPMENT C ON A.EQUIPMENT_ID = C.EQUIPMENT_ID
              WHERE A.EQUIPMENT_JOB_LOCATION = ? AND A.EQUIPMENT_JOB_BARANCH_ID = ?";
    $data = $this->db->query($sql,$params)->result_array();
    return $data;
  }

	function get_arr_vessel($filter){
		$params = array('BRANCH' => $this->session->USER_BRANCH);
		$sql = "SELECT * FROM TM_VESSEL WHERE VESSEL_BRANCH_ID = ?
						AND LOWER(VESSEL_NAME) LIKE '%".strtolower($filter)."%'
						OR LOWER(VESSEL_CODE) LIKE '%".strtolower($filter)."%'
						ORDER BY VESSEL_NAME";
		$data = $this->db->query($sql,$params)->result_array();
		return $data;
	}

	function getUsers(){
		$data = $this->db->get('TM_USER')->where('USER_DELETE_STATUS', 0)->result_array();
		return $data;//array('data' => $data);
	}

	public function get_port_list(){
		$filter = isset($_GET['query']) ? $_GET['query'] : 0;
		$query 		= "SELECT PORT_CODE, PORT_CODE||'-'||PORT_NAME PORT_NAME FROM TM_PORT
		WHERE LOWER(PORT_NAME) LIKE '%".strtolower($filter)."%'
			OR LOWER(PORT_CODE) LIKE '%".strtolower($filter)."%'
		ORDER BY PORT_CODE";
		$rs 		= $this->db->query($query);
		$data 		= $rs->result_array();

		return $data;
	}

	public function get_shipping_list($filter){
		$query 		= "SELECT ID_SHIPPING, ID_SHIPPING||' - '||SHIPPING_NAME SHIPPING_NAME FROM TM_SHIPPING
		WHERE LOWER(SHIPPING_NAME) LIKE '%".strtolower($filter)."%'
			OR LOWER(ID_SHIPPING) LIKE '%".strtolower($filter)."%'
		ORDER BY SHIPPING_NAME";
		$rs 		= $this->db->query($query);
		$data 		= $rs->result_array();
		return $data;
	}

	public function getEquipment(){
		$branch_id = $this->session->USER_BRANCH;
		$data = $this->db->select('EQUIPMENT_ID, EQUIPMENT_NAME')
		->from('TM_EQUIPMENT')
		->join('')
		->where('EQUIPMENT_BRANCH_ID',$branch_id)
		->get()->result_array();
		return $data;
	}

	public function getVoyage(){
		$branch_id = $this->session->USER_BRANCH;
		$query = isset($_REQUEST['query']) ? strtoupper($_REQUEST['query']) : 0;
		$arrData = $this->db->select("A.VOY_CODE, B.VESSEL_NAME")
				 ->from('TX_VOYAGE A')
				 ->join('TM_VESSEL B','A.VOY_VESSEL_CODE = B.VESSEL_CODE')
				 ->where('VOY_BRANCH_ID',$branch_id)
				 ->like('A.VOY_CODE',$query)
				 ->order_by('VOY_CODE','ASC')
				 ->get()->result_array();

		return $arrData;
	}

	function get_placement_activity(){
    $sql = "SELECT A.REFF_ID, A.REFF_NAME, A.REFF_ORDER FROM TM_REFF A
      JOIN TR_REFF B ON A.REFF_TR_ID = B.REFF_ID
          WHERE B.REFF_ID = 22 AND A.REFF_ID NOT IN (4,5,6,7,8,9)";
    $data = $this->db->query($sql)->result_array();
    return $data;
  }

	function get_yard_activity(){
    $sql = "SELECT A.REFF_ID, A.REFF_NAME, A.REFF_ORDER FROM TM_REFF A
      JOIN TR_REFF B ON A.REFF_TR_ID = B.REFF_ID
          WHERE B.REFF_ID = 22 AND A.REFF_ID IN (1,2,3)";
    $data = $this->db->query($sql)->result_array();
    return $data;
  }

	function get_origin_placement(){
    $sql = "SELECT A.REFF_ID, A.REFF_NAME, A.REFF_ORDER FROM TM_REFF A
      JOIN TR_REFF B ON A.REFF_TR_ID = B.REFF_ID
          WHERE B.REFF_ID = 20 AND A.REFF_ID NOT IN ('INTERNAL')";
    $data = $this->db->query($sql)->result_array();
    return $data;
  }

	function get_cmsConfig(){
		$branch_id = $this->session->USER_BRANCH;
		$data = $this->db->select('*')->where('BRANCH',$branch_id)->get('TM_CMS_CONFIG')->result_array();
		return $data;
	}

	function get_branch(){
		$branch_id = $this->session->USER_BRANCH;
		$data = $this->db->select('BRANCH_NAME')->where('BRANCH_ID',$branch_id)->get('TR_BRANCH')->row_array()['BRANCH_NAME'];
		return $data;
	}

	function getInterval(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$data = $this->db->where('BRANCH',$branch_id)->where('USER_ID',$user_id)->get('TM_INTERVAL')->row_array()['INTERVAL'];
		$interval = (count($data) > 0)? (int)$data : 2;
		return array('INTERVAL' => $interval);
	}

	function setInterval(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$interval = $this->input->post('interval');
		$message = 'Interval Updated';

		$this->db->trans_start();

		$data = $this->db->where('BRANCH',$branch_id)->where('USER_ID',$user_id)->from('TM_INTERVAL')->count_all_results();
		if($data > 0){
			$this->db->where('BRANCH',$branch_id)->where('USER_ID',$user_id)->update('TM_INTERVAL',array('INTERVAL' => $interval));
		}
		else{
			$arrData = array('INTERVAL' => $interval, 'BRANCH' => $branch_id, 'USER_ID' => $user_id);
			$this->db->insert('TM_INTERVAL',$arrData);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'Failed Update Interval';
		}

		return $message;

	}

	public function setPrinter(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$message = 'SUCCESS';
		$sukses = true;
		$name = $this->input->post('PRINTER');
		$desc = $this->input->post('DESC');

		$this->db->trans_start();
		$data = $this->db->where('BRANCH_ID',$branch_id)->where('USER_ID', $user_id)->from('TM_PRINTER')->count_all_results();
		if($data <= 0){
			$this->db->insert('TM_PRINTER',array('PRINTER' => $name, 'DESC_' => $desc, 'BRANCH_ID' => $branch_id, 'USER_ID' => $user_id));
		}
		else{
			$this->db->set('PRINTER',$name)->set('DESC_',$desc)->where('BRANCH_ID',$branch_id)->where('USER_ID', $user_id)->update('TM_PRINTER');
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'FAILED';
				$sukses = false;
		}
		return array('success' => $sukses, 'message' => $message);

	}

	public function getPrinter(){
		$branch_id = $this->session->USER_BRANCH;
		$user_id = $this->session->isId;
		$data = $this->db->where('BRANCH_ID',$branch_id)->where('USER_ID', $user_id)->get('TM_PRINTER')->row_array();
		return $data;
	}

}
