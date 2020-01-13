<?php
class M_disablecell extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	function getTier(){
		$branch_id = $this->session->userdata('USER_BRANCH');
		$yard = $this->input->get('yard');
		$block = $this->input->get('block');
		$slot = $this->input->get('slot');
		$row = $this->input->get('row');

		// get yard data
		$ybc = $this->db->where('YBC_BRANCH_ID',$branch_id)->where('YBC_YARD_ID',$yard)->where('YBC_BLOCK_ID',$block)->where('YBC_SLOT',$slot)->where('YBC_ROW',$row)->get('TX_YARD_BLOCK_CELL')->row_array();

		// get max tier form block
		$maxTier = $this->db->select('BLOCK_NAME, BLOCK_TIER')->from('TM_BLOCK')->where('BLOCK_ID',$ybc['YBC_BLOCK_ID'])->where('BLOCK_BRANCH_ID',$branch_id)->get()->row_array()['BLOCK_TIER'];

		// $tierData = $this->db->select('A.*')->from('TX_CELL_DISABLE A')
		// 						->join('TX_YARD_BLOCK_CELL B','B.YBC_ID = A.CELL_YCB_ID')
		// 						->where('YCB_ID',$ybc['YBC_BLOCK_ID'])
		// 						->where('A.CELL_TIER')
		// 						->get()->row_array();

		$data = array();
		for ($i=0; $i < (int)$maxTier; $i++) {

			$tierData = $this->db->select('A.*')->from('TX_CELL_DISABLE A')
									->join('TX_YARD_BLOCK_CELL B','B.YBC_ID = A.CELL_YCB_ID','left')
									->where('B.YBC_ID',$ybc['YBC_ID'])
									->where('A.CELL_TIER',$i+1)
									->count_all_results();
			if($tierData > 0){
				$status = 'Y';
			}
			else{
				$status = 'T';
			}
				$data[$i]['TIER'] = $i+1;
				$data[$i]['DISABLED'] = $status;
				$data[$i]['MAX'] = (int)$maxTier;
		}

		return $data;
	}

	function getAllVariables(){
		$param[] = $this->session->userdata('USER_BRANCH');

		$sql = "SELECT * FROM TM_CMS_CONFIG WHERE BRANCH = ?";
		$data = $this->db->query($sql,$param)->result();
		return $data;
	}

	function setDisable(){

		$branch_id = $this->session->userdata('USER_BRANCH');
		$user = $this->session->userdata('isId');
		$yard = $this->input->get_request_header('yard');
		$block = $this->input->get_request_header('block');
		$slot = $this->input->get_request_header('slot');
		$row = $this->input->get_request_header('row');

		$this->db->trans_begin();

		// get yard data
		$ybc = $this->db->where('YBC_BRANCH_ID',$branch_id)->where('YBC_YARD_ID',$yard)->where('YBC_BLOCK_ID',$block)->where('YBC_SLOT',$slot)->where('YBC_ROW',$row)->get('TX_YARD_BLOCK_CELL')->row_array();

		// detele current ybc_id
		$this->db->delete('TX_CELL_DISABLE', array('CELL_YARD_ID' => $yard, 'CELL_YCB_ID' => $ybc['YBC_ID']));

		foreach ($this->input->post(NULL, TRUE) as $key => $value) {
			if($value != 0){
				$this->db->set('CELL_YARD_ID',$yard);
				$this->db->set('CELL_YCB_ID',$ybc['YBC_ID']);
				$this->db->set('CELL_BRANCH_ID',$branch_id);
				$this->db->set('CELL_CREATE_BY',$user);
				$this->db->set('CELL_TIER',$value);
				$this->db->insert('TX_CELL_DISABLE');
			}
		}

		if ($this->db->trans_status() === FALSE)
		{
	        $this->db->trans_rollback();
	        return array('success' => false, 'message' => 'Failed to update data');
		}
		else
		{
	        $this->db->trans_commit();
	        return array('success' => true, 'message' => 'Data successfully updated');
		}

	}
}
