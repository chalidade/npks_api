<?php

class M_user extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

	public function getAllUserMenu($user_id,$parent_id = false){
		$params = array(
	        'USER_ID' => $user_id
	    );
	    $whereParamId = " AND M.MENU_PARENT IS NULL";
		if($parent_id){
			$params[] = $parent_id;
			$whereParamId = " AND M.MENU_PARENT = ?";
		}
		$sql = "SELECT M.MENU_ID, M.MENU_TITLE, M.MENU_CONTROLLER, M.MENU_REGION, M.MENU_METHOD, M.MENU_TEXT, M.MENU_ITEM_ID, M.MENU_PARENT FROM TM_USER U 
				JOIN TR_ROLE R ON U.USER_ROLE = R.ROLE_ID
				JOIN TR_ROLE_ACCESS RA ON RA.ROLE_ID = R.ROLE_ID
				JOIN TR_MENU M ON M.MENU_ID = RA.ROLE_MENU_ID
				WHERE U.USER_ID = ?". $whereParamId;
			
		$data = $this->db->query($sql,$params)->result();
    	return $data;

	}

	public function getUsers($filter){

		$params['USER_ID'] 	= $filter['USER_ID'];

		$params['END'] 		= $_REQUEST['start'] + $_REQUEST['limit'];
		$params['START'] 	= $_REQUEST['start'];

		$paramsTotal['USER_ID'] = $filter['USER_ID'];
		$whereParams = '';
		if($filter['USER_NAME']){
			$params['USER_NAME'] 	= '%' . $filter['USER_NAME'] . '%';
			$paramsTotal['USER_NAME'] = '%' . $filter['USER_NAME'] . '%';
			$whereParams .= ' AND LOWER(USER_NAME) LIKE ?';
		}
		if($filter['USER_ROLE']){
			$params['USER_ROLE'] = '%' . $filter['USER_ROLE'] . '%';
			$paramsTotal['USER_ROLE'] = '%' . $filter['USER_ROLE'] . '%';
			$whereParams .= ' AND LOWER(USER_ROLE) LIKE ?';
		}
		if($filter['USER_NIK']){
			$params['USER_NIK'] = '%' . $filter['USER_NIK'] . '%';
			$paramsTotal['USER_NIK'] = '%' . $filter['USER_NIK'] . '%';
			$whereParams .= ' AND LOWER(USER_NIK) LIKE ?';
		}
		if($filter['USER_BRANCH']){
			$params['USER_BRANCH'] = '%' . $filter['USER_BRANCH'] . '%' ;
			$paramsTotal['USER_BRANCH'] = '%' . $filter['USER_BRANCH'] . '%';
			$whereParams .= ' AND LOWER(USER_BRANCH) LIKE ?';
		}
		if($filter['USER_GROUP']){
			$params['USER_GROUP'] = '%' . $filter['USER_GROUP'] . '%';
			$paramsTotal['USER_GROUP'] = '%' . $filter['USER_GROUP'] . '%';
			$whereParams .= ' AND LOWER(USER_GROUP) LIKE ?';
		}

		$sql = "
				SELECT * FROM 
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
							SELECT * FROM USER_VIEW WHERE USER_ID != ? ORDER BY USER_ID
					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1" . $whereParams;		
		
		$data = $this->db->query($sql,$params)->result();

		$sqlTotal = "SELECT USER_ID FROM USER_VIEW WHERE USER_ID != ? " . $whereParams;

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
	}

	public function getGroup($filter){
		$params = [];
		$whereParams = '';
		if($filter['GROUP_NAME']){
			$params['GROUP_NAME'] = '%' . $filter['GROUP_NAME'] . '%';
			$whereParams .= ' WHERE LOWER(GROUP_NAME) LIKE ?';
		}
		
		$sql = "SELECT * FROM TR_GROUP" . $whereParams;
		
		$data = $this->db->query($sql,$params)->result();
		return array (
	      'data' => $data,
	      'total' => count($data)
	    );
	}
	public function checkUser($username){
		$data = $this->db->select('USER_ID')
		 					->from('TM_USER')
							->where('USER_NAME', $username)
							->get()->result();
	    return $data;
	}
	public function getBranch($filter){
		$params = [];
		$whereParams = '';
		if($filter['BRANCH_NAME']){
			$params['BRANCH_NAME'] = '%' . $filter['BRANCH_NAME'] . '%';
			$whereParams .= ' WHERE LOWER(BRANCH_NAME) LIKE ?';
		}		
		
		$sql = "SELECT * FROM TR_BRANCH" . $whereParams;
		
		$data = $this->db->query($sql,$params)->result();
		return array (
	      'data' => $data,
	      'total' => count($data)
	    );
	}

	public function addMoreUser(){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'USER_NAME' => $this->input->post('USER_NAME'),
			'USER_PASSWD' => $this->password_hash(hash('sha256', '123456')),
			'USER_ROLE' => $this->input->post('USER_ROLE'),
			'USER_NIK' => $this->input->post('USER_NIK'),
			'USER_BRANCH_ID' => $this->input->post('USER_BRANCH'),
			'USER_GROUP_ID' => $this->input->post('USER_GROUP')
		);
		//die(var_dump($arrData));
		$this->db->insert('TM_USER',$arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	private function password_hash($password) {
	    $options = array(
	      'cost' => 12,
	    );
	    $hash = password_hash($password, PASSWORD_BCRYPT, $options);
	    return $hash;
  	}
}