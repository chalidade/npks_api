<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH .'/libraries/SignatureInvalidException.php';
require_once APPPATH .'/libraries/JWT.php';
// require_once 'vendor/mike42/escpos-php/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\SignatureInvalidException;
// use Mike42\Escpos\Printer;
// use Mike42\Escpos\ImagickEscposImage;
// use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class Print_ extends CI_Controller {
	private $secret = 'this is key secret';

	public function index()
	{
    $this->load->library('m_pdf');
		// $this->load->library('Printer');
    $this->load->model('m_master');
    $data['gate'] = explode("~",$this->input->get('params'));
    $data['branch'] = $this->m_master->get_branch();
		$date = date('dmYHis');
		// print_r($data); die();
    $html = $this->load->view('print/print',$data,true);
		$name = 'assets/cms/gate_'.$date.'.pdf';

    //rite some HTML code:
		$header = '<img src="'.DIR.'/../assets/logo.png" width="100" height="40" style=" margin-left:-43px; margin-top:-20px;">';
		$header .= '<div style="margin-left:-44px;margin-right:-45px; margin-top:-10px"><hr></div>';
		$this->m_pdf->pdf->SetHTMLHeader($header);
    $this->m_pdf->pdf->WriteHTML($html);
    // $this->m_pdf->pdf->SetJS('window.print();');
		$this->m_pdf->pdf->SetJS('this.print();');
		$this->m_pdf->pdf->Output($name,'I');

		// exec("\"C:\\Program Files (x86)\\Adobe\\Acrobat Reader DC\\Reader\\AcroRd32.exe\" /t \"D:\\Webserver\\xampp5.6\\htdocs\\npks-api\\assets\\cms\\gate_17102018113918.pdf");


		// $temp = "/assets/cms/gate_17102018113918.pdf";
		// $pHandle = fopen($name, "r+");
		// $pHandle = file_get_contents($name);
		//
		// // $fh = fopen($name, "rb");
		// // $content = fread($fh, filesize($name));
		// // fclose($fh);
		//
		// $data = $this->m_master->getPrinter();
		// // $handle = printer_open($data['PRINTER']);
		// $handle = printer_open('Microsoft Print to PDF');
		// if(!$handle || $handle == NULL)
		// {
		// 	die('Error connecting to Printer');
		// }
		// else
		// {
		// 		$content = 'DERIAN';
		// 		printer_set_option($handle, PRINTER_MODE, "RAW");
		// 		// printer_start_doc($handle);
		// 		// printer_start_page($handle);
		// 		printer_write($handle,$pHandle);
		// 		// printer_end_page($handle);
		// 		// printer_end_doc($handle);
		// 		printer_close($handle);
		// }

		/* 1: Print an entire PDF, start-to-finish (shorter form of the example) */
		// $image = new \Imagick('D:\Webserver\xampp5.6\htdocs\npks-api\assets\cms\gate_17102018113918.pdf');
		//$pdf =  "./assets/cms/gate_17102018113918.pdf";
		// $pdf =  dirname(__FILE__,3)."\assets\cms\gate_17102018175508.pdf";

		// $pdf = $temp;
		// $connector = new WindowsPrintConnector("loc1");
		// $printer = new Printer($connector);
		// try {
		//     $pages = ImagickEscposImage::loadPdf($pdf);
		//     foreach ($pages as $page) {
		//         $printer -> graphics($page);
		//     }
		//     $printer -> cut();
		// } catch (Exception $e) {
		//     /*
		// 	 * loadPdf() throws exceptions if files or not found, or you don't have the
		// 	 * imagick extension to read PDF's
		// 	 */
		//     echo $e -> getMessage() . "\n";
		// } finally {
		//     $printer -> close();
		// }

	}

	function test_print()
  {
      // $pHandle = fopen("gate.pdf", "r+");
			//
      // $handle = printer_open("EPSON L220 Series");
      // if(!$handle || $handle == NULL)
      // {
      //   die('Error connecting to Printer');
      // }
      // else
      // {
      //     printer_set_option($handle, PRINTER_MODE, "raw");
      //     printer_write($handle,$pHandle);
      //     printer_close($handle);
      // }

			$html = 'DERIAN';
			/* select printer to print */
			// $this->load->model('m_master');
			// $data = $this->m_master->getPrinter();
			$printer = printer_open();
			/* write the text to the print job */
			printer_set_option($printer, PRINTER_MODE, "raw");
			printer_start_doc($printer);
			printer_start_page($printer);
			printer_write($printer, $html);
			printer_end_page($printer);
			printer_end_doc($printer);
			/* close the connection */
			printer_close($printer);

			// printer_write($handle, fread($pHandle, filesize("gate.pdf")));

  }

	public function test(){
      $this->load->library('m_pdf');

			// echo substr(DIR,0,-10); die();
			$printerList = printer_list(PRINTER_ENUM_LOCAL);
        var_dump($printerList);
				echo '<br>';
				$printerName = $printerList[4]['NAME'];
	      echo $printerName;
			echo '<img src="'.substr(DIR,0,-10).'/assets/logo.png" width="100" height="40">';
			// die();
			//
      //       // Write some HTML code:
      // $this->m_pdf->pdf->WriteHTML('Hello World');
			//
      // // Output a PDF file directly to the browser
      // // $this->m_pdf->pdf->SetJS('this.print();');
      // $this->m_pdf->pdf->SetJS('window.print();');
			//
      // $this->m_pdf->pdf->Output();

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

	public function printer_list(){
		$printerList = printer_list(PRINTER_ENUM_LOCAL);
		$arrData = array();
		// foreach ($printerList as $val) {
		// 	$arrData[]['NAME'] = $val['NAME'];
		// }
		header('Content-Type: text/javascript');
		echo json_encode($printerList);
	}

	public function setPrinter(){
		$this->load->model('m_master');
		$data = $this->m_master->setPrinter();
		header('Content-Type: text/javascript');
		echo json_encode($data);
	}

	public function getPrinter(){
		$this->load->model('m_master');
		$data = $this->m_master->getPrinter();
		header('Content-Type: text/javascript');
		echo json_encode($data);
	}

	public function di(){
		echo $_SERVER['DOCUMENT_ROOT']."/npks-api/assets/cms/gate_17102018113918.pdf";
		// exec("\"C:\\Program Files\\Adobe\\Reader 9.0\\Reader\\AcroRd32.exe\" /t \"C:\\PathToPDF.pdf");
		exec("\"C:\\Program Files (x86)\\Adobe\\Acrobat Reader DC\\Reader\\AcroRd32.exe\" /t \"D:\\Webserver\\xampp5.6\\htdocs\\npks-api\\assets\\cms\\gate_17102018113918.pdf");
		// exec('\"C:\\Program Files (x86)\\Adobe\\Acrobat Reader DC\\Reader\\AcroRd32.exe\" /t \"D:\\Webserver\\xampp5.6\\htdocs\\npks-api\\assets\\cms\\gate_17102018113918.pdf"');
	}

	public function imagick()
	{
    // $this->load->library('m_pdf');
		// // $this->load->library('Printer');
    // $this->load->model('m_master');
    // $data['gate'] = explode("~",$this->input->get('params'));
    // $data['branch'] = $this->m_master->get_branch();
		// $date = date('dmYHis');
		//
    // $html = $this->load->view('print/print',$data,true);
		// $name = 'assets/cms/gate_'.$date.'.pdf';
		//
    // //rite some HTML code:
		// $header = '<img src="'.DIR.'/../assets/logo.png" width="100" height="40" style=" margin-left:-43px; margin-top:-20px;">';
		// $header .= '<div style="margin-left:-44px;margin-right:-45px; margin-top:-10px"><hr></div>';
		// $this->m_pdf->pdf->SetHTMLHeader($header);
    // $this->m_pdf->pdf->WriteHTML($html);
    // // $this->m_pdf->pdf->SetJS('window.print();');
		// $this->m_pdf->pdf->SetJS('this.print();');
		// $this->m_pdf->pdf->Output($name,'F');
		//
		// // exec("\"C:\\Program Files (x86)\\Adobe\\Acrobat Reader DC\\Reader\\AcroRd32.exe\" /t \"D:\\Webserver\\xampp5.6\\htdocs\\npks-api\\assets\\cms\\gate_17102018113918.pdf");
		//
		//
		// $temp = "/assets/cms/gate_17102018175920.pdf";
		// $pHandle = fopen($name, "r+");
		// $pHandle = file_get_contents($name);
		//
		// // $fh = fopen($name, "rb");
		// // $content = fread($fh, filesize($name));
		// // fclose($fh);
		//
		// $data = $this->m_master->getPrinter();
		// // $handle = printer_open($data['PRINTER']);
		// $handle = printer_open('Microsoft Print to PDF');
		// if(!$handle || $handle == NULL)
		// {
		// 	die('Error connecting to Printer');
		// }
		// else
		// {
		// 		$content = 'DERIAN';
		// 		printer_set_option($handle, PRINTER_MODE, "RAW");
		// 		// printer_start_doc($handle);
		// 		// printer_start_page($handle);
		// 		printer_write($handle,$pHandle);
		// 		// printer_end_page($handle);
		// 		// printer_end_doc($handle);
		// 		printer_close($handle);
		// }

		/* 1: Print an entire PDF, start-to-finish (shorter form of the example) */
		// $image = new \Imagick('D:\Webserver\xampp5.6\htdocs\npks-api\assets\cms\gate_17102018175920.pdf');
		$image = new Imagick($_SERVER['DOCUMENT_ROOT'] . '/npks-api/assets/cms/gate_17102018175920.pdf');
		// echo $_SERVER['DOCUMENT_ROOT'] . '/npks-api/assets/cms/gate_17102018175920.pdf';
		$pdf =  "assets/cms/gate_17102018175920.pdf";
		$pdf_handle = fopen('assets/cms/gate_17102018175920.pdf','rb');
		// $pdf = $temp;
		$connector = new WindowsPrintConnector("LOCALSHARING");
		$printer = new Printer($connector);
		try {
		    $pages = ImagickEscposImage::loadPdf($image);
		    foreach ($pages as $page) {
		        $printer -> graphics($page);
		    }
		    $printer -> cut();
		} catch (Exception $e) {
		    /*
			 * loadPdf() throws exceptions if files or not found, or you don't have the
			 * imagick extension to read PDF's
			 */
		    echo $e -> getMessage() . "\n";
		} finally {
		    $printer -> close();
		}

	}

	public function imagic(){
		$pdf_handle = fopen('assets/cms/gate_17102018175920.pdf', 'rb');
		$loc = 'assets/cms/';
		$doc_preview = new Imagick();
		$doc_preview->setResolution(180,180);
		$doc_preview->readImageFile($pdf_handle);
		$doc_preview->setIteratorIndex(0);
		$doc_preview->setImageFormat('jpeg');
		$doc_preview->writeImage($loc);
		$doc_preview->clear();
		$doc_preview->destroy();
	}


}
