<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Client extends CI_Controller {
	//private $func;
	public function __construct(){
		parent::__construct();
		$this->load->library("Nusoap_library");
        $this->load->database();

		//$client = new nusoap_client();
		//$this->nusoap_client->configureWSDL('nusoap_server','urn:nusoap_server');
	}

	public function index(){
		//echo $_SERVER['SERVER_NAME'];die();
		//show_404();
		echo date('Y-m-d H:i:s');
		//die();
	}

	function getServer($branch){

		$ch = curl_init(SERVICE_SERVER_NODEJS."/updateGateJobManager?branch=".$branch."");
		curl_exec($ch);
		curl_close($ch);
		//echo SERVICE_SERVER;
	}

	function sendSocketYardMonitor(){
		$branch = $this->input->post('branch');
		$ybc_id = $this->input->post('ybc_id');
		$tier = $this->input->post('tier');
		$cont = $this->input->post('cont');
		$activity = $this->input->post('activity');
		$ybc_id40 = $this->input->post('ybc_id40');
		$size = $this->input->post('size');
		$ybc_id_old = $this->input->post('ybc_id_old');
		$ybc_id_old40 = $this->input->post('ybc_id_old40');
		$tier_before = $this->input->post('tier_old');
		$tier_old = 0;
		$tier_old40 = 0;
		$tier_before_ = 0;
		$cont_detail = 0;

		if($activity == 'Shifting'){
			$activity = 'Shuffling';
		}

		$result = $this->db->select('B.YARD_NAME, C.BLOCK_NAME, A.YBC_SLOT, A.YBC_ROW, C.BLOCK_ID')
										 ->from('TX_YARD_BLOCK_CELL A')
										 ->join('TM_YARD B','B.YARD_ID = A.YBC_YARD_ID')
										 ->join('TM_BLOCK C','C.BLOCK_ID = A.YBC_BLOCK_ID')
										 ->where('A.YBC_BRANCH_ID',$branch)->where('A.YBC_ID',$ybc_id)
										 ->get()->row_array();
		$slot = $result['YBC_SLOT'];

		$ybc_id_old40 = ($ybc_id_old40 != '')? $ybc_id_old40 : 0;
		$ybc_id_old = ($ybc_id_old != '')? $ybc_id_old : 0;
		$ybc_id40 = ($ybc_id40 != '')? $ybc_id40 : 0;

		$result_old = $this->db->select('B.YARD_NAME, C.BLOCK_NAME, A.YBC_SLOT, A.YBC_ROW, C.BLOCK_ID')
										 ->from('TX_YARD_BLOCK_CELL A')
										 ->join('TM_YARD B','B.YARD_ID = A.YBC_YARD_ID')
										 ->join('TM_BLOCK C','C.BLOCK_ID = A.YBC_BLOCK_ID')
										 ->where('A.YBC_BRANCH_ID',$branch)->where('A.YBC_ID',$ybc_id_old)
										 ->get()->row_array();

		 $result40 = $this->db->select('B.YARD_NAME, C.BLOCK_NAME, A.YBC_SLOT, A.YBC_ROW, C.BLOCK_ID')
 										 ->from('TX_YARD_BLOCK_CELL A')
 										 ->join('TM_YARD B','B.YARD_ID = A.YBC_YARD_ID')
 										 ->join('TM_BLOCK C','C.BLOCK_ID = A.YBC_BLOCK_ID')
 										 ->where('A.YBC_BRANCH_ID',$branch)->where('A.YBC_ID',$ybc_id40)
 										 ->get()->row_array();

		 $result40_old = $this->db->select('B.YARD_NAME, C.BLOCK_NAME, A.YBC_SLOT, A.YBC_ROW, C.BLOCK_ID')
											 ->from('TX_YARD_BLOCK_CELL A')
											 ->join('TM_YARD B','B.YARD_ID = A.YBC_YARD_ID')
											 ->join('TM_BLOCK C','C.BLOCK_ID = A.YBC_BLOCK_ID')
											 ->where('A.YBC_BRANCH_ID',$branch)->where('A.YBC_ID',$ybc_id_old40)
											 ->get()->row_array();

		$delivery_to = $this->db->select("NVL(B.REQUEST_TO,'T') DELIVERY_TO")->from('TX_REQ_DELIVERY_DTL A')->join('TX_REQ_DELIVERY_HDR B','B.REQ_ID = A.REQ_HDR_ID AND A.REQ_DTL_STATUS <> 2')->where('B.REQ_BRANCH_ID',$branch)->where('A.REQ_DTL_CONT',$cont)->get()->row_array()['DELIVERY_TO'];

		if($activity == 'OnChasis' || $activity == 'Gate Out TPK' || $activity == 'Gate Out Luar'){
			$tier_before_ = $tier;
			$tier_loc = $tier;
			$tier = $this->db->select('REAL_YARD_YBC_ID')->from('TX_REAL_YARD')->where('REAL_YARD_USED',1)->where('REAL_YARD_YBC_ID',$ybc_id)->count_all_results();
			$cont_detail = array('detail' => 0, 'detail_actve' => 0);
		}
		else{
			// $cont_detail = $this->db->select('REAL_YARD_CONT CONT, REAL_YARD_CONT_TYPE "TYPE", REAL_YARD_CONT_STATUS STATUS, REAL_YARD_CONT_SIZE "SIZE"')->from('TX_REAL_YARD')->where('REAL_YARD_USED',1)->where('REAL_YARD_CONT',$cont)->get()->row_array();
			$cont_detail = $this->db->select('A.REAL_YARD_CONT CONT, A.REAL_YARD_CONT_TYPE "TYPE", A.REAL_YARD_CONT_STATUS STATUS, A.REAL_YARD_CONT_SIZE "SIZE", C.OWNER_NAME "OWNER" ')->from('TX_REAL_YARD A')
															->join('TM_CONTAINER B','B.CONTAINER_NO = A.REAL_YARD_CONT','left')
															->join('TM_OWNER C','C.OWNER_CODE = B.CONTAINER_OWNER','left')
															->where('A.REAL_YARD_USED',1)->where('A.REAL_YARD_CONT',$cont)->get()->row_array();
			$cont_detail = array('detail' => $cont_detail, 'detail_actve' => 1);
			$tier_loc = $tier;
		}

		$result_json = json_encode(array('new_loc' => $result, 'old_loc' => $result_old, 'new_loc40' => $result40, 'old_loc40' => $result40_old, 'tier' => $tier_before_, 'tier_old' => $tier_before, 'cont_detail' => $cont_detail));

		$tier_old = $this->db->select('REAL_YARD_YBC_ID')->from('TX_REAL_YARD')->where('REAL_YARD_USED',1)->where('REAL_YARD_YBC_ID',$ybc_id_old)->count_all_results();

		if($size == 40){
			$tier_old40 = $this->db->select('REAL_YARD_YBC_ID')->from('TX_REAL_YARD')->where('REAL_YARD_USED',1)->where('REAL_YARD_YBC_ID',$ybc_id_old40)->count_all_results();
			if($activity == 'OnChasis' || $activity == 'Gate Out TPK' || $activity == 'Gate Out Luar'){
			$slot40 = (int)$result['YBC_SLOT'] - 1;
			$slot = $slot40.' - '.$result['YBC_SLOT'];
			}
			else{
				$slot40 = (int)$result['YBC_SLOT'] + 1;
				$slot = $result['YBC_SLOT'].' - '.$slot40;
			}
		}


		if($activity == 'Gate In'){
			$loc = $activity;
		}
		else{
			$loc = $activity.' | '.$result['BLOCK_NAME'].' / '.$slot.' / '.$result['YBC_ROW'].' / '.$tier_loc;
		}

		$ch = curl_init(SERVICE_SERVER_NODEJS."/updateTierMonitor?branch=".$branch."&ybc_id=".$ybc_id."&tier=".$tier."&cont=".$cont."&loc=".urlencode($loc)."&ybc_id40=".$ybc_id40."&size=".$size."&ybc_id_old=".$ybc_id_old."&ybc_id_old40=".$ybc_id_old40."&tier_old=".$tier_old."&tier_old40=".$tier_old40."&detail=".urlencode($result_json)."&delivery_to=".$delivery_to);
		curl_exec($ch);
		curl_close($ch);

		if($activity == 'Placement' || $activity == 'OnChasis' || $activity == 'Gate Out TPK' || $activity == 'Gate Out Luar' ){
			$ch = curl_init(SERVICE_SERVER_NODEJS."/updateYor?branch=".$branch);
			curl_exec($ch);
			curl_close($ch);
		}
	}

	function getcrontab(){

		$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML, SERVICES_RESP_XML from TX_SERVICES where SERVICES_ID = '20190108163223648' AND services_method = 'setRealStripping' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
				$req = $row['SERVICES_REQ_XML']->read(2000000);

				$xml = explode("#",$req);
				$totalHDR = count($xml);
				$i = 0;
				$data = array();
				while ($i < $totalHDR) {
					$arrdata = explode("~", $xml[$i]);
					$data[$i]['NO_CONTAINER'] = $arrdata[0];
					$data[$i]['NO_REQUEST'] = $arrdata[1];
					$i++;
				}

				print_r($data);
			}
		}

		die();
		$aa = '12/27/2018 20:00:00';
		$aa = substr($aa,0,-9);
		echo $aa;
	}

	function getTest(){
		echo "Running well\n";
	}


	function getDelivery($branch = false){
		$time_start_new = microtime(true);
		echo "getDelivery | ";
		//$client = SERVICE_SERVER."/api.php";
		$client = SERVICE_SERVER."/api.php";

        $method = 'getDelivery'; //method

        $sqlLastDateNota = "select TGL from (
                            select TO_CHAR(REQUEST_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_DELIVERY_HDR  WHERE REQUEST_PAID_DATE IS NOT NULL AND REQUEST_ALIH_KAPAL = 'T' AND REQ_BRANCH_ID = ".$branch." order by REQUEST_PAID_DATE DESC
                            )A where rownum =1";

        $resultLastDateNota = $this->db->query($sqlLastDateNota);
        $totalservice = $resultLastDateNota->num_rows();

        if($totalservice <= 0){
        	$lastDateNota = date('YmdHis',strtotime("-1 hours"));
        }else{
        	$resultLastDateNota = $resultLastDateNota->result_array();
        	$lastDateNota = $resultLastDateNota[0]['TGL'];
        }
		// $lastDateNota = '20181220010101';
        $params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);

        $xml = xml2ary($response);
        $valResponse = $xml['document']['_c']['respon']['_v'];
       	$response = $this->split_character($response,4000);
        if($valResponse != 0){
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function getDeliveryNoreq($noreq){
		$time_start_new = microtime(true);
		echo "getDelivery | ";
		//$client = SERVICE_SERVER."/api.php";
		$client = SERVICE_SERVER."/api.php";

        $method = 'getDeliveryNoreq'; //method

        $params = array('string0'=>'npks','string1'=>'12345','string2'=>$noreq);

        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);

        $xml = xml2ary($response);
        $valResponse = $xml['document']['_c']['respon']['_v'];
       	$response = $this->split_character($response,4000);
        if($valResponse != 0){
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'getDelivery','".$noreq."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function ParsingDelivery($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingDelivery | ";
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
					$response = str_replace('&','&amp;', $response);
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

									$sqlcek = "SELECT REQ_NO, REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO='".$REQ_NO."' AND REQ_BRANCH_ID = ".$branch." ";
									$resultCek = $this->db->query($sqlcek);
									$totalcek = $resultCek->num_rows();

									// if($totalcek <=0){
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

											if($totalcek <=0){

												$qlID = "SELECT SEQ_REQ_DELIVERY_HDR.NEXTVAL AS ID FROM DUAL";
												$resultID = $this->db->query($qlID)->result_array();
												$IDheader = $resultID[0]['ID'];

												$insertHDR = "INSERT INTO TX_REQ_DELIVERY_HDR (REQ_ID, REQ_NO, REQ_CONSIGNEE_ID, REQ_BRANCH_ID, REQ_MARK, REQ_DELIVERY_DATE, REQUEST_NOTA_DATE, REQUEST_PAID_DATE, REQUEST_TO, REQUEST_STATUS, REQUEST_EXTEND_FROM,REQUEST_EXTEND_LOOP) VALUES
																		(".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",".$branch.",'".$REQ_MARK."', TO_DATE('".$REQ_DELIVERY_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'),'".$DELIVERY_KE."', '1','".$PERP_DARI."',".$PERP_KE.")";
												$resultHDR = $this->db->query($insertHDR);
											}else{
												$resultHDR = true;
												$resultCek = $resultCek->result_array();
												$IDheader = $resultCek[0]['REQ_ID'];
											}

											if($resultHDR){

													// if($DELIVERY_KE == 'TPK' && $PERP_DARI ==''){
													// 	$ServiceID = date('YmdHis').rand(100,999);
												    //     $method = 'getGateOutToTPK'; //method
												    //     $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
												    //     					VALUES (".$ServiceID.",'".$method."','".$REQ_NO."','0')";
												    //     $insert = $this->db->query($insertServices);
													// }

													//echo $i.", sukses | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
													$totalDTL = count($detail);
													$a = 0;
													while($a < $totalDTL){
															if($totalDTL == 1){
																	$detailroot = $detail['_c'];
															}else{
																	$detailroot = $detail[$a]['_c'];
															}

															$sqlcekdetilnya = "SELECT * FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID='".$IDheader."' AND REQ_DTL_CONT = '".trim($detailroot['REQ_DTL_CONT']['_v'])."' ";
															$resultCekdetilNya = $this->db->query($sqlcekdetilnya);
															$totalcekDetilnya = $resultCekdetilNya->num_rows();

															if($totalcekDetilnya <= 0){

																$REQ_DTL_CONT = trim($detailroot['REQ_DTL_CONT']['_v']);
																$REQ_DTL_CONT_STATUS = trim($detailroot['REQ_DTL_CONT_STATUS']['_v']);
																$REQ_DTL_COMMODITY = trim($detailroot['REQ_DTL_COMMODITY']['_v']);
																$REQ_DTL_VIA = trim($detailroot['REQ_DTL_VIA']['_v']);
																$REQ_DTL_CONT_HAZARD = trim($detailroot['REQ_DTL_CONT_HAZARD']['_v']);
																$REQ_DTL_SIZE = trim($detailroot['REQ_DTL_SIZE']['_v']);
																$REQ_DTL_TYPE = trim($detailroot['REQ_DTL_TYPE']['_v']);
																$REQ_DTL_DEL_DATE = trim($detailroot['REQ_DTL_DEL_DATE']['_v']);
																$REQ_DTL_NO_SEAL = trim($detailroot['REQ_DTL_NO_SEAL']['_v']);

																if($DELIVERY_KE == 'TPK' && $PERP_DARI ==''){
																	$ServiceID = date('YmdHis').rand(100,999);
																	$method = 'getGateOutToTPK'; //method
																	$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
																						VALUES (".$ServiceID.",'".$method."','".$REQ_NO."~".$REQ_DTL_CONT."','0')";
																	// $insert = $this->db->query($insertServices);
																	// digantikan dengan modul on chasis
																}

																$insertDTL = "INSERT INTO TX_REQ_DELIVERY_DTL (REQ_HDR_ID, REQ_DTL_CONT, REQ_DTL_CONT_STATUS, REQ_DTL_CONT_HAZARD, REQ_DTL_CONT_SIZE, REQ_DTL_CONT_TYPE, REQ_DTL_COMMODITY, REQ_DTL_DEL_DATE, REQ_DTL_NO_SEAL)
																							VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_STATUS."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."',TO_DATE('".$REQ_DTL_DEL_DATE."','MM/DD/YYYY HH24:MI:SS'), '".$REQ_DTL_NO_SEAL."')";
																$resultDtl = $this->db->query($insertDTL);
																if($resultDtl){
																		echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
																}else{
																		echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
																}

																if($PERP_DARI !=""){
																	echo $a.", perpanjangan dari  | ".$PERP_DARI."~".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
																	$updateDeliveryTDL = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_ACTIVE = 'T', REQ_DTL_STATUS = '2' WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = ".$branch.") AND REQ_DTL_CONT = '".$REQ_DTL_CONT."'  ";
																	$this->db->query($updateDeliveryTDL);

																	$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = ".$branch.")")->row()->JML;
																	$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = ".$branch.") AND REQ_DTL_ACTIVE = 'T' ")->row()->JML;

																	if($cek_tot_dtl == $cek_tot_dtl_T){
																		$updateStuffHDR = "UPDATE TX_REQ_DELIVERY_HDR SET REQUEST_STATUS = '2' WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = ".$branch." ";
																		$this->db->query($updateStuffHDR);
																	}
																}

																//insert history container
																$this->db->query("CALL ADD_HISTORY_CONTAINER(
																			'".$REQ_DTL_CONT."',
																			'".$REQ_NO."',
																			'".$REQ_DELIVERY_DATE."',
																			'".$REQ_DTL_SIZE."',
																			'".$REQ_DTL_TYPE."',
																			'".$REQ_DTL_CONT_STATUS."',
																			NULL,
																			NULL,
																			NULL,
																			NULL,
																			NULL,
																			4,
																			'Request Delivery',
																			NULL,
																			NULL,
																			".$branch.",
																			NULL,
																			NULL)");
															}

															$a++;
													}


											}else{
													echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
													$error++;
											}
									// }else{
									// 		echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
									// }
							$i++;
							}
							$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
							$this->db->query($updateServices);
					}else{
							 $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
						 $this->db->query($updateServices);
						 echo("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
					}
				}
				//start call nodejs
					// $updateGateJobManager = curl_init(SERVICE_SERVER_NODEJS."/updateGateJobManager?branch=".$branch."");
					// curl_exec($updateGateJobManager);
					// curl_close($updateGateJobManager);
					$updateSP2 = curl_init(SERVICE_SERVER_NODEJS."/updateSP2?branch=".$branch."");
					curl_exec($updateSP2);
					curl_close($updateSP2);
				//end call nodejs
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
				echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
			}else{
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
				echo("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n");
			}
	}


	function getCopyYard(){
		$time_start_new = microtime(true);
		echo "getCopyYard | ";
		$client = SERVICE_SERVER."/api.php";
        $method = 'getCopyYard'; //method

        $params = array('string0'=>'npks','string1'=>'12345','string2'=>'');
        $lastDateNota = 'bobi';
        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);

        $xml = xml2ary($response);
        $valResponse = $xml['document']['_c']['respon']['_v'];
       	$response = $this->split_character($response,4000);
        if($valResponse != 0){
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function ParsingCopyYard(){
		$time_start_new = microtime(true);
		echo "ParsingCopyYard | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getCopyYard' order by services_req_date desc";
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

		                $CONT_NO = $data['CONT_NO']['_v'];
		                $CONT_TYPE = $data['CONT_TYPE']['_v'];
		                $CONT_SIZE = $data['CONT_SIZE']['_v'];
		                $CONT_STATUS = $data['CONT_STATUS']['_v'];
		                $BLOCK = $data['BLOCK']['_v'];
		                $SLOT = $data['SLOT']['_v'];
		                $ROW = $data['ROW']['_v'];
		                $TIER = $data['TIER']['_v'];
						$NO_REQ = $data['NO_REQ']['_v'];

						$OWNER_CODE = $data['OWNER_CODE']['_v'];
						$OWNER_NAME = $data['OWNER_NAME']['_v'];
						$CONSIGNEE_NAME = $data['CONSIGNEE_NAME']['_v'];
						$CONSIGNEE_ADDRESS = $data['CONSIGNEE_ADDRESS']['_v'];
						$NPWP = $data['NPWP']['_v'];

		                $STACK_DATE = $data['STACK_DATE']['_v'];

		            	$insert = "INSERT INTO TP_CONTAINER (CONT_NO,CONT_TYPE,CONT_SIZE,CONT_STATUS,BLOCK,SLOT,\"ROW\",TIER,NO_REQ,STACK_DATE,OWNER_CODE, OWNER_NAME, CONSIGNEE_NAME, CONSIGNEE_ADDRESS, NPWP )
												VALUES ('".$CONT_NO."','".$CONT_TYPE."','".$CONT_SIZE."','".$CONT_STATUS."','".$BLOCK."','".$SLOT."', '".$ROW."', '".$TIER."', '".$NO_REQ."',
													TO_DATE('".$STACK_DATE."','MM/DD/YYYY HH24:MI:SS'), '".$OWNER_CODE."', '".$OWNER_NAME."', '".$CONSIGNEE_NAME."', '".$CONSIGNEE_ADDRESS."', '".$NPWP."')";
						$result = $this->db->query($insert);
						if($result){
							echo $i.", sukses insert | ".$CONT_NO." ".date('Y-m-d H:i:s')."<br>\n";
						}else{
							echo $i.", gagal insert | ".$CONT_NO." ".date('Y-m-d H:i:s')."<br>\n";
						}

		                $i++;
		            }
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					$this->db->query($updateServices);
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
    				echo "Generated ".$row['SERVICES_ID']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		        }
        	}
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }


	function getReceiving($branch = false){
		$time_start_new = microtime(true);
		echo "getReceiving | ";
		$client = SERVICE_SERVER."/api.php";
        $method = 'getReceiving'; //method

        $sqlLastDateNota = "select TGL from (
                            select TO_CHAR(REQUEST_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_RECEIVING_HDR  WHERE REQUEST_PAID_DATE IS NOT NULL AND REQUEST_BRANCH_ID = ".$branch." order by REQUEST_PAID_DATE DESC
                            )A where rownum =1";
        $resultLastDateNota = $this->db->query($sqlLastDateNota);
        $totalservice = $resultLastDateNota->num_rows();

        if($totalservice <= 0){
        	$lastDateNota = date('YmdHis',strtotime("-1 days"));
        }else{
        	$resultLastDateNota = $resultLastDateNota->result_array();
			$lastDateNota = $resultLastDateNota[0]['TGL'];
		}
		 //$lastDateNota = '20181220010101';

        $params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);
        $xml = xml2ary($response);
        $response = $this->split_character($response,4000);
        $valResponse = $xml['document']['_c']['respon']['_v'];
        if($valResponse != 0){
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function getReceivingNoreq($noreq){
		echo "getReceivingNoreq | ";
		$time_start_new = microtime(true);
		$client = SERVICE_SERVER."/api.php";
        $method = 'getReceivingNoreq'; //method

        $lastDateNota = $noreq;

        $params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);
        $xml = xml2ary($response);
        $response = $this->split_character($response,4000);
        $valResponse = $xml['document']['_c']['respon']['_v'];
        if($valResponse != 0){
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'getReceiving','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "getReceiving -> Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "getReceiving -> ".$xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function getReceivingNoreqApp($noreq){
		//echo "getReceivingNoreq | ";
		$time_start_new = microtime(true);
		$client = SERVICE_SERVER."/api.php";
				$method = 'getReceivingNoreq'; //method

				$lastDateNota = $noreq;

				$params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

				$ServiceID = date('YmdHis').rand(100,999);
				$response = $this->call_service($client, $method,$params);
				$xml = xml2ary($response);
				$response = $this->split_character($response,4000);
				$valResponse = $xml['document']['_c']['respon']['_v'];
				if($valResponse != 0){
					$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
										VALUES (".$ServiceID.",'getReceiving','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
					$detail = "getReceiving -> Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>";
					$return = array('success' => true, 'msg' => 'success', 'detail' => $detail);
					echo json_encode($return);
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
					$detail = "getReceiving -> ".$xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>";
					$return = array('success' => false, 'msg' => 'Tidak ada data', 'detail' => $detail);
					echo json_encode($return);
		}
	}

	function ParsingReceiving($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingReceiving | ";
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
						$response = str_replace('&','&amp;', $response);

		        $error = 0;
		        $xml = xml2ary($response);
		        $valResponse = $xml['document']['_c']['respon']['_v'];
		        if($valResponse != 0){
					$isDEPO = false;
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

						if($RECEIVING_DARI == 'LUAR'){
							$RECEIVING_DARI = 'DEPO';
							$isDEPO = true;
						}

		                $sqlcek = "SELECT REQUEST_NO, REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO='".$REQ_NO."' AND REQUEST_BRANCH_ID = ".$branch." ";
		                $resultCek = $this->db->query($sqlcek);
		                $totalcek = $resultCek->num_rows();

		                //if($totalcek <=0){
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



							if($totalcek <=0){
								$qlID = "SELECT SEQ_REQ_RECEIVING_HDR.NEXTVAL AS ID FROM DUAL";
								$resultID = $this->db->query($qlID)->result_array();
								$IDheader = $resultID[0]['ID'];

								$insertHDR = "INSERT INTO TX_REQ_RECEIVING_HDR (REQUEST_ID, REQUEST_NO, REQUEST_CONSIGNEE_ID, REQUEST_BRANCH_ID, REQUEST_NOTA, REQUEST_MARK, REQUEST_RECEIVING_DATE, REQUEST_NOTA_DATE, REQUEST_PAID_DATE, REQUEST_FROM, REQUEST_STATUS, REQUEST_DI) VALUES
											(".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",".$branch.",'".$NO_NOTA."','".$REQ_MARK."', TO_DATE('".$REQ_RECEIVING_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'), '".$RECEIVING_DARI."','1','".$DI."')";
								$resultHDR = $this->db->query($insertHDR);
							}else{
								$resultHDR = true;
								$resultCek = $resultCek->result_array();
		                        $IDheader = $resultCek[0]['REQUEST_ID'];
							}


		                    if($resultHDR){
		                        //echo $i.", sukses | ".$REQ_NO."<br>\n";
								$totalDTL = count($detail);

		                        $a = 0;
		                        while($a < $totalDTL){
									if($totalDTL == 1){
										$detailroot = $detail['_c'];
		                            }else{
										$detailroot = $detail[$a]['_c'];
									}

									$sqlcekdetilnya = "SELECT * FROM TX_REQ_RECEIVING_DTL WHERE REQUEST_HDR_ID='".$IDheader."' AND REQUEST_DTL_CONT = '".trim($detailroot['REQ_DTL_CONT']['_v'])."' ";
									$resultCekdetilNya = $this->db->query($sqlcekdetilnya);
									$totalcekDetilnya = $resultCekdetilNya->num_rows();

									if($totalcekDetilnya <=0){


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
										if($REQUEST_DTL_OWNER_CODE != '') {
											$sqlcekowner = "SELECT OWNER_CODE FROM TM_OWNER WHERE OWNER_CODE='".$REQUEST_DTL_OWNER_CODE."' AND OWNER_BRANCH_ID = ".$branch." ";
											$resultCekowner = $this->db->query($sqlcekowner);
											$totalcekowner = $resultCekowner->num_rows();
											if($totalcekowner <=0){
												$insertOwner = "INSERT INTO TM_OWNER (OWNER_CODE, OWNER_NAME, OWNER_BRANCH_ID) VALUES ('".$REQUEST_DTL_OWNER_CODE."','".$REQUEST_DTL_OWNER_NAME."',".$branch.")";
												$this->db->query($insertOwner);
											}
										}

										//insert history container
										$this->db->query("CALL ADD_HISTORY_CONTAINER(
													'".$REQ_DTL_CONT."',
													'".$REQ_NO."',
													'".$REQ_RECEIVING_DATE."',
													'".$REQ_DTL_SIZE."',
													'".$REQ_DTL_TYPE."',
													'".$REQ_DTL_CONT_STATUS."',
													NULL,
													NULL,
													NULL,
													NULL,
													NULL,
													3,
													'Request Receiving',
													NULL,
													NULL,
													".$branch.",
													'".$RECEIVING_DARI."',
													NULL)");

										//update master container
										$sqlcekmstcont = "SELECT CONTAINER_NO FROM TM_CONTAINER WHERE CONTAINER_NO='".$REQ_DTL_CONT."' AND CONTAINER_BRANCH_ID = ".$branch." ";
										$resultCekmstcont = $this->db->query($sqlcekmstcont);
										$totalcekmstcont = $resultCekmstcont->num_rows();
										if($totalcekmstcont >0){
											if($REQUEST_DTL_OWNER_CODE != '') {
												$updatecontowner = "UPDATE TM_CONTAINER SET CONTAINER_OWNER = '".$REQUEST_DTL_OWNER_CODE."' WHERE CONTAINER_NO='".$REQ_DTL_CONT."' AND CONTAINER_BRANCH_ID = ".$branch." ";
												$this->db->query($updatecontowner);
											}
										}

									}

		                            $a++;
		                        }

		                    }else{
		                        echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
		                        $error++;
		                    }
		                // }else{
		                //     echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
		                // }
		            $i++;
		            }
		            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					$this->db->query($updateServices);

					if($isDEPO){
						// $updateGateJobManager = curl_init(SERVICE_SERVER_NODEJS."/updateGateJobManager?branch=".$branch."");
						// curl_exec($updateGateJobManager );
						// curl_close($updateGateJobManager );
					}
		        }else{
		             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
        			 $this->db->query($updateServices);
        			 echo("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
		        }
			}
			//start call nodejs
				$updateGateJobManager = curl_init(SERVICE_SERVER_NODEJS."/updateGateJobManager?branch=".$branch."");
				curl_exec($updateGateJobManager);
				curl_close($updateGateJobManager);
				$updateReceiving = curl_init(SERVICE_SERVER_NODEJS."/updateReceiving?branch=".$branch."");
				curl_exec($updateReceiving);
				curl_close($updateReceiving);
			//end call nodejs
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n");
        }
    }

	function getStuffing($branch = false){
		$time_start_new = microtime(true);
		echo "getStuffing | ";
		$client = SERVICE_SERVER."/api.php";
		$method = 'getStuffing'; //method

		$sqlLastDateNota = "select TGL from (
							select TO_CHAR(STUFF_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_STUFF_HDR WHERE STUFF_PAID_DATE IS NOT NULL AND STUFF_ALIH_KAPAL = 'T' AND STUFF_BRANCH_ID = ".$branch." order by STUFF_PAID_DATE DESC
							)A where rownum =1";
        $resultLastDateNota = $this->db->query($sqlLastDateNota);
        $totalservice = $resultLastDateNota->num_rows();

        if($totalservice <= 0){
        	$lastDateNota = date('YmdHis',strtotime("-5 days"));
        }else{
        	$resultLastDateNota = $resultLastDateNota->result_array();
        	$lastDateNota = $resultLastDateNota[0]['TGL'];
        }

		//$lastDateNota = '20181220010101';
		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

		$ServiceID = date('YmdHis').rand(100,999);
		$response = $this->call_service($client, $method,$params);

		$xml = xml2ary($response);
        $valResponse = $xml['document']['_c']['respon']['_v'];
        $response = $this->split_character($response,4000);
        if($valResponse != 0){
			$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
								VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo "getStuffing -> Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo "getStuffing -> ".$xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function getStuffingNoreq($noreq){
		$time_start_new = microtime(true);
		echo "getStuffingNoreq | ";
		$client = SERVICE_SERVER."/api.php";
		$method = 'getStuffingNoreq'; //method

		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$noreq);

		$ServiceID = date('YmdHis').rand(100,999);
		$response = $this->call_service($client, $method,$params);

		$xml = xml2ary($response);
        $valResponse = $xml['document']['_c']['respon']['_v'];
        $response = $this->split_character($response,4000);
        if($valResponse != 0){
			$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
								VALUES (".$ServiceID.",'getStuffing','".$noreq."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function getReceivingFromTPK($branch = false){
		$time_start_new = microtime(true);
		echo "getReceivingFromTPK | ";
		$client = SERVICE_SERVER."/api.php";
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
					echo "getReceivingFromTPK -> Tidak Ada response | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
					continue;
			    }

        		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$noReqReciving);
				$ServiceID = date('YmdHis').rand(100,999);
				$response = $this->call_service($client, $method,$params);
				$xml = xml2ary($response);
				$response = $this->split_character($response,4000);
		        $valResponse = $xml['document']['_c']['respon']['_v'];
		        if($valResponse != 0){
					$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML =".$response." WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
					 $this->db->query($updateServices);
					 $time_end = microtime(true);
					 $SQLCEK = ($time_end - $time_start_new);
					echo "Sukses Update | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
				}else{
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
					echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
				}
			}
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function getReceivingFromTPKNoreq($noreq){
		$time_start_new = microtime(true);
		echo "getReceivingFromTPKNoreq | ";
		$client = SERVICE_SERVER."/api.php";
		$method = 'getReceivingFromTPK';
		$noReqReciving = $noreq;
		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$noReqReciving);
		$ServiceID = date('YmdHis').rand(100,999);
		$response = $this->call_service($client, $method,$params);
		$xml = xml2ary($response);

		$response = $this->split_character($response,4000);
		$valResponse = $xml['document']['_c']['respon']['_v'];
		$ServiceID = date('YmdHis').rand(100,999);

		if($valResponse != 0){
			$ServiceID = date('YmdHis').rand(100,999);
	        // $returnXML = $this->split_character($returnXML,4000);

	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS, SERVICES_RESP_XML)
								VALUES (".$ServiceID.",'".$method."','".$noreq."','1',".$response.")";
			$insert = $this->db->query($insertServices);
			if($insert){
				echo "success";
			}else{
				echo "failed";
			}
		}
		$time_end = microtime(true);
		$SQLCEK = ($time_end - $time_start_new);
		echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
	}


	function getGateInFromTPK($branch = false){
		$time_start_new = microtime(true);
		echo "getGateInFromTPK | ";
		$client = SERVICE_SERVER."/api.php";
		$method = 'getGateInFromTPK';

		$sqlservices = "SELECT * FROM (
			select SERVICES_ID, SERVICES_REQ_XML,  TO_CHAR(services_req_date,'MM/DD/YYYY HH24:MI:SS') TGL_SERVICE from TX_SERVICES where services_status = '0' AND services_method = 'getGateInFromTPK' order by SERVICES_DATE_GENERATE ASC
			)A WHERE rownum <=100";
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
					echo "getGateInFromTPK -> Tidak Ada response | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
					continue;
			    }

        		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$noReqReciving);
				$ServiceID = date('YmdHis').rand(100,999);
				$response = $this->call_service($client, $method,$params);
				$xml = xml2ary($response);
				$response = $this->split_character($response,4000);
		        $valResponse = $xml['document']['_c']['respon']['_v'];
		        if($valResponse != 0){
					$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML =".$response." WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
					 $this->db->query($updateServices);
					echo "Sukses Update | ".$row['SERVICES_ID']." | ".date('Y-m-d H:i:s')."<br>\n";
				}else{
					$updateServices = "UPDATE TX_SERVICES SET SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
					$this->db->query($updateServices);
					echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')."<br>\n";
				}
			}
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function getGateOutToTPK($branch = false){
		$time_start_new = microtime(true);
		echo "getGateOutToTPK | ";
		$client = SERVICE_SERVER."/api.php";
		$method = 'getGateOutToTPK';

		$sqlservices = "SELECT * FROM (
			select SERVICES_ID, SERVICES_REQ_XML, TO_CHAR(services_req_date,'MM/DD/YYYY HH24:MI:SS') TGL_SERVICE from TX_SERVICES where services_status = '0' AND services_method = 'getGateOutToTPK' order by services_req_date ASC
			)A WHERE rownum <=2";
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
					echo "getGateOutToTPK -> Tidak Ada response | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
					continue;
			    }

        		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$noReqReciving);
				$ServiceID = date('YmdHis').rand(100,999);
				$response = $this->call_service($client, $method,$params);
				$xml = xml2ary($response);
				$response = $this->split_character($response,4000);
		        $valResponse = $xml['document']['_c']['respon']['_v'];
		        if($valResponse != 0){
					$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML =".$response." WHERE SERVICES_ID = '".$row['SERVICES_ID']."'  AND SERVICES_STATUS = '0'";
					 $this->db->query($updateServices);
					echo "Sukses Update | ".$ServiceID." | ".date('Y-m-d H:i:s')."<br>\n";
				}else{
					// echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')."<br>\n";
				}
			}
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function ParsingGateOutToTPK($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingGateOutToTPK | ";
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

						$sqlcekdetilnya = "SELECT * FROM TX_GATE WHERE GATE_NOREQ='".$REQ_NO."' AND GATE_CONT = '".$NO_CONT."' ";
						$resultCekdetilNya = $this->db->query($sqlcekdetilnya);
						$totalcekDetilnya = $resultCekdetilNya->num_rows();

						if($totalcekDetilnya <= 0) {

							$insertGATEIN = "INSERT INTO TX_GATE (GATE_NOREQ, GATE_CONT,GATE_CONT_STATUS, GATE_TRUCK_NO,  GATE_ORIGIN, GATE_CREATE_DATE, GATE_STATUS, GATE_ACTIVITY, GATE_STACK_STATUS, GATE_FL_SEND, GATE_BRANCH_ID) VALUES
										('".$REQ_NO."','".$NO_CONT."','".$STATUS_CONT."','".$TRUK."','".$DELIVERY_TO."', TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS'),'1', '".$ACTIVITY."','1', '9', ".$branch.")";
							$resultHDR = $this->db->query($insertGATEIN);

							/*$qlIDGATE = "SELECT SEQ_TX_GATE.NEXTVAL AS ID FROM DUAL";
							$resultIDGATE = $this->db->query($qlIDGATE)->result_array();
							$GATE_ID = $resultIDGATE[0]['ID'];*/

							$insertGATEOUT = "INSERT INTO TX_GATE (GATE_NOREQ, GATE_CONT,GATE_CONT_STATUS, GATE_TRUCK_NO,  GATE_ORIGIN, GATE_CREATE_DATE, GATE_STATUS, GATE_ACTIVITY, GATE_STACK_STATUS, GATE_FL_SEND, GATE_BRANCH_ID) VALUES
										('".$REQ_NO."','".$NO_CONT."','".$STATUS_CONT."','".$TRUK."','".$DELIVERY_TO."', TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS'),'3', '".$ACTIVITY."','2', '9', ".$branch.")";
							$resultHDR = $this->db->query($insertGATEOUT);


							//get ID hdr Delivery
							$sqlIDHDR = "SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR  WHERE REQ_NO = '".$REQ_NO."' AND REQ_BRANCH_ID = ".$branch."";
							$resultIDHDR = $this->db->query($sqlIDHDR)->result_array();
							$HDR_ID = $resultIDHDR[0]['REQ_ID'];

							//update request delivery dtl
							$updateReqDelDTL = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_STATUS = '2', REQ_DTL_ACTIVE = 'T' WHERE REQ_HDR_ID = '".$HDR_ID."' AND REQ_DTL_CONT = '".$NO_CONT."'  ";
							$this->db->query($updateReqDelDTL);

							//update request delivery hdr
							//$updateReqDelHDR = "UPDATE TX_REQ_DELIVERY_HDR SET REQUEST_STATUS = '2' WHERE REQ_NO = '".$REQ_NO."' AND REQ_BRANCH_ID = ".$branch." ";
							//$this->db->query($updateReqDelHDR);

							$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = '".$HDR_ID."' AND  REQ_BRANCH_ID = ".$branch." ")->row()->JML;
							$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = '".$HDR_ID."' AND  REQ_BRANCH_ID = ".$branch." AND REQ_DTL_ACTIVE = 'T' ")->row()->JML;

							if($cek_tot_dtl == $cek_tot_dtl_T){
								$updateStuffHDR = "UPDATE TX_REQ_DELIVERY_HDR SET REQUEST_STATUS = '2' WHERE REQ_NO = '".$REQ_NO."' AND REQ_BRANCH_ID = ".$branch." ";
								$this->db->query($updateStuffHDR);
							}

							//get cont size, type
							$sql_getCont = "SELECT  REAL_YARD_CONT_STATUS, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE FROM TX_REAL_YARD WHERE REAL_YARD_ID = (SELECT MAX(REAL_YARD_ID) FROM TX_REAL_YARD WHERE REAL_YARD_CONT = '".$NO_CONT."' AND REAL_YARD_BRANCH_ID = ".$branch.")";
							$resultgetCont = $this->db->query($sql_getCont)->result_array();

							//Update yard menjadi unstacking
							// $result = $this->db->query("INSERT INTO  TX_REAL_YARD (REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, REAL_YARD_CREATE_BY,REAL_YARD_CREATE_DATE,REAL_YARD_TYPE, REAL_YARD_STATUS, REAL_YARD_REQ_NO,REAL_YARD_FL_SEND, REAL_YARD_CONT_STATUS,REAL_YARD_MARK, REAL_YARD_CONT_SIZE,REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY,REAL_YARD_ACTIVITY)
							// 	(SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT,REAL_YARD_NO,REAL_YARD_CREATE_BY, TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS') REAL_YARD_CREATE_DATE, REAL_YARD_TYPE, '2' REAL_YARD_STATUS, '".$REQ_NO."' REAL_YARD_REQ_NO, '9' REAL_YARD_FL_SEND, REAL_YARD_CONT_STATUS, REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY, REAL_YARD_ACTIVITY FROM TX_REAL_YARD WHERE REAL_YARD_ID = (SELECT MAX(REAL_YARD_ID) FROM TX_REAL_YARD WHERE REAL_YARD_CONT = '".$NO_CONT."' AND REAL_YARD_BRANCH_ID = ".$branch."))");


							// get data container yg stacking sekarang.
							$cont_old = $this->db->query("SELECT REAL_YARD_YBC_ID, REAL_YARD_TIER, REAL_YARD_BRANCH_ID, REAL_YARD_CONT, REAL_YARD_NO, '' AS REAL_YARD_CREATE_BY, REAL_YARD_TYPE, 2 AS REAL_YARD_STATUS, '0' REAL_YARD_USED, REAL_YARD_ACTIVITY,
							REAL_YARD_REQ_NO, REAL_YARD_CONT_STATUS, TO_CHAR(REAL_YARD_MARK) REAL_YARD_MARK, REAL_YARD_CONT_SIZE, REAL_YARD_CONT_TYPE, REAL_YARD_VOY, REAL_YARD_VESSEL_CODE, REAL_YARD_VESSEL_NAME, REAL_YARD_COMMODITY, '9' REAL_YARD_FL_SEND, TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS') REAL_YARD_CREATE_DATE
							FROM TX_REAL_YARD
							WHERE REAL_YARD_BRANCH_ID = ".$branch." AND REAL_YARD_CONT = '".$NO_CONT."' AND REAL_YARD_STATUS = 1 AND REAL_YARD_USED = 1");

							if($cont_old->num_rows() > 0){
								foreach ($cont_old->result_array() as $val) {
								$this->db->insert('TX_REAL_YARD',$val);
								}
								//UPDATE DATA LAMA MENJADI TIDAK AKTIF
								$this->db->query("UPDATE TX_REAL_YARD SET REAL_YARD_USED = '0' WHERE REAL_YARD_BRANCH_ID = ".$branch." AND REAL_YARD_CONT = '".$NO_CONT."' AND REAL_YARD_STATUS = 1 AND REAL_YARD_USED = 1");
							}

							//insert history container
							$this->db->query("CALL ADD_HISTORY_CONTAINER(
										'".$NO_CONT."',
										'".$REQ_NO."',
										'".$TGL_GATE."',
										'".$resultgetCont[0]['REAL_YARD_CONT_SIZE']."',
										'".$resultgetCont[0]['REAL_YARD_CONT_TYPE']."',
										'".$resultgetCont[0]['REAL_YARD_CONT_STATUS']."',
										NULL,
										NULL,
										NULL,
										NULL,
										NULL,
										3,
										'GATE OUT TO TPK',
										NULL,
										NULL,
										".$branch.",
										NULL,
										NULL)");
						}

		            $i++;
		            }

		            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					$this->db->query($updateServices);
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
        			echo "Data Inserted | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		        }else{
		             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					 $this->db->query($updateServices);
					 $time_end = microtime(true);
					 $SQLCEK = ($time_end - $time_start_new);
        			 echo("tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n");
		        }
       	 	}
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n");
        }
    }

	function ParsingGateInFromTPK($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingGateInFromTPK | ";
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

						$sqlcek = "SELECT * FROM TX_GATE WHERE GATE_NOREQ='".$REQ_NO."' AND GATE_CONT = '".$NO_CONT."' ";
						$resultCek = $this->db->query($sqlcek);
						$totalcek = $resultCek->num_rows();

						if($totalcek <= 0) {

							$insertGATEIN = "INSERT INTO TX_GATE (GATE_NOREQ, GATE_CONT,GATE_CONT_SIZE, GATE_CONT_TYPE, GATE_CONT_STATUS, GATE_TRUCK_NO, GATE_MARK_SERVICES, GATE_ORIGIN, GATE_CREATE_DATE, GATE_STATUS, GATE_ACTIVITY, GATE_STACK_STATUS, GATE_FL_SEND, GATE_BRANCH_ID) VALUES
										('".$REQ_NO."','".$NO_CONT."','".$CONT_SIZE."','".$CONT_TYPE."','".$STATUS_CONT."','".$TRUK."','".$PERALIHAN."','".$ORIGIN."', TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS'),'1', '".$ACTIVITY."','0', '9', ".$branch.")";
							$resultHDR = $this->db->query($insertGATEIN);

							$insertGATEOUT = "INSERT INTO TX_GATE (GATE_NOREQ, GATE_CONT,GATE_CONT_SIZE, GATE_CONT_TYPE,GATE_CONT_STATUS, GATE_TRUCK_NO, GATE_MARK_SERVICES, GATE_ORIGIN, GATE_CREATE_DATE, GATE_STATUS, GATE_ACTIVITY, GATE_STACK_STATUS, GATE_FL_SEND, GATE_BRANCH_ID) VALUES
										('".$REQ_NO."','".$NO_CONT."','".$CONT_SIZE."','".$CONT_TYPE."','".$STATUS_CONT."','".$TRUK."','".$PERALIHAN."','".$ORIGIN."', TO_DATE('".$TGL_GATE."','MM/DD/YYYY HH24:MI:SS'),'3', '".$ACTIVITY."','0', '9', ".$branch.")";
							$resultHDR = $this->db->query($insertGATEOUT);

							//get ID hdr receiving
							$sqlIDHDR = "SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR  WHERE REQUEST_NO = '".$REQ_NO."' AND REQUEST_BRANCH_ID = ".$branch." ";
							$resultIDHDR = $this->db->query($sqlIDHDR)->result_array();
							$HDR_ID = $resultIDHDR[0]['REQUEST_ID'];

							//update request receiving dtl
							$updateReqDelDTL = "UPDATE TX_REQ_RECEIVING_DTL SET REQUEST_DTL_STATUS = '2' WHERE REQUEST_DTL_CONT = '".$NO_CONT."' AND REQUEST_HDR_ID = '".$HDR_ID."'  ";
							$this->db->query($updateReqDelDTL);

							$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_RECEIVING_DTL WHERE REQUEST_HDR_ID = (SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO = '".$REQ_NO."' AND REQUEST_BRANCH_ID = ".$branch.")")->row()->JML;
							$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_RECEIVING_DTL WHERE REQUEST_HDR_ID = (SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO = '".$REQ_NO."' AND REQUEST_BRANCH_ID = ".$branch.") AND REQUEST_DTL_STATUS = '2' ")->row()->JML;

							if($cek_tot_dtl == $cek_tot_dtl_T){
								//update request receiving hdr
								$updateRecHDR = "UPDATE TX_REQ_RECEIVING_HDR SET REQUEST_STATUS = '2' WHERE REQUEST_NO = '".$REQ_NO."' AND REQUEST_BRANCH_ID = ".$branch." ";
								$this->db->query($updateRecHDR);
							}


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
										NULL,
										".$branch.",
										NULL,
										NULL)");
						}

		            $i++;
		            }
		            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					$this->db->query($updateServices);
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
        			echo "Data Inserted | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		        }else{
		             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					 $this->db->query($updateServices);
					 $time_end = microtime(true);
					 $SQLCEK = ($time_end - $time_start_new);
        			 echo("tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n");
		        }
       	 	}
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n");
        }
    }

	function ParsingReceivingFromTPK($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingReceivingFromTPK | ";
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
						$response = str_replace('&','&amp;', $response);

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

		                $sqlcek = "SELECT REQUEST_NO,REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO='".$REQ_NO."' AND REQUEST_BRANCH_ID = ".$branch."";
		                $resultCek = $this->db->query($sqlcek);
		                $totalcek = $resultCek->num_rows();

		                // if($totalcek <=0){
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

							if($totalcek <=0){
								$qlID = "SELECT SEQ_REQ_RECEIVING_HDR.NEXTVAL AS ID FROM DUAL";
								$resultID = $this->db->query($qlID)->result_array();
								$IDheader = $resultID[0]['ID'];

								$insertHDR = "INSERT INTO TX_REQ_RECEIVING_HDR (REQUEST_ID, REQUEST_NO, REQUEST_CONSIGNEE_ID, REQUEST_BRANCH_ID, REQUEST_NOTA, REQUEST_MARK, REQUEST_RECEIVING_DATE,  REQUEST_FROM, REQUEST_STATUS) VALUES
											(".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",".$branch.",'".$NO_NOTA."','".$PERALIHAN."', TO_DATE('".$REQ_RECEIVING_DATE."','MM/DD/YYYY HH24:MI:SS'),  '".$RECEIVING_DARI."','1')";
								$resultHDR = $this->db->query($insertHDR);
							}else{
								$resultHDR = true;
								$resultCek = $resultCek->result_array();
								$IDheader = $resultCek[0]['REQUEST_ID'];
							}

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

									$sqlcekdetilnya = "SELECT * FROM TX_REQ_RECEIVING_DTL WHERE REQUEST_HDR_ID='".$IDheader."' AND REQUEST_DTL_CONT = '".$REQ_DTL_CONT."' ";
									$resultCekdetilNya = $this->db->query($sqlcekdetilnya);
									$totalcekDetilnya = $resultCekdetilNya->num_rows();

									if($totalcekDetilnya <= 0) {

										$ownerCode = '';
										$ownerName = '';
										$sqlcekmstcont = "SELECT B.OWNER_CODE, B.OWNER_NAME FROM TM_CONTAINER A JOIN TM_OWNER B ON B.OWNER_CODE = A.CONTAINER_OWNER WHERE CONTAINER_NO = '".$REQ_DTL_CONT."' AND A.CONTAINER_BRANCH_ID = ".$branch;
										$resultCekmstcont = $this->db->query($sqlcekmstcont);
										$totalcekmstcont = $resultCekmstcont->num_rows();
										if($totalcekmstcont >0){
											$arrData = $resultCekmstcont->row_array();
											$ownerCode = $arrData['OWNER_CODE'];
											$ownerName =  $arrData['OWNER_NAME'];
										}

										$insertDTL = "INSERT INTO TX_REQ_RECEIVING_DTL (REQUEST_DTL_ID, REQUEST_HDR_ID, REQUEST_DTL_CONT, REQUEST_DTL_CONT_STATUS, REQUEST_DTL_DANGER, REQUEST_DTL_CONT_SIZE, REQUEST_DTL_CONT_TYPE, REQUEST_DTL_COMMODITY, REQUEST_DTL_OWNER_CODE, REQUEST_DTL_OWNER_NAME)
													VALUES (SEQ_REQ_RECEIVING_DTL.NEXTVAL, ".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_STATUS."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."','".$ownerCode."','".$ownerName."')";
										$resultDtl = $this->db->query($insertDTL);
										if($resultDtl){
											echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
										}else{
											echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
										}

										//insert history container
										$this->db->query("CALL ADD_HISTORY_CONTAINER(
													'".$REQ_DTL_CONT."',
													'".$REQ_NO."',
													'".$REQ_RECEIVING_DATE."',
													'".$REQ_DTL_SIZE."',
													'".$REQ_DTL_TYPE."',
													'".$REQ_DTL_CONT_STATUS."',
													NULL,
													NULL,
													NULL,
													NULL,
													NULL,
													3,
													'Request Receiving',
													NULL,
													NULL,
													".$branch.",
													'TPK',
													NULL)");

									}

		                            $a++;
		                        }
		                    }else{
		                        echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
		                        $error++;
		                    }
		                // }else{
		                //     echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
		                // }
		            $i++;
		            }
		            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
        			$this->db->query($updateServices);
		        }else{
		             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
        			 $this->db->query($updateServices);
        			 echo "tidak ada data | ".date('Y-m-d H:i:s')."<br>\n";
		        }

				}
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
				echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

	function ParsingStuffing($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingStuffing | ";
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
					$response = str_replace('&','&amp;', $response);
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

									$sqlcek = "SELECT STUFF_NO, STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO='".$REQ_NO."' AND STUFF_BRANCH_ID = ".$branch."";
									$resultCek = $this->db->query($sqlcek);
									$totalcek =  $resultCek->num_rows();

									// if($totalcek <=0){
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

											if($totalcek <=0){
												$qlID = "SELECT SEQ_TX_STUFF_HDR.NEXTVAL AS ID FROM DUAL";
												$resultID = $this->db->query($qlID)->result_array();
												$IDheader = $resultID[0]['ID'];

												$STUFF_ORIGIN = 'INTERNAL';
												if($STUFFING_DARI == 'TPK')
													$STUFF_ORIGIN = 'TPK';

												$insertHDR = "INSERT INTO TX_REQ_STUFF_HDR (STUFF_ID, STUFF_NO, STUFF_CONSIGNEE_ID, STUFF_BRANCH_ID, STUFF_CREATE_DATE, STUFF_NOTA_DATE, STUFF_NOTA_NO, STUFF_PAID_DATE, STUFF_NO_BOOKING, STUFF_NO_UKK, STUFF_NOREQ_RECEIVING, STUFF_EXTEND_FROM, STUFF_EXTEND_LOOP, STUFF_ORIGIN, STUFF_STATUS) VALUES
																		(".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",".$branch.", TO_DATE('".$REQ_STUFF_DATE."','MM/DD/YYYY HH24:MI:SS'),
																		TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),'".$NO_NOTA."',TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'), '".$NO_BOOKING."', '".$NO_UKK."','".$NO_REQUEST_RECEIVING."', '".$PERP_DARI."', ".$PERP_KE.",'".$STUFF_ORIGIN."','1')";
												$resultHDR = $this->db->query($insertHDR);
											}else{
												$resultHDR = true;
												$resultCek = $resultCek->result_array();
												$IDheader = $resultCek[0]['STUFF_ID'];
											}

											if($resultHDR){

													//cek detil container ada tidak dari TPK
													$contFromTPK = 'T';
													$totalDTL = count($detail);
													$b = 0;
													$arrTpk = array();
													$ketPerpanjangan = 'Request Stuffing';

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

													if($PERP_DARI !=''){
														$ketPerpanjangan = 'Perpanjangan Stuffing';
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

															$sqlcekdetilnya = "SELECT * FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID='".$IDheader."' AND STUFF_DTL_CONT = '".$REQ_DTL_CONT."' ";
															$resultCekdetilNya = $this->db->query($sqlcekdetilnya);
															$totalcekDetilnya = $resultCekdetilNya->num_rows();

															if($totalcekDetilnya <= 0 ){

																if(($STUFFING_DARI == 'TPK') && ($PERP_DARI =='') && ($contFromTPK == 'Y')){
																	$ServiceID = date('YmdHis').rand(100,999);
																	$method = 'getGateInFromTPK'; //method
																	$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
																						VALUES (".$ServiceID.",'".$method."','".$NO_REQUEST_RECEIVING."~".$REQ_DTL_CONT."','0')";
																	// $insert = $this->db->query($insertServices);
																}

																//cek counter container
																$cont_count = '';
																$sqlCekCounterCont = "SELECT CONTAINER_COUNTER FROM TM_CONTAINER WHERE CONTAINER_BRANCH_ID = ".$branch." AND CONTAINER_NO = '".$REQ_DTL_CONT."'";
																$resultCekCounterCont= $this->db->query($sqlCekCounterCont)->row_array();
																$totalCekCounterCont = $this->db->query($sqlCekCounterCont)->num_rows();

																if($totalCekCounterCont > 0) {
																	$cont_count = $resultCekCounterCont['CONTAINER_COUNTER'];
																}
																else{
																	$cont_count = 1;
																}

																$insertDTL = "INSERT INTO TX_REQ_STUFF_DTL (STUFF_DTL_HDR_ID, STUFF_DTL_CONT, STUFF_DTL_CONT_HAZARD, STUFF_DTL_CONT_SIZE, STUFF_DTL_CONT_TYPE, STUFF_DTL_COMMODITY, STUFF_DTL_REMARK_SP2, STUFF_DTL_ORIGIN,STUFF_DTL_START_STUFF_PLAN,STUFF_DTL_END_STUFF_PLAN, STUFF_DTL_CONT_STATUS, STUFF_DTL_COUNTER)
																							VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."','".$REQ_DTL_REMARK_SP2."','".$REQ_DTL_ORIGIN."', TO_DATE('".$STUFF_DTL_START_STUFF_PLAN."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$STUFF_DTL_END_STUFF_PLAN."','MM/DD/YYYY HH24:MI:SS'), 'MTY', ".$cont_count.")";
																$resultDtl = $this->db->query($insertDTL);
																if($resultDtl){
																		echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
																}else{
																		echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
																}

																if($PERP_DARI !=""){
																	$updateStuffTDL = "UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_ACTIVE = 'T', STUFF_DTL_STATUS = '2' WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch.") AND STUFF_DTL_CONT = '".$REQ_DTL_CONT."'  ";
																	$this->db->query($updateStuffTDL);

																	$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch.")")->row()->JML;
																	$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch.") AND STUFF_DTL_ACTIVE = 'T' ")->row()->JML;

																	if($cek_tot_dtl == $cek_tot_dtl_T){
																		$updateStuffHDR = "UPDATE TX_REQ_STUFF_HDR SET STUFF_STATUS = '2' WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch." ";
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
																	'".$ketPerpanjangan."',
																	NULL,
																	NULL,
																	".$branch.",
																	'".$REQ_DTL_ORIGIN."',
																	NULL)");
														}

															$a++;
													}



											}else{
													echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
													$error++;
											}
									// }else{
									// 		echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
									// }
							$i++;
							}
							$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
							$this->db->query($updateServices);

					}else{
							 $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
						$this->db->query($updateServices);
						 echo("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
					}
				}
				//start call nodejs
					$updateStuffingProcess = curl_init(SERVICE_SERVER_NODEJS."/updateStuffingProcess?branch=".$branch."");
					curl_exec($updateStuffingProcess);
					curl_close($updateStuffingProcess);
					// $updateGateJobTruckManager = curl_init(SERVICE_SERVER_NODEJS."/updateGateJobTruckManager?branch=".$branch."");
					// curl_exec($updateGateJobTruckManager );
					// curl_close($updateGateJobTruckManager );
				//end call nodejs
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
				echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
			}else{
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
				echo "tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
			}
	}

	function generateRealStuffing($branch = false){
		$time_start_new = microtime(true);
		echo "generateRealStuffing | ";
		$sqlgetStuff = "SELECT * FROM (SELECT D.REAL_YARD_YBC_ID YBC_ID, A.REAL_STUFF_ID, A.REAL_STUFF_CONT NO_CONT, B.STUFF_NO NO_REQUEST,
					(SELECT STUFF_DTL_COMMODITY FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) COMMODITY,
					(SELECT STUFF_DTL_CONT_HAZARD FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) HZ,
					(SELECT STUFF_DTL_CONT_SIZE FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) CONT_SIZE,
					(SELECT STUFF_DTL_CONT_TYPE FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) CONT_TYPE,
					TO_CHAR(B.STUFF_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQUEST, B.STUFF_NO_BOOKING NO_BOOKING, B.STUFF_NO_UKK NO_UKK,
					A.REAL_STUFF_BY, TO_CHAR(A.REAL_STUFF_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REALISASI, A.REAL_STUFF_MECHANIC_TOOLS ALAT,
					(SELECT STUFF_DTL_REMARK_SP2 FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) REMARK_SP2,
					CASE WHEN U.USER_ID_NBS IS NOT NULL THEN TO_NUMBER(U.USER_ID_NBS) ELSE A.REAL_STUFF_BY END AS ID_USER
					FROM TX_REAL_STUFF A
					INNER JOIN TX_REQ_STUFF_HDR B ON B.STUFF_ID = A.REAL_STUFF_HDR_ID
					INNER JOIN TX_REAL_YARD D ON D.REAL_YARD_CONT = A.REAL_STUFF_CONT AND D.REAL_YARD_STATUS = '1'
					LEFT JOIN TM_USER U ON U.USER_ID = A.REAL_STUFF_BY
					WHERE A.REAL_STUFF_STATUS = 1
					AND A.REAL_STUFF_FL_SEND = 0
					AND D.REAL_YARD_USED = 1
					ORDER BY A.REAL_STUFF_DATE ASC)Z WHERE ROWNUM <= 1";

    	$resultservices = $this->db->query($sqlgetStuff);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){

					$IdData = "";
					$arrData = '';
					$i = 1;
        	foreach($resultservices->result_array() as $row){
        		$IdData .= $row['REAL_STUFF_ID'].",";

						if($i < $totalservice){
							$arrData .= $row['NO_CONT'].'~';
							$arrData .= $row['NO_REQUEST'].'~';
							$arrData .= $row['COMMODITY'].'~';
							$arrData .= $row['HZ'].'~';
							$arrData .= $row['CONT_SIZE'].'~';
							$arrData .= $row['CONT_TYPE'].'~';
							$arrData .= $row['TGL_REQUEST'].'~';
							$arrData .= $row['NO_BOOKING'].'~';
							$arrData .= $row['NO_UKK'].'~';
							$arrData .= $row['ID_USER'].'~';
							$arrData .= $row['YBC_ID'].'~';
							$arrData .= $row['TGL_REALISASI'].'~';
							$arrData .= $row['REMARK_SP2'].'~';
							$arrData .= $row['ALAT'];
							$arrData .= '#';
					}
					else{
						$arrData .= $row['NO_CONT'].'~';
						$arrData .= $row['NO_REQUEST'].'~';
						$arrData .= $row['COMMODITY'].'~';
						$arrData .= $row['HZ'].'~';
						$arrData .= $row['CONT_SIZE'].'~';
						$arrData .= $row['CONT_TYPE'].'~';
						$arrData .= $row['TGL_REQUEST'].'~';
						$arrData .= $row['NO_BOOKING'].'~';
						$arrData .= $row['NO_UKK'].'~';
						$arrData .= $row['ID_USER'].'~';
						$arrData .= $row['YBC_ID'].'~';
						$arrData .= $row['TGL_REALISASI'].'~';
						$arrData .= $row['REMARK_SP2'].'~';
						$arrData .= $row['ALAT'];
					}
					$i++;
        	}

			$IdData = substr($IdData, 0,-1);

	        $ServiceID = date('YmdHis').rand(100,999);
	        $method = 'setRealStuffing'; //method
			$response = $this->split_character($arrData,4000);
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."',$response,'0')";
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxRealStat = "UPDATE TX_REAL_STUFF SET REAL_STUFF_FL_SEND = '1' WHERE REAL_STUFF_ID IN (".$IdData.") AND REAL_STUFF_FL_SEND = '0' ";
				$this->db->query($updateTxRealStat);
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
	        }
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
	}

	function setRealStuffing($branch = false){
		$time_start_new = microtime(true);
		echo "setRealStuffing | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setRealStuffing' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/api.php";
			    $method = 'setRealStuffing'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);
			    $response = $this->split_character($response,4000);
			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = ".$response."  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
				$this->db->query($updateServices);
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
        		echo "ok | ".$row['SERVICES_ID']." | time execute : ".round($SQLCEK,2)."<br>\n";
			}
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
    }

    function ParsingRealStuffing($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingRealStuffing | ";
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

		            	$updateTxGate = "UPDATE TX_REAL_STUFF SET REAL_STUFF_FL_SEND = '9', REAL_STUFF_MARK = '".$KET."' WHERE REAL_STUFF_NOREQ = '".$NO_REQUEST."' AND REAL_STUFF_BRANCH_ID = ".$branch." AND REAL_STUFF_CONT = '".$NO_CONTAINER."' AND REAL_STUFF_FL_SEND = '1' ";
    					$this->db->query($updateTxGate);
		                $i++;
		            }
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					$this->db->query($updateServices);
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
    				echo "Generated ".$row['SERVICES_ID']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		        }
        	}
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }



		function generateRealStripping($branch = false){
			$time_start_new = microtime(true);
			echo "generateRealStripping | ";
				$sqlgetStrip = "SELECT * FROM (SELECT * FROM (
					SELECT MAX(C.REAL_YARD_YBC_ID) over () as MAX_ID, A.REAL_STRIP_ID, A.REAL_STRIP_CONT NO_CONT, A.REAL_STRIP_NOREQ NO_REQUEST, TO_CHAR(B.STRIP_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQUEST,  A.REAL_STRIP_MECHANIC_TOOLS ALAT, TO_CHAR(A.REAL_STRIP_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REALISASI,A.REAL_STRIP_BY, C.REAL_YARD_YBC_ID YBC_ID, A.REAL_STRIP_MARK, A.REAL_STRIP_BACKDATE,
					CASE WHEN U.USER_ID_NBS IS NOT NULL THEN TO_NUMBER(U.USER_ID_NBS) ELSE A.REAL_STRIP_BY END AS ID_USER
					FROM TX_REAL_STRIP A
					INNER JOIN TX_REQ_STRIP_HDR B ON B.STRIP_ID = A.REAL_STRIP_HDR_ID
					INNER JOIN TX_REAL_YARD C ON C.REAL_YARD_CONT = A.REAL_STRIP_CONT AND C.REAL_YARD_STATUS = '1'
					LEFT JOIN TM_USER U ON U.USER_ID = A.REAL_STRIP_BY
					WHERE A.REAL_STRIP_FL_SEND = 0 AND A.REAL_STRIP_STATUS = 2 AND A.REAl_STRIP_BRANCH_ID = ".$branch."
					ORDER BY A.REAL_STRIP_DATE ASC
					) Z WHERE Z.MAX_ID = Z.YBC_ID
				)ZZ  WHERE  rownum <=1 ";
		// echo $sqlgetStrip;die();
    	$resultservices = $this->db->query($sqlgetStrip);
		$totalservice = $resultservices->num_rows();

        if($totalservice > 0){
			$IdData = "";
			$arrData = '';
			$i = 1;
        	foreach($resultservices->result_array() as $row){
        		$IdData .= $row['REAL_STRIP_ID'].",";

						if($i < $totalservice){
							$arrData .= $row['NO_CONT'].'~';
							$arrData .= $row['NO_REQUEST'].'~';
							$arrData .= $row['TGL_REQUEST'].'~';
							$arrData .= $row['ID_USER'].'~';
							$arrData .= $row['YBC_ID'].'~';
							$arrData .= $row['TGL_REALISASI'].'~';
							$arrData .= $row['ALAT'].'~';
							$arrData .= $row['REAL_STRIP_MARK'].'~';
							if($row['REAL_STRIP_BACKDATE'] !=''){
								$arrData .= 'Y';
							}else{
								$arrData .= 'T';
							}
							$arrData .= '#';
						}
						else{
							$arrData .= $row['NO_CONT'].'~';
							$arrData .= $row['NO_REQUEST'].'~';
							$arrData .= $row['TGL_REQUEST'].'~';
							$arrData .= $row['ID_USER'].'~';
							$arrData .= $row['YBC_ID'].'~';
							$arrData .= $row['TGL_REALISASI'].'~';
							$arrData .= $row['ALAT'].'~';
							$arrData .= $row['REAL_STRIP_MARK'].'~';
							if($row['REAL_STRIP_BACKDATE'] !=''){
								$arrData .= 'Y';
							}else{
								$arrData .= 'T';
							}
						}
						$i++;
        	}


			$IdData = substr($IdData, 0,-1);

	        $ServiceID = date('YmdHis').rand(100,999);
			$method = 'setRealStripping'; //method
			$response = $this->split_character($arrData,4000);
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
	        		VALUES (".$ServiceID.",'".$method."',$response,'0')";
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxRealStat = "UPDATE TX_REAL_STRIP SET REAL_STRIP_FL_SEND = '1' WHERE REAL_STRIP_ID IN (".$IdData.") AND REAL_STRIP_FL_SEND = '0' AND REAL_STRIP_STATUS = 2 ";
				$this->db->query($updateTxRealStat);
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
	        }
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
	}

	function setRealStripping($branch = false){
		$time_start_new = microtime(true);
		echo "setRealStripping | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setRealStripping' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/api.php";
			    $method = 'setRealStripping'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);
			    $response = $this->split_character($response,4000);
			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = ".$response."  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
				$this->db->query($updateServices);
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
        		echo "ok | ".$row['SERVICES_ID']." | time execute : ".round($SQLCEK,2)."<br>\n";
			}
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
  }

	function ParsingRealStripping($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingRealStripping | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'setRealStripping' order by services_req_date desc";
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

		            	$updateTxGate = "UPDATE TX_REAL_STRIP SET REAL_STRIP_FL_SEND = '9', REAL_STRIP_MARK = '".$KET."' WHERE REAL_STRIP_NOREQ = '".$NO_REQUEST."' AND REAL_STRIP_BRANCH_ID = ".$branch." AND REAL_STRIP_CONT = '".$NO_CONTAINER."' AND REAL_STRIP_FL_SEND = '1' ";
    					$this->db->query($updateTxGate);
		                $i++;
		            }
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					$this->db->query($updateServices);
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
    				echo "Generated ".$row['SERVICES_ID']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		        }
        	}
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

	// services set stripping ke-2
	function generateRealStrippingSecond($branch = false){
		$time_start_new = microtime(true);
		echo "generateRealStrippingSecond | ";
			$sqlgetStrip = "SELECT * FROM (SELECT * FROM (
				SELECT MAX(C.REAL_YARD_YBC_ID) over () as MAX_ID, A.REAL_STRIP_ID, A.REAL_STRIP_CONT NO_CONT, A.REAL_STRIP_NOREQ NO_REQUEST, TO_CHAR(B.STRIP_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQUEST,  A.REAL_STRIP_MECHANIC_TOOLS ALAT, TO_CHAR(A.REAL_STRIP_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REALISASI,A.REAL_STRIP_BY, C.REAL_YARD_YBC_ID YBC_ID, A.REAL_STRIP_MARK, A.REAL_STRIP_BACKDATE,
				CASE WHEN U.USER_ID_NBS IS NOT NULL THEN TO_NUMBER(U.USER_ID_NBS) ELSE A.REAL_STRIP_BY END AS ID_USER
				FROM TX_REAL_STRIP A
				INNER JOIN TX_REQ_STRIP_HDR B ON B.STRIP_ID = A.REAL_STRIP_HDR_ID
				INNER JOIN TX_REAL_YARD C ON C.REAL_YARD_CONT = A.REAL_STRIP_CONT AND C.REAL_YARD_STATUS = '1'
				LEFT JOIN TM_USER U ON U.USER_ID = A.REAL_STRIP_BY
				WHERE A.REAL_STRIP_FL_SEND = 0 AND A.REAL_STRIP_STATUS = 2 AND A.REAl_STRIP_BRANCH_ID = ".$branch."
				ORDER BY A.REAL_STRIP_DATE ASC
				) Z WHERE Z.MAX_ID = Z.YBC_ID
			)ZZ  WHERE  rownum <=1 ";
	// echo $sqlgetStrip;die();
		$resultservices = $this->db->query($sqlgetStrip);
	$totalservice = $resultservices->num_rows();

			if($totalservice > 0){
		$IdData = "";
		$arrData = '';
		$i = 1;
				foreach($resultservices->result_array() as $row){
					$IdData .= $row['REAL_STRIP_ID'].",";

					if($i < $totalservice){
						$arrData .= $row['NO_CONT'].'~';
						$arrData .= $row['NO_REQUEST'].'~';
						$arrData .= $row['TGL_REQUEST'].'~';
						$arrData .= $row['ID_USER'].'~';
						$arrData .= $row['YBC_ID'].'~';
						$arrData .= $row['TGL_REALISASI'].'~';
						$arrData .= $row['ALAT'].'~';
						$arrData .= $row['REAL_STRIP_MARK'].'~';
						if($row['REAL_STRIP_BACKDATE'] !=''){
							$arrData .= 'Y';
						}else{
							$arrData .= 'T';
						}
						$arrData .= '#';
					}
					else{
						$arrData .= $row['NO_CONT'].'~';
						$arrData .= $row['NO_REQUEST'].'~';
						$arrData .= $row['TGL_REQUEST'].'~';
						$arrData .= $row['ID_USER'].'~';
						$arrData .= $row['YBC_ID'].'~';
						$arrData .= $row['TGL_REALISASI'].'~';
						$arrData .= $row['ALAT'].'~';
						$arrData .= $row['REAL_STRIP_MARK'].'~';
						if($row['REAL_STRIP_BACKDATE'] !=''){
							$arrData .= 'Y';
						}else{
							$arrData .= 'T';
						}
					}
					$i++;
				}


		$IdData = substr($IdData, 0,-1);

				$ServiceID = date('YmdHis').rand(100,999);
		$method = 'setRealStrippingSecond'; //method
		$response = $this->split_character($arrData,4000);
				$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
						VALUES (".$ServiceID.",'".$method."',$response,'0')";
				$insert = $this->db->query($insertServices);
				if($insert){
					$updateTxRealStat = "UPDATE TX_REAL_STRIP SET REAL_STRIP_FL_SEND = '1' WHERE REAL_STRIP_ID IN (".$IdData.") AND REAL_STRIP_FL_SEND = '0' AND REAL_STRIP_STATUS = 2 ";
			$this->db->query($updateTxRealStat);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
					echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
				}
			}else{
		$time_end = microtime(true);
		$SQLCEK = ($time_end - $time_start_new);
				echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
			}
}

function setRealStrippingSecond($branch = false){
	$time_start_new = microtime(true);
	echo "setRealStrippingSecond | ";
		$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setRealStrippingSecond' order by services_req_date desc";
		$resultservices = $this->db->query($sqlservices);
			$totalservice = $resultservices->num_rows();
			if($totalservice > 0){
				foreach($resultservices->result_array() as $row){
				$client = SERVICE_SERVER."/api.php";
				$method = 'setRealStripping'; //method

				$params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
				$response = $this->call_service($client, $method,$params);
				$response = $this->split_character($response,4000);
				$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = ".$response."  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
			$this->db->query($updateServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
					echo "ok | ".$row['SERVICES_ID']." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}else{
		$time_end = microtime(true);
		$SQLCEK = ($time_end - $time_start_new);
		 echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
	}
}

	function ParsingRealStrippingSecond($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingRealStrippingSecond | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'setRealStrippingSecond' order by services_req_date desc";
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

		            	$updateTxGate = "UPDATE TX_REAL_STRIP SET REAL_STRIP_FL_SEND = '9', REAL_STRIP_MARK = '".$KET."' WHERE REAL_STRIP_NOREQ = '".$NO_REQUEST."' AND REAL_STRIP_BRANCH_ID = ".$branch." AND REAL_STRIP_CONT = '".$NO_CONTAINER."' AND REAL_STRIP_FL_SEND = '1' ";
    					$this->db->query($updateTxGate);
		                $i++;
		            }
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					$this->db->query($updateServices);
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
    				echo "Generated ".$row['SERVICES_ID']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		        }
        	}
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

	function getStripping($branch = false){
		$time_start_new = microtime(true);
		echo "getStripping | ";
		$client = SERVICE_SERVER."/api.php";
		$method = 'getStripping';
		$sqlLastDateNota = "select TGL from (
												select TO_CHAR(STRIP_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_STRIP_HDR WHERE STRIP_PAID_DATE IS NOT NULL AND STRIP_BRANCH_ID = ".$branch." order by STRIP_PAID_DATE DESC
												)A where rownum =1";

		$resultLastDateNota = $this->db->query($sqlLastDateNota);
        $totalservice = $resultLastDateNota->num_rows();

        if($totalservice <= 0){
        	$lastDateNota = date('YmdHis',strtotime("-10 hours"));
        }else{
        	$resultLastDateNota = $resultLastDateNota->result_array();
        	$lastDateNota = $resultLastDateNota[0]['TGL'];
        }

		//$lastDateNota = '20181220010101';

		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

		$ServiceID = date('YmdHis').rand(100,999);
		$response = $this->call_service($client, $method,$params);

		$xml = xml2ary($response);
		$response = $this->split_character($response,4000);
        $valResponse = $xml['document']['_c']['respon']['_v'];
        if($valResponse != 0){
			$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
								VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function getStrippingNoreq($noreq){
		$time_start_new = microtime(true);
		echo "getStrippingNoreq | ";
		$client = SERVICE_SERVER."/api.php";
		$method = 'getStrippingNoreq';

		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$noreq);

		$ServiceID = date('YmdHis').rand(100,999);
		$response = $this->call_service($client, $method,$params);

		$xml = xml2ary($response);
		$response = $this->split_character($response,4000);
        $valResponse = $xml['document']['_c']['respon']['_v'];
        if($valResponse != 0){
			$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
								VALUES (".$ServiceID.",'getStripping','".$noreq."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function ParsingStripping($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingStripping | ";
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
					$response = str_replace('&','&amp;', $response);
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

	                $sqlcek = "SELECT STRIP_NO, STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO='".$REQ_NO."' AND STRIP_BRANCH_ID = ".$branch."";
	                $resultCek = $this->db->query($sqlcek);
	                $totalcek = $resultCek->num_rows();

	                // if($totalcek <=0){
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

						if($totalcek <=0){
							$qlID = "SELECT SEQ_REQ_STRIP_HDR.NEXTVAL AS ID FROM DUAL";
							$resultID = $this->db->query($qlID)->result_array();
							$IDheader = $resultID[0]['ID'];

							$STRIP_ORIGIN = 'INTERNAL';
							if($STRIP_DARI == 'TPK')
								$STRIP_ORIGIN = 'TPK';

							$insertHDR = "INSERT INTO TX_REQ_STRIP_HDR (STRIP_ID, STRIP_NO, STRIP_CONSIGNEE_ID, STRIP_BRANCH_ID, STRIP_DO, STRIP_BL, STRIP_CREATE_DATE, STRIP_NOTA_DATE, STRIP_NOTA_NO, STRIP_PAID_DATE, STRIP_NOREQ_RECEIVING, STRIP_EXTEND_FROM,STRIP_EXTEND_LOOP, STRIP_ORIGIN) VALUES
										(".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",".$branch.",'".$DO."','".$BL."',
											 TO_DATE('".$REQ_STRIP_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),'".$NO_NOTA."',TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'), '".$NO_REQUEST_RECEIVING."','".$PERP_DARI."',".$PERP_KE.",'".$STRIP_DARI."')";
							$resultHDR = $this->db->query($insertHDR);
						}else{
							$resultHDR = true;
							$resultCek = $resultCek->result_array();
							$IDheader = $resultCek[0]['STRIP_ID'];
						}

	                    if($resultHDR){
							$ketPerpanjangan = 'Request Stripping';
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
							        // $insert = $this->db->query($insertServices);
	                            }

								$sqlcekdetilnya = "SELECT * FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID='".$IDheader."' AND STRIP_DTL_CONT = '".$REQ_DTL_CONT."' ";
								$resultCekdetilNya = $this->db->query($sqlcekdetilnya);
								$totalcekDetilnya = $resultCekdetilNya->num_rows();

								if($totalcekDetilnya <= 0){

									//cek counter container
									$cont_count = '';
									$sqlCekCounterCont = "SELECT CONTAINER_COUNTER FROM TM_CONTAINER WHERE CONTAINER_BRANCH_ID = ".$branch." AND CONTAINER_NO = '".$REQ_DTL_CONT."'";
									$resultCekCounterCont= $this->db->query($sqlCekCounterCont)->row_array();
									$totalCekCounterCont = $this->db->query($sqlCekCounterCont)->num_rows();

									if($totalCekCounterCont > 0) {
										if($STRIP_DARI == 'TPK') {
											if($PERP_DARI != ""){
												$cont_count = (int)$resultCekCounterCont['CONTAINER_COUNTER'];
											}
											else{
												$cont_count = (int)$resultCekCounterCont['CONTAINER_COUNTER'] + 1;
											}
										}
										else{
											$cont_count = (int)$resultCekCounterCont['CONTAINER_COUNTER'];
										}
									}
									else{
										$cont_count = 1;
									}

									$insertDTL = "INSERT INTO TX_REQ_STRIP_DTL (STRIP_DTL_HDR_ID, STRIP_DTL_CONT, STRIP_DTL_DANGER, STRIP_DTL_CONT_SIZE, STRIP_DTL_CONT_TYPE, STRIP_DTL_COMMODITY, STRIP_DTL_STATUS, STRIP_DTL_ORIGIN,STRIP_DTL_START_STRIP_PLAN,STRIP_DTL_END_STRIP_PLAN, STRIP_DTL_CONT_STATUS, STRIP_DTL_COUNTER)
												VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."','1', '".$STRIP_DTL_ORIGIN."',TO_DATE('".$STRIP_DTL_START_STRIP_PLAN."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$STRIP_DTL_END_STRIP_PLAN."','MM/DD/YYYY HH24:MI:SS'), 'FCL', ".$cont_count.")";
									$resultDtl = $this->db->query($insertDTL);
									if($resultDtl){
										echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
									}else{
										echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
									}

									if($PERP_DARI !=""){
										$ketPerpanjangan = 'Perpanjangan Stripping';
										$updateStripTDL = "UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_ACTIVE = 'T', STRIP_DTL_STATUS = '2' WHERE STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch.") AND STRIP_DTL_CONT = '".$REQ_DTL_CONT."'  ";
										$this->db->query($updateStripTDL);

										$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch.")")->row()->JML;
										$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch.") AND STRIP_DTL_ACTIVE = 'T' ")->row()->JML;

										if($cek_tot_dtl == $cek_tot_dtl_T){
											$updateStripHDR = "UPDATE TX_REQ_STRIP_HDR SET STRIP_STATUS = '2' WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch." ";
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
												'FCL',
												NULL,
												NULL,
												NULL,
												NULL,
												NULL,
												1,
												'".$ketPerpanjangan."',
												NULL,
												NULL,
												".$branch.",
												'".$STRIP_DARI."',
												NULL)");
								}
	                            $a++;
	                        }

	                    }else{
	                        echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
	                        $error++;
	                    }
	                // }else{
	                //     echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
	                // }
	            $i++;
	            }
	            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
			  	$this->db->query($updateServices);
	        }else{
	             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
	           $this->db->query($updateServices);
	           echo("tidak ada data parsing stripping  | ".date('Y-m-d H:i:s')."<br>\n");
	        }
		  }
			//start call nodejs
				$updateStrippingProcess = curl_init(SERVICE_SERVER_NODEJS."/updateStrippingProcess?branch=".$branch."");
				curl_exec($updateStrippingProcess);
				curl_close($updateStrippingProcess);
				// $updateGateJobTruckManager = curl_init(SERVICE_SERVER_NODEJS."/updateGateJobTruckManager?branch=".$branch."");
				// curl_exec($updateGateJobTruckManager );
				// curl_close($updateGateJobTruckManager );
			//end call nodejs
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
	    }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	      echo "tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
	    }
	}

	function generateSetGateIn($branch = false){
		echo "generateSetGateIn | ";
			$time_start_new = microtime(true);
			$sqlgetIn = "SELECT GATE_ID, GATE_CONT AS NO_CONTAINER, GATE_NOREQ AS NO_REQUEST, GATE_TRUCK_NO AS NOPOL, GATE_CREATE_BY, TO_CHAR(GATE_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_IN, GATE_CONT_STATUS AS STATUS, GATE_ORIGIN,
									 CASE WHEN U.USER_ID_NBS IS NOT NULL THEN TO_NUMBER(U.USER_ID_NBS) ELSE GATE_CREATE_BY END AS ID_USER
									 FROM TX_GATE
									 LEFT JOIN TM_USER U ON U.USER_ID = TX_GATE.GATE_CREATE_BY
									 WHERE GATE_FL_SEND = '0' AND GATE_ACTIVITY = '3' AND GATE_STATUS = '1' AND GATE_BRANCH_ID = ".$branch."  ORDER BY GATE_CREATE_DATE ASC";
			// echo $sqlgetIn;die();
			$resultservices = $this->db->query($sqlgetIn);
			$totalservice = $resultservices->num_rows();
			if($totalservice > 0){
			$IdData = "";
			$arrData = '';
			$i = 1;
			foreach($resultservices->result_array() as $row){
				$IdData .= $row['GATE_ID'].",";
				if($i < $totalservice){
					$arrData	 .= $row['NO_CONTAINER']."~";
					$arrData	 .= $row['NO_REQUEST']."~";
					$arrData	 .= $row['NOPOL']."~";
					$arrData	 .= $row['ID_USER']."~";
					$arrData	 .= $row['TGL_IN']."~";
					$arrData	 .= $row['STATUS']."~";
					$arrData	 .= $row['GATE_ORIGIN'];
					$arrData	 .= '#';
				}
				else{
					$arrData	 .= $row['NO_CONTAINER']."~";
					$arrData	 .= $row['NO_REQUEST']."~";
					$arrData	 .= $row['NOPOL']."~";
					$arrData	 .= $row['ID_USER']."~";
					$arrData	 .= $row['TGL_IN']."~";
					$arrData	 .= $row['STATUS']."~";
					$arrData	 .= $row['GATE_ORIGIN'];
				}
				$i++;
			}

					$IdData = substr($IdData, 0,-1);
					$ServiceID = date('YmdHis').rand(100,999);
					$method = 'setGateIn'; //method
					$response = $this->split_character($arrData,4000);
					$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
										VALUES (".$ServiceID.",'".$method."',$response,'0')";
					// echo $insertServices;die();
					$insert = $this->db->query($insertServices);
					if($insert){
						$updateTxgate = "UPDATE TX_GATE SET GATE_FL_SEND = '1' WHERE GATE_ID IN (".$IdData.") AND GATE_FL_SEND = '0' ";
						$this->db->query($updateTxgate);
						$time_end = microtime(true);
						$SQLCEK = ($time_end - $time_start_new);
						echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
					}

				}else{
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
					echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
				}
		}

	function SetGateInTest($branch = false){

		$client = SERVICE_SERVER."/api.php";
		$method = 'setGateInTest'; //method

		$arraydata = '<?xml version="1.0" encoding="UTF-8"?><document><respon>1</respon><loop><data><NO_CONTAINER>BSIU3185693</NO_CONTAINER><NO_REQUEST>REC1118001679</NO_REQUEST><NOPOL>56789</NOPOL><ID_USER>163</ID_USER><TGL_IN>11/26/2018 09:47:03</TGL_IN><STATUS>MTY</STATUS><GATE_ORIGIN>DEPO</GATE_ORIGIN></data></loop></document>';

		$params = array('string0'=>'npks','string1'=>'12345','string2'=>$arraydata);
		$response = $this->call_service($client, $method,$params);
		print_r($response);
	}

    function SetGateIn($branch = false){
		$time_start_new = microtime(true);
		echo "SetGateIn | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setGateIn' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
				//echo $totalservice;die();
		$client = SERVICE_SERVER."/api.php";
		$method = 'setGateIn'; //method

        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){

				$params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000) );

				$response = $this->call_service($client, $method,$params);
				// $xml = xml2ary($response);
				$response = $this->split_character($response,4000);

				$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = ".$response."  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";

        		$result = $this->db->query($updateServices);
        		if($result)
        			echo "ok ".$row['SERVICES_ID']."<br>\n";
        		else
        			echo "gagal update ".$row['SERVICES_ID']."<br>\n";
			}
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
    }

    function ParsingGateIn($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingGateIn | ";
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

		            	$updateTxGate = "UPDATE TX_GATE SET GATE_FL_SEND = '9' WHERE GATE_NOREQ = '".$NO_REQUEST."' AND GATE_CONT = '".$NO_CONTAINER."' AND GATE_FL_SEND = '1' AND GATE_BRANCH_ID = ".$branch." ";
    					$this->db->query($updateTxGate);
		                $i++;
		            }
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					$this->db->query($updateServices);
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
    				echo "Generated ".$row['SERVICES_ID']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		        }
        	}
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }


	function generateSetGateOut($branch = false){
		echo "generateSetGateOut | ";
		$time_start_new = microtime(true);
    	$sqlgetIn = "SELECT A.GATE_ID, A.GATE_CONT AS NO_CONTAINER, A.GATE_NOREQ AS NO_REQUEST, TO_CHAR(B.REQ_DELIVERY_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQ_DELIVERY, A.GATE_TRUCK_NO AS NOPOL,
					A.GATE_CREATE_BY, TO_CHAR(A.GATE_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_OUT, GATE_CONT_STATUS AS STATUS, GATE_ORIGIN AS GATE_DESTINATION, A.GATE_NO_SEAL AS NO_SEAL, A.GATE_MARK AS MARK,
					CASE WHEN U.USER_ID_NBS IS NOT NULL THEN TO_NUMBER(U.USER_ID_NBS) ELSE A.GATE_CREATE_BY END AS ID_USER
					FROM TX_GATE A
					INNER JOIN TX_REQ_DELIVERY_HDR B ON B.REQ_NO = A.GATE_NOREQ
					LEFT JOIN TM_USER U ON U.USER_ID = A.GATE_CREATE_BY
					WHERE A.GATE_FL_SEND = '0' AND A.GATE_ACTIVITY = '4' AND A.GATE_STATUS = '3' AND GATE_BRANCH_ID = ".$branch." AND A.GATE_ORIGIN = 'DEPO' ORDER BY A.GATE_CREATE_DATE ASC";
    	$resultservices = $this->db->query($sqlgetIn);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
			$IdData = '';
			$arrData = '';
			$i = 1;
        	foreach($resultservices->result_array() as $row){
        		$IdData .= $row['GATE_ID'].",";

						if($i < $totalservice){
							$arrData	 .= $row['NO_CONTAINER']."~";
							$arrData	 .= $row['NO_REQUEST']."~";
							$arrData	 .= $row['TGL_REQ_DELIVERY']."~";
							$arrData	 .= $row['NOPOL']."~";
							$arrData	 .= $row['ID_USER']."~";
							$arrData	 .= $row['TGL_OUT']."~";
							$arrData	 .= $row['STATUS']."~";
							$arrData	 .= $row['NO_SEAL']."~";
							$arrData	 .= $row['MARK']."~";
							$arrData	 .= $row['GATE_DESTINATION'];
							$arrData	 .= '#';
						}
						else{
							$arrData	 .= $row['NO_CONTAINER']."~";
							$arrData	 .= $row['NO_REQUEST']."~";
							$arrData	 .= $row['TGL_REQ_DELIVERY']."~";
							$arrData	 .= $row['NOPOL']."~";
							$arrData	 .= $row['ID_USER']."~";
							$arrData	 .= $row['TGL_OUT']."~";
							$arrData	 .= $row['STATUS']."~";
							$arrData	 .= $row['NO_SEAL']."~";
							$arrData	 .= $row['MARK']."~";
							$arrData	 .= $row['GATE_DESTINATION'];
						}
						$i++;

        	}

			$IdData = substr($IdData, 0,-1);

	        $ServiceID = date('YmdHis').rand(100,999);
			$method = 'setGateOut'; //method
			$response = $this->split_character($arrData,4000);
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."',$response,'0')";
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxgate = "UPDATE TX_GATE SET GATE_FL_SEND = '1' WHERE GATE_ID IN (".$IdData.") AND GATE_FL_SEND = '0' AND GATE_BRANCH_ID = ".$branch."  ";
				$this->db->query($updateTxgate);
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
	        }
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

    function SetGateOut($branch = false){
		$time_start_new = microtime(true);
		echo "SetGateOut | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setGateOut' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/api.php";
			    $method = 'setGateOut'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);
			    $response = $this->split_character($response,4000);
			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = ".$response."  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
				$this->db->query($updateServices);
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
        		echo "ok ".$row['SERVICES_ID']." | time execute : ".round($SQLCEK,2)."<br>\n";
			}
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
    }

    function ParsingGateOut($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingGateOut | ";
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
			            	$updateTxGate = "UPDATE TX_GATE SET GATE_FL_SEND = '9', GATE_MARK = '".$UR_STATUS."' WHERE GATE_NOREQ = '".$NO_REQUEST."' AND GATE_CONT = '".$NO_CONTAINER."' AND GATE_FL_SEND = '1' AND GATE_BRANCH_ID = ".$branch." ";
							$this->db->query($updateTxGate);
							echo "berhasil generate EXIST_GATO | ".date('Y-m-d H:i:s')."<br>\n";
		                }else{
		                	$updateTxGate = "UPDATE TX_GATE SET GATE_FL_SEND = '9' WHERE GATE_NOREQ = '".$NO_REQUEST."' AND GATE_CONT = '".$NO_CONTAINER."' AND GATE_FL_SEND = '1' AND GATE_BRANCH_ID = ".$branch." ";
							$this->db->query($updateTxGate);
							echo "berhasil generate OK | ".date('Y-m-d H:i:s')."<br>\n";
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
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

    function setPlacement($branch = false){
		$time_start_new = microtime(true);
		echo "setPlacement | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setPlacement' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/api.php";
			    $method = 'setPlacement'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);
			    $response = $this->split_character($response,4000);
			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = ".$response."  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
				$this->db->query($updateServices);

				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
        		echo "ok ".$row['SERVICES_ID']." | time execute : ".round($SQLCEK,2)."<br>\n";
			}
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
    }

    function setPlacementAll($branch = false){
		$time_start_new = microtime(true);
		echo "setPlacementAll | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_REQ_XML from TX_SERVICES where services_status = '0' AND services_method = 'setPlacementAll' order by services_req_date desc";
    	$resultservices = $this->db->query($sqlservices);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
        	foreach($resultservices->result_array() as $row){
		    	$client = SERVICE_SERVER."/api.php";
			    $method = 'setPlacementAll'; //method

			    $params = array('string0'=>'npks','string1'=>'12345','string2'=>$row['SERVICES_REQ_XML']->read(2000000));
			    $response = $this->call_service($client, $method,$params);
			    $response = $this->split_character($response,4000);
			    $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '1', SERVICES_RESP_XML = ".$response."  WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '0' ";
				$this->db->query($updateServices);
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
        		echo "ok ".$row['SERVICES_ID']." | time execute : ".round($SQLCEK,2)."<br>\n";
			}
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			 echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
    }

	function generatePlacement($branch = false){
		echo "generatePlacement | ";
		$time_start_new = microtime(true);
    	$sqlgetIn = "SELECT A.REAL_YARD_ID, A.REAL_YARD_YBC_ID, B.YBC_SLOT, B.YBC_ROW, B.YBC_BLOCK_ID, A.REAL_YARD_TIER TIER, A.REAL_YARD_NO ID_YARD, UPPER(A.REAL_YARD_CONT) NO_CONTAINER, A.REAL_YARD_REQ_NO NO_REQUEST, A.REAL_YARD_BRANCH_ID, TO_CHAR(A.REAL_YARD_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_PLACEMENT, A.REAL_YARD_CREATE_BY, A.REAL_YARD_CONT_STATUS CONT_STATUS,
					CASE WHEN U.USER_ID_NBS IS NOT NULL THEN TO_NUMBER(U.USER_ID_NBS) ELSE A.REAL_YARD_CREATE_BY END AS ID_USER
					FROM TX_REAL_YARD A
					INNER JOIN TX_YARD_BLOCK_CELL B ON B.YBC_ID = A.REAL_YARD_YBC_ID
					LEFT JOIN TM_USER U ON U.USER_ID = A.REAL_YARD_CREATE_BY
					WHERE --A.REAL_YARD_STATUS = '1' AND
					A.REAL_YARD_ACTIVITY = '3' AND A.REAL_YARD_FL_SEND = '0' AND A.REAL_YARD_BRANCH_ID = ".$branch." ORDER BY A.REAL_YARD_CREATE_DATE ASC";
    	$resultservices = $this->db->query($sqlgetIn);
        $totalservice = $resultservices->num_rows();
        if($totalservice > 0){
			$IdData = '';
			$arrData = '';
			$i = 1;
        	foreach($resultservices->result_array() as $row){
        		$IdData .= $row['REAL_YARD_ID'].",";
						if($i < $totalservice){
							$arrData .= $row['NO_CONTAINER'].'~';
							$arrData .= $row['NO_REQUEST'].'~';
							$arrData .= $row['YBC_SLOT'].'~';
							$arrData .= $row['YBC_ROW'].'~';
							$arrData .= $row['YBC_BLOCK_ID'].'~';
							$arrData .= $row['TIER'].'~';
							$arrData .= $row['ID_YARD'].'~';
							$arrData .= $row['ID_USER'].'~';
							$arrData .= $row['CONT_STATUS'].'~';
							$arrData .= $row['TGL_PLACEMENT'];
							$arrData .= '#';
						}
						else{
							$arrData .= $row['NO_CONTAINER'].'~';
							$arrData .= $row['NO_REQUEST'].'~';
							$arrData .= $row['YBC_SLOT'].'~';
							$arrData .= $row['YBC_ROW'].'~';
							$arrData .= $row['YBC_BLOCK_ID'].'~';
							$arrData .= $row['TIER'].'~';
							$arrData .= $row['ID_YARD'].'~';
							$arrData .= $row['ID_USER'].'~';
							$arrData .= $row['CONT_STATUS'].'~';
							$arrData .= $row['TGL_PLACEMENT'];
						}
						$i++;
        	}

	        $IdData = substr($IdData, 0,-1);

	        $ServiceID = date('YmdHis').rand(100,999);
			$method = 'setPlacement'; //method
			$response = $this->split_character($arrData,4000);
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."',$response,'0')";
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxRealYard = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '1' WHERE REAL_YARD_ID IN (".$IdData.") AND REAL_YARD_FL_SEND = '0' AND REAL_YARD_BRANCH_ID = ".$branch." ";
				$this->db->query($updateTxRealYard);
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
			}
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

    function generatePlacementAll($branch = false){
		$time_start_new = microtime(true);
		echo "generatePlacementAll | ";
    	$sqlgetIn = "SELECT A.REAL_YARD_ID, A.REAL_YARD_YBC_ID, B.YBC_SLOT, B.YBC_ROW, B.YBC_BLOCK_ID, A.REAL_YARD_TIER TIER, A.REAL_YARD_NO ID_YARD, A.REAL_YARD_CONT NO_CONTAINER, A.REAL_YARD_REQ_NO NO_REQUEST, A.REAL_YARD_BRANCH_ID, TO_CHAR(A.REAL_YARD_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_PLACEMENT, A.REAL_YARD_CREATE_BY AS ID_USER, A.REAL_YARD_CONT_STATUS CONT_STATUS, A.REAL_YARD_TYPE TIPE_ACTIVITY
					FROM TX_REAL_YARD A
					INNER JOIN TX_YARD_BLOCK_CELL B ON B.YBC_ID = A.REAL_YARD_YBC_ID
					WHERE A.REAL_YARD_STATUS = '1' AND A.REAL_YARD_ACTIVITY NOT IN ('3','4') AND A.REAL_YARD_FL_SEND = '0' AND REAL_YARD_BRANCH_ID = ".$branch." ORDER BY A.REAL_YARD_CREATE_DATE ASC";
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
	        $returnXML = $this->split_character($returnXML,4000);
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."',".$returnXML.",'0')";
	        $insert = $this->db->query($insertServices);
	        if($insert){
	        	$updateTxRealYard = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '1' WHERE REAL_YARD_ID IN (".$IdData.") AND REAL_YARD_FL_SEND = '0' AND REAL_YARD_BRANCH_ID = ".$branch."";
				$this->db->query($updateTxRealYard);
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
	        	echo "Service ID ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
	        }
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

    function ParsingPlacement($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingPlacement | ";
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

		                // if($KET == 'OK'){
			            // 	$updateTxGate = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '9', REAL_YARD_MARK = '".$KET."' WHERE REAL_YARD_REQ_NO = '".$NO_REQUEST."' AND REAL_YARD_CONT = '".$NO_CONTAINER."'  AND REAL_YARD_BRANCH_ID = ".$branch." AND REAL_YARD_FL_SEND = '1' ";
	    				// 	$this->db->query($updateTxGate);
						// }else
						if($KET == "CONTAINER BELUM GATE IN"){
							$updateTxGate = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '0', REAL_YARD_MARK = '".$KET."' WHERE REAL_YARD_REQ_NO = '".$NO_REQUEST."' AND REAL_YARD_CONT = '".$NO_CONTAINER."' AND REAL_YARD_BRANCH_ID = ".$branch." AND REAL_YARD_FL_SEND = '1' ";
	    					$this->db->query($updateTxGate);
						}else{
							$updateTxGate = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '9', REAL_YARD_MARK = '".$KET."' WHERE REAL_YARD_REQ_NO = '".$NO_REQUEST."' AND REAL_YARD_CONT = '".$NO_CONTAINER."' AND REAL_YARD_BRANCH_ID = ".$branch." AND REAL_YARD_FL_SEND = '1' ";
	    					$this->db->query($updateTxGate);
							echo "parsing data ".$NO_REQUEST." ~ ".$NO_CONTAINER." ~ ".$KET." | ".date('Y-m-d H:i:s')."<br>\n";
		                }
		                $i++;
		            }
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS  = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					$this->db->query($updateServices);
					echo "Parsing data ".$row['SERVICES_ID']." | ".date('Y-m-d H:i:s')."<br>\n";
		        }else{
		        	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
    				$this->db->query($updateServices);
		        }
			}
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

    function ParsingPlacementAll($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingPlacementAll | ";
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
			            	$updateTxGate = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '9', REAL_YARD_MARK = '".$KET."' WHERE REAL_YARD_REQ_NO = '".$NO_REQUEST."' AND REAL_YARD_CONT = '".$NO_CONTAINER."' AND REAL_YARD_BRANCH_ID = ".$branch." AND REAL_YARD_FL_SEND = '1' ";
	    					$this->db->query($updateTxGate);
		                }else{
		                	$updateTxGate = "UPDATE TX_REAL_YARD SET REAL_YARD_FL_SEND = '9', REAL_YARD_MARK = '".$KET."' WHERE REAL_YARD_REQ_NO = '".$NO_REQUEST."' AND REAL_YARD_CONT = '".$NO_CONTAINER."' AND REAL_YARD_BRANCH_ID = ".$branch." AND REAL_YARD_FL_SEND = '1' ";
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
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

    function getBatalMuat($branch = false){
		$time_start_new = microtime(true);
		echo "getBatalMuat | ";
		$client = SERVICE_SERVER."/api.php";
        $method = 'getBatalMuat'; //method

        $sqlLastDateNota = "select TGL from (
                            select TO_CHAR(CANCELLED_REQ_DATE,'YYYYMMDDHH24MISS') TGL from  TH_CANCELLED  WHERE CANCELLED_STATUS IN (1,2,3,4,5,6) AND CANCELLED_BRANCH_ID = ".$branch." order by CANCELLED_REQ_DATE DESC
                            )A where rownum =1";

        $resultLastDateNota = $this->db->query($sqlLastDateNota);
        $totalservice = $resultLastDateNota->num_rows();

        if($totalservice <= 0){
        	$lastDateNota = date('YmdHis',strtotime("-1 hours"));
        }else{
        	$resultLastDateNota = $resultLastDateNota->result_array();
        	$lastDateNota = $resultLastDateNota[0]['TGL'];
        }
		// echo $lastDateNota;die(); // = '20190109192740';
        $params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);

        $xml = xml2ary($response);
        $valResponse = $xml['document']['_c']['respon']['_v'];
       	$response = $this->split_character($response,4000);
        if($valResponse != 0){
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function ParsingBatalMuat($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingBatalMuat | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getBatalMuat' order by services_req_date desc";
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
						$response = str_replace('&','&amp;', $response);
		        $error = 0;
		        $xml = xml2ary($response);
				$valResponse = $xml['document']['_c']['respon']['_v'];
		        if($valResponse != 0){
		            $loop =  $xml['document']['_c']['loop']['_c']['data'];
		            $totalData = count($loop);
					$i =0;
		            while ($i < $totalData) {
		                if($totalData == 1){
								$header = $loop['_c']['header']['_c'];
								$detail = $loop['_c']['arrdetail']['_c']['detail'];
						}else{
								$header = $loop[$i]['_c']['header']['_c'];
								$detail = $loop[$i]['_c']['arrdetail']['_c']['detail'];
						}
						// echo $header['NO_REQUEST']['_v'];die()
						$NO_REQUEST = $header['NO_REQUEST']['_v'];
						$JENIS_BM = strtolower($header['JENIS_BM']['_v']);
						$STATUS_GATE = $header['STATUS_GATE']['_v'];
						$NO_REQ_BARU = $header['NO_REQ_BARU']['_v'];
						$TGLREQUEST = $header['TGLREQUEST']['_v'];

						$totalDTL = count($detail);
						$a = 0;
						while($a < $totalDTL){
							if($totalDTL == 1){
									$detailroot = $detail['_c'];
							}else{
									$detailroot = $detail[$a]['_c'];
							}

							$NO_CONTAINER = trim($detailroot['NO_CONTAINER']['_v']);
							$NO_REQ_BATAL = trim($detailroot['NO_REQ_BATAL']['_v']);
							$STATUS_CONT = trim($detailroot['STATUS_CONT']['_v']);

							$stat = '';
							if ($JENIS_BM == 'alih_kapal' && $STATUS_GATE == '1'){
								$stat = 1;
								$kegiatan = 'Alih Kapal After Stuffing';
							}
							if ($JENIS_BM == 'alih_kapal' && $STATUS_GATE == '2'){
								$stat = 3;
								$kegiatan = 'Alih Kapal Ex Repo';
							}
							if ($JENIS_BM == 'alih_kapal' && $STATUS_GATE == '3'){
								$stat = 2;
								$kegiatan = 'Alih Kapal Before Stuffing';
							}
							if ($JENIS_BM == 'delivery' && $STATUS_GATE == '1'){
								$stat = 4;
								$kegiatan = 'Delivery After Stuffing';
							}
							if ($JENIS_BM == 'delivery' && $STATUS_GATE == '2'){
								$stat = 6;
								$kegiatan = 'Delivery Ex Repo';
							}
							if ($JENIS_BM == 'delivery' && $STATUS_GATE == '3'){
								$stat = 5;
								$kegiatan = 'Delivery Before Stuffing';
							}

							$insertCancelled = "INSERT INTO TH_CANCELLED (CANCELLED_NOREQ, CANCELLED_NO_CONT, CANCELLED_STATUS, CANCELLED_NOREQ_OLD, CANCELLED_REQ_DATE, CANCELLED_BRANCH_ID)
	        									VALUES ('".$NO_REQUEST."','".$NO_CONTAINER."','".$stat."','".$NO_REQ_BATAL."', TO_DATE('".$TGLREQUEST."','MM/DD/YYYY HH24:MI:SS'), ".$branch.")";
	       					$this->db->query($insertCancelled);

							if($STATUS_GATE == '1' || $STATUS_GATE == '3'){
								$sqlDtl = "SELECT A.STUFF_DTL_ID, B.STUFF_ID FROM TX_REQ_STUFF_DTL A INNER JOIN TX_REQ_STUFF_HDR B ON B.STUFF_ID = A.STUFF_DTL_HDR_ID WHERE A.STUFF_DTL_CONT = '".$NO_CONTAINER."' AND B.STUFF_BRANCH_ID = ".$branch." AND B.STUFF_NO = '".$NO_REQ_BATAL."'";
						        $resultID = $this->db->query($sqlDtl)->row_array();
						        $DTL_ID = $resultID['STUFF_DTL_ID'];
								$HDR_ID = $resultID['STUFF_ID'];

								if($DTL_ID != ''){
									$updateDtlStuff = "UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_ACTIVE = 'T', STUFF_DTL_STATUS = '2', STUFF_DTL_CANCELLED = 'Y' WHERE STUFF_DTL_ID = ".$DTL_ID;
									$this->db->query($updateDtlStuff);

									$cek_jumlah_dtl = $this->db->where('STUFF_DTL_HDR_ID',$HDR_ID)->from('TX_REQ_STUFF_DTL')->count_all_results();
									$cek_jumlah_out = $this->db->where('STUFF_DTL_HDR_ID',$HDR_ID)->where('STUFF_DTL_ACTIVE','T')->from('TX_REQ_STUFF_DTL')->count_all_results();
									if($cek_jumlah_out == $cek_jumlah_dtl){
										$this->db->set('STUFF_STATUS',2)->where('STUFF_ID',$HDR_ID)->update('TX_REQ_STUFF_HDR');
									}
									echo $kegiatan." | Id detail ".$DTL_ID." ~ ".$NO_CONTAINER." | ".date('Y-m-d H:i:s')."<br>\n";
								}
							}else{
								$sqlDtl = "SELECT A.REQ_DTL_ID, B.REQ_ID FROM TX_REQ_DELIVERY_DTL A INNER JOIN TX_REQ_DELIVERY_HDR B ON B.REQ_ID = A.REQ_HDR_ID WHERE A.REQ_DTL_CONT = '".$NO_CONTAINER."' AND B.REQ_BRANCH_ID = ".$branch." AND B.REQ_NO = '".$NO_REQ_BATAL."'";
						        $resultID = $this->db->query($sqlDtl)->row_array();
						        $DTL_ID = $resultID['REQ_DTL_ID'];
						        $HDR_ID = $resultID['REQ_ID'];

								if($DTL_ID != ''){
									$updateDtlStuff = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_ACTIVE = 'T', REQ_DTL_STATUS = '2', REQ_DTL_CANCELLED = 'Y' WHERE REQ_DTL_ID = ".$DTL_ID;
									$this->db->query($updateDtlStuff);

									$cek_jumlah_dtl = $this->db->where('REQ_HDR_ID',$HDR_ID)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
									$cek_jumlah_out = $this->db->where('REQ_HDR_ID',$HDR_ID)->where('REQ_DTL_ACTIVE','T')->from('TX_REQ_DELIVERY_DTL')->count_all_results();
									if($cek_jumlah_out == $cek_jumlah_dtl){
										$this->db->set('REQUEST_STATUS',2)->where('REQ_ID',$HDR_ID)->update('TX_REQ_DELIVERY_HDR');
									}
									echo $kegiatan." | Id detail ".$DTL_ID." ~ ".$NO_CONTAINER." | ".date('Y-m-d H:i:s')."<br>\n";
								}
							}

						$SelectMaterCont = "SELECT CONTAINER_TYPE, CONTAINER_SIZE FROM TM_CONTAINER WHERE CONTAINER_NO = '".$NO_CONTAINER."' AND CONTAINER_BRANCH_ID = ".$branch."";
						$result = $this->db->query($SelectMaterCont)->row_array();

						//insert history container
						$this->db->query("CALL ADD_HISTORY_CONTAINER(
									'".$NO_CONTAINER."',
									'".$NO_REQ_BATAL."',
									'".$TGLREQUEST."',
									'".$result['CONTAINER_SIZE']."',
									'".$result['CONTAINER_TYPE']."',
									'".$STATUS_CONT."',
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									$stat,
									'".$kegiatan."',
									NULL,
									NULL,
									".$branch.",
									NULL,
									NULL)");
							$a++;
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
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

    function getBatalStuffStrip($branch = false){
		$time_start_new = microtime(true);
		echo "getBatalStuffStrip | ";
		$client = SERVICE_SERVER."/api.php";
        $method = 'getBatalStuffStrip'; //method

        $sqlLastDateNota = "select TGL from (
                            select TO_CHAR(CANCELLED_CREATE_DATE,'YYYYMMDDHH24MISS') TGL from  TH_CANCELLED  WHERE CANCELLED_STATUS IN (7,8,18,19) AND CANCELLED_BRANCH_ID = ".$branch."  order by CANCELLED_CREATE_DATE DESC
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
       	$response = $this->split_character($response,4000);
        if($valResponse != 0){
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function ParsingBatalStuffStrip($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingBatalStuffStrip | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getBatalStuffStrip' order by services_req_date desc";
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
						$response = str_replace('&','&amp;', $response);
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
		                $REQ_NO = $data['REQ_NO']['_v'];
		                $JENIS = $data['JENIS']['_v'];
		                $NO_BA = $data['NO_BA']['_v'];
		                $TGL_REQUEST = $data['TGL_REQUEST']['_v'];

						if ($JENIS == 'STR' && $NO_BA != 'BILLINK'){
							$stat = 7;
							$kegiatan = 'Batal SPPS';
						}
						else if ($JENIS == 'STP' && $NO_BA != 'BILLING'){
							$stat = 8;
							$kegiatan = 'Batal STUFFING';
						}
						else if ($JENIS == 'STP' && $NO_BA == 'BILLING'){
							$stat = 19;
							$kegiatan = 'Batal STUFFING BILLING';
						}
						else if ($JENIS == 'STR' && $NO_BA == 'BILLING'){
							$stat = 18;
							$kegiatan = 'Batal STRIPPING BILLING';
						}

						$insertCancelled = "INSERT INTO TH_CANCELLED (CANCELLED_NOREQ, CANCELLED_NO_CONT, CANCELLED_STATUS, CANCELLED_NOREQ_OLD, CANCELLED_REQ_DATE, CANCELLED_MARK, CANCELLED_BRANCH_ID)
        									VALUES ('".$REQ_NO."','".$NO_CONTAINER."','".$stat."','".$REQ_NO."', TO_DATE('".$TGL_REQUEST."','MM/DD/YYYY HH24:MI:SS'), '".$NO_BA."', ".$branch.")";
       					$this->db->query($insertCancelled);

		                if($JENIS == 'STR'){
		                	$sqlDtl = "SELECT A.STRIP_DTL_ID, B.STRIP_ID, B.STRIP_NOREQ_RECEIVING FROM TX_REQ_STRIP_DTL A INNER JOIN TX_REQ_STRIP_HDR B ON B.STRIP_ID = A.STRIP_DTL_HDR_ID WHERE A.STRIP_DTL_CONT = '".$NO_CONTAINER."' AND B.STRIP_NO = '".$REQ_NO."'";
					        $resultID = $this->db->query($sqlDtl)->row_array();
					        $DTL_ID = $resultID['STRIP_DTL_ID'];
					        $HDR_ID = $resultID['STRIP_ID'];
					        $NO_REC = $resultID['STRIP_NOREQ_RECEIVING'];
							$updateDtlStrip = "UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_ACTIVE = 'T', STRIP_DTL_CANCELLED = 'Y', STRIP_DTL_STATUS = '2' WHERE STRIP_DTL_ID = ".$DTL_ID;
    						$this->db->query($updateDtlStrip);

    						$cek_jumlah_dtl = $this->db->where('STRIP_DTL_HDR_ID',$HDR_ID)->from('TX_REQ_STRIP_DTL')->count_all_results();
							$cek_jumlah_out = $this->db->where('STRIP_DTL_HDR_ID',$HDR_ID)->where('STRIP_DTL_ACTIVE','T')->from('TX_REQ_STRIP_DTL')->count_all_results();
							if($cek_jumlah_out == $cek_jumlah_dtl){
								$this->db->set('STRIP_STATUS',2)->where('STRIP_ID',$HDR_ID)->update('TX_REQ_STRIP_HDR');
							}
		                }else{
		                	$sqlDtl = "SELECT A.STUFF_DTL_ID, B.STUFF_ID, B.STUFF_NOREQ_RECEIVING FROM TX_REQ_STUFF_DTL A INNER JOIN TX_REQ_STUFF_HDR B ON B.STUFF_ID = A.STUFF_DTL_HDR_ID WHERE A.STUFF_DTL_CONT = '".$NO_CONTAINER."' AND REAL_STUFF_BRANCH_ID = ".$branch." AND B.STUFF_NO = '".$REQ_NO."'";
					        $resultID = $this->db->query($sqlDtl)->row_array();
					        $DTL_ID = $resultID['REQ_ID'];
					        $HDR_ID = $resultID['STUFF_ID'];
					        $NO_REC = $resultID['STUFF_NOREQ_RECEIVING'];
							$updateDtlStuff = "UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_ACTIVE = 'T', STUFF_DTL_CANCELLED = 'Y', STUFF_DTL_STATUS = '2' WHERE STUFF_DTL_ID = ".$DTL_ID;
    						$this->db->query($updateDtlStuff);

    						$cek_jumlah_dtl = $this->db->where('STUFF_DTL_HDR_ID',$HDR_ID)->from('TX_REQ_STUFF_DTL')->count_all_results();
							$cek_jumlah_out = $this->db->where('STUFF_DTL_HDR_ID',$HDR_ID)->where('STUFF_DTL_ACTIVE','T')->from('TX_REQ_STUFF_DTL')->count_all_results();
							if($cek_jumlah_out == $cek_jumlah_dtl){
								$this->db->set('STUFF_STATUS',2)->where('STUFF_ID',$HDR_ID)->update('TX_REQ_STUFF_HDR');
							}
		                }

						$master_container  = $this->db->select('CONTAINER_STATUS')->where('NO_CONTAINER',$NO_CONTAINER)->where('CONTAINER_BRANCH_ID',$branch)->get('TM_CONTAINER')->row_array();
						if($master_container == 'GATO'){
							$sqlDtl = "SELECT A.REQUEST_DTL_ID, B.REQUEST_ID FROM TX_REQ_RECEIVING_DTL A INNER JOIN TX_REQ_RECEIVING_HDR B ON B.REQUEST_ID = A.REQUEST_HDR_ID WHERE A.REQUEST_DTL_CONT = '".$NO_CONTAINER."' AND B.REQUEST_NO = '".$NO_REC."' AND B.REQUEST_BRANCH_ID = ".$branch."";
							$resultID = $this->db->query($sqlDtl)->row_array();
							$DTL_ID = $resultID['REQUEST_DTL_ID'];
							$HDR_ID = $resultID['REQUEST_ID'];
							$updateDtlReceiving = "UPDATE TX_REQ_RECEIVING_DTL SET REQ_DTL_ACTIVE = 'T', REQ_DTL_STATUS = '2' WHERE REQUEST_DTL_ID = ".$DTL_ID;
							$this->db->query($updateDtlReceiving);
						}

						$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_RECEIVING_DTL WHERE REQUEST_HDR_ID = (SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO = '".$NO_REC."' AND REQUEST_BRANCH_ID = ".$branch.")")->row()->JML;
						$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_RECEIVING_DTL WHERE REQUEST_HDR_ID = (SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO = '".$NO_REC."' AND REQUEST_BRANCH_ID = ".$branch.") AND REQUEST_DTL_STATUS = '2' ")->row()->JML;

						if($cek_tot_dtl == $cek_tot_dtl_T){
							//update request receiving hdr
							$updateRecHDR = "UPDATE TX_REQ_RECEIVING_HDR SET REQUEST_STATUS = '2' WHERE REQUEST_NO = '".$NO_REC."' AND REQUEST_BRANCH_ID = ".$branch." ";
							$this->db->query($updateRecHDR);
						}

						$SelectMaterCont = "SELECT CONTAINER_TYPE, CONTAINER_SIZE FROM TM_CONTAINER WHERE CONTAINER_NO = '".$NO_CONTAINER."' AND CONTAINER_BRANCH_ID = ".$branch."";
						$result = $this->db->query($SelectMaterCont)->row_array();
						//insert history container
						$this->db->query("CALL ADD_HISTORY_CONTAINER(
									'".$NO_CONTAINER."',
									'".$REQ_NO."',
									'".$TGL_REQUEST."',
									'".$result['CONTAINER_SIZE']."',
									'".$result['CONTAINER_TYPE']."',
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									$stat,
									'".$kegiatan."',
									NULL,
									NULL,
									".$branch.",
									NULL,
									NULL)");

		                $i++;
		            }
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS  = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
    				$this->db->query($updateServices);
		        }else{
		        	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
    				$this->db->query($updateServices);
		        }
			}
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

    function getBatalOperation($branch = false){
		$time_start_new = microtime(true);
		echo "getBatalOperation | ";
		$client = SERVICE_SERVER."/api.php";
        $method = 'getBatalOperation'; //method

        $sqlLastDateNota = "select TGL from (
                            select TO_CHAR(CANCELLED_CREATE_DATE,'YYYYMMDDHH24MISS') TGL from  TH_CANCELLED  WHERE CANCELLED_STATUS IN (9,19,11,12,13,14,15) AND CANCELLED_BRANCH_ID = ".$branch." order by CANCELLED_CREATE_DATE DESC
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
       	$response = $this->split_character($response,4000);
        if($valResponse != 0){
	        $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
	        					VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
	        echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function ParsingBatalOperation($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingBatalOperation | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getBatalOperation' order by services_req_date desc";
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
						$response = str_replace('&','&amp;', $response);
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
		                $KEGIATAN = strtoupper(trim($data['KEGIATAN']['_v']));
		                $TGL_REQUEST = $data['TGL_REQUEST']['_v'];

		                $stat = '';
						if($KEGIATAN == 'REALISASI STRIPPING')
							$stat = 11;
						if($KEGIATAN == 'BORDER GATE IN')
							$stat = 12;
						if($KEGIATAN == 'PLACEMENT')
							$stat = 13;
						if($KEGIATAN == 'REALISASI STUFFING')
							$stat = 14;
						if($KEGIATAN == 'GATE IN')
							$stat = 15;
						if($KEGIATAN == 'GATE OUT')
							$stat = 9;
						if($KEGIATAN == 'BORDER GATE OUT')
							$stat = 10;


						$insertCancelled = "INSERT INTO TH_CANCELLED (CANCELLED_NOREQ, CANCELLED_NO_CONT, CANCELLED_STATUS, CANCELLED_NOREQ_OLD, CANCELLED_REQ_DATE, CANCELLED_MARK, CANCELLED_BRANCH_ID)
        									VALUES ('".$NO_REQUEST."','".$NO_CONTAINER."','".$stat."','".$NO_REQUEST."', TO_DATE('".$TGL_REQUEST."','MM/DD/YYYY HH24:MI:SS'), '".$KEGIATAN."',".$branch.")";
       					$this->db->query($insertCancelled);

		                if($KEGIATAN == 'REALISASI STRIPPING'){
		                	$sqlDtl = "SELECT A.STRIP_DTL_ID, B.STRIP_ID, B.STRIP_NOREQ_RECEIVING FROM TX_REQ_STRIP_DTL A INNER JOIN TX_REQ_STRIP_HDR B ON B.STRIP_ID = A.STRIP_DTL_HDR_ID WHERE A.STRIP_DTL_CONT = '".$NO_CONTAINER."' AND B.STRIP_NO = '".$NO_REQUEST."' AND STRIP_BRANCH_ID = ".$branch."";
					        $resultID = $this->db->query($sqlDtl)->row_array();
					        $DTL_ID = $resultID['STRIP_DTL_ID'];
					        $HDR_ID = $resultID['STRIP_ID'];
					        $NO_REC = $resultID['STRIP_NOREQ_RECEIVING'];
							$updateDtlStrip = "UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_ACTIVE = 'Y' WHERE STRIP_DTL_ID = ".$DTL_ID;
    						$this->db->query($updateDtlStrip);

    						$this->db->where('REAL_STRIP_CONT', $NO_CONTAINER)->$this->db->where('REAL_STRIP_NOREQ', $NO_REQUEST)->$this->db->delete('TX_REAL_STRIP');

    						$cek_jumlah_dtl = $this->db->where('STRIP_DTL_HDR_ID',$HDR_ID)->from('TX_REQ_STRIP_DTL')->count_all_results();
							$cek_jumlah_out = $this->db->where('STRIP_DTL_HDR_ID',$HDR_ID)->where('STRIP_DTL_ACTIVE','T')->from('TX_REQ_STRIP_DTL')->count_all_results();
							if($cek_jumlah_out != $cek_jumlah_dtl){
								$this->db->set('STRIP_STATUS',1)->where('STRIP_ID',$HDR_ID)->update('TX_REQ_STRIP_HDR');
							}
		                }else if($KEGIATAN == 'REALISASI STUFFING'){
		                	$sqlDtl = "SELECT A.STUFF_DTL_ID, B.STUFF_ID, B.STUFF_NOREQ_RECEIVING FROM TX_REQ_STUFF_DTL A INNER JOIN TX_REQ_STUFF_HDR B ON B.STUFF_ID = A.STUFF_DTL_HDR_ID WHERE A.STUFF_DTL_CONT = '".$NO_CONTAINER."' AND B.STUFF_NO = '".$NO_REQUEST."' AND STUFF_BRANCH_ID = ".$branch."";
					        $resultID = $this->db->query($sqlDtl)->row_array();
					        $DTL_ID = $resultID['REQ_ID'];
					        $HDR_ID = $resultID['STUFF_ID'];
					        $NO_REC = $resultID['STUFF_NOREQ_RECEIVING'];
							$updateDtlStuff = "UPDATE TX_STUF_REQ_DTL SET STUFF_DTL_ACTIVE = 'Y' WHERE STUFF_DTL_ID = ".$DTL_ID;
    						$this->db->query($updateDtlStuff);

    						$this->db->where('REAL_STUFF_CONT', $NO_CONTAINER)->$this->db->where('REAL_STUFF_NOREQ', $NO_REQUEST)->$this->db->delete('TX_REAL_STUFF');

    						$cek_jumlah_dtl = $this->db->where('STUFF_DTL_HDR_ID',$HDR_ID)->from('TX_REQ_STUFF_DTL')->count_all_results();
							$cek_jumlah_out = $this->db->where('STUFF_DTL_HDR_ID',$HDR_ID)->where('STUFF_DTL_ACTIVE','T')->from('TX_REQ_STUFF_DTL')->count_all_results();
							if($cek_jumlah_out != $cek_jumlah_dtl){
								$this->db->set('STUFF_STATUS',1)->where('STUFF_ID',$HDR_ID)->update('TX_REQ_STUFF_HDR');
							}
		                }else if($KEGIATAN == 'BORDER GATE IN'){
		                	// Do Nothing on NPKS
		                	$this->db->where('GATE_CONT', $NO_CONTAINER)->$this->db->where('GATE_NOREQ', $NO_REQUEST)->$this->db->where('GATE_ACTIVITY', '3')->$this->db->delete('TX_GATE');
		                	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '0' WHERE SERVICES_METHOD = 'getGateInFromTPK' AND SERVICES_REQ_XML = '".$NO_REQUEST."~".$NO_CONTAINER."' ";
							$this->db->query($updateServices);
		                }else if($KEGIATAN == 'BORDER GATE OUT'){
			                $sqlDtl = "SELECT A.REQ_DTL_ID, B.REQ_ID FROM TX_REQ_DELIVERY_DTL A INNER JOIN TX_REQ_DELIVERY_HDR B ON B.REQ_ID = A.REQ_HDR_ID WHERE A.REQ_DTL_CONT = '".$NO_CONTAINER."' AND REQ_BRANCH_ID = ".$branch." AND B.REQ_NO = '".$NO_REQUEST."'";
					        $resultID = $this->db->query($sqlDtl)->row_array();
					        $DTL_ID = $resultID['REQ_DTL_ID'];
					        $HDR_ID = $resultID['REQ_ID'];
							$updateDtlDelivery = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_ACTIVE = 'Y', REQ_DTL_STATUS = '1' WHERE REQ_DTL_ID = ".$DTL_ID;
							$this->db->query($updateDtlDelivery);

							$this->db->where('REAL_YARD_CONT', $NO_CONTAINER)->$this->db->where('REAL_YARD_REQ_NO', $NO_REQUEST)->$this->db->where('REAL_YARD_TYPE', '1')->$this->db->where('REAL_YARD_STATUS', '2')->$this->db->delete('TX_REAL_YARD');

							$cek_jumlah_dtl = $this->db->where('REQ_HDR_ID',$HDR_ID)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
							$cek_jumlah_out = $this->db->where('REQ_HDR_ID',$HDR_ID)->where('REQ_DTL_ACTIVE','T')->from('TX_REQ_DELIVERY_DTL')->count_all_results();
							if($cek_jumlah_out != $cek_jumlah_dtl){
								$this->db->set('REQUEST_STATUS',1)->where('REQ_ID',$HDR_ID)->update('TX_REQ_DELIVERY_HDR');
							}

							$this->db->where('GATE_CONT', $NO_CONTAINER)->$this->db->where('GATE_NOREQ', $NO_REQUEST)->$this->db->where('GATE_ACTIVITY', '4')->$this->db->delete('TX_GATE');
		                	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '0' WHERE SERVICES_METHOD = 'getGateOutToTPK' AND SERVICES_REQ_XML = '".$NO_REQUEST."' ";
							$this->db->query($updateServices);

						}else if($KEGIATAN == 'PLACEMENT'){
							$sqlDtl = "SELECT A.REQUEST_DTL_ID, B.REQUEST_ID FROM TX_REQ_RECEIVING_DTL A INNER JOIN TX_REQ_RECEIVING_HDR B ON B.REQUEST_ID = A.REQUEST_HDR_ID WHERE A.REQUEST_DTL_CONT = '".$NO_CONTAINER."' AND B.REQUEST_NO = '".$NO_REQUEST."' AND B.REQUEST_BRANCH_ID = ".$branch."";
					        $resultID = $this->db->query($sqlDtl)->row_array();
					        $DTL_ID = $resultID['REQUEST_DTL_ID'];
					        $HDR_ID = $resultID['REQUEST_ID'];
							$updateDtlReceiving = "UPDATE TX_REQ_RECEIVING_DTL SET REQ_DTL_ACTIVE = 'Y', REQ_DTL_STATUS = '0' WHERE REQUEST_DTL_ID = ".$DTL_ID;
							$this->db->query($updateDtlReceiving);

							$this->db->where('REAL_YARD_CONT', $NO_CONTAINER)->$this->db->where('REAL_YARD_REQ_NO', $NO_REQUEST)->$this->db->where('REAL_YARD_ACTIVITY', '3')->$this->db->delete('TX_REAL_YARD');
						}else if($KEGIATAN == 'GATE IN'){
							$sqlDtl = "SELECT A.REQUEST_DTL_ID, B.REQUEST_ID FROM TX_REQ_RECEIVING_DTL A INNER JOIN TX_REQ_RECEIVING_HDR B ON B.REQUEST_ID = A.REQUEST_HDR_ID WHERE A.REQUEST_DTL_CONT = '".$NO_CONTAINER."' AND B.REQUEST_NO = '".$NO_REQUEST."' AND B.REQUEST_BRANCH_ID = ".$branch."";
					        $resultID = $this->db->query($sqlDtl)->row_array();
					        $DTL_ID = $resultID['REQUEST_DTL_ID'];
					        $HDR_ID = $resultID['REQUEST_ID'];
							$updateDtlReceiving = "UPDATE TX_REQ_RECEIVING_DTL SET REQ_DTL_ACTIVE = 'Y', REQ_DTL_STATUS = '0' WHERE REQUEST_DTL_ID = ".$DTL_ID;
							$this->db->query($updateDtlReceiving);

							$this->db->where('GATE_CONT', $NO_CONTAINER)->$this->db->where('GATE_NOREQ', $NO_REQUEST)->$this->db->where('GATE_ACTIVITY', '3')->$this->db->delete('TX_GATE');
						}else if($KEGIATAN == 'GATE OUT'){
							$sqlDtl = "SELECT A.REQ_DTL_ID, B.REQ_ID FROM TX_REQ_DELIVERY_DTL A INNER JOIN TX_REQ_DELIVERY_HDR B ON B.REQ_ID = A.REQ_HDR_ID WHERE A.REQ_DTL_CONT = '".$NO_CONTAINER."' AND REQ_BRANCH_ID = ".$branch." AND B.REQ_NO = '".$NO_REQUEST."'";
					        $resultID = $this->db->query($sqlDtl)->row_array();
					        $DTL_ID = $resultID['REQ_DTL_ID'];
					        $HDR_ID = $resultID['REQ_ID'];
							$updateDtlDelivery = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_ACTIVE = 'Y', REQ_DTL_STATUS = '0' WHERE REQ_DTL_ID = ".$DTL_ID;
							$this->db->query($updateDtlDelivery);

							$this->db->where('GATE_CONT', $NO_CONTAINER)->$this->db->where('GATE_NOREQ', $NO_REQUEST)->$this->db->where('GATE_ACTIVITY', '4')->$this->db->delete('TX_GATE');
		                }

		                $SelectMaterCont = "SELECT CONTAINER_TYPE, CONTAINER_SIZE FROM TM_CONTAINER WHERE CONTAINER_NO = '".$NO_CONTAINER."' AND CONTAINER_BRANCH_ID = ".$branch." ";
						$result = $this->db->query($SelectMaterCont)->row_array();
						//insert history container
						$this->db->query("CALL ADD_HISTORY_CONTAINER(
									'".$NO_CONTAINER."',
									'".$NO_REQUEST."',
									'".$TGL_REQUEST."',
									'".$result['CONTAINER_SIZE']."',
									'".$result['CONTAINER_TYPE']."',
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									$stat,
									'".$KEGIATAN."',
									NULL,
									NULL,
									".$branch.",
									NULL,
									NULL)");

		                $i++;
		            }
	                $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS  = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
    				$this->db->query($updateServices);
		        }else{
		        	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
    				$this->db->query($updateServices);
		        }
			}
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
	}

	function getListContGateOut($noreq, $export = null){
		$client = SERVICE_SERVER."/api.php";

        $method = 'getListContGateOut'; //method

        $params = array('string0'=>'npks','string1'=>'12345','string2'=>$noreq);

        $ServiceID = date('YmdHis').rand(100,999);
        $response = $this->call_service($client, $method,$params);

        $xml = xml2ary($response);
        $valResponse = $xml['document']['_c']['respon']['_v'];

		$arrdata = array();
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
				$arrdata[$i]['CONT_NO'] = $data['NO_CONTAINER']['_v'];
				$arrdata[$i]['NO_REQUEST'] = $data['NO_REQUEST']['_v'];
				$arrdata[$i]['STATUS'] = $data['STATUS']['_v'];
				$arrdata[$i]['TGL_UPDATE'] = date('d/m/Y H:i:s',strtotime($data['TGL_UPDATE']['_v']));
				$i++;
			}
			$return = json_encode($arrdata);
		}else{
			$return = '[{"NO_REQUEST":"Tidak ada data"}]';
		}

		$json = $return;

		if($export == 'pdf'){
			$this->load->library('m_pdf');
			$data['data'] = json_decode($json);
			// print_r($data); die();
	    $name = 'Gato_repo.pdf';
	    $html = $this->load->view('gatoinyard/pdf',$data,true);
	    //rite some HTML code:
	    $this->m_pdf->export->WriteHTML($html);
	    $this->m_pdf->export->Output($name,'I');
		}
		elseif($export == 'excel'){
			$data['data'] = json_decode($json);
			// print_r($data); die();
			$name = 'Gato_repo.pdf';
			$html = $this->load->view('gatoinyard/pdf',$data,true);
			header("Content-type: application/vnd-ms-excel");
	    header("Content-Disposition: attachment; filename=$name.xls");
	    header("Pragma: no-cache");
	    header("Expires: 0");
	    echo $html;
		}
		else{
			echo $json;
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

	function get_all_methods(){
		$class = new ReflectionClass($this);
		$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
		$dataMethods;

		$count = 0;
		foreach ($methods as $method) {
			if($method->name != 'get_all_methods' and $method->name != 'split_character' and $method->name != 'get_all_api' and $method->name != 'call_service' and $method->name != 'getServer' and $method->name != '__construct' and $method->name != 'getcrontab' and $method->name != 'index' and $method->name != 'get_instance'){
				$dataMethods[$count]["METHOD"] = $method->name;
				$count++;
			}
		}
		$dataJson = array (
	      'data' => $dataMethods,
	      'total' => count($dataMethods)
	    );
		echo json_encode($dataJson);
	}

	function split_character($str,$length){
		$tot_char =  strlen($str);
		$per = 4000;
		$start = 0;
		$tot_per = round((int)$tot_char/$per);
		$val = '';
		for($i=0;$i<=$tot_per;$i++){
			$val .= "to_clob('".substr($str,$start,$per)."')||";
			$start = $start + $per;
		}
		$str = substr($val,0,-2);
		return $str;
	}

	function get_all_api(){
		$time_start = microtime(true);
		$branch = $this->uri->segment(3);

		$params[] = $branch;

		$sql = "SELECT API_CLIENT_NAME FROM TR_API_CLIENT WHERE API_CLIENT_STATUS = 1 AND API_CLIENT_BRANCH = ? ORDER BY API_GROUP, API_GROUP_ORDER ASC";

		$allData = $this->db->query($sql,$params)->result();

		$this->load->helper('url');

		foreach ($allData as $data) {

			$func = $data->API_CLIENT_NAME;
			$this->$func($branch);
			echo "\r";
		}

		$time_end = microtime(true);
		$SQLCEK = ($time_end - $time_start);
		echo "Total time execute : ".round($SQLCEK,2)."<br>\n";
		echo "\r\r";
	}

	function getBatalSp2($branch = false){
		$time_start_new = microtime(true);
		echo "getBatalSp2 | ";
		$client = SERVICE_SERVER."/api.php";
				$method = 'getBatalSp2'; //method

				$sqlLastDateNota = "select TGL from (
														select TO_CHAR(CANCELLED_CREATE_DATE,'YYYYMMDDHH24MISS') TGL from  TH_CANCELLED  WHERE CANCELLED_STATUS IN (20) AND CANCELLED_BRANCH_ID = ".$branch."  order by CANCELLED_CREATE_DATE DESC
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
				$response = $this->split_character($response,4000);
				if($valResponse != 0){
					$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
										VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
			$this->db->query($insertServices);
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
					echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
					echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
		}
	}

	function parsingBatalSp2($branch = false){
		$time_start_new = microtime(true);
		echo "ParsingBatalSp2 | ";
    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getBatalSp2' order by services_req_date desc";
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
						$response = str_replace('&','&amp;', $response);
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
		                $REQ_NO = $data['REQ_NO']['_v'];
		                $JENIS = $data['JENIS']['_v'];
		                $NO_BA = $data['NO_BA']['_v'];
		                $TGL_REQUEST = $data['TGL_REQUEST']['_v'];

										$stat = 20;
										$kegiatan = 'Batal SP2';

						$insertCancelled = "INSERT INTO TH_CANCELLED (CANCELLED_NOREQ, CANCELLED_NO_CONT, CANCELLED_STATUS, CANCELLED_NOREQ_OLD, CANCELLED_REQ_DATE, CANCELLED_MARK, CANCELLED_BRANCH_ID)
        									VALUES ('".$REQ_NO."','".$NO_CONTAINER."','".$stat."','".$REQ_NO."', TO_DATE('".$TGL_REQUEST."','MM/DD/YYYY HH24:MI:SS'), '".$NO_BA."', ".$branch.")";
       			$this->db->query($insertCancelled);

						$sqlDtl = "SELECT A.REQ_DTL_ID, B.REQ_ID FROM TX_REQ_DELIVERY_DTL A INNER JOIN TX_REQ_DELIVERY_HDR B ON B.REQ_ID = A.REQ_HDR_ID WHERE A.REQ_DTL_CONT = '".$NO_CONTAINER."' AND REQ_BRANCH_ID = ".$branch." AND B.REQ_NO = '".$REQ_NO."'";
						$resultID = $this->db->query($sqlDtl)->row_array();
						$DTL_ID = $resultID['REQ_DTL_ID'];
						$HDR_ID = $resultID['REQ_ID'];
						$updateDtlDelivery = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_ACTIVE = 'T', REQ_DTL_STATUS = 2, REQ_DTL_CANCELLED = 'Y' WHERE REQ_DTL_ID = ".$DTL_ID;
						$this->db->query($updateDtlDelivery);

						$cek_jumlah_dtl = $this->db->where('REQ_HDR_ID',$HDR_ID)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
						$cek_jumlah_out = $this->db->where('REQ_HDR_ID',$HDR_ID)->where('REQ_DTL_ACTIVE','T')->from('TX_REQ_DELIVERY_DTL')->count_all_results();
						if($cek_jumlah_out == $cek_jumlah_dtl){
							$this->db->set('REQUEST_STATUS',2)->where('REQ_ID',$HDR_ID)->update('TX_REQ_DELIVERY_HDR');
						}

						$SelectMaterCont = "SELECT CONTAINER_TYPE, CONTAINER_SIZE FROM TM_CONTAINER WHERE CONTAINER_NO = '".$NO_CONTAINER."' AND CONTAINER_BRANCH_ID = ".$branch."";
						$result = $this->db->query($SelectMaterCont)->row_array();
						//insert history container
						$this->db->query("CALL ADD_HISTORY_CONTAINER(
									'".$NO_CONTAINER."',
									'".$REQ_NO."',
									'".$TGL_REQUEST."',
									'".$result['CONTAINER_SIZE']."',
									'".$result['CONTAINER_TYPE']."',
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									NULL,
									$stat,
									'".$kegiatan."',
									NULL,
									NULL,
									".$branch.",
									NULL,
									NULL)");

		                $i++;
		            }
	          $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS  = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
    				$this->db->query($updateServices);
		        }else{
		        	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
    				$this->db->query($updateServices);
		        }
			}
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
			echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }else{
			$time_end = microtime(true);
			$SQLCEK = ($time_end - $time_start_new);
        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
        }
    }

		function getBatalReceiving($branch = false){
			$time_start_new = microtime(true);
			echo "getBatalReceivingMTY | ";
			$client = SERVICE_SERVER."/api.php";
					$method = 'getBatalReceivingMTY'; //method

					$sqlLastDateNota = "select TGL from (
															select TO_CHAR(CANCELLED_CREATE_DATE,'YYYYMMDDHH24MISS') TGL from  TH_CANCELLED  WHERE CANCELLED_STATUS IN (16) AND CANCELLED_BRANCH_ID = ".$branch."  order by CANCELLED_CREATE_DATE DESC
															)A where rownum =1";

					$resultLastDateNota = $this->db->query($sqlLastDateNota);
					$totalservice = $resultLastDateNota->num_rows();

					if($totalservice <= 0){
						$lastDateNota = date('YmdHis',strtotime("-7 days"));
					}else{
						$resultLastDateNota = $resultLastDateNota->result_array();
						$lastDateNota = $resultLastDateNota[0]['TGL'];
					}

					$params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

					$ServiceID = date('YmdHis').rand(100,999);
					$response = $this->call_service($client, $method,$params);

					$xml = xml2ary($response);

					$valResponse = $xml['document']['_c']['respon']['_v'];
					$response = $this->split_character($response,4000);
					if($valResponse != 0){
						$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
											VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
				$this->db->query($insertServices);
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
						echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
			}else{
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
						echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
			}
		}

		function parsingBatalReceiving($branch = false){
			$time_start_new = microtime(true);
			echo "ParsingBatalReceivingMTY | ";
	    	$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getBatalReceivingMTY' order by services_req_date desc";
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
							$response = str_replace('&','&amp;', $response);
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
			                $REQ_NO = $data['REQ_NO']['_v'];
			                $JENIS = $data['JENIS']['_v'];
			                $NO_BA = $data['NO_BA']['_v'];
			                $TGL_REQUEST = $data['TGL_REQUEST']['_v'];

											$stat = 16;
											$kegiatan = 'Batal Receiving';

							$insertCancelled = "INSERT INTO TH_CANCELLED (CANCELLED_NOREQ, CANCELLED_NO_CONT, CANCELLED_STATUS, CANCELLED_NOREQ_OLD, CANCELLED_REQ_DATE, CANCELLED_MARK, CANCELLED_BRANCH_ID)
	        									VALUES ('".$REQ_NO."','".$NO_CONTAINER."','".$stat."','".$REQ_NO."', TO_DATE('".$TGL_REQUEST."','MM/DD/YYYY HH24:MI:SS'), '".$NO_BA."', ".$branch.")";
	       					$this->db->query($insertCancelled);

							$sqlDtl = "SELECT A.REQUEST_DTL_ID, B.REQUEST_ID FROM TX_REQ_RECEIVING_DTL A INNER JOIN TX_REQ_RECEIVING_HDR B ON B.REQUEST_ID = A.REQUEST_HDR_ID WHERE A.REQUEST_DTL_CONT = '".$NO_CONTAINER."' AND B.REQUEST_NO = '".$REQ_NO."' AND B.REQUEST_BRANCH_ID = ".$branch."";
							$cekrow = $this->db->query($sqlDtl)->num_rows();
							if($cekrow <= 0){
								$i++;
								continue;
							}

							$resultID = $this->db->query($sqlDtl)->row_array();
							$DTL_ID = $resultID['REQUEST_DTL_ID'];
							$HDR_ID = $resultID['REQUEST_ID'];

							$updateDtlReceiving = "UPDATE TX_REQ_RECEIVING_DTL SET REQUEST_DTL_STATUS = '2' WHERE REQUEST_DTL_ID = ".$DTL_ID;
							$this->db->query($updateDtlReceiving);

							$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_RECEIVING_DTL WHERE REQUEST_HDR_ID = (SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO = '".$REQ_NO."' AND REQUEST_BRANCH_ID = ".$branch.")")->row()->JML;
							$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_RECEIVING_DTL WHERE REQUEST_HDR_ID = (SELECT REQUEST_ID FROM TX_REQ_RECEIVING_HDR WHERE REQUEST_NO = '".$REQ_NO."' AND REQUEST_BRANCH_ID = ".$branch.") AND REQUEST_DTL_STATUS = '2' ")->row()->JML;

							if($cek_tot_dtl == $cek_tot_dtl_T){
								//update request receiving hdr
								$updateRecHDR = "UPDATE TX_REQ_RECEIVING_HDR SET REQUEST_STATUS = '2' WHERE REQUEST_NO = '".$REQ_NO."' AND REQUEST_BRANCH_ID = ".$branch." ";
								$this->db->query($updateRecHDR);
							}


							$SelectMaterCont = "SELECT CONTAINER_TYPE, CONTAINER_SIZE FROM TM_CONTAINER WHERE CONTAINER_NO = '".$NO_CONTAINER."' AND CONTAINER_BRANCH_ID = ".$branch."";
							$result = $this->db->query($SelectMaterCont)->row_array();
							//insert history container
							$this->db->query("CALL ADD_HISTORY_CONTAINER(
										'".$NO_CONTAINER."',
										'".$REQ_NO."',
										'".$TGL_REQUEST."',
										'".$result['CONTAINER_SIZE']."',
										'".$result['CONTAINER_TYPE']."',
										NULL,
										NULL,
										NULL,
										NULL,
										NULL,
										NULL,
										$stat,
										'".$kegiatan."',
										NULL,
										NULL,
										".$branch.",
										NULL,
										NULL)");

			                $i++;
			            }
		          $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS  = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
	    				$this->db->query($updateServices);
			        }else{
			        	$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
	    				$this->db->query($updateServices);
			        }
				}
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
				echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
	        }else{
				$time_end = microtime(true);
				$SQLCEK = ($time_end - $time_start_new);
	        	echo "tidak ada data | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
	        }
	    }


			function getStuffingAlihKapal($branch = false){
				$time_start_new = microtime(true);
				echo "getStuffingAlihKapal | ";
				$client = SERVICE_SERVER."/api.php";
				$method = 'getStuffingAlihKapal'; //method

				$sqlLastDateNota = "select TGL from (
									select TO_CHAR(STUFF_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_STUFF_HDR WHERE STUFF_PAID_DATE IS NOT NULL AND STUFF_ALIH_KAPAL = 'Y' AND STUFF_BRANCH_ID = ".$branch." order by STUFF_PAID_DATE DESC
									)A where rownum =1";
		        $resultLastDateNota = $this->db->query($sqlLastDateNota);
		        $totalservice = $resultLastDateNota->num_rows();

		        if($totalservice <= 0){
		        	$lastDateNota = date('YmdHis',strtotime("-5 days"));
		        }else{
		        	$resultLastDateNota = $resultLastDateNota->result_array();
		        	$lastDateNota = $resultLastDateNota[0]['TGL'];
		        }

				//$lastDateNota = '20181220010101';
				$params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

				$ServiceID = date('YmdHis').rand(100,999);
				$response = $this->call_service($client, $method,$params);

				$xml = xml2ary($response);
		        $valResponse = $xml['document']['_c']['respon']['_v'];
		        $response = $this->split_character($response,4000);
		        if($valResponse != 0){
					$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
										VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
					$this->db->query($insertServices);
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
					echo "getStuffingAlihKapal -> Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
				}else{
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
					echo "getStuffingAlihKapal -> ".$xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
				}
			}

			function parsingStuffingAlihKapal($branch = false){
				$time_start_new = microtime(true);
				echo "ParsingStuffingAlihKapal | ";
				$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getStuffingAlihKapal' order by services_req_date desc";
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
							$response = str_replace('&','&amp;', $response);
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
											$STATUS_GATE = $header['STATUS_GATE']['_v'];

			                				if($PERP_KE == ""){
			                					$PERP_KE = 0;
			                				}

											$sqlcek = "SELECT STUFF_NO, STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO='".$REQ_NO."' AND STUFF_BRANCH_ID = ".$branch."";
											$resultCek = $this->db->query($sqlcek);
											$totalcek =  $resultCek->num_rows();

											// if($totalcek <=0){
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

													if($totalcek <=0){
														$qlID = "SELECT SEQ_TX_STUFF_HDR.NEXTVAL AS ID FROM DUAL";
														$resultID = $this->db->query($qlID)->result_array();
														$IDheader = $resultID[0]['ID'];

														$STUFF_ORIGIN = 'INTERNAL';
														if($STUFFING_DARI == 'TPK')
															$STUFF_ORIGIN = 'TPK';

														if($STATUS_GATE == 1){
														$insertHDR = "INSERT INTO TX_REQ_STUFF_HDR (STUFF_ID, STUFF_NO, STUFF_CONSIGNEE_ID, STUFF_BRANCH_ID, STUFF_CREATE_DATE, STUFF_NOTA_DATE, STUFF_NOTA_NO, STUFF_PAID_DATE, STUFF_NO_BOOKING, STUFF_NO_UKK, STUFF_NOREQ_RECEIVING, STUFF_EXTEND_FROM, STUFF_EXTEND_LOOP, STUFF_ORIGIN, STUFF_STATUS, STUFF_ALIH_KAPAL) VALUES
																				(".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",".$branch.", TO_DATE('".$REQ_STUFF_DATE."','MM/DD/YYYY HH24:MI:SS'),
																				TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),'".$NO_NOTA."',TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'), '".$NO_BOOKING."', '".$NO_UKK."','".$NO_REQUEST_RECEIVING."', '".$PERP_DARI."', ".$PERP_KE.",'".$STUFF_ORIGIN."','2', 'Y')";
														}
														else if($STATUS_GATE == 3){
															$insertHDR = "INSERT INTO TX_REQ_STUFF_HDR (STUFF_ID, STUFF_NO, STUFF_CONSIGNEE_ID, STUFF_BRANCH_ID, STUFF_CREATE_DATE, STUFF_NOTA_DATE, STUFF_NOTA_NO, STUFF_PAID_DATE, STUFF_NO_BOOKING, STUFF_NO_UKK, STUFF_NOREQ_RECEIVING, STUFF_EXTEND_FROM, STUFF_EXTEND_LOOP, STUFF_ORIGIN, STUFF_STATUS, STUFF_ALIH_KAPAL) VALUES
																					(".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",".$branch.", TO_DATE('".$REQ_STUFF_DATE."','MM/DD/YYYY HH24:MI:SS'),
																					TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),'".$NO_NOTA."',TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'), '".$NO_BOOKING."', '".$NO_UKK."','".$NO_REQUEST_RECEIVING."', '".$PERP_DARI."', ".$PERP_KE.",'".$STUFF_ORIGIN."','1', 'Y')";
														}
														$resultHDR = $this->db->query($insertHDR);
													}else{
														$resultHDR = true;
														$resultCek = $resultCek->result_array();
														$IDheader = $resultCek[0]['STUFF_ID'];
													}

													if($resultHDR){

															//cek detil container ada tidak dari TPK
															$contFromTPK = 'T';
															$totalDTL = count($detail);
															$b = 0;
															$arrTpk = array();
															$ketPerpanjangan = 'Request Stuffing';

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

															if($PERP_DARI !=''){
																$ketPerpanjangan = 'Perpanjangan Stuffing';
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

																	$sqlcekdetilnya = "SELECT * FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID='".$IDheader."' AND STUFF_DTL_CONT = '".$REQ_DTL_CONT."' ";
																	$resultCekdetilNya = $this->db->query($sqlcekdetilnya);
																	$totalcekDetilnya = $resultCekdetilNya->num_rows();

																	if($totalcekDetilnya <= 0 ){

																		if(($STUFFING_DARI == 'TPK') && ($PERP_DARI =='') && ($contFromTPK == 'Y')){
																			$ServiceID = date('YmdHis').rand(100,999);
																			$method = 'getGateInFromTPK'; //method
																			$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
																								VALUES (".$ServiceID.",'".$method."','".$NO_REQUEST_RECEIVING."~".$REQ_DTL_CONT."','0')";
																			// $insert = $this->db->query($insertServices);
																		}

																		if($STATUS_GATE == 1){
																			$insertDTL = "INSERT INTO TX_REQ_STUFF_DTL (STUFF_DTL_HDR_ID, STUFF_DTL_CONT, STUFF_DTL_CONT_HAZARD, STUFF_DTL_CONT_SIZE, STUFF_DTL_CONT_TYPE, STUFF_DTL_COMMODITY, STUFF_DTL_REMARK_SP2, STUFF_DTL_ORIGIN,STUFF_DTL_START_STUFF_PLAN,STUFF_DTL_END_STUFF_PLAN, STUFF_DTL_CONT_STATUS, STUFF_DTL_STATUS, STUFF_DTL_ACTIVE)
																									VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."','".$REQ_DTL_REMARK_SP2."','".$REQ_DTL_ORIGIN."', TO_DATE('".$STUFF_DTL_START_STUFF_PLAN."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$STUFF_DTL_END_STUFF_PLAN."','MM/DD/YYYY HH24:MI:SS'), 'MTY', 2, 'T')";
																		}
																		else if($STATUS_GATE == 3) {
																			$insertDTL = "INSERT INTO TX_REQ_STUFF_DTL (STUFF_DTL_HDR_ID, STUFF_DTL_CONT, STUFF_DTL_CONT_HAZARD, STUFF_DTL_CONT_SIZE, STUFF_DTL_CONT_TYPE, STUFF_DTL_COMMODITY, STUFF_DTL_REMARK_SP2, STUFF_DTL_ORIGIN,STUFF_DTL_START_STUFF_PLAN,STUFF_DTL_END_STUFF_PLAN, STUFF_DTL_CONT_STATUS)
																									VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."','".$REQ_DTL_REMARK_SP2."','".$REQ_DTL_ORIGIN."', TO_DATE('".$STUFF_DTL_START_STUFF_PLAN."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$STUFF_DTL_END_STUFF_PLAN."','MM/DD/YYYY HH24:MI:SS'), 'MTY')";
																		}
																		$resultDtl = $this->db->query($insertDTL);
																		if($resultDtl){
																				echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
																		}else{
																				echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
																		}

																		if($PERP_DARI !=""){
																			$updateStuffTDL = "UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_ACTIVE = 'T', STUFF_DTL_STATUS = '2' WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch.") AND STUFF_DTL_CONT = '".$REQ_DTL_CONT."'  ";
																			$this->db->query($updateStuffTDL);

																			$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch.")")->row()->JML;
																			$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch.") AND STUFF_DTL_ACTIVE = 'T' ")->row()->JML;

																			if($cek_tot_dtl == $cek_tot_dtl_T){
																				$updateStuffHDR = "UPDATE TX_REQ_STUFF_HDR SET STUFF_STATUS = '2' WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch." ";
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
																			'".$ketPerpanjangan."',
																			NULL,
																			NULL,
																			".$branch.",
																			'".$REQ_DTL_ORIGIN."',
																			NULL)");
																}

																	$a++;
															}



													}else{
															echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
															$error++;
													}
											// }else{
											// 		echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
											// }
									$i++;
									}
									$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
									$this->db->query($updateServices);

							}else{
									 $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
								$this->db->query($updateServices);
								 echo("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
							}
						}
						//start call nodejs
							$updateStuffingProcess = curl_init(SERVICE_SERVER_NODEJS."/updateStuffingProcess?branch=".$branch."");
							curl_exec($updateStuffingProcess);
							curl_close($updateStuffingProcess);
							// $updateGateJobTruckManager = curl_init(SERVICE_SERVER_NODEJS."/updateGateJobTruckManager?branch=".$branch."");
							// curl_exec($updateGateJobTruckManager );
							// curl_close($updateGateJobTruckManager );
						//end call nodejs
						$time_end = microtime(true);
						$SQLCEK = ($time_end - $time_start_new);
						echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
					}else{
						$time_end = microtime(true);
						$SQLCEK = ($time_end - $time_start_new);
						echo "tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
					}
			}

			function getDeliveryExRepo($branch = false){
				$time_start_new = microtime(true);
				echo "getDeliveryExRepo | ";
				//$client = SERVICE_SERVER."/api.php";
				$client = SERVICE_SERVER."/api.php";

						$method = 'getDeliveryExRepo'; //method

						$sqlLastDateNota = "select TGL from (
																select TO_CHAR(REQUEST_PAID_DATE,'YYYYMMDDHH24MISS') TGL from  TX_REQ_DELIVERY_HDR  WHERE REQUEST_PAID_DATE IS NOT NULL AND REQUEST_ALIH_KAPAL = 'Y' AND REQ_BRANCH_ID = ".$branch." order by REQUEST_PAID_DATE DESC
																)A where rownum =1";

						$resultLastDateNota = $this->db->query($sqlLastDateNota);
						$totalservice = $resultLastDateNota->num_rows();

						if($totalservice <= 0){
							$lastDateNota = date('YmdHis',strtotime("-1 hours"));
						}else{
							$resultLastDateNota = $resultLastDateNota->result_array();
							$lastDateNota = $resultLastDateNota[0]['TGL'];
						}
				// $lastDateNota = '20181220010101';
						$params = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota);

						$ServiceID = date('YmdHis').rand(100,999);
						$response = $this->call_service($client, $method,$params);

						$xml = xml2ary($response);
						$valResponse = $xml['document']['_c']['respon']['_v'];
						$response = $this->split_character($response,4000);
						if($valResponse != 0){
							$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_RESP_XML, SERVICES_STATUS)
												VALUES (".$ServiceID.",'".$method."','".$lastDateNota."',".$response.",'1')";
					$this->db->query($insertServices);
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
							echo "Sukses Insert | ".$ServiceID." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
				}else{
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
							echo $xml['document']['_c']['URresponse']['_v']." | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
				}
			}

			function parsingDeliveryExRepo($branch = false){
				$time_start_new = microtime(true);
				echo "ParsingDeliveryExRepo | ";
				$sqlservices = "select SERVICES_ID, SERVICES_RESP_XML from TX_SERVICES where services_status = '1' AND services_method = 'getDeliveryExRepo' order by services_req_date desc";
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
							$response = str_replace('&','&amp;', $response);
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

											$sqlcek = "SELECT REQ_NO, REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO='".$REQ_NO."' AND REQ_BRANCH_ID = ".$branch." ";
											$resultCek = $this->db->query($sqlcek);
											$totalcek = $resultCek->num_rows();

											// if($totalcek <=0){
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

													if($totalcek <=0){

														$qlID = "SELECT SEQ_REQ_DELIVERY_HDR.NEXTVAL AS ID FROM DUAL";
														$resultID = $this->db->query($qlID)->result_array();
														$IDheader = $resultID[0]['ID'];

														$insertHDR = "INSERT INTO TX_REQ_DELIVERY_HDR (REQ_ID, REQ_NO, REQ_CONSIGNEE_ID, REQ_BRANCH_ID, REQ_MARK, REQ_DELIVERY_DATE, REQUEST_NOTA_DATE, REQUEST_PAID_DATE, REQUEST_TO, REQUEST_STATUS, REQUEST_EXTEND_FROM,REQUEST_EXTEND_LOOP, REQUEST_ALIH_KAPAL) VALUES
																				(".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",".$branch.",'".$REQ_MARK."', TO_DATE('".$REQ_DELIVERY_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'),'".$DELIVERY_KE."', '1','".$PERP_DARI."',".$PERP_KE.", 'Y')";
														$resultHDR = $this->db->query($insertHDR);
													}else{
														$resultHDR = true;
														$resultCek = $resultCek->result_array();
														$IDheader = $resultCek[0]['REQ_ID'];
													}

													if($resultHDR){

															// if($DELIVERY_KE == 'TPK' && $PERP_DARI ==''){
															// 	$ServiceID = date('YmdHis').rand(100,999);
														    //     $method = 'getGateOutToTPK'; //method
														    //     $insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
														    //     					VALUES (".$ServiceID.",'".$method."','".$REQ_NO."','0')";
														    //     $insert = $this->db->query($insertServices);
															// }

															//echo $i.", sukses | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
															$totalDTL = count($detail);
															$a = 0;
															while($a < $totalDTL){
																	if($totalDTL == 1){
																			$detailroot = $detail['_c'];
																	}else{
																			$detailroot = $detail[$a]['_c'];
																	}

																	$sqlcekdetilnya = "SELECT * FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID='".$IDheader."' AND REQ_DTL_CONT = '".trim($detailroot['REQ_DTL_CONT']['_v'])."' ";
																	$resultCekdetilNya = $this->db->query($sqlcekdetilnya);
																	$totalcekDetilnya = $resultCekdetilNya->num_rows();

																	if($totalcekDetilnya <= 0){

																		$REQ_DTL_CONT = trim($detailroot['REQ_DTL_CONT']['_v']);
																		$REQ_DTL_CONT_STATUS = trim($detailroot['REQ_DTL_CONT_STATUS']['_v']);
																		$REQ_DTL_COMMODITY = trim($detailroot['REQ_DTL_COMMODITY']['_v']);
																		$REQ_DTL_VIA = trim($detailroot['REQ_DTL_VIA']['_v']);
																		$REQ_DTL_CONT_HAZARD = trim($detailroot['REQ_DTL_CONT_HAZARD']['_v']);
																		$REQ_DTL_SIZE = trim($detailroot['REQ_DTL_SIZE']['_v']);
																		$REQ_DTL_TYPE = trim($detailroot['REQ_DTL_TYPE']['_v']);
																		$REQ_DTL_DEL_DATE = trim($detailroot['REQ_DTL_DEL_DATE']['_v']);
																		$REQ_DTL_NO_SEAL = trim($detailroot['REQ_DTL_NO_SEAL']['_v']);

																		if($DELIVERY_KE == 'TPK' && $PERP_DARI ==''){
																			$ServiceID = date('YmdHis').rand(100,999);
																			$method = 'getGateOutToTPK'; //method
																			$insertServices = "INSERT INTO TX_SERVICES (SERVICES_ID, SERVICES_METHOD, SERVICES_REQ_XML, SERVICES_STATUS)
																								VALUES (".$ServiceID.",'".$method."','".$REQ_NO."~".$REQ_DTL_CONT."','0')";
																			// $insert = $this->db->query($insertServices);
																			// digantikan dengan modul on chasis
																		}

																		$insertDTL = "INSERT INTO TX_REQ_DELIVERY_DTL (REQ_HDR_ID, REQ_DTL_CONT, REQ_DTL_CONT_STATUS, REQ_DTL_CONT_HAZARD, REQ_DTL_CONT_SIZE, REQ_DTL_CONT_TYPE, REQ_DTL_COMMODITY, REQ_DTL_DEL_DATE, REQ_DTL_NO_SEAL)
																									VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_STATUS."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."',TO_DATE('".$REQ_DTL_DEL_DATE."','MM/DD/YYYY HH24:MI:SS'), '".$REQ_DTL_NO_SEAL."')";
																		$resultDtl = $this->db->query($insertDTL);
																		if($resultDtl){
																				echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
																		}else{
																				echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
																		}

																		if($PERP_DARI !=""){
																			echo $a.", perpanjangan dari  | ".$PERP_DARI."~".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
																			$updateDeliveryTDL = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_ACTIVE = 'T', REQ_DTL_STATUS = '2' WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = ".$branch.") AND REQ_DTL_CONT = '".$REQ_DTL_CONT."'  ";
																			$this->db->query($updateDeliveryTDL);

																			$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = ".$branch.")")->row()->JML;
																			$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = ".$branch.") AND REQ_DTL_ACTIVE = 'T' ")->row()->JML;

																			if($cek_tot_dtl == $cek_tot_dtl_T){
																				$updateStuffHDR = "UPDATE TX_REQ_DELIVERY_HDR SET REQUEST_STATUS = '2' WHERE REQ_NO = '".$PERP_DARI."' AND REQ_BRANCH_ID = ".$branch." ";
																				$this->db->query($updateStuffHDR);
																			}
																		}

																		//insert history container
																		$this->db->query("CALL ADD_HISTORY_CONTAINER(
																					'".$REQ_DTL_CONT."',
																					'".$REQ_NO."',
																					'".$REQ_DELIVERY_DATE."',
																					'".$REQ_DTL_SIZE."',
																					'".$REQ_DTL_TYPE."',
																					'".$REQ_DTL_CONT_STATUS."',
																					NULL,
																					NULL,
																					NULL,
																					NULL,
																					NULL,
																					4,
																					'Request Delivery',
																					NULL,
																					NULL,
																					".$branch.",
																					NULL,
																					NULL)");
																	}

																	$a++;
															}


													}else{
															echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
															$error++;
													}
											// }else{
											// 		echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
											// }
									$i++;
									}
									$updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
									$this->db->query($updateServices);
							}else{
									 $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
								 $this->db->query($updateServices);
								 echo("tidak ada data | ".date('Y-m-d H:i:s')."<br>\n");
							}
						}
						//start call nodejs
							// $updateGateJobManager = curl_init(SERVICE_SERVER_NODEJS."/updateGateJobManager?branch=".$branch."");
							// curl_exec($updateGateJobManager);
							// curl_close($updateGateJobManager);
							$updateSP2 = curl_init(SERVICE_SERVER_NODEJS."/updateSP2?branch=".$branch."");
							curl_exec($updateSP2);
							curl_close($updateSP2);
						//end call nodejs
						$time_end = microtime(true);
						$SQLCEK = ($time_end - $time_start_new);
						echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
					}else{
						$time_end = microtime(true);
						$SQLCEK = ($time_end - $time_start_new);
						echo("tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n");
					}
			}

			function ParsingStripping2($branch = false){
				$time_start_new = microtime(true);
				echo "ParsingStripping | ";
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
									print_r($xml); die();
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
			                $ALAMAT = htmlspecialchars($header['ALAMAT']['_v']);
			                $TANGGAL_LUNAS = $header['TANGGAL_LUNAS']['_v'];

			                $sqlcek = "SELECT STRIP_NO, STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO='".$REQ_NO."' AND STRIP_BRANCH_ID = ".$branch."";
			                $resultCek = $this->db->query($sqlcek);
			                $totalcek = $resultCek->num_rows();

			                // if($totalcek <=0){
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

								if($totalcek <=0){
									$qlID = "SELECT SEQ_REQ_STRIP_HDR.NEXTVAL AS ID FROM DUAL";
									$resultID = $this->db->query($qlID)->result_array();
									$IDheader = $resultID[0]['ID'];

									$STRIP_ORIGIN = 'INTERNAL';
									if($STRIP_DARI == 'TPK')
										$STRIP_ORIGIN = 'TPK';

									$insertHDR = "INSERT INTO TX_REQ_STRIP_HDR (STRIP_ID, STRIP_NO, STRIP_CONSIGNEE_ID, STRIP_BRANCH_ID, STRIP_DO, STRIP_BL, STRIP_CREATE_DATE, STRIP_NOTA_DATE, STRIP_NOTA_NO, STRIP_PAID_DATE, STRIP_NOREQ_RECEIVING, STRIP_EXTEND_FROM,STRIP_EXTEND_LOOP, STRIP_ORIGIN) VALUES
												(".$IDheader.", '".$REQ_NO."',".$CONSIGNE_ID.",".$branch.",'".$DO."','".$BL."',
													 TO_DATE('".$REQ_STRIP_DATE."','MM/DD/YYYY HH24:MI:SS'), TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),'".$NO_NOTA."',TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'), '".$NO_REQUEST_RECEIVING."','".$PERP_DARI."',".$PERP_KE.",'".$STRIP_DARI."')";
									$resultHDR = $this->db->query($insertHDR);
								}else{
									$resultHDR = true;
									$resultCek = $resultCek->result_array();
									$IDheader = $resultCek[0]['STRIP_ID'];
								}

			                    if($resultHDR){
									$ketPerpanjangan = 'Request Stripping';
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
									        // $insert = $this->db->query($insertServices);
			                            }

										$sqlcekdetilnya = "SELECT * FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID='".$IDheader."' AND STRIP_DTL_CONT = '".$REQ_DTL_CONT."' ";
										$resultCekdetilNya = $this->db->query($sqlcekdetilnya);
										$totalcekDetilnya = $resultCekdetilNya->num_rows();

										if($totalcekDetilnya <= 0){

											//cek counter container
											$cont_count = '';
											$sqlCekCounterCont = "SELECT CONTAINER_COUNTER FROM TM_CONTAINER WHERE CONTAINER_BRANCH_ID = ".$branch." AND CONTAINER_NO = '".$REQ_DTL_CONT."'";
											$resultCekCounterCont= $this->db->query($sqlCekCounterCont)->row_array();
											$totalCekCounterCont = $this->db->query($sqlCekCounterCont)->num_rows();

											if($totalCekCounterCont > 0) {
												if($STRIP_DARI == 'TPK') {
													$cont_count = (int)$resultCekCounterCont['CONTAINER_COUNTER'] + 1;
												}
												else{
													$cont_count = (int)$resultCekCounterCont['CONTAINER_COUNTER'];
												}
											}
											else{
												$cont_count = 1;
											}

											$insertDTL = "INSERT INTO TX_REQ_STRIP_DTL (STRIP_DTL_HDR_ID, STRIP_DTL_CONT, STRIP_DTL_DANGER, STRIP_DTL_CONT_SIZE, STRIP_DTL_CONT_TYPE, STRIP_DTL_COMMODITY, STRIP_DTL_STATUS, STRIP_DTL_ORIGIN,STRIP_DTL_START_STRIP_PLAN,STRIP_DTL_END_STRIP_PLAN, STRIP_DTL_CONT_STATUS, STRIP_DTL_COUNTER)
														VALUES (".$IDheader.", '".$REQ_DTL_CONT."','".$REQ_DTL_CONT_HAZARD."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."','1', '".$STRIP_DTL_ORIGIN."',TO_DATE('".$STRIP_DTL_START_STRIP_PLAN."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$STRIP_DTL_END_STRIP_PLAN."','MM/DD/YYYY HH24:MI:SS'), 'FCL', ".$cont_count.")";
											$resultDtl = $this->db->query($insertDTL);
											if($resultDtl){
												echo $a.", sukses detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
											}else{
												echo $a.", gagal detil | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";
											}

											if($PERP_DARI !=""){
												$ketPerpanjangan = 'Perpanjangan Stripping';
												$updateStripTDL = "UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_ACTIVE = 'T', STRIP_DTL_STATUS = '2' WHERE STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch.") AND STRIP_DTL_CONT = '".$REQ_DTL_CONT."'  ";
												$this->db->query($updateStripTDL);

												$cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch.")")->row()->JML;
												$cek_tot_dtl_T = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch.") AND STRIP_DTL_ACTIVE = 'T' ")->row()->JML;

												if($cek_tot_dtl == $cek_tot_dtl_T){
													$updateStripHDR = "UPDATE TX_REQ_STRIP_HDR SET STRIP_STATUS = '2' WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch." ";
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
														'".$ketPerpanjangan."',
														NULL,
														NULL,
														".$branch.",
														'".$STRIP_DARI."',
														NULL)");
										}
			                            $a++;
			                        }

			                    }else{
			                        echo $i.", gagal | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
			                        $error++;
			                    }
			                // }else{
			                //     echo "data sudah ada | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
			                // }
			            $i++;
			            }
			            $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '9', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
					  	$this->db->query($updateServices);
			        }else{
			             $updateServices = "UPDATE TX_SERVICES SET SERVICES_STATUS = '3', SERVICES_DATE_GENERATE = SYSDATE WHERE SERVICES_ID = '".$row['SERVICES_ID']."' AND SERVICES_STATUS = '1' ";
			           $this->db->query($updateServices);
			           echo("tidak ada data parsing stripping  | ".date('Y-m-d H:i:s')."<br>\n");
			        }
				  }
					//start call nodejs
						$updateStrippingProcess = curl_init(SERVICE_SERVER_NODEJS."/updateStrippingProcess?branch=".$branch."");
						curl_exec($updateStrippingProcess);
						curl_close($updateStrippingProcess);
						// $updateGateJobTruckManager = curl_init(SERVICE_SERVER_NODEJS."/updateGateJobTruckManager?branch=".$branch."");
						// curl_exec($updateGateJobTruckManager );
						// curl_close($updateGateJobTruckManager );
					//end call nodejs
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
					echo date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
			    }else{
					$time_end = microtime(true);
					$SQLCEK = ($time_end - $time_start_new);
			      echo "tidak ada data untuk digenerate | ".date('Y-m-d H:i:s')." | time execute : ".round($SQLCEK,2)."<br>\n";
			    }
			}

}
