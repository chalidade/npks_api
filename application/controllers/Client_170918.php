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
		echo $_SERVER['SERVER_NAME'];die();
		//show_404();
		echo date('Y-m-d H:i:s');
		die();
	}
	
	function getServer(){
		echo SERVICE_SERVER;
	}

	function getDelivery(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
        $method = 'getDelivery'; //method

        $sqlLastDateNota = "select TGL from (
                            select TO_CHAR(REQUEST_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_DELIVERY_HDR  WHERE REQUEST_PAID_DATE IS NOT NULL order by REQUEST_PAID_DATE DESC
                            )A where rownum =1";

        $resultLastDateNota = $this->db->query($sqlLastDateNota);
        $totalservice = $resultLastDateNota->num_rows();        

        if($totalservice <= 0){
        	$lastDateNota = date('YmdHis',strtotime("-1 hours"));         	   
        }else{
        	$resultLastDateNota = $resultLastDateNota->result_array();
        	$lastDateNota = $resultLastDateNota[0]['TGL'];
        }

        $params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);
				
        $xml = xml2ary($response);			               
        $valResponse = $xml['document']['_c']['respon']['_v'];		        
        if($valResponse != 0){
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."','".$lastDateNota."','".$response."','1')";
	        $this->db->query($insertServices);
	        echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
		}else{
        	echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')."<br>\n";			
		}        
	}

	function ParsingDelivery(){
		$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getDelivery' order by services_req_date desc";
		$resultservices = $this->db->query($sqlservices);
			$totalservice = $resultservices->num_rows();
			if($totalservice > 0){
				foreach($resultservices->result_array() as $row){
					if($row['SERVICES_RESP_XML'] == ""){
						$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";
						 $this->db->query($updateServices);
						echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
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
									$DELIVERY_KE = $header['DELIVERY_KE']['_v'];

									$PERP_DARI = $header['PERP_DARI']['_v'];
	                				$PERP_KE = $header['PERP_KE']['_v'];

	                				if($PERP_KE == ""){	                					
	                					$PERP_KE = 0;
	                				}

									if($DELIVERY_KE == 'LUAR')
										$DELIVERY_KE = 'DEPO';

									$sqlcek = "SELECT REQ_NO FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO='".$REQ_NO."'";
									$resultCek = $this->db->query($sqlcek);
									$totalcek = $resultCek->num_rows();

									if($totalcek <=0){
											$sqlceknpwp = "SELECT CONSIGNEE_ID FROM TM_CONSIGNEE WHERE CONSIGNEE_NPWP='".$NPWP."'";
											$resultCeknpwp = $this->db->query($sqlceknpwp);
											$totalceknpwp = $resultCeknpwp->num_rows();

											if($totalceknpwp <=0){
													$qlIDCONSIGNEE = "SELECT SEQ_CONSIGNEE_ID.NEXTVAL AS ID FROM DUAL";
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

											$insertHDR = "INSERT INTO TX_REQ_DELIVERY_HDR (REQ_ID, REQ_NO, REQ_CONSIGNEE_ID, REQ_BRANCH_ID, REQ_MARK, REQ_DELIVERY_DATE, REQUEST_NOTA_DATE, REQUEST_PAID_DATE, REQUEST_TO, REQUEST_STATUS, REQUEST_EXTEND_FROM,REQUEST_EXTEND_LOOP) VALUES
																	 (".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",3,'".$REQ_MARK."', TO_DATE('".$REQ_DELIVERY_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'),'".$DELIVERY_KE."', '1','".$PERP_DARI."',".$PERP_KE.")";
											$resultHDR = $this->db->query($insertHDR);

											if($resultHDR){
													
													if($DELIVERY_KE == 'TPK' && $PERP_DARI ==''){
														$ServiceID = date('YmdHis').rand(100,999);	        
												        $method = 'getGateOutToTPK'; //method
												        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS) 
												        					VALUES (".$ServiceID.",'".$method."','".$REQ_NO."','0')";
												        $insert = $this->db->query($insertServices);
													}

													echo $i.", sukses | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
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
															$REQ_DTL_DEL_DATE = trim($detailroot['REQ_DTL_DEL_DATE']['_v']);

															$insertDTL = "INSERT INTO TX_REQ_DELIVERY_DTL (REQ_HDR_ID, REQ_DTL_CONT, REQ_DTL_CONT_STATUS, REQ_DTL_CONT_HAZARD, REQ_DTL_CONT_SIZE, REQ_DTL_CONT_TYPE, REQ_DTL_COMMODITY, REQ_DTL_STATUS, REQ_DTL_DEL_DATE)
																						VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_STATUS."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."', '1',TO_DATE('".$REQ_DTL_DEL_DATE."','MM/DD/YYYY HH24:MI:SS'))";
															$resultDtl = $this->db->query($insertDTL);
															if($resultDtl){
																	echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
															}else{
																	echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
															}

															if($PERP_DARI !=""){
									                    		$updateDeliveryTDL = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_ACTIVE = 'T', REQ_DTL_STATUS = '2' WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = '3') AND REQ_DTL_CONT = '".$REQ_DTL_CONT."'  ";
									          					$this->db->query($updateDeliveryTDL);

																$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = 3)")->row()->JML;
																$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = 3) AND REQ_DTL_ACTIVE = 'T' ")->row()->JML;

																if($cek_tot_dtl == $cek_tot_dtl_T){
																	$updateStuffHDR = "UPDATE TX_REQ_DELIVERY_HDR SET REQUEST_STATUS = '2' WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = '3' ";
	          														$this->db->query($updateStuffHDR);
																}															
									                    	}

															$a++;
													}
											}else{
													echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
													$error++;
											}
									}else{
											echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
									}
							$i++;
							}
							$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
						$this->db->query($updateServices);
					}else{
							 $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
						 $this->db->query($updateServices);
						 die("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
					}

				}
			}else{
				die("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')."<br>\n");
			}
	}

	function getReceiving(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
        $method = 'getReceiving'; //method

        $sqlLastDateNota = "select TGL from (
                            select TO_CHAR(REQUEST_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_RECEIVING_HDR  WHERE REQUEST_PAID_DATE IS NOT NULL order by REQUEST_PAID_DATE DESC
                            )A where rownum =1";
        $resultLastDateNota = $this->db->query($sqlLastDateNota);
        $totalservice = $resultLastDateNota->num_rows();        

        if($totalservice <= 0){
        	$lastDateNota = date('YmdHis',strtotime("-1 hours"));         	   
        }else{
        	$resultLastDateNota = $resultLastDateNota->result_array();
        	$lastDateNota = $resultLastDateNota[0]['TGL'];
        }
        $params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);
        
        $xml = xml2ary($response);			               
        $valResponse = $xml['document']['_c']['respon']['_v'];		        
        if($valResponse != 0){
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."','".$lastDateNota."','".$response."','1')";
	        $this->db->query($insertServices);
	        echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
		}else{
        	echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')."<br>\n";			
		}        
	}

	function ParsingReceiving(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getReceiving' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
        		if($row['SERVICES_RESP_XML'] == ""){        			
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";        					
        			 $this->db->query($updateServices);
        			echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
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
		                $RECEIVING_DARI = $header['RECEIVING_DARI']['_v'];
		                $DI = $header['DI']['_v'];

						if((strtolower($DI) == 'domestik' or strtolower($DI) == 'd')){
							$DI = 'D';
						}else{
							$DI = 'I';
						}

						if($RECEIVING_DARI == 'LUAR')
							$RECEIVING_DARI = 'DEPO';

		                $sqlcek = "SELECT REQUEST_NO FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO='".$REQ_NO."'";
		                $resultCek = $this->db->query($sqlcek);
		                $totalcek = $resultCek->num_rows();
		                
		                if($totalcek <=0){
		                    $sqlceknpwp = "SELECT CONSIGNEE_ID FROM TM_CONSIGNEE WHERE CONSIGNEE_NPWP='".$NPWP."'";
		                    $resultCeknpwp = $this->db->query($sqlceknpwp);
		                    $totalceknpwp = $resultCeknpwp->num_rows();
		                    
		                    if($totalceknpwp <=0){
		                        $qlIDCONSIGNEE = "SELECT SEQ_CONSIGNEE_ID.NEXTVAL AS ID FROM DUAL";
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

		                    $insertHDR = "INSERT INTO TX_REQ_RECEIVING_HDR (REQUEST_ID, REQUEST_NO, REQUEST_CONSIGNEE_ID, REQUEST_BRANCH_ID, REQUEST_NOTA, REQUEST_MARK, REQUEST_RECEIVING_DATE, REQUEST_NOTA_DATE, REQUEST_PAID_DATE, REQUEST_FROM, REQUEST_STATUS, REQUEST_DI) VALUES 
		                                 (".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",3,'".$NO_NOTA."','".$REQ_MARK."', TO_DATE('".$REQ_RECEIVING_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'), '".$RECEIVING_DARI."','1','".$DI."')";                    
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
		                            $REQUEST_DTL_OWNER_CODE = trim($detailroot['REQ_DTL_OWNER_CODE']['_v']);
		                            $REQUEST_DTL_OWNER_NAME = trim($detailroot['REQ_DTL_OWNER_NAME']['_v']);

		                            $insertDTL = "INSERT INTO TX_REQ_RECEIVING_DTL (REQUEST_DTL_ID, REQUEST_HDR_ID, REQUEST_DTL_CONT, REQUEST_DTL_CONT_STATUS, REQUEST_DTL_DANGER, REQUEST_DTL_CONT_SIZE, REQUEST_DTL_CONT_TYPE, REQUEST_DTL_COMMODITY,REQUEST_DTL_OWNER_CODE, REQUEST_DTL_OWNER_NAME) 
		                                          VALUES (SEQ_REQ_RECEIVING_DTL.NEXTVAL, ".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_STATUS."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."', '".$REQUEST_DTL_OWNER_CODE."','".$REQUEST_DTL_OWNER_NAME."')";                            
		                            $resultDtl = $this->db->query($insertDTL);
		                            if($resultDtl){
		                                echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
		                            }else{
		                                echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
		                            }
		                            //cek owner
		                            $sqlcekowner = "SELECT OWNER_CODE FROM TM_OWNER WHERE OWNER_CODE='".$REQUEST_DTL_OWNER_CODE."' AND OWNER_BRANCH_ID = 3 ";		                            		                            
				                    $resultCekowner = $this->db->query($sqlcekowner);
				                    $totalcekowner = $resultCekowner->num_rows();				                    
				                    if($totalcekowner <=0){
				                        $insertOwner = "INSERT INTO TM_OWNER (OWNER_CODE, OWNER_NAME, OWNER_BRANCH_ID) VALUES ('".$REQUEST_DTL_OWNER_CODE."','".$REQUEST_DTL_OWNER_NAME."',3)";				                        
				                        $this->db->query($insertOwner);
				                    }

				                    //insert history container
									$this->db->query("CALL ADD_HISTORY_CONTAINER(
												'".$REQ_DTL_CONT."', 
												'".$REQ_NO."',
												'".$REQ_RECEIVING_DATE."',
												'".$REQ_DTL_SIZE."',
												'".$REQ_DTL_TYPE."',
												NULL,
												NULL,
												NULL,
												NULL,
												NULL,
												NULL,
												3,
												'Receiving',
												NULL,
												3)");
		                            $a++;
		                        }
		                    }else{
		                        echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
		                        $error++;
		                    }
		                }else{
		                    echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
		                }
		            $i++;
		            }
		            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
        			$this->db->query($updateServices);
		        }else{		            
		             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
        			 $this->db->query($updateServices);
        			 die("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
		        }

       	 	}
        }else{
        	die("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')."<br>\n");
        }
    }

	function getStuffing(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
		$method = 'getStuffing'; //method

		$sqlLastDateNota = "select TGL from (
							select TO_CHAR(STUFF_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_STUFF_HDR WHERE STUFF_PAID_DATE IS NOT NULL order by STUFF_PAID_DATE DESC
							)A where rownum =1";
        $resultLastDateNota = $this->db->query($sqlLastDateNota);
        $totalservice = $resultLastDateNota->num_rows();        

        if($totalservice <= 0){
        	$lastDateNota = date('YmdHis',strtotime("-1 hours"));         	   
        }else{
        	$resultLastDateNota = $resultLastDateNota->result_array();
        	$lastDateNota = $resultLastDateNota[0]['TGL'];
        }

		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

		$ServiceID = date('YmdHis').rand(100,999);
		$response = $this->call_service($client, $method,$params);

		$xml = xml2ary($response);			               
        $valResponse = $xml['document']['_c']['respon']['_v'];		        
        if($valResponse != 0){        	
			$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
								VALUES (".$ServiceID.",'".$method."','".$lastDateNota."','".$response."','1')";
			$this->db->query($insertServices);
			echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
		}else{
			echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')."<br>\n";
		}
	}

	function getReceivingFromTPK(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
		$method = 'getReceivingFromTPK'; 

		$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML, TO_CHAR(services_req_date,'MM/DD/YYYY HH24:MI:SS') TGL_SERVICE from TX_SERVICES where services_status = '0' AND services_method = 'getReceivingFromTPK' order by services_req_date desc";
		$resultservices = $this->db->query($sqlservices);
		$totalservice = $resultservices->num_rows();
		if($totalservice > 0){
			foreach($resultservices->result_array() as $row){
        		$noReqReciving = $row['SERVICES_REQ_XML']->read(2000000);

        		$createDate = $row['TGL_SERVICE'];
        		$date1=date_create($createDate);            
			    $date2=date_create(date('m/d/Y H:i:s'));			    
			    $diff=date_diff($date1,$date2);
			    
			    $hari = (int)$diff->format("%a");
			    //$jam = (int)$diff->format("%h"); 

			    if(($hari > 5)){
			    	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '4', SERVICES_RESP_XML ='Tidak ada respon selama 3 hari' WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
			    	$this->db->query($updateServices);					
					echo "Tidak Ada response | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
					continue;
			    } 
        		
        		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$noReqReciving);
				$ServiceID = date('YmdHis').rand(100,999);
				$response = $this->call_service($client, $method,$params);				
				$xml = xml2ary($response);	
				
		        $valResponse = $xml['document']['_c']['respon']['_v'];		        
		        if($valResponse != 0){ 					
					$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML ='".$response."' WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
					 $this->db->query($updateServices);					
					echo "Sukses Update | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
				}else{
					echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')."<br>\n";
				}
			}			
		}else{
			echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
		}
	}

	
	function getGateInFromTPK(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
		$method = 'getGateInFromTPK'; 

		$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML,  TO_CHAR(services_req_date,'MM/DD/YYYY HH24:MI:SS') TGL_SERVICE from TX_SERVICES where services_status = '0' AND services_method = 'getGateInFromTPK' order by services_req_date desc";
		$resultservices = $this->db->query($sqlservices);
		$totalservice = $resultservices->num_rows();
		if($totalservice > 0){
			foreach($resultservices->result_array() as $row){
        		$noReqReciving = $row['SERVICES_REQ_XML']->read(2000000);

        		$createDate = $row['TGL_SERVICE'];
        		$date1=date_create($createDate);            
			    $date2=date_create(date('m/d/Y H:i:s'));			    
			    $diff=date_diff($date1,$date2);
			    
			    $hari = (int)$diff->format("%a");
			    //$jam = (int)$diff->format("%h"); 

			    if(($hari > 5)){
			    	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '4', SERVICES_RESP_XML ='Tidak ada respon selama 3 hari' WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
			    	$this->db->query($updateServices);					
					echo "Tidak Ada response | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
					continue;
			    } 
        		
        		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$noReqReciving);
				$ServiceID = date('YmdHis').rand(100,999);
				$response = $this->call_service($client, $method,$params);				
				$xml = xml2ary($response);	
				
		        $valResponse = $xml['document']['_c']['respon']['_v'];		        
		        if($valResponse != 0){ 					
					$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML ='".$response."' WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
					 $this->db->query($updateServices);					
					echo "Sukses Update | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
				}else{
					echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')."<br>\n";
				}
			}			
		}else{
			echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
		}
	}

	function getGateOutToTPK(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
		$method = 'getGateOutToTPK'; 

		$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML, TO_CHAR(services_req_date,'MM/DD/YYYY HH24:MI:SS') TGL_SERVICE from TX_SERVICES where services_status = '0' AND services_method = 'getGateOutToTPK' order by services_req_date desc";
		$resultservices = $this->db->query($sqlservices);
		$totalservice = $resultservices->num_rows();
		if($totalservice > 0){
			foreach($resultservices->result_array() as $row){
        		$noReqReciving = $row['SERVICES_REQ_XML']->read(2000000);

        		$createDate = $row['TGL_SERVICE'];
        		$date1=date_create($createDate);            
			    $date2=date_create(date('m/d/Y H:i:s'));			    
			    $diff=date_diff($date1,$date2);
			    
			    $hari = (int)$diff->format("%a");
			    //$jam = (int)$diff->format("%h"); 

			    if(($hari > 5)){
			    	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '4', SERVICES_RESP_XML ='Tidak ada respon selama 3 hari' WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
			    	$this->db->query($updateServices);					
					echo "Tidak Ada response | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
					continue;
			    }
        		
        		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$noReqReciving);
				$ServiceID = date('YmdHis').rand(100,999);
				$response = $this->call_service($client, $method,$params);				
				$xml = xml2ary($response);	
				
		        $valResponse = $xml['document']['_c']['respon']['_v'];		        
		        if($valResponse != 0){ 					
					$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML ='".$response."' WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
					 $this->db->query($updateServices);					
					echo "Sukses Update | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
				}else{
					echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')."<br>\n";
				}
			}			
		}else{
			echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
		}
	}

	function ParsingGateOutToTPK(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getGateOutToTPK' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
        		if($row['SERVICES_RESP_XML'] == ""){        			
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";        					
        			 $this->db->query($updateServices);
        			echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
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
		                }else{
		                    $header = $loop[$i]['_c']['header']['_c'];
		                }

		                $REQ_NO = $header['REQ_NO']['_v'];
		                $NO_CONT = $header['NO_CONT']['_v'];		               
		                $TGL_GATE = $header['TGL_GATE']['_v'];
		                $STATUS_CONT = $header['STATUS_CONT']['_v'];
		                $TRUK = $header['TRUK']['_v'];		                
		                $DELIVERY_TO = $header['DELIVERY_TO']['_v'];
		                $KEGIATAN = $header['KEGIATAN']['_v'];		                

		                $ACTIVITY = '4';
		                	                		                
	                    /*$qlIDGATE = "SELECT SEQ_TX_GATE.NEXTVAL AS ID FROM DUAL";
				        $resultIDGATE = $this->db->query($qlIDGATE)->result_array();
				        $GATE_ID = $resultIDGATE[0]['ID'];*/

	                    $insertGATEIN = "INSERT INTO TX_GATE (GATE_NOREQ, GATE_CONT,GATE_CONT_STATUS, GATE_TRUCK_NO,  GATE_ORIGIN, GATE_CREATE_DATE, GATE_STATUS, GATE_ACTIVITY, GATE_STACK_STATUS, GATE_FL_SEND, GATE_BRANCH_ID) VALUES 
	                                 ('".$REQ_NO."','".$NO_CONT."','".$STATUS_CONT."','".$TRUK."','".$DELIVERY_TO."', TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS'),'1', '".$ACTIVITY."','1', '9', 3)";                   
	                    $resultHDR = $this->db->query($insertGATEIN);  

	                    /*$qlIDGATE = "SELECT SEQ_TX_GATE.NEXTVAL AS ID FROM DUAL";
				        $resultIDGATE = $this->db->query($qlIDGATE)->result_array();
				        $GATE_ID = $resultIDGATE[0]['ID'];*/

	                     $insertGATEOUT = "INSERT INTO TX_GATE (GATE_NOREQ, GATE_CONT,GATE_CONT_STATUS, GATE_TRUCK_NO,  GATE_ORIGIN, GATE_CREATE_DATE, GATE_STATUS, GATE_ACTIVITY, GATE_STACK_STATUS, GATE_FL_SEND, GATE_BRANCH_ID) VALUES 
	                                 ('".$REQ_NO."','".$NO_CONT."','".$STATUS_CONT."','".$TRUK."','".$DELIVERY_TO."', TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS'),'3', '".$ACTIVITY."','2', '9', 3)";                   
	                    $resultHDR = $this->db->query($insertGATEOUT); 

	                    //update request delivery hdr
	                	$updateReqDelHDR = "UPDATE TX_REQ_DELIVERY_HDR SET REQUEST_STATUS = '2' WHERE REQ_NO = '".$REQ_NO."' AND REQ_BRANCH_ID = '3' ";        					
        				$this->db->query($updateReqDelHDR);  

        				//get ID hdr Delivery
        				$sqlIDHDR = "SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR  WHERE REQ_NO = '".$REQ_NO."' AND REQ_BRANCH_ID = '3'";
				        $resultIDHDR = $this->db->query($sqlIDHDR)->result_array();
				        $HDR_ID = $resultIDHDR[0]['REQ_ID'];

				        //update request delivery dtl
				        $updateReqDelDTL = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_STATUS = '2' WHERE REQ_HDR_ID = '".$HDR_ID."'  ";        					
        				$this->db->query($updateReqDelDTL);  

        				//Update yard menjadi unstacking
        				$result = $this->db->query("INSERT INTO  TX_REAL_YARD (REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, REAL_YARD_CREATE_BY,REAL_YARD_CREATE_DATE,REAL_YARD_TYPE, REAL_YARD_STATUS, REAL_YARD_REQ_NO,REAL_YARD_FL_SEND, REAL_YARD_CONT_STATUS,REAL_YARD_MARK, REAL_YARD_CONT_SIZE,REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY,REAL_YARD_ACTIVITY)
        					(SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT,REAL_YARD_NO,REAL_YARD_CREATE_BY, TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS') REAL_YARD_CREATE_DATE, REAL_YARD_TYPE, '2' REAL_YARD_STATUS, '".$REQ_NO."' REAL_YARD_REQ_NO, '9' REAL_YARD_FL_SEND, REAL_YARD_CONT_STATUS, REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY, REAL_YARD_ACTIVITY FROM TX_REAL_YARD WHERE REAL_YARD_ID = (SELECT MAX(REAL_YARD_ID) FROM TX_REAL_YARD WHERE REAL_YARD_CONT = '".$NO_CONT."' AND REAL_YARD_BRANCH_ID = 3))");
        				
		            $i++;
		            }

		            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
        			$this->db->query($updateServices);
        			echo "Data Inserted | ".date('Y-m-d H:i:s')."<br>\n";
		        }else{		            
		             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
        			 $this->db->query($updateServices);
        			 die("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
		        }
       	 	}
        }else{
        	die("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')."<br>\n");
        }
    }

	function ParsingGateInFromTPK(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getGateInFromTPK' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
        		if($row['SERVICES_RESP_XML'] == ""){        			
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";        					
        			 $this->db->query($updateServices);
        			echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
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
		                }else{
		                    $header = $loop[$i]['_c']['header']['_c'];
		                }

		                $REQ_NO = $header['REQ_NO']['_v'];
		                $NO_CONT = $header['NO_CONT']['_v'];		               
		                $TGL_GATE = $header['TGL_GATE']['_v'];
		                $STATUS_CONT = $header['STATUS_CONT']['_v'];
		                $TRUK = $header['TRUK']['_v'];		                
		                $ORIGIN = $header['ORIGIN']['_v'];
		                $KEGIATAN = $header['KEGIATAN']['_v'];		                
		                $PERALIHAN = $header['PERALIHAN']['_v'];
		                $CONT_SIZE = $header['CONT_SIZE']['_v'];
		                $CONT_TYPE = $header['CONT_TYPE']['_v'];

		                $ACTIVITY = '3';
		                /*if($PERALIHAN == 'STRIPPING'){
		                	$ACTIVITY = '3';
		                }else if($PERALIHAN == 'STUFFING'){
		                	$ACTIVITY = '3';
		                }*/
	                		                
	                    /*$qlIDGATE = "SELECT SEQ_TX_GATE.NEXTVAL AS ID FROM DUAL";
				        $resultIDGATE = $this->db->query($qlIDGATE)->result_array();
				        $GATE_ID = $resultIDGATE[0]['ID'];*/

	                    $insertGATEIN = "INSERT INTO TX_GATE (GATE_NOREQ, GATE_CONT,GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_CONT_STATUS, GATE_TRUCK_NO, GATE_MARK_SERVICES, GATE_ORIGIN, GATE_CREATE_DATE, GATE_STATUS, GATE_ACTIVITY, GATE_STACK_STATUS, GATE_FL_SEND, GATE_BRANCH_ID) VALUES 
	                                 ('".$REQ_NO."','".$NO_CONT."','".$CONT_SIZE."','".$CONT_TYPE."','".$STATUS_CONT."','".$TRUK."','".$PERALIHAN."','".$ORIGIN."', TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS'),'1', '".$ACTIVITY."','0', '9', 3)";                   
	                    $resultHDR = $this->db->query($insertGATEIN);  

	                     $insertGATEOUT = "INSERT INTO TX_GATE (GATE_NOREQ, GATE_CONT,GATE_CONT_SIZE, GATE_CONT_TYPE,GATE_CONT_STATUS, GATE_TRUCK_NO, GATE_MARK_SERVICES, GATE_ORIGIN, GATE_CREATE_DATE, GATE_STATUS, GATE_ACTIVITY, GATE_STACK_STATUS, GATE_FL_SEND, GATE_BRANCH_ID) VALUES 
	                                 ('".$REQ_NO."','".$NO_CONT."','".$CONT_SIZE."','".$CONT_TYPE."','".$STATUS_CONT."','".$TRUK."','".$PERALIHAN."','".$ORIGIN."', TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS'),'3', '".$ACTIVITY."','0', '9', 3)";                   
	                    $resultHDR = $this->db->query($insertGATEOUT); 

	                    //update request receiving hdr
	                	$updateReqDelHDR = "UPDATE TX_REQ_RECEIVING_HDR SET REQUEST_STATUS = '2' WHERE REQUEST_NO = '".$REQ_NO."' AND REQUEST_BRANCH_ID = '3' ";        					
        				$this->db->query($updateReqDelHDR); 

        				//get ID hdr receiving
        				$sqlIDHDR = "SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR  WHERE REQUEST_NO = '".$REQ_NO."' AND REQUEST_BRANCH_ID = '3'";
				        $resultIDHDR = $this->db->query($sqlIDHDR)->result_array();
				        $HDR_ID = $resultIDHDR[0]['REQUEST_ID'];

				         //update request receiving dtl
				        $updateReqDelDTL = "UPDATE TX_REQ_RECEIVING_DTL SET REQUEST_DTL_STATUS = '1' WHERE REQUEST_HDR_ID = '".$HDR_ID."'  ";        					
        				$this->db->query($updateReqDelDTL); 

        				//insert history container
						$this->db->query("CALL ADD_HISTORY_CONTAINER(
									'".$NO_CONT."', 
									'".$REQ_NO."',
									'".$TGL_GATE."',
									'".$CONT_SIZE."',
									'".$CONT_TYPE."',
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									1,
									'GATE IN FROM TPK',
									NULL,
									3)");

		            $i++;
		            }
		            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
        			$this->db->query($updateServices);
        			echo "Data Inserted | ".date('Y-m-d H:i:s')."<br>\n";
		        }else{		            
		             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
        			 $this->db->query($updateServices);
        			 die("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
		        }
       	 	}
        }else{
        	die("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')."<br>\n");
        }
    }

	function ParsingReceivingFromTPK(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getReceivingFromTPK' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
        		if($row['SERVICES_RESP_XML'] == ""){        			
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";        					
        			 $this->db->query($updateServices);
        			echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
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
		               // $TGL_NOTA = $header['TGL_NOTA']['_v'];
		                $REQ_RECEIVING_DATE = $header['REQ_RECEIVING_DATE']['_v'];
		                $NM_CONSIGNEE = $header['NM_CONSIGNEE']['_v'];
		                $REQ_MARK = $header['REQ_MARK']['_v'];
		                $NPWP =  str_replace(".", "",str_replace("-", "", trim($header['NPWP']['_v'])));
		                $ALAMAT = $header['ALAMAT']['_v'];
		               // $TANGGAL_LUNAS = $header['TANGGAL_LUNAS']['_v'];
		                $RECEIVING_DARI = $header['RECEIVING_DARI']['_v'];
		                $PERALIHAN = $header['PERALIHAN']['_v'];

						if($RECEIVING_DARI == 'LUAR')
							$RECEIVING_DARI = 'DEPO';

		                $sqlcek = "SELECT REQUEST_NO FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO='".$REQ_NO."'";
		                $resultCek = $this->db->query($sqlcek);
		                $totalcek = $resultCek->num_rows();
		                
		                if($totalcek <=0){
		                    $sqlceknpwp = "SELECT CONSIGNEE_ID FROM TM_CONSIGNEE WHERE CONSIGNEE_NPWP='".$NPWP."'";
		                    $resultCeknpwp = $this->db->query($sqlceknpwp);
		                    $totalceknpwp = $resultCeknpwp->num_rows();
		                    
		                    if($totalceknpwp <=0){
		                        $qlIDCONSIGNEE = "SELECT SEQ_CONSIGNEE_ID.NEXTVAL AS ID FROM DUAL";
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

		                    $insertHDR = "INSERT INTO TX_REQ_RECEIVING_HDR (REQUEST_ID, REQUEST_NO, REQUEST_CONSIGNEE_ID, REQUEST_BRANCH_ID, REQUEST_NOTA, REQUEST_MARK, REQUEST_RECEIVING_DATE,  REQUEST_FROM, REQUEST_STATUS) VALUES 
		                                 (".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",3,'".$NO_NOTA."','".$PERALIHAN."', TO_DATE('".$REQ_RECEIVING_DATE."','MM/DD/YYYY HH24:MI:SS'),  '".$RECEIVING_DARI."','1')";                    
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
		                                echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
		                            }else{
		                                echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
		                            }

		                            $a++;
		                        }
		                    }else{
		                        echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
		                        $error++;
		                    }
		                }else{
		                    echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
		                }
		            $i++;
		            }
		            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
        			$this->db->query($updateServices);
		        }else{		            
		             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
        			 $this->db->query($updateServices);
        			 die("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
		        }

       	 	}
        }else{
        	die("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')."<br>\n");
        }
    }

	function ParsingStuffing(){		
		
		$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getStuffing' order by services_req_date desc";
		$resultservices = $this->db->query($sqlservices);
			$totalservice = $resultservices->num_rows();
			if($totalservice > 0){
				foreach($resultservices->result_array() as $row){
					if($row['SERVICES_RESP_XML'] == ""){
						$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";
						 $this->db->query($updateServices);
						echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
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
									$NO_BOOKING = $header['NO_BOOKING']['_v'];
									$NO_UKK = $header['NO_UKK']['_v'];
									$NPWP =  str_replace(".", "",str_replace("-", "", trim($header['NPWP']['_v'])));
									$ALAMAT = $header['ALAMAT']['_v'];
									$TANGGAL_LUNAS = $header['TANGGAL_LUNAS']['_v'];
									$NO_REQUEST_RECEIVING = $header['NO_REQUEST_RECEIVING']['_v'];
									$STUFFING_DARI = $header['STUFFING_DARI']['_v'];
									$PERP_DARI = $header['PERP_DARI']['_v'];
	                				$PERP_KE = $header['PERP_KE']['_v'];

	                				if($PERP_KE == ""){	                					
	                					$PERP_KE = 0;
	                				}

									$sqlcek = "SELECT STUFF_NO FROM TX_REQ_STUFF_HDR WHERE STUFF_NO='".$REQ_NO."'";
									$resultCek = $this->db->query($sqlcek);
									$totalcek =  $resultCek->num_rows();

									if($totalcek <=0){
											$sqlceknpwp = "SELECT CONSIGNEE_ID FROM TM_CONSIGNEE WHERE CONSIGNEE_NPWP='".$NPWP."'";
											$resultCeknpwp = $this->db->query($sqlceknpwp);
											$totalceknpwp = $resultCeknpwp->num_rows();

											if($totalceknpwp <=0){
													$qlIDCONSIGNEE = "SELECT SEQ_CONSIGNEE_ID.NEXTVAL AS ID FROM DUAL";
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

											$STUFF_ORIGIN = 'INTERNAL';
											if($STUFFING_DARI == 'TPK')
												$STUFF_ORIGIN = 'TPK';

											$insertHDR = "INSERT INTO TX_REQ_STUFF_HDR (STUFF_ID, STUFF_NO, STUFF_CONSIGNEE_ID, STUFF_BRANCH_ID, STUFF_CREATE_DATE, STUFF_NOTA_DATE, STUFF_NOTA_NO, STUFF_PAID_DATE, STUFF_NO_BOOKING, STUFF_NO_UKK, STUFF_NOREQ_RECEIVING, STUFF_EXTEND_FROM, STUFF_EXTEND_LOOP, STUFF_ORIGIN, STUFF_STATUS) VALUES
																	 (".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",3, TO_DATE('".$REQ_STUFF_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),'".$NO_NOTA."',TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'), '".$NO_BOOKING."', '".$NO_UKK."','".$NO_REQUEST_RECEIVING."', '".$PERP_DARI."', ".$PERP_KE.",'".$STUFF_ORIGIN."','1')";
											$resultHDR = $this->db->query($insertHDR);

											if($resultHDR){					
													
													//cek detil container ada tidak dari TPK																					
													$contFromTPK = 'T';
													$totalDTL = count($detail);													
													$b = 0;
													$arrTpk = array();
													while($b < $totalDTL){
														if($totalDTL == 1){
																$detailroot = $detail['_c'];
														}else{
																$detailroot = $detail[$b]['_c'];
														}
														$REQ_DTL_ORIGIN = trim($detailroot['REQ_DTL_ORIGIN']['_v']);														
														$arrTpk[] =$REQ_DTL_ORIGIN;
														$b++;
													} 

													if (in_array("TPK", $arrTpk)) {
													    $contFromTPK = 'Y';
													}
													
							                    	if(($STUFFING_DARI == 'TPK') && ($PERP_DARI =='') && ($contFromTPK == 'Y')){
														$ServiceID = date('YmdHis').rand(100,999);	        
												        $method = 'getReceivingFromTPK'; //method
												        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS) 
												        					VALUES (".$ServiceID.",'".$method."','".$NO_REQUEST_RECEIVING."','0')";
												        $insert = $this->db->query($insertServices);												        							                    		
							                    	}

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
															$REQ_DTL_REMARK_SP2 = trim($detailroot['REQ_DTL_REMARK_SP2']['_v']);
															$REQ_DTL_ORIGIN = trim($detailroot['REQ_DTL_ORIGIN']['_v']);
															$STUFF_DTL_START_STUFF_PLAN = trim($detailroot['TGL_MULAI']['_v']);
															$STUFF_DTL_END_STUFF_PLAN = trim($detailroot['TGL_SELESAI']['_v']);

															if(($STUFFING_DARI == 'TPK') && ($PERP_DARI =='') && ($contFromTPK == 'Y')){
																$ServiceID = date('YmdHis').rand(100,999);	        
														        $method = 'getGateInFromTPK'; //method
														        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS) 
														        					VALUES (".$ServiceID.",'".$method."','".$NO_REQUEST_RECEIVING."~".$REQ_DTL_CONT."','0')";
														        $insert = $this->db->query($insertServices);
															}
															
															$insertDTL = "INSERT INTO TX_REQ_STUFF_DTL (STUFF_DTL_HDR_ID, STUFF_DTL_CONT, STUFF_DTL_CONT_HAZARD, STUFF_DTL_CONT_SIZE, STUFF_DTL_CONT_TYPE, STUFF_DTL_COMMODITY, STUFF_DTL_REMARK_SP2, STUFF_DTL_ORIGIN,STUFF_DTL_START_STUFF_PLAN,STUFF_DTL_END_STUFF_PLAN)
																						VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."','".$REQ_DTL_REMARK_SP2."','".$REQ_DTL_ORIGIN."', TO_DATE('".$STUFF_DTL_START_STUFF_PLAN."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$STUFF_DTL_END_STUFF_PLAN."','MM/DD/YYYY HH24:MI:SS'))";															
															$resultDtl = $this->db->query($insertDTL);
															if($resultDtl){
																	echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
															}else{
																	echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
															}

															if($PERP_DARI !=""){
									                    		$updateStuffTDL = "UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_ACTIVE = 'T', STUFF_DTL_STATUS = '2' WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = '3') AND STUFF_DTL_CONT = '".$REQ_DTL_CONT."'  ";
									          					$this->db->query($updateStuffTDL);

																$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_HDR_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = 3)")->row()->JML;
																$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_HDR_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = 3) AND STUFF_DTL_ACTIVE = 'T' ")->row()->JML;

																if($cek_tot_dtl == $cek_tot_dtl_T){
																	$updateStuffHDR = "UPDATE TX_REQ_STUFF_HDR SET STUFF_STATUS = '2' WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = '3' ";
	          														$this->db->query($updateStuffHDR);
																}
									                    	}
													//insert history container
													$this->db->query("CALL ADD_HISTORY_CONTAINER(
																'".$REQ_DTL_CONT."', 
																'".$REQ_NO."',
																'".$REQ_STUFF_DATE."',
																'".$REQ_DTL_SIZE."',
																'".$REQ_DTL_TYPE."',
																NULL,
																NULL,
																NULL,
																NULL,
																NULL,
																NULL,
																2,
																'Stuffing',
																NULL,
																3)");

															$a++;
													}
											}else{
													echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
													$error++;
											}
									}else{
											echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
									}
							$i++;
							}
							$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
						$this->db->query($updateServices);
					}else{
							 $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
						$this->db->query($updateServices);
						 die("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
					}
				}
			}else{
				die("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')."<br>\n");
			}
	}

	function generateRealStuffing(){
		/*$sqlgetIn = "SELECT D.REAL_YARD_YBC_ID YBC_ID, A.REAL_STUFF_ID, A.REAL_STUFF_CONT NO_CONT, B.STUFF_NO NO_REQUEST, C.STUFF_DTL_COMMODITY COMMODITY, C.STUFF_DTL_CONT_HAZARD HZ, C.STUFF_DTL_CONT_SIZE CONT_SIZE, 
					C.STUFF_DTL_CONT_TYPE CONT_TYPE, TO_CHAR(B.STUFF_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQUEST, B.STUFF_NO_BOOKING NO_BOOKING, B.STUFF_NO_UKK NO_UKK,
					A.REAL_STUFF_BY ID_USER, TO_CHAR(A.REAL_STUFF_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REALISASI, A.REAL_STUFF_MECHANIC_TOOLS ALAT, C.STUFF_DTL_REMARK_SP2 REMARK_SP2
					FROM TX_REAL_STUFF A 
					INNER JOIN TX_REQ_STUFF_HDR B ON B.STUFF_ID = A.REAL_STUFF_HDR_ID
					INNER JOIN TX_REQ_STUFF_DTL C ON C.STUFF_DTL_HDR_ID = B.STUFF_ID
					INNER JOIN TX_REAL_YARD D ON D.REAL_YARD_CONT = A.REAL_STUFF_CONT AND D.REAL_YARD_STATUS = '1'
					WHERE A.REAL_STUFF_STATUS = '1' AND A.REAL_STUFF_FL_SEND = 0 ORDER BY A.REAL_STUFF_DATE ASC";*/

		$sqlgetIn = "SELECT D.REAL_YARD_YBC_ID YBC_ID, A.REAL_STUFF_ID, A.REAL_STUFF_CONT NO_CONT, B.STUFF_NO NO_REQUEST,
					(SELECT STUFF_DTL_COMMODITY FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) COMMODITY,
					(SELECT STUFF_DTL_CONT_HAZARD FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) HZ,
					(SELECT STUFF_DTL_CONT_SIZE FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) CONT_SIZE,
					(SELECT STUFF_DTL_CONT_TYPE FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) CONT_TYPE,
					TO_CHAR(B.STUFF_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQUEST, B.STUFF_NO_BOOKING NO_BOOKING, B.STUFF_NO_UKK NO_UKK,
					A.REAL_STUFF_BY ID_USER, TO_CHAR(A.REAL_STUFF_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REALISASI, A.REAL_STUFF_MECHANIC_TOOLS ALAT,
					(SELECT STUFF_DTL_REMARK_SP2 FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) REMARK_SP2
					FROM TX_REAL_STUFF A
					INNER JOIN TX_REQ_STUFF_HDR B ON B.STUFF_ID = A.REAL_STUFF_HDR_ID		 
					INNER JOIN TX_REAL_YARD D ON D.REAL_YARD_CONT = A.REAL_STUFF_CONT AND D.REAL_YARD_STATUS = '1'
					WHERE A.REAL_STUFF_STATUS = 2
					AND A.REAL_STUFF_FL_SEND = 0
					AND D.REAL_YARD_ID IN (
											SELECT X.REAL_YARD_ID FROM (
												SELECT MAX(H.REAL_YARD_ID) REAL_YARD_ID FROM TX_REAL_YARD H WHERE H.REAL_YARD_BRANCH_ID = 3 GROUP BY H.REAL_YARD_CONT
											)X INNER JOIN TX_REAL_YARD I ON I.REAL_YARD_ID = X.REAL_YARD_ID WHERE I.REAL_YARD_STATUS = 1
										  )
					ORDER BY A.REAL_STUFF_DATE ASC";

    	$resultservices = $this->db->query($sqlgetIn);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
    		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$IdData = "";
        	foreach($resultservices->result_array() as $row){
        		$IdData .= $row['REAL_STUFF_ID'].",";
        		$returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$row['NO_CONT'].'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$row['NO_REQUEST'].'</NO_REQUEST>';
        		$returnXML .= '<COMMODITY>'.$row['COMMODITY'].'</COMMODITY>';
        		$returnXML .= '<HZ>'.$row['HZ'].'</HZ>';
        		$returnXML .= '<CONT_SIZE>'.$row['CONT_SIZE'].'</CONT_SIZE>';
        		$returnXML .= '<CONT_TYPE>'.$row['CONT_TYPE'].'</CONT_TYPE>';
        		$returnXML .= '<TGL_REQUEST>'.$row['TGL_REQUEST'].'</TGL_REQUEST>';
        		$returnXML .= '<NO_BOOKING>'.$row['NO_BOOKING'].'</NO_BOOKING>';
        		$returnXML .= '<NO_UKK>'.$row['NO_UKK'].'</NO_UKK>';
        		$returnXML .= '<ID_USER>'.$row['ID_USER'].'</ID_USER>';
        		$returnXML .= '<YBC_ID>'.$row['YBC_ID'].'</YBC_ID>';
        		$returnXML .= '<TGL_REALISASI>'.$row['TGL_REALISASI'].'</TGL_REALISASI>';
        		$returnXML .= '<REMARK_SP2>'.$row['REMARK_SP2'].'</REMARK_SP2>';
        		$returnXML .= '<ALAT>'.$row['ALAT'].'</ALAT>';
        		$returnXML .= '</data>';
        	}
        	$returnXML .= '</loop>';			
			$returnXML .= '</document>';
			$IdData = substr($IdData, 0,-1);
	        
	        $ServiceID = date('YmdHis').rand(100,999);	        
	        $method = 'setRealStuffing'; //method
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS) 
	        					VALUES (".$ServiceID.",'".$method."','".$returnXML."','0')";
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxRealStat = "UPDATE TX_REAL_STUFF SET REAL_STUFF_FL_SEND = '1' WHERE REAL_STUFF_ID IN (".$IdData.") AND REAL_STUFF_FL_SEND = '0' ";
        		$this->db->query($updateTxRealStat);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
	        }
        }else{
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
	}

	function setRealStuffing(){
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setRealStuffing' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/billing_npks_pnk/api.php";
			    $method = 'setRealStuffing'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);

			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = '".$response."'  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
        		$this->db->query($updateServices);
        		echo "ok | ".$row['SERVICES_ID']."<br>\n";
			}
		}else{
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
		}
    }

    function ParsingRealStuffing(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'setRealStuffing' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();        
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){

        		if($row['SERVICES_RESP_XML'] == ""){          		
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";        					
        			 $this->db->query($updateServices);
        			echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
        			continue;
        		} 

        		$response = $row['SERVICES_RESP_XML']->read(2000000);        		
		        $error = 0;
		        $xml = xml2ary($response);		        
		        $valResponse = $xml['document']['_c']['respon']['_v'];		        
		        if($valResponse != 0){

		            $loop =  $xml['document']['_c']['loop']['_c']['data'];
		            $totalData = count($loop);		            
		            $i = 0;
		            while ($i < $totalData) {
		                if($totalData == 1){
		                    $data = $loop['_c'];		                    
		                }else{
		                    $data = $loop[$i]['_c'];
		                }

		                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
		                $NO_REQUEST = $data['NO_REQUEST']['_v'];
		                $KET = $data['KET']['_v'];

		            	$updateTxGate = "UPDATE TX_REAL_STUFF SET REAL_STUFF_FL_SEND = '9', REAL_STUFF_MARK = '".$KET."' WHERE REAL_STUFF_NOREQ = '".$NO_REQUEST."' AND REAL_STUFF_CONT = '".$NO_CONTAINER."' AND REAL_STUFF_FL_SEND = '1' ";
    					$this->db->query($updateTxGate);   
		                $i++;		            
		            }		            
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";    	                
    				$this->db->query($updateServices);
    				echo "Generated ".$row['SERVICES_ID']." | ".date('Y-m-d H:i:s')."<br>\n";
		        }
        	}
        }else{
        	echo "tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
    }



    function generateRealStripping(){
		$sqlgetIn = "SELECT A.REAL_STRIP_ID, A.REAL_STRIP_CONT NO_CONT, A.REAL_STRIP_NOREQ NO_REQUEST, TO_CHAR(B.STRIP_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQUEST,  A.REAL_STRIP_MECHANIC_TOOLS ALAT, TO_CHAR(A.REAL_STRIP_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REALISASI,A.REAL_STRIP_BY ID_USER, C.REAL_YARD_YBC_ID YBC_ID, A.REAL_STRIP_MARK  
					FROM TX_REAL_STRIP A
					INNER JOIN TX_REQ_STRIP_HDR B ON B.STRIP_ID = A.REAL_STRIP_HDR_ID
					INNER JOIN TX_REAL_YARD C ON C.REAL_YARD_CONT = A.REAL_STRIP_CONT AND C.REAL_YARD_STATUS = '1'
					WHERE A.REAL_STRIP_FL_SEND = 0 AND A.REAL_STRIP_STATUS = 2
					ORDER BY A.REAL_STRIP_DATE ASC";
    	$resultservices = $this->db->query($sqlgetIn);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
    		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$IdData = "";
        	foreach($resultservices->result_array() as $row){
        		$IdData .= $row['REAL_STRIP_ID'].",";
        		$returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$row['NO_CONT'].'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$row['NO_REQUEST'].'</NO_REQUEST>';
        		$returnXML .= '<TGL_REQUEST>'.$row['TGL_REQUEST'].'</TGL_REQUEST>'; 
        		$returnXML .= '<ID_USER>'.$row['ID_USER'].'</ID_USER>';
        		$returnXML .= '<YBC_ID>'.$row['YBC_ID'].'</YBC_ID>';
        		$returnXML .= '<MARK>'.$row['REAL_STRIP_MARK'].'</MARK>';
        		$returnXML .= '<TGL_REALISASI>'.$row['TGL_REALISASI'].'</TGL_REALISASI>';
        		$returnXML .= '<ALAT>'.$row['ALAT'].'</ALAT>';
        		$returnXML .= '</data>';
        	}
        	$returnXML .= '</loop>';			
			$returnXML .= '</document>';
			$IdData = substr($IdData, 0,-1);
	        
	        $ServiceID = date('YmdHis').rand(100,999);	        
	        $method = 'setRealStripping'; //method
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS) 
	        					VALUES (".$ServiceID.",'".$method."','".$returnXML."','0')";
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxRealStat = "UPDATE TX_REAL_STRIP SET REAL_STRIP_FL_SEND = '1' WHERE REAL_STRIP_ID IN (".$IdData.") AND REAL_STRIP_FL_SEND = '0' AND REAL_STRIP_STATUS = 2 ";
        		$this->db->query($updateTxRealStat);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
	        }
        }else{
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
	}

	function setRealStripping(){
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setRealStripping' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/billing_npks_pnk/api.php";
			    $method = 'setRealStripping'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);

			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = '".$response."'  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
        		$this->db->query($updateServices);
        		echo "ok | ".$row['SERVICES_ID']."<br>\n";
			}
		}else{
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
		}
    }

	function ParsingRealStripping(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'setRealStripping' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();        
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){

        		if($row['SERVICES_RESP_XML'] == ""){          		
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";        					
        			 $this->db->query($updateServices);
        			echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
        			continue;
        		} 

        		$response = $row['SERVICES_RESP_XML']->read(2000000);        		
		        $error = 0;
		        $xml = xml2ary($response);		        
		        $valResponse = $xml['document']['_c']['respon']['_v'];		        
		        if($valResponse != 0){

		            $loop =  $xml['document']['_c']['loop']['_c']['data'];
		            $totalData = count($loop);		            
		            $i = 0;
		            while ($i < $totalData) {
		                if($totalData == 1){
		                    $data = $loop['_c'];		                    
		                }else{
		                    $data = $loop[$i]['_c'];
		                }

		                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
		                $NO_REQUEST = $data['NO_REQUEST']['_v'];
		                $KET = $data['KET']['_v'];

		            	$updateTxGate = "UPDATE TX_REAL_STRIP SET REAL_STRIP_FL_SEND = '9', REAL_STRIP_MARK = '".$KET."' WHERE REAL_STRIP_NOREQ = '".$NO_REQUEST."' AND REAL_STRIP_CONT = '".$NO_CONTAINER."' AND REAL_STRIP_FL_SEND = '1' ";
    					$this->db->query($updateTxGate);   
		                $i++;		            
		            }		            
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";    	                
    				$this->db->query($updateServices);
    				echo "Generated ".$row['SERVICES_ID']." | ".date('Y-m-d H:i:s')."<br>\n";
		        }
        	}
        }else{
        	echo "tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
    }

	function getStripping(){
		$client = SERVICE_SERVER."/billing_npks_pnk/api.php";//alamat web service     http://172.20.19.70/billing_npks_pnk/login
		$method = 'getStripping'; 
		$sqlLastDateNota = "select TGL from (
												select TO_CHAR(STRIP_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_STRIP_HDR WHERE STRIP_PAID_DATE IS NOT NULL order by STRIP_PAID_DATE DESC
												)A where rownum =1";

		$resultLastDateNota = $this->db->query($sqlLastDateNota);
        $totalservice = $resultLastDateNota->num_rows();        

        if($totalservice <= 0){
        	$lastDateNota = date('YmdHis',strtotime("-10 hours"));         	   
        }else{
        	$resultLastDateNota = $resultLastDateNota->result_array();
        	$lastDateNota = $resultLastDateNota[0]['TGL'];
        }

		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

		$ServiceID = date('YmdHis').rand(100,999);
		$response = $this->call_service($client, $method,$params);
		
		$xml = xml2ary($response);	
		
        $valResponse = $xml['document']['_c']['respon']['_v'];		        
        if($valResponse != 0){ 
			$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
								VALUES (".$ServiceID.",'".$method."','".$lastDateNota."','".$response."','1')";
			$this->db->query($insertServices);
			echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
		}else{
			echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')."<br>\n";
		}
	}

	function ParsingStripping(){
	  $sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getStripping' order by services_req_date desc";
	  $resultservices = $this->db->query($sqlservices);
	    $totalservice = $resultservices->num_rows();
	    if($totalservice > 0){
	      foreach($resultservices->result_array() as $row){
	        if($row['SERVICES_RESP_XML'] == ""){
	          $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";
	           $this->db->query($updateServices);
	          echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
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
	                $NO_REQUEST_RECEIVING = $header['NO_REQUEST_RECEIVING']['_v'];
	                $PERP_DARI = $header['PERP_DARI']['_v'];
	                $PERP_KE = $header['PERP_KE']['_v'];
	                $STRIP_DARI = $header['STRIP_DARI']['_v'];

	                if($PERP_KE == ""){	                					
    					$PERP_KE = 0;
    				}

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
	                        $qlIDCONSIGNEE = "SELECT SEQ_CONSIGNEE_ID.NEXTVAL AS ID FROM DUAL";
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

	                    $STRIP_ORIGIN = 'INTERNAL';
	                    if($STRIP_DARI == 'TPK')
	                    	$STRIP_ORIGIN = 'TPK';

	                    $insertHDR = "INSERT INTO TX_REQ_STRIP_HDR (STRIP_ID, STRIP_NO, STRIP_CONSIGNEE_ID, STRIP_BRANCH_ID, STRIP_DO, STRIP_BL, STRIP_CREATE_DATE, STRIP_NOTA_DATE, STRIP_NOTA_NO, STRIP_PAID_DATE, STRIP_NOREQ_RECEIVING, STRIP_EXTEND_FROM,STRIP_EXTEND_LOOP, STRIP_ORIGIN) VALUES
	                                 (".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",3,'".$DO."','".$BL."', TO_DATE('".$REQ_STRIP_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),'".$NO_NOTA."',TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'), '".$NO_REQUEST_RECEIVING."','".$PERP_DARI."',".$PERP_KE.",'".$STRIP_DARI."')";
	                    $resultHDR = $this->db->query($insertHDR);

	                    if($resultHDR){
	                    	if($STRIP_DARI == 'TPK' && $PERP_DARI ==''){
						        $ServiceID = date('YmdHis').rand(100,999);	        
						        $method = 'getReceivingFromTPK'; //method
						        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS) 
						        					VALUES (".$ServiceID.",'".$method."','".$NO_REQUEST_RECEIVING."','0')";
						        $insert = $this->db->query($insertServices);							        
	                    	}

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
	                            $STRIP_DTL_ORIGIN = trim($detailroot['REQ_DTL_ORIGIN']['_v']);
	                            $STRIP_DTL_START_STRIP_PLAN = trim($detailroot['TGL_MULAI']['_v']);
	                            $STRIP_DTL_END_STRIP_PLAN = trim($detailroot['TGL_SELESAI']['_v']);

	                            if($STRIP_DARI == 'TPK' && $PERP_DARI ==''){
	                            	$ServiceID = date('YmdHis').rand(100,999);	        
							        $method = 'getGateInFromTPK'; //method
							        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS) 
							        					VALUES (".$ServiceID.",'".$method."','".$NO_REQUEST_RECEIVING."~".$REQ_DTL_CONT."','0')";
							        $insert = $this->db->query($insertServices);
	                            }

	                            $insertDTL = "INSERT INTO TX_REQ_STRIP_DTL (STRIP_DTL_HDR_ID, STRIP_DTL_CONT, STRIP_DTL_DANGER, STRIP_DTL_CONT_SIZE, STRIP_DTL_CONT_TYPE, STRIP_DTL_COMMODITY, STRIP_DTL_STATUS, STRIP_DTL_ORIGIN,STRIP_DTL_START_STRIP_PLAN,STRIP_DTL_END_STRIP_PLAN)
	                                          VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."','1', '".$STRIP_DTL_ORIGIN."',TO_DATE('".$STRIP_DTL_START_STRIP_PLAN."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$STRIP_DTL_END_STRIP_PLAN."','MM/DD/YYYY HH24:MI:SS'))";
	                            $resultDtl = $this->db->query($insertDTL);
	                            if($resultDtl){
	                                echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
	                            }else{
	                                echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
	                            }
	                           
	                            if($PERP_DARI !=""){
		                    		$updateStripTDL = "UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_ACTIVE = 'T', STRIP_DTL_STATUS = '2' WHERE STRIP_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = '3') AND STRIP_DTL_CONT = '".$REQ_DTL_CONT."'  ";
		          					$this->db->query($updateStripTDL);

									$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = 3)")->row()->JML;
									$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = 3) AND STRIP_DTL_ACTIVE = 'T' ")->row()->JML;

									if($cek_tot_dtl == $cek_tot_dtl_T){
										$updateStripHDR = "UPDATE TX_REQ_STRIP_HDR SET STRIP_STATUS = '2' WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = '3' ";
											$this->db->query($updateStripHDR);
									}
		                    	}

		                    	//insert history container
								$this->db->query("CALL ADD_HISTORY_CONTAINER(
											'".$REQ_DTL_CONT."', 
											'".$REQ_NO."',
											'".$REQ_STRIP_DATE."',
											'".$REQ_DTL_SIZE."',
											'".$REQ_DTL_TYPE."',
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											NULL,
											1,
											'Stripping',
											NULL,
											3)");

	                            $a++;
	                        }
	                    }else{
	                        echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
	                        $error++;
	                    }
	                }else{
	                    echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
	                }
	            $i++; 
	            }	             
	            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
	          $this->db->query($updateServices);
	        }else{
	             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
	           $this->db->query($updateServices);
	           die("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
	        }

	      }
	    }else{
	      die("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')."<br>\n");
	    }
	}

	function generateSetGateIn(){
    	$sqlgetIn = "SELECT GATE_ID, GATE_CONT AS NO_CONTAINER, GATE_NOREQ AS NO_REQUEST, GATE_TRUCK_NO AS NOPOL, GATE_CREATE_BY AS ID_USER, TO_CHAR(GATE_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_IN, GATE_CONT_STATUS AS STATUS, GATE_ORIGIN FROM TX_GATE  WHERE GATE_FL_SEND = '0' AND GATE_ACTIVITY = '3' AND GATE_STATUS = '1' ORDER BY GATE_CREATE_DATE ASC";
    	$resultservices = $this->db->query($sqlgetIn);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
    		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$IdData = "";
        	foreach($resultservices->result_array() as $row){
        		$IdData .= $row['GATE_ID'].",";        		
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
			$IdData = substr($IdData, 0,-1);

	        $ServiceID = date('YmdHis').rand(100,999);	        
	        $method = 'setGateIn'; //method
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS) 
	        					VALUES (".$ServiceID.",'".$method."','".$returnXML."','0')";
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxgate = "UPDATE TX_GATE SET GATE_FL_SEND = '1' WHERE GATE_ID IN (".$IdData.") AND GATE_FL_SEND = '0' ";
        		$this->db->query($updateTxgate);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
	        }
	        
        }else{
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
    }

    function SetGateIn(){
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setGateIn' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/billing_npks_pnk/api.php";
			    $method = 'setGateIn'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);		    

			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = '".$response."'  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
        		$result = $this->db->query($updateServices);
        		if($result)
        			echo "ok ".$row['SERVICES_ID']."<br>\n";
        		else
        			echo "gagal update ".$row['SERVICES_ID']."<br>\n";
			}	
		}else{
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
		}
    }

    function ParsingGateIn(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'setGateIn' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();        
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){

        		if($row['SERVICES_RESP_XML'] == ""){          		
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";        					
        			 $this->db->query($updateServices);
        			echo "tidak ada response | ".date('Y-m-d H:i:s')."<br>\n";
        			continue;
        		} 

        		$response = $row['SERVICES_RESP_XML']->read(2000000);        		
		        $error = 0;
		        $xml = xml2ary($response);		        
		        $valResponse = $xml['document']['_c']['respon']['_v'];		        
		        if($valResponse != 0){

		            $loop =  $xml['document']['_c']['loop']['_c']['data'];
		            $totalData = count($loop);		            
		            $i = 0;
		            while ($i < $totalData) {
		                if($totalData == 1){
		                    $data = $loop['_c'];		                    
		                }else{
		                    $data = $loop[$i]['_c'];
		                }

		                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
		                $NO_REQUEST = $data['NO_REQUEST']['_v'];
		                $KET = $data['KET']['_v'];

		            	$updateTxGate = "UPDATE TX_GATE SET GATE_FL_SEND = '9' WHERE GATE_NOREQ = '".$NO_REQUEST."' AND GATE_CONT = '".$NO_CONTAINER."' AND GATE_FL_SEND = '1' ";
    					$this->db->query($updateTxGate);   
		                $i++;		            
		            }		            
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";    	                
    				$this->db->query($updateServices);
    				echo "Generated ".$row['SERVICES_ID']." | ".date('Y-m-d H:i:s')."<br>\n";
		        }
        	}
        }else{
        	echo "tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
    }

    function generateSetGateOut(){
    	$sqlgetIn = "SELECT A.GATE_ID, A.GATE_CONT AS NO_CONTAINER, A.GATE_NOREQ AS NO_REQUEST, TO_CHAR(B.REQ_DELIVERY_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQ_DELIVERY, A.GATE_TRUCK_NO AS NOPOL, 
					A.GATE_CREATE_BY AS ID_USER, TO_CHAR(A.GATE_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_OUT, GATE_CONT_STATUS AS STATUS, GATE_ORIGIN AS GATE_DESTINATION, A.GATE_NO_SEAL AS NO_SEAL, A.GATE_MARK AS MARK 
					FROM TX_GATE A 
					INNER JOIN TX_REQ_DELIVERY_HDR B ON B.REQ_NO = A.GATE_NOREQ
					WHERE A.GATE_FL_SEND = '0' AND A.GATE_ACTIVITY = '4' AND A.GATE_STATUS = '3' ORDER BY A.GATE_CREATE_DATE ASC";
    	$resultservices = $this->db->query($sqlgetIn);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
    		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$IdData = '';
        	foreach($resultservices->result_array() as $row){
        		$IdData .= $row['GATE_ID'].","; 
        		$returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$row['NO_CONTAINER'].'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$row['NO_REQUEST'].'</NO_REQUEST>';
        		$returnXML .= '<TGL_REQ_DELIVERY>'.$row['TGL_REQ_DELIVERY'].'</TGL_REQ_DELIVERY>';
        		$returnXML .= '<NOPOL>'.$row['NOPOL'].'</NOPOL>';
        		$returnXML .= '<ID_USER>'.$row['ID_USER'].'</ID_USER>';
        		$returnXML .= '<TGL_OUT>'.$row['TGL_OUT'].'</TGL_OUT>';
        		$returnXML .= '<STATUS>'.$row['STATUS'].'</STATUS>';
        		$returnXML .= '<NO_SEAL>'.$row['NO_SEAL'].'</NO_SEAL>';
        		$returnXML .= '<MARK>'.$row['MARK'].'</MARK>';
        		$returnXML .= '<GATE_DESTINATION>'.$row['GATE_DESTINATION'].'</GATE_DESTINATION>';
        		$returnXML .= '</data>';
        	}
        	$returnXML .= '</loop>';
			$returnXML .= '</document>';

			$IdData = substr($IdData, 0,-1);

	        $ServiceID = date('YmdHis').rand(100,999);
	        $method = 'setGateOut'; //method
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."','".$returnXML."','0')";	       
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxgate = "UPDATE TX_GATE SET GATE_FL_SEND = '1' WHERE GATE_ID IN (".$IdData.") AND GATE_FL_SEND = '0' ";
        		$this->db->query($updateTxgate);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
	        }

        }else{
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
    }

    function SetGateOut(){
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setGateOut' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();        
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){         		
		    	$client = SERVICE_SERVER."/billing_npks_pnk/api.php";
			    $method = 'setGateOut'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);	

			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = '".$response."'  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";        					
        		$this->db->query($updateServices);
        		echo "ok ".$row['SERVICES_ID']."<br>\n";
			}
		}else{
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
		}
    }

    function ParsingGateOut(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'setGateOut' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
        		
        		if($row['SERVICES_RESP_XML'] == ""){          		
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";        					
        			 $this->db->query($updateServices);
        			echo "tidak ada response | ".date('Y-m-d H:i:s')."<br>\n";
        			continue;
        		}

        		$response = $row['SERVICES_RESP_XML']->read(2000000);			        
		        $error = 0;
		        $xml = xml2ary($response);		        
		        $valResponse = $xml['document']['_c']['respon']['_v'];		        
		        if($valResponse != 0){		        	
		            $loop =  $xml['document']['_c']['loop']['_c']['data'];
		            $totalData = count($loop);            
		            $i =0;
		            while ($i < $totalData) {
		                if($totalData == 1){
		                    $data = $loop['_c'];		                    
		                }else{
		                    $data = $loop[$i]['_c'];
		                }

		                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
		                $NO_REQUEST = $data['NO_REQUEST']['_v'];
		                $STATUS = $data['STATUS']['_v']; 
		                $UR_STATUS = $data['UR_STATUS']['_v'];

		                if($STATUS == '0'){
			            	$updateTxGate = "UPDATE TX_GATE SET GATE_FL_SEND = '9', GATE_MARK = '".$UR_STATUS."' WHERE GATE_NOREQ = '".$NO_REQUEST."' AND GATE_CONT = '".$NO_CONTAINER."' AND GATE_FL_SEND = '1' ";        					
	    					$this->db->query($updateTxGate);		                	
		                }else{
		                	$updateTxGate = "UPDATE TX_GATE SET GATE_FL_SEND = '9' WHERE GATE_NOREQ = '".$NO_REQUEST."' AND GATE_CONT = '".$NO_CONTAINER."' AND GATE_FL_SEND = '1' ";        					
	    					$this->db->query($updateTxGate);
		                }
		                $i++;		            
		            }		            
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
    				$this->db->query($updateServices);
		        }else{
		        	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
    				$this->db->query($updateServices);
		        }
        	}
        }else{
        	echo "tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
    }

    function setPlacement(){
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setPlacement' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/billing_npks_pnk/api.php";
			    $method = 'setPlacement'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);

			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = '".$response."'  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
        		$this->db->query($updateServices);
        		echo "ok ".$row['SERVICES_ID']."<br>\n";
			}
		}else{
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
		}
    }

    function setPlacementAll(){
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setPlacementAll' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/billing_npks_pnk/api.php";
			    $method = 'setPlacementAll'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);

			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = '".$response."'  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
        		$this->db->query($updateServices);
        		echo "ok ".$row['SERVICES_ID']."<br>\n";
			}
		}else{
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
		}
    }

    function generatePlacement(){
    	$sqlgetIn = "SELECT A.REAL_YARD_ID, A.REAL_YARD_YBC_ID, B.YBC_SLOT, B.YBC_ROW, B.YBC_BLOCK_ID, A.REAL_YARD_TIER TIER, A.REAL_YARD_NO ID_YARD, A.REAL_YARD_CONT NO_CONTAINER, A.REAL_YARD_REQ_NO NO_REQUEST, A.REAL_YARD_BRANCH_ID, TO_CHAR(A.REAL_YARD_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_PLACEMENT, A.REAL_YARD_CREATE_BY AS ID_USER, A.REAL_YARD_CONT_STATUS CONT_STATUS
					FROM TX_REAL_YARD A
					INNER JOIN TX_YARD_BLOCK_CELL B ON B.YBC_ID = A.REAL_YARD_YBC_ID
					WHERE A.REAL_YARD_STATUS = '1' AND A.REAL_YARD_ACTIVITY = '3' AND A.REAL_YARD_FL_SEND = '0' ORDER BY A.REAL_YARD_CREATE_DATE ASC";
    	$resultservices = $this->db->query($sqlgetIn);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
    		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$IdData = '';
        	foreach($resultservices->result_array() as $row){
        		$IdData .= $row['REAL_YARD_ID'].",";
        		$returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$row['NO_CONTAINER'].'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$row['NO_REQUEST'].'</NO_REQUEST>';
        		$returnXML .= '<SLOT>'.$row['YBC_SLOT'].'</SLOT>';
        		$returnXML .= '<ROW>'.$row['YBC_ROW'].'</ROW>';
        		$returnXML .= '<BLOCK>'.$row['YBC_BLOCK_ID'].'</BLOCK>';
        		$returnXML .= '<TIER>'.$row['TIER'].'</TIER>';
        		$returnXML .= '<ID_YARD>'.$row['ID_YARD'].'</ID_YARD>';
        		$returnXML .= '<ID_USER>'.$row['ID_USER'].'</ID_USER>';
        		$returnXML .= '<CONT_STATUS>'.$row['CONT_STATUS'].'</CONT_STATUS>';
        		$returnXML .= '<TGL_PLACEMENT>'.$row['TGL_PLACEMENT'].'</TGL_PLACEMENT>';
        		$returnXML .= '</data>';
        	}
        	$returnXML .= '</loop>';			
			$returnXML .= '</document>';
	        
	        $IdData = substr($IdData, 0,-1);

	        $ServiceID = date('YmdHis').rand(100,999);	        
	        $method = 'setPlacement'; //method
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS) 
	        					VALUES (".$ServiceID.",'".$method."','".$returnXML."','0')";
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxRealYard = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '1' WHERE REAL_YARD_ID IN (".$IdData.") AND REAL_YARD_FL_SEND = '0' ";
        		$this->db->query($updateTxRealYard);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
	        }
        }else{
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
    }

    function generatePlacementAll(){
    	$sqlgetIn = "SELECT A.REAL_YARD_ID, A.REAL_YARD_YBC_ID, B.YBC_SLOT, B.YBC_ROW, B.YBC_BLOCK_ID, A.REAL_YARD_TIER TIER, A.REAL_YARD_NO ID_YARD, A.REAL_YARD_CONT NO_CONTAINER, A.REAL_YARD_REQ_NO NO_REQUEST, A.REAL_YARD_BRANCH_ID, TO_CHAR(A.REAL_YARD_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_PLACEMENT, A.REAL_YARD_CREATE_BY AS ID_USER, A.REAL_YARD_CONT_STATUS CONT_STATUS, A.REAL_YARD_TYPE TIPE_ACTIVITY
					FROM TX_REAL_YARD A
					INNER JOIN TX_YARD_BLOCK_CELL B ON B.YBC_ID = A.REAL_YARD_YBC_ID
					WHERE A.REAL_YARD_STATUS = '1' AND A.REAL_YARD_ACTIVITY NOT IN ('3','4') AND A.REAL_YARD_FL_SEND = '0' ORDER BY A.REAL_YARD_CREATE_DATE ASC";
    	$resultservices = $this->db->query($sqlgetIn);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
    		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$IdData = '';
        	foreach($resultservices->result_array() as $row){
        		$IdData .= $row['REAL_YARD_ID'].",";
        		$returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$row['NO_CONTAINER'].'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$row['NO_REQUEST'].'</NO_REQUEST>';
        		$returnXML .= '<SLOT>'.$row['YBC_SLOT'].'</SLOT>';
        		$returnXML .= '<ROW>'.$row['YBC_ROW'].'</ROW>';
        		$returnXML .= '<BLOCK>'.$row['YBC_BLOCK_ID'].'</BLOCK>';
        		$returnXML .= '<TIER>'.$row['TIER'].'</TIER>';
        		$returnXML .= '<ID_YARD>'.$row['ID_YARD'].'</ID_YARD>';
        		$returnXML .= '<ID_USER>'.$row['ID_USER'].'</ID_USER>';
        		$returnXML .= '<CONT_STATUS>'.$row['CONT_STATUS'].'</CONT_STATUS>';
        		$returnXML .= '<TGL_PLACEMENT>'.$row['TGL_PLACEMENT'].'</TGL_PLACEMENT>';
        		$returnXML .= '<TIPE_ACTIVITY>'.$row['TIPE_ACTIVITY'].'</TIPE_ACTIVITY>';
        		$returnXML .= '</data>';
        	}
        	$returnXML .= '</loop>';			
			$returnXML .= '</document>';
	        
	        $IdData = substr($IdData, 0,-1);

	        $ServiceID = date('YmdHis').rand(100,999);	        
	        $method = 'setPlacementAll'; //method
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS) 
	        					VALUES (".$ServiceID.",'".$method."','".$returnXML."','0')";
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxRealYard = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '1' WHERE REAL_YARD_ID IN (".$IdData.") AND REAL_YARD_FL_SEND = '0' ";
        		$this->db->query($updateTxRealYard);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
	        }
        }else{
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
    }

    function ParsingPlacement(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'setPlacement' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
        		
        		if($row['SERVICES_RESP_XML'] == ""){          		
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";        					
        			 $this->db->query($updateServices);
        			echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
        			continue;
        		}

        		$response = $row['SERVICES_RESP_XML']->read(2000000);			        
		        $error = 0;
		        $xml = xml2ary($response);		        
		        $valResponse = $xml['document']['_c']['respon']['_v'];		        
		        if($valResponse != 0){		        	
		            $loop =  $xml['document']['_c']['loop']['_c']['data'];
		            $totalData = count($loop);            
		            $i =0;
		            while ($i < $totalData) {
		                if($totalData == 1){
		                    $data = $loop['_c'];		                    
		                }else{
		                    $data = $loop[$i]['_c'];
		                }

		                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
		                $NO_REQUEST = $data['NO_REQUEST']['_v'];
		                $KET = $data['KET']['_v']; 
		                
		                if($KET == 'OK'){
			            	$updateTxGate = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '9', REAL_YARD_MARK = '".$KET."' WHERE REAL_YARD_REQ_NO = '".$NO_REQUEST."' AND REAL_YARD_CONT = '".$NO_CONTAINER."' AND REAL_YARD_FL_SEND = '1' ";			
	    					$this->db->query($updateTxGate);		                	
		                }else{
		                	$updateTxGate = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '9', REAL_YARD_MARK = '".$KET."' WHERE REAL_YARD_REQ_NO = '".$NO_REQUEST."' AND REAL_YARD_CONT = '".$NO_CONTAINER."' AND REAL_YARD_FL_SEND = '1' ";    			
	    					$this->db->query($updateTxGate);
		                }
		                $i++;		            
		            }		            
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS  = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
    				$this->db->query($updateServices);
		        }else{
		        	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
    				$this->db->query($updateServices);
		        }
        	}
        }else{
        	echo "tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
        }
    }

    function ParsingPlacementAll(){
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'setPlacementAll' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
        		
        		if($row['SERVICES_RESP_XML'] == ""){          		
        			$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '2', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '1'";        					
        			 $this->db->query($updateServices);
        			echo "tidak ada response ".date('Y-m-d H:i:s')."<br>\n";
        			continue;
        		}

        		$response = $row['SERVICES_RESP_XML']->read(2000000);			        
		        $error = 0;
		        $xml = xml2ary($response);		        
		        $valResponse = $xml['document']['_c']['respon']['_v'];		        
		        if($valResponse != 0){		        	
		            $loop =  $xml['document']['_c']['loop']['_c']['data'];
		            $totalData = count($loop);            
		            $i =0;
		            while ($i < $totalData) {
		                if($totalData == 1){
		                    $data = $loop['_c'];		                    
		                }else{
		                    $data = $loop[$i]['_c'];
		                }

		                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
		                $NO_REQUEST = $data['NO_REQUEST']['_v'];
		                $KET = $data['KET']['_v']; 
		                
		                if($KET == 'OK'){
			            	$updateTxGate = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '9', REAL_YARD_MARK = '".$KET."' WHERE REAL_YARD_REQ_NO = '".$NO_REQUEST."' AND REAL_YARD_CONT = '".$NO_CONTAINER."' AND REAL_YARD_FL_SEND = '1' ";		
	    					$this->db->query($updateTxGate);		                	
		                }else{
		                	$updateTxGate = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '9', REAL_YARD_MARK = '".$KET."' WHERE REAL_YARD_REQ_NO = '".$NO_REQUEST."' AND REAL_YARD_CONT = '".$NO_CONTAINER."' AND REAL_YARD_FL_SEND = '1' ";
	    					$this->db->query($updateTxGate);
		                }
		                $i++;		            
		            }		            
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS  = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
    				$this->db->query($updateServices);
		        }else{
		        	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";        					
    				$this->db->query($updateServices);
		        }
        	}
        }else{
        	echo "tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
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

	


}
