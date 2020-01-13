<?php

class M_menu extends CI_Model {
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
		$sql = "SELECT M.MENU_ID, M.MENU_TITLE, M.MENU_CONTROLLER, M.MENU_REGION, M.MENU_METHOD, M.MENU_TEXT, M.MENU_ITEM_ID, M.MENU_PARENT, B.BRANCH_NAME, M.MENU_ICON FROM TM_USER U
				JOIN TR_ROLE R ON U.USER_ROLE = R.ROLE_ID
				JOIN TR_ROLE_ACCESS RA ON RA.ROLE_ID = R.ROLE_ID AND RA.BRANCH_ID = U.USER_BRANCH_ID
				JOIN TR_MENU M ON M.MENU_ID = RA.ROLE_MENU_ID
				JOIN TR_BRANCH B ON B.BRANCH_ID = RA.BRANCH_ID
				WHERE U.USER_ID = ?". $whereParamId;

		$data = $this->db->query($sql,$params)->result();
    	return $data;

	}

	public function getAllDataMenu($title = false){
		$title = strtolower($title);
		$params['END'] 		= $_REQUEST['start'] + $_REQUEST['limit'];
		$params['START'] 	= $_REQUEST['start'];

	    $whereParamName = "";

		if($title){
			$whereParamName = " WHERE LOWER(CHILD.MENU_TITLE) LIKE '%".$this->db->escape_like_str($title)."%' ";
		}
		$sql = "
			SELECT * FROM 
				(
				SELECT TABLE_1.*, rownum AS rnum FROM (
					SELECT
						CHILD.MENU_ID,
						CHILD.MENU_TITLE,
						CHILD.MENU_CONTROLLER,
						CHILD.MENU_REGION,
						CHILD.MENU_METHOD,
						CHILD.MENU_TEXT,
						CHILD.MENU_ITEM_ID,
						PARENT.MENU_TITLE AS PARENT_TITLE,
						PARENT.MENU_ID AS PARENT_ID
					FROM TR_MENU CHILD
					LEFT JOIN TR_MENU PARENT ON CHILD.MENU_PARENT = PARENT.MENU_ID " . $whereParamName . " 
				) TABLE_1
				WHERE ROWNUM <= ?
			)
			WHERE  rnum >= ? + 1";
		
		$data = $this->db->query($sql,$params)->result();

		$sqlTotal = "SELECT
						CHILD.MENU_ID,
						CHILD.MENU_TITLE,
						CHILD.MENU_CONTROLLER,
						CHILD.MENU_REGION,
						CHILD.MENU_METHOD,
						CHILD.MENU_TEXT,
						CHILD.MENU_ITEM_ID,
						PARENT.MENU_TITLE AS PARENT_TITLE,
						PARENT.MENU_ID AS PARENT_ID
					FROM TR_MENU CHILD
					LEFT JOIN TR_MENU PARENT ON CHILD.MENU_PARENT = PARENT.MENU_ID " . $whereParamName;
		$dataTotal = $this->db->query($sqlTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
    	//return $data;
	}

	public function getAllDataMenuList(){
		$sqlTotal = "SELECT
						CHILD.MENU_ID,
						CHILD.MENU_TITLE,
						CHILD.MENU_CONTROLLER,
						CHILD.MENU_REGION,
						CHILD.MENU_METHOD,
						CHILD.MENU_TEXT,
						CHILD.MENU_ITEM_ID,
						PARENT.MENU_TITLE AS PARENT_TITLE,
						PARENT.MENU_ID AS PARENT_ID
					FROM TR_MENU CHILD
					LEFT JOIN TR_MENU PARENT ON CHILD.MENU_PARENT = PARENT.MENU_ID ";
		$dataTotal = $this->db->query($sqlTotal)->result();
		return array (
	      'data' => $dataTotal,
	      'total' => count($dataTotal)
	    );
	}
	public function addMenu(){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'MENU_TITLE' => $this->input->post('MENU_TITLE'),
			'MENU_CONTROLLER' => $this->input->post('MENU_CONTROLLER'),
			'MENU_REGION' => $this->input->post('MENU_REGION'),
			'MENU_METHOD' => $this->input->post('MENU_METHOD'),
			'MENU_TEXT' => $this->input->post('MENU_TEXT'),
			'MENU_ITEM_ID' => $this->input->post('MENU_ITEM_ID'),
			'MENU_PARENT' => $this->input->post('MENU_PARENT')
		);

		$this->db->insert('TR_MENU',$arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}
	public function getAllAccessMenu($filter){

		
		$params['END'] 		= 1;
		$params['START'] 	= 0;
		if(isset($_REQUEST['start']) &&  isset($_REQUEST['limit'])){
			$params['END'] 		= $_REQUEST['start'] + $_REQUEST['limit'];
			$params['START'] 	= $_REQUEST['start'];
		}

		$whereParams = '';
		if(isset($filter['ROLE_NAME']) || isset($filter['MENU_TITLE'])){
			if($filter['ROLE_NAME'] && $filter['MENU_TITLE']){
				$whereParams = " WHERE LOWER(TROLE.ROLE_NAME) LIKE '%".$this->db->escape_like_str(strtolower($filter['ROLE_NAME']))."%' AND LOWER(TMENU.MENU_TITLE) LIKE '%".$this->db->escape_like_str(strtolower($filter['MENU_TITLE']))."%'";
			}
			else if($filter['ROLE_NAME']){
				$whereParams = " WHERE LOWER(TROLE.ROLE_NAME) LIKE '%".$this->db->escape_like_str(strtolower($filter['ROLE_NAME']))."%'";
			}
			else if($filter['MENU_TITLE']){
				$whereParams = " WHERE LOWER(TMENU.MENU_TITLE) LIKE '%".$this->db->escape_like_str(strtolower($filter['MENU_TITLE']))."%'";
			}
		}

		else if(isset($filter['ROLE_ID']) && isset($filter['ROLE_MENU_ID'])){
			$whereParams = " WHERE TACCESS.ROLE_ID = ".$this->db->escape($filter['ROLE_ID'])." AND TACCESS.ROLE_MENU_ID = ".$this->db->escape($filter['ROLE_MENU_ID'])." ";
		}
		$sql = "SELECT * FROM 
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						SELECT
							TACCESS.ROLE_ACCESS_ID,
							TROLE.ROLE_NAME,
							TMENU.MENU_TITLE,
							TBRANCH.BRANCH_NAME
						FROM
							TR_ROLE_ACCESS TACCESS
						LEFT JOIN
							TR_MENU TMENU
						ON
							TACCESS.ROLE_MENU_ID = TMENU.MENU_ID
						LEFT JOIN
							TR_BRANCH TBRANCH
						ON
							TACCESS.BRANCH_ID = TBRANCH.BRANCH_ID
						LEFT JOIN
							TR_ROLE TROLE
						ON
							TACCESS.ROLE_ID = TROLE.ROLE_ID " . $whereParams ."
					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1";

		$data = $this->db->query($sql,$params)->result();

		$sqlTotal = "SELECT
							TACCESS.ROLE_ACCESS_ID,
							TROLE.ROLE_NAME,
							TMENU.MENU_TITLE,
							TBRANCH.BRANCH_NAME
						FROM
							TR_ROLE_ACCESS TACCESS
						LEFT JOIN
							TR_MENU TMENU
						ON
							TACCESS.ROLE_MENU_ID = TMENU.MENU_ID
						LEFT JOIN
							TR_BRANCH TBRANCH
						ON
							TACCESS.BRANCH_ID = TBRANCH.BRANCH_ID
						LEFT JOIN
							TR_ROLE TROLE
						ON
							TACCESS.ROLE_ID = TROLE.ROLE_ID " . $whereParams;

		$dataTotal = $this->db->query($sqlTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );

	}

	public function checkMenu($filter){
		$sql = "SELECT ROLE_ACCESS_ID FROM TR_ROLE_ACCESS WHERE ROLE_ID = ? AND ROLE_MENU_ID = ? AND BRANCH_ID = ?";
		$data = $this->db->query($sql,$filter)->result();

		return $data;
	}

	public function getAllRole($filter){
		$params = array(
	        'ROLE_NAME' => $filter['ROLE_NAME']
	    );
	    $whereParam = "";
		if($filter['ROLE_NAME']){

			$whereParam = " WHERE ROLE_NAME = ?";
		}
		$sql = "SELECT ROLE_ID, ROLE_NAME FROM TR_ROLE". $whereParam;

		$data = $this->db->query($sql,$params)->result();
    	return $data;
	}

	public function addRoleAccess(){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'ROLE_ID' => $this->input->post('ROLE_ID'),
			'ROLE_MENU_ID' => $this->input->post('ROLE_MENU_ID'),
			'BRANCH_ID' => $this->input->post('BRANCH_ID')
		);

		//die(var_dump($arrData));

		$this->db->insert('TR_ROLE_ACCESS',$arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			$message = 'ERROR';
		}

		return $message;
	}
}
