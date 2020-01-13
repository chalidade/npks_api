<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_main extends CI_Model{

	function get_user($q) {
		$this->reponpks = $this->load->database('reponpks',true);
		return $this->reponpks->where('USERNAME',$q)->get('M_USER_API');
	}

	
}