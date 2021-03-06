<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends BD_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
         $this->load->database();
        // $this->default = $this->load->database('default',true);
        $this->reponpks = $this->load->database('reponpks',true);
        $this->npks = $this->load->database('npks',true);
    }

    //Menampilkan data kontak
    function index_get($param) {
        $this->auth_basic();
        $table = strtoupper($param);
        $id = $this->get('id');
        if ($id == '') {
            $kontak = $this->db->get($table)->result();
        } else {
            $this->db->where('id', $id);
            $kontak = $this->db->get($table)->result();
        }
        $this->response($kontak, 200);
    }

     //Mengirim atau menambah data kontak baru
    function basic_post() {
        $this->auth_basic();
        $data = $this->post();

        if ($data['action'] == 'simplelist'){
            $list = $this->reponpks->get($data['table'])->result();
        }
        $this->response($list, 200);
    }

    public function bearer_post() {
        $this->auth_berier(); //bearer token
        $data = $this->post();
        if ($data['action'] == 'simplelist'){
            $list = $this->reponpks->get($data['table'])->result();
        }
        $this->response($list, 200);
    }

    function api_post() {
        $this->auth_api();
        $data = $this->post();

        if ($data['action'] == 'simplelist'){
            $list = $this->reponpks->get($data['table'])->result();
        }
        $this->response($list, 200);
    }

    public function del_delete() {
        $this->auth_basic();
        $data = $this->delete();
        $id = (int)$data['id'];
        // echo $id;die();
        // Validate the id.
        if ($id <= 0)
        {
            // Set the response and exit
            $this->response(NULL, 400); // BAD_REQUEST (400) being the HTTP response code
        }

        // $this->some_model->delete_something($id);
        $message = [
            'id' => $id,
            'message' => 'Deleted the resource'
        ];
        $this->response($message, 200); // NO_CONTENT (204) being the HTTP response code
    }

    public function repo_post() {
      $this->auth_basic();
      $branch     = 3;
      $url        = explode('encode=',$_SERVER['REQUEST_URI']);

      if (!isset($url[1])) {
        $request  = $this->post();
        $input    = json_decode(json_encode($this->post()), TRUE);
      } else {
        $input    = json_decode(json_encode($this->post()), TRUE);
        $input    = json_decode($input["request"], TRUE);
      }

      $action     = $input["action"]."_post";
      $this->$action($input, $branch);
    }

    // New
    function getDelivery_post($input, $branch) {
      $this->auth_basic();
      $branch              = 3;
      //header
      $header              = $input['header'];

      $REQ_NO              = $header['REQ_NO'];
      $REQ_DELIVERY_DATE   = $header['REQ_DELIVERY_DATE'];
      $NO_NOTA             = $header['NO_NOTA'];
      $TGL_NOTA            = $header['TGL_NOTA'];
      $NM_CONSIGNEE        = $header['NM_CONSIGNEE'];
      $ALAMAT              = $header['ALAMAT'];
      $REQ_MARK            = $header['REQ_MARK'];
      $NPWP                = str_replace(".", "", str_replace("-", "", trim($header['NPWP'])));
      $DELIVERY_KE         = $header['DELIVERY_KE'];
      $TANGGAL_LUNAS       = $header['TANGGAL_LUNAS'];
      $PERP_DARI           = $header['PERP_DARI'];
      $PERP_KE             = $header['PERP_KE'];


      $sqlcek           = $this->reponpks->where('REQ_BRANCH_ID', $branch)->where("REQ_NO", $REQ_NO)->get('TX_REQ_DELIVERY_HDR');
      $resultCek        = $sqlcek->result_array();

      $sqlceknpwp       = $this->db->where("CONSIGNEE_NPWP", $NPWP)->select("CONSIGNEE_ID")->get('TM_CONSIGNEE');
      $resultCeknpwp    = $sqlceknpwp->result_array();

      if (empty($resultCeknpwp)) {
        // If NPWP empty Create New Consigne
        $qlIDCONSIGNEE      = $this->db->select("SEQ_CONSIGNEE_ID.NEXTVAL AS ID")->get('DUAL');
        $resultIDCONSIGNEE  = $qlIDCONSIGNEE->result_array();
        $CONSIGNE_ID        = $resultIDCONSIGNEE[0]['ID'];
        $insertConsignee    = "
                                 INSERT INTO TM_CONSIGNEE
                                 (
                                   CONSIGNEE_ID,
                                   CONSIGNEE_NAME,
                                   CONSIGNEE_ADDRESS,
                                   CONSIGNEE_NPWP
                                 )
                                 VALUES
                                 (
                                   " . $CONSIGNE_ID . ",
                                   '" . $NM_CONSIGNEE . "',
                                   '" . $ALAMAT . "',
                                   '" . $NPWP . "'
                                 )
                                 ";
        $this->db->query($insertConsignee);
      } else {
        $CONSIGNE_ID       = $resultCeknpwp[0]['CONSIGNEE_ID'];
      }

      //print_r($resultID);
      $i = 0;
      if ($PERP_KE == "") {
        $PERP_KE = 0;
      }

      if ($DELIVERY_KE == 'LUAR')
        $DELIVERY_KE = 'DEPO';

      if (empty($resultCek)) {
        $qlID = "SELECT SEQ_REQ_DELIVERY_HDR.NEXTVAL AS ID FROM DUAL";
        $resultID = $this->db->query($qlID)->result_array();
        $IDheader = $resultID[0]['ID'];

        $query = "
          INSERT INTO TX_REQ_DELIVERY_HDR
          (
            REQ_ID,
            REQ_NO,
            REQ_CONSIGNEE_ID,
            REQ_BRANCH_ID,
            REQ_MARK,
            REQ_DELIVERY_DATE,
            REQUEST_NOTA_DATE,
            REQUEST_PAID_DATE,
            REQUEST_TO, REQUEST_STATUS,
            REQUEST_EXTEND_FROM,
            REQUEST_EXTEND_LOOP,
            REQUEST_ALIH_KAPAL
          )
          VALUES
          (
            " . $IDheader . ",
            '" . $REQ_NO . "',
            " . $CONSIGNE_ID . ",
            " . $branch . ",
            '" . $REQ_MARK . "',
            TO_DATE('" . $REQ_DELIVERY_DATE . "','MM/DD/YYYY HH24:MI:SS'),
            TO_DATE('" . $TGL_NOTA . "','MM/DD/YYYY HH24:MI:SS'),
            TO_DATE('" . $TANGGAL_LUNAS . "','MM/DD/YYYY HH24:MI:SS'),
            '" . $DELIVERY_KE . "',
            '1','" . $PERP_DARI . "',
            " . $PERP_KE . ",
            'Y'
          )";

        $insertHDR = $this->reponpks->query($query);
        $result["header"] = "1. Header Sukses | " . $REQ_NO . " " . date('Y-m-d H:i:s') . "<br>\n";
      } else {
        $result["header"] = "Header Exist REQUEST_NO = " . $REQ_NO . " <br>\n";
        $insertHDR = true;
        $IDheader = $resultCek[0]['REQ_ID'];
      }


      //detail
      $detail = $input['arrdetail'];
      if ($insertHDR) {
        foreach ($detail as $val) {

          $sqlcek = $this->reponpks->where('REQ_DTL_CONT', $val['REQ_DTL_CONT'])->where('REQ_HDR_ID', $IDheader)->get('TX_REQ_DELIVERY_DTL');
          $resultcekdtl = $sqlcek->result_array();

          if (empty($resultcekdtl)) {
            $sqlIDTL = "SELECT SEQ_REQ_DELIVERY_DTL.NEXTVAL AS ID FROM DUAL";
            $resultIDTL = $this->db->query($sqlIDTL)->result_array();
            $IDdetail = $resultIDTL[0]['ID'];

            $REQ_DTL_CONT = $val['REQ_DTL_CONT'];
            $REQ_DTL_CONT_STATUS = $val['REQ_DTL_CONT_STATUS'];
            $REQ_DTL_COMMODITY = $val['REQ_DTL_COMMODITY'];
            $REQ_DTL_VIA = $val['REQ_DTL_VIA'];
            $REQ_DTL_TYPE = $val['REQ_DTL_TYPE'];
            $REQ_DTL_SIZE = $val['REQ_DTL_SIZE'];
            $REQ_DTL_DEL_DATE = $val['REQ_DTL_DEL_DATE'];
            $REQ_DTL_CONT_HAZARD = $val['REQ_DTL_CONT_HAZARD'];
            $REQ_DTL_NO_SEAL = $val['REQ_DTL_NO_SEAL'];

            $queryDTL = "
                    INSERT INTO TX_REQ_DELIVERY_DTL
                    (
                      REQ_DTL_ID,
                      REQ_HDR_ID,
                      REQ_DTL_CONT,
                      REQ_DTL_CONT_STATUS,
                      REQ_DTL_CONT_HAZARD,
                      REQ_DTL_CONT_SIZE,
                      REQ_DTL_CONT_TYPE,
                      REQ_DTL_COMMODITY,
                      REQ_DTL_DEL_DATE,
                      REQ_DTL_NO_SEAL
                    )
                    VALUES
                    (
                      " . $IDdetail . ",
                      " . $IDheader . ",
                      '" . $REQ_DTL_CONT . "',
                      '" . $REQ_DTL_CONT_STATUS . "',
                      '" . $REQ_DTL_CONT_HAZARD . "',
                      '" . $REQ_DTL_SIZE . "',
                      '" . $REQ_DTL_TYPE . "',
                      '" . $REQ_DTL_COMMODITY . "',
                      TO_DATE('" . $REQ_DTL_DEL_DATE . "','MM/DD/YYYY HH24:MI:SS'),
                      '" . $REQ_DTL_NO_SEAL . "'
                    )";
            $resultDtl = $this->reponpks->query($queryDTL);
            if ($resultDtl) $result["detail"] = "Detail Success | " . $REQ_DTL_CONT . " " . date('Y-m-d H:i:s') . "<br>\n";

            if ($PERP_DARI != "") {
              echo $a . ", perpanjangan dari  | " . $PERP_DARI . "~" . $REQ_DTL_CONT . " " . date('Y-m-d H:i:s') . "<br>\n";
              $updateDeliveryTDL = "UPDATE TX_REQ_DELIVERY_DTL SET REQ_DTL_ACTIVE = 'T', REQ_DTL_STATUS = '2' WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '" . $PERP_DARI . "' AND REQ_BRANCH_ID = " . $branch . ") AND REQ_DTL_CONT = '" . $REQ_DTL_CONT . "'  ";
              $this->reponpks->query($updateDeliveryTDL);

              $cek_tot_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '" . $PERP_DARI . "' AND REQ_BRANCH_ID = " . $branch . ")")->row()->JML;
              $cek_tot_dtl_T = $this->reponpks->query("SELECT COUNT(*) JML FROM TX_REQ_DELIVERY_DTL WHERE REQ_HDR_ID = (SELECT REQ_ID FROM TX_REQ_DELIVERY_HDR WHERE REQ_NO = '" . $PERP_DARI . "' AND REQ_BRANCH_ID = " . $branch . ") AND REQ_DTL_ACTIVE = 'T' ")->row()->JML;

              if ($cek_tot_dtl == $cek_tot_dtl_T) {
                $updateStuffHDR = "UPDATE TX_REQ_DELIVERY_HDR SET REQUEST_STATUS = '2' WHERE REQ_NO = '" . $PERP_DARI . "' AND REQ_BRANCH_ID = " . $branch . " ";
                $this->reponpks->query($updateStuffHDR);
              }
            }

            $this->db->query("CALL ADD_HISTORY_CONTAINER(
                    '" . $REQ_DTL_CONT . "',
                    '" . $REQ_NO . "',
                    '" . $REQ_DELIVERY_DATE . "',
                    '" . $REQ_DTL_SIZE . "',
                    '" . $REQ_DTL_TYPE . "',
                    '" . $REQ_DTL_CONT_STATUS . "',
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    4,
                    'Request Delivery',
                    NULL,
                    NULL,
                    " . $branch . ",
                    NULL,
                    NULL)");
          } else {
            $result["detail"] = "Detail Exist <br>\n";
          }
        }
      }

      $link        = oci_connect('NPKS_PLG_REPO', 'npksplgrepo', '10.88.48.34:1521/INVDB');
      // Syn Header
      $sqlHeader   = "
                      DECLARE
                      v_flag VARCHAR2(2);
                      v_msg VARCHAR2(100);
                      BEGIN PKG_SYNC_TABLE.P_TX_REQ_DELIVERY_HDR(v_flag,v_msg);
                      end;
                      ";

      $stmtHeader       = oci_parse($link, $sqlHeader);
      $queryHeader      = oci_execute($stmtHeader);

      // Syn Detail
      $sqlDetail   = "
                      DECLARE
                      v_flag VARCHAR2(2);
                      v_msg VARCHAR2(100);
                      BEGIN PKG_SYNC_TABLE.P_TX_REQ_DELIVERY_DTL(v_flag,v_msg);
                      end;
                      ";

      $stmtDetail       = oci_parse($link, $sqlDetail);
      $queryDetail      = oci_execute($stmtDetail);

      // JSON Response
      header('Content-Type: application/json');
      echo json_encode($result);
    }

    function getStuffing_post($input, $branch) {
      $this->auth_basic();
      $devdb                            = $this->db;
      $repodb                           = $this->reponpks;
      $branch                           = 3;
      $header                           = $input["header"];
      $detail                           = $input["arrdetail"];

      // Header
      $REQ_NO                           = $header['REQ_NO'];
      $NO_NOTA                          = $header['NO_NOTA'];
      $TGL_NOTA                         = $header['TGL_NOTA'];
      $REQ_STUFF_DATE                   = $header['REQ_STUFF_DATE'];
      $NM_CONSIGNEE                     = $header['NM_CONSIGNEE'];
      $REQ_MARK                         = $header['REQ_MARK'];
      $NO_BOOKING                       = $header['NO_BOOKING'];
      $NO_UKK                           = $header['NO_UKK'];
      $NPWP                             = str_replace(".", "",str_replace("-", "", trim($header['NPWP'])));
      $ALAMAT                           = $header['ALAMAT'];
      $TANGGAL_LUNAS                    = $header['TANGGAL_LUNAS'];
      $NO_REQUEST_REC                   = $header['NO_REQUEST_RECEIVING'];
      $STUFFING_DARI                    = $header['STUFFING_DARI'];
      $PERP_DARI                        = $header['PERP_DARI'];
      $PERP_KE                          = $header['PERP_KE'];

      if(empty($PERP_KE)) $PERP_KE      = 0;

      $sqlcek                           = $repodb->where('STUFF_BRANCH_ID', $branch)->where("STUFF_NO", $REQ_NO)->get('TX_REQ_STUFF_HDR');
      $resultCek                        = $sqlcek->result_array();
      // print_r($resultCek);

      // Check Data Exist or No
      $sqlceknpwp                       = $devdb->where("CONSIGNEE_NPWP", $NPWP)->select("CONSIGNEE_ID")->get('TM_CONSIGNEE');
      $resultCeknpwp                    = $sqlceknpwp->result_array();

      // Check NPWP Exist Or No
      if(empty($resultCeknpwp)) {
          // If NPWP empty Create New Consigne
          $qlIDCONSIGNEE                = $devdb->select("SEQ_CONSIGNEE_ID.NEXTVAL AS ID")->get('DUAL');
          $resultIDCONSIGNEE            = $qlIDCONSIGNEE->result_array();
          $CONSIGNE_ID                  = $resultIDCONSIGNEE[0]['ID'];
          $insertConsignee              = "
                                                 INSERT INTO TM_CONSIGNEE
                                                 (
                                                   CONSIGNEE_ID,
                                                   CONSIGNEE_NAME,
                                                   CONSIGNEE_ADDRESS,
                                                   CONSIGNEE_NPWP
                                                 )
                                                 VALUES
                                                 (
                                                   ".$CONSIGNE_ID.",
                                                   '".$NM_CONSIGNEE."',
                                                   '".$ALAMAT."',
                                                   '".$NPWP."'
                                                 )
                                                 ";
          $devdb->query($insertConsignee);
        } else {
          $CONSIGNE_ID                  = $resultCeknpwp[0]['CONSIGNEE_ID'];
      }

      if(empty($resultCek)){
          // If Empty Direct Insert to TX_REQ_STUFF_HDR
          $qlID                         = $devdb->select("SEQ_TX_STUFF_HDR.NEXTVAL AS ID")->get('DUAL');
          $resultID                     = $qlID->result_array();
          $IDheader                     = $resultID[0]['ID'];

          $STUFF_ORIGIN                 = 'INTERNAL';
          if($STUFFING_DARI == 'TPK') $STUFF_ORIGIN = 'TPK';

          // Insert Header
          $insertHDR                    = "
                                          INSERT INTO TX_REQ_STUFF_HDR
                                          (
                                            STUFF_ID,
                                            STUFF_NO,
                                            STUFF_CONSIGNEE_ID,
                                            STUFF_BRANCH_ID,
                                            STUFF_CREATE_DATE,
                                            STUFF_NOTA_DATE,
                                            STUFF_NOTA_NO,
                                            STUFF_PAID_DATE,
                                            STUFF_NO_BOOKING,
                                            STUFF_NO_UKK,
                                            STUFF_NOREQ_RECEIVING,
                                            STUFF_EXTEND_FROM,
                                            STUFF_EXTEND_LOOP,
                                            STUFF_ORIGIN,
                                            STUFF_STATUS
                                            )
                                          VALUES
                                          (
                                            ".$IDheader.",
                                            '".$REQ_NO."',
                                            ".$CONSIGNE_ID.",
                                            ".$branch.",
                                            TO_DATE('".$REQ_STUFF_DATE."','MM/DD/YYYY HH24:MI:SS'),
                                            TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),
                                            '".$NO_NOTA."',
                                            TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'),
                                            '".$NO_BOOKING."',
                                            '".$NO_UKK."',
                                            '".$NO_REQUEST_REC."',
                                            '".$PERP_DARI."',
                                            ".$PERP_KE.",
                                            '".$STUFF_ORIGIN."',
                                            '1'
                                            )
                                          ";
          $resultHDR                      = $repodb->query($insertHDR);
          $result["header"]               = "1. Header Sukses | ".$REQ_NO." ".date('Y-m-d H:i:s')."<br>\n";
        } else {
          $result["header"]               = "Header Exist REQ_NO = ".$REQ_NO." <br>\n";
          $resultHDR                      = true;
          $IDheader                       = $resultCek[0]['STUFF_ID'];
      }

      if ($resultHDR) {
          //cek detil container ada tidak dari TPK
          $contFromTPK                    = 'T';
          $totalDTL                       = count($detail);
          $b                              = 0;
          $arrTpk                         = array();
          $ketPerpanjangan                = 'Request Stuffing';

          while($b < $totalDTL) {
            $detailroot                   = $detail[$b];
            $REQ_DTL_ORIGIN               = trim($detailroot['REQ_DTL_ORIGIN']);
            $arrTpk[]                     = $REQ_DTL_ORIGIN;
            $b++;
          }

          if (in_array("TPK", $arrTpk)) $contFromTPK  = 'Y';

          if(($STUFFING_DARI == 'TPK') && ($PERP_DARI =='') && ($contFromTPK == 'Y')) {
            $ServiceID      = date('YmdHis').rand(100,999);
            $method         = 'getReceivingFromTPK'; //method
            $insertServices = "
                              INSERT INTO TX_SERVICES
                              (
                                SERVICES_ID,
                                SERVICES_METHOD,
                                SERVICES_REQ_XML,
                                SERVICES_STATUS
                              )
                              VALUES
                              (
                                ".$ServiceID.",
                                '".$method."',
                                '".$NO_REQUEST_REC."',
                                '0'
                                )
                              ";

            $insert         = $repodb->query($insertServices);
          }

          if(!empty($PERP_DARI)) $ketPerpanjangan = 'Perpanjangan Stuffing';

          // Exceion Detail
          $totalDTL                       = count($detail);
          $a                              = 0;

          // Loop Detail
          while($a < $totalDTL) {
            $detailroot                   = $detail[$a];
            $REQ_DTL_CONT                 = trim($detailroot['REQ_DTL_CONT']);
            $REQ_DTL_COMMODITY            = trim($detailroot['REQ_DTL_COMMODITY']);
            $REQ_DTL_CONT_HAZARD          = trim($detailroot['REQ_DTL_CONT_HAZARD']);
            $REQ_DTL_SIZE                 = trim($detailroot['REQ_DTL_SIZE']);
            $REQ_DTL_TYPE                 = trim($detailroot['REQ_DTL_TYPE']);
            $REQ_DTL_REMARK_SP2           = trim($detailroot['REQ_DTL_REMARK_SP2']);
            $REQ_DTL_ORIGIN               = trim($detailroot['REQ_DTL_ORIGIN']);
            $STUFF_DTL_START_STUFF_PLAN   = trim($detailroot['TGL_MULAI']);
            $STUFF_DTL_END_STUFF_PLAN     = trim($detailroot['TGL_SELESAI']);

            // Cek Detail Exist Or No
            $sqlcekdetilnya               = $repodb->where("STUFF_DTL_HDR_ID", $IDheader)->where("STUFF_DTL_CONT", $REQ_DTL_CONT)->get('TX_REQ_STUFF_DTL');
            $resultCekdetilNya            = $sqlcekdetilnya->result_array();

            if(empty($resultCekdetilNya)) {
              if(($STUFFING_DARI == 'TPK') && ($PERP_DARI =='') && ($contFromTPK == 'Y')){
                $ServiceID              = date('YmdHis').rand(100,999);
                $method                 = 'getGateInFromTPK'; //method
                $insertServices         = "
                                          INSERT INTO TX_SERVICES
                                          (
                                            SERVICES_ID,
                                            SERVICES_METHOD,
                                            SERVICES_REQ_XML,
                                            SERVICES_STATUS)
                                          VALUES
                                          (
                                            ".$ServiceID.",
                                            '".$method."',
                                            '".$NO_REQUEST_RECEIVING."~".$REQ_DTL_CONT."',
                                            '0'
                                          )";
                // $insert                 = $devdb->query($insertServices);
              }

              // Container Counter Check
              $cont_count                 = '';
              $sqlCekCounterCont          = $devdb->where("CONTAINER_BRANCH_ID", $branch)->where("CONTAINER_NO", $REQ_DTL_CONT)->get('TM_CONTAINER');
              $resultCekCounterCont       = $sqlCekCounterCont->result_array();

              // Give Container Number
              if(!empty($totalCekCounterCont)) {
                  $cont_count = $resultCekCounterCont['CONTAINER_COUNTER'];
                }
                else{
                  $cont_count = 1;
              }

              $insertDTL                  = "
                                            INSERT INTO TX_REQ_STUFF_DTL
                                            (
                                              STUFF_DTL_HDR_ID,
                                              STUFF_DTL_CONT,
                                              STUFF_DTL_CONT_HAZARD,
                                              STUFF_DTL_CONT_SIZE,
                                              STUFF_DTL_CONT_TYPE,
                                              STUFF_DTL_COMMODITY,
                                              STUFF_DTL_REMARK_SP2,
                                              STUFF_DTL_ORIGIN,STUFF_DTL_START_STUFF_PLAN,STUFF_DTL_END_STUFF_PLAN,
                                              STUFF_DTL_CONT_STATUS,
                                              STUFF_DTL_COUNTER
                                            )
                                            VALUES
                                            (
                                              ".$IDheader.",
                                               '".$REQ_DTL_CONT."',
                                               '".$REQ_DTL_CONT_HAZARD."',
                                               '".$REQ_DTL_SIZE."',
                                               '".$REQ_DTL_TYPE."',
                                               '".$REQ_DTL_COMMODITY."',
                                               '".$REQ_DTL_REMARK_SP2."',
                                               '".$REQ_DTL_ORIGIN."',
                                               TO_DATE('".$STUFF_DTL_START_STUFF_PLAN."','MM/DD/YYYY HH24:MI:SS'),
                                               TO_DATE('".$STUFF_DTL_END_STUFF_PLAN."','MM/DD/YYYY HH24:MI:SS'),
                                               'MTY', ".$cont_count."
                                            )";

              $resultDtl                   = $repodb->query($insertDTL);

              if($resultDtl)
                $result["detail"]          = $a.". Detail Success | ".$REQ_DTL_CONT." ".date('Y-m-d H:i:s')."<br>\n";

              if($PERP_DARI !="") {
                $updateStuffTDL            = "
                                              UPDATE TX_REQ_STUFF_DTL SET
                                                STUFF_DTL_ACTIVE = 'T',
                                                STUFF_DTL_STATUS = '2'
                                              WHERE
                                                STUFF_DTL_HDR_ID =
                                                (
                                                  SELECT STUFF_ID FROM TX_REQ_STUFF_HDR
                                                  WHERE
                                                    STUFF_NO = '".$PERP_DARI."'
                                                  AND
                                                    STUFF_BRANCH_ID = ".$branch."
                                                )
                                              AND
                                                STUFF_DTL_CONT = '".$REQ_DTL_CONT."'
                                              ";
                $devdb->query($updateStuffTDL);

                $cek_tot_dtl            = $devdb->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch.")")->row()->JML;
                $cek_tot_dtl_T          = $devdb->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch.") AND STUFF_DTL_ACTIVE = 'T' ")->row()->JML;

                if($cek_tot_dtl == $cek_tot_dtl_T){
                  $updateStuffHDR       = "UPDATE TX_REQ_STUFF_HDR SET STUFF_STATUS = '2' WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch." ";
                  $devdb->query($updateStuffHDR);
                }
              }

              //insert history container
              $devdb->query("
                    CALL ADD_HISTORY_CONTAINER(
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
            } else {
              $result["detail"] = "Detail Exist <br>\n";
            }
            $a++;
        }
      }

      // Syncronize Database PlG - PLG_REPO
      $link        = oci_connect('NPKS_PLG_REPO', 'npksplgrepo', '10.88.48.34:1521/INVDB');
      // Syn Header
      $sqlHeader   = "
                     DECLARE
                     v_flag VARCHAR2(2);
                     v_msg VARCHAR2(100);
                     BEGIN PKG_SYNC_TABLE.P_TX_REQ_STUFF_HDR(v_flag,v_msg);
                     end;
                     ";

      $stmtHeader       = oci_parse($link,$sqlHeader);
      $queryHeader      = oci_execute($stmtHeader);

      // Syn Detail
      $sqlDetail   = "
                     DECLARE
                     v_flag VARCHAR2(2);
                     v_msg VARCHAR2(100);
                     BEGIN pkg_sync_table.p_tx_req_stuff_dtl(v_flag,v_msg);
                     end;
                     ";

      $stmtDetail       = oci_parse($link,$sqlDetail);
      $queryDetail      = oci_execute($stmtDetail);

      // JSON Response
      header('Content-Type: application/json');
      echo json_encode($result);
    }

    function getStripping_post($input, $branch) {
      // Initialization
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $branch                       = 3;
      $header                       = $input["header"];
      $detail                       = $input["arrdetail"];

      // Get Header Data
      $REQ_NO                       = $header['REQ_NO'];
      $NO_NOTA                      = $header['NO_NOTA'];
      $TGL_NOTA                     = $header['TGL_NOTA'];
      $REQ_STRIP_DATE               = $header['REQ_STRIP_DATE'];
      $NM_CONSIGNEE                 = $header['NM_CONSIGNEE'];
      $NO_REQUEST_RECEIVING         = $header['NO_REQUEST_RECEIVING'];
      $PERP_DARI                    = $header['PERP_DARI'];
      $PERP_KE                      = $header['PERP_KE'];
      $STRIP_DARI                   = $header['STRIP_DARI'];
      $REQ_MARK                     = $header['REQ_MARK'];
      $DO                           = $header['DO'];
      $BL                           = $header['BL'];
      $NPWP                         =  str_replace(".", "",str_replace("-", "", trim($header['NPWP'])));
      $ALAMAT                       = $header['ALAMAT'];
      $TANGGAL_LUNAS                = $header['TANGGAL_LUNAS'];

      if(empty($PERP_KE)) $PERP_KE  = 0;

      // Check Data Exist or No
        $sqlcek                     = $repodb->where('STRIP_BRANCH_ID', $branch)->where("STRIP_NO", $REQ_NO)->select("STRIP_NO, STRIP_ID")->get('TX_REQ_STRIP_HDR');
        $resultCek                  = $sqlcek->result_array();

        $sqlceknpwp                 = $devdb->where('CONSIGNEE_NPWP', $NPWP)->select("CONSIGNEE_ID")->get('TM_CONSIGNEE');
        $resultCeknpwp              = $sqlceknpwp->result_array();

        // Get CONSIGNEE_ID
        if(empty($resultCeknpwp)) {
            // Adding New Data To TM_CONSIGNEE
            $qlIDCONSIGNEE          = $devdb->select("SEQ_CONSIGNEE_ID.NEXTVAL AS ID")->get('DUAL');
            $resultIDCONSIGNEE      = $qlIDCONSIGNEE->result_array();
            $CONSIGNE_ID            = $resultIDCONSIGNEE[0]['ID'];
            $insertConsignee        = "
                                      INSERT INTO TM_CONSIGNEE
                                      (
                                        CONSIGNEE_ID,
                                        CONSIGNEE_NAME,
                                        CONSIGNEE_ADDRESS,
                                        CONSIGNEE_NPWP
                                      )
                                      VALUES
                                      (
                                        ".$CONSIGNE_ID.",
                                        '".$NM_CONSIGNEE."',
                                        '".$ALAMAT."',
                                        '".$NPWP."'
                                      )";
            $devdb->query($insertConsignee);
        } else {
            $CONSIGNE_ID            = $resultCeknpwp[0]['CONSIGNEE_ID'];
        }

        if(empty($resultCek)){
          $qlID                     = $devdb->select("SEQ_REQ_STRIP_HDR.NEXTVAL AS ID")->get('DUAL');
          $resultID                 = $qlID->result_array();
          $IDheader                 = $resultID[0]['ID'];

          $STRIP_ORIGIN             = 'INTERNAL';
          if($STRIP_DARI == 'TPK') $STRIP_ORIGIN = 'TPK';

          $insertHDR                = "
                                      INSERT INTO TX_REQ_STRIP_HDR
                                      (
                                        STRIP_ID,
                                        STRIP_NO,
                                        STRIP_CONSIGNEE_ID,
                                        STRIP_BRANCH_ID,
                                        STRIP_DO,
                                        STRIP_BL,
                                        STRIP_CREATE_DATE,
                                        STRIP_NOTA_DATE,
                                        STRIP_NOTA_NO,
                                        STRIP_PAID_DATE,
                                        STRIP_NOREQ_RECEIVING,
                                        STRIP_EXTEND_FROM,STRIP_EXTEND_LOOP,
                                        STRIP_ORIGIN
                                      )
                                      VALUES
                                      (
                                        ".$IDheader.",
                                        '".$REQ_NO."',
                                        ".$CONSIGNE_ID.",
                                        ".$branch.",
                                        '".$DO."',
                                        '".$BL."',
                                        TO_DATE('".$REQ_STRIP_DATE."','MM/DD/YYYY HH24:MI:SS'),
                                        TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),
                                        '".$NO_NOTA."',
                                        TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'),
                                        '".$NO_REQUEST_RECEIVING."',
                                        '".$PERP_DARI."',
                                        ".$PERP_KE.",
                                        '".$STRIP_DARI."'
                                      )";

          $resultHDR                  = $repodb->query($insertHDR);
          $result["header"]           = "Header Insert | REQ_NO = $REQ_NO";
        } else {
          $resultHDR                  = true;
          $IDheader                   = $resultCek[0]['STRIP_ID'];
          $result["header"]           = "Header Exists | REQ_NO = $REQ_NO";
        }

        if($resultHDR) {
          $ketPerpanjangan            = 'Request Stripping';

        if($STRIP_DARI == 'TPK' && $PERP_DARI =='') {
          // Not Checking Yet
          $ServiceID                  = date('YmdHis').rand(100,999);
          $method                     = 'getReceivingFromTPK'; //method
          $insertServices             = "
                                        INSERT INTO TX_SERVICES
                                        (
                                          SERVICES_ID,
                                          SERVICES_METHOD,
                                          SERVICES_REQ_XML,
                                          SERVICES_STATUS
                                        )
                                        VALUES
                                        (
                                          ".$ServiceID.",
                                          '".$method."',
                                          '".$NO_REQUEST_RECEIVING."',
                                          '0'
                                        )";
          $insert                     = $repodb->query($insertServices);
        }

        // Detail Insert
        $totalDTL                     = count($detail);
        $a                            = 0;
        while($a < $totalDTL) {
          $detailroot                 = $detail[$a];
          $REQ_DTL_CONT               = trim($detailroot['REQ_DTL_CONT']);
          $REQ_DTL_COMMODITY          = trim($detailroot['REQ_DTL_COMMODITY']);
          $REQ_DTL_CONT_HAZARD        = trim($detailroot['REQ_DTL_CONT_HAZARD']);
          $REQ_DTL_SIZE               = trim($detailroot['REQ_DTL_SIZE']);
          $REQ_DTL_TYPE               = trim($detailroot['REQ_DTL_TYPE']);
          $STRIP_DTL_ORIGIN           = trim($detailroot['REQ_DTL_ORIGIN']);
          $STRIP_DTL_START_STRIP_PLAN = trim($detailroot['TGL_MULAI']);
          $STRIP_DTL_END_STRIP_PLAN   = trim($detailroot['TGL_SELESAI']);

          if($STRIP_DARI == 'TPK' && $PERP_DARI =='') {
            // Not Checking Yet
            $ServiceID                = date('YmdHis').rand(100,999);
            $method                   = 'getGateInFromTPK'; //method
            $insertServices           = "
                                        INSERT INTO TX_SERVICES
                                        (
                                          SERVICES_ID,
                                          SERVICES_METHOD,
                                          SERVICES_REQ_XML,
                                          SERVICES_STATUS
                                        )
                                        VALUES
                                        (
                                          ".$ServiceID.",
                                          '".$method."',
                                          '".$NO_REQUEST_RECEIVING."~".$REQ_DTL_CONT."',
                                          '0'
                                        )";
              // $insert = $this->db->query($insertServices);
            }

            $sqlcekdetilnya            = $repodb->where("STRIP_DTL_HDR_ID",$IDheader)->where("STRIP_DTL_CONT",$REQ_DTL_CONT)->get('TX_REQ_STRIP_DTL');
            $resultCekdetilNya         = $sqlcekdetilnya->result_array();

            if(empty($resultCekdetilNya)) {

              //cek counter container
              $cont_count              = '';
              $sqlCekCounterCont       = $devdb->select("CONTAINER_COUNTER")->where("CONTAINER_BRANCH_ID",$branch)->where("CONTAINER_NO",$REQ_DTL_CONT)->get('TM_CONTAINER');
              $resultCekCounterCont    = $sqlCekCounterCont->result_array();

              if(empty($resultCekCounterCont)) {
                // Strip From TPK Must In Condition
                if($STRIP_DARI == 'TPK') {
                  // Prep Not Empty
                  if(!empty($PERP_DARI)){
                    $cont_count        = (int)$resultCekCounterCont['CONTAINER_COUNTER'];
                    } else {
                      $cont_count      = (int)$resultCekCounterCont['CONTAINER_COUNTER'] + 1;
                  }
                  // Strip From Not-TPK
                  } else {
                    $cont_count        = (int)$resultCekCounterCont['CONTAINER_COUNTER'];
                }
                // Result Cek Counteer Container Not Empty
                } else {
                  $cont_count          = 1;
              }

              $insertDTL               = "
                                         INSERT INTO TX_REQ_STRIP_DTL
                                         (
                                           STRIP_DTL_HDR_ID,
                                           STRIP_DTL_CONT,
                                           STRIP_DTL_DANGER,
                                           STRIP_DTL_CONT_SIZE,
                                           STRIP_DTL_CONT_TYPE,
                                           STRIP_DTL_COMMODITY,
                                           STRIP_DTL_STATUS,
                                           STRIP_DTL_ORIGIN,STRIP_DTL_START_STRIP_PLAN,STRIP_DTL_END_STRIP_PLAN,
                                           STRIP_DTL_CONT_STATUS,
                                           STRIP_DTL_COUNTER
                                         )
                                         VALUES
                                         (
                                           ".$IDheader.",
                                           '".$REQ_DTL_CONT."',
                                           '".$REQ_DTL_CONT_HAZARD."',
                                           '".$REQ_DTL_SIZE."',
                                           '".$REQ_DTL_TYPE."',
                                           '".$REQ_DTL_COMMODITY."',
                                           '1',
                                           '".$STRIP_DTL_ORIGIN."',
                                           TO_DATE('".$STRIP_DTL_START_STRIP_PLAN."','MM/DD/YYYY HH24:MI:SS'),
                                           TO_DATE('".$STRIP_DTL_END_STRIP_PLAN."','MM/DD/YYYY HH24:MI:SS'),
                                           'FCL',
                                           ".$cont_count."
                                         )";
              $resultDtl                 = $repodb->query($insertDTL);
              if($resultDtl) {
                $result["detail"][]      = "Detail Insert | REQ_NO = $REQ_DTL_CONT".date('Y-m-d H:i:s');
              }

              if(!empty($PERP_DARI)) {
                $ketPerpanjangan         = 'Perpanjangan Stripping';
                $updateStripTDL          = "
                                           UPDATE TX_REQ_STRIP_DTL
                                           SET
                                            STRIP_DTL_ACTIVE = 'T',
                                            STRIP_DTL_STATUS = '2'
                                           WHERE
                                            STRIP_DTL_HDR_ID =
                                            (
                                              SELECT STRIP_ID FROM TX_REQ_STRIP_HDR
                                              WHERE
                                                STRIP_NO = '".$PERP_DARI."'
                                              AND
                                                STRIP_BRANCH_ID = ".$branch."
                                              )
                                            AND
                                              STRIP_DTL_CONT = '".$REQ_DTL_CONT."'
                                            ";

                $devdb->query($updateStripTDL);

                $cek_tot_dtl              = $repodb->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch.")")->row()->JML;
                $cek_tot_dtl_T            = $repodb->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch.") AND STRIP_DTL_ACTIVE = 'T' ")->row()->JML;

                if($cek_tot_dtl == $cek_tot_dtl_T) {
                  $updateStripHDR         = "
                                            UPDATE TX_REQ_STRIP_HDR SET
                                              STRIP_STATUS = '2'
                                            WHERE
                                              STRIP_NO = '".$PERP_DARI."'
                                            AND
                                              STRIP_BRANCH_ID = ".$branch."
                                            ";
                    $repodb->query($updateStripHDR);
                }
              }

              //insert history container
              $devdb->query("
                    CALL ADD_HISTORY_CONTAINER
                    (
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
                      NULL
                      )");
            } else {
              $result["detail"][]  = "Detail Exist | REQ_NO = $REQ_DTL_CONT".date('Y-m-d H:i:s');
            }
            $a++;
        }
      }

      // Syncronize Database PlG - PLG_REPO
      $link        = oci_connect('NPKS_PLG_REPO', 'npksplgrepo', '10.88.48.34:1521/INVDB');
      // Syn Header
      $sqlHeader   = "
                     DECLARE
                     v_flag VARCHAR2(2);
                     v_msg VARCHAR2(100);
                     BEGIN PKG_SYNC_TABLE.P_TX_REQ_STRIP_HDR(v_flag,v_msg);
                     end;
                     ";

      $stmtHeader       = oci_parse($link,$sqlHeader);
      $queryHeader      = oci_execute($stmtHeader);

      // Syn Detail
      $sqlDetail   = "
                     DECLARE
                     v_flag VARCHAR2(2);
                     v_msg VARCHAR2(100);
                     BEGIN PKG_SYNC_TABLE.P_TX_REQ_STRIP_DTL(v_flag,v_msg);
                     end;
                     ";

      $stmtDetail       = oci_parse($link,$sqlDetail);
      $queryDetail      = oci_execute($stmtDetail);

      // JSON Response
      header('Content-Type: application/json');
      echo json_encode($result);
    }

    function getPlugging_post($input, $branch) {
      $db       = $this->db;
      $repodb   = $this->reponpks;
      $header   = $input["header"];
      $detail   = $input["arrdetail"];

      $query    = $repodb->where('PLUG_ID', $header["PLUG_ID"])->where("PLUG_ID", $header["PLUG_ID"])->get('TX_REQ_PLUG_HDR');
      $result   = $query->result();

      if (!empty($result)) {
        $result["header"] = "Header Exist Ada";
      } else {
        $result["header"] = "Header Insert";
      }

      $head     = $repodb->set($header)->get_compiled_insert('TX_REQ_PLUG_HDR');
      $this->reponpks->query($head);

      foreach ($detail as $detail) {
        $det    = $db->set($detail)->get_compiled_insert('TX_REQ_PLUG_DTL');
        $this->reponpks->query($det);
      }

      // JSON Response
      header('Content-Type: application/json');
      echo json_encode($result);
    }

    function getFumigasi_post($input, $branch) {
      $db       = $this->db;
      $repodb   = $this->reponpks;
      $header   = $input["header"];
      $detail   = $input["arrdetail"];

      $query    = $repodb->where('FUMI_ID', $header["FUMI_ID"])->where("FUMI_ID", $header["FUMI_ID"])->get('TX_REQ_FUMI_HDR');
      $result   = $query->result();

      if (!empty($result)) {
        $result["header"] = "Header Exist Ada";
      } else {
        $result["header"] = "Header Insert";
      }

      $head     = $repodb->set($header)->get_compiled_insert('TX_REQ_FUMI_HDR');
      $this->reponpks->query($head);

      foreach ($detail as $detail) {
        $det    = $db->set($detail)->get_compiled_insert('TX_REQ_FUMI_DTL');
        $this->reponpks->query($det);
      }

      // JSON Response
      header('Content-Type: application/json');
      echo json_encode($result);
    }

    function generateGetIn_post($input, $branch) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $branch                       = 3;
      $data                         = $input["data"];

      $newdt                        = [];

      foreach ($data as $data) {
        $sqlgetIn                     = $repodb->select("
                                                        GATE_ID,
                                                        GATE_CONT AS NO_CONTAINER,
                                                        GATE_NOREQ AS NO_REQUEST,
                                                        GATE_TRUCK_NO AS NOPOL,
                                                        GATE_CREATE_BY AS ID_USER,
                                                        GATE_BRANCH_ID AS BRANCH_ID,
                                                        TO_CHAR(GATE_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_IN,
                                                        GATE_CONT_STATUS AS STATUS,
                                                        GATE_ORIGIN
                                                        ")
                                               ->where("GATE_ACTIVITY","3")
                                               ->where("GATE_STATUS","1")
                                               ->where("GATE_CONT",$data["NO_CONTAINER"])
                                               ->where("GATE_NOREQ",$data["NO_REQUEST"])
                                               ->where("GATE_BRANCH_ID",$data["BRANCH_ID"])
                                               ->order_by("GATE_CREATE_DATE", "ASC")
                                               ->get('TX_GATE');
        $resultservices               = $sqlgetIn->result_array();

        $data_view = json_encode($resultservices);
        $data_use  = json_decode($data_view);

        foreach ($data_use as $value) {
          $newdt[] = $value;
        }
      }

      echo json_encode($newdt);
    }

    function generateGetOut_post($input, $branch) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $branch                       = 3;
      $data                         = $input["data"];

      $newdt                        = [];

      foreach ($data as $data) {
        $sqlgetOut                  = $repodb->select("
                                                        A.GATE_ID,
                                                        A.GATE_CONT AS NO_CONTAINER,
                                                        A.GATE_NOREQ AS NO_REQUEST,
                                                        TO_CHAR(B.REQ_DELIVERY_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQ_DELIVERY,
                                                        A.GATE_TRUCK_NO AS NOPOL,
                                                        A.GATE_CREATE_BY,
                                                        TO_CHAR(A.GATE_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_OUT,
                                                        GATE_CONT_STATUS AS STATUS,
                                                        GATE_ORIGIN AS GATE_DESTINATION,
                                                        A.GATE_NO_SEAL AS NO_SEAL,
                                                        A.GATE_MARK AS MARK
                                                        ")
                                               ->join('TX_REQ_DELIVERY_HDR B', 'B.REQ_NO = A.GATE_NOREQ')
                                               ->where("GATE_ACTIVITY","4")
                                               ->where("GATE_STATUS","3")
                                               ->where("GATE_CONT",$data["NO_CONTAINER"])
                                               ->where("GATE_NOREQ",$data["NO_REQUEST"])
                                               ->where("GATE_BRANCH_ID",$data["BRANCH_ID"])
                                               ->where("GATE_ORIGIN","DEPO")
                                               ->order_by("GATE_CREATE_DATE", "ASC")
                                               ->get("TX_GATE A");
        $resultservices               = $sqlgetOut->result_array();

        $data_view = json_encode($resultservices);
        $data_use  = json_decode($data_view);

        foreach ($data_use as $value) {
          $newdt[] = $value;
        }
      }

      echo json_encode($newdt);
    }

    function generateRealStuffing_post($input, $branch) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $npksdb                       = $this->npks;
      $branch                       = 3;
      $data                         = $input["data"];

      $newdt                        = [];

      foreach ($data as $data) {
        // Change Later
        $sqlgetStuf = "
                      SELECT * FROM (SELECT D.REAL_YARD_YBC_ID YBC_ID, A.REAL_STUFF_ID, A.REAL_STUFF_CONT NO_CONT, B.STUFF_NO NO_REQUEST,
                      (SELECT STUFF_DTL_COMMODITY FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) COMMODITY,
                      (SELECT STUFF_DTL_CONT_HAZARD FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) HZ,
                      (SELECT STUFF_DTL_CONT_SIZE FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) CONT_SIZE,
                      (SELECT STUFF_DTL_CONT_TYPE FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) CONT_TYPE,
                      TO_CHAR(B.STUFF_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQUEST, B.STUFF_NO_BOOKING NO_BOOKING, B.STUFF_NO_UKK NO_UKK,
                      A.REAL_STUFF_BY, TO_CHAR(A.REAL_STUFF_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REALISASI, A.REAL_STUFF_MECHANIC_TOOLS ALAT,
                      (SELECT STUFF_DTL_REMARK_SP2 FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = B.STUFF_ID AND STUFF_DTL_CONT = A.REAL_STUFF_CONT) REMARK_SP2
                      FROM TX_REAL_STUFF A
                      INNER JOIN TX_REQ_STUFF_HDR B ON B.STUFF_ID = A.REAL_STUFF_HDR_ID
                      INNER JOIN TX_REAL_YARD D ON D.REAL_YARD_CONT = A.REAL_STUFF_CONT AND D.REAL_YARD_STATUS = '1'
                      WHERE A.REAL_STUFF_STATUS = 1
                      AND A.REAL_STUFF_FL_SEND = 0
                      AND D.REAL_YARD_USED = 1
                      ORDER BY A.REAL_STUFF_DATE ASC)Z WHERE ROWNUM <= 1
                        ";

        $resultservices = $npksdb->query($sqlgetStuf);
        $totalservice = $resultservices->result_array();

        $data_view = json_encode($totalservice);
        $data_use  = json_decode($data_view);

        foreach ($data_use as $value) {
          $newdt[] = $value;
        }
      }

      echo json_encode($newdt);
    }

    function generatePlacement_post($input, $branch) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $npksdb                       = $this->npks;
      $branch                       = 3;
      $data                         = $input["data"];

      $newdt                        = [];

      foreach ($data as $data) {
        // Change Later
        $sqlPlacement            = $npksdb->select("
                                                    A.REAL_YARD_ID,
                                                    A.REAL_YARD_YBC_ID,
                                                    B.YBC_SLOT,
                                                    B.YBC_ROW,
                                                    B.YBC_BLOCK_ID,
                                                    A.REAL_YARD_TIER TIER,
                                                    A.REAL_YARD_NO ID_YARD,
                                                    UPPER(A.REAL_YARD_CONT) NO_CONTAINER,
                                                    A.REAL_YARD_REQ_NO NO_REQUEST,
                                                    A.REAL_YARD_BRANCH_ID,
                                                    TO_CHAR(A.REAL_YARD_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_PLACEMENT,
                                                    A.REAL_YARD_CREATE_BY,
                                                    A.REAL_YARD_CONT_STATUS CONT_STATUS
                                                    ")
                                               ->join('TX_YARD_BLOCK_CELL B', 'B.YBC_ID = A.REAL_YARD_YBC_ID')
                                               ->where("REAL_YARD_CONT",$data["NO_CONTAINER"])
                                               ->where("REAL_YARD_REQ_NO",$data["NO_REQUEST"])
                                               ->where("REAL_YARD_BRANCH_ID",$data["BRANCH_ID"])
                                               ->order_by("REAL_YARD_CREATE_DATE", "ASC")
                                               ->get("TX_REAL_YARD A");

        $resultservices       = $sqlPlacement->result_array();
        $data_view            = json_encode($resultservices);
        $data_use             = json_decode($data_view);

        foreach ($data_use as $value) {
          $newdt[] = $value;
        }
      }

      echo json_encode($newdt);
    }

    function generateGateInCargo_post($input, $branch) {
      echo "GateInCargo";
    }

    function generateRealStripping_post($input, $branch) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $npksdb                       = $this->npks;
      $branch                       = 3;
      $data                         = $input["data"];

      $newdt                        = [];

      foreach ($data as $data) {
        // Change Later
        $sqlgetStrip = "
                       SELECT * FROM
                       (
                         SELECT * FROM
                         (
                           SELECT MAX
                              (C.REAL_YARD_YBC_ID) over () as MAX_ID,
                              A.REAL_STRIP_ID,
                              A.REAL_STRIP_CONT NO_CONT,
                              A.REAL_STRIP_NOREQ NO_REQUEST,
                              TO_CHAR(B.STRIP_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REQUEST,
                              A.REAL_STRIP_MECHANIC_TOOLS ALAT,
                              TO_CHAR(A.REAL_STRIP_DATE,'MM/DD/YYYY HH24:MI:SS') AS TGL_REALISASI,A.REAL_STRIP_BY,
                              C.REAL_YARD_YBC_ID YBC_ID,
                              A.REAL_STRIP_MARK,
                              A.REAL_STRIP_BACKDATE
                           FROM
                              TX_REAL_STRIP A
                           INNER JOIN
                              TX_REQ_STRIP_HDR B ON B.STRIP_ID = A.REAL_STRIP_HDR_ID
                           INNER JOIN
                              TX_REAL_YARD C ON C.REAL_YARD_CONT = A.REAL_STRIP_CONT AND C.REAL_YARD_STATUS = '1'
                           WHERE
                              A.REAL_STRIP_FL_SEND = 0
                           AND
                              A.REAL_STRIP_STATUS = 2
                           AND
                              A.REAl_STRIP_BRANCH_ID = '3'
                          ORDER BY
                              A.REAL_STRIP_DATE ASC
                          ) Z
                        WHERE
                          Z.MAX_ID = Z.YBC_ID
                      ) ZZ
                      WHERE
                        rownum <=1 ";

        $resultservices = $npksdb->query($sqlgetStrip);
        $totalservice = $resultservices->result_array();

        $data_view = json_encode($totalservice);
        $data_use  = json_decode($data_view);

        foreach ($data_use as $value) {
          $newdt[] = $value;
        }
      }

      echo json_encode($newdt);
    }

    function getReceiving_post($input, $branch) {
	    $this->auth_basic();
	    $branch             = 3;
	    //header
	    $header             = $this->post('header');

	    $REQ_NO             = $header['REQ_NO'];
	    $REQ_RECEIVING_DATE = $header['REQ_RECEIVING_DATE'];
	    $NO_NOTA            = $header['NO_NOTA'];
	    $TGL_NOTA           = $header['TGL_NOTA'];
	    $NM_CONSIGNEE       = $header['NM_CONSIGNEE'];
	    $ALAMAT             = $header['ALAMAT'];
	    $REQ_MARK           = $header['REQ_MARK'];
	    $NPWP               = str_replace(".", "", str_replace("-", "", trim($header['NPWP'])));
	    $RECEIVING_DARI     = $header['RECEIVING_DARI'];
	    $TANGGAL_LUNAS      = $header['TANGGAL_LUNAS'];
	    $DI                 = $header['DI'];

	    if ((strtolower($DI) == 'domestik' or strtolower($DI) == 'd')) {
	      $DI = 'D';
	    } else {
	      $DI = 'I';
	    }

	    if ($RECEIVING_DARI == 'LUAR') {
	      $RECEIVING_DARI = 'DEPO';
	      $isDEPO = true;
	    }


	    $sqlcek           = $this->reponpks->where('REQUEST_BRANCH_ID', $branch)->where("REQUEST_NO", $REQ_NO)->get('TX_REQ_RECEIVING_HDR');
	    $resultCek        = $sqlcek->result_array();

	    $sqlceknpwp       = $this->db->where("CONSIGNEE_NPWP", $NPWP)->select("CONSIGNEE_ID")->get('TM_CONSIGNEE');
	    $resultCeknpwp    = $sqlceknpwp->result_array();

	    //print_r($resultCek);

	    if (empty($resultCeknpwp)) {
	      // If NPWP empty Create New Consigne
	      $qlIDCONSIGNEE      = $this->db->select("SEQ_CONSIGNEE_ID.NEXTVAL AS ID")->get('DUAL');
	      $resultIDCONSIGNEE  = $qlIDCONSIGNEE->result_array();
	      $CONSIGNE_ID        = $resultIDCONSIGNEE[0]['ID'];
	      $insertConsignee    = "
	                                   INSERT INTO TM_CONSIGNEE
	                                   (
	                                     CONSIGNEE_ID,
	                                     CONSIGNEE_NAME,
	                                     CONSIGNEE_ADDRESS,
	                                     CONSIGNEE_NPWP
	                                   )
	                                   VALUES
	                                   (
	                                     " . $CONSIGNE_ID . ",
	                                     '" . $NM_CONSIGNEE . "',
	                                     '" . $ALAMAT . "',
	                                     '" . $NPWP . "'
	                                   )
	                                   ";
	      $this->db->query($insertConsignee);
	    } else {
	      $CONSIGNE_ID       = $resultCeknpwp[0]['CONSIGNEE_ID'];
	    }


	    if (empty($resultCek)) {
	      $qlID = "SELECT SEQ_REQ_RECEIVING_HDR.NEXTVAL AS ID FROM DUAL";
	      $resultID = $this->db->query($qlID)->result_array();
	      $IDheader = $resultID[0]['ID'];

	      $query = "INSERT INTO TX_REQ_RECEIVING_HDR
	                  (
	                    REQUEST_ID,
	                    REQUEST_NO,
	                    REQUEST_CONSIGNEE_ID,
	                    REQUEST_BRANCH_ID,
	                    REQUEST_NOTA,
	                    REQUEST_MARK,
	                    REQUEST_RECEIVING_DATE,
	                    REQUEST_NOTA_DATE,
	                    REQUEST_PAID_DATE,
	                    REQUEST_FROM,
	                    REQUEST_STATUS,
	                    REQUEST_DI
	                  )
	                  VALUES
	                  (
	                  " . $IDheader . ",
	                  '" . $REQ_NO . "',
	                  " . $CONSIGNE_ID . ",
	                  " . $branch . ",
	                  '" . $NO_NOTA . "',
	                  '" . $REQ_MARK . "',
	                  TO_DATE('" . $REQ_RECEIVING_DATE . "','MM/DD/YYYY HH24:MI:SS'),
	                  TO_DATE('" . $TGL_NOTA . "','MM/DD/YYYY HH24:MI:SS'),
	                  TO_DATE('" . $TANGGAL_LUNAS . "','MM/DD/YYYY HH24:MI:SS'),
	                  '" . $RECEIVING_DARI . "',
	                  '1',
	                  '" . $DI . "'
	                  )";

	      $insertHDR = $this->reponpks->query($query);
	      $result["header"] = "1. Header Sukses | " . $REQ_NO . " " . date('Y-m-d H:i:s') . "<br>\n";
	    } else {
	      $result["header"] = "Header Exist REQUEST_NO = " . $REQ_NO . " <br>\n";
	      $insertHDR = true;
	      $IDheader = $resultCek[0]['REQUEST_ID'];
	    }


	    //detail
	    $detail = $this->post('arrdetail');
	    if ($insertHDR) {
	      foreach ($detail as $val) {

	        $sqlcek = $this->reponpks->where('REQUEST_DTL_CONT', $val['REQ_DTL_CONT'])->where('REQUEST_HDR_ID', $IDheader)->get('TX_REQ_RECEIVING_DTL');
	        $resultcekdtl = $sqlcek->result_array();

	        if (empty($resultcekdtl)) {
	          $sqlIDTL = "SELECT SEQ_REQ_RECEIVING_DTL.NEXTVAL AS ID FROM DUAL";
	          $resultIDTL = $this->db->query($sqlIDTL)->result_array();
	          $IDdetail = $resultIDTL[0]['ID'];
	          $REQ_DTL_CONT = $val['REQ_DTL_CONT'];
	          $REQ_DTL_CONT_STATUS = $val['REQ_DTL_CONT_STATUS'];
	          $REQ_DTL_COMMODITY = $val['REQ_DTL_COMMODITY'];
	          $REQ_DTL_VIA = $val['REQ_DTL_VIA'];
	          $REQ_DTL_SIZE = $val['REQ_DTL_SIZE'];
	          $REQ_DTL_TYPE = $val['REQ_DTL_TYPE'];
	          $REQ_DTL_CONT_HAZARD = $val['REQ_DTL_CONT_HAZARD'];
	          $REQUEST_DTL_OWNER_CODE = $val['REQ_DTL_OWNER_CODE'];
	          $REQ_DTL_OWNER_NAME = $val['REQ_DTL_OWNER_NAME'];

	          $queryDTL = "INSERT INTO TX_REQ_RECEIVING_DTL
	                      (
	                        REQUEST_DTL_ID,
	                        REQUEST_HDR_ID,
	                        REQUEST_DTL_CONT,
	                        REQUEST_DTL_CONT_STATUS,
	                        REQUEST_DTL_DANGER,
	                        REQUEST_DTL_CONT_SIZE,
	                        REQUEST_DTL_CONT_TYPE,
	                        REQUEST_DTL_COMMODITY,
	                        REQUEST_DTL_OWNER_CODE,
	                        REQUEST_DTL_OWNER_NAME
	                      )
	                      VALUES
	                      (
	                        " . $IDdetail . ",
	                        " . $IDheader . ",
	                        '" . $REQ_DTL_CONT . "',
	                        '" . $REQ_DTL_CONT_STATUS . "',
	                        '" . $REQ_DTL_CONT_HAZARD . "',
	                        '" . $REQ_DTL_SIZE . "',
	                        '" . $REQ_DTL_TYPE . "',
	                        '" . $REQ_DTL_COMMODITY . "',
	                        '" . $REQUEST_DTL_OWNER_CODE . "',
	                        '" . $REQ_DTL_OWNER_NAME . "'
	                      )";
	          $resultDtl = $this->reponpks->query($queryDTL);
	          if ($resultDtl) $result["detail"] = "Detail Success | " . $REQ_DTL_CONT . " " . date('Y-m-d H:i:s') . "<br>\n";


	          if ($REQUEST_DTL_OWNER_CODE != '') {
	            $sqlcekowner = "SELECT OWNER_CODE FROM TM_OWNER WHERE OWNER_CODE='" . $REQUEST_DTL_OWNER_CODE . "' AND OWNER_BRANCH_ID = " . $branch . " ";
	            $resultCekowner = $this->db->query($sqlcekowner);
	            $totalcekowner = $resultCekowner->num_rows();
	            if ($totalcekowner <= 0) {
	              $insertOwner = "INSERT INTO TM_OWNER (OWNER_CODE, OWNER_NAME, OWNER_BRANCH_ID) VALUES ('" . $REQUEST_DTL_OWNER_CODE . "','" . $REQUEST_DTL_OWNER_NAME . "'," . $branch . ")";
	              $this->db->query($insertOwner);
	            }
	          }

	          //insert history container
	          $this->db->query("CALL ADD_HISTORY_CONTAINER(
	                      '" . $REQ_DTL_CONT . "',
	                      '" . $REQ_NO . "',
	                      '" . $REQ_RECEIVING_DATE . "',
	                      '" . $REQ_DTL_SIZE . "',
	                      '" . $REQ_DTL_TYPE . "',
	                      '" . $REQ_DTL_CONT_STATUS . "',
	                      NULL,
	                      NULL,
	                      NULL,
	                      NULL,
	                      NULL,
	                      3,
	                      'Request Receiving',
	                      NULL,
	                      NULL,
	                      " . $branch . ",
	                      '" . $RECEIVING_DARI . "',
	                      NULL)");

	          //   $sqlcekmstcont = "SELECT CONTAINER_NO FROM TM_CONTAINER WHERE CONTAINER_NO='".$REQ_DTL_CONT."' AND CONTAINER_BRANCH_ID = ".$branch." ";
	          //   $resultCekmstcont = $this->db->query($sqlcekmstcont);
	          //   $totalcekmstcont = $resultCekmstcont->num_rows();
	          //   if($totalcekmstcont >0){
	          //     if($REQUEST_DTL_OWNER_CODE != '') {
	          //       $updatecontowner = "UPDATE TM_CONTAINER SET CONTAINER_OWNER = '".$REQUEST_DTL_OWNER_CODE."' WHERE CONTAINER_NO='".$REQ_DTL_CONT."' AND CONTAINER_BRANCH_ID = ".$branch." ";
	          //       $this->db->query($updatecontowner);
	          //     }
	          //   }
	          // }

	        } else {
	          $result["detail"] = "Detail Exist <br>\n";
	        }
	      }
	    }
	    // Syncronize Database PlG - PLG_REPO
	    $link        = oci_connect('NPKS_PLG_REPO', 'npksplgrepo', '10.88.48.34:1521/INVDB');
	    // Syn Header
	    $sqlHeader   = "
	                      DECLARE
	                      v_flag VARCHAR2(2);
	                      v_msg VARCHAR2(100);
	                      BEGIN PKG_SYNC_TABLE.P_TX_REQ_RECEIVING_HDR(v_flag,v_msg);
	                      end;
	                      ";

	    $stmtHeader       = oci_parse($link, $sqlHeader);
	    $queryHeader      = oci_execute($stmtHeader);

	    // Syn Detail
	    $sqlDetail   = "
	                      DECLARE
	                      v_flag VARCHAR2(2);
	                      v_msg VARCHAR2(100);
	                      BEGIN PKG_SYNC_TABLE.P_TX_REQ_RECEIVING_DTL(v_flag,v_msg);
	                      end;
	                      ";

	    $stmtDetail       = oci_parse($link, $sqlDetail);
	    $queryDetail      = oci_execute($stmtDetail);
	    // JSON Response
	    header('Content-Type: application/json');
	    echo json_encode($result);
    }

    function getAlihKapalStuffing_post($input, $branch) {
      $devdb                            = $this->db;
      $repodb                           = $this->reponpks;
      $sqlLastDateNota                  = "
                                           SELECT TGL FROM
                                           (
                                             SELECT TO_CHAR(STUFF_PAID_DATE,'YYYYMMDDHH24MISS') TGL FROM
                                                TX_REQ_STUFF_HDR
                                             WHERE
                                                STUFF_PAID_DATE IS NOT NULL
                                             AND
                                                STUFF_ALIH_KAPAL = 'Y'
                                             AND
                                                STUFF_BRANCH_ID = ".$branch."
                                             ORDER BY
                                                STUFF_PAID_DATE DESC
                                            ) A
                                            WHERE rownum = 1";
      $resultLastDateNota               = $repodb->query($sqlLastDateNota);
      $totalservice                     = $resultLastDateNota->num_rows();

      if($totalservice <= 0){
        $lastDateNota                   = date('YmdHis',strtotime("-5 days"));
      } else {
        $resultLastDateNota             = $resultLastDateNota->result_array();
        $lastDateNota                   = $resultLastDateNota[0]['TGL'];
      }

      $params                           = array('string0'=>'npks','string1'=>'12345','string2'=>$lastDateNota); //Tanya
      $ServiceID                        = date('YmdHis').rand(100,999);

      echo $ServiceID;


    }

    function getReceivingTPK_post($input, $branch) {
      $this->auth_basic();
  	  $branch             = 3;
  	  //header
  	  $header             = $this->post('header');

  	  $REQ_NO             = $header['REQ_NO'];
  	  $REQ_RECEIVING_DATE = $header['REQ_RECEIVING_DATE'];
  	  $NO_NOTA            = $header['NO_NOTA'];
  	  $TGL_NOTA           = $header['TGL_NOTA'];
  	  $NM_CONSIGNEE       = $header['NM_CONSIGNEE'];
  	  $ALAMAT             = $header['ALAMAT'];
  	  $REQ_MARK           = $header['REQ_MARK'];
  	  $NPWP               = str_replace(".", "", str_replace("-", "", trim($header['NPWP'])));
  	  $RECEIVING_DARI     = $header['RECEIVING_DARI'];
  	  $TANGGAL_LUNAS      = $header['TANGGAL_LUNAS'];
  	  $DI                 = $header['DI'];

  	  if ((strtolower($DI) == 'domestik' or strtolower($DI) == 'd')) {
  	    $DI = 'D';
  	  } else {
  	    $DI = 'I';
  	  }

  	  if ($RECEIVING_DARI == 'LUAR') {
  	    $RECEIVING_DARI = 'DEPO';
  	  }


  	  $sqlcek           = $this->reponpks->where('REQUEST_BRANCH_ID', $branch)->where("REQUEST_NO", $REQ_NO)->get('TX_REQ_RECEIVING_HDR');
  	  $resultCek        = $sqlcek->result_array();

  	  $sqlceknpwp       = $this->db->where("CONSIGNEE_NPWP", $NPWP)->select("CONSIGNEE_ID")->get('TM_CONSIGNEE');
  	  $resultCeknpwp    = $sqlceknpwp->result_array();

  	    //print_r($resultCek);

  	  if (empty($resultCeknpwp)) {
  	    // If NPWP empty Create New Consigne
  	    $qlIDCONSIGNEE      = $this->db->select("SEQ_CONSIGNEE_ID.NEXTVAL AS ID")->get('DUAL');
  	    $resultIDCONSIGNEE  = $qlIDCONSIGNEE->result_array();
  	    $CONSIGNE_ID        = $resultIDCONSIGNEE[0]['ID'];
  	    $insertConsignee    = "
  	                                INSERT INTO TM_CONSIGNEE
  	                                (
  	                                  CONSIGNEE_ID,
  	                                  CONSIGNEE_NAME,
  	                                  CONSIGNEE_ADDRESS,
  	                                  CONSIGNEE_NPWP
  	                                )
  	                                VALUES
  	                                (
  	                                  " . $CONSIGNE_ID . ",
  	                                  '" . $NM_CONSIGNEE . "',
  	                                  '" . $ALAMAT . "',
  	                                  '" . $NPWP . "'
  	                                )
  	                                ";
  	    $this->db->query($insertConsignee);
  	  } else {
  	    $CONSIGNE_ID       = $resultCeknpwp[0]['CONSIGNEE_ID'];
  	  }

  	  if (empty($resultCek)) {
  	    $qlID = "SELECT SEQ_REQ_RECEIVING_HDR.NEXTVAL AS ID FROM DUAL";
  	    $resultID = $this->db->query($qlID)->result_array();
  	    $IDheader = $resultID[0]['ID'];
  	    $query = "INSERT INTO TX_REQ_RECEIVING_HDR
  	                (
  	                  REQUEST_ID,
  	                  REQUEST_NO,
  	                  REQUEST_CONSIGNEE_ID,
  	                  REQUEST_BRANCH_ID,
  	                  REQUEST_NOTA,
  	                  REQUEST_MARK,
  	                  REQUEST_RECEIVING_DATE,
  	                  REQUEST_NOTA_DATE,
  	                  REQUEST_PAID_DATE,
  	                  REQUEST_FROM,
  	                  REQUEST_STATUS,
  	                  REQUEST_DI
  	                )
  	                VALUES
  	                (
  	                " . $IDheader . ",
  	                '" . $REQ_NO . "',
  	                " . $CONSIGNE_ID . ",
  	                " . $branch . ",
  	                '" . $NO_NOTA . "',
  	                '" . $REQ_MARK . "',
  	                TO_DATE('" . $REQ_RECEIVING_DATE . "','MM/DD/YYYY HH24:MI:SS'),
  	                TO_DATE('" . $TGL_NOTA . "','MM/DD/YYYY HH24:MI:SS'),
  	                TO_DATE('" . $TANGGAL_LUNAS . "','MM/DD/YYYY HH24:MI:SS'),
  	                '" . $RECEIVING_DARI . "',
  	                '1',
  	                '" . $DI . "'
  	                )";

  	    $insertHDR = $this->reponpks->query($query);
  	    $result["header"] = "1. Header Sukses | " . $REQ_NO . " " . date('Y-m-d H:i:s') . "<br>\n";
  	  } else {
  	    $result["header"] = "Header Exist REQUEST_NO = " . $REQ_NO . " <br>\n";
  	    $insertHDR = true;
  	    $IDheader = $resultCek[0]['REQUEST_ID'];
  	  }

  	  //detail
  	  $detail = $this->post('arrdetail');
  	  if ($insertHDR) {
  	    foreach ($detail as $val) {

  	      $sqlcek = $this->reponpks->where('REQUEST_DTL_CONT', $val['REQ_DTL_CONT'])->where('REQUEST_HDR_ID', $IDheader)->get('TX_REQ_RECEIVING_DTL');
  	      $resultcekdtl = $sqlcek->result_array();

  	      if (empty($resultcekdtl)) {
  	        $sqlIDTL = "SELECT SEQ_REQ_RECEIVING_DTL.NEXTVAL AS ID FROM DUAL";
  	        $resultIDTL = $this->db->query($sqlIDTL)->result_array();
  	        $IDdetail = $resultIDTL[0]['ID'];
  	        $REQ_DTL_CONT = $val['REQ_DTL_CONT'];
  	        $REQ_DTL_CONT_STATUS = $val['REQ_DTL_CONT_STATUS'];
  	        $REQ_DTL_COMMODITY = $val['REQ_DTL_COMMODITY'];
  	        $REQ_DTL_VIA = $val['REQ_DTL_VIA'];
  	        $REQ_DTL_SIZE = $val['REQ_DTL_SIZE'];
  	        $REQ_DTL_TYPE = $val['REQ_DTL_TYPE'];
  	        $REQ_DTL_CONT_HAZARD = $val['REQ_DTL_CONT_HAZARD'];
  	        $REQUEST_DTL_OWNER_CODE = $val['REQ_DTL_OWNER_CODE'];
  	        $REQ_DTL_OWNER_NAME = $val['REQ_DTL_OWNER_NAME'];

  	        $ownerCode = '';
  	        $ownerName = '';
  	        $sqlcekmstcont = "SELECT B.OWNER_CODE, B.OWNER_NAME FROM TM_CONTAINER A JOIN TM_OWNER B ON B.OWNER_CODE = A.CONTAINER_OWNER
  	                          WHERE CONTAINER_NO = '" . $REQ_DTL_CONT . "' AND A.CONTAINER_BRANCH_ID = " . $branch;
  	        $resultCekmstcont = $this->db->query($sqlcekmstcont);
  	        $arrData = $resultCekmstcont->row_array();
  	        if (!empty($arrData)) {
  	          $ownerCode = $arrData['OWNER_CODE'];
  	          $ownerName =  $arrData['OWNER_NAME'];
  	        }

  	        $queryDTL = "INSERT INTO TX_REQ_RECEIVING_DTL
  	                    (
  	                      REQUEST_DTL_ID,
  	                      REQUEST_HDR_ID,
  	                      REQUEST_DTL_CONT,
  	                      REQUEST_DTL_CONT_STATUS,
  	                      REQUEST_DTL_DANGER,
  	                      REQUEST_DTL_CONT_SIZE,
  	                      REQUEST_DTL_CONT_TYPE,
  	                      REQUEST_DTL_COMMODITY,
  	                      REQUEST_DTL_OWNER_CODE,
  	                      REQUEST_DTL_OWNER_NAME
  	                    )
  	                    VALUES
  	                    (
  	                      " . $IDdetail . ",
  	                      " . $IDheader . ",
  	                      '" . $REQ_DTL_CONT . "',
  	                      '" . $REQ_DTL_CONT_STATUS . "',
  	                      '" . $REQ_DTL_CONT_HAZARD . "',
  	                      '" . $REQ_DTL_SIZE . "',
  	                      '" . $REQ_DTL_TYPE . "',
  	                      '" . $REQ_DTL_COMMODITY . "',
  	                      '" . $ownerCode . "',
  	                      '" . $ownerName . "'
  	                    )";
  	        $resultDtl = $this->reponpks->query($queryDTL);
  	        if ($resultDtl) $result["detail"] = "Detail Success | " . $REQ_DTL_CONT . " " . date('Y-m-d H:i:s') . "<br>\n";


  	        //insert history container
  	        $this->db->query("CALL ADD_HISTORY_CONTAINER(
  	                        '" . $REQ_DTL_CONT . "',
  	                        '" . $REQ_NO . "',
  	                        '" . $REQ_RECEIVING_DATE . "',
  	                        '" . $REQ_DTL_SIZE . "',
  	                        '" . $REQ_DTL_TYPE . "',
  	                        '" . $REQ_DTL_CONT_STATUS . "',
  	                        NULL,
  	                        NULL,
  	                        NULL,
  	                        NULL,
  	                        NULL,
  	                        3,
  	                        'Request Receiving',
  	                        NULL,
  	                        NULL,
  	                        " . $branch . ",
  	                        'TPK',
  	                        NULL)");
  	      } else {
  	        $result["detail"] = "Detail Exist <br>\n";
  	      }
  	    }
  	  }
  	  // Syncronize Database PlG - PLG_REPO
  	  $link        = oci_connect('NPKS_PLG_REPO', 'npksplgrepo', '10.88.48.34:1521/INVDB');
  	  // Syn Header
  	  $sqlHeader   = "
  	                      DECLARE
  	                      v_flag VARCHAR2(2);
  	                      v_msg VARCHAR2(100);
  	                      BEGIN PKG_SYNC_TABLE.P_TX_REQ_RECEIVING_HDR(v_flag,v_msg);
  	                      end;
  	                      ";

  	  $stmtHeader       = oci_parse($link, $sqlHeader);
  	  $queryHeader      = oci_execute($stmtHeader);

  	  // Syn Detail
  	  $sqlDetail   = "
  	                      DECLARE
  	                      v_flag VARCHAR2(2);
  	                      v_msg VARCHAR2(100);
  	                      BEGIN PKG_SYNC_TABLE.P_TX_REQ_RECEIVING_DTL(v_flag,v_msg);
  	                      end;
  	                      ";

  	  $stmtDetail       = oci_parse($link, $sqlDetail);
  	  $queryDetail      = oci_execute($stmtDetail);
  	    // JSON Response
  	  header('Content-Type: application/json');
  	  echo json_encode($result);
	   }
}
?>
