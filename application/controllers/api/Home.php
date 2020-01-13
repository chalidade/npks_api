<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends BD_Controller {

    function __construct($config = 'rest') {
        parent::__construct($config);
         $this->load->database();
        // $this->default = $this->load->database('default',true);
        $this->reponpks = $this->load->database('reponpks',true);
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

    public function bearer_post()
    {
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

    public function del_delete()
    {
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

    public function getStuffing_post() {
      $this->auth_basic();
      $devdb                            = $this->db;
      $repodb                           = $this->reponpks;
      $branch                           = 3;
      $header                           = $this->post("header");
      $detail                           = $this->post("arrdetail");

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

      $sqlcek                           = $devdb->where('STUFF_BRANCH_ID', $branch)->where("STUFF_NO", $REQ_NO)->get('TX_REQ_STUFF_HDR');
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

            $insert         = $devdb->query($insertServices);
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
            $sqlcekdetilnya               = $devdb->where("STUFF_DTL_HDR_ID", $IDheader)->where("STUFF_DTL_CONT", $REQ_DTL_CONT)->get('TX_REQ_STUFF_DTL');
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

      // JSON Response
      header('Content-Type: application/json');
      echo json_encode($result);
    }

    public function getStripping_post() {
      // Initialization
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $branch                       = 3;
      $header                       = $this->post("header");
      $detail                       = $this->post("arrdetail");

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
        $sqlcek                     = $devdb->where('STRIP_BRANCH_ID', $branch)->where("STRIP_NO", $REQ_NO)->select("STRIP_NO, STRIP_ID")->get('TX_REQ_STRIP_HDR');
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
          $insert                     = $this->db->query($insertServices);
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

            $sqlcekdetilnya            = $devdb->where("STRIP_DTL_HDR_ID",$IDheader)->where("STRIP_DTL_CONT",$REQ_DTL_CONT)->get('TX_REQ_STRIP_DTL');
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

                $cek_tot_dtl              = $devdb->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch.")")->row()->JML;
                $cek_tot_dtl_T            = $devdb->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = (SELECT STRIP_ID FROM TX_REQ_STRIP_HDR WHERE STRIP_NO = '".$PERP_DARI."' AND STRIP_BRANCH_ID = ".$branch.") AND STRIP_DTL_ACTIVE = 'T' ")->row()->JML;

                if($cek_tot_dtl == $cek_tot_dtl_T) {
                  $updateStripHDR         = "
                                            UPDATE TX_REQ_STRIP_HDR SET
                                              STRIP_STATUS = '2'
                                            WHERE
                                              STRIP_NO = '".$PERP_DARI."'
                                            AND
                                              STRIP_BRANCH_ID = ".$branch."
                                            ";
                    $devdb->query($updateStripHDR);
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
      // JSON Response
      header('Content-Type: application/json');
      echo json_encode($result);
    }


    public function getDelivery_post()
    {
    	$this->auth_basic();

    	//header
    	$header = $this->post('header');

    	//detail
    	$arrdetail = $this->post('arrdetail');
    	$detail = $arrdetail['detail'];
    	foreach ($detail as $a) {
    		print_r($a['REQ_DTL_CONT']);
    	}
    	$this->response($detail,200);
    }

    public function getReceiving_post()
    {
        $this->auth_basic();
        //header

        $header = $this->post('header');

        $IDheader = 3;
        $REQ_NO = $header['REQ_NO'];
        $REQ_RECEIVING_DATE = $header['REQ_RECEIVING_DATE'];
        $NO_NOTA = $header['NO_NOTA'];
        $TGL_NOTA = $header['TGL_NOTA'];
        $NM_CONSIGNEE = $header['NM_CONSIGNEE'];
        $ALAMAT = $header['ALAMAT'];
        $REQ_MARK = $header['REQ_MARK'];
        $NPWP = $header['NPWP'];
        $RECEIVING_DARI = $header['RECEIVING_DARI'];
        $TANGGAL_LUNAS = $header['TANGGAL_LUNAS'];
        $DI = $header['DI'];
        $CONSIGNEE = 33213;
        $BRANCH = 441321;
        $REQUEST_CREATE_BY=444432;
        $REQUEST_NO_TPK=444332;
        $REQUEST_CREATE_BY=444432;
        $REQUEST_DO_NO = "AAE";
        $REQUEST_BL_NO = "AAEA";
        $REQUEST_SPPB_NO = "AAEAA";

        // $insertHDR = "INSERT INTO TX_REQ_RECEIVING_HDR
        //             ('REQUEST_ID')
        //             VALUES
        //             (4)";
        // $resultHDR = $this->reponpks->query($insertHDR);
        $query = "INSERT INTO TX_REQ_RECEIVING_HDR (REQUEST_ID,REQUEST_NO,REQUEST_CONSIGNEE_ID,REQUEST_BRANCH_ID,REQUEST_CREATE_BY,REQUEST_NOTA,REQUEST_NO_TPK,REQUEST_DO_NO,REQUEST_BL_NO,REQUEST_SPPB_NO,REQUEST_SPPB_DATE,REQUEST_RECEIVING_DATE,REQUEST_NOTA_DATE,REQUEST_PAID_DATE,REQUEST_FROM,REQUEST_STATUS,REQUEST_DI) VALUES (".$IDheader.",'".$REQ_NO."',".$CONSIGNEE.",".$BRANCH
        .",'".$REQUEST_CREATE_BY."','".$NO_NOTA."','".$REQUEST_NO_TPK."','".$REQUEST_DO_NO."','".$REQUEST_BL_NO."','".$REQUEST_SPPB_NO."',TO_DATE('".$TGL_NOTA."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$REQ_RECEIVING_DATE."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$REQ_RECEIVING_DATE."','MM/DD/YYYY HH24:MI:SS'),TO_DATE('".$TANGGAL_LUNAS."','MM/DD/YYYY HH24:MI:SS'),'TEST','AA','D')";


       if($this->reponpks->query($query)){
       	$alert = "Header Masuk!";
       } else {
       	$alert = "Header Gagal Masuk";
       }
        //detail
        $arrdetail = $this->post('arrdetail');
        $detail = $arrdetail['detail'];

        $REQ_DTL_ID = 2;
        $REQ_HDR_ID = 3;
        $REQ_DTL_DANGER = "A";
        $REQ_DTL_VESSEL_NAME = "AAA";
        $REQ_DTL_VESSEL_CODE = "AAAA";
        $REQ_DTL_CALL_SIGN = "0100101";
        $REQ_DTL_DEST_REPO = "0103a";
        $REQ_DTL_STATUS = "12345";
        $REQ_DTL_CONT = $detail['REQ_DTL_CONT'];
        $REQ_DTL_CONT_STATUS = $detail['REQ_DTL_CONT_STATUS'];
        $REQ_DTL_COMMODITY = $detail['REQ_DTL_COMMODITY'];
        $REQ_DTL_VIA = $detail['REQ_DTL_VIA'];
        $REQ_DTL_SIZE = $detail['REQ_DTL_SIZE'];
        $REQ_DTL_TYPE = $detail['REQ_DTL_TYPE'];
        $REQ_DTL_CONT_HAZARD = $detail['REQ_DTL_CONT_HAZARD'];
        $REQ_DTL_OWNER_CODE = $detail['REQ_DTL_OWNER_CODE'];
        $REQ_DTL_OWNER_NAME = $detail['REQ_DTL_OWNER_NAME'];

        //$queryd = "INSERT INTO TX_REQ_RECEIVING_DTL (REQUEST_DTL_ID,REQUEST_HDR_ID) VALUES ('".$REQ_DTL_ID."','".$REQ_HDR_ID."')";
        $queryd = "INSERT INTO TX_REQ_RECEIVING_DTL (REQUEST_DTL_ID, REQUEST_HDR_ID, REQUEST_DTL_CONT, REQUEST_DTL_CONT_SIZE, REQUEST_DTL_CONT_TYPE,REQUEST_DTL_COMMODITY,REQUEST_DTL_CONT_STATUS,REQUEST_DTL_DANGER,REQUEST_DTL_VOY,REQUEST_DTL_VESSEL_NAME,REQUEST_DTL__VESSEL_CODE,REQUEST_DTL_CALL_SIGN,REQUEST_DTL_DEST_DEPO,REQUEST_DTL_STATUS,REQUEST_DTL_OWNER_CODE,REQUEST_DTL_OWNER_NAME) VALUES (".$REQ_DTL_ID.",".$REQ_HDR_ID.",'".$REQ_DTL_CONT."','".$REQ_DTL_SIZE."','".$REQ_DTL_TYPE."','".$REQ_DTL_COMMODITY."','".$REQ_DTL_CONT_STATUS."','".$REQ_DTL_DANGER."','".$REQ_DTL_VIA."','".$REQ_DTL_VESSEL_NAME."','".$REQ_DTL_VESSEL_CODE."','".$REQ_DTL_CALL_SIGN."','".$REQ_DTL_DEST_REPO."','".$REQ_DTL_STATUS."','".$REQ_DTL_OWNER_CODE."','".$REQ_DTL_OWNER_NAME."')";



       if($this->reponpks->query($queryd)){
       	$alertd = "Detail Masuk!";
       } else{
       	$alertd = "Detail Gagal Masuk!";
       }
       $this->response([$alertd,$alert],200);
    }
}
?>
