<?php

include "framework/conf.php";
include "framework/_debug.php";
include "framework/_viewstate.php";
include "framework/init.php";


	/*$db = getDB();
	$query = "SELECT * from BILLING.TB_USER WHERE USERNAME = 'dama' ";
	$result = $db->query($query);
	//echo $result->RecordCount()."-";die;
 	if($result->RecordCount() >0)
	{
		$row = $result->fetchRow();
		echo $row['ID'];die();
	}*/


//echo R_PATH;die();
ini_set("display_errors", "0");

include('lib/xml2array.php');
require_once('lib/nusoap/nusoap.php');

$server = new soap_server();
$server->configureWSDL('ApiNbs', 'urn:ApiNbs');

$server->register('getDelivery',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:getDelivery',
	'urn:getDelivery#getDelivery',
    'rpc',
    'encoded',
    'Get Data Req Delivery'
);

$server->register('getReceiving',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:getReceiving',
	'urn:getReceiving#getReceiving',
    'rpc',
    'encoded',
    'Get Data Req Receiving'
);

$server->register('sendRelocation',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:sendRelocation',
	'urn:sendRelocation#sendRelocation',
    'rpc',
    'encoded',
    'Send Data Req Relocation'
);

$server->register('sendStripping',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:sendStripping',
	'urn:sendStripping#sendStripping',
    'rpc',
    'encoded',
    'Send Data Req Stripping'
);

$server->register('getStuffing',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:getStuffing',
	'urn:getStuffing#getStuffing',
    'rpc',
    'encoded',
    'Get Data Req Stuffing'
);

$server->register('getStripping',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:getStripping',
	'urn:getStripping#getStripping',
    'rpc',
    'encoded',
    'Get Data Req Stripping'
);

$server->register('getReceivingFromTPK',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:getReceivingFromTPK',
	'urn:getReceivingFromTPK#getReceivingFromTPK',
    'rpc',
    'encoded',
    'Get Data Req getReceivingFromTPK'
);

$server->register('getGateInFromTPK',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:getGateInFromTPK',
	'urn:getGateInFromTPK#getGateInFromTPK',
    'rpc',
    'encoded',
    'Get Data Req getGateInFromTPK'
);

$server->register('getGateOutToTPK',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:getGateOutToTPK',
	'urn:getGateOutToTPK#getGateOutToTPK',
    'rpc',
    'encoded',
    'Get Data Req getGateOutToTPK'
);

$server->register('getBatal',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:getBatal',
	'urn:getBatal#getBatal',
    'rpc',
    'encoded',
    'Get Data Batal dibilling'
);

//list service insert data ke billing

$server->register('setGateIn',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:setGateIn',
	'urn:setGateIn#setGateIn',
    'rpc',
    'encoded',
    'insert data gate in ke db billing'
);

$server->register('setGateOut',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:setGateOut',
	'urn:setGateOut#setGateOut',
    'rpc',
    'encoded',
    'insert data gate out ke db billing'
);

$server->register('setPlacement',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:setPlacement',
	'urn:setPlacement#setPlacement',
    'rpc',
    'encoded',
    'insert data Placement ke db billing'
);

$server->register('setPlacementAll',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:setPlacementAll',
	'urn:setPlacementAll#setPlacementAll',
    'rpc',
    'encoded',
    'insert data Shifting ke db billing'
);

$server->register('setRealStuffing',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:setRealStuffing',
	'urn:setRealStuffing#setRealStuffing',
    'rpc',
    'encoded',
    'insert data Realisasi Stuffing ke db billing'
);

$server->register('setRealStripping',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:setRealStripping',
	'urn:setRealStripping#setRealStripping',
    'rpc',
    'encoded',
    'insert data Realisasi Stripping ke db billing'
);

$server->register('renameContainer',
     array('string0'=> 'xsd:string',
	 		'string1'=> 'xsd:string',
			'string2'=> 'xsd:string'),
    array('return' => 'xsd:string'),
    'urn:renameContainer',
	'urn:renameContainer#renameContainer',
    'rpc',
    'encoded',
    'Rename Container'
);

function getDelivery($user, $pass, $xml)
{
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
		$query = "SELECT A.PERP_DARI, A.PERP_KE, A.NO_REQUEST, B.EMKL NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST, B.NO_NOTA, TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.DELIVERY_KE
				FROM REQUEST_DELIVERY A
				INNER JOIN NOTA_DELIVERY B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') > '".$xml."' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

		$result = $db->query($query);
		if($result->RecordCount() >0)
		{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$result = $result->getAll();
			$a = 0;
			foreach ($result as $row){$a++;
				$returnXML .= '<data>';
				$returnXML .= '<header>';
				$returnXML .= '<REQ_NO>'.$row['NO_REQUEST'].'</REQ_NO>';
				$returnXML .= '<REQ_DELIVERY_DATE>'.$row['TGL_REQUEST'].'</REQ_DELIVERY_DATE>';
				$returnXML .= '<NO_NOTA>'.$row['NO_NOTA'].'</NO_NOTA>';
				$returnXML .= '<TGL_NOTA>'.$row['TGL_NOTA'].'</TGL_NOTA>';
				$returnXML .= '<NM_CONSIGNEE>'.$row['NM_CONSIGNEE'].'</NM_CONSIGNEE>';
				$returnXML .= '<ALAMAT>'.$row['ALAMAT'].'</ALAMAT>';
				$returnXML .= '<REQ_MARK>'.$row['KETERANGAN'].'</REQ_MARK>';
				$returnXML .= '<NPWP>'.$row['NPWP'].'</NPWP>';
				$returnXML .= '<DELIVERY_KE>'.$row['DELIVERY_KE'].'</DELIVERY_KE>';
				$returnXML .= '<TANGGAL_LUNAS>'.$row['TANGGAL_LUNAS'].'</TANGGAL_LUNAS>';
				$returnXML .= '<PERP_DARI>'.$row['PERP_DARI'].'</PERP_DARI>';
				$returnXML .= '<PERP_KE>'.$row['PERP_KE'].'</PERP_KE>';
				$returnXML .= '</header>';
				$returnXML .= '<arrdetail>';
				$queryDTL = "SELECT A.NO_CONTAINER, A.STATUS, A.KOMODITI, A.VIA, A.HZ, B.SIZE_, B.TYPE_, TO_CHAR(A.TGL_DELIVERY,'MM/DD/YYYY HH24:MI:SS') TGL_DELIVERY, A.NO_SEAL  FROM CONTAINER_DELIVERY A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '".$row['NO_REQUEST']."'";
				$resultDTL = $db->query($queryDTL);
				if($resultDTL->RecordCount() >0){
					$resultDTL = $resultDTL->getAll();
					foreach ($resultDTL as $dtl){
						$returnXML .= '<detail>';
						$returnXML .= '<REQ_DTL_CONT>'.$dtl['NO_CONTAINER'].'</REQ_DTL_CONT>';
						$returnXML .= '<REQ_DTL_CONT_STATUS>'.$dtl['STATUS'].'</REQ_DTL_CONT_STATUS>';
						$returnXML .= '<REQ_DTL_COMMODITY>'.trim($dtl['KOMODITI']).'</REQ_DTL_COMMODITY>';
						$returnXML .= '<REQ_DTL_VIA>'.$dtl['VIA'].'</REQ_DTL_VIA>';
						$returnXML .= '<REQ_DTL_SIZE>'.$dtl['SIZE_'].'</REQ_DTL_SIZE>';
						$returnXML .= '<REQ_DTL_TYPE>'.$dtl['TYPE_'].'</REQ_DTL_TYPE>';
						$returnXML .= '<REQ_DTL_DEL_DATE>'.$dtl['TGL_DELIVERY'].'</REQ_DTL_DEL_DATE>';
						$returnXML .= '<REQ_DTL_CONT_HAZARD>'.$dtl['HZ'].'</REQ_DTL_CONT_HAZARD>';
						$returnXML .= '<REQ_DTL_NO_SEAL>'.$dtl['NO_SEAL'].'</REQ_DTL_NO_SEAL>';
						$returnXML .= '</detail>';
					}
				}
				$returnXML .= '</arrdetail>';
				$returnXML .= '</data>';
			}
			$returnXML .= '</loop>';
			$returnXML .= '</document>';

		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
		}
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
		$returnXML .= '<document>';
		$returnXML .= '<respon>0</respon>';
		$returnXML .= '<URresponse>Username atau password salah</URresponse>';
  		$returnXML .= '</document>';
	}
	return $returnXML;
}

function getBatal($user, $pass, $tgl)
{
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
		$query = "SELECT ID_REQ_SPPS NO_REQUEST, NO_CONTAINER, NO_BA, TO_CHAR(TANGGAL_PEMBUATAN,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST FROM REQ_BATAL_SPPS 	WHERE  TO_CHAR(TANGGAL_PEMBUATAN,'YYYYMMDDHH24MISS') > '".$tgl."' ORDER BY TO_CHAR(TANGGAL_PEMBUATAN,'YYYYMMDDHH24MISS') ASC";

		$result = $db->query($query);
		if($result->RecordCount() >0)
		{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$result = $result->getAll();
			$a = 0;
			foreach ($result as $row){$a++;
				$returnXML .= '<data>';
				$returnXML .= '<REQ_NO>'.$row['NO_REQUEST'].'</REQ_NO>';
				$returnXML .= '<NO_CONTAINER>'.$row['NO_CONTAINER'].'</NO_CONTAINER>';
				$returnXML .= '<NO_BA>'.$row['NO_BA'].'</NO_BA>';
				$returnXML .= '<TGL_REQUEST>'.$row['TGL_REQUEST'].'</TGL_REQUEST>';
				$returnXML .= '</data>';
			}
			$returnXML .= '</loop>';
			$returnXML .= '</document>';

		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
		}
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
		$returnXML .= '<document>';
		$returnXML .= '<respon>0</respon>';
		$returnXML .= '<URresponse>Username atau password salah</URresponse>';
  		$returnXML .= '</document>';
	}
	return $returnXML;
}

function getReceiving($user, $pass, $tgl)
{
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
		$query = "SELECT A.DI, A.NO_REQUEST, A.NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST,B.NO_NOTA,TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.EMKL, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.RECEIVING_DARI
				FROM REQUEST_RECEIVING A
				INNER JOIN NOTA_RECEIVING B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') > '".$tgl."' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

		$result = $db->query($query);
		if($result->RecordCount() >0)
		{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$result = $result->getAll();
			$a = 0;
			foreach ($result as $row){$a++;
				$returnXML .= '<data>';
				$returnXML .= '<header>';
				$returnXML .= '<REQ_NO>'.$row['NO_REQUEST'].'</REQ_NO>';
				$returnXML .= '<REQ_RECEIVING_DATE>'.$row['TGL_REQUEST'].'</REQ_RECEIVING_DATE>';
				$returnXML .= '<NO_NOTA>'.$row['NO_NOTA'].'</NO_NOTA>';
				$returnXML .= '<TGL_NOTA>'.$row['TGL_NOTA'].'</TGL_NOTA>';
				$returnXML .= '<NM_CONSIGNEE>'.$row['NM_CONSIGNEE'].'</NM_CONSIGNEE>';
				$returnXML .= '<ALAMAT>'.$row['ALAMAT'].'</ALAMAT>';
				$returnXML .= '<REQ_MARK>'.$row['KETERANGAN'].'</REQ_MARK>';
				$returnXML .= '<NPWP>'.$row['NPWP'].'</NPWP>';
				$returnXML .= '<RECEIVING_DARI>'.$row['RECEIVING_DARI'].'</RECEIVING_DARI>';
				$returnXML .= '<TANGGAL_LUNAS>'.$row['TANGGAL_LUNAS'].'</TANGGAL_LUNAS>';
				$returnXML .= '<DI>'.$row['DI'].'</DI>';
				$returnXML .= '</header>';
				$returnXML .= '<arrdetail>';
				$queryDTL = "SELECT A.NO_CONTAINER, A.STATUS, A.KOMODITI, A.VIA, A.HZ, B.SIZE_, B.TYPE_, A.KD_OWNER, A.NM_OWNER  FROM CONTAINER_RECEIVING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '".$row['NO_REQUEST']."'";
				$resultDTL = $db->query($queryDTL);
				if($resultDTL->RecordCount() >0){
					$resultDTL = $resultDTL->getAll();
					foreach ($resultDTL as $dtl){
						$returnXML .= '<detail>';
						$returnXML .= '<REQ_DTL_CONT>'.$dtl['NO_CONTAINER'].'</REQ_DTL_CONT>';
						$returnXML .= '<REQ_DTL_CONT_STATUS>'.$dtl['STATUS'].'</REQ_DTL_CONT_STATUS>';
						$returnXML .= '<REQ_DTL_COMMODITY>'.trim($dtl['KOMODITI']).'</REQ_DTL_COMMODITY>';
						$returnXML .= '<REQ_DTL_VIA>'.$dtl['VIA'].'</REQ_DTL_VIA>';
						$returnXML .= '<REQ_DTL_SIZE>'.$dtl['SIZE_'].'</REQ_DTL_SIZE>';
						$returnXML .= '<REQ_DTL_TYPE>'.$dtl['TYPE_'].'</REQ_DTL_TYPE>';
						$returnXML .= '<REQ_DTL_CONT_HAZARD>'.$dtl['HZ'].'</REQ_DTL_CONT_HAZARD>';
						$returnXML .= '<REQ_DTL_OWNER_CODE>'.$dtl['KD_OWNER'].'</REQ_DTL_OWNER_CODE>';
						$returnXML .= '<REQ_DTL_OWNER_NAME>'.$dtl['NM_OWNER'].'</REQ_DTL_OWNER_NAME>';
						$returnXML .= '</detail>';
					}
				}
				$returnXML .= '</arrdetail>';
				$returnXML .= '</data>';
			}
			$returnXML .= '</loop>';
			$returnXML .= '</document>';

		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
		}
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
		$returnXML .= '<document>';
		$returnXML .= '<respon>0</respon>';
		$returnXML .= '<URresponse>Username atau password salah</URresponse>';
  		$returnXML .= '</document>';
	}
	return $returnXML;
}

function getGateInFromTPK($user, $pass, $NoreqReciving)
{
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
		$arrData = explode("~", $NoreqReciving);
		$cekSql = "SELECT PERALIHAN FROM REQUEST_RECEIVING WHERE NO_REQUEST = '".$arrData[0]."'";
		$result = $db->query($cekSql);
		if($result->RecordCount() >0)
		{
			$result = $result->getAll();
			foreach ($result as $row){
				$tipePeralihan = $row['PERALIHAN'];
			}
		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
	  		return $returnXML;die();
		}

        $query = "SELECT B.SIZE_ CONT_SIZE, B.TYPE_ CONT_TYPE, A.NO_CONTAINER, A.NO_REQUEST, A.STATUS_CONT, 'PELINDO' TRUK, 'TPK' ORIGIN, A.KEGIATAN , '$tipePeralihan' PERALIHAN, TO_CHAR(A.TGL_UPDATE,'MM/DD/YYYY HH24:MI:SS') TGL_GATE
        		  FROM HISTORY_CONTAINER A INNER JOIN MASTER_CONTAINER B ON B.NO_CONTAINER =A.NO_CONTAINER WHERE A.NO_REQUEST = '".$arrData[0]."' AND A.NO_CONTAINER = '".$arrData[1]."' AND A.KEGIATAN = 'BORDER GATE IN' AND A.ID_USER = 'opus'";

        $result = $db->query($query);
		if($result->RecordCount() >0)
		{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$result = $result->getAll();

			foreach ($result as $row){
				$returnXML .= '<data>';
				$returnXML .= '<header>';
				$returnXML .= '<NO_CONT>'.$row['NO_CONTAINER'].'</NO_CONT>';
				$returnXML .= '<REQ_NO>'.$row['NO_REQUEST'].'</REQ_NO>';
				$returnXML .= '<TGL_GATE>'.$row['TGL_GATE'].'</TGL_GATE>';
				$returnXML .= '<STATUS_CONT>'.$row['STATUS_CONT'].'</STATUS_CONT>';
				$returnXML .= '<TRUK>'.$row['TRUK'].'</TRUK>';
				$returnXML .= '<ORIGIN>'.$row['ORIGIN'].'</ORIGIN>';
				$returnXML .= '<KEGIATAN>'.$row['KEGIATAN'].'</KEGIATAN>';
				$returnXML .= '<PERALIHAN>'.$row['PERALIHAN'].'</PERALIHAN>';
				$returnXML .= '<CONT_SIZE>'.$row['CONT_SIZE'].'</CONT_SIZE>';
				$returnXML .= '<CONT_TYPE>'.$row['CONT_TYPE'].'</CONT_TYPE>';
				$returnXML .= '</header>';
				$returnXML .= '</data>';
			}
			$returnXML .= '</loop>';
			$returnXML .= '</document>';
		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
		}
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
		$returnXML .= '<document>';
		$returnXML .= '<respon>0</respon>';
		$returnXML .= '<URresponse>Username atau password salah</URresponse>';
  		$returnXML .= '</document>';
	}
	return $returnXML;
}

function getGateOutToTPK($user, $pass, $NoreqDelivery)
{
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');

		$cekData = $db->query("SELECT DELIVERY_KE FROM REQUEST_DELIVERY WHERE NO_REQUEST = '".$NoreqDelivery."'");
		$rowdata = $cekData->fetchRow();

		if($rowdata['DELIVERY_KE'] != 'TPK'){
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
	  		return $returnXML;die();
		}

        $query = "SELECT NO_CONTAINER, NO_REQUEST, STATUS_CONT, 'PELINDO' TRUK, 'TPK' DELIVERY_TO, KEGIATAN ,  TO_CHAR(TGL_UPDATE,'MM/DD/YYYY HH24:MI:SS') TGL_GATE
				  FROM HISTORY_CONTAINER
				  WHERE NO_REQUEST = '".$NoreqDelivery."' AND KEGIATAN = 'BORDER GATE OUT' AND ID_USER = 'opus'";

        $result = $db->query($query);
		if($result->RecordCount() >0)
		{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$result = $result->getAll();

			foreach ($result as $row){
				$returnXML .= '<data>';
				$returnXML .= '<header>';
				$returnXML .= '<NO_CONT>'.$row['NO_CONTAINER'].'</NO_CONT>';
				$returnXML .= '<REQ_NO>'.$row['NO_REQUEST'].'</REQ_NO>';
				$returnXML .= '<TGL_GATE>'.$row['TGL_GATE'].'</TGL_GATE>';
				$returnXML .= '<STATUS_CONT>'.$row['STATUS_CONT'].'</STATUS_CONT>';
				$returnXML .= '<TRUK>'.$row['TRUK'].'</TRUK>';
				$returnXML .= '<DELIVERY_TO>'.$row['DELIVERY_TO'].'</DELIVERY_TO>';
				$returnXML .= '<KEGIATAN>'.$row['KEGIATAN'].'</KEGIATAN>';
				$returnXML .= '</header>';
				$returnXML .= '</data>';
			}
			$returnXML .= '</loop>';
			$returnXML .= '</document>';
		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
		}
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
		$returnXML .= '<document>';
		$returnXML .= '<respon>0</respon>';
		$returnXML .= '<URresponse>Username atau password salah</URresponse>';
  		$returnXML .= '</document>';
	}
	return $returnXML;
}

function getReceivingFromTPK($user, $pass, $NoreqReciving)
{
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
		$cekSql = "SELECT PERALIHAN FROM REQUEST_RECEIVING WHERE NO_REQUEST = '".$NoreqReciving."'";
		$result = $db->query($cekSql);
		if($result->RecordCount() >0)
		{
			$result = $result->getAll();
			foreach ($result as $row){
				$tipePeralihan = $row['PERALIHAN'];
			}
		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
	  		return $returnXML;die();
		}

		if($tipePeralihan == 'STRIPPING'){
			$query = "SELECT A.NO_REQUEST, A.NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST,'AUTO_RECEIVE' NO_NOTA,
						(SELECT I.EMKL FROM REQUEST_STRIPPING H LEFT JOIN NOTA_STRIPPING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STRIPPING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING))  EMKL,
						(SELECT I.ALAMAT FROM REQUEST_STRIPPING H LEFT JOIN NOTA_STRIPPING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STRIPPING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING)) ALAMAT,
						(SELECT I.NPWP FROM REQUEST_STRIPPING H LEFT JOIN NOTA_STRIPPING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STRIPPING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING)) NPWP,
						 A.RECEIVING_DARI, A.PERALIHAN
					FROM REQUEST_RECEIVING A
					WHERE A.NO_REQUEST = '".$NoreqReciving."'";
		}else{
			$query = "SELECT A.NO_REQUEST, A.NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST,'AUTO_RECEIVE' NO_NOTA,
						(SELECT I.EMKL FROM REQUEST_STUFFING H LEFT JOIN NOTA_STUFFING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STUFFING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING))  EMKL,
						(SELECT I.ALAMAT FROM REQUEST_STUFFING H LEFT JOIN NOTA_STUFFING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STUFFING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING)) ALAMAT,
						(SELECT I.NPWP FROM REQUEST_STUFFING H LEFT JOIN NOTA_STUFFING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STUFFING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING)) NPWP,
						 A.RECEIVING_DARI, A.PERALIHAN
					FROM REQUEST_RECEIVING A
					WHERE A.NO_REQUEST = '".$NoreqReciving."'";
		}

		$result = $db->query($query);
		if($result->RecordCount() >0)
		{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$result = $result->getAll();

			foreach ($result as $row){
				$returnXML .= '<data>';
				$returnXML .= '<header>';
				$returnXML .= '<REQ_NO>'.$row['NO_REQUEST'].'</REQ_NO>';
				$returnXML .= '<REQ_RECEIVING_DATE>'.$row['TGL_REQUEST'].'</REQ_RECEIVING_DATE>';
				$returnXML .= '<NO_NOTA>'.$row['NO_NOTA'].'</NO_NOTA>';
				$returnXML .= '<NM_CONSIGNEE>'.$row['NM_CONSIGNEE'].'</NM_CONSIGNEE>';
				$returnXML .= '<ALAMAT>'.$row['ALAMAT'].'</ALAMAT>';
				$returnXML .= '<REQ_MARK>'.$row['KETERANGAN'].'</REQ_MARK>';
				$returnXML .= '<NPWP>'.$row['NPWP'].'</NPWP>';
				$returnXML .= '<RECEIVING_DARI>'.$row['RECEIVING_DARI'].'</RECEIVING_DARI>';
				$returnXML .= '<PERALIHAN>'.$row['PERALIHAN'].'</PERALIHAN>';
				$returnXML .= '</header>';
				$returnXML .= '<arrdetail>';
				$queryDTL = "SELECT A.NO_CONTAINER, A.STATUS, A.KOMODITI, A.VIA, A.HZ, B.SIZE_, B.TYPE_  FROM CONTAINER_RECEIVING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '".$row['NO_REQUEST']."'";
				$resultDTL = $db->query($queryDTL);
				if($resultDTL->RecordCount() >0){
					$resultDTL = $resultDTL->getAll();
					foreach ($resultDTL as $dtl){
						$returnXML .= '<detail>';
						$returnXML .= '<REQ_DTL_CONT>'.$dtl['NO_CONTAINER'].'</REQ_DTL_CONT>';
						$returnXML .= '<REQ_DTL_CONT_STATUS>'.$dtl['STATUS'].'</REQ_DTL_CONT_STATUS>';
						$returnXML .= '<REQ_DTL_COMMODITY>'.trim($dtl['KOMODITI']).'</REQ_DTL_COMMODITY>';
						$returnXML .= '<REQ_DTL_VIA>'.$dtl['VIA'].'</REQ_DTL_VIA>';
						$returnXML .= '<REQ_DTL_SIZE>'.$dtl['SIZE_'].'</REQ_DTL_SIZE>';
						$returnXML .= '<REQ_DTL_TYPE>'.$dtl['TYPE_'].'</REQ_DTL_TYPE>';
						$returnXML .= '<REQ_DTL_CONT_HAZARD>'.$dtl['HZ'].'</REQ_DTL_CONT_HAZARD>';
						$returnXML .= '</detail>';
					}
				}
				$returnXML .= '</arrdetail>';
				$returnXML .= '</data>';
			}
			$returnXML .= '</loop>';
			$returnXML .= '</document>';

		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
		}
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
		$returnXML .= '<document>';
		$returnXML .= '<respon>0</respon>';
		$returnXML .= '<URresponse>Username atau password salah</URresponse>';
  		$returnXML .= '</document>';
	}
	return $returnXML;
}

function getStuffing($user, $pass, $xml)
{
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
		$query = "SELECT A.STUFFING_DARI, A.PERP_DARI, A.PERP_KE, A.NO_REQUEST, A.NO_BOOKING, A.O_IDVSB NO_UKK, B.EMKL NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST, B.NO_NOTA, TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.NO_REQUEST_RECEIVING
				FROM REQUEST_STUFFING A
				INNER JOIN NOTA_STUFFING B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') > '".$xml."' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

		$result = $db->query($query);
		// print_r($result->RecordCount());die();
		if($result->RecordCount() >0)
		{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$result = $result->getAll();

			foreach ($result as $row){
				$returnXML .= '<data>';
				$returnXML .= '<header>';
				$returnXML .= '<REQ_NO>'.$row['NO_REQUEST'].'</REQ_NO>';
				$returnXML .= '<REQ_STUFF_DATE>'.$row['TGL_REQUEST'].'</REQ_STUFF_DATE>';
				$returnXML .= '<NO_NOTA>'.$row['NO_NOTA'].'</NO_NOTA>';
				$returnXML .= '<TGL_NOTA>'.$row['TGL_NOTA'].'</TGL_NOTA>';
				$returnXML .= '<NM_CONSIGNEE>'.$row['NM_CONSIGNEE'].'</NM_CONSIGNEE>';
				$returnXML .= '<ALAMAT>'.$row['ALAMAT'].'</ALAMAT>';
				$returnXML .= '<REQ_MARK>'.$row['KETERANGAN'].'</REQ_MARK>';
				$returnXML .= '<NO_UKK>'.$row['NO_UKK'].'</NO_UKK>';
				$returnXML .= '<NO_BOOKING>'.$row['NO_BOOKING'].'</NO_BOOKING>';
				$returnXML .= '<NPWP>'.$row['NPWP'].'</NPWP>';
				$returnXML .= '<TANGGAL_LUNAS>'.$row['TANGGAL_LUNAS'].'</TANGGAL_LUNAS>';
				$returnXML .= '<NO_REQUEST_RECEIVING>'.$row['NO_REQUEST_RECEIVING'].'</NO_REQUEST_RECEIVING>';
				$returnXML .= '<STUFFING_DARI>'.$row['STUFFING_DARI'].'</STUFFING_DARI>';
				$returnXML .= '<PERP_DARI>'.$row['PERP_DARI'].'</PERP_DARI>';
				$returnXML .= '<PERP_KE>'.$row['PERP_KE'].'</PERP_KE>';
				$returnXML .= '</header>';
				$returnXML .= '<arrdetail>';
				$queryDTL = "SELECT A.NO_CONTAINER, A.COMMODITY, A.HZ, B.SIZE_, B.TYPE_, A.REMARK_SP2, A.ASAL_CONT, TO_CHAR(A.START_PERP_PNKN,'MM/DD/YYYY HH24:MI:SS') TGL_SELESAI, TO_CHAR(A.START_STACK,'MM/DD/YYYY HH24:MI:SS') TGL_MULAI  FROM CONTAINER_STUFFING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '".$row['NO_REQUEST']."'";
				$resultDTL = $db->query($queryDTL);
				if($resultDTL->RecordCount() >0){
					$resultDTL = $resultDTL->getAll();
					foreach ($resultDTL as $dtl){
						$returnXML .= '<detail>';
						$returnXML .= '<REQ_DTL_CONT>'.$dtl['NO_CONTAINER'].'</REQ_DTL_CONT>';
						$returnXML .= '<REQ_DTL_COMMODITY>'.trim($dtl['COMMODITY']).'</REQ_DTL_COMMODITY>';
						$returnXML .= '<REQ_DTL_SIZE>'.$dtl['SIZE_'].'</REQ_DTL_SIZE>';
						$returnXML .= '<REQ_DTL_TYPE>'.$dtl['TYPE_'].'</REQ_DTL_TYPE>';
						$returnXML .= '<REQ_DTL_CONT_HAZARD>'.$dtl['HZ'].'</REQ_DTL_CONT_HAZARD>';
						$returnXML .= '<REQ_DTL_REMARK_SP2>'.$dtl['REMARK_SP2'].'</REQ_DTL_REMARK_SP2>';
						$returnXML .= '<REQ_DTL_ORIGIN>'.$dtl['ASAL_CONT'].'</REQ_DTL_ORIGIN>';
						$returnXML .= '<TGL_MULAI>'.$dtl['TGL_MULAI'].'</TGL_MULAI>';
						$returnXML .= '<TGL_SELESAI>'.$dtl['TGL_SELESAI'].'</TGL_SELESAI>';
						$returnXML .= '</detail>';
					}
				}
				$returnXML .= '</arrdetail>';
				$returnXML .= '</data>';
			}
			$returnXML .= '</loop>';
			$returnXML .= '</document>';
		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
		}
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
		$returnXML .= '<document>';
		$returnXML .= '<respon>0</respon>';
		$returnXML .= '<URresponse>Username atau password salah</URresponse>';
  		$returnXML .= '</document>';
	}
	return $returnXML;
}

function getStripping($user, $pass, $xml)
{
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
		$query = "SELECT 'TPK' STRIP_DARI, A.PERP_DARI, A.PERP_KE, A.NO_REQUEST, B.EMKL NM_CONSIGNEE, A.KETERANGAN, A.NO_BL, A.NO_DO, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST, B.NO_NOTA, TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.NO_REQUEST_RECEIVING
				FROM REQUEST_STRIPPING A
				INNER JOIN NOTA_STRIPPING B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') > '".$xml."' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

		$result = $db->query($query);
		// print_r($result->RecordCount());die();
		if($result->RecordCount() >0)
		{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';
			$result = $result->getAll();
			$a = 0;
			foreach ($result as $row){$a++;
				$returnXML .= '<data>';
				$returnXML .= '<header>';
				$returnXML .= '<REQ_NO>'.$row['NO_REQUEST'].'</REQ_NO>';
				$returnXML .= '<REQ_STRIP_DATE>'.$row['TGL_REQUEST'].'</REQ_STRIP_DATE>';
				$returnXML .= '<NO_NOTA>'.$row['NO_NOTA'].'</NO_NOTA>';
				$returnXML .= '<TGL_NOTA>'.$row['TGL_NOTA'].'</TGL_NOTA>';
				$returnXML .= '<NM_CONSIGNEE>'.$row['NM_CONSIGNEE'].'</NM_CONSIGNEE>';
				$returnXML .= '<ALAMAT>'.$row['ALAMAT'].'</ALAMAT>';
				$returnXML .= '<REQ_MARK>'.$row['KETERANGAN'].'</REQ_MARK>';
				$returnXML .= '<NPWP>'.$row['NPWP'].'</NPWP>';
        		$returnXML .= '<DO>'.$row['NO_DO'].'</DO>';
        		$returnXML .= '<BL>'.$row['NO_BL'].'</BL>';
        		$returnXML .= '<NO_REQUEST_RECEIVING>'.$row['NO_REQUEST_RECEIVING'].'</NO_REQUEST_RECEIVING>';
				$returnXML .= '<TANGGAL_LUNAS>'.$row['TANGGAL_LUNAS'].'</TANGGAL_LUNAS>';
				$returnXML .= '<STRIP_DARI>'.$row['STRIP_DARI'].'</STRIP_DARI>';
				$returnXML .= '<PERP_DARI>'.$row['PERP_DARI'].'</PERP_DARI>';
				$returnXML .= '<PERP_KE>'.$row['PERP_KE'].'</PERP_KE>';
				$returnXML .= '</header>';
				$returnXML .= '<arrdetail>';
				$queryDTL = "SELECT A.NO_CONTAINER, A.COMMODITY, A.HZ, B.SIZE_, B.TYPE_, A.VIA, TO_CHAR(A.TGL_SELESAI,'MM/DD/YYYY HH24:MI:SS') TGL_SELESAI, TO_CHAR(A.TGL_APPROVE,'MM/DD/YYYY HH24:MI:SS') TGL_MULAI  FROM CONTAINER_STRIPPING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '".$row['NO_REQUEST']."'";
				$resultDTL = $db->query($queryDTL);
				if($resultDTL->RecordCount() >0){
					$resultDTL = $resultDTL->getAll();
					foreach ($resultDTL as $dtl){
						$returnXML .= '<detail>';
						$returnXML .= '<REQ_DTL_CONT>'.$dtl['NO_CONTAINER'].'</REQ_DTL_CONT>';
						$returnXML .= '<REQ_DTL_COMMODITY>'.trim($dtl['COMMODITY']).'</REQ_DTL_COMMODITY>';
						$returnXML .= '<REQ_DTL_SIZE>'.$dtl['SIZE_'].'</REQ_DTL_SIZE>';
						$returnXML .= '<REQ_DTL_TYPE>'.$dtl['TYPE_'].'</REQ_DTL_TYPE>';
						$returnXML .= '<REQ_DTL_CONT_HAZARD>'.$dtl['HZ'].'</REQ_DTL_CONT_HAZARD>';
						$returnXML .= '<REQ_DTL_ORIGIN>'.$dtl['VIA'].'</REQ_DTL_ORIGIN>';
						$returnXML .= '<TGL_MULAI>'.$dtl['TGL_MULAI'].'</TGL_MULAI>';
						$returnXML .= '<TGL_SELESAI>'.$dtl['TGL_SELESAI'].'</TGL_SELESAI>';
						$returnXML .= '</detail>';
					}
				}
				$returnXML .= '</arrdetail>';
				$returnXML .= '</data>';
			}
			$returnXML .= '</loop>';
			$returnXML .= '</document>';

		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>Tidak ada data</URresponse>';
	  		$returnXML .= '</document>';
		}
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
		$returnXML .= '<document>';
		$returnXML .= '<respon>0</respon>';
		$returnXML .= '<URresponse>Username atau password salah</URresponse>';
  		$returnXML .= '</document>';
	}
	return $returnXML;
}

function sendRelocation($user, $pass, $xml)
{
	$db = getDB('storage');
	$query = "SELECT * from REQUEST_RELOKASI WHERE NOTA = 'Y' ";
	$result = $db->query($query);
	$tot = $result->RecordCount()."-";

 	if($result->RecordCount() >0)
	{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>
					  <document>
							<response>';
		$result = $result->getAll();
		foreach ($result as $row){
			$returnXML .= '<NO_REQUEST>'.$row['NO_REQUEST'].'</NO_REQUEST>';
		}
		$returnXML .= '</response>
					  </document>';
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>
					  <document>
							<response>empty</response>
					  </document>';
	}

	/*$query = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE, STATUS_CONT FROM HISTORY_CONTAINER WHERE NO_CONTAINER = 'OOLU222222' AND NO_REQUEST = 'REC0718000007' AND KEGIATAN = 'REQUEST RECEIVING'";
				$result = $db->query($query);
                if($result->RecordCount() >0)
                {
                	$result = $result->getAll();
					foreach ($result as $row){
						$nobok = $row['NO_BOOKING'];
						return $nobok;die();
					}
                }*/

	return $returnXML;
}

function sendStripping($user, $pass, $xml)
{
	$db = getDB('storage');
	$query = "SELECT * from REQUEST_STRIPPING WHERE NOTA = 'Y' ";
	$result = $db->query($query);
	$tot = $result->RecordCount()."-";

	if($result->RecordCount() >0)
	{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>
					  <document>
							<response>';
		$result = $result->getAll();
		foreach ($result as $row){
			$returnXML .= '<NO_REQUEST>'.$row['NO_REQUEST'].'</NO_REQUEST>';
		}
		$returnXML .= '</response>
					  </document>';
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>
					  <document>
							<response>empty</response>
					  </document>';
	}

	return $returnXML;
}

function setRealStuffing($user, $pass, $xml)
{
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
        $error = 0;
        $arrxml = xml2ary($xml);
        $valResponse = $arrxml['document']['_c']['respon']['_v'];
        if($valResponse != 0){
        	$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';

			$loop =  $arrxml['document']['_c']['loop']['_c']['data'];
            $totalHDR = count($loop);
            $i =0;
            $nocont = "";
            while ($i < $totalHDR) {
                if($totalHDR == 1){
                    $data = $loop['_c'];
                }else{
                    $data = $loop[$i]['_c'];
                }

                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
                $NO_REQUEST = $data['NO_REQUEST']['_v'];
                $COMMODITY = $data['COMMODITY']['_v'];
                $HZ = $data['HZ']['_v'];
                $CONT_SIZE = $data['CONT_SIZE']['_v'];
                $CONT_TYPE = $data['CONT_TYPE']['_v'];
                $TGL_REQUEST = $data['TGL_REQUEST']['_v'];
                $NO_BOOKING = $data['NO_BOOKING']['_v'];
                $NO_UKK = $data['NO_UKK']['_v'];
                $ID_USER = $data['ID_USER']['_v'];
                $YBC_ID = $data['YBC_ID']['_v'];
                $TGL_REALISASI = $data['TGL_REALISASI']['_v'];
                $ALAT = $data['ALAT']['_v'];
                $REMARK_SP2 = $data['REMARK_SP2']['_v'];
                $STATUS= "FCL";

                $returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$NO_CONTAINER.'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$NO_REQUEST.'</NO_REQUEST>';


        		$cekexist = $db->query("select aktif, tgl_realisasi from container_stuffing where no_request = '$NO_CONTAINER' and no_container = '$NO_REQUEST'");
				$rowexist = $cekexist->fetchRow();

				if ($rowexist["AKTIF"] == 'T' && $rowexist["TGL_REALISASI"] != NULL) {
					$returnXML .= '<KET>EXECUTED</KET>';
					$returnXML .= '</data>';
					$i++;
					continue;
				}

				if($NO_UKK != ""){
				   $qclosing = "select count(*) CLOSING_TIME
			                from m_vsb_voyage@dbint_link where id_vsb_voyage = '$NO_UKK'
			                and TRUNC(sysdate) >= to_date(clossing_time,'rrrrmmddhh24miss')";

			        $rclosing = $db->query($qclosing)->fetchRow();
				    $rowclosing = $rclosing['CLOSING_TIME'];
				}else{
				    $rowclosing = 0;
				}

				if($rowclosing == 1){
					$returnXML .= '<KET>CLOSING_TIME</KET>';
					$returnXML .= '</data>';
					$i++;
					continue;
				}else{
					//Cek posisi container GATI, GATO, IN_YARD
					$query_posisi	="SELECT LOCATION FROM MASTER_CONTAINER
										WHERE NO_CONTAINER = '$NO_CONTAINER'";
					$result_posisi	= $db->query($query_posisi);
					$row_posisi		= $result_posisi->fetchRow();
					$posisi			= $row_posisi["LOCATION"];

					if($posisi == 'IN_YARD')
					{
						//cek apakah container tersebut status stuffingnya aktif
						$query_cek2		= "SELECT COUNT(1) AS JUM FROM CONTAINER_STUFFING WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' AND AKTIF = 'Y'";
						$result_cek2	= $db->query($query_cek2);
						$row_cek2		= $result_cek2->fetchRow();
						if($row_cek2["JUM"] > 0){
							$q_perp = "SELECT STATUS_REQ, STUFFING_DARI FROM REQUEST_STUFFING WHERE NO_REQUEST = '$NO_REQUEST'";
							$r_prep = $db->query($q_perp);
							$rpre = $r_prep->fetchRow();
							if($rpre["STATUS_REQ"] == 'PERP'){
								$query_cek1	= "SELECT
				                                        CASE
				                                        WHEN TO_DATE(SYSDATE,'DD-MM-RRRR') <= END_STACK_PNKN THEN 'OK'
				                                        ELSE 'NO'
				                                        END AS STATUS
				                                FROM CONTAINER_STUFFING
				                                WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
							}else{
								//cek apakah container tersebut masa stuffingnya masih berlaku
								if($REMARK_SP2  == 'Y'){
									$query_cek1	= "SELECT
				                                        CASE
				                                        WHEN TO_DATE(SYSDATE,'DD-MM-RRRR') <= END_STACK_PNKN THEN 'OK'
				                                        ELSE 'NO'
				                                        END AS STATUS
				                                FROM CONTAINER_STUFFING
				                                WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
								}
								else {
									$query_cek1	= "SELECT
			                                        CASE
			                                        WHEN TO_DATE(SYSDATE,'DD-MM-RRRR') <= START_PERP_PNKN THEN 'OK'
			                                        ELSE 'NO'
			                                        END AS STATUS
			                                FROM CONTAINER_STUFFING
			                                WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
								}
							}

							$result_cek1	= $db->query($query_cek1);
							$row_cek1		= $result_cek1->fetchRow();
							$row_perp 		= $rpre['STATUS_REQ'];
							$stuf_dari 		= $rpre['STUFFING_DARI'];

							if ($stuf_dari != 'AUTO') {
								$cek_nota 	= $db->query("SELECT LUNAS FROM NOTA_STUFFING WHERE NO_REQUEST = '$NO_REQUEST'");
								$rceknota	= $cek_nota->fetchRow();
								if ($rceknota["LUNAS"] != 'YES') {
									$returnXML .= '<KET>NOTA_BLM_LUNAS</KET>';
									$returnXML .= '</data>';
									$i++;
									continue;
								}
							}else{
								$cek_nota_batal = $db->query("SELECT LUNAS, BIAYA FROM REQUEST_BATAL_MUAT, NOTA_BATAL_MUAT
													WHERE REQUEST_BATAL_MUAT.NO_REQUEST = NOTA_BATAL_MUAT.NO_REQUEST(+) AND
													NO_REQ_BARU = '$NO_REQUEST'");
								$rcekbatal = $cek_nota_batal->fetchRow();
								if ($rcekbatal["LUNAS"] != 'YES' && $rcekbatal["BIAYA"] == 'Y') {
									$returnXML .= '<KET>NOTA_BLM_LUNAS</KET>';
									$returnXML .= '</data>';
									$i++;
									continue;
								}
							}

							if($row_cek1["STATUS"] == "OK"){
								//insert ke container delivery
								// mengetahui tanggal start_stack
								$query_cek1		= "SELECT tes.NO_REQUEST,
															CASE SUBSTR(KEGIATAN,9)
																WHEN 'RECEIVING' THEN (SELECT CONCAT('RECEIVING_',a.RECEIVING_DARI) FROM request_receiving a WHERE a.NO_REQUEST = tes.NO_REQUEST)
																ELSE SUBSTR(KEGIATAN,9)
															END KEGIATAN FROM (SELECT TGL_UPDATE, NO_REQUEST,KEGIATAN FROM history_container WHERE no_container = '$NO_CONTAINER' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI')) tes
															WHERE tes.TGL_UPDATE=(SELECT MAX(TGL_UPDATE) FROM history_container WHERE no_container = '$NO_CONTAINER' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI'))";
								$result_cek1		= $db->query($query_cek1);
								$row_cek1		= $result_cek1->fetchRow();
								$no_request		= $row_cek1["NO_REQUEST"];
								$kegiatan		= $row_cek1["KEGIATAN"];

								if($kegiatan == 'RECEIVING_LUAR') {
										$query_cek1		= "SELECT SUBSTR(TO_CHAR(b.TGL_IN, 'MM/DD/YYYY'),1,10) START_STACK FROM GATE_IN b WHERE b.NO_CONTAINER = '$NO_CONTAINER' AND b.NO_REQUEST = '$NO_REQUEST'";
										$result_cek1	= $db->query($query_cek1);
										$row_cek1		= $result_cek1->fetchRow();
										$start_stack	= $row_cek1["START_STACK"];
										$asal_cont 		= 'LUAR';
								} else if ($kegiatan == 'RECEIVING_TPK') {
										$query_cek1		= "SELECT TGL_BONGKAR START_STACK FROM container_receiving WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
										$result_cek1	= $db->query($query_cek1);
										$row_cek1		= $result_cek1->fetchRow();
										$start_stack	= $row_cek1["START_STACK"];
										$asal_cont 		= 'TPK';
								} else if ($kegiatan == 'STUFFING') {
										$query_cek1		= "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'MM/DD/YYYY'),1,10) START_STACK FROM container_stuffing WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
										$result_cek1	= $db->query($query_cek1);
										$row_cek1		= $result_cek1->fetchRow();
										$start_stack	= $row_cek1["START_STACK"];
										$asal_cont 		= 'DEPO';
								} else if ($kegiatan == 'STRIPPING') {
										$query_cek1		= "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'MM/DD/YYYY'),1,10) START_STACK FROM container_stripping WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
										$result_cek1	= $db->query($query_cek1);
										$row_cek1		= $result_cek1->fetchRow();
										$start_stack	= $row_cek1["START_STACK"];
										$asal_cont 		= 'DEPO';
								}

								$q_getcounter4 = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' ORDER BY COUNTER DESC";
								$r_getcounter4 = $db->query($q_getcounter4);
								$rw_getcounter4 = $r_getcounter4->fetchRow();
								$cur_booking4  = $rw_getcounter4["NO_BOOKING"];
								$cur_counter4  = $rw_getcounter4["COUNTER"];

								$history = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, STATUS_CONT, NO_BOOKING, COUNTER)
											VALUES ('$NO_CONTAINER','$NO_REQUEST','REALISASI STUFFING', TO_DATE('".$TGL_REALISASI."','MM/DD/YYYY HH24:MI:SS'),'$ID_USER','$YBC_ID','$STATUS', '$NO_BOOKING', $cur_counter4)";

								$query_update = "UPDATE CONTAINER_STUFFING SET AKTIF = 'T', TGL_REALISASI = TO_DATE('".$TGL_REALISASI."','MM/DD/YYYY HH24:MI:SS'), ID_USER_REALISASI = '$ID_USER', PEMAKAIAN_ALAT = '$ALAT' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
								$query_update_plan	= "UPDATE PLAN_CONTAINER_STUFFING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = REPLACE('$NO_REQUEST','S','P')";
								$db->query($query_update_plan);

								$req_receiving ="SELECT NO_REQUEST_RECEIVING FROM REQUEST_STUFFING WHERE NO_REQUEST = '$NO_REQUEST'";
								$req_rec = $db->query($req_receiving);
								$no_req_r = $req_rec->fetchRow();
								$no_rr = $no_req_r["NO_REQUEST_RECEIVING"];
								$aktif_rec = "SELECT AKTIF FROM CONTAINER_RECEIVING WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$no_rr'";
								$raktif_rec = $db->query($aktif_rec);
								$aktifa = $raktif_rec->fetchRow();
								$recaktif = $aktifa["AKTIF"];
								if($recaktif == "Y"){
									//$db->query("UPDATE CONTAINER_RECEIVING WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$no_rr'"); ???????
								}

								//$db->startTransaction();
								if($db->query($history))
								{
									if($db->query($query_update))
									{

										if($row_perp != 'PERP' && $stuf_dari != 'AUTO' && $REMARK_SP2 != 'Y'){

											if($db->query($query_update_plan))
											{
												$returnXML .= '<KET>OK</KET>';
												$returnXML .= '</data>';
												$i++;
												continue;
											}
											else
											{
												$returnXML .= '<KET>gagal update plan stuff</KET>';
												$returnXML .= '</data>';
												$i++;
												continue;
											}
										}
										else {
											$returnXML .= '<KET>OK</KET>';
											$returnXML .= '</data>';
											$i++;
											continue;
										}
									}
									else
									{
										$returnXML .= '<KET>gagal update cont stuff</KET>';
										$returnXML .= '</data>';
										$i++;
										continue;
									}
								}
								else
								{
									$returnXML .= '<KET>gagal insert History</KET>';
									$returnXML .= '</data>';
									$i++;
									continue;
								}
								//$db->endTransaction();

							}else{
								$returnXML .= '<KET>OVER</KET>';
								$returnXML .= '</data>';
								$i++;
								continue;
							}
						}else{
							$returnXML .= '<KET>NOT_AKTIF</KET>';
							$returnXML .= '</data>';
							$i++;
							continue;
						}
					}else{
						$returnXML .= '<KET>NOT_IN_YARD</KET>';
						$returnXML .= '</data>';
						$i++;
						continue;
					}
				}

				$returnXML .= '</data>';
				$i++;
            }
            $returnXML .= '</loop>';
			$returnXML .= '</document>';
        }
    }else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>
						  <document>
								<respon>0</respon>
								<URresponse>password salah</URresponse>
						  </document>';
	}
	return $returnXML;
}

function setRealStripping($user, $pass, $xml)
{
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
        $error = 0;
        $arrxml = xml2ary($xml);
        $valResponse = $arrxml['document']['_c']['respon']['_v'];
        if($valResponse != 0){
        	$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';

			$loop =  $arrxml['document']['_c']['loop']['_c']['data'];
            $totalHDR = count($loop);
            $i =0;
            $nocont = "";
            while ($i < $totalHDR) {
                if($totalHDR == 1){
                    $data = $loop['_c'];
                }else{
                    $data = $loop[$i]['_c'];
                }

                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
                $NO_REQUEST = $data['NO_REQUEST']['_v'];
                $TGL_REQUEST = $data['TGL_REQUEST']['_v'];
                $ID_USER = $data['ID_USER']['_v'];
                $YBC_ID = $data['YBC_ID']['_v'];
                $TGL_REALISASI = $data['TGL_REALISASI']['_v'];
                $ALAT = $data['ALAT']['_v'];
                $MARK = $data['MARK']['_v'];

                $returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$NO_CONTAINER.'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$NO_REQUEST.'</NO_REQUEST>';

                $nota_lunas = $db->query("select case when lunas = 'YES' THEN 'OK'
				        else 'NO'
				         end  STATUS_NOTA
				 from nota_stripping where no_request = '$NO_REQUEST'
				  AND STATUS <> 'BATAL'");
				$rnota_lunas 	= $nota_lunas->fetchRow();

				if ($rnota_lunas["STATUS_NOTA"] != 'OK') {
					$returnXML .= '<KET>NOTA_FAIL</KET>';
					$returnXML .= '</data>';
					$i++;
					continue;
				}

				//cek apakah container telah placement
				$qcekpl = $db->query("SELECT NO_BOOKING, COUNTER, LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER'");
				$rcekpl = $qcekpl->fetchRow();
				$no_booking = $rcekpl['NO_BOOKING'];
				$counter = $rcekpl['COUNTER'];

				$qbook = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' ORDER BY TGL_UPDATE DESC";
				$rbook = $db->query($qbook);
				$rwbook = $rbook->fetchRow();
				$cur_booking1 = $rwbook["NO_BOOKING"];
				$cur_counter1 = $rwbook["COUNTER"];
				$tgl_update = $rwbook["TGL_UPDATE"];

				if($rcekpl['LOCATION'] != 'IN_YARD'){
					$returnXML .= '<KET>NOT_PLACEMENT</KET>';
					$returnXML .= '</data>';
					$i++;
					continue;
				}else {
				//cek apakah container tersebut status strippingnya aktif
					$query_cek2		= "SELECT COUNT(1) AS JUM FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' AND AKTIF = 'Y'";
					$result_cek2	= $db->query($query_cek2);
					$row_cek2		= $result_cek2->fetchRow();
					if($row_cek2["JUM"] > 0)
					{
						//cek apakah container perpanjangan
						$q_cek_perp = "SELECT STATUS_REQ FROM REQUEST_STRIPPING WHERE NO_REQUEST = '$NO_REQUEST'";
						$rc_perp = $db->query($q_cek_perp);
						$rpep = $rc_perp->fetchRow();
						if($rpep["STATUS_REQ"] == "PERP"){
							$query_cek1 = "SELECT END_STACK_PNKN, sysdate,
				                                    CASE
				                                    WHEN TO_DATE(SYSDATE,'dd/mm/rrrr') <= END_STACK_PNKN THEN 'OK'
				                                    ELSE 'NO'
				                                    END AS STATUS,
				                                    CASE
				                                    WHEN TO_DATE(SYSDATE,'dd/mm/rrrr') = END_STACK_PNKN-1 THEN 'OK'
				                                    ELSE 'NO'
				                                    END AS WARNING
				                            FROM CONTAINER_STRIPPING
				                            WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
						} else {
						//cek apakah container tersebut masa strippingnya masih berlaku
						$query_cek1	= "SELECT   TGL_BONGKAR+4, sysdate,
				                                   CASE WHEN CONTAINER_STRIPPING.TGL_SELESAI IS NULL
				                                        THEN    CASE
				                                                    WHEN TO_DATE(SYSDATE,'dd/mm/rrrr') <= TGL_BONGKAR+4 THEN 'OK'
				                                                    ELSE 'NO'
				                                                    END
														ELSE       CASE
																	WHEN TO_DATE(SYSDATE,'dd/mm/rrrr') <= TGL_SELESAI THEN 'OK'
																	ELSE 'NO'
																	END
				                                    END  STATUS
				                            FROM CONTAINER_STRIPPING
				                            WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
						}
						$result_cek1	= $db->query($query_cek1);
						$row_cek1		= $result_cek1->fetchRow();

						if($row_cek1["STATUS"] == "OK")
						{
							//update status aktif
							$query_update		= "UPDATE CONTAINER_STRIPPING SET AKTIF = 'T', TGL_REALISASI = TO_DATE('".$TGL_REALISASI."','MM/DD/YYYY HH24:MI:SS'), ID_USER_REALISASI = '$ID_USER', PEMAKAIAN_ALAT = '$ALAT' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
							$query_update_plan	= "UPDATE PLAN_CONTAINER_STRIPPING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = REPLACE('$NO_REQUEST','S','P')";
							if($db->query($query_update))
							{
								$db->query($query_update_plan);
								//update status aktif kartu yang masih Y
								$query_update2	= "UPDATE KARTU_STRIPPING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
								$db->query($query_update2);

								$hist = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT, WHY)
										 VALUES ('$NO_CONTAINER','$NO_REQUEST','REALISASI STRIPPING',TO_DATE('".$TGL_REALISASI."','MM/DD/YYYY HH24:MI:SS'),'$ID_USER','$YBC_ID', '$cur_booking1', '$cur_counter1','MTY','$MARK')";
								$db->query($hist);

								$returnXML .= '<KET>OK</KET>';
								$returnXML .= '</data>';
								$i++;

							}
						}
						else
						{
							$returnXML .= '<KET>OVER</KET>';
							$returnXML .= '</data>';
							$i++;
							continue;
						}
					}
					else
					{
						$returnXML .= '<KET>NOT_AKTIF</KET>';
						$returnXML .= '</data>';
						$i++;
						continue;
					}
				}
                $returnXML .= '</data>';
				$i++;
            }
            $returnXML .= '</loop>';
			$returnXML .= '</document>';
        }
    }else{
    	$returnXML = '<?xml version="1.0" encoding="UTF-8"?>
						  <document>
								<respon>0</respon>
								<URresponse>password salah</URresponse>
						  </document>';
    }
    return $returnXML;
}

function setGateOut($user, $pass, $xml){
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
        $error = 0;
        $arrxml = xml2ary($xml);
        $valResponse = $arrxml['document']['_c']['respon']['_v'];
        if($valResponse != 0){
        	$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';

			$loop =  $arrxml['document']['_c']['loop']['_c']['data'];
            $totalHDR = count($loop);
            $i =0;
            $nocont = "";
            while ($i < $totalHDR) {
                if($totalHDR == 1){
                    $data = $loop['_c'];
                }else{
                    $data = $loop[$i]['_c'];
                }

                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
                $NO_REQUEST = $data['NO_REQUEST']['_v'];
                $NOPOL = $data['NOPOL']['_v'];
                $ID_USER = $data['ID_USER']['_v'];
                $TGL_OUT = $data['TGL_OUT']['_v'];
                $TGL_REQ_DELIVERY = $data['TGL_REQ_DELIVERY']['_v'];
                $STATUS = $data['NO_CONTAINER']['_v'];
                $GATE_DESTINATION = $data['GATE_DESTINATION']['_v'];
                $NO_SEAL = $data['NO_SEAL']['_v'];
                $MARK = $data['MARK']['_v'];

                $returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$NO_CONTAINER.'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$NO_REQUEST.'</NO_REQUEST>';

        		$responStatus = '<STATUS>1</STATUS>';
				$responStatus .= '<UR_STATUS>OK</UR_STATUS>';

        		$selisih    = "SELECT TRUNC(TO_DATE('$TGL_REQ_DELIVERY','MM/DD/YYYY HH24:MI:SS') - SYSDATE) SELISIH FROM dual";
				$result_cek	= $db->query($selisih);
				$row_cek	= $result_cek->fetchRow();
				$selisih_tgl	= $row_cek["SELISIH"];

				$qcek_gati = "SELECT COUNT(NO_CONTAINER) AS JUM
							  FROM GATE_OUT
							  WHERE NO_CONTAINER = '$NO_CONTAINER'
							  AND NO_REQUEST = '$NO_REQUEST'";
				$rcek_gati = $db->query($qcek_gati);
				$rwc_gati = $rcek_gati->fetchRow();
				$jum_gati = $rwc_gati["JUM"];
				if($jum_gati > 0){
					//echo "EXIST_GATO";
					$responStatus = '<STATUS>0</STATUS>';
					$responStatus .= '<UR_STATUS>EXIST_GATO</UR_STATUS>';
					$returnXML .= $responStatus;
					$returnXML .= '</data>';
    				$i++;
					continue;
				}

				//cek request relokasi internal
				$q_cek_relokasi = "SELECT REQUEST_RELOKASI.NO_REQUEST NOREQ, REQUEST_RELOKASI.* FROM REQUEST_RELOKASI, CONTAINER_RELOKASI WHERE REQUEST_RELOKASI.NO_REQUEST = CONTAINER_RELOKASI.NO_REQUEST
									AND NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST_DELIVERY = '$NO_REQUEST'";
				$res_cek = $db->query($q_cek_relokasi);
				$row_cek = $res_cek->fetchRow();
				$no_req_relokasi = $row_cek["NOREQ"];

				if($row_cek["TIPE_RELOKASI"] == 'INTERNAL'){
					$q_insert_lolo = "INSERT INTO HANDLING_PIUTANG(NO_CONTAINER, KEGIATAN, STATUS_CONT, TANGGAL, KETERANGAN, NO_REQUEST)
				 					  VALUES('$NO_CONTAINER','DELIVERY','$STATUS',SYSDATE,'LIFT ON','$NO_REQUEST')";
					$q_insert_lolo_ = "INSERT INTO HANDLING_PIUTANG(NO_CONTAINER, KEGIATAN, STATUS_CONT, TANGGAL, KETERANGAN, NO_REQUEST)
								 	  VALUES('$NO_CONTAINER','DELIVERY','$STATUS',SYSDATE,'LIFT OFF','$NO_REQUEST')";
					$q_insert_haulage = "INSERT INTO HANDLING_PIUTANG(NO_CONTAINER, KEGIATAN, STATUS_CONT, TANGGAL, KETERANGAN, NO_REQUEST)
								 		 VALUES('$NO_CONTAINER','DELIVERY','$STATUS',SYSDATE,'HAULAGE','$NO_REQUEST')";
					$db->query($q_insert_lolo);
					$db->query($q_insert_lolo_);
					$db->query($q_insert_haulage);

					$query_insert	= "INSERT INTO GATE_OUT( NO_REQUEST, NO_CONTAINER, ID_USER, TGL_IN, NOPOL, STATUS, NO_SEAL, KETERANGAN) VALUES('$NO_REQUEST', '$NO_CONTAINER', '$ID_USER', TO_DATE('".$TGL_OUT."','MM/DD/YYYY HH24:MI:SS'), '$NOPOL', '$STATUS','$NO_SEAL','$MARK')";
				   // echo $query_insert;
					//$id_yard	= $_SESSION["IDYARD_STORAGE"];

					$q_getcounter1 = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' ORDER BY COUNTER DESC";
					$r_getcounter1 = $db->query($q_getcounter1);
					$rw_getcounter1 = $r_getcounter1->fetchRow();
					$cur_booking1  = $rw_getcounter1["NO_BOOKING"];
					$cur_counter1  = $rw_getcounter1["COUNTER"];

					$history = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER,  STATUS_CONT, NO_BOOKING, COUNTER)
								VALUES ('$NO_CONTAINER','$NO_REQUEST','GATE OUT',TO_DATE('".$TGL_OUT."','MM/DD/YYYY HH24:MI:SS'),'$ID_USER', '$STATUS','$cur_booking1','$cur_counter1')";

					$db->query($history);
					$db->query("UPDATE MASTER_CONTAINER SET LOCATION = 'GATO' WHERE NO_CONTAINER = '$NO_CONTAINER'");
					$db->query("UPDATE CONTAINER_DELIVERY SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'");
					$db->query("UPDATE CONTAINER_RELOKASI SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$no_req_relokasi'");

					$db->query("DELETE FROM PLACEMENT WHERE NO_CONTAINER = '$NO_CONTAINER'");
					$db->query($query_insert);
				}else{
					if ($selisih_tgl < 0) {
						//echo "EXPIRED";
						$responStatus = '<STATUS>0</STATUS>';
						$responStatus .= '<UR_STATUS>EXPIRED</UR_STATUS>';
						$returnXML .= $responStatus;
						$returnXML .= '</data>';
        				$i++;
						continue;
					} else {
						$query_insert	= "INSERT INTO GATE_OUT( NO_REQUEST, NO_CONTAINER, ID_USER, TGL_IN, NOPOL, STATUS, NO_SEAL, KETERANGAN) VALUES('$NO_REQUEST', '$NO_CONTAINER', '$ID_USER', TO_DATE('".$TGL_OUT."','MM/DD/YYYY HH24:MI:SS'), '$NOPOL', '$STATUS','$NO_SEAL','$MARK')";
						$db->query($query_insert);

						//$id_yard	= $_SESSION["IDYARD_STORAGE"];
						$qbook = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE, STATUS_CONT FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
								$rbook = $db->query($qbook);
								$rwbook = $rbook->fetchRow();
								$cur_booking1 = $rwbook["NO_BOOKING"];
								$cur_counter1 = $rwbook["COUNTER"];
								$tgl_update = $rwbook["TGL_UPDATE"];
								$status_ = $rwbook["STATUS_CONT"];

						$history = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, STATUS_CONT, NO_BOOKING, COUNTER)
									VALUES ('$NO_CONTAINER','$NO_REQUEST','GATE OUT',TO_DATE ('$tgl_update', 'MM/DD/YYYY HH:MI:SS AM'),'$ID_USER', '$status_','$cur_booking1','$cur_counter1')";
						$db->query($history);

						$db->query("UPDATE MASTER_CONTAINER SET LOCATION = 'GATO' WHERE NO_CONTAINER = '$NO_CONTAINER'");
						$db->query("UPDATE CONTAINER_DELIVERY SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'");
						$db->query("UPDATE CONTAINER_RELOKASI SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$no_req_relokasi'");

						$db->query("DELETE FROM PLACEMENT WHERE NO_CONTAINER = '$NO_CONTAINER'");
						$returnXML .= $responStatus;
					}
				}
			$returnXML .= '</data>';
        	$i++;
        	}
        	$returnXML .= '</loop>';
			$returnXML .= '</document>';
		}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>tidak ada data</URresponse>';
			$returnXML .= '</document>';
		}
	}else{
			$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>0</respon>';
			$returnXML .= '<URresponse>password salah</URresponse>';
			$returnXML .= '</document>';
		}
	return $returnXML;
}

function setPlacement($user, $pass, $xml){
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
        $error = 0;
        $arrxml = xml2ary($xml);
        $valResponse = $arrxml['document']['_c']['respon']['_v'];
        if($valResponse != 0){
        	$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';

			$loop =  $arrxml['document']['_c']['loop']['_c']['data'];
            $totalHDR = count($loop);
            $i =0;
            $nocont = "";
            while ($i < $totalHDR) {
                if($totalHDR == 1){
                    $data = $loop['_c'];
                }else{
                    $data = $loop[$i]['_c'];
                }

                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
                $NO_REQUEST = $data['NO_REQUEST']['_v'];
                $BLOCK = $data['BLOCK']['_v'];
                $SLOT = $data['SLOT']['_v'];
                $ROW = $data['ROW']['_v'];
                $TIER = $data['TIER']['_v'];
                $ID_YARD = $data['ID_YARD']['_v'];
                $ID_USER = $data['ID_USER']['_v'];
                $CONT_STATUS = $data['CONT_STATUS']['_v'];
                $TGL_PLACEMENT = $data['TGL_PLACEMENT']['_v'];

                $returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$NO_CONTAINER.'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$NO_REQUEST.'</NO_REQUEST>';

        		//$q_optrgate = "declare begin PROC_UPD_GATEOPTR('$NO_CONTAINER','$NO_REQUEST'); end;";
        		//$db->query($q_optrgate);

        		$query_cek_cont = "SELECT * FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER'";
				$result_cek_c	= $db->query($query_cek_cont);
				$row_cek_c		= $result_cek_c->fetchRow();
				$count_c		= count($row_cek_c);
				$size			= $row_cek_c['SIZE_'];

				$query_get_noreq	= "SELECT CONTAINER_RECEIVING.NO_REQUEST, MASTER_CONTAINER.LOCATION
										 FROM  CONTAINER_RECEIVING
										 INNER JOIN MASTER_CONTAINER
												ON CONTAINER_RECEIVING.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
										  WHERE CONTAINER_RECEIVING.NO_CONTAINER = '$NO_CONTAINER'
											AND CONTAINER_RECEIVING.AKTIF = 'Y'
											AND MASTER_CONTAINER.LOCATION = 'GATI'";
				$result_noreq		= $db->query($query_get_noreq);
				$row_noreq			= $result_noreq->fetchRow();
				$no_req_rec			= $row_noreq["NO_REQUEST"];
				$location			= $row_noreq["LOCATION"];

				$query_get_rec	= "SELECT * FROM CONTAINER_RECEIVING WHERE NO_CONTAINER = '$NO_CONTAINER' AND AKTIF = 'Y'";
				$result_req		= $db->query($query_get_rec);
				$row_req		= $result_req->fetchRow();
				$req_rec		= $row_req["NO_REQUEST"];
				$status			= $row_req["STATUS"];

				if($count_c > 0){
					if($location == "GATI"){
						$query_insert_placement = "INSERT INTO PLACEMENT(NO_CONTAINER, ID_BLOCKING_AREA, SLOT_, ROW_, TIER_, TGL_UPDATE, USER_NAME, TGL_PLACEMENT, NO_REQUEST_RECEIVING, STATUS) VALUES('$NO_CONTAINER', '$BLOCK', '$SLOT', '$ROW', '$TIER', TO_DATE('".$TGL_PLACEMENT."','MM/DD/YYYY HH24:MI:SS'), '$ID_USER', TO_DATE('".$TGL_PLACEMENT."','MM/DD/YYYY HH24:MI:SS'), '$NO_REQUEST', '$CONT_STATUS')";

						if($db->query($query_insert_placement)){
							$query_update_rec	= "UPDATE CONTAINER_RECEIVING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
							$db->query($query_update_rec);

							$no_req	= $row_req["NO_REQUEST"];
							$query_insert_history = "INSERT INTO HISTORY_PLACEMENT(NO_CONTAINER, NO_REQUEST, ID_BLOCKING_AREA, SLOT_, ROW_, TIER_, TGL_UPDATE, NIPP_USER, BAYAR_LOLO,KETERANGAN) VALUES('$NO_CONTAINER', '$NO_REQUEST', '$BLOCK', '$SLOT', '$ROW', '$TIER', TO_DATE('".$TGL_PLACEMENT."','MM/DD/YYYY HH24:MI:SS'), '$ID_USER', 'N', 'PLACEMENT' )";

							if($db->query($query_insert_history))
							{
								$query_update_rec	= "UPDATE CONTAINER_RECEIVING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
								$db->query($query_update_rec);
								$db->query("UPDATE MASTER_CONTAINER SET LOCATION = 'IN_YARD' WHERE NO_CONTAINER = '$NO_CONTAINER'");
								$returnXML .= '<KET>OK</KET>';
							}
						}else{
							$returnXML .= '<KET>DB ERROR</KET>';
						}
					}else{
						$returnXML .= '<KET>CONTAINER BELUM GATE IN</KET>';
					}
				}else{
					$returnXML .= '<KET>TIDAK ADA CONTAINER</KET>';
				}
				$returnXML .= '</data>';
				 $i++;
            }

            $returnXML .= '</loop>';
			$returnXML .= '</document>';
        }
    }else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>
						  <document>
								<respon>0</respon>
								<URresponse>password salah</URresponse>
						  </document>';
	}
	return $returnXML;
}

function setPlacementAll($user, $pass, $xml){
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
        $error = 0;
        $arrxml = xml2ary($xml);
        $valResponse = $arrxml['document']['_c']['respon']['_v'];
        if($valResponse != 0){
        	$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';

			$loop =  $arrxml['document']['_c']['loop']['_c']['data'];
            $totalHDR = count($loop);
            $i =0;
            $nocont = "";
            while ($i < $totalHDR) {
                if($totalHDR == 1){
                    $data = $loop['_c'];
                }else{
                    $data = $loop[$i]['_c'];
                }

                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
                $NO_REQUEST = $data['NO_REQUEST']['_v'];
                $BLOCK = $data['BLOCK']['_v'];
                $SLOT = $data['SLOT']['_v'];
                $ROW = $data['ROW']['_v'];
                $TIER = $data['TIER']['_v'];
                $ID_YARD = $data['ID_YARD']['_v'];
                $ID_USER = $data['ID_USER']['_v'];
                $CONT_STATUS = $data['CONT_STATUS']['_v'];
                $TGL_PLACEMENT = $data['TGL_PLACEMENT']['_v'];
                $TIPE_ACTIVITY = $data['TIPE_ACTIVITY']['_v'];

                $returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$NO_CONTAINER.'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$NO_REQUEST.'</NO_REQUEST>';

        		$query_cek_cont = "SELECT * FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER'";
				$result_cek_c	= $db->query($query_cek_cont);
				$row_cek_c		= $result_cek_c->fetchRow();
				$count_c		= count($row_cek_c);
				$size			= $row_cek_c['SIZE_'];

				if($count_c > 0){
					if($location == "GATI"){
						$query_update	= "UPDATE MASTER_CONTAINER SET LOCATION = 'IN_YARD' WHERE NO_CONTAINER = '$NO_CONTAINER'";
						if($db->query($query_update))
						{
							$query_insert_placement = "INSERT INTO PLACEMENT(NO_CONTAINER, ID_BLOCKING_AREA, SLOT_, ROW_, TIER_, TGL_UPDATE, USER_NAME, TGL_PLACEMENT, STATUS) VALUES('$NO_CONTAINER', '$BLOCK', '$SLOT', '$ROW', '$TIER', TO_DATE('".$TGL_PLACEMENT."','MM/DD/YYYY HH24:MI:SS'), '$ID_USER', TO_DATE('".$TGL_PLACEMENT."','MM/DD/YYYY HH24:MI:SS'), '$CONT_STATUS')";
							if($db->query($query_insert_placement)){
								$query_insert_history = "INSERT INTO HISTORY_PLACEMENT(NO_CONTAINER, NO_REQUEST, ID_BLOCKING_AREA, SLOT_, ROW_, TIER_, TGL_UPDATE, NIPP_USER, BAYAR_LOLO,KETERANGAN) VALUES('$NO_CONTAINER', '$NO_REQUEST', '$BLOCK', '$SLOT', '$ROW', '$TIER', TO_DATE('".$TGL_PLACEMENT."','MM/DD/YYYY HH24:MI:SS'), '$ID_USER', 'N', 'RELOKASI')";

								if($db->query($query_insert_history))
								{
									$returnXML .= '<KET>OK</KET>';
								}
							}else{
								$returnXML .= '<KET>DB ERROR</KET>';
							}
						}
					}else if($location == "IN_YARD"){
						$query_update	= "UPDATE PLACEMENT SET ID_BLOCKING_AREA = '$BLOCK', SLOT_ = '$SLOT', ROW_ = '$ROW', TIER_ = '$TIER', TGL_UPDATE = SYSDATE WHERE NO_CONTAINER = '$NO_CONTAINER'";
						if($db->query($query_update))
						{
							$lolo = 'Y';
							if($TIPE_ACTIVITY = '3')
								$lolo = 'N';

							$query_insert_history = "INSERT INTO HISTORY_PLACEMENT(NO_CONTAINER, NO_REQUEST, ID_BLOCKING_AREA, SLOT_, ROW_, TIER_, TGL_UPDATE, NIPP_USER, BAYAR_LOLO,KETERANGAN) VALUES('$NO_CONTAINER', '$NO_REQUEST', '$BLOCK', '$SLOT', '$ROW', '$TIER', TO_DATE('".$TGL_PLACEMENT."','MM/DD/YYYY HH24:MI:SS'), '$ID_USER', '$lolo', 'RELOKASI')";
							if($db->query($query_insert_history))
							{
								$returnXML .= '<KET>OK</KET>';
							}
						}else{
								$returnXML .= '<KET>DB ERROR</KET>';
						}
					}
				}else{
					$returnXML .= '<KET>TIDAK ADA CONTAINER</KET>';
				}
				$returnXML .= '</data>';
				 $i++;
            }
            $returnXML .= '</loop>';
			$returnXML .= '</document>';
        }
    }else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>
						  <document>
								<respon>0</respon>
								<URresponse>password salah</URresponse>
						  </document>';
	}
	return $returnXML;
}

function setGateIn($user, $pass, $xml){
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
        $error = 0;
        $arrxml = xml2ary($xml);
        $valResponse = $arrxml['document']['_c']['respon']['_v'];
        if($valResponse != 0){
        	$returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
			$returnXML .= '<document>';
			$returnXML .= '<respon>1</respon>';
			$returnXML .= '<loop>';

            $loop =  $arrxml['document']['_c']['loop']['_c']['data'];
            $totalHDR = count($loop);
            $i =0;
            $nocont = "";
            while ($i < $totalHDR) {
                if($totalHDR == 1){
                    $data = $loop['_c'];
                }else{
                    $data = $loop[$i]['_c'];
                }

                $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
                $NO_REQUEST = $data['NO_REQUEST']['_v'];
                $NOPOL = $data['NOPOL']['_v'];
                $ID_USER = $data['ID_USER']['_v'];
                $TGL_IN = $data['TGL_IN']['_v'];
                $STATUS = $data['STATUS']['_v'];
                $GATE_ORIGIN = $data['GATE_ORIGIN']['_v'];

                $returnXML .= '<data>';
        		$returnXML .= '<NO_CONTAINER>'.$NO_CONTAINER.'</NO_CONTAINER>';
        		$returnXML .= '<NO_REQUEST>'.$NO_REQUEST.'</NO_REQUEST>';
        		//Cek posisi container
				$query_gati = "SELECT LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER'";
				$result_gati = $db->query($query_gati);
				$row_gati = $result_gati->fetchRow();
				$gati = $row_gati["LOCATION"];
				if($gati == "GATI"){
					$returnXML .= '<KET>EXIST</KET>';
					$returnXML .= '</data>';
					$i++;
					continue;
				}
                //Cek asal container
                if($GATE_ORIGIN == 'DEPO'){//asal depo
                	$cekGateIn = "SELECT NO_CONTAINER, NO_REQUEST FROM GATE_IN WHERE NO_CONTAINER = '".$NO_CONTAINER."' AND NO_REQUEST = '".$NO_REQUEST."'";
                	$resultcekGateIn = $db->query($cekGateIn);
					if($resultcekGateIn->RecordCount() <= 0){
	                	$query_insert	= "INSERT INTO GATE_IN(NO_CONTAINER, NO_REQUEST,NOPOL, ID_USER, TGL_IN,STATUS) VALUES('".$NO_CONTAINER."', '".$NO_REQUEST."','".$NOPOL."', '".$ID_USER."', TO_DATE('".$TGL_IN."','MM/DD/YYYY HH24:MI:SS'), '".$STATUS."')";
						$result_insert	= $db->query($query_insert);

						$qbook = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE, STATUS_CONT FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' AND KEGIATAN = 'REQUEST RECEIVING'";
						$rbook = $db->query($qbook);
						$rwbook = $rbook->fetchRow();
						$cur_booking1 = $rwbook["NO_BOOKING"];
						$cur_counter1 = $rwbook["COUNTER"];
						$tgl_update = $rwbook["TGL_UPDATE"];
						$status_ = $rwbook["STATUS_CONT"];

						$history  = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, STATUS_CONT, NO_BOOKING, COUNTER)
		                              VALUES ('$NO_CONTAINER','$NO_REQUEST','GATE IN',TO_DATE('".$TGL_IN."','MM/DD/YYYY HH24:MI:SS'),'$ID_USER','$STATUS','$cur_booking1','$cur_counter1')";
		                $db->query($history);

						//Update status lokasi container, di dalam atau di luar
						$query_upd	= "UPDATE MASTER_CONTAINER SET LOCATION = 'GATI' WHERE NO_CONTAINER = '$NO_CONTAINER'";
						$db->query($query_upd);

						//hist
						$db->query("INSERT INTO HIST_LOCATION(NO_CONTAINER, NO_REQUEST, LOCATION) VALUES('$NO_CONTAINER','$NO_REQUEST','GATI')");

						//Select Nopol
						$query_nopol ="SELECT NO_TRUCK
								   FROM TRUCK
								   WHERE NO_TRUCK = '$NOPOL'";
						if($db->query($query_nopol))
						{
							$query_insert_nopol = "UPDATE GATE_IN SET TRUCKING ='PELINDO' WHERE NO_CONTAINER = '$NO_CONTAINER'";
							$result_insert_nopol	= $db->query($query_insert_nopol);
						}
						$returnXML .= '<KET>OK</KET>';
					}
                }else if($GATE_ORIGIN == 'TPK'){//asal tpk
					//Insert data cont ke tabel get in
					$query_insert	= "INSERT INTO GATE_IN(NO_CONTAINER, NO_REQUEST,NOPOL, ID_USER, TGL_IN) VALUES('$NO_CONTAINER', '$NO_REQUEST','$NOPOL', '$ID_USER', TO_DATE('".$TGL_IN."','MM/DD/YYYY HH24:MI:SS'))";
					$result_insert	= $db->query($query_insert);

					//Update status lokasi container, di dalam atau di luar
					$query_upd	= "UPDATE MASTER_CONTAINER SET LOCATION = 'GATI' WHERE NO_CONTAINER = '$NO_CONTAINER'";
					$db->query($query_upd);

					//Insert ke handling piutang
					$kegiatan_ = array("LIFT_ON","HAULAGE","LIFT_OFF");
					foreach($kegiatan_ as $kegiatan )
					{
						$query_insert = "INSERT INTO HANDLING_PIUTANG
													(NO_CONTAINER,
													 KEGIATAN,
													 STATUS_CONT,
													 TANGGAL,
													 PENAGIHAN,
													 KETERANGAN
													)
											VALUES	('$NO_CONTAINER',
													 '$kegiatan',
													 (SELECT STATUS
														FROM CONTAINER_RECEIVING
														WHERE NO_CONTAINER = '$NO_CONTAINER'
														AND NO_REQUEST ='$NO_REQUEST'  ),
													  SYSDATE,
													  'PELAYARAN',
													  'FAKTOR_YOR'
													)";
						$result_query_insert = $db->query($query_insert);
					}
					//Cek Truck Apakh dari pelindo atau tidak
					$query_nopol ="SELECT NO_TRUCK
								   FROM TRUCK
								   WHERE NO_TRUCK = '$NOPOL'
								";
					if($db->query($query_nopol))
					{
						$query_insert_nopol = "UPDATE GATE_IN SET TRUCKING ='PELINDO' WHERE NO_CONTAINER = '$NO_CONTAINER'";
						$result_insert_nopol	= $db->query($query_insert_nopol);
					}
					$returnXML .= '<KET>OK</KET>';
                }
                $returnXML .= '</data>';
                $i++;
            }
			$returnXML .= '</loop>';
			$returnXML .= '</document>';
		}
	}else{
		$returnXML = '<?xml version="1.0" encoding="UTF-8"?>
						  <document>
								<respon>0</respon>
								<URresponse>password salah</URresponse>
						  </document>';
	}
	return $returnXML;
}

function renameContainer($user, $pass, $xml){
	if($user == 'npks' && $pass == '12345'){
		$db = getDB('storage');
        $error = 0;
        $arrdata = json_decode($xml);

        $no_cont_old = $arrdata->CONT_OLD;
        $no_cont_new = $arrdata->CONT_NEW;
        $size_new = $arrdata->SIZE_NEW;
        $type_new = $arrdata->TYPE_NEW;
        //echo $no_cont_old;die();
        //cek ketersediaan kontainer baru
        $q_cek_new = "SELECT NO_CONTAINER, NO_BOOKING, COUNTER, LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont_new'";
		$rcek_new = $db->query($q_cek_new);
		$rc_new = $rcek_new->fetchRow();
		if($rc_new["NO_CONTAINER"] != NULL){
			$returnXML = json_encode(array('respon' => 0, 'URresponse' => 'Maaf, No. Container Baru Sudah Ada'));
			return $returnXML; die();
		}

		$q_cek = "SELECT NO_CONTAINER, NO_BOOKING, COUNTER, LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont_old'";
		$rcek = $db->query($q_cek);
		$rc = $rcek->fetchRow();
		$no_cont = $rc["NO_CONTAINER"];
		$no_booking = $rc["NO_BOOKING"];
		$counter = $rc["COUNTER"];
		$location = $rc["LOCATION"];

		if($no_cont != NULL){
			$gethistory = "SELECT NO_CONTAINER,NO_REQUEST,KEGIATAN FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont_old'";
			$rhist = $db->query($gethistory);
			$rh = $rhist->getAll();
			foreach($rh as $rha){
				$no_req = $rha["NO_REQUEST"];
				$keg 	= $rha["KEGIATAN"];
				if($keg == "REQUEST RECEIVING"){
					$qupdrec = "UPDATE CONTAINER_RECEIVING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdrec);
				}
				else if($keg == "GATE IN"){
					$qupdgati = "UPDATE GATE_IN SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdgati);
				}
				else if($keg == "GATE OUT"){
					$qupdgato = "UPDATE GATE_OUT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdgato);
				}
				else if($keg == "BORDER GATE IN" || $keg == "BORDER GATE OUT"){
					$qupdgatib = "UPDATE BORDER_GATE_IN SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdgatib);
					$qupdgatob = "UPDATE BORDER_GATE_OUT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdgatob);
				}
				else if($keg == "PERPANJANGAN STRIPPING" || $keg == "REQUEST STRIPPING"){
					$qupdstrip = "UPDATE CONTAINER_STRIPPING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdstrip);
				}
				else if($keg == "REQUEST STUFFING" || $keg == "PERPANJANGAN STUFFING"){
					$qupdstuf = "UPDATE CONTAINER_STUFFING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdstuf);
				}
				else if($keg == "PLAN REQUEST STRIPPING"){
					$qupdplst = "UPDATE PLAN_CONTAINER_STRIPPING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdplst);
				}
				else if($keg == "PLAN REQUEST STUFFING"){
					$qupdplstf = "UPDATE PLAN_CONTAINER_STUFFING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdplstf);
				}
				else if($keg == "REQUEST BATALMUAT"){
					$qupdbm = "UPDATE CONTAINER_BATAL_MUAT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdbm);
				}
				else if($keg == "REQUEST DELIVERY" || $keg == "PERP DELIVERY"){
					$qupdbm = "UPDATE CONTAINER_DELIVERY SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
					$db->query($qupdbm);
				}
			}

			$get_history_ = "SELECT NO_REQUEST, NO_CONTAINER, NO_BOOKING FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont_old'";
			$exhist		= $db->query($get_history_);
			$rxhist 	= $exhist->getAll();
			foreach($rxhist as $rxh){
				$noreq_hist = $rxh["NO_REQUEST"];
				$nobook_hist = $rxh["NO_BOOKING"];

				$update_history = "UPDATE HISTORY_CONTAINER SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$noreq_hist' AND NO_BOOKING = '$nobook_hist' AND NO_CONTAINER = '$no_cont_old'";
				$db->query($update_history);
			}

			$update_master = "INSERT INTO MASTER_CONTAINER (NO_CONTAINER, SIZE_, TYPE_, LOCATION, NO_BOOKING, COUNTER) VALUES ('$no_cont_new', '$size_new' , '$type_new', '$location', '$no_booking', '$counter')";
			$db->query($update_master);
			$update_old = "UPDATE MASTER_CONTAINER SET MLO = '-' WHERE NO_CONTAINER = '$no_cont_old'";
			$db->query($update_old);

			//update placement
			$cek_placement = "SELECT NO_CONTAINER, NO_REQUEST_RECEIVING FROM PLACEMENT WHERE NO_CONTAINER = '$no_cont_old'";
			$r_cek_placement = $db->query($cek_placement);
			$rwcek = $r_cek_placement->fetchRow();
			$contplace = $rwcek["NO_REQUEST_RECEIVING"];
			$db->query("UPDATE PLACEMENT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST_RECEIVING = '$contplace' AND NO_CONTAINER = '$no_cont_old'");

			//update history placement
			$cek_hplace = "SELECT NO_REQUEST FROM HISTORY_PLACEMENT WHERE NO_CONTAINER = '$no_cont_old'";
			$rhplace = $db->query($cek_hplace);
			$rhwplace = $rhplace->getAll();
			foreach($rhwplace as $rhw){
				$reqh = $rhw["NO_REQUEST"];
				$updhsplace = "UPDATE HISTORY_PLACEMENT SET NO_CONTAINER = '$no_cont_new' WHERE NO_CONTAINER = '$no_cont_old'";
				$db->query("");
			}

			$returnXML = json_encode(array('respon' => 1, 'URresponse' => 'Rename Success'));
		}else{
			$returnXML = json_encode(array('respon' => 0, 'URresponse' => 'Failed'));
			return $returnXML; die();
		}
    }else{
		$returnXML = json_encode(array('respon' => 0, 'URresponse' => 'password salah'));
	}
	return $returnXML;
}

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>
