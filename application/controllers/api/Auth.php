<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use \Firebase\JWT\JWT;

class Auth extends BD_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
        $this->load->model('M_main');
        $this->npksrepo = $this->load->database('npksrepo',true);
    }

    public function login_post()
    {
        $u = $this->post('username'); //Username Posted
        $p = sha1($this->post('password')); //Pasword Posted
        $q = array('username' => $u); //For where query condition
        $kunci = $this->config->item('thekey');
        $invalidLogin = ['status' => 'Invalid Login']; //Respon if login invalid
        $val = $this->M_main->get_user($u)->row(); //Model to get single data row from database base on username
        if($this->M_main->get_user($u)->num_rows() == 0){$this->response($invalidLogin, REST_Controller::HTTP_NOT_FOUND);}
		$match = $val->PASSWORD;   //Get password for user from database
        if($p == $match){  //Condition if password matched
        	$token['id'] = $val->ID;  //From here
            $token['username'] = $u;
            $date = new DateTime();
            $token['iat'] = $date->getTimestamp();
            $token['exp'] = $date->getTimestamp() + 60*60*5; //To here is to generate token
            $output['token'] = JWT::encode($token,$kunci ); //This is the output token
            $this->set_response($output, REST_Controller::HTTP_OK); //This is the respon if success
        }
        else {
            $this->set_response($invalidLogin, REST_Controller::HTTP_NOT_FOUND); //This is the respon if failed
        }
    }

    public function pass_post()
    {
        $p = sha1($this->post('password')); //Pasword Posted        
        $this->set_response($p, REST_Controller::HTTP_OK); //This is the respon if succes       
    }


    public function decode_post()
    {
        $kunci = $this->config->item('thekey');
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjEiLCJ1c2VybmFtZSI6Im5wa3MiLCJpYXQiOjE1Nzc3NzI5MjYsImV4cCI6MTU3Nzc5MDkyNn0.F0l4k7Nq1i5eyQlTwoTu4Jh65YAnv6hiGmfJmVSeW9Q';
        $verify = true;
        $output  = JWT::decode($token,$kunci,array('HS256'),$verify); //This is the output token
        $this->set_response($output, REST_Controller::HTTP_OK); //This is the respon if success
    }

}
