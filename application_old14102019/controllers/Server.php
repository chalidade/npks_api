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

		// $this->nusoap_server->register('hello',	// method name
		// array('name' => 'xsd:string'),        	// input parameters
		// array('return' => 'xsd:string'),    	// output parameters
		// 'urn:nusoap_server',                    // namespace
		// 'urn:nusoap_server#hello',              // soapaction
		// 'rpc',                                	// style
		// 'encoded',                            	// use
		// 'Says hello to the caller'            	// documentation
		// );


		// $this->nusoap_server->register('testxml',	// method name
		// array('name' => 'xsd:string'),        	// input parameters
		// array('return' => 'xsd:string'),    	// output parameters
		// 'urn:nusoap_server',                    // namespace
		// 'urn:nusoap_server#testxml',            	// soapaction
		// 'rpc',                                	// style
		// 'encoded',                            	// use
		// 'Says hello to the caller'            	// documentation
		// );

		//{"name":"Bobi Hariadi1yyyyy","address":"Jakarta Selatan","phone":"081374336102","age":"28","func":"testSample"}

		$this->nusoap_server->register('test1',	// method name
		array('params' => 'xsd:string'),        	// input parameters
		array('return' => 'xsd:string'),    	// output parameters
		'urn:nusoap_server',                    // namespace
		'urn:nusoap_server#hello',              // soapaction
		'rpc',                                	// style
		'encoded',                            	// use
		'Says hello to the caller'            	// documentation
		);

		// $this->nusoap_server->register('testEsb',	// method name
		// array('params' => 'xsd:string'),        	// input parameters
		// array('return' => 'xsd:string'),    	// output parameters
		// 'urn:nusoap_server',                    // namespace
		// 'urn:nusoap_server#hello',              // soapaction
		// 'rpc',                                	// style
		// 'encoded',                            	// use
		// 'Says hello to the caller'            	// documentation
		// );

		function test1($params){
			$val['name'] = "Lorem Ipsum";
			$val['note'] = "Good Job";

			$value = json_encode($val);
			return $value;
		}

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

		
		function testEsb($params) {   //fungsi yg d jalankan ktika webservice d panggil
			$paramLempar = json_encode($params);
			$param = json_decode($params, true);
			// return $param;die();
			$func = $param['func'];
			return $func($paramLempar);
			// $val ['Name'] = $name;
			// $val ['Address'] = 'Jakarta';
			// $val ['Phone'] = '081374336102';

			// $value = json_encode($val);
			// $arr ['params'] = $value;
			// // $arr ['params1'] = "test1";//$value;
			// // return 'Hellooo, ' . $name;
			// $balikan = json_encode($arr);
			// return $return;
		}

		function testSample($para){
			$arrPara = json_decode($para);
			$arrdata = json_decode($arrPara,true);
			// echo $arrdata['name'];die();
			$val ['Name'] = $arrdata['name'];
			$val ['Address'] = $arrdata['address'];
			$val ['Phone'] = $arrdata['phone'];
			$val ['Age'] = $arrdata['age'];
			$val ['Function'] = 'test Sample';

			$value = json_encode($val);
			$arr ['params'] = $value;
			$balikan = json_encode($arr);
			return $balikan;
		}

		function testSampleXML($para){
			$arrPara = json_decode($para);
			$arrdata = json_decode($arrPara,true);
			
			$return = '<?xml version="1.0" encoding="UTF-8" ?>';
        	$return .= '<DOCUMENT>';
        	$return .= '<HEADER>';
            $return .= '<NAMA>'.$arrdata['name'].'</NAMA>';	
            $return .= '<ADDRESS>'.$arrdata['address'].'</ADDRESS>';	
            $return .= '<PHONE>'.$arrdata['phone'].'</PHONE>';	
            $return .= '<AGE>'.$arrdata['age'].'</AGE>';	
            $return .= '<FUNCTION>XML</FUNCTION>';
            $return .= '</HEADER>';
            $return .= '<DOCUMENT>';

			return $return;
		}

		function getReceiving($para){	
			$arrPara = json_decode($para);
			$arrdata = json_decode($arrPara,true);
			// echo $arrdata['name'];die();
			$val ['Name'] = $arrdata['name'];
			$val ['Address'] = $arrdata['address'];
			$val ['Phone'] = $arrdata['phone'];
			$val ['Function'] = 'get Receiving';

			$value = json_encode($val);
			$arr ['params'] = $value;
			$balikan = json_encode($arr);
			return $balikan;
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
