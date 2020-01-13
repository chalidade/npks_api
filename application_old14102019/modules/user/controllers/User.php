<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;
class User extends CI_Controller {
	private $secret = 'this is key secret';
  	public function __construct(){
		parent::__construct();
     	$this->load->model('m_user');

	    if(!$this->session->userdata('isLogin')){
			echo '<h2>you are not allowed access to this URL<h2>';
			die();
		}

	}

	public function get_all_users(){
        $callback = isset($request['callback']) ? $request['callback'] : false;

        $filter['USER_ID'] 		= $this->session->userdata('isId');
        $filter['USER_NAME'] 	= strtolower($this->input->get('USER_NAME'));
        $filter['USER_ROLE'] 	= strtolower($this->input->get('USER_ROLE'));
        $filter['USER_NIK'] 	= strtolower($this->input->get('USER_NIK'));
        $filter['USER_BRANCH'] 	= strtolower($this->input->get('USER_BRANCH'));

        $data = $this->m_user->getUsers($filter);

        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . json_encode($data) . ');';
        } else {
            header('Content-Type: application/x-json');
            echo json_encode($data);
        }

    }

    public function get_all_group(){
        $callback = isset($request['callback']) ? $request['callback'] : false;

        $filter['GROUP_NAME'] 	= strtolower($this->input->get('GROUP_NAME'));
        $data = $this->m_user->getGroup($filter);

        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . json_encode($data) . ');';
        } else {
            header('Content-Type: application/x-json');
            echo json_encode($data);
        }

    }

    public function get_all_branch(){
        $callback = isset($request['callback']) ? $request['callback'] : false;

        $filter['BRANCH_NAME'] 	= strtolower($this->input->get('BRANCH_NAME'));
        $data = $this->m_user->getBranch($filter);

        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . json_encode($data) . ');';
        } else {
            header('Content-Type: application/x-json');
            echo json_encode($data);
        }

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

	public function add_more_user(){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){

	    		if(count($this->m_user->checkUser($this->input->post('USER_NAME'))) > 0){
	    			$return = array(
			          'success' => false,
			          'message' => 'User already exists'
			        );
	    		}
		        else{
		        	$data = $this->m_user->addMoreUser();

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

	public function get_user_by_id($idHdr = NULL){
		$data = $this->m_user->getUserById($idHdr);
	    header('Content-Type: text/javascript');
	    echo json_encode($data);
	}

	public function update_user($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){

	    		$dataUser = $this->m_user->getUserById($idHdr);

	    		if(count($this->m_user->checkUser($this->input->post('USER_NAME'))) > 0 AND strtolower($dataUser['data'][0]['USER_NAME']) != strtolower($this->input->post('USER_NAME')) ){
	    			$return = array(
			          'success' => false,
			          'message' => 'User already exists'
			        );
	    		}
		        else{
		        	$data = $this->m_user->updateUser($idHdr);

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

	public function update_pass_user($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_user->updatePassUser($idHdr);

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

	public function delete_user($idHdr = NULL){
		if($id = $this->check_token()){
	    	if($id == $this->session->userdata('isId')){
		        $data = $this->m_user->deleteUser($idHdr);

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
}
