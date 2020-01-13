<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Yard_management extends CI_Controller {

	public function __construct(){
		parent::__construct();

		if(!$this->session->userdata('isLogin')){
			echo '<h2>you are not allowed access to this URL<h2>';
			die();
		}

	}

	public function index()
	{
    	$this->load->view('layouts/header');
    	$this->load->view('content/yard_management/placement_container');
	}

	public function yard_config(){
		$this->load->view('yard_config/config');
	}

}
