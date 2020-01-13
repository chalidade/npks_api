<?php
class M_cmsconfig extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	function getCmsConfig(){
		$branch_id = $this->session->userdata('USER_BRANCH');
		return $this->db->where('BRANCH',$branch_id)->where('STATUS','Y')->get('TM_CMS_CONFIG')->result_array();
	}

	function getAllVariables(){
		$param[] = $this->session->userdata('USER_BRANCH');

		$sql = "SELECT * FROM TM_CMS_CONFIG WHERE BRANCH = ?";
		$data = $this->db->query($sql,$param)->result();
		return $data;
	}

	function setConfigVariables(){
		
		$branch_id = $this->session->userdata('USER_BRANCH');

		

		$this->db->trans_begin();

		foreach ($this->input->post(NULL, TRUE) as $key => $value) {
			$this->db->set('STATUS',$value);
	        $this->db->where('CONFIG',$key);
	       	$this->db->where('BRANCH', $branch_id);
			$this->db->update('TM_CMS_CONFIG');
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
