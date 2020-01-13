<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;

class Welcome extends CI_Controller {
	private $secret = 'this is key secret';
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function test(){
		echo APPPATH;
	}

	public function check_token()
	{
		$jwt = $this->input->get_request_header('Authorization');
		try {
			$decoded = JWT::decode($jwt, $this->secret, array('HS256'));
			echo $decoded->id;
		} catch(\Exception $e) {
			return $this->response([
				'success' => false,
				'message' => 'gagal, error token'
			], 401);
		}
	}

	public function login()
	{
		$date = new DateTime();

		if (!$this->user->is_valid()) {
			return $this->response([
				'success' => false,
				'message' => 'email atau password salah'
			]);
		}

		$user = $this->user->get('email', $this->input->post('email'));
		//lanjutkan encode datanya
		$payload['id']    = $user->id;
		$payload['iat']   = $date->getTimestamp();
		$payload['exp']   = $date->getTimestamp() + 60*60*2;

		$output['id_token'] = JWT::encode($payload, $this->secret);
		$this->response($output);
	}

	public function response($data, $status = 200)
	{
		$this->output
			 ->set_content_type('application/json')
			 ->set_status_header($status)
			 ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
			 ->_display();

		exit;
	}


}
