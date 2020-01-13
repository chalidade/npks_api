<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library("Nusoap_library");
        $this->load->database();
		//$client = new nusoap_client();
		//$this->nusoap_client->configureWSDL('nusoap_server','urn:nusoap_server');
	}


	public function index(){
		show_404();
		die();
	}

	function test(){
		$client = "http://127.0.0.1/npks-api/index.php/server";//alamat web service
        $method = 'hello'; //method
        $params = array('name'=>'bobi asdfsdfriadi');

        $response = $this->call_service($client, $method,$params);

        echo $response;
	}

	function testxml(){
		$aa =  rand(100,999);
		echo $aa;
		die();


		$client = "http://127.0.0.1/npks-api/index.php/server";//alamat web service
        $method = 'testxml'; //method
        $params = array('name'=>'bobi hariadi');

        $response = $this->call_service($client, $method,$params);

        $xml = xml2ary($response);

    	$nama = $xml['DOCUMENT']['_c']['HEADER']['_c']['CAR1']['_v'];

        echo $nama;
	}

	function getServer(){
		echo SERVICE_SERVER;
	}

	function getDelivery(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
        $method = 'getDelivery'; //method

        $sqlLastDateNota = "select TGL from (
                            select TO_CHAR(REQUEST_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_DELIVERY_HDR order by REQUEST_PAID_DATE DESC
                            )A where rownum =1";

        $resultLastDateNota = $this->db->query($sqlLastDateNota)->result_array();
        $lastDateNota = $resultLastDateNota[0]['TGL'];
        $lastDateNota = '20180712172903';

        $params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);
				// echo $response; die();

        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
        					VALUES (".$ServiceID.",'".$method."','".$lastDateNota."','".$response."','0')";
        $this->db->query($insertServices);

        echo date('Y-m-d H:i:s')."<br>\n";
	}

	function getReceiving(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
        $method = 'getReceiving'; //method

        $sqlLastDateNota = "select TGL from (
                            select TO_CHAR(REQUEST_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_RECEIVING_HDR order by REQUEST_PAID_DATE DESC
                            )A where rownum =1";

        $resultLastDateNota = $this->db->query($sqlLastDateNota)->result_array();
        $lastDateNota = $resultLastDateNota[0]['TGL'];

        $params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);

				print_r($response); die();

        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
        					VALUES (".$ServiceID.",'".$method."','".$lastDateNota."','".$response."','0')";
        $this->db->query($insertServices);

        echo date('Y-m-d H:i:s')."<br>\n";
	}

	function getStuffing(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
				$method = 'getStuffing'; //method

				$sqlLastDateNota = "select TGL from (
														select TO_CHAR(STUFF_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_STUFF_HDR order by STUFF_PAID_DATE DESC
														)A where rownum =1";

				$resultLastDateNota = $this->db->query($sqlLastDateNota)->result_array();
				$lastDateNota = $resultLastDateNota[0]['TGL'];
				$lastDateNota = '20180712172903';
				// echo $lastDateNota; die();
				$params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

				$ServiceID = date('YmdHis').rand(100,999);
				$response = $this->call_service($client, $method,$params);

				$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
									VALUES (".$ServiceID.",'".$method."','".$lastDateNota."','".$response."','0')";
				$this->db->query($insertServices);

				echo date('Y-m-d H:i:s')."<br>\n";
	}

	function getStripping(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
				$method = 'getStripping'; //method

				$sqlLastDateNota = "select TGL from (
														select TO_CHAR(STRIP_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_STRIP_HDR order by STRIP_PAID_DATE DESC
														)A where rownum =1";

				$resultLastDateNota = $this->db->query($sqlLastDateNota)->result_array();
				$lastDateNota = @$resultLastDateNota[0]['TGL'];
				$lastDateNota = '20180712172903';
				// echo $lastDateNota; die();
				$params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

				$ServiceID = date('YmdHis').rand(100,999);
				$response = $this->call_service($client, $method,$params);
				// print_r($response); die();

				$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
									VALUES (".$ServiceID.",'".$method."','".$lastDateNota."','".$response."','0')";
				$this->db->query($insertServices);

				echo date('Y-m-d H:i:s')."<br>\n";
	}

    function GenerateReceiving(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '0' AND services_method = 'getReceiving' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
        		if($row['SERVICES_RESP_XML'] == ""){
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
        			 $this->db->query($updateServices);
        			echo "tidak ada response <br>\n";
        			continue;
        		}
		        $response = $row['SERVICES_RESP_XML']->read(2000000);
		        $error = 0;
		        $xml = xml2ary($response);
		        $valResponse = $xml['document']['_c']['respon']['_v'];
		        if($valResponse != 0){
		            $loop =  $xml['document']['_c']['loop']['_c']['data'];
		            $totalHDR = count($loop);
		            $i =0;
		            while ($i < $totalHDR) {
		                if($totalHDR == 1){
		                    $header = $loop['_c']['header']['_c'];
		                    $detail = $loop['_c']['arrdetail']['_c']['detail'];
		                }else{
		                    $header = $loop[$i]['_c']['header']['_c'];
		                    $detail = $loop[$i]['_c']['arrdetail']['_c']['detail'];
		                }

		                $REQ_NO = $header['REQ_NO']['_v'];
		                $NO_NOTA = $header['NO_NOTA']['_v'];
		                $TGL_NOTA = $header['TGL_NOTA']['_v'];
		                $REQ_RECEIVING_DATE = $header['REQ_RECEIVING_DATE']['_v'];
		                $NM_CONSIGNEE = $header['NM_CONSIGNEE']['_v'];
		                $REQ_MARK = $header['REQ_MARK']['_v'];
		                $NPWP =  str_replace(".", "",str_replace("-", "", trim($header['NPWP']['_v'])));
		                $ALAMAT = $header['ALAMAT']['_v'];
		                $TANGGAL_LUNAS = $header['TANGGAL_LUNAS']['_v'];

		                $sqlcek = "SELECT REQUEST_NO FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO='".$REQ_NO."'";
		                $resultCek = $this->db->query($sqlcek);
		                $totalcek = $resultCek->num_rows();

		                if($totalcek <=0){
		                    $sqlceknpwp = "SELECT CONSIGNEE_ID FROM TM_CONSIGNEE WHERE CONSIGNEE_NPWP='".$NPWP."'";
		                    $resultCeknpwp = $this->db->query($sqlceknpwp);
		                    $totalceknpwp = $resultCeknpwp->num_rows();

		                    if($totalceknpwp <=0){
		                        $qlIDCONSIGNEE = "SELECT SEQ_REQ_RECEIVING_HDR.NEXTVAL AS ID FROM DUAL";
		                        $resultIDCONSIGNEE = $this->db->query($qlIDCONSIGNEE)->result_array();
		                        $CONSIGNE_ID = $resultIDCONSIGNEE[0]['ID'];

		                        $insertConsignee = "INSERT INTO TM_CONSIGNEE (CONSIGNEE_ID, CONSIGNEE_NAME, CONSIGNEE_ADDRESS, CONSIGNEE_NPWP) VALUES (".$CONSIGNE_ID.",'".$NM_CONSIGNEE."','".$ALAMAT."','".$NPWP."')";
		                        $this->db->query($insertConsignee);
		                    }else{
		                        $resultnpwp = $resultCeknpwp->result_array();
		                        $CONSIGNE_ID = $resultnpwp[0]['CONSIGNEE_ID'];
		                    }

		                    $qlID = "SELECT SEQ_REQ_RECEIVING_HDR.NEXTVAL AS ID FROM DUAL";
		                    $resultID = $this->db->query($qlID)->result_array();
		                    $IDheader = $resultID[0]['ID'];

		                    $insertHDR = "INSERT INTO TX_REQ_RECEIVING_HDR (REQUEST_ID, REQUEST_NO, REQUEST_CONSIGNEE_ID, REQUEST_BRANCH_ID, REQUEST_NOTA, REQUEST_MARK, REQUEST_RECEIVING_DATE, REQUEST_NOTA_DATE, REQUEST_PAID_DATE) VALUES
		                                 (".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",3,'".$NO_NOTA."','".$REQ_MARK."', TO_DATE('".$REQ_RECEIVING_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS') )";
		                    $resultHDR = $this->db->query($insertHDR);

		                    if($resultHDR){
		                        echo $i.", sukses | ".$REQ_NO."<br>\n";
		                        $totalDTL = count($detail);
		                        $a = 0;
		                        while($a < $totalDTL){
		                            if($totalDTL == 1){
		                                $detailroot = $detail['_c'];
		                            }else{
		                                $detailroot = $detail[$a]['_c'];
		                            }
		                            $REQ_DTL_CONT = trim($detailroot['REQ_DTL_CONT']['_v']);
		                            $REQ_DTL_CONT_STATUS = trim($detailroot['REQ_DTL_CONT_STATUS']['_v']);
		                            $REQ_DTL_COMMODITY = trim($detailroot['REQ_DTL_COMMODITY']['_v']);
		                            $REQ_DTL_VIA = trim($detailroot['REQ_DTL_VIA']['_v']);
		                            $REQ_DTL_CONT_HAZARD = trim($detailroot['REQ_DTL_CONT_HAZARD']['_v']);
		                            $REQ_DTL_SIZE = trim($detailroot['REQ_DTL_SIZE']['_v']);
		                            $REQ_DTL_TYPE = trim($detailroot['REQ_DTL_TYPE']['_v']);

		                            $insertDTL = "INSERT INTO TX_REQ_RECEIVING_DTL (REQUEST_DTL_ID, REQUEST_HDR_ID, REQUEST_DTL_CONT, REQUEST_DTL_CONT_STATUS, REQUEST_DTL_DANGER, REQUEST_DTL_CONT_SIZE, REQUEST_DTL_CONT_TYPE, REQUEST_DTL_COMMODITY)
		                                          VALUES (SEQ_REQ_RECEIVING_DTL.NEXTVAL, ".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_STATUS."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."')";
		                            $resultDtl = $this->db->query($insertDTL);
		                            if($resultDtl){
		                                echo $a.", sukses detil | ".$REQ_DTL_CONT."<br>\n";
		                            }else{
		                                echo $a.", gagal detil | ".$REQ_DTL_CONT."<br>\n";
		                            }

		                            $a++;
		                        }
		                    }else{
		                        echo $i.", gagal | ".$REQ_NO."<br>\n";
		                        $error++;
		                    }
		                }else{
		                    echo "data sudah ada | ".$REQ_NO."<br>\n";
		                }
		            $i++;
		            }
		            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
        			$this->db->query($updateServices);
		        }else{
		             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
        			 $this->db->query($updateServices);
        			 die("tidak ada data <br>\n");
		        }

       	 	}
        }else{
        	die("tidak ada data untuk digenerate <br>\n");
        }

    }

    function generateSetGetIn(){
    	$sqlgetIn = "SELECT GATE_CONT AS NO_CONTAINER, GATE_NOREQ AS NO_REQUEST, GATE_TRUCK_NO AS NOPOL, GATE_CREATE_BY AS ID_USER, TO_CHAR(GATE_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_IN, GATE_CONT_STATUS AS STATUS, GATE_ORIGIN FROM TX_GATE A WHERE GATE_FL_SEND = '0' AND GATE_ACTIVITY = '3' ORDER BY GATE_CREATE_DATE ASC";
    	$resultservices = $this->db->query($sqlgetIn);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
    		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
        	foreach($resultservices->result_array() as $row){
        		$returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$row['NO_CONTAINER'].'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$row['NO_REQUEST'].'</NO_REQUEST>';
        		$returnXML .= '<NOPOL>'.$row['NOPOL'].'</NOPOL>';
        		$returnXML .= '<ID_USER>'.$row['ID_USER'].'</ID_USER>';
        		$returnXML .= '<TGL_IN>'.$row['TGL_IN'].'</TGL_IN>';
        		$returnXML .= '<STATUS>'.$row['STATUS'].'</STATUS>';
        		$returnXML .= '<GATE_ORIGIN>'.$row['GATE_ORIGIN'].'</GATE_ORIGIN>';
        		$returnXML .= '</data>';
        	}
        	$returnXML .= '</loop>';
			$returnXML .= '</document>';


	        $ServiceID = date('YmdHis').rand(100,999);
	        $method = 'setGateIn'; //method
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."','".$returnXML."','0')";
	        $this->db->query($insertServices);

	        echo date('Y-m-d H:i:s')."<br>\n";
        }
    }

    function SetGetIn(){
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setGateIn' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/billing_npks_pnk/api.php";
			    $method = 'setGateIn'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);

			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '4', SERVICES_RESP_XML = '".$response."'  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
        		$this->db->query($updateServices);
        		echo "ok ".$row['SERVICES_ID']."<br>\n";
			}
		}
    }

	function call_service($url, $method, $params){
		$client = new nusoap_client($url);//alamat web service
		$error = $client->getError();//respon web service error

		if ($error) {
            return "<h2>Constructor error</h2><pre>" . $error . "</pre>";
        }

        if ($client->fault) {//web service client fault
            return "error";
        }else {
            $error = $client->getError();//web service client error
            if ($error) {
                return "<h2>Error</h2><pre>" . $error . "</pre>";
            }else {
                $result = $client->call($method, $params);//respon web service
                return $result;
            }
        }
	}

	function GenerateDelivery(){
		$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '0' AND services_method = 'getDelivery' order by services_req_date desc";
		$resultservices = $this->db->query($sqlservices);
			$totalservice = $resultservices->num_rows();
			if($totalservice > 0){
				foreach($resultservices->result_array() as $row){
					if($row['SERVICES_RESP_XML'] == ""){
						$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
						 $this->db->query($updateServices);
						echo "tidak ada response <br>\n";
						continue;
					}
					$response = $row['SERVICES_RESP_XML']->read(2000000);
					$error = 0;
					$xml = xml2ary($response);
					$valResponse = $xml['document']['_c']['respon']['_v'];
					if($valResponse != 0){
							$loop =  $xml['document']['_c']['loop']['_c']['data'];
							$totalHDR = count($loop);
							$i =0;
							while ($i < $totalHDR) {
									if($totalHDR == 1){
											$header = $loop['_c']['header']['_c'];
											$detail = $loop['_c']['arrdetail']['_c']['detail'];
									}else{
											$header = $loop[$i]['_c']['header']['_c'];
											$detail = $loop[$i]['_c']['arrdetail']['_c']['detail'];
									}

									$REQ_NO = $header['REQ_NO']['_v'];
									$NO_NOTA = $header['NO_NOTA']['_v'];
									$TGL_NOTA = $header['TGL_NOTA']['_v'];
									$REQ_DELIVERY_DATE = $header['REQ_DELIVERY_DATE']['_v'];
									$NM_CONSIGNEE = $header['NM_CONSIGNEE']['_v'];
									$REQ_MARK = $header['REQ_MARK']['_v'];
									$NPWP =  str_replace(".", "",str_replace("-", "", trim($header['NPWP']['_v'])));
									$ALAMAT = $header['ALAMAT']['_v'];
									$TANGGAL_LUNAS = $header['TANGGAL_LUNAS']['_v'];

									$sqlcek = "SELECT REQ_NO FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO='".$REQ_NO."'";
									$resultCek = $this->db->query($sqlcek);
									$totalcek = $resultCek->num_rows();

									if($totalcek <=0){
											$sqlceknpwp = "SELECT CONSIGNEE_ID FROM TM_CONSIGNEE WHERE CONSIGNEE_NPWP='".$NPWP."'";
											$resultCeknpwp = $this->db->query($sqlceknpwp);
											$totalceknpwp = $resultCeknpwp->num_rows();

											if($totalceknpwp <=0){
													$qlIDCONSIGNEE = "SELECT SEQ_REQ_DELIVERY_HDR.NEXTVAL AS ID FROM DUAL";
													$resultIDCONSIGNEE = $this->db->query($qlIDCONSIGNEE)->result_array();
													$CONSIGNE_ID = $resultIDCONSIGNEE[0]['ID'];

													$insertConsignee = "INSERT INTO TM_CONSIGNEE (CONSIGNEE_ID, CONSIGNEE_NAME, CONSIGNEE_ADDRESS, CONSIGNEE_NPWP) VALUES (".$CONSIGNE_ID.",'".$NM_CONSIGNEE."','".$ALAMAT."','".$NPWP."')";
													$this->db->query($insertConsignee);
											}else{
													$resultnpwp = $resultCeknpwp->result_array();
													$CONSIGNE_ID = $resultnpwp[0]['CONSIGNEE_ID'];
											}

											$qlID = "SELECT SEQ_REQ_DELIVERY_HDR.NEXTVAL AS ID FROM DUAL";
											$resultID = $this->db->query($qlID)->result_array();
											$IDheader = $resultID[0]['ID'];

											$insertHDR = "INSERT INTO TX_REQ_DELIVERY_HDR (REQ_ID, REQ_NO, REQ_CONSIGNEE_ID, REQ_BRANCH_ID, REQ_MARK, REQ_DELIVERY_DATE, REQUEST_NOTA_DATE, REQUEST_PAID_DATE) VALUES
																	 (".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",3,'".$REQ_MARK."', TO_DATE('".$REQ_DELIVERY_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS') )";
											$resultHDR = $this->db->query($insertHDR);

											if($resultHDR){
													echo $i.", sukses | ".$REQ_NO."<br>\n";
													$totalDTL = count($detail);
													$a = 0;
													while($a < $totalDTL){
															if($totalDTL == 1){
																	$detailroot = $detail['_c'];
															}else{
																	$detailroot = $detail[$a]['_c'];
															}
															$REQ_DTL_CONT = trim($detailroot['REQ_DTL_CONT']['_v']);
															$REQ_DTL_CONT_STATUS = trim($detailroot['REQ_DTL_CONT_STATUS']['_v']);
															$REQ_DTL_COMMODITY = trim($detailroot['REQ_DTL_COMMODITY']['_v']);
															$REQ_DTL_VIA = trim($detailroot['REQ_DTL_VIA']['_v']);
															$REQ_DTL_CONT_HAZARD = trim($detailroot['REQ_DTL_CONT_HAZARD']['_v']);
															$REQ_DTL_SIZE = trim($detailroot['REQ_DTL_SIZE']['_v']);
															$REQ_DTL_TYPE = trim($detailroot['REQ_DTL_TYPE']['_v']);

															$insertDTL = "INSERT INTO TX_REQ_DELIVERY_DTL (REQ_HDR_ID, REQ_DTL_CONT, REQ_DTL_CONT_STATUS, REQ_DTL_CONT_HAZARD, REQ_DTL_CONT_SIZE, REQ_DTL_CONT_TYPE, REQ_DTL_COMMODITY)
																						VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_STATUS."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."')";
															$resultDtl = $this->db->query($insertDTL);
															if($resultDtl){
																	echo $a.", sukses detil | ".$REQ_DTL_CONT."<br>\n";
															}else{
																	echo $a.", gagal detil | ".$REQ_DTL_CONT."<br>\n";
															}

															$a++;
													}
											}else{
													echo $i.", gagal | ".$REQ_NO."<br>\n";
													$error++;
											}
									}else{
											echo "data sudah ada | ".$REQ_NO."<br>\n";
									}
							$i++;
							}
							$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
						$this->db->query($updateServices);
					}else{
							 $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
						 $this->db->query($updateServices);
						 die("tidak ada data <br>\n");
					}

				}
			}else{
				die("tidak ada data untuk digenerate <br>\n");
			}

	}

	function GenerateStuffing(){
		$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '0' AND services_method = 'getStuffing' order by services_req_date desc";
		$resultservices = $this->db->query($sqlservices);
			$totalservice = $resultservices->num_rows();
			if($totalservice > 0){
				foreach($resultservices->result_array() as $row){
					if($row['SERVICES_RESP_XML'] == ""){
						$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
						 $this->db->query($updateServices);
						echo "tidak ada response <br>\n";
						continue;
					}
					$response = $row['SERVICES_RESP_XML']->read(2000000);
					$error = 0;
					$xml = xml2ary($response);
					$valResponse = $xml['document']['_c']['respon']['_v'];
					if($valResponse != 0){
							$loop =  $xml['document']['_c']['loop']['_c']['data'];
							$totalHDR = count($loop);
							$i =0;
							while ($i < $totalHDR) {
									if($totalHDR == 1){
											$header = $loop['_c']['header']['_c'];
											$detail = $loop['_c']['arrdetail']['_c']['detail'];
									}else{
											$header = $loop[$i]['_c']['header']['_c'];
											$detail = $loop[$i]['_c']['arrdetail']['_c']['detail'];
									}

									$REQ_NO = $header['REQ_NO']['_v'];
									$NO_NOTA = $header['NO_NOTA']['_v'];
									$TGL_NOTA = $header['TGL_NOTA']['_v'];
									$REQ_STUFF_DATE = $header['REQ_STUFF_DATE']['_v'];
									$NM_CONSIGNEE = $header['NM_CONSIGNEE']['_v'];
									$REQ_MARK = $header['REQ_MARK']['_v'];
									$NPWP =  str_replace(".", "",str_replace("-", "", trim($header['NPWP']['_v'])));
									$ALAMAT = $header['ALAMAT']['_v'];
									$TANGGAL_LUNAS = $header['TANGGAL_LUNAS']['_v'];

									$sqlcek = "SELECT STUFF_NO FROM TX_REQ_STUFF_HDR WHERE STUFF_NO='".$REQ_NO."'";
									$resultCek = $this->db->query($sqlcek);
									$totalcek = $resultCek->num_rows();

									if($totalcek <=0){
											$sqlceknpwp = "SELECT CONSIGNEE_ID FROM TM_CONSIGNEE WHERE CONSIGNEE_NPWP='".$NPWP."'";
											$resultCeknpwp = $this->db->query($sqlceknpwp);
											$totalceknpwp = $resultCeknpwp->num_rows();

											if($totalceknpwp <=0){
													$qlIDCONSIGNEE = "SELECT SEQ_REQ_DELIVERY_HDR.NEXTVAL AS ID FROM DUAL";
													$resultIDCONSIGNEE = $this->db->query($qlIDCONSIGNEE)->result_array();
													$CONSIGNE_ID = $resultIDCONSIGNEE[0]['ID'];

													$insertConsignee = "INSERT INTO TM_CONSIGNEE (CONSIGNEE_ID, CONSIGNEE_NAME, CONSIGNEE_ADDRESS, CONSIGNEE_NPWP) VALUES (".$CONSIGNE_ID.",'".$NM_CONSIGNEE."','".$ALAMAT."','".$NPWP."')";
													$this->db->query($insertConsignee);
											}else{
													$resultnpwp = $resultCeknpwp->result_array();
													$CONSIGNE_ID = $resultnpwp[0]['CONSIGNEE_ID'];
											}

											$qlID = "SELECT SEQ_TX_STUFF_HDR.NEXTVAL AS ID FROM DUAL";
											$resultID = $this->db->query($qlID)->result_array();
											$IDheader = $resultID[0]['ID'];

											$insertHDR = "INSERT INTO TX_REQ_STUFF_HDR (STUFF_ID, STUFF_NO, STUFF_CONSIGNEE_ID, STUFF_BRANCH_ID, STUFF_CREATE_DATE, STUFF_NOTA_DATE, STUFF_NOTA_NO, STUFF_PAID_DATE) VALUES
																	 (".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",3, TO_DATE('".$REQ_STUFF_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),'".$NO_NOTA."',TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS') )";
											$resultHDR = $this->db->query($insertHDR);

											if($resultHDR){
													echo $i.", sukses | ".$REQ_NO."<br>\n";
													$totalDTL = count($detail);
													$a = 0;
													while($a < $totalDTL){
															if($totalDTL == 1){
																	$detailroot = $detail['_c'];
															}else{
																	$detailroot = $detail[$a]['_c'];
															}
															$REQ_DTL_CONT = trim($detailroot['REQ_DTL_CONT']['_v']);
															$REQ_DTL_COMMODITY = trim($detailroot['REQ_DTL_COMMODITY']['_v']);
															$REQ_DTL_CONT_HAZARD = trim($detailroot['REQ_DTL_CONT_HAZARD']['_v']);
															$REQ_DTL_SIZE = trim($detailroot['REQ_DTL_SIZE']['_v']);
															$REQ_DTL_TYPE = trim($detailroot['REQ_DTL_TYPE']['_v']);

															$insertDTL = "INSERT INTO TX_REQ_STUFF_DTL (STUFF_DTL_HDR_ID, STUFF_DTL_CONT, STUFF_DTL_CONT_HAZARD, STUFF_DTL_CONT_SIZE, STUFF_DTL_CONT_TYPE, STUFF_DTL_COMMODITY)
																						VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."')";
															$resultDtl = $this->db->query($insertDTL);
															if($resultDtl){
																	echo $a.", sukses detil | ".$REQ_DTL_CONT."<br>\n";
															}else{
																	echo $a.", gagal detil | ".$REQ_DTL_CONT."<br>\n";
															}

															$a++;
													}
											}else{
													echo $i.", gagal | ".$REQ_NO."<br>\n";
													$error++;
											}
									}else{
											echo "data sudah ada | ".$REQ_NO."<br>\n";
									}
							$i++;
							}
							$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
						$this->db->query($updateServices);
					}else{
							 $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
						 $this->db->query($updateServices);
						 die("tidak ada data <br>\n");
					}

				}
			}else{
				die("tidak ada data untuk digenerate <br>\n");
			}

	}

	function GenerateStripping(){
	  $sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '0' AND services_method = 'getStripping' order by services_req_date desc";
	  $resultservices = $this->db->query($sqlservices);
	    $totalservice = $resultservices->num_rows();
	    if($totalservice > 0){
	      foreach($resultservices->result_array() as $row){
	        if($row['SERVICES_RESP_XML'] == ""){
	          $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
	           $this->db->query($updateServices);
	          echo "tidak ada response <br>\n";
	          continue;
	        }
	        $response = $row['SERVICES_RESP_XML']->read(2000000);
	        $error = 0;
	        $xml = xml2ary($response);
	        $valResponse = $xml['document']['_c']['respon']['_v'];
	        if($valResponse != 0){
	            $loop =  $xml['document']['_c']['loop']['_c']['data'];
	            $totalHDR = count($loop);
	            $i =0;
	            while ($i < $totalHDR) {
	                if($totalHDR == 1){
	                    $header = $loop['_c']['header']['_c'];
	                    $detail = $loop['_c']['arrdetail']['_c']['detail'];
	                }else{
	                    $header = $loop[$i]['_c']['header']['_c'];
	                    $detail = $loop[$i]['_c']['arrdetail']['_c']['detail'];
	                }

	                $REQ_NO = $header['REQ_NO']['_v'];
	                $NO_NOTA = $header['NO_NOTA']['_v'];
	                $TGL_NOTA = $header['TGL_NOTA']['_v'];
	                $REQ_STRIP_DATE = $header['REQ_STRIP_DATE']['_v'];
	                $NM_CONSIGNEE = $header['NM_CONSIGNEE']['_v'];
	                $REQ_MARK = $header['REQ_MARK']['_v'];
									$DO = $header['DO']['_v'];
									$BL = $header['BL']['_v'];
	                $NPWP =  str_replace(".", "",str_replace("-", "", trim($header['NPWP']['_v'])));
	                $ALAMAT = $header['ALAMAT']['_v'];
	                $TANGGAL_LUNAS = $header['TANGGAL_LUNAS']['_v'];

	                $sqlcek = "SELECT STRIP_NO FROM TX_REQ_STRIP_HDR WHERE STRIP_NO='".$REQ_NO."'";
	                $resultCek = $this->db->query($sqlcek);
	                $totalcek = $resultCek->num_rows();

	                if($totalcek <=0){
	                    $sqlceknpwp = "SELECT CONSIGNEE_ID FROM TM_CONSIGNEE WHERE CONSIGNEE_NPWP='".$NPWP."'";
	                    $resultCeknpwp = $this->db->query($sqlceknpwp);
	                    $totalceknpwp = $resultCeknpwp->num_rows();

	                    if($totalceknpwp <=0){
	                        $qlIDCONSIGNEE = "SELECT SEQ_REQ_DELIVERY_HDR.NEXTVAL AS ID FROM DUAL";
	                        $resultIDCONSIGNEE = $this->db->query($qlIDCONSIGNEE)->result_array();
	                        $CONSIGNE_ID = $resultIDCONSIGNEE[0]['ID'];

	                        $insertConsignee = "INSERT INTO TM_CONSIGNEE (CONSIGNEE_ID, CONSIGNEE_NAME, CONSIGNEE_ADDRESS, CONSIGNEE_NPWP) VALUES (".$CONSIGNE_ID.",'".$NM_CONSIGNEE."','".$ALAMAT."','".$NPWP."')";
	                        $this->db->query($insertConsignee);
	                    }else{
	                        $resultnpwp = $resultCeknpwp->result_array();
	                        $CONSIGNE_ID = $resultnpwp[0]['CONSIGNEE_ID'];
	                    }

	                    $qlID = "SELECT SEQ_REQ_STRIP_HDR.NEXTVAL AS ID FROM DUAL";
	                    $resultID = $this->db->query($qlID)->result_array();
	                    $IDheader = $resultID[0]['ID'];

	                    $insertHDR = "INSERT INTO TX_REQ_STRIP_HDR (STRIP_ID, STRIP_NO, STRIP_CONSIGNEE_ID, STRIP_BRANCH_ID, STRIP_DO, STRIP_BL, STRIP_CREATE_DATE, STRIP_NOTA_DATE, STRIP_NOTA_NO, STRIP_PAID_DATE) VALUES
	                                 (".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",3,'".$DO."','".$BL."', TO_DATE('".$REQ_STRIP_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),'".$NO_NOTA."',TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS') )";
	                    $resultHDR = $this->db->query($insertHDR);

	                    if($resultHDR){
	                        echo $i.", sukses | ".$REQ_NO."<br>\n";
	                        $totalDTL = count($detail);
	                        $a = 0;
	                        while($a < $totalDTL){
	                            if($totalDTL == 1){
	                                $detailroot = $detail['_c'];
	                            }else{
	                                $detailroot = $detail[$a]['_c'];
	                            }
	                            $REQ_DTL_CONT = trim($detailroot['REQ_DTL_CONT']['_v']);
	                            $REQ_DTL_COMMODITY = trim($detailroot['REQ_DTL_COMMODITY']['_v']);
	                            $REQ_DTL_CONT_HAZARD = trim($detailroot['REQ_DTL_CONT_HAZARD']['_v']);
	                            $REQ_DTL_SIZE = trim($detailroot['REQ_DTL_SIZE']['_v']);
	                            $REQ_DTL_TYPE = trim($detailroot['REQ_DTL_TYPE']['_v']);

	                            $insertDTL = "INSERT INTO TX_REQ_STRIP_DTL (STRIP_DTL_HDR_ID, STRIP_DTL_CONT, STRIP_DTL_DANGER, STRIP_DTL_CONT_SIZE, STRIP_DTL_CONT_TYPE, STRIP_DTL_COMMODITY)
	                                          VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."')";
	                            $resultDtl = $this->db->query($insertDTL);
	                            if($resultDtl){
	                                echo $a.", sukses detil | ".$REQ_DTL_CONT."<br>\n";
	                            }else{
	                                echo $a.", gagal detil | ".$REQ_DTL_CONT."<br>\n";
	                            }

	                            $a++;
	                        }
	                    }else{
	                        echo $i.", gagal | ".$REQ_NO."<br>\n";
	                        $error++;
	                    }
	                }else{
	                    echo "data sudah ada | ".$REQ_NO."<br>\n";
	                }
	            $i++;
	            }
	            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
	          $this->db->query($updateServices);
	        }else{
	             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
	           $this->db->query($updateServices);
	           die("tidak ada data <br>\n");
	        }

	      }
	    }else{
	      die("tidak ada data untuk digenerate <br>\n");
	    }

	}


}
