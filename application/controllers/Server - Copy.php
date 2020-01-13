<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Server extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library("Nusoap_library");
		$this->nusoap_server = new soap_server();
		$this->nusoap_server->configureWSDL('NPKS','urn:npks_server');
	}


	public function index(){

		$this->nusoap_server->register('hello',	// method name
		array('name' => 'xsd:string'),        	// input parameters
		array('return' => 'xsd:string'),    	// output parameters
		'urn:nusoap_server',                    // namespace
		'urn:nusoap_server#hello',              // soapaction
		'rpc',                                	// style
		'encoded',                            	// use
		'Says hello to the caller'            	// documentation
		);


		$this->nusoap_server->register('testxml',	// method name
		array('name' => 'xsd:string'),        	// input parameters
		array('return' => 'xsd:string'),    	// output parameters
		'urn:nusoap_server',                    // namespace
		'urn:nusoap_server#testxml',            	// soapaction
		'rpc',                                	// style
		'encoded',                            	// use
		'Says hello to the caller'            	// documentation
		);

		function hello($name) {   //fungsi yg d jalankan ktika webservice d panggil
		return 'Hellooo, ' . $name;
		}

		function testxml($name){
			//return $name;
			$return = '<?xml version="1.0" encoding="UTF-8" ?>';
        	$return .= '<DOCUMENT>';
        	$return .= '<HEADER>';
            $return .= '<CAR>'.$name.'</CAR>';	
            $return .= '<CAR1>dian</CAR1>';
            $return .= '</HEADER>';
            $return .= '<DOCUMENT>';
            return $return;
		}

		// Use the request to (try to) invoke the service
		$GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents ('php://input');
		$HTTP_RAW_POST_DATA = $GLOBALS['HTTP_RAW_POST_DATA'];
		//$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : ”;
		$this->nusoap_server->service($HTTP_RAW_POST_DATA);
	}


	function cserver0(){
		$this->nusoap_server->register('cserver0',	// method name
		array('name' => 'xsd:string'),        	// input parameters
		array('return' => 'xsd:string'),    	// output parameters
		'urn:nusoap_server',                    // namespace
		'urn:nusoap_server#cserver0',              // soapaction
		'rpc',                                	// style
		'encoded',                            	// use
		'Says hello to the caller'            	// documentation
		);

		function cserver0($name) {   //fungsi yg d jalankan ktika webservice d panggil
		return 'Hellooo, ' . $name;
		}

		// Use the request to (try to) invoke the service
		$GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents ('php://input');
		$HTTP_RAW_POST_DATA = $GLOBALS['HTTP_RAW_POST_DATA'];
		//$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : ”;
		$this->nusoap_server->service($HTTP_RAW_POST_DATA);
		
	}


	

}
