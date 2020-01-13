<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
require_once APPPATH . '/libraries/BeforeValidException.php';
require_once APPPATH . '/libraries/ExpiredException.php';
require_once APPPATH . '/libraries/SignatureInvalidException.php';
use \Firebase\JWT\JWT;

class BD_Controller extends REST_Controller
{
	private $user_credential;
    public function auth_berier()
    {
        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
        //JWT Auth middleware
        $headers = $this->input->get_request_header('Authorization');
        $kunci = $this->config->item('thekey'); //secret key for encode and decode
        $token= "token";
       	if (!empty($headers)) {
        	if (preg_match('/Bearer\s(\S+)/', $headers , $matches)) {
            $token = $matches[1];
        	}
    	}
        try {
           $decoded = JWT::decode($token, $kunci, array('HS256'));
           $this->user_data = $decoded;
        } catch (Exception $e) {
            $invalid = ['status' => $e->getMessage()]; //Respon if credential invalid
            $this->response($invalid, 401);//401
        }
    }

    function auth_basic(){
        $ci =& get_instance();
        $ci->load->database();
        $ci->reponpks = $ci->load->database('reponpks',true);
    
        if (empty($ci->input->server('PHP_AUTH_USER')))
        {
            header('HTTP/1.0 401 Unauthorized');
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Basic realm="My Realm"');
            echo 'You must login to use this service'; // User sees this if hit cancel
            die();
            }

            $username = $ci->input->server('PHP_AUTH_USER');
            $password = $ci->input->server('PHP_AUTH_PW');

            $val = $ci->reponpks->where('USERNAME',$username)->get('M_USER_API')->row();
            if($ci->reponpks->where('USERNAME',$username)->get('M_USER_API')->num_rows() == 0){echo 'Username and Password not match';die();}
            $match = $val->PASSWORD;
            if(sha1($password) != $match){
                echo 'Username and Password not match'; die();
            }
    }

    function auth_api(){
        $ci =& get_instance();
        $ci->load->database();
        $ci->reponpks = $ci->load->database('reponpks',true);
        $arrVal = $ci->input->request_headers();
        $key = false;
        foreach($arrVal as $a => $b){            
            if(substr($a,0,3) == 'key'){
                $key = true;
                $username = trim(substr($a,4,15));
                $password = trim($b);
            }            
        }     
        
        if ($key)
        {            
            $val = $ci->reponpks->where('USERNAME',$username)->get('M_USER_API')->row();
            if($ci->reponpks->where('USERNAME',$username)->get('M_USER_API')->num_rows() == 0){echo 'Key and Value not match';die();}
            $match = $val->PASSWORD;
            if(sha1($password) != $match){
                echo 'Key and Value not match'; die();
            }
        }else{
            echo 'Key and Value not valid'; die();
        }
    }
}