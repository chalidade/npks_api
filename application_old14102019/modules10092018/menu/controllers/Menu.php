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

	    if(!$this->session->userdata('isLogin')){
			echo '<h2>you are not allowed access to this URL<h2>';
			die();
		}
		
		
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
		        
		        $filter['ROLE_ID'] = $this->input->post('ROLE_ID');
		        $filter['ROLE_MENU_ID'] = $this->input->post('ROLE_MENU_ID');
		        $filter['BRANCH_ID'] = $this->input->post('BRANCH_ID');
		        
		        $dataReturn = $this->m_menu->checkMenu($filter);
		        if(count($dataReturn) > 0){
		        	$return = array(
			          'success' => false,
			          'message' => 'This data already exists'
			        );
		        }
		        else{
		        	$data = $this->m_menu->addRoleAccess();
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
		        
		        $filter['ROLE_ID'] = $this->input->post('ROLE_ID');
		        $filter['ROLE_MENU_PDA_ID'] = $this->input->post('ROLE_MENU_PDA_ID');
		        $filter['BRANCH_ID'] = $this->input->post('BRANCH_ID');
		        
		        $dataReturn = $this->m_menu->checkMenuPDA($filter);
		        if(count($dataReturn) > 0){
		        	$return = array(
			          'success' => false,
			          'message' => 'This data already exists'
			        );
		        }
		        else{
		        	$data = $this->m_menu->addRoleAccessPDA();
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
	
}