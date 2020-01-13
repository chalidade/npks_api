<?php
class M_vesselvoyage extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	function getVoyage(){
		$branch_id = $this->session->USER_BRANCH;
		$params = array(
			'VOY_BRANCH_ID' => $branch_id,
			'END' => $_REQUEST['start'] + $_REQUEST['limit'],
			'START' => $_REQUEST['start']
		);
		$query = '';
		if(!empty($_REQUEST['VOYAGE'])){
			$query .= " AND A.VOY_CODE = ".$this->db->escape($_REQUEST['VOYAGE']);
		}
		if(!empty($_REQUEST['ARRIVAL'])){
			// $query .= " AND A.ATA = to_date($_REQUEST['ARRIVAL']),'dd/mm/yyyy')";
		}
		if(!empty($_REQUEST['DEPARTURE'])){
			$query .= " AND A.ATD LIKE '%" .$this->db->escape_like_str($_REQUEST['DEPARTURE']). "%' ESCAPE '!'";
		}
		$sql = "SELECT F.* FROM (SELECT T.*, ROWNUM r FROM (SELECT A.VOY_CODE, A.VOY_VESSEL_CODE||' '||A.VOY_IN||'-'||A.VOY_OUT AS VESVOY, B.VESSEL_NAME, to_char(A.ETD,'dd/mm/yyyy hh24:mi') ETD, to_char(A.ATA,'dd/mm/yyyy hh24:mi') ATA, to_char(A.ATB,'dd/mm/yyyy hh24:mi') ATB
				FROM TX_VOYAGE A
					JOIN TM_VESSEL B ON A.VOY_VESSEL_CODE = B.VESSEL_CODE
					WHERE VOY_BRANCH_ID = ? $query
								ORDER BY A.VOY_ID DESC) T
									WHERE ROWNUM <= ?) F
										WHERE r >= ? + 1";
		$data = $this->db->query($sql,$params)->result_array();

		$sql2 = "SELECT A.VOY_ID FROM TX_VOYAGE A
					JOIN TM_VESSEL B ON A.VOY_VESSEL_CODE = B.VESSEL_CODE
					WHERE VOY_BRANCH_ID = $branch_id $query";
		$total = $this->db->query($sql2)->result_array();

		return array (
			'data' => $data,
			'total' => count($total)
		);
	}

	function setVoyage(){
		$branch_id = $this->session->USER_BRANCH;
		$user = $this->session->isId;
		$year = date('Y');
		$ETA = $this->input->post('ETA_DATE').' '.$this->input->post('ETA_TIME');
		$ETB = $this->input->post('ETB_DATE').' '.$this->input->post('ETB_TIME');
		$ETD = $this->input->post('ETD_DATE').' '.$this->input->post('ETD_TIME');
		$VOYAGE_ID = $this->generate_voyage_no($this->input->post('VESSEL'),$year);

		$message = 'SUCCESS';

		$this->db->trans_start();

		$this->db->set('VOY_CODE', $VOYAGE_ID);
		$this->db->set('VOY_VESSEL_CODE', $this->input->post('VESSEL'));
		$this->db->set('YEAR', $year);
		$this->db->set('VOY_IN',  $this->input->post('VOY_OUT'));
		$this->db->set('VOY_OUT', $this->input->post('VOY_OUT'));
		$this->db->set('ETA', "to_date('$ETA','dd/mm/yyyy hh24:mi')",false);
		$this->db->set('ETB', "to_date('$ETB','dd/mm/yyyy hh24:mi')",false);
		$this->db->set('ETD', "to_date('$ETD','dd/mm/yyyy hh24:mi')",false);
		$this->db->set('VOY_BRANCH_ID', $branch_id);
		$this->db->set('VOY_CREATE_BY', $user);
		$this->db->insert('TX_VOYAGE');

		$this->db->trans_complete();

    if ($this->db->trans_status() === FALSE)
    {
        $message = 'ERROR';
    }
		return $message;
	}

	private function generate_voyage_no($vessel,$year){
		$branch_id = $this->session->USER_BRANCH;
		$data = $this->db->select('MAX(VOY_CODE) AS VOY_CODE')
									 ->from('TX_VOYAGE')
									 ->where('VOY_VESSEL_CODE',$vessel)
									 ->where('YEAR',$year)
									 ->where('VOY_BRANCH_ID',$branch_id)
									 ->get()->row_array();
		$data['VOY_CODE'];
		if($data['VOY_CODE'] == null){
			$noUrut = 1;
		}else{
			$noUrut = (int) substr($data['VOY_CODE'], -3);
			$noUrut++;
		}
		$sNew_voy_no = $vessel."".$year."" . sprintf("%03s", $noUrut);

		return $sNew_voy_no;
	}

	public function getVoyById(){
		$branch_id = $this->session->USER_BRANCH;

		$arrData = $this->db->select("CONCAT(CONCAT(B.VESSEL_NAME,' - '),A.VOY_CODE) VESSEL, A.VOY_CODE, B.VESSEL_CODE, B.VESSEL_NAME, A.VOY_IN, A.VOY_OUT, to_char(A.ATA,'dd/mm/yyyy hh24:mi') ATA,
				 to_char(A.ATB,'dd/mm/yyyy hh24:mi') ATB, to_char(A.ATD,'dd/mm/yyyy hh24:mi') ATD, to_char(A.ETA,'dd/mm/yyyy hh24:mi') ETA,
		 		 to_char(A.ETB,'dd/mm/yyyy hh24:mi') ETB, to_char(A.ETD,'dd/mm/yyyy hh24:mi') ETD")
				 ->from('TX_VOYAGE A')
				 ->join('TM_VESSEL B','A.VOY_VESSEL_CODE = B.VESSEL_CODE')
				 ->where('VOY_BRANCH_ID',$branch_id)
				 ->where('VOY_CODE',$this->input->post('VOY_ID'))
				 ->get()->row_array();

		return $arrData;
	}

}
