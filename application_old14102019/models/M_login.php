<?php
class M_login extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

  function do_login(){
  		$param		= array(strtolower($this->input->post('username')));
  		$query 		= "SELECT A.USER_ID, A.USER_NAME, A.USER_PASSWD, A.USER_ROLE, A.USER_NIK, A.USER_BRANCH_ID, A.USER_YARD, A.FULL_NAME
          FROM TM_USER A
          WHERE A.USER_DELETE_STATUS = 0 AND LOWER(A.USER_NAME) = ?";
  		$data = $this->db->query($query,$param);
  		return $data;
  }

  private function password_hash($password) {
    $options = array(
      'cost' => 12,
    );
    $hash = password_hash($password, PASSWORD_BCRYPT, $options);
    return $hash;
  }

}
