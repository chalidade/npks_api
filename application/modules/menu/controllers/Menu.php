<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;
class Menu extends CI_Controller {
	private $secret = 'this is key secret';
  	public function __construct(){
		parent::__construct();
     	$this->load->model('m_menu');

	  //   if(!$this->session->userdata('isLogin')){
		// 	echo '<h2>you are not allowed access to this URL<h2>';
		// 	die();
		// }


	}

	public function all_main_menu(){

		$dataAllMenu = $this->childMenu($this->session->userdata('isId'),false);

		header('Content-Type: text/javascript');

		die(json_encode($dataAllMenu));

	}

	private function childMenu($user_id,$parent_id){

		$dataAllMenu = $this->m_menu->getAllUserMenu($user_id,$parent_id);

		$counter = 0;

		if(count($dataAllMenu) > 0){

			foreach ($dataAllMenu as $dataMenu) {

				$dataAllMenuChild = $this->m_menu->getAllUserMenu($user_id,$dataMenu->MENU_ID);

				if(count($dataAllMenuChild) > 0 ){

					$dataAllMenu[$counter]->children = $this->childMenu($user_id,$dataMenu->MENU_ID);
				}

				$counter++;
			}
		}

		return $dataAllMenu;
	}
	public function get_all_menu(){
		$filter = isset($_GET['MENU_TITLE']) && $_GET['MENU_TITLE']? $_GET['MENU_TITLE'] : 0;

		$dataAllMenu = $this->m_menu->getAllDataMenu($filter);
		header('Content-Type: text/javascript');

		die(json_encode($dataAllMenu));
	}

	public function get_all_menu_list(){

		$dataAllMenu = $this->m_menu->getAllDataMenuList();
		header('Content-Type: text/javascript');

		die(json_encode($dataAllMenu));
	}
	public function add_menu(){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_menu->addMenu();

		        $return = array('success' => true, 'message' => $data);
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function check_token()
	{
	    $jwt = $this->input->get_request_header('auth');
	    try {
	      	$decoded = JWT::decode($jwt, $this->secret, array('HS256'));
	      	return $decoded->id;
	    } catch(\Exception $e) {
	    	return false;
	    }
	}

	public function get_all_role_access(){
		$filter['ROLE_NAME'] = isset($_GET['ROLE_NAME']) && $_GET['ROLE_NAME']? $_GET['ROLE_NAME'] : 0;
		$filter['MENU_TITLE'] = isset($_GET['MENU_TITLE']) && $_GET['MENU_TITLE']? $_GET['MENU_TITLE'] : 0;

		$dataAllAccess = $this->m_menu->getAllAccessMenu($filter);

		header('Content-Type: text/javascript');

		die(json_encode($dataAllAccess));
	}

	public function get_all_role(){
		$filter['ROLE_NAME'] = isset($_GET['ROLE_NAME']) && $_GET['ROLE_NAME']? $_GET['ROLE_NAME'] : 0;

		$dataAllRole = $this->m_menu->getAllRole($filter);

		header('Content-Type: text/javascript');

		die(json_encode($dataAllRole));
	}

	public function add_role_access(){

		if($id = $this->check_token()){

	    	if($id == $this->session->userdata('isId')){

	    		$allRoleAccessId = json_decode($this->input->post('roleaccess'));
	    		$dataInsert = array();
	    		$counter	= 0;

	    		foreach ($allRoleAccessId as $value) {

	    			$filter['ROLE_ID'] = $this->input->post('ROLE_ID');
			        $filter['ROLE_MENU_ID'] = $value;
			        $filter['BRANCH_ID'] = $this->input->post('BRANCH_ID');

			        $dataReturn = $this->m_menu->checkMenu($filter);

	    			if(count($dataReturn) == 0){
			        	$dataInsert[$counter]['ROLE_ID'] 		= $this->input->post('ROLE_ID');
				        $dataInsert[$counter]['ROLE_MENU_ID'] 	= (int)$value;
				        $dataInsert[$counter]['BRANCH_ID'] 		= $this->input->post('BRANCH_ID');
			        	$counter++;
		        	}
	    		}
	    		if(count($dataInsert) > 0){
	    			$difference = count($allRoleAccessId) - count($dataInsert);

	    			$data = $this->m_menu->addRoleAccess($dataInsert,$difference);
	        		$return = array('success' => true, 'message' => $data);
	    		}
	        	else{
	        		$return = array(
			          'success' => false,
			          'message' => 'These/This data already exists'
			        );
	        	}

	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_menu_by_id($idHdr = NULL){
		$data = $this->m_menu->getMenuById($idHdr);
	    header('Content-Type: text/javascript');
	    echo json_encode($data);
	}

	public function update_menu($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_menu->updateMenu($idHdr);

		        $return = array('success' => true, 'message' => $data);
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function delete_menu($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_menu->deleteMenu($idHdr);

		        $return = array('success' => true, 'message' => $data);
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_roleaccess_by_id($idHdr = NULL){
		$data = $this->m_menu->getRoleaccessById($idHdr);
	    header('Content-Type: text/javascript');
	    echo json_encode($data);
	}

	public function update_roleaccess($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
	    		$filter['ROLE_ID'] = $this->input->post('ROLE_ID');
		        $filter['ROLE_MENU_ID'] = $this->input->post('ROLE_MENU_ID');
		        $filter['BRANCH_ID'] = $this->input->post('BRANCH_ID');

		        $dataReturn = $this->m_menu->checkMenu($filter);

		        if(count($dataReturn) > 0 and $dataReturn[0]->ROLE_ACCESS_ID != $idHdr){

		        	$return = array(
			          'success' => false,
			          'message' => 'This data already exists'
			        );

		        }
		        else{

			        $data = $this->m_menu->updateRoleaccess($idHdr);

			        $return = array('success' => true, 'message' => $data);

		        }
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function delete_roleaccess($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_menu->deleteRoleaccess($idHdr);

		        $return = array('success' => true, 'message' => $data);
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_all_menu_pda(){
		$filter['MENU_TEXT'] = isset($_GET['MENU_TEXT']) && $_GET['MENU_TEXT']? $_GET['MENU_TEXT'] : 0;
		//die(var_dump($filter));
		$dataAllMenuPDA = $this->m_menu->getAllDataMenuPDA($filter);
		header('Content-Type: text/javascript');

		die(json_encode($dataAllMenuPDA));
	}

	public function add_menu_pda(){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_menu->addMenuPDA();

		        $return = array('success' => true, 'message' => $data);
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_menu_pda_by_id($idHdr = NULL){
		$data = $this->m_menu->getMenuPdaById($idHdr);
	    header('Content-Type: text/javascript');
	    echo json_encode($data);
	}

	public function update_menu_pda($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_menu->updateMenuPDA($idHdr);

		        $return = array('success' => true, 'message' => $data);
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function delete_menu_pda($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_menu->deleteMenuPDA($idHdr);

		        $return = array('success' => true, 'message' => $data);
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_all_role_access_pda(){
		$filter['ROLE_NAME'] = isset($_GET['ROLE_NAME']) && $_GET['ROLE_NAME']? $_GET['ROLE_NAME'] : 0;
		$filter['MENU_TEXT'] = isset($_GET['MENU_TEXT']) && $_GET['MENU_TEXT']? $_GET['MENU_TEXT'] : 0;

		$dataAllAccessPDA = $this->m_menu->getAllAccessPDAMenu($filter);

		header('Content-Type: text/javascript');

		die(json_encode($dataAllAccessPDA));
	}

	public function get_all_menu_pda_list(){

		$dataAllMenuPDA = $this->m_menu->getAllDataMenuPDAList();
		header('Content-Type: text/javascript');

		die(json_encode($dataAllMenuPDA));
	}

	public function add_role_access_pda(){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){

		        $allRoleAccessId = json_decode($this->input->post('roleaccess'));
	    		$dataInsert = array();
	    		$counter	= 0;

	    		foreach ($allRoleAccessId as $value) {

	    			$filter['ROLE_ID'] = $this->input->post('ROLE_ID');
			        $filter['ROLE_MENU_PDA_ID'] = $value;
			        $filter['BRANCH_ID'] = $this->input->post('BRANCH_ID');

			        $dataReturn = $this->m_menu->checkMenuPDA($filter);

	    			if(count($dataReturn) == 0){
			        	$dataInsert[$counter]['ROLE_ID'] 			= $this->input->post('ROLE_ID');
				        $dataInsert[$counter]['ROLE_MENU_PDA_ID'] 	= (int)$value;
				        $dataInsert[$counter]['BRANCH_ID'] 			= $this->input->post('BRANCH_ID');
			        	$counter++;
		        	}
	    		}
	    		if(count($dataInsert) > 0){
	    			$difference = count($allRoleAccessId) - count($dataInsert);

	    			$data = $this->m_menu->addRoleAccessPDA($dataInsert,$difference);
	        		$return = array('success' => true, 'message' => $data);
	    		}
	        	else{
	        		$return = array(
			          'success' => false,
			          'message' => 'These/This data already exists'
			        );
	        	}

	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_roleaccess_pda_by_id($idHdr = NULL){
		$data = $this->m_menu->getRoleaccessPDAById($idHdr);
	    header('Content-Type: text/javascript');
	    echo json_encode($data);
	}

	public function update_roleaccess_pda($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){

	    		$filter['ROLE_ID'] = $this->input->post('ROLE_ID');
		        $filter['ROLE_MENU_PDA_ID'] = $this->input->post('ROLE_MENU_PDA_ID');
		        $filter['BRANCH_ID'] = $this->input->post('BRANCH_ID');

		        $dataReturn = $this->m_menu->checkMenuPDA($filter);

		        if(count($dataReturn) > 0 and $dataReturn[0]->ROLE_ACCESS_PDA_ID != $idHdr){
		        	$return = array(
			          'success' => false,
			          'message' => 'This data already exists'
			        );
		        }
		        else{
		        	$data = $this->m_menu->updateRoleaccessPDA($idHdr);

		        	$return = array('success' => true, 'message' => $data);
		        }

	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function delete_roleaccess_pda($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_menu->deleteRoleaccessPDA($idHdr);

		        $return = array('success' => true, 'message' => $data);
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_all_menu_pda_list_user(){
		$dataAllMenuPDA = $this->m_menu->getAllDataMenuPDAListUser();
		header('Content-Type: text/javascript');

		die(json_encode($dataAllMenuPDA));
	}

	public function get_form_roleaccess(){

		$data['dataAllHtmlMenu'] = $this->get_html_menu(false,1);
		$data['form_id'] 	= $this->input->get('form_id');
		$data['win_id'] 	= $this->input->get('win_id');
		$data['grid_id'] 	= $this->input->get('grid_id');

		$this->load->view('form_roleaccess', $data);

	}

	private function get_html_menu($parent_id,$first){
		$dataAllMenu = $this->m_menu->getMenu($parent_id);

		$counter = 0;

		$html = '';
		$style = '';
		if($first){
			$style = 'style="padding:0;margin:20px 0 0"';
		}
		if(count($dataAllMenu) > 0){

			$html .= '<ul id="list_menu" '.$style.'>';

			foreach ($dataAllMenu as $dataMenu) {

				$html .= '<li><input id="roleaccess" type="checkbox" data-value="'.$dataMenu->MENU_ID.'" name="roleaccess[]"/>' .$dataMenu->MENU_TEXT;

				$dataAllMenuChild = $this->m_menu->getMenu($dataMenu->MENU_ID);

				if(count($dataAllMenuChild) > 0 ){

					$html .= $this->get_html_menu($dataMenu->MENU_ID,false);
				}

				$html .= '</li>';

				$counter++;
			}
			$html .= '</ul>';
		}

		return $html;
	}

	public function get_form_roleaccess_pda(){

		$data['dataAllMenu'] = $this->m_menu->getAllMenuPDA();
		$data['form_id'] 	= $this->input->get('form_id');
		$data['win_id'] 	= $this->input->get('win_id');
		$data['grid_id'] 	= $this->input->get('grid_id');

		$this->load->view('form_roleaccess_pda', $data);

	}

	public function get_all_role_list(){
		$filter = isset($_GET['ROLE_NAME']) && $_GET['ROLE_NAME']? $_GET['ROLE_NAME'] : 0;
		$dataAllMenuPDA = $this->m_menu->getAllRoleList($filter);
		header('Content-Type: text/javascript');

		die(json_encode($dataAllMenuPDA));
	}

	public function add_role(){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){


		        $filter['ROLE_NAME'] = strtolower(trim($this->input->post('ROLE_NAME')));
		        $dataReturn = $this->m_menu->checkRole($filter);
		        if(count($dataReturn) > 0){
		        	$return = array(
			          'success' => false,
			          'message' => 'This data already exists'
			        );
		        }
		        else{
		        	$data = $this->m_menu->addRole();
		        	$return = array('success' => true, 'message' => $data);
		        }

	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_role_by_id($idHdr = NULL){
		$data = $this->m_menu->getRoleById($idHdr);
	    header('Content-Type: text/javascript');
	    echo json_encode($data);
	}

	public function update_role($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){

	    		$filter['ROLE_NAME'] = strtolower(trim($this->input->post('ROLE_NAME')));
		        $dataReturn = $this->m_menu->checkRole($filter);

		        if(count($dataReturn) > 0 && $dataReturn[0]->ROLE_ID != $idHdr){
		        	$return = array(
			          'success' => false,
			          'message' => 'This data already exists'
			        );
		        }
		        else{

			        $data = $this->m_menu->updateRole($idHdr);

			        $return = array('success' => true, 'message' => $data);
		        }
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function delete_role($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_menu->deleteRole($idHdr);

		        $return = array('success' => true, 'message' => $data);
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_all_api(){
		$filter['API_CLIENT_NAME'] = isset($_GET['API_CLIENT_NAME']) && $_GET['API_CLIENT_NAME']? $_GET['API_CLIENT_NAME'] : 0;
		$filter['BRANCH_NAME'] = isset($_GET['BRANCH_NAME']) && $_GET['BRANCH_NAME']? $_GET['BRANCH_NAME'] : 0;

		$dataAllMenu = $this->m_menu->getAllDataApi($filter);
		header('Content-Type: text/javascript');

		die(json_encode($dataAllMenu));
	}

	public function add_api_client(){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){


		        $filters['API_CLIENT_NAME'] = strtolower(trim($this->input->post('API_CLIENT_NAME')));
		        $filters['API_CLIENT_BRANCH'] = strtolower(trim($this->input->post('API_CLIENT_BRANCH')));
		        $dataReturn = $this->m_menu->checkApiClient($filters);
		        if(count($dataReturn) > 0){
		        	$return = array(
			          'success' => false,
			          'message' => 'This data already exists'
			        );
		        }
		        else{
		        	$data = $this->m_menu->addApiClient();
		        	$return = array('success' => true, 'message' => $data);
		        }

	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function get_api_client_by_id($idHdr = NULL){
		$data = $this->m_menu->getApiClientById($idHdr);
	    header('Content-Type: text/javascript');
	    echo json_encode($data);
	}

	public function update_api_client($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
	    		$data = $this->m_menu->getApiClientById($idHdr);

	    		$filters['API_CLIENT_NAME'] = strtolower(trim($this->input->post('API_CLIENT_NAME')));
		        $filters['API_CLIENT_BRANCH'] = strtolower(trim($this->input->post('API_CLIENT_BRANCH')));

		        //$dataReturn = $this->m_menu->getApiClientById($idHdr);
		        $dataReturnCheck = $this->m_menu->checkApiClient($filters);
		        if(count($dataReturnCheck) > 0 AND $dataReturnCheck[0]->API_CLIENT_ID != $idHdr){
		        	$return = array(
			          'success' => false,
			          'message' => 'This data already exists'
			        );
		        }
		        else{
		        	$data = $this->m_menu->updateApiClient($idHdr);
		        	$return = array('success' => true, 'message' => $data);
		        }
	      	}
	      	else{
		        $return = array(
		          'success' => false,
		          'message' => 'error authentication'
		        );
	      	}
	    }
	    else{
		      $return = array(
		        'success' => false,
		        'message' => 'error authentication'
		      );
	    }
	    echo json_encode($return);
	}

	public function delete_api_client($idHdr = NULL){
		$this->m_menu->deleteApiClient($idHdr);
		$return = array(
		        'success' => true,
		        'message' => 'SUKSES'
		      );
	    echo json_encode($return);
	}
}
