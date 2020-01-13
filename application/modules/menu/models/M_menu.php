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
				JOIN TR_ROLE R ON U.USER_ROLE = R.ROLE_ID AND R.ROLE_DELETE_STATUS = 0 
				JOIN TR_ROLE_ACCESS RA ON RA.ROLE_ID = R.ROLE_ID AND RA.BRANCH_ID = U.USER_BRANCH_ID AND RA.ROLE_ACCESS_DELETE_STATUS = 0
				JOIN TR_MENU M ON M.MENU_ID = RA.ROLE_MENU_ID
				JOIN TR_BRANCH B ON B.BRANCH_ID = RA.BRANCH_ID
				WHERE M.MENU_DELETE_STATUS = '0' AND U.USER_DELETE_STATUS = 0 AND U.USER_ID = ? ". $whereParamId . " ORDER BY M.MENU_ORDER ASC";

		$data = $this->db->query($sql,$params)->result();
    	return $data;

	}

	public function getAllDataMenu($title = false){
		$title = strtolower($title);
		$params['END'] 		= $_REQUEST['start'] + $_REQUEST['limit'];
		$params['START'] 	= $_REQUEST['start'];

	    $whereParamName = "";

		if($title){
			$whereParamName = " AND LOWER(CHILD.MENU_TITLE) LIKE '%".$this->db->escape_like_str($title)."%' ";
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
					LEFT JOIN TR_MENU PARENT ON CHILD.MENU_PARENT = PARENT.MENU_ID WHERE CHILD.MENU_DELETE_STATUS = '0' " . $whereParamName . " 
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
					LEFT JOIN TR_MENU PARENT ON CHILD.MENU_PARENT = PARENT.MENU_ID WHERE CHILD.MENU_DELETE_STATUS = '0' " . $whereParamName;
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
					LEFT JOIN TR_MENU PARENT ON CHILD.MENU_PARENT = PARENT.MENU_ID WHERE CHILD.MENU_DELETE_STATUS = '0' ";
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
			'MENU_PARENT' => $this->input->post('MENU_PARENT'),
			'MENU_DELETE_STATUS' => 0
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
		if(isset($filter['ROLE_NAME'])){
			if($filter['ROLE_NAME']){
				$whereParams .= " AND LOWER(TROLE.ROLE_NAME) LIKE '%".$this->db->escape_like_str(strtolower($filter['ROLE_NAME']))."%'";
			}
			if($filter['MENU_TITLE']){
				$whereParams .= " AND LOWER(TMENU.MENU_TITLE) LIKE '%".$this->db->escape_like_str(strtolower($filter['MENU_TITLE']))."%'";
			}
		}

		else if(isset($filter['ROLE_ID']) && isset($filter['ROLE_MENU_ID'])){
			$whereParams .= " AND TACCESS.ROLE_ID = ".$this->db->escape($filter['ROLE_ID'])." AND TACCESS.ROLE_MENU_ID = ".$this->db->escape($filter['ROLE_MENU_ID'])." ";
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
							TACCESS.ROLE_ID = TROLE.ROLE_ID AND TROLE.ROLE_DELETE_STATUS = 0 
					WHERE TACCESS.ROLE_ACCESS_DELETE_STATUS = 0 AND TMENU.MENU_DELETE_STATUS = '0'" . $whereParams ."
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
							TACCESS.ROLE_ID = TROLE.ROLE_ID AND TROLE.ROLE_DELETE_STATUS = 0 
						WHERE TACCESS.ROLE_ACCESS_DELETE_STATUS = 0 AND TMENU.MENU_DELETE_STATUS = '0'" . $whereParams;

		$dataTotal = $this->db->query($sqlTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );

	}

	public function checkMenu($filter){
		$sql = "SELECT ROLE_ACCESS_ID FROM TR_ROLE_ACCESS WHERE ROLE_ID = ? AND ROLE_MENU_ID = ? AND BRANCH_ID = ? AND ROLE_ACCESS_DELETE_STATUS = 0 ";
		$data = $this->db->query($sql,$filter)->result();

		return $data;
	}

	public function getAllRole($filter){
		$params = array(
	        'ROLE_NAME' => $filter['ROLE_NAME']
	    );
	    $whereParam = "";
		if($filter['ROLE_NAME']){

			$whereParam = " AND ROLE_NAME = ?";
		}
		$sql = "SELECT ROLE_ID, ROLE_NAME FROM TR_ROLE WHERE ROLE_DELETE_STATUS = 0 ". $whereParam;

		$data = $this->db->query($sql,$params)->result();
    	return $data;
	}

	public function addRoleAccess($dataInsert,$difference){
		$message =  'SUKSES';
		$this->db->trans_start();
		

		$this->db->insert_batch('TR_ROLE_ACCESS', $dataInsert);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			$message = 'ERROR';
		}
		elseif($difference){
			$message = count($dataInsert).' data successfully inserted and '. $difference . ' data failed to insert, because those/that data already exists';
		}

		return $message;
	}

	public function getMenuById($id = NULL){
		$this->db->select('*');
		$this->db->from('TR_MENU');
		$this->db->where('MENU_ID', $id);
		$this->db->where('MENU_DELETE_STATUS', 0);
		$query = $this->db->get();

		$data =  $query->result_array();
		return array (
			'data' => $data,
			'total' => count($data)
		);	
	}

	public function updateMenu($id = NULL){
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

		$this->db->where('MENU_ID', $id);
		$this->db->update('TR_MENU', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	public function getRoleaccessById($id = NULL){
		$this->db->select('*');
		$this->db->from('TR_ROLE_ACCESS');
		$this->db->where('ROLE_ACCESS_ID', $id);
		$this->db->where('ROLE_ACCESS_DELETE_STATUS', 0);

		$query = $this->db->get();

		$data =  $query->result_array();
		return array (
			'data' => $data,
			'total' => count($data)
		);
	}

	public function updateRoleaccess($id = NULL){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'ROLE_ID' => $this->input->post('ROLE_ID'),
			'BRANCH_ID' => $this->input->post('BRANCH_ID')
		);

		$this->db->where('ROLE_ACCESS_ID', $id);
		$this->db->update('TR_ROLE_ACCESS', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	public function deleteRoleaccess($id = NULL){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'ROLE_ACCESS_DELETE_STATUS' => 1
		);

		$this->db->where('ROLE_ACCESS_ID', $id);
		$this->db->update('TR_ROLE_ACCESS', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;

	}

	public function deleteMenu($id = NULL){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'MENU_DELETE_STATUS' => 1
		);

		$this->db->where('MENU_ID', $id);
		$this->db->update('TR_MENU', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	public function getAllDataMenuPDA($filter){
		$paramsTotal = array();
		$params['END'] 		= 1;
		$params['START'] 	= 0;

		if(isset($_REQUEST['start']) &&  isset($_REQUEST['limit'])){
			$params['END'] 		= $_REQUEST['start'] + $_REQUEST['limit'];
			$params['START'] 	= $_REQUEST['start'];
		}
		$whereParams = "";
		if($filter['MENU_TEXT']){
			$params['MENU_PDA_TEXT'] = '%' . $filter['MENU_TEXT'] . '%';
			$paramsTotal['MENU_PDA_TEXT'] = '%' . $filter['MENU_TEXT'] . '%';
			$whereParams .= ' AND LOWER(MENU_PDA_TEXT) LIKE ?';
		}
		$sql = "SELECT * FROM 
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						SELECT
							*
						FROM
							TR_MENU_PDA
						WHERE MENU_PDA_DELETE_STATUS = 0 
					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1" . $whereParams;

		$data = $this->db->query($sql,$params)->result();

		$sqlTotal = "SELECT
							*
						FROM
							TR_MENU_PDA
						WHERE MENU_PDA_DELETE_STATUS = 0 " . $whereParams;

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
	}

	public function addMenuPDA(){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'MENU_PDA_TEXT' => $this->input->post('MENU_PDA_TEXT'),
			'MENU_PDA_FUNCTION' => $this->input->post('MENU_PDA_FUNCTION'),
			'MENU_PDA_ICON' => $this->input->post('MENU_PDA_ICON'),
			'MENU_PDA_DELETE_STATUS' => 0
		);

		$this->db->insert('TR_MENU_PDA',$arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	public function getMenuPdaById($id = NULL){
		$this->db->select('*');
		$this->db->from('TR_MENU_PDA');
		$this->db->where('MENU_PDA_ID', $id);
		$this->db->where('MENU_PDA_DELETE_STATUS', 0);

		$query = $this->db->get();

		$data =  $query->result_array();
		return array (
			'data' => $data,
			'total' => count($data)
		);
	}

	public function updateMenuPDA($id = NULL){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'MENU_PDA_TEXT' => $this->input->post('MENU_PDA_TEXT'),
			'MENU_PDA_FUNCTION' => $this->input->post('MENU_PDA_FUNCTION'),
			'MENU_PDA_ICON' => $this->input->post('MENU_PDA_ICON'),
		);

		$this->db->where('MENU_PDA_ID', $id);
		$this->db->update('TR_MENU_PDA', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	public function deleteMenuPDA($id = NULL){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'MENU_PDA_DELETE_STATUS' => 1
		);

		$this->db->where('MENU_PDA_ID', $id);
		$this->db->update('TR_MENU_PDA', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	public function getAllAccessPDAMenu(){
		$params['END'] 		= 1;
		$params['START'] 	= 0;
		if(isset($_REQUEST['start']) &&  isset($_REQUEST['limit'])){
			$params['END'] 		= $_REQUEST['start'] + $_REQUEST['limit'];
			$params['START'] 	= $_REQUEST['start'];
		}

		$whereParams = '';
		if(isset($filter['ROLE_NAME'])){
			if($filter['ROLE_NAME']){
				$whereParams .= " AND LOWER(TROLE.ROLE_NAME) LIKE '%".$this->db->escape_like_str(strtolower($filter['ROLE_NAME']))."%'";
			}
			if($filter['MENU_TEXT']){
				$whereParams .= " AND LOWER(TMENU.MENU_TEXT) LIKE '%".$this->db->escape_like_str(strtolower($filter['MENU_TEXT']))."%'";
			}
		}

		else if(isset($filter['ROLE_ID']) && isset($filter['ROLE_MENU_ID'])){
			$whereParams .= " AND TACCESS.ROLE_ID = ".$this->db->escape($filter['ROLE_ID'])." AND TACCESS.ROLE_MENU_ID = ".$this->db->escape($filter['ROLE_MENU_ID'])." ";
		}
		$sql = "SELECT * FROM 
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						SELECT
							TACCESS.ROLE_ACCESS_PDA_ID,
							TROLE.ROLE_NAME,
							TMENU.MENU_PDA_TEXT,
							TBRANCH.BRANCH_NAME
						FROM
							TR_ROLE_ACCESS_PDA TACCESS
						LEFT JOIN
							TR_MENU_PDA TMENU
						ON
							TACCESS.ROLE_MENU_PDA_ID = TMENU.MENU_PDA_ID
						LEFT JOIN
							TR_BRANCH TBRANCH
						ON
							TACCESS.BRANCH_ID = TBRANCH.BRANCH_ID
						LEFT JOIN
							TR_ROLE TROLE
						ON
							TACCESS.ROLE_ID = TROLE.ROLE_ID AND TROLE.ROLE_DELETE_STATUS = 0 
					WHERE TACCESS.ROLE_ACCESS_PDA_DELETE_STATUS = 0 AND TMENU.MENU_PDA_DELETE_STATUS = '0'" . $whereParams ."
					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1";

		$data = $this->db->query($sql,$params)->result();

		$sqlTotal = "SELECT
							TACCESS.ROLE_ACCESS_PDA_ID,
							TROLE.ROLE_NAME,
							TMENU.MENU_PDA_TEXT,
							TBRANCH.BRANCH_NAME
						FROM
							TR_ROLE_ACCESS_PDA TACCESS
						LEFT JOIN
							TR_MENU_PDA TMENU
						ON
							TACCESS.ROLE_MENU_PDA_ID = TMENU.MENU_PDA_ID
						LEFT JOIN
							TR_BRANCH TBRANCH
						ON
							TACCESS.BRANCH_ID = TBRANCH.BRANCH_ID
						LEFT JOIN
							TR_ROLE TROLE
						ON
							TACCESS.ROLE_ID = TROLE.ROLE_ID AND TROLE.ROLE_DELETE_STATUS = 0
					WHERE TACCESS.ROLE_ACCESS_PDA_DELETE_STATUS = 0 AND TMENU.MENU_PDA_DELETE_STATUS = '0'" . $whereParams;

		$dataTotal = $this->db->query($sqlTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
	}

	public function getAllDataMenuPDAList(){
		$sqlTotal = "SELECT
						CHILD.MENU_PDA_ID,
						CHILD.MENU_PDA_TEXT,
						CHILD.MENU_PDA_FUNCTION,
						CHILD.MENU_PDA_ICON
					FROM TR_MENU_PDA CHILD
					WHERE CHILD.MENU_PDA_DELETE_STATUS = '0' ORDER BY MENU_PDA_ID ASC";
		$dataTotal = $this->db->query($sqlTotal)->result();
		return array (
	      'data' => $dataTotal,
	      'total' => count($dataTotal)
	    );
	}

	public function getAllMenuPDA(){
		$sqlTotal = "SELECT
						MENU_PDA_ID,
						MENU_PDA_TEXT,
						MENU_PDA_FUNCTION,
						MENU_PDA_ICON
					FROM TR_MENU_PDA
					WHERE MENU_PDA_DELETE_STATUS = '0' ORDER BY MENU_PDA_ID ASC";
		
		$data = $this->db->query($sqlTotal)->result();
    	return $data;
	}

	public function checkMenuPDA($filter){
		$sql = "SELECT ROLE_ACCESS_PDA_ID FROM TR_ROLE_ACCESS_PDA WHERE ROLE_ID = ? AND ROLE_MENU_PDA_ID = ? AND BRANCH_ID = ? AND ROLE_ACCESS_PDA_DELETE_STATUS = 0 ";
		$data = $this->db->query($sql,$filter)->result();

		return $data;
	}

	public function addRoleAccessPDA($dataInsert,$difference){
		$message =  'SUKSES';
		$this->db->trans_start();

		$this->db->insert_batch('TR_ROLE_ACCESS_PDA', $dataInsert);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			$message = 'ERROR';
		}
		else if($difference){
			$message = count($dataInsert).' data successfully inserted and '. $difference . ' data failed to insert, because those/that data already exists';
		}

		return $message;		
	}

	public function getRoleaccessPDAById($id = NULL){
		$this->db->select('*');
		$this->db->from('TR_ROLE_ACCESS_PDA');
		$this->db->where('ROLE_ACCESS_PDA_ID', $id);
		$this->db->where('ROLE_ACCESS_PDA_DELETE_STATUS', 0);

		$query = $this->db->get();

		$data =  $query->result_array();
		return array (
			'data' => $data,
			'total' => count($data)
		);
	}

	public function updateRoleaccessPDA($id = NULL){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'ROLE_ID' => $this->input->post('ROLE_ID'),
			'BRANCH_ID' => $this->input->post('BRANCH_ID')
		);

		$this->db->where('ROLE_ACCESS_PDA_ID', $id);
		$this->db->update('TR_ROLE_ACCESS_PDA', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	public function deleteRoleaccessPDA($id = NULL){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'ROLE_ACCESS_PDA_DELETE_STATUS' => 1
		);

		$this->db->where('ROLE_ACCESS_PDA_ID', $id);
		$this->db->update('TR_ROLE_ACCESS_PDA', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;

	}

	public function getAllDataMenuPDAListUser(){
		$BRANCH_ID = $this->session->userdata('USER_BRANCH');
		$USER_ROLE = $this->session->userdata('USER_ROLE');
		$sqlTotal = "SELECT
						CHILD.MENU_PDA_ID,
						CHILD.MENU_PDA_TEXT,
						CHILD.MENU_PDA_FUNCTION,
						CHILD.MENU_PDA_ICON
					FROM TR_MENU_PDA CHILD
					JOIN TR_ROLE_ACCESS_PDA TROLE
					ON 
						TROLE.ROLE_MENU_PDA_ID = CHILD.MENU_PDA_ID 
					AND TROLE.ROLE_ACCESS_PDA_DELETE_STATUS = 0
					AND TROLE.ROLE_ID = ".$USER_ROLE."
					WHERE CHILD.MENU_PDA_DELETE_STATUS = '0' AND TROLE.BRANCH_ID = ".$BRANCH_ID." ORDER BY MENU_PDA_ID ASC";
		$dataTotal = $this->db->query($sqlTotal)->result();
		return array (
	      'data' => $dataTotal,
	      'total' => count($dataTotal)
	    );
	}

	public function getMenu($parent_id = false){
		$params = array();
	    $whereParamId = " AND MENU_PARENT IS NULL";
		if($parent_id){
			$params[] = $parent_id;
			$whereParamId = " AND MENU_PARENT = ?";
		}

		$sql =  "SELECT * FROM TR_MENU WHERE MENU_DELETE_STATUS = 0 ". $whereParamId;

		$data = $this->db->query($sql,$params)->result();
    	return $data;
	}
	public function getAllRoleList($filter){
		$paramsTotal = array();
		$params['END'] 		= 1;
		$params['START'] 	= 0;

		if(isset($_REQUEST['start']) &&  isset($_REQUEST['limit'])){
			$params['END'] 		= $_REQUEST['start'] + $_REQUEST['limit'];
			$params['START'] 	= $_REQUEST['start'];
		}
		$whereParams = "";
		if($filter['ROLE_NAME']){
			$params['ROLE_NAME'] = '%' . $filter['ROLE_NAME'] . '%';
			$paramsTotal['ROLE_NAME'] = '%' . $filter['ROLE_NAME'] . '%';
			$whereParams .= ' AND LOWER(ROLE_NAME) LIKE ?';
		}
		$sql = "SELECT * FROM 
				(
					SELECT TABLE_1.*, rownum AS rnum FROM (
						SELECT
							*
						FROM
							TR_ROLE
						WHERE ROLE_DELETE_STATUS = 0 
					) TABLE_1
					WHERE ROWNUM <= ?
				)
				WHERE  rnum >= ? + 1" . $whereParams;

		$data = $this->db->query($sql,$params)->result();

		$sqlTotal = "SELECT
							*
						FROM
							TR_ROLE
						WHERE ROLE_DELETE_STATUS = 0 " . $whereParams;

		$dataTotal = $this->db->query($sqlTotal,$paramsTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
	}

	public function checkRole($filter){
		$sql = "SELECT * FROM TR_ROLE WHERE LOWER(ROLE_NAME) = ? AND ROLE_DELETE_STATUS = 0 ";
		$data = $this->db->query($sql,$filter)->result();
		//var_dump($data);
		return $data;
	}

	public function addRole(){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'ROLE_NAME' => $this->input->post('ROLE_NAME'),
			'ROLE_DELETE_STATUS' => 0
		);

		$this->db->insert('TR_ROLE',$arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			$message = 'ERROR'; 
		}

		return $message;		
	}

	public function getRoleById($id = NULL){
		$this->db->select('*');
		$this->db->from('TR_ROLE');
		$this->db->where('ROLE_ID', $id);
		$this->db->where('ROLE_DELETE_STATUS', 0);

		$query = $this->db->get();

		$data =  $query->result_array();
		return array (
			'data' => $data,
			'total' => count($data)
		);
	}

	public function updateRole($id = NULL){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'ROLE_NAME' => $this->input->post('ROLE_NAME')
		);

		$this->db->where('ROLE_ID', $id);
		$this->db->update('TR_ROLE', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	public function deleteRole($id = NULL){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'ROLE_DELETE_STATUS' => 1
		);

		$this->db->where('ROLE_ID', $id);
		$this->db->update('TR_ROLE', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;

	}

	public function getAllDataApi($filters){
		
		$params['END'] 		= $_REQUEST['start'] + $_REQUEST['limit'];
		$params['START'] 	= $_REQUEST['start'];

	    $whereParamName = "";

		foreach ($filters as $filterKey => $fileterValue) {
			if($fileterValue){
				$whereParamName .= " AND LOWER(".$filterKey.") LIKE '%".$this->db->escape_like_str($fileterValue)."%' ";
			}
			
		}
		$sql = "
			SELECT * FROM 
				(
				SELECT TABLE_1.*, rownum AS rnum FROM (
					SELECT
						API_CLIENT_NAME,
						API_CLIENT_BRANCH,
						API_CLIENT_STATUS,
						API_CLIENT_ID,
						CASE API_CLIENT_STATUS WHEN 1 THEN 'AKTIF' ELSE 'TIDAK AKTIF' END TEXT_STATUS,
						BRANCH_NAME
					FROM TR_API_CLIENT
					LEFT JOIN TR_BRANCH ON API_CLIENT_BRANCH = BRANCH_ID WHERE 1=1 " . $whereParamName . " ORDER BY BRANCH_NAME,API_CLIENT_NAME
				) TABLE_1
				WHERE ROWNUM <= ?
			)
			WHERE  rnum >= ? + 1";
		
		$data = $this->db->query($sql,$params)->result();

		$sqlTotal = "SELECT
						API_CLIENT_NAME,
						API_CLIENT_BRANCH,
						API_CLIENT_STATUS,
						API_CLIENT_ID,
						CASE API_CLIENT_STATUS WHEN 1 THEN 'AKTIF' ELSE 'TIDAK AKTIF' END,
						BRANCH_NAME
					FROM TR_API_CLIENT 
					LEFT JOIN TR_BRANCH ON API_CLIENT_BRANCH = BRANCH_ID WHERE 1=1 " . $whereParamName;
		$dataTotal = $this->db->query($sqlTotal)->result();
		return array (
	      'data' => $data,
	      'total' => count($dataTotal)
	    );
	}

	public function checkApiClient($filters){

		$sql = "SELECT API_CLIENT_ID FROM TR_API_CLIENT WHERE API_CLIENT_NAME = ? AND API_CLIENT_BRANCH = ?";
		$data = $this->db->query($sql,$filters)->result();

		return $data;

	}

	public function addApiClient(){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'API_CLIENT_NAME' => trim($this->input->post('API_CLIENT_NAME')),
			'API_CLIENT_BRANCH' => $this->input->post('API_CLIENT_BRANCH'),
			'API_CLIENT_STATUS' => $this->input->post('API_CLIENT_STATUS')
		);

		$this->db->insert('TR_API_CLIENT',$arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	public function getApiClientById($idHdr){
		$this->db->select('*');
		$this->db->from('TR_API_CLIENT');
		$this->db->where('API_CLIENT_ID', $idHdr);
		$query = $this->db->get();

		$data =  $query->result_array();
		return array (
			'data' => $data,
			'total' => count($data)
		);	
	}

	public function updateApiClient($id = NULL){
		$message =  'SUKSES';
		$this->db->trans_start();

		$arrData = array(
			'API_CLIENT_NAME' => trim($this->input->post('API_CLIENT_NAME')),
			'API_CLIENT_STATUS' => $this->input->post('API_CLIENT_STATUS'),
			'API_CLIENT_BRANCH' => $this->input->post('API_CLIENT_BRANCH')
		);

		$this->db->where('API_CLIENT_ID', $id);
		$this->db->update('TR_API_CLIENT', $arrData);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
				$message = 'ERROR';
		}

		return $message;
	}

	public function deleteApiClient($id = false){
		$this->db->where('API_CLIENT_ID', $id);
		$this->db->delete('TR_API_CLIENT');
	}
}
