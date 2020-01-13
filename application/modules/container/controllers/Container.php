<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;
class Container extends CI_Controller {
	private $secret = 'this is key secret';
  	public function __construct(){
		parent::__construct();
     	$this->load->model('m_container');

	    if(!$this->session->userdata('isLogin')){
			echo '<h2>you are not allowed access to this URL<h2>';
			die();
		}


	}

	public function index(){
		$filter = array($this->input->post('CONTAINER_NUMBER'), $this->session->userdata('USER_BRANCH'),$this->input->post('HIST_COUNTER') );
		$dataContainer = $this->m_container->get_data_container_inquiry($filter);

		if(count($dataContainer) > 0){

			$dataContainer->HIST_CYCLE_LIST = $this->m_container->get_cycle_container(array($filter[0], $filter[1]));

		}
		header('Content-Type: text/javascript');

		die(json_encode($dataContainer));
	}

	public function container_history(){
		$filter = array($this->input->post('CONTAINER_NUMBER'), $this->session->userdata('USER_BRANCH'),$this->input->post('HIST_COUNTER') );
		$dataContainer = $this->m_container->get_data_container_history($filter);

		header('Content-Type: text/javascript');

		die(json_encode($dataContainer));
	}

	public function container_list(){
		$filter = array($this->session->userdata('USER_BRANCH'), $this->input->get('query'));
		$dataContainer = $this->m_container->get_all_container_list($filter);

		header('Content-Type: text/javascript');

		die(json_encode($dataContainer));
	}

	public function stuffing_history(){
		$callback = isset($request['callback']) ? $request['callback'] : false;

        $filter['BRANCH_ID']		= $this->session->userdata('USER_BRANCH');
        $filter['CONTAINER_NUMBER']	= strtolower($this->input->get('CONTAINER_NUMBER'));
        $filter['REQUEST_NUMBER'] 	= strtolower($this->input->get('REQUEST_NUMBER'));

        $data = $this->m_container->getStuffingHistory($filter);

        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . json_encode($data) . ');';
        } else {
            header('Content-Type: application/x-json');
            echo json_encode($data);
        }
	}

	public function stripping_history(){
		$callback = isset($request['callback']) ? $request['callback'] : false;

        $filter['BRANCH_ID']		= $this->session->userdata('USER_BRANCH');
        $filter['CONTAINER_NUMBER']	= strtolower($this->input->get('CONTAINER_NUMBER'));
        $filter['REQUEST_NUMBER'] 	= strtolower($this->input->get('REQUEST_NUMBER'));

        $data = $this->m_container->getStrippingHistory($filter);

        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . json_encode($data) . ');';
        } else {
            header('Content-Type: application/x-json');
            echo json_encode($data);
        }
	}

	public function get_stay_period_container(){

        $data = $this->m_container->getStayPeriodContainer();
	    header('Content-Type: text/javascript');
	    die(json_encode($data));
	}

	public function print_stay_period_pdf(){
		ini_set('memory_limit', '-1');
		$this->load->library('m_pdf');
	   	$this->load->model('m_master');
	    $data['branch'] = $this->m_master->get_branch();
	    $name = 'Stay_Period_Container.pdf';
	    $data['data'] = $this->m_container->getPrintStayPeriodContainer();

	    $html = $this->load->view('stay_period_pdf',$data,true);
	    //Write some HTML code:
	    $this->m_pdf->export->WriteHTML($html);
	    $this->m_pdf->export->Output($name,'I');
	}

	public function print_stay_period_excel(){
		$this->load->model('m_master');
		$name = 'Stay_Period_Container';
	    $data['data'] = $this->m_container->getPrintStayPeriodContainer();

	    $data['branch'] = $this->m_master->get_branch();
	    $html = $this->load->view('stay_period_html',$data,true);
	    header("Content-type: application/vnd-ms-excel");
	    header("Content-Disposition: attachment; filename=$name.xls");
	    header("Pragma: no-cache");
	    header("Expires: 0");
	    echo $html;
	}

	public function getMasterContainer(){
		$filter = array($this->session->userdata('USER_BRANCH'), $this->input->get('query'));
		$data = $this->m_container->getMasterContainer($filter);
		header('Content-Type: text/javascript');
		die(json_encode($data));

	}


	public function setContainerRename(){
		if($id = $this->check_token()){
			if($id == $this->session->userdata('isId')){
				$data = $this->m_container->setContainerRename();
				$return = $data;
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
		header('Content-Type: text/javascript');
		echo json_encode($return);

	}

	public function pluggingReeferHistory(){
		$callback = isset($request['callback']) ? $request['callback'] : false;

        $filter['BRANCH_ID']		= $this->session->userdata('USER_BRANCH');
        $filter['CONTAINER_NUMBER']	= strtolower($this->input->get('CONTAINER_NUMBER'));
        $filter['REQUEST_NUMBER'] 	= strtolower($this->input->get('REQUEST_NUMBER'));

        $data = $this->m_container->getPluggingReeferHistory($filter);

        if ($callback) {
            header('Content-Type: text/javascript');
            echo $callback . '(' . json_encode($data) . ');';
        } else {
            header('Content-Type: application/x-json');
            echo json_encode($data);
        }
	}

	public function setContainerSize(){
		if($id = $this->check_token()){
			if($id == $this->session->userdata('isId')){
				$data = $this->m_container->setContainerSize();
				$return = $data;
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
		header('Content-Type: text/javascript');
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
}
