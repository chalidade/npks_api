<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store extends BD_Controller {

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
      header('Content-Type: application/json');
      $this->auth_basic();
      $url        = explode('encode=',$_SERVER['REQUEST_URI']);

      if (!isset($url[1])) {
        $encode   = "false";
        $request  = $this->post();
        $input    = json_decode(json_encode($this->post()), TRUE);
      } else {
        $encode   = "true";
        $input    = json_decode(json_encode($this->post()), TRUE);
        $input    =  json_decode(base64_decode($input['request']),TRUE);
      }

      // echo json_encode($input);

      $branch     = $input["header"]["BRANCH_ID"];
      $action     = $input["action"]."_post";
      $this->$action($input, $branch, $encode);
    }

    function getUpateRename_post($input, $branch, $encode) {
      $this->auth_basic();
      //header
      $devdb               = $this->db;
      $repodb              = $this->reponpks;
      $header              = $input['header'];
      $branch              = $header["BRANCH_ID"];
      $CONT_NO             = $header['CONT_NO'];

      $update              = $repodb->set("RENAMED_STATUS", "1")
                                    ->where('RENAMED_CONT_OLD', $CONT_NO)
                                    ->where('RENAMED_BRANCH_ID', $branch)
                                    ->update('TH_RENAMED');

      // History Container
      // $devdb->query("CALL INSERT_HISTORY_CONTAINER(
      //         '" . $REQ_DTL_CONT . "',
      //         '" . $REQ_NO . "',
      //         '" . $REQ_DELIVERY_DATE . "',
      //         '" . $REQ_DTL_SIZE . "',
      //         '" . $REQ_DTL_TYPE . "',
      //         '" . $REQ_DTL_CONT_STATUS . "',
      //         NULL,
      //         NULL,
      //         NULL,
      //         NULL,
      //         NULL,
      //         4,
      //         'Request Delivery',
      //         NULL,
      //         NULL,
      //         " . $branch . ",
      //         NULL,
      //         NULL)");

      $result   = [
        "BRANCH_ID" => $branch,
        "CONT_NO"   => $CONT_NO
      ];

      // JSON Response
      header('Content-Type: application/json');
      if ($encode == "true") {
        $out["result"] = base64_encode(json_encode($result));
        echo json_encode($out);
      } else {
        echo json_encode($result);
      }

    }

    function getDelivery_post($input, $branch, $encode) {
      $this->auth_basic();
      //header
      $devdb               = $this->db;
      $repodb              = $this->reponpks;
      $header              = $input['header'];
      $branch              = $header["BRANCH_ID"];
      $REQ_NO              = $header['REQ_NO'];
      $REQ_DELIVERY_DATE   = $header['REQ_DELIVERY_DATE'];
      $NO_NOTA             = $header['NO_NOTA'];
      $TGL_NOTA            = $header['TGL_NOTA'];
      $NM_CONSIGNEE        = $header['NM_CONSIGNEE'];
      $ALAMAT              = $header['ALAMAT'];
      $REQ_MARK            = $header['REQ_MARK'];
      $PAYMENT             = $header['PAYMENT_METHOD'];
      $NPWP                = str_replace(".", "", str_replace("-", "", trim($header['NPWP'])));
      $DELIVERY_KE         = $header['DELIVERY_KE'];
      $TANGGAL_LUNAS       = $header['TANGGAL_LUNAS'];
      $PERP_DARI           = $header['PERP_DARI'];
      $PERP_KE             = $header['PERP_KE'];


      $sqlcek           = $repodb->where('REQ_BRANCH_ID', $branch)->where("REQ_NO", $REQ_NO)->get('TX_REQ_DELIVERY_HDR');
      $resultCek        = $sqlcek->result_array();

      $sqlceknpwp       = $devdb->where("CONSIGNEE_NPWP", $NPWP)->select("CONSIGNEE_ID")->get('TM_CONSIGNEE');
      $resultCeknpwp    = $sqlceknpwp->result_array();

      if (empty($resultCeknpwp)) {
        // If NPWP empty Create New Consigne
        $qlIDCONSIGNEE      = $devdb->select("SEQ_CONSIGNEE_ID.NEXTVAL AS ID")->get('DUAL');
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
        $devdb->query($insertConsignee);
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
        $resultID = $devdb->query($qlID)->result_array();
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
            REQUEST_TO,
            REQUEST_STATUS,
            REQUEST_EXTEND_FROM,
            REQUEST_EXTEND_LOOP,
            REQUEST_PAYMENT_METHOD,
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
            '1',
            '" . $PERP_DARI . "',
            " . $PERP_KE . ",
            " . $PAYMENT . ",
            'Y'
          )";

        $insertHDR = $repodb->query($query);
        $result["SUCCESS"]      = "true";
        $result["MSG"]          = " Success";
        $result["REQ_NO"]       = $input["header"]["REQ_NO"];
        $result["NO_NOTA"]      = $input["header"]["NO_NOTA"];
        $result["NM_CONSIGNEE"] = $input["header"]["NM_CONSIGNEE"];
      } else {
        $result["SUCCESS"]      = "false";
        $result["MSG"]          = " Already Exist";
        $result["REQ_NO"]       = $input["header"]["REQ_NO"];
        $result["NO_NOTA"]      = $input["header"]["NO_NOTA"];
        $result["NM_CONSIGNEE"] = $input["header"]["NM_CONSIGNEE"];
        $result["header"] = "Header Exist REQUEST_NO = " . $REQ_NO . " <br>\n";
        $insertHDR = true;
        $IDheader = $resultCek[0]['REQ_ID'];
      }


      //detail
      $detail = $input['arrdetail'];
      if ($insertHDR) {
        foreach ($detail as $val) {

          $sqlcek = $repodb->where('REQ_DTL_CONT', $val['REQ_DTL_CONT'])->where('REQ_HDR_ID', $IDheader)->get('TX_REQ_DELIVERY_DTL');
          $resultcekdtl = $sqlcek->result_array();

          if (empty($resultcekdtl)) {
            $sqlIDTL = "SELECT SEQ_REQ_DELIVERY_DTL.NEXTVAL AS ID FROM DUAL";
            $resultIDTL = $devdb->query($sqlIDTL)->result_array();
            $IDdetail = $resultIDTL[0]['ID'];

            $REQ_DTL_CONT               = $val['REQ_DTL_CONT'];
            $REQ_DTL_CONT_STATUS        = $val['REQ_DTL_CONT_STATUS'];
            $REQ_DTL_COMMODITY          = $val['REQ_DTL_COMMODITY'];
            $REQ_DTL_VIA_ID             = $val['REQ_DTL_VIA_ID'];
            $REQ_DTL_VIA_NAME           = $val['REQ_DTL_VIA_NAME'];
            $REQ_DTL_TYPE               = $val['REQ_DTL_TYPE'];
            $REQ_DTL_SIZE               = $val['REQ_DTL_SIZE'];
            $REQ_DTL_DEL_DATE           = $val['REQ_DTL_DEL_DATE'];
            $REQ_DTL_CONT_HAZARD        = $val['REQ_DTL_CONT_HAZARD'];
            $REQ_DTL_NO_SEAL            = $val['REQ_DTL_NO_SEAL'];

            if (isset($val["REQ_DTL_TL"])) {
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
                      REQ_DTL_NO_SEAL,
                      REQ_DTL_VIA_ID,
                      REQ_DTL_VIA,
                      REQ_DTL_TL
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
                      '" . $REQ_DTL_NO_SEAL . "',
                      '" . $REQ_DTL_VIA_ID . "',
                      '" . $REQ_DTL_VIA_NAME . "',
                      '" . $val["REQ_DTL_TL"] . "'

                    )";
            } else {
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
                        REQ_DTL_NO_SEAL,
                        REQ_DTL_VIA_ID,
                        REQ_DTL_VIA
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
                        '" . $REQ_DTL_NO_SEAL . "',
                        '" . $REQ_DTL_VIA_ID . "',
                        '" . $REQ_DTL_VIA_NAME . "'
                      )";
            }
            $resultDtl = $repodb->query($queryDTL);
            if ($resultDtl)
            $result["DETAIL"][] = [
              "REQ_DTL_CONT"               => $val['REQ_DTL_CONT'],
              "REQ_DTL_CONT_STATUS"        => $val['REQ_DTL_CONT_STATUS'],
              "REQ_DTL_COMMODITY"          => $val['REQ_DTL_COMMODITY'],
              "REQ_DTL_VIA"                => $val['REQ_DTL_VIA'],
              "REQ_DTL_TYPE"               => $val['REQ_DTL_TYPE'],
              "REQ_DTL_SIZE"               => $val['REQ_DTL_SIZE'],
              "REQ_DTL_DEL_DATE"           => $val['REQ_DTL_DEL_DATE'],
              "REQ_DTL_CONT_HAZARD"        => $val['REQ_DTL_CONT_HAZARD'],
              "REQ_DTL_NO_SEAL"            => $val['REQ_DTL_NO_SEAL']
            ];

            if ($PERP_DARI != "") {
              // Ready For Test Ext Delivery
              echo $a . ", perpanjangan dari  | " . $PERP_DARI . "~" . $REQ_DTL_CONT . " " . date('Y-m-d H:i:s') . "<br>\n";
              $queryhdr             = $repodb->where("REQ_NO", $PERP_DARI)->where('REQ_BRANCH_ID', $branch)->get('TX_REQ_DELIVERY_HDR');
              $hdrData              = $queryhdr->result_array();
              $hdrId                = $hdrData[0]["REQ_ID"];
              $update               = $repodb->set("REQ_DTL_ACTIVE", "T")
                                             ->set("REQ_DTL_STATUS", "2")
                                             ->where('REQ_DTL_CONT', $REQ_DTL_CONT)
                                             ->where('REQ_HDR_ID', $hdrId)
                                             ->where('REQ_BRANCH_ID', $branch)
                                             ->update('TX_REQ_DELIVERY_DTL');
              // Total Detail
              $listDtl             = $repodb->where('REQ_HDR_ID', $hdrId)->where('REQ_BRANCH_ID', $branch)->get('TX_REQ_DELIVERY_DTL');
              $listDtl             = $listDtl->result_array();
              $countDtl            = count($listDtl);

              // Total Detail Tidak Aktif
              $listDtlT             = $repodb->set("REQ_DTL_ACTIVE", "T")->where('REQ_HDR_ID', $hdrId)->where('REQ_BRANCH_ID', $branch)->get('TX_REQ_DELIVERY_DTL');
              $listDtlT             = $listDtlT->result_array();
              $countDtlT            = count($listDtlT);


              if ($countDtl == $countDtlT) {
                $updateDelHdr       = $repodb->set("REQUEST_STATUS", "2")->where("REQ_NO", $PERP_DARI)->where('REQ_BRANCH_ID', $branch)->update('TX_REQ_DELIVERY_HDR');
              }
            }

            $devdb->query("CALL INSERT_HISTORY_CONTAINER(
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
                    '".$val['REQ_DTL_CONT_HAZARD']."',
                    NULL)
                    ");
          } else {
            $result["DETAIL"][] = [
              "REQ_DTL_CONT"               => $val['REQ_DTL_CONT'],
              "REQ_DTL_CONT_STATUS"        => $val['REQ_DTL_CONT_STATUS'],
              "REQ_DTL_COMMODITY"          => $val['REQ_DTL_COMMODITY'],
              "REQ_DTL_VIA"                => $val['REQ_DTL_VIA'],
              "REQ_DTL_TYPE"               => $val['REQ_DTL_TYPE'],
              "REQ_DTL_SIZE"               => $val['REQ_DTL_SIZE'],
              "REQ_DTL_DEL_DATE"           => $val['REQ_DTL_DEL_DATE'],
              "REQ_DTL_CONT_HAZARD"        => $val['REQ_DTL_CONT_HAZARD'],
              "REQ_DTL_NO_SEAL"            => $val['REQ_DTL_NO_SEAL']
            ];
          }
        }
      }

      // JSON Response
      header('Content-Type: application/json');
      if ($encode == "true") {
        $out["result"] = base64_encode(json_encode($result));
        echo json_encode($out);
      } else {
        echo json_encode($result);
      }
    }

    function getRecStuffing_post($input, $branch, $encode){
      $this->auth_basic();
      $this->getReceiving_post($input, $branch, $encode);
      $this->getStuffing_post($input, $branch, $encode);
      // buatkan funct returnnya
    }

    function getRecStripping_post($input, $branch, $encode){
      $this->auth_basic();
      $this->getReceiving_post($input, $branch, $encode);
      $this->getStripping_post($input, $branch, $encode);
      // buatkan funct returnnya
    }

    // bELUM
    function getTL_post($input, $branch, $encode) {
        $arrdetilRec = '';
        $arrdetilDel = '';
        $detail   = $input["arrdetail"];
        $header   = $input["header"];

        // Receiving
        foreach ($input["arrdetail"] as $dtlRec) {
          $dtlRec = (array)$dtlRec;
          $arrdetilRec .= '{
              "REQ_DTL_CONT": "'.$dtlRec["TL_DTL_CONT"].'",
              "REQ_DTL_VESSEL_NAME" : "'.$header["TL_VESSEL_NAME"].'",
              "REQ_DTL_VESSEL_CODE" : "'.$header["TL_VESSEL_CODE"].'",
              "REQ_DTL_VOYIN" : "'.$header["TL_VOYIN"].'",
              "REQ_DTL_VOYOUT" : "'.$header["TL_VOYOUT"].'",
              "REQ_DTL_VIA_ID" : "'.$dtlRec["TL_DTL_REC_VIA"].'",
              "REQ_DTL_CONT_STATUS": "'.$dtlRec["TL_DTL_CONT_TYPE"].'",
              "REQ_DTL_COMMODITY": "'.$dtlRec["TL_DTL_CMDTY_NAME"].'",
              "REQ_DTL_VIA_NAME": "'.$dtlRec["TL_DTL_VIA_REC_NAME"].'",
              "REQ_DTL_VIA_ID": "'.$dtlRec["TL_DTL_REC_VIA"].'",
              "REQ_DTL_SIZE": "'.$dtlRec["TL_DTL_CONT_SIZE"].'",
              "REQ_DTL_TYPE": "'.$dtlRec["TL_DTL_CONT_TYPE"].'",
              "REQ_DTL_CONT_HAZARD": "'.$dtlRec["TL_DTL_CONT_DANGER"].'",
              "REQ_DTL_OWNER_CODE": "'.$dtlRec["TL_DTL_OWNER"].'",
              "REQ_DTL_OWNER_NAME": "'.$dtlRec["TL_DTL_OWNER_NAME"].'",
              "REQ_DTL_TL": "Y"
          },';
        }

        $arrdetilRec = substr($arrdetilRec, 0,-1);

        $jsonReceiving     = '
        {
           "action" : "getReceiving",
           "header": {
              "REQ_NO": "'.$header["TL_NO"].'",
              "TL" : "true",
              "REQ_RECEIVING_DATE": "'.$header["TL_CREATE_DATE"].'",
              "NO_NOTA": "'.$header["TL_NOTA"].'",
              "TGL_NOTA": "'.$header["TL_DATE"].'",
              "NM_CONSIGNEE": "'.$header["TL_CUST_NAME"].'",
              "ALAMAT": "'.$header["TL_CUST_ADDRESS"].'",
              "REQ_MARK": "'.$header["TL_MSG"].'",
              "NPWP": "'.$header["TL_CUST_NPWP"].'",
              "RECEIVING_DARI": "'.$header["TL_FROM"].'",
              "TANGGAL_LUNAS": "'.$header["TL_CORRECTION_DATE"].'",
              "DI": "'.$header["TL_NO"].'",
              "PAYMENT_METHOD": "'.$header["TL_PAYMETHOD"].'",
              "BRANCH_ID": "'.$header["BRANCH_ID"].'"
           },
           "arrdetail": ['.$arrdetilRec.']
            }';

       $inputReceiving = json_decode($jsonReceiving, TRUE);
       // End Receiving

       // Delivery
       foreach ($input["arrdetail"] as $dtlDel) {
         $dtlDel = (array)$dtlDel;
         $arrdetilDel .= '
         {
              "REQ_DTL_CONT": "'.$dtlDel["TL_DTL_CONT"].'",
              "REQ_DTL_CONT_STATUS": "'.$dtlDel["TL_DTL_CONT_TYPE"].'",
              "REQ_DTL_VESSEL_NAME" : "'.$header["TL_VESSEL_NAME"].'",
              "REQ_DTL_VESSEL_CODE" : "'.$header["TL_VESSEL_CODE"].'",
              "REQ_DTL_VOYIN" : "'.$header["TL_VOYIN"].'",
              "REQ_DTL_VOYOUT" : "'.$header["TL_VOYOUT"].'",
              "REQ_DTL_VIA_ID" : "'.$dtlDel["TL_DTL_DEL_VIA"].'",
              "REQ_DTL_COMMODITY": "'.$dtlDel["TL_DTL_CMDTY_NAME"].'",
              "REQ_DTL_VIA_NAME": "'.$dtlDel["TL_DTL_DEL_VIA_NAME"].'",
              "REQ_DTL_VIA": "'.$dtlDel["TL_DTL_DEL_VIA"].'",
              "REQ_DTL_SIZE": "'.$dtlDel["TL_DTL_CONT_SIZE"].'",
              "REQ_DTL_TYPE": "'.$dtlDel["TL_DTL_CONT_TYPE"].'",
              "REQ_DTL_DEL_DATE": "'.$dtlDel["TL_DTL_DEL_DATE"].'",
              "REQ_DTL_CONT_HAZARD": "'.$dtlDel["TL_DTL_CONT_DANGER"].'",
              "REQ_DTL_NO_SEAL": "",
              "REQ_DTL_TL": "Y"
          },';
       }

       $arrdetilDel = substr($arrdetilDel, 0,-1);

       $jsonDelivery     = '
       {
          "action" : "getDelivery",
          "header":
          {
          "REQ_NO": "'.$header["TL_NO"].'",
          "TL" : "true",
          "REQ_DELIVERY_DATE": "'.$header["TL_CREATE_DATE"].'",
          "NO_NOTA": "'.$header["TL_NOTA"].'",
          "TGL_NOTA": "'.$header["TL_DATE"].'",
          "NM_CONSIGNEE": "'.$header["TL_CUST_NAME"].'",
          "ALAMAT": "'.$header["TL_CUST_ADDRESS"].'",
          "REQ_MARK": "'.$header["TL_MSG"].'",
          "NPWP": "'.$header["TL_CUST_NPWP"].'",
          "DELIVERY_KE": "'.$header["TL_TO"].'",
          "TANGGAL_LUNAS": "'.$header["TL_CORRECTION_DATE"].'",
          "PERP_DARI": "",
          "PERP_KE": "",
          "PAYMENT_METHOD": "'.$header["TL_PAYMETHOD"].'",
          "BRANCH_ID": "'.$header["BRANCH_ID"].'"
          },
          "arrdetail": ['.$arrdetilDel.']
           }';

      $inputDelivery = json_decode($jsonDelivery, TRUE);
      // End Delivery

      $this->auth_basic();
      $realRec = $this->getReceiving_post($inputReceiving, $branch, $encode);
      $realDel = $this->getDelivery_post($inputDelivery, $branch, $encode);

      $result["realRec"] = $realRec;
      $result["realDel"] = $realDel;

      // JSON Response
      header('Content-Type: application/json');
      if ($encode == "true") {
        $out["result"] = base64_encode(json_encode($result));
        echo json_encode($out);
      } else {
        echo json_encode($result);
      }
    }

    function getStuffing_post($input, $branch, $encode) {
      $this->auth_basic();
      $devdb                            = $this->db;
      $repodb                           = $this->reponpks;
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
      $PAYMENT                          = $header['PAYMENT_METHOD'];
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
                                            STUFF_PAYMENT_METHOD,
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
                                            '".$PAYMENT."',
                                            '1'
                                            )
                                          ";
          $resultHDR                      = $repodb->query($insertHDR);
          $result["SUCCESS"]              = "true";
          $result["MSG"]                  = " Success";
          $result["REQ_NO"]               = $input["header"]["REQ_NO"];
          $result["NO_NOTA"]              = $input["header"]["NO_NOTA"];
          $result["NM_CONSIGNEE"]         = $input["header"]["NM_CONSIGNEE"];
        } else {
          $result["SUCCESS"]              = "false";
          $result["MSG"]                  = " Already Exist";
          $result["REQ_NO"]               = $input["header"]["REQ_NO"];
          $result["NO_NOTA"]              = $input["header"]["NO_NOTA"];
          $result["NM_CONSIGNEE"]         = $input["header"]["NM_CONSIGNEE"];
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
            $STUFF_DTL_VIA                = trim($detailroot['REQ_DTL_VIA']);
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

              $sqlIDTL = "SELECT SEQ_REQ_STUFF_DTL.NEXTVAL AS ID FROM DUAL";
              $resultIDTL = $this->db->query($sqlIDTL)->result_array();
              $IDdetail = $resultIDTL[0]['ID'];

              $insertDTL                  = "
                                            INSERT INTO TX_REQ_STUFF_DTL
                                            (
                                              STUFF_DTL_ID,
                                              STUFF_DTL_HDR_ID,
                                              STUFF_DTL_CONT,
                                              STUFF_DTL_CONT_HAZARD,
                                              STUFF_DTL_CONT_SIZE,
                                              STUFF_DTL_CONT_TYPE,
                                              STUFF_DTL_COMMODITY,
                                              STUFF_DTL_REMARK_SP2,
                                              STUFF_DTL_ORIGIN,STUFF_DTL_START_STUFF_PLAN,STUFF_DTL_END_STUFF_PLAN,
                                              STUFF_DTL_CONT_STATUS,
                                              STUFF_DTL_COUNTER,
                                              STUFF_DTL_VIA
                                            )
                                            VALUES
                                            (
                                              ".$IDdetail.",
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
                                               'MTY', ".$cont_count.",
                                               '".$STUFF_DTL_VIA."'
                                            )";

              $resultDtl                   = $repodb->query($insertDTL);

              if($resultDtl)
                $result["DETAIL"][] = [
                  "REQ_DTL_CONT"               => $REQ_DTL_CONT,
                  "REQ_DTL_COMMODITY"          => $REQ_DTL_COMMODITY,
                  "REQ_DTL_CONT_HAZARD"        => $REQ_DTL_CONT_HAZARD,
                  "REQ_DTL_SIZE"               => $REQ_DTL_SIZE,
                  "REQ_DTL_TYPE"               => $REQ_DTL_TYPE,
                  "REQ_DTL_REMARK_SP2"         => $REQ_DTL_REMARK_SP2,
                  "REQ_DTL_ORIGIN"             => $REQ_DTL_ORIGIN,
                  "STUFF_DTL_START_STUFF_PLAN" => $STUFF_DTL_START_STUFF_PLAN,
                  "STUFF_DTL_END_STUFF_PLAN"   => $STUFF_DTL_END_STUFF_PLAN,
                  "STUFF_DTL_VIA"              => $STUFF_DTL_VIA
                ];

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
                $repodb->query($updateStuffTDL);

                $cek_tot_dtl            = $repodb->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch.")")->row()->JML;
                $cek_tot_dtl_T          = $repodb->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = (SELECT STUFF_ID FROM TX_REQ_STUFF_HDR WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch.") AND STUFF_DTL_ACTIVE = 'T' ")->row()->JML;

                if($cek_tot_dtl == $cek_tot_dtl_T){
                  $updateStuffHDR       = "UPDATE TX_REQ_STUFF_HDR SET STUFF_STATUS = '2' WHERE STUFF_NO = '".$PERP_DARI."' AND STUFF_BRANCH_ID = ".$branch." ";
                  $repodb->query($updateStuffHDR);
                }
              }

              //insert history container
              $devdb->query("
                    CALL INSERT_HISTORY_CONTAINER(
                    '".$REQ_DTL_CONT."',
                    '".$REQ_NO."',
                    '".$REQ_STUFF_DATE."',
                    '".$REQ_DTL_SIZE."',
                    '".$REQ_DTL_TYPE."',
                    'MTY',
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
                    '".$REQ_DTL_CONT_HAZARD."',
                    NULL)
                    ");
            } else {
              $result["DETAIL"][] = [
                "REQ_DTL_CONT"               => $REQ_DTL_CONT,
                "REQ_DTL_COMMODITY"          => $REQ_DTL_COMMODITY,
                "REQ_DTL_CONT_HAZARD"        => $REQ_DTL_CONT_HAZARD,
                "REQ_DTL_SIZE"               => $REQ_DTL_SIZE,
                "REQ_DTL_TYPE"               => $REQ_DTL_TYPE,
                "REQ_DTL_REMARK_SP2"         => $REQ_DTL_REMARK_SP2,
                "REQ_DTL_ORIGIN"             => $REQ_DTL_ORIGIN,
                "STUFF_DTL_START_STUFF_PLAN" => $STUFF_DTL_START_STUFF_PLAN,
                "STUFF_DTL_END_STUFF_PLAN"   => $STUFF_DTL_END_STUFF_PLAN
              ];
            }
            $a++;
        }
      }

      // JSON Response
      header('Content-Type: application/json');
      if ($encode == "true") {
        $out["result"] = base64_encode(json_encode($result));
        echo json_encode($out);
      } else {
        echo json_encode($result);
      }
    }

    function getStripping_post($input, $branch, $encode) {
      // Initialization
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $header                       = $input["header"];
      $detail                       = $input["arrdetail"];

      // Get Header Data
      $REQ_NO                       = $header['REQ_NO'];
      $NO_NOTA                      = $header['NO_NOTA'];
      $TGL_NOTA                     = $header['TGL_NOTA'];
      $REQ_STRIP_DATE               = $header['REQ_STRIP_DATE'];
      $NM_CONSIGNEE                 = $header['NM_CONSIGNEE'];
      $PAYMENT                      = $header['PAYMENT_METHOD'];
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
                                        STRIP_PAYMENT_METHOD,
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
                                        ".$PAYMENT.",
                                        '".$STRIP_DARI."'
                                      )";

          $resultHDR                  = $repodb->query($insertHDR);
          $result["SUCCESS"]          = "true";
          $result["MSG"]              = "Success";
          $result["REQ_NO"]           = $REQ_NO;
          $result["NO_NOTA"]          = $NO_NOTA;
          $result["NM_CONSIGNEE"]     = $CONSIGNE_ID;
        } else {
          $result["SUCCESS"]          = "false";
          $result["MSG"]              = "Already Exist";
          $result["REQ_NO"]           = $REQ_NO;
          $result["NO_NOTA"]          = $NO_NOTA;
          $result["NM_CONSIGNEE"]     = $CONSIGNE_ID;
          $resultHDR                  = true;
          $IDheader                   = $resultCek[0]['STRIP_ID'];
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

              $sqlIDTL = "SELECT SEQ_TX_REQ_STRIP_DTL.NEXTVAL AS ID FROM DUAL";
              $resultIDTL = $this->db->query($sqlIDTL)->result_array();
              $IDdetail = $resultIDTL[0]['ID'];

              $insertDTL               = "
                                         INSERT INTO TX_REQ_STRIP_DTL
                                         (
                                           STRIP_DTL_ID,
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
                                           ".$IDdetail.",
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
                $result["DETAIL"][] = [
                                      "REQ_DTL_CONT"                => $REQ_DTL_CONT,
                                      "REQ_DTL_COMMODITY"           => $REQ_DTL_COMMODITY,
                                      "STRIP_DTL_ORIGIN"            => $STRIP_DTL_ORIGIN,
                                      "REQ_DTL_CONT_HAZARD"         => $REQ_DTL_CONT_HAZARD,
                                      "REQ_DTL_SIZE"                => $REQ_DTL_SIZE,
                                      "REQ_DTL_TYPE"                => $REQ_DTL_TYPE,
                                      "STRIP_DTL_START_STRIP_PLAN"  => $STRIP_DTL_START_STRIP_PLAN,
                                      "STRIP_DTL_END_STRIP_PLAN"    => $STRIP_DTL_END_STRIP_PLAN

                                      ];
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

                $repodb->query($updateStripTDL);

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
                    CALL INSERT_HISTORY_CONTAINER
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
                      '".$REQ_DTL_CONT_HAZARD."',
                      NULL)");
            } else {
              $result["DETAIL"][] = [
                                    "REQ_DTL_CONT"                => $REQ_DTL_CONT,
                                    "REQ_DTL_COMMODITY"           => $REQ_DTL_COMMODITY,
                                    "STRIP_DTL_ORIGIN"            => $STRIP_DTL_ORIGIN,
                                    "REQ_DTL_CONT_HAZARD"         => $REQ_DTL_CONT_HAZARD,
                                    "REQ_DTL_SIZE"                => $REQ_DTL_SIZE,
                                    "REQ_DTL_TYPE"                => $REQ_DTL_TYPE,
                                    "STRIP_DTL_START_STRIP_PLAN"  => $STRIP_DTL_START_STRIP_PLAN,
                                    "STRIP_DTL_END_STRIP_PLAN"    => $STRIP_DTL_END_STRIP_PLAN

                                    ];
            }
            $a++;
        }
      }

      // JSON Response
      header('Content-Type: application/json');
      if ($encode == "true") {
        $out["result"] = base64_encode(json_encode($result));
        echo json_encode($out);
      } else {
        echo json_encode($result);
      }
    }

    function getPlugging_post($input, $branch,$encode) {
      $db                         = $this->db;
      $repodb                     = $this->reponpks;
      $header                     = $input["header"];
      $detail                     = $input["arrdetail"];

      $query                      = $repodb->where("PLUG_NO", $header["PLUG_NO"])->get('TX_REQ_PLUG_HDR');
      $resultQuery                = $query->result_array();


      if (!empty($resultQuery)) {
        $result["SUCCESS"]        = "false";
        $result["MSG"]            = "Already Exist";
        $result["REQ_NO"]         = $header["PLUG_NO"];
        $result["NM_CONSIGNEE"]   = $header["PLUG_CONSIGNEE_ID"];
        $result["STATUS"]         = $header["PLUG_STATUS"];

        $queryDTL                 = $repodb->where("PLUG_DTL_HDR_ID", $header["PLUG_ID"])->get('TX_REQ_PLUG_DTL');
        $resultDTLQuery           = $queryDTL->result_array();

        foreach ($resultDTLQuery as $valDtl) {
          $result["DETAIL"][]     = [
                                    "PLUG_DTL_ID"              => $valDtl["PLUG_DTL_ID"],
                                    "PLUG_DTL_HDR_ID"          => $valDtl["PLUG_DTL_HDR_ID"],
                                    "PLUG_DTL_CONT"            => $valDtl["PLUG_DTL_CONT"],
                                    "PLUG_DTL_CONT_SIZE"       => $valDtl["PLUG_DTL_CONT_SIZE"],
                                    "PLUG_DTL_CONT_STATUS"     => $valDtl["PLUG_DTL_CONT_STATUS"],
                                    "PLUG_DTL_STATUS"          => $valDtl["PLUG_DTL_STATUS"],
                                    "PLUG_DTL_CANCELLED"       => $valDtl["PLUG_DTL_CANCELLED"],
                                    "PLUG_DTL_ACTIVE"          => $valDtl["PLUG_DTL_ACTIVE"],
                                    "PLUG_DTL_START_PLUG_PLAN" => $valDtl["PLUG_DTL_START_PLUG_PLAN"],
                                    "PLUG_DTL_END_PLUG_PLAN"   => $valDtl["PLUG_DTL_END_PLUG_PLAN"],
                                    "PLUG_DTL_COMMODITY"       => $valDtl["PLUG_DTL_COMMODITY"],
                                    "PLUG_DTL_COUNTER"         => $valDtl["PLUG_DTL_COUNTER"]
                                    ];
        }


      } else {
        $queryHdrId               = $db->select("SEQ_TX_REQ_PLUG_HDR.NEXTVAL AS ID")->get('DUAL');
        $hdrID                    = $queryHdrId->result_array();
        $hederID                  = $hdrID[0]["ID"];

        $storeHeader              = [
          "PLUG_ID"               => $hederID,
          "PLUG_NO"               => $header["PLUG_NO"],
          "PLUG_BRANCH_ID"        => $header["BRANCH_ID"],
          "PLUG_CONSIGNEE_ID"     => $header["PLUG_CONSIGNEE_ID"],
          "PLUG_CREATE_DATE"      => $header["PLUG_CREATE_DATE"],
          "PLUG_CREATE_BY"        => $header["PLUG_CREATE_BY"],
          "PLUG_STATUS"           => $header["PLUG_STATUS"],
          "PLUG_PAYMENT_METHOD"   => $header["PAYMENT_METHOD"]
        ];

        $head                     = $repodb->set($storeHeader)->get_compiled_insert('TX_REQ_PLUG_HDR');
        $queryHdr                 = $this->reponpks->query($head);

        $result["SUCCESS"]        = "true";
        $result["MSG"]            = "Success";
        $result["REQ_NO"]         = $header["PLUG_NO"];
        $result["NM_CONSIGNEE"]   = $header["PLUG_CONSIGNEE_ID"];
        $result["STATUS"]         = $header["PLUG_STATUS"];

        foreach ($detail as $detail) {
          $queryDtlId             = $db->select("SEQ_TX_REQ_PLUG_DTL.NEXTVAL AS ID")->get('DUAL');
          $dtlID                  = $queryDtlId->result_array();

          // Insert History Container
          $recDate                = date('m/d/Y',strtotime($header["PLUG_CREATE_DATE"]));
          $this->db->query("CALL INSERT_HISTORY_CONTAINER(
                  '" . $detail["PLUG_DTL_CONT"] . "',
                  '" . $header["PLUG_NO"] . "',
                  '" . $recDate . "',
                  '" . $detail["PLUG_DTL_CONT_SIZE"] . "',
                  '',
                  '" . $detail["PLUG_DTL_STATUS"] . "',
                  NULL,
                  NULL,
                  NULL,
                  NULL,
                  NULL,
                  4,
                  'Request Pluggin',
                  NULL,
                  NULL,
                  " . $branch . ",
                  NULL,
                  NULL,
                  NULL)");

          $storeDetail            = [
            "PLUG_DTL_ID"              => $dtlID[0]["ID"],
            "PLUG_DTL_HDR_ID"          => $hederID,
            "PLUG_DTL_CONT"            => $detail["PLUG_DTL_CONT"],
            "PLUG_DTL_CONT_SIZE"       => $detail["PLUG_DTL_CONT_SIZE"],
            "PLUG_DTL_CONT_STATUS"     => $detail["PLUG_DTL_CONT_STATUS"],
            "PLUG_DTL_STATUS"          => $detail["PLUG_DTL_STATUS"],
            "PLUG_DTL_CANCELLED"       => $detail["PLUG_DTL_CANCELLED"],
            "PLUG_DTL_ACTIVE"          => $detail["PLUG_DTL_ACTIVE"],
            "PLUG_DTL_START_PLUG_PLAN" => $detail["PLUG_DTL_START_PLUG_PLAN"],
            "PLUG_DTL_END_PLUG_PLAN"   => $detail["PLUG_DTL_END_PLUG_PLAN"],
            "PLUG_DTL_COMMODITY"       => $detail["PLUG_DTL_COMMODITY"],
            "PLUG_DTL_COUNTER"         => $detail["PLUG_DTL_COUNTER"]
          ];

          $det                    = $db->set($storeDetail)->get_compiled_insert('TX_REQ_PLUG_DTL');
          $queryDtl               = $this->reponpks->query($det);
          $result["DETAIL"][]     = $storeDetail;
          }
        }

        if ($encode == "true") {
          $out["result"] = base64_encode(json_encode($result));
          echo json_encode($out);
        } else {
          echo json_encode($result);
        }
    }

    function getFumigasi_post($input, $branch,$encode) {
      $db                         = $this->db;
      $repodb                     = $this->reponpks;
      $header                     = $input["header"];
      $detail                     = $input["arrdetail"];

      $query                      = $repodb->where("FUMI_NO", $header["FUMI_NO"])->get('TX_REQ_FUMI_HDR');
      $resultQuery                = $query->result_array();

      if (!empty($resultQuery)) {
        $result["SUCCESS"]        = "false";
        $result["MSG"]            = "Already Exist";
        $result["REQ_NO"]         = $header["FUMI_NO"];
        $result["NM_CONSIGNEE"]   = $header["FUMI_CONSIGNEE_ID"];

        $queryDTL                 = $repodb->where("FUMI_DTL_HDR_ID", $resultQuery[0]["FUMI_ID"])->get('TX_REQ_FUMI_DTL');
        $resultDTLQuery           = $queryDTL->result_array();

        foreach ($resultDTLQuery as $valDtl) {
          $result["DETAIL"][]     = [
                                    "FUMI_DTL_ID"              =>$valDtl["FUMI_DTL_ID"],
                                    "FUMI_DTL_HDR_ID"          =>$valDtl["FUMI_DTL_HDR_ID"],
                                    "FUMI_DTL_CONT"            =>$valDtl["FUMI_DTL_CONT"],
                                    "FUMI_DTL_CONT_SIZE"       =>$valDtl["FUMI_DTL_CONT_SIZE"],
                                    "FUMI_DTL_CONT_STATUS"     =>$valDtl["FUMI_DTL_CONT_STATUS"],
                                    "FUMI_DTL_STATUS"          =>$valDtl["FUMI_DTL_STATUS"],
                                    "FUMI_DTL_CANCELLED"       =>$valDtl["FUMI_DTL_CANCELLED"],
                                    "FUMI_DTL_ACTIVE"          =>$valDtl["FUMI_DTL_ACTIVE"],
                                    "FUMI_DTL_START_FUMI_PLAN" =>$valDtl["FUMI_DTL_START_FUMI_PLAN"],
                                    "FUMI_DTL_END_FUMI_PLAN"   =>$valDtl["FUMI_DTL_END_FUMI_PLAN"],
                                    "FUMI_DTL_COMMODITY"       =>$valDtl["FUMI_DTL_COMMODITY"],
                                    "FUMI_DTL_COUNTER"         =>$valDtl["FUMI_DTL_COUNTER"]
                                    ];
        }

      } else {

        $queryHdrId               = $db->select("SEQ_REQ_FUMI_HDR.NEXTVAL AS ID")->get('DUAL');
        $hdrID                    = $queryHdrId->result_array();
        $hederID                  = $hdrID[0]["ID"];
        $storeHeader              = [
          "FUMI_ID"               => $hederID,
          "FUMI_NO"               => $header["FUMI_NO"],
          "FUMI_BRANCH_ID"        => $header["BRANCH_ID"],
          "FUMI_CONSIGNEE_ID"     => $header["FUMI_CONSIGNEE_ID"],
          "FUMI_CREATE_DATE"      => $header["FUMI_CREATE_DATE"],
          "FUMI_CREATE_BY"        => $header["FUMI_CREATE_BY"],
          "FUMI_PAYMENT_METHOD"   => $header["PAYMENT_METHOD"]
        ];

        $head                     = $repodb->set($storeHeader)->get_compiled_insert('TX_REQ_FUMI_HDR');
        $queryHdr                 = $repodb->query($head);

        $result["SUCCESS"]        = "true";
        $result["MSG"]            = "Success";
        $result["REQ_NO"]         = $header["FUMI_NO"];
        $result["NM_CONSIGNEE"]   = $header["FUMI_CONSIGNEE_ID"];

        foreach ($detail as $detail) {
          $queryDtlId             = $db->select("SEQ_REQ_FUMI_DTL.NEXTVAL AS ID")->get('DUAL');
          $dtlID                  = $queryDtlId->result_array();

          // Insert History Container
          $recDate                = date('m/d/Y',strtotime($header["FUMI_CREATE_DATE"]));
          $this->db->query("CALL INSERT_HISTORY_CONTAINER(
                  '" . $detail["FUMI_DTL_CONT"] . "',
                  '" . $header["FUMI_NO"] . "',
                  '" . $recDate . "',
                  '" . $detail["FUMI_DTL_CONT_SIZE"] . "',
                  '',
                  '" . $detail["FUMI_DTL_CONT_STATUS"] . "',
                  NULL,
                  NULL,
                  NULL,
                  NULL,
                  NULL,
                  15,
                  'Request Fumigasi',
                  NULL,
                  NULL,
                  " . $branch . ",
                  NULL,
                  NULL,
                  NULL)");

          $storeDetail            = [
            "FUMI_DTL_ID"              => $dtlID[0]["ID"],
            "FUMI_DTL_HDR_ID"          => $hederID,
            "FUMI_DTL_CONT"            => $detail["FUMI_DTL_CONT"],
            "FUMI_DTL_CONT_SIZE"       => $detail["FUMI_DTL_CONT_SIZE"],
            "FUMI_DTL_CONT_STATUS"     => $detail["FUMI_DTL_CONT_STATUS"],
            "FUMI_DTL_STATUS"          => $detail["FUMI_DTL_STATUS"],
            "FUMI_DTL_CANCELLED"       => $detail["FUMI_DTL_CANCELLED"],
            "FUMI_DTL_ACTIVE"          => $detail["FUMI_DTL_ACTIVE"],
            "FUMI_DTL_START_FUMI_PLAN" => $detail["FUMI_DTL_START_FUMI_PLAN"],
            "FUMI_DTL_END_FUMI_PLAN"   => $detail["FUMI_DTL_END_FUMI_PLAN"],
            "FUMI_DTL_COMMODITY"       => $detail["FUMI_DTL_COMMODITY"],
            "FUMI_DTL_COUNTER"         => $detail["FUMI_DTL_COUNTER"],
            "FUMI_DTL_TYPE"            => $detail["FUMI_DTL_TYPE"]
          ];

          $det                    = $repodb->set($storeDetail)->get_compiled_insert('TX_REQ_FUMI_DTL');
          $queryDtl               = $this->reponpks->query($det);
          $result["DETAIL"][]     = $storeDetail;
          }
      }

      // JSON Response
      if ($encode == "true") {
        $out["result"] = base64_encode(json_encode($result));
        echo json_encode($out);
      } else {
        echo json_encode($result);
      }
    }

    function getReceivingBrg_post($input,$branch, $encode) {
      $db                         = $this->db;
      $repodb                     = $this->reponpks;
      $header                     = $input["header"];
      $detail                     = $input["arrdetail"];

      $query                      = $repodb->where("REQUEST_NO", $header["REQUEST_NO"])->get('TX_REQ_RECEIVING_BRG_HDR');
      $resultQuery                = $query->result_array();

      // echo json_encode($resultQuery[0]["REQUEST_NO"]);

      if (!empty($resultQuery)) {
        $result["SUCCESS"]        = "false";
        $result["MSG"]            = "Already Exist";
        $result["REQ_NO"]         = $resultQuery[0]["REQUEST_NO"];
        $result["NM_CONSIGNEE"]   = $resultQuery[0]["REQUEST_CONSIGNEE_ID"];
        $result["STATUS"]         = $resultQuery[0]["REQUEST_STATUS"];


        $queryDTL                 = $repodb->where("REQUEST_HDR_ID", $resultQuery[0]["REQUEST_ID"])->get('TX_REQ_RECEIVING_BRG_DTL');
        $resultDTLQuery           = $queryDTL->result_array();

        foreach ($resultDTLQuery as $valDtl) {
          $result["DETAIL"][]     = [
                                    "REQUEST_DTL_ID"            => $valDtl["REQUEST_DTL_ID"],
                                    "REQUEST_HDR_ID"            => $valDtl["REQUEST_HDR_ID"],
                                    "REQUEST_DTL_SI"            => $valDtl["REQUEST_DTL_SI"],
                                    "REQUEST_DTL_COMMODITY"     => $valDtl["REQUEST_DTL_COMMODITY"],
                                    "REQUEST_DTL_DANGER"        => $valDtl["REQUEST_DTL_DANGER"],
                                    "REQUEST_DTL_VOY"           => $valDtl["REQUEST_DTL_VOY"],
                                    "REQUEST_DTL_VESSEL_NAME"   => $valDtl["REQUEST_DTL_VESSEL_NAME"],
                                    "REQUEST_DTL__VESSEL_CODE"  => $valDtl["REQUEST_DTL__VESSEL_CODE"],
                                    "REQUEST_DTL_CALL_SIGN"     => $valDtl["REQUEST_DTL_CALL_SIGN"],
                                    "REQUEST_DTL_DEST_DEPO"     => $valDtl["REQUEST_DTL_DEST_DEPO"],
                                    "REQUEST_DTL_STATUS"        => $valDtl["REQUEST_DTL_STATUS"],
                                    "REQUEST_DTL_OWNER_CODE"    => $valDtl["REQUEST_DTL_OWNER_CODE"],
                                    "REQUEST_DTL_OWNER_NAME"    => $valDtl["REQUEST_DTL_OWNER_NAME"],
                                    "REQUEST_DTL_TOTAL"         => $valDtl["REQUEST_DTL_TOTAL"],
                                    "REQUEST_DTL_UNIT"          => $valDtl["REQUEST_DTL_UNIT"]
                                    ];
        }

      } else {
        $queryHdrId               = $db->select("SEQ_REQ_REC_BRG_HDR.NEXTVAL AS ID")->get('DUAL');
        $hdrID                    = $queryHdrId->result_array();
        $hederID                  = $hdrID[0]["ID"];

        $storeHeader              = [
          "REQUEST_ID"            => $hederID,
          "REQUEST_NO"            => $header["REQUEST_NO"],
          "REQUEST_CONSIGNEE_ID"  => $header["REQUEST_CONSIGNEE_ID"],
          "REQUEST_MARK"          => $header["REQUEST_MARK"],
          "REQUEST_CREATE_DATE"   => $header["REQUEST_CREATE_DATE"],
          "REQUEST_CREATE_BY"     => $header["REQUEST_CREATE_BY"],
          "REQUEST_NOTA"          => $header["REQUEST_NOTA"],
          "REQUEST_NO_TPK"        => $header["REQUEST_NO_TPK"],
          "REQUEST_DO_NO"         => $header["REQUEST_DO_NO"],
          "REQUEST_BL_NO"         => $header["REQUEST_BL_NO"],
          "REQUEST_SPPB_NO"       => $header["REQUEST_SPPB_NO"],
          "REQUEST_SPPB_DATE"     => $header["REQUEST_SPPB_DATE"],
          "REQUEST_RECEIVING_DATE"=> $header["REQUEST_RECEIVING_DATE"],
          "REQUEST_NOTA_DATE"     => $header["REQUEST_NOTA_DATE"],
          "REQUEST_PAID_DATE"     => $header["REQUEST_PAID_DATE"],
          "REQUEST_FROM"          => $header["REQUEST_FROM"],
          "REQUEST_STATUS"        => $header["REQUEST_STATUS"],
          "REQUEST_DI"            => $header["REQUEST_DI"],
          "REQUEST_BRANCH_ID"     => $header["BRANCH_ID"],
          "REQUEST_PAYMENT_METHOD"=> $header['PAYMENT_METHOD']
        ];

        $head                     = $repodb->set($storeHeader)->get_compiled_insert('TX_REQ_RECEIVING_BRG_HDR');
        $queryHdr                 = $repodb->query($head);

        $result["SUCCESS"]        = "true";
        $result["MSG"]            = "Success";
        $result["REQ_NO"]         = $header["REQUEST_NO"];
        $result["NM_CONSIGNEE"]   = $header["REQUEST_CONSIGNEE_ID"];
        $result["STATUS"]         = $header["REQUEST_STATUS"];

        foreach ($detail as $detail) {
          $queryDtlId             = $db->select("SEQ_REQ_REC_BRG_DTL.NEXTVAL AS ID")->get('DUAL');
          $dtlID                  = $queryDtlId->result_array();

          $storeDetail            = [
            "REQUEST_DTL_ID"           => $dtlID[0]["ID"],
            "REQUEST_HDR_ID"           => $hederID,
            "REQUEST_DTL_SI"           => $detail["REQUEST_DTL_SI"],
            "REQUEST_DTL_COMMODITY"    => $detail["REQUEST_DTL_COMMODITY"],
            "REQUEST_DTL_DANGER"       => $detail["REQUEST_DTL_DANGER"],
            "REQUEST_DTL_VOY"          => $detail["REQUEST_DTL_VOY"],
            "REQUEST_DTL_VESSEL_NAME"  => $detail["REQUEST_DTL_VESSEL_NAME"],
            "REQUEST_DTL__VESSEL_CODE" => $detail["REQUEST_DTL__VESSEL_CODE"],
            "REQUEST_DTL_CALL_SIGN"    => $detail["REQUEST_DTL_CALL_SIGN"],
            "REQUEST_DTL_DEST_DEPO"    => $detail["REQUEST_DTL_DEST_DEPO"],
            "REQUEST_DTL_STATUS"       => $detail["REQUEST_DTL_STATUS"],
            "REQUEST_DTL_OWNER_CODE"   => $detail["REQUEST_DTL_OWNER_CODE"],
            "REQUEST_DTL_OWNER_NAME"   => $detail["REQUEST_DTL_OWNER_NAME"],
            "REQUEST_DTL_TOTAL"        => $detail["REQUEST_DTL_TOTAL"],
            "REQUEST_DTL_UNIT"         => $detail["REQUEST_DTL_UNIT"]
          ];

          $det                    = $db->set($storeDetail)->get_compiled_insert('TX_REQ_RECEIVING_BRG_DTL');
          $queryDtl               = $this->reponpks->query($det);
          $result["DETAIL"][]     = $storeDetail;

          // REFF_NAME
          $tmReff                 = $repodb->where("REFF_ID", "1")->where('REFF_TR_ID', '5')->get('TM_REFF');
          $reffData               = $tmReff->result_array();
          $reffName               = $reffData[0]["REFF_NAME"];


          // TX_HISTORY_BARANG
          $db->query("CALL ADD_HISTORY_CARGO(
                '".$detail["REQUEST_DTL_SI"]."',
                '".$header["REQUEST_NO"]."',
                '".date('d-M-Y', strtotime($header["REQUEST_RECEIVING_DATE"]))."',
                NULL,
                NULL,
                1,
                '".$reffName."',
                '".$header["REQUEST_CREATE_DATE"]."',
                '".$detail["REQUEST_DTL_TOTAL"]."',
                '".$detail["REQUEST_DTL_TOTAL"]."',
                NULL,
                '".$detail["REQUEST_DTL_VESSEL_NAME"]."',
                ".$branch.",
                NULL)");

          // $storeHistory  = [
          //   "HIST_SI"         => $detail["REQUEST_DTL_SI"],
          //   "HIST_BRANCH_ID"  => $branch,
          //   "HIST_COUNTER"    => "",
          //   "HIST_STORAGE"    => $detail["REQUEST_DTL_TOTAL"],
          //   "HIST_ACTIVITY_ID"=> "1",
          //   "HIST_ACTIVITY"   => $reffName,
          //   "HIST_DATE"       => date('d-M-Y', strtotime($header["REQUEST_CREATE_DATE"])),
          //   "HIST_NOREQ"      => $header["REQ_NO"],
          //   "HIST_DATE_REQ"   => date('d-M-Y', strtotime($header["REQUEST_RECEIVING_DATE"])),
          //   "HIST_TOTAL"      => $detail["REQUEST_DTL_TOTAL"],
          //   "HIST_IN"         => $detail["REQUEST_DTL_TOTAL"],
          //   "HIST_OUT"        => "",
          //   "HIST_USER"       => ""
          // ];
          //
          // $historyBrg               = $devdb->set($storeHistory)->get_compiled_insert('TH_HISTORY_BRG');
          // $queryHistory             = $devdb->query($historyBrg);
        }
      }

      // JSON Response
      if ($encode == "true") {
        $out["result"] = base64_encode(json_encode($result));
        echo json_encode($out);
      } else {
        echo json_encode($result);
      }
    }

    function getDeliveryBrg_post($input,$branch, $encode) {
      $db                         = $this->db;
      $repodb                     = $this->reponpks;
      $header                     = $input["header"];
      $detail                     = $input["arrdetail"];

      $query                      = $repodb->where("REQUEST_NO", $header["REQUEST_NO"])->get('TX_REQ_DELIVERY_BRG_HDR');
      $resultQuery                = $query->result_array();

      // echo json_encode($resultQuery);

      if (!empty($resultQuery)) {
        $result["SUCCESS"]        = "false";
        $result["MSG"]            = "Already Exist";
        $result["REQ_NO"]         = $resultQuery[0]["REQUEST_NO"];
        $result["NM_CONSIGNEE"]   = $resultQuery[0]["REQUEST_CONSIGNEE_ID"];
        $result["STATUS"]         = $resultQuery[0]["REQUEST_STATUS"];

        $queryDTL                 = $repodb->where("REQUEST_HDR_ID", $resultQuery[0]["REQUEST_ID"])->get('TX_REQ_DELIVERY_BRG_DTL');
        $resultDTLQuery           = $queryDTL->result_array();

        foreach ($resultDTLQuery as $valDtl) {
          $result["DETAIL"][]     = [
                                    "REQUEST_DTL_ID"            => $valDtl["REQUEST_DTL_ID"],
                                    "REQUEST_HDR_ID"            => $valDtl["REQUEST_HDR_ID"],
                                    "REQUEST_DTL_SI"            => $valDtl["REQUEST_DTL_SI"],
                                    "REQUEST_DTL_COMMODITY"     => $valDtl["REQUEST_DTL_COMMODITY"],
                                    "REQUEST_DTL_DANGER"        => $valDtl["REQUEST_DTL_DANGER"],
                                    "REQUEST_DTL_VOY"           => $valDtl["REQUEST_DTL_VOY"],
                                    "REQUEST_DTL_VESSEL_NAME"   => $valDtl["REQUEST_DTL_VESSEL_NAME"],
                                    "REQUEST_DTL__VESSEL_CODE"  => $valDtl["REQUEST_DTL__VESSEL_CODE"],
                                    "REQUEST_DTL_CALL_SIGN"     => $valDtl["REQUEST_DTL_CALL_SIGN"],
                                    "REQUEST_DTL_DEST_DEPO"     => $valDtl["REQUEST_DTL_DEST_DEPO"],
                                    "REQUEST_DTL_STATUS"        => $valDtl["REQUEST_DTL_STATUS"],
                                    "REQUEST_DTL_OWNER_CODE"    => $valDtl["REQUEST_DTL_OWNER_CODE"],
                                    "REQUEST_DTL_OWNER_NAME"    => $valDtl["REQUEST_DTL_OWNER_NAME"],
                                    "REQUEST_DTL_TOTAL"         => $valDtl["REQUEST_DTL_TOTAL"],
                                    "REQUEST_DTL_UNIT"          => $valDtl["REQUEST_DTL_UNIT"]
                                    ];
        }


      } else {
        $queryHdrId               = $db->select("SEQ_TX_REQ_DELIVERY_BRG_HDR.NEXTVAL AS ID")->get('DUAL');
        $hdrID                    = $queryHdrId->result_array();
        $hederID                  = $hdrID[0]["ID"];


        $storeHeader              = [
          "REQUEST_ID"            => $hederID,
          "REQUEST_NO"            => $header["REQUEST_NO"],
          "REQUEST_CONSIGNEE_ID"  => $header["REQUEST_CONSIGNEE_ID"],
          "REQUEST_MARK"          => $header["REQUEST_MARK"],
          "REQUEST_CREATE_DATE"   => $header["REQUEST_CREATE_DATE"],
          "REQUEST_CREATE_BY"     => $header["REQUEST_CREATE_BY"],
          "REQUEST_NOTA"          => $header["REQUEST_NOTA"],
          "REQUEST_NO_TPK"        => $header["REQUEST_NO_TPK"],
          "REQUEST_DO_NO"         => $header["REQUEST_DO_NO"],
          "REQUEST_BL_NO"         => $header["REQUEST_BL_NO"],
          "REQUEST_SPPB_NO"       => $header["REQUEST_SPPB_NO"],
          "REQUEST_SPPB_DATE"     => $header["REQUEST_SPPB_DATE"],
          "REQUEST_DATE"          => $header["REQUEST_DATE"],
          "REQUEST_NOTA_DATE"     => $header["REQUEST_NOTA_DATE"],
          "REQUEST_PAID_DATE"     => $header["REQUEST_PAID_DATE"],
          "REQUEST_FROM"          => $header["REQUEST_FROM"],
          "REQUEST_STATUS"        => $header["REQUEST_STATUS"],
          "REQUEST_DI"            => $header["REQUEST_DI"],
          "REQUEST_BRANCH_ID"     => $header["BRANCH_ID"],
          "REQUEST_PAYMENT_METHOD"=> $header['PAYMENT_METHOD']
        ];


        $head                     = $repodb->set($storeHeader)->get_compiled_insert('TX_REQ_DELIVERY_BRG_HDR');
        $queryHdr                 = $repodb->query($head);

        $result["SUCCESS"]        = "true";
        $result["MSG"]            = "Success";
        $result["REQ_NO"]         = $header["REQUEST_NO"];
        $result["NM_CONSIGNEE"]   = $header["REQUEST_CONSIGNEE_ID"];
        $result["STATUS"]         = $header["REQUEST_STATUS"];

        // echo json_encode($result);

        foreach ($detail as $detail) {
          $queryDtlId             = $db->select("SEQ_TX_REQ_DELIVERY_BRG_DTL.NEXTVAL AS ID")->get('DUAL');
          $dtlID                  = $queryDtlId->result_array();

          $storeDetail            = [
            "REQUEST_DTL_ID"           => $dtlID[0]["ID"],
            "REQUEST_HDR_ID"           => $hederID,
            "REQUEST_DTL_SI"           => $detail["REQUEST_DTL_SI"],
            "REQUEST_DTL_COMMODITY"    => $detail["REQUEST_DTL_COMMODITY"],
            "REQUEST_DTL_DANGER"       => $detail["REQUEST_DTL_DANGER"],
            "REQUEST_DTL_VOY"          => $detail["REQUEST_DTL_VOY"],
            "REQUEST_DTL_VESSEL_NAME"  => $detail["REQUEST_DTL_VESSEL_NAME"],
            "REQUEST_DTL__VESSEL_CODE" => $detail["REQUEST_DTL__VESSEL_CODE"],
            "REQUEST_DTL_CALL_SIGN"    => $detail["REQUEST_DTL_CALL_SIGN"],
            "REQUEST_DTL_DEST_DEPO"    => $detail["REQUEST_DTL_DEST_DEPO"],
            "REQUEST_DTL_STATUS"       => $detail["REQUEST_DTL_STATUS"],
            "REQUEST_DTL_OWNER_CODE"   => $detail["REQUEST_DTL_OWNER_CODE"],
            "REQUEST_DTL_OWNER_NAME"   => $detail["REQUEST_DTL_OWNER_NAME"],
            "REQUEST_DTL_TOTAL"        => $detail["REQUEST_DTL_TOTAL"],
            "REQUEST_DTL_UNIT"         => $detail["REQUEST_DTL_UNIT"]
          ];

          $det                    = $db->set($storeDetail)->get_compiled_insert('TX_REQ_DELIVERY_BRG_DTL');
          $queryDtl               = $this->reponpks->query($det);
          $result["DETAIL"][]     = $storeDetail;

          // REFF_NAME
          $tmReff                 = $repodb->where("REFF_ID", "2")->where('REFF_TR_ID', '5')->get('TM_REFF');
          $reffData               = $tmReff->result_array();
          $reffName               = $reffData[0]["REFF_NAME"];


          // TX_HISTORY_BARANG
          $devdb->query("CALL ADD_HISTORY_CARGO(
                '".$detail["REQUEST_DTL_SI"]."',
                '".$header["REQ_NO"]."',
                '".date('d-M-Y', strtotime($header["REQUEST_RECEIVING_DATE"]))."',
                NULL,
                NULL,
                2,
                '".$reffName."',
                '".$gate_back_date."',
                '".$detail["REQUEST_DTL_TOTAL"]."',
                null,
                '".$detail["REQUEST_DTL_TOTAL"]."',
                '".$detail["REQUEST_DTL_VESSEL_NAME"]."',
                ".$branch.",
                NULL)");

          // $storeHistory  = [
          //   "HIST_SI"         => $detail["REQUEST_DTL_SI"],
          //   "HIST_BRANCH_ID"  => $branch,
          //   "HIST_COUNTER"    => "",
          //   "HIST_STORAGE"    => $detail["REQUEST_DTL_TOTAL"],
          //   "HIST_ACTIVITY_ID"=> "2",
          //   "HIST_ACTIVITY"   => $reffName,
          //   "HIST_DATE"       => date('d-M-Y', strtotime($header["REQUEST_CREATE_DATE"])),
          //   "HIST_NOREQ"      => $header["REQ_NO"],
          //   "HIST_DATE_REQ"   => date('d-M-Y', strtotime($header["REQUEST_RECEIVING_DATE"])),
          //   "HIST_TOTAL"      => "",
          //   "HIST_IN"         => "",
          //   "HIST_OUT"        => $detail["REQUEST_DTL_TOTAL"],
          //   "HIST_USER"       => ""
          // ];
          //
          // $historyBrg               = $devdb->set($storeHistory)->get_compiled_insert('TH_HISTORY_BRG');
          // $queryHistory             = $devdb->query($historyBrg);
          }
        }

        if ($encode == "true") {
          $out["result"] = base64_encode(json_encode($result));
          echo json_encode($out);
        } else {
          echo json_encode($result);
        }
      }

    // Done
    function getReceiving_post($input, $branch, $encode) {
      $this->auth_basic();
      //header
      $header             = $input['header'];
      $REQ_NO             = $header['REQ_NO'];
      $REQ_RECEIVING_DATE = $header['REQ_RECEIVING_DATE'];
      $NO_NOTA            = $header['NO_NOTA'];
      $TGL_NOTA           = $header['TGL_NOTA'];
      $NM_CONSIGNEE       = $header['NM_CONSIGNEE'];
      $ALAMAT             = $header['ALAMAT'];
      $PAYMENT            = $header['PAYMENT_METHOD'];
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

          $query = "
          INSERT INTO TX_REQ_RECEIVING_HDR
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
          REQUEST_PAYMENT_METHOD,
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
          '" . $PAYMENT . "',
          '" . $DI . "'
          )";

          $insertHDR = $this->reponpks->query($query);
          $result["SUCCESS"]      = "true";
          $result["MSG"]          = " Success";
          $result["REQ_NO"]       = $input["header"]["REQ_NO"];
          $result["NO_NOTA"]      = $input["header"]["NO_NOTA"];
          $result["NM_CONSIGNEE"] = $input["header"]["NM_CONSIGNEE"];
        } else {
          $result["SUCCESS"]      = "false";
          $result["MSG"]          = " Already Exist";
          $result["REQ_NO"]       = $input["header"]["REQ_NO"];
          $result["NO_NOTA"]      = $input["header"]["NO_NOTA"];
          $result["NM_CONSIGNEE"] = $input["header"]["NM_CONSIGNEE"];
          $insertHDR = true;
          $IDheader = $resultCek[0]['REQUEST_ID'];
        }


        //detail
        $detail = $input['arrdetail'];
        if ($insertHDR) {
          foreach ($detail as $val) {

            $sqlcek = $this->reponpks->where('REQUEST_DTL_CONT', $val['REQ_DTL_CONT'])->where('REQUEST_HDR_ID', $IDheader)->get('TX_REQ_RECEIVING_DTL');
            $resultcekdtl = $sqlcek->result_array();

            if (empty($resultcekdtl)) {
              $sqlIDTL                = "SELECT SEQ_REQ_RECEIVING_DTL.NEXTVAL AS ID FROM DUAL";
              $resultIDTL             = $this->db->query($sqlIDTL)->result_array();
              $IDdetail               = $resultIDTL[0]['ID'];
              $REQ_DTL_CONT           = $val['REQ_DTL_CONT'];
              $REQ_DTL_CONT_STATUS    = $val['REQ_DTL_CONT_STATUS'];
              $REQ_DTL_COMMODITY      = $val['REQ_DTL_COMMODITY'];
              $REQ_DTL_VIA_ID         = $val['REQ_DTL_VIA_ID'];
              $REQ_DTL_VIA_NAME       = $val['REQ_DTL_VIA_NAME'];
              $REQ_DTL_SIZE           = $val['REQ_DTL_SIZE'];
              $REQ_DTL_TYPE           = $val['REQ_DTL_TYPE'];
              $REQ_DTL_CONT_HAZARD    = $val['REQ_DTL_CONT_HAZARD'];
              $REQUEST_DTL_OWNER_CODE = $val['REQ_DTL_OWNER_CODE'];
              $REQ_DTL_OWNER_NAME     = $val['REQ_DTL_OWNER_NAME'];
              $REQ_DTL_VESSEL_NAME    = $val['REQ_DTL_VESSEL_NAME'];
              $REQ_DTL_VESSEL_CODE    = $val['REQ_DTL_VESSEL_CODE'];
              $REQ_DTL_VOY            = $val['REC_DTL_VOYIN']." / ".$val['REC_DTL_VOYOUT'];

              if (isset($val["REQ_DTL_TL"])) {
                $queryDTL = "
                INSERT INTO TX_REQ_RECEIVING_DTL
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
                REQUEST_DTL_OWNER_NAME,
                REQUEST_DTL_VIA,
                REQUEST_DTL_VIA_ID,
                REQUEST_DTL_TL,
                REQUEST_DTL_VESSEL_NAME,
                REQUEST_DTL__VESSEL_CODE,
                REQUEST_DTL_VOY
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
                '" . $REQ_DTL_OWNER_NAME . "',
                '" . $REQ_DTL_VIA_NAME . "',
                '" . $REQ_DTL_VIA_ID . "',
                '" . $val["REQ_DTL_TL"] . "',
                '" . $REQ_DTL_VESSEL_NAME . "',
                '" . $REQ_DTL_VESSEL_CODE . "',
                '" . $REQ_DTL_VOY . "'
                )";
              } else {
                $queryDTL = "
                INSERT INTO TX_REQ_RECEIVING_DTL
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
                REQUEST_DTL_OWNER_NAME,
                REQUEST_DTL_VIA,
                REQUEST_DTL_VIA_ID,
                REQUEST_DTL_VESSEL_NAME,
                REQUEST_DTL__VESSEL_CODE,
                REQUEST_DTL_VOY
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
                '" . $REQ_DTL_OWNER_NAME . "',
                '" . $REQ_DTL_VIA_NAME . "',
                '" . $REQ_DTL_VIA_ID . "',
                '" . $REQ_DTL_VESSEL_NAME . "',
                '" . $REQ_DTL_VESSEL_CODE . "',
                '" . $REQ_DTL_VOY . "'
                )";
              }

              $resultDtl = $this->reponpks->query($queryDTL);
              if ($resultDtl) {
                $result["DETAIL"][] = ["REQ_DTL_CONT" => $val["REQ_DTL_CONT"], "REQ_DTL_OWNER_NAME" => $val["REQ_DTL_OWNER_NAME"]];
              }


              if ($REQUEST_DTL_OWNER_CODE != '') {
                $sqlcekowner = "SELECT OWNER_CODE FROM TM_OWNER WHERE OWNER_CODE='" . $REQUEST_DTL_OWNER_CODE . "' AND OWNER_BRANCH_ID = " . $branch . " ";
                $resultCekowner = $this->db->query($sqlcekowner);
                $totalcekowner = $resultCekowner->num_rows();
                if ($totalcekowner <= 0) {
                  $insertOwner = "INSERT INTO TM_OWNER (OWNER_CODE, OWNER_NAME, OWNER_BRANCH_ID) VALUES ('" . $REQUEST_DTL_OWNER_CODE . "','" . $REQ_DTL_OWNER_NAME . "'," . $branch . ")";
                  $this->db->query($insertOwner);
                }
              }

              //insert history container
              $this->db->query("CALL INSERT_HISTORY_CONTAINER(
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
              '".$REQ_DTL_CONT_HAZARD."',
              NULL)");

                $sqlcekmstcont = "SELECT CONTAINER_NO FROM TM_CONTAINER WHERE CONTAINER_NO='".$REQ_DTL_CONT."' AND CONTAINER_BRANCH_ID = ".$branch." ";
                $resultCekmstcont = $this->db->query($sqlcekmstcont);
                $totalcekmstcont = $resultCekmstcont->num_rows();
                if($totalcekmstcont >0){
                  if($REQUEST_DTL_OWNER_CODE != '') {
                    $updatecontowner = "UPDATE TM_CONTAINER SET CONTAINER_OWNER = '".$REQUEST_DTL_OWNER_CODE."' WHERE CONTAINER_NO='".$REQ_DTL_CONT."' AND CONTAINER_BRANCH_ID = ".$branch." ";
                    $this->db->query($updatecontowner);
                  }
                }
            } else {
              $result["DETAIL"][] = ["REQ_DTL_CONT" => $val["REQ_DTL_CONT"], "REQ_DTL_OWNER_NAME" => $val["REQ_DTL_OWNER_NAME"]];
            }
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

        // JSON Response
        header('Content-Type: application/json');
        if ($encode == "true") {
          $out["result"] = base64_encode(json_encode($result));
          echo json_encode($out);
        } else {
          echo json_encode($result);
        }
      }

    function getAlihKapalStuffing_post($input, $branch,$encode) {
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

    function getReceivingTPK_post($input, $branch,$encode) {
        $this->auth_basic();
        //header
        $header             = $input['header'];

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
        $detail = $input['arrdetail'];
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
              $this->db->query("CALL INSERT_HISTORY_CONTAINER(
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

        // JSON Response
        header('Content-Type: application/json');
        echo json_encode($result);
      }

      function getCancelledReq_post($input, $branch,$encode) {
        $devdb                        = $this->db;
        $repodb                       = $this->reponpks;
        $header                       = $input["header"];
        $detail                       = $input["arrdetail"];

        foreach ($detail as $detail) {
          if (!empty($detail["REQ_DTL_CONT"])) {
            $noContainer              = $detail["REQ_DTL_CONT"];
          } else {
            $noContainer              = $detail["REQ_DTL_SI"];
          }

          $query                      = $repodb->where("CANCELLED_NOREQ", $header["REQ_CANCEL_NO"])->where("CANCELLED_NO_CONT", $noContainer)->get('TH_CANCELLED');
          $resultQuery                = $query->result_array();

          if (empty($resultQuery)) {
          $storeDetail            = [
            "CANCELLED_NOREQ"       => $header["REQ_CANCEL_NO"],
            "CANCELLED_NO_CONT"     => $noContainer,
            "CANCELLED_CREATE_DATE" => "",
            "CANCELLED_CREATE_BY"   => "",
            "CANCELLED_MARK"        => $header["REQ_MARK"],
            "CANCELLED_STATUS"      => $header["CANCELLED_STATUS"],
            "CANCELLED_NOREQ_OLD"   => $header["REQ_NO"],
            "CANCELLED_REQ_DATE"    => $header["REQ_RECEIVING_DATE"],
            "CANCELLED_BRANCH_ID"   => $branch,
            "CANCELLED_NOREQ_NEW"   => "",
            "CANCELLED_QTY"         => $detail["REQ_DTL_QTY"]
          ];

          $det                      = $repodb->set($storeDetail)->get_compiled_insert('TH_CANCELLED');
          $queryDtl                 = $this->reponpks->query($det);
          $result["MSG"]            = "Success";
          $result["DETAIL"][]       = $storeDetail;

          if ($header["CANCELLED_STATUS"] == 17) {
            //Batal Delivery Container
            $queryhdr               = $repodb->where("REQ_NO", $header["REQ_NO"])->get('TX_REQ_DELIVERY_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["REQ_ID"];
            $update                 = $repodb->set("REQ_DTL_ACTIVE", "T")->where('REQ_DTL_CONT', $noContainer)->where('REQ_HDR_ID', $hdrId)->update('TX_REQ_DELIVERY_DTL');
          } else if($header["CANCELLED_STATUS"] == 16) {
            // Batal Receiving
            $queryhdr               = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_RECEIVING_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["REQUEST_ID"];
            $update                 = $repodb->set("REQUEST_DTL_CANCELLED", "Y")->where('REQUEST_DTL_CONT', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_DTL');
          } else if($header["CANCELLED_STATUS"] == 8) {
            // Batal Stuffing
            $queryhdr               = $repodb->where("STUFF_NO", $header["REQ_NO"])->get('TX_REQ_STUFF_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["STUFF_ID"];
            $update                 = $repodb->set("STUFF_DTL_ACTIVE", "T")->set("STUFF_DTL_CANCELLED", "Y")->set("STUFF_DTL_STATUS", "2")->where('STUFF_DTL_CONT', $noContainer)->where('STUFF_DTL_HDR_ID', $hdrId)->update('TX_REQ_STUFF_DTL');

            $cek_jumlah_dtl         = $repodb->where('STUFF_DTL_HDR_ID',$hdrId)->from('TX_REQ_STUFF_DTL')->count_all_results();
						$cek_jumlah_out         = $repodb->where('STUFF_DTL_HDR_ID',$hdrId)->where('STUFF_DTL_ACTIVE','T')->from('TX_REQ_STUFF_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
							$repodb->set('STUFF_STATUS',2)->where('STUFF_ID',$hdrId)->update('TX_REQ_STUFF_HDR');
						}
          } else if($header["CANCELLED_STATUS"] == 18) {
            // Batal Striping
            $queryhdr               = $repodb->where("STRIP_NO", $header["REQ_NO"])->get('TX_REQ_STRIP_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["STRIP_ID"];
            $update                 = $repodb->set("STRIP_DTL_ACTIVE", "T")->set("STRIP_DTL_CANCELLED", "Y")->set("STRIP_DTL_STATUS", "2")->where('STRIP_DTL_CONT', $noContainer)->where('STRIP_DTL_HDR_ID', $hdrId)->update('TX_REQ_STRIP_DTL');

            $cek_jumlah_dtl         = $repodb->where('STRIP_DTL_HDR_ID',$hdrId)->from('TX_REQ_STRIP_DTL')->count_all_results();
						$cek_jumlah_out         = $repodb->where('STRIP_DTL_HDR_ID',$hdrId)->where('STRIP_DTL_ACTIVE','T')->from('TX_REQ_STRIP_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
							$repodb->set('STRIP_STATUS',2)->where('STRIP_ID',$hdrId)->update('TX_REQ_STRIP_HDR');
						}
          } else if($header["CANCELLED_STATUS"] == 19) {
            // Batal Fumigasi
            $queryhdr               = $repodb->where("FUMI_NO", $header["REQ_NO"])->get('TX_REQ_FUMI_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["FUMI_ID"];
            $update                 = $repodb->set("FUMI_DTL_ACTIVE", "T")->set("FUMI_DTL_CANCELLED", "Y")->set("FUMI_DTL_STATUS", "2")->where('FUMI_DTL_CONT', $noContainer)->where('FUMI_DTL_HDR_ID', $hdrId)->update('TX_REQ_FUMI_DTL');

            $cek_jumlah_dtl         = $repodb->where('FUMI_DTL_HDR_ID',$hdrId)->from('TX_REQ_FUMI_DTL')->count_all_results();
						$cek_jumlah_out         = $repodb->where('FUMI_DTL_HDR_ID',$hdrId)->where('FUMI_DTL_ACTIVE','T')->from('TX_REQ_FUMI_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
							$repodb->set('FUMI_STATUS',2)->where('FUMI_ID',$hdrId)->update('TX_REQ_FUMI_HDR');
              }
						} else if($header["CANCELLED_STATUS"] == 20) {
              // Batal Plugging
              $queryhdr             = $repodb->where("PLUG_NO", $header["REQ_NO"])->get('TX_REQ_PLUG_HDR');
              $hdrData              = $queryhdr->result_array();
              $hdrId                = $hdrData[0]["PLUG_ID"];
              $update               = $repodb->set("PLUG_DTL_ACTIVE", "T")->set("PLUG_DTL_CANCELLED", "Y")->set("PLUG_DTL_STATUS", "2")->where('PLUG_DTL_CONT', $noContainer)->where('PLUG_DTL_HDR_ID', $hdrId)->update('TX_REQ_PLUG_DTL');

              $cek_jumlah_dtl       = $repodb->where('PLUG_DTL_HDR_ID',$hdrId)->from('TX_REQ_PLUG_DTL')->count_all_results();
  						$cek_jumlah_out       = $repodb->where('PLUG_DTL_HDR_ID',$hdrId)->where('PLUG_DTL_ACTIVE','T')->from('TX_REQ_PLUG_DTL')->count_all_results();

              if($cek_jumlah_out == $cek_jumlah_dtl){
  							$repodb->set('PLUG_STATUS',2)->where('PLUG_ID',$hdrId)->update('TX_REQ_PLUG_HDR');
  						}
          }  else if($header["CANCELLED_STATUS"] == 21) {
            // Batal Receiving Barang
            $queryhdr             = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_RECEIVING_BRG_HDR');
            $hdrData              = $queryhdr->result_array();
            $hdrId                = $hdrData[0]["REQUEST_ID"];

            $queryQtyReq          = $repodb->where('REQUEST_HDR_ID', $hdrId)->where('REQUEST_DTL_SI', $noContainer)->get("TX_REQ_RECEIVING_BRG_DTL");
            $arrReq               = $queryQtyReq->result_array();
            $qtyAwal              = $arrReq[0]["REQUEST_DTL_TOTAL"];

            $totalQty             = $qtyAwal - $detail["REQ_DTL_QTY"];

            if ($totalQty > 0) {
              $update             = $repodb->set("REQUEST_DTL_TOTAL", $totalQty)->where('REQUEST_DTL_SI', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_BRG_DTL');
            } else {
              $update             = $repodb->set("REQUEST_DTL_TOTAL", $totalQty)->set("REQUEST_DTL_ACTIVE", 'T')->where('REQUEST_DTL_SI', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_BRG_DTL');
            }

            $cek_jumlah_dtl       = $repodb->where('REQUEST_HDR_ID',$hdrId)->from('TX_REQ_RECEIVING_BRG_DTL')->count_all_results();
            $cek_jumlah_out       = $repodb->where('REQUEST_HDR_ID',$hdrId)->where('REQUEST_DTL_ACTIVE','T')->from('TX_REQ_RECEIVING_BRG_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
              $repodb->set('REQUEST_STATUS',2)->where('REQUEST_ID',$hdrId)->update('TX_REQ_RECEIVING_BRG_HDR');
            }
          }  else if($header["CANCELLED_STATUS"] == 22) {
            // Batal Delivery Barang
            $queryhdr             = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_DELIVERY_BRG_HDR');
            $hdrData              = $queryhdr->result_array();
            $hdrId                = $hdrData[0]["REQUEST_ID"];

            $queryQtyReq          = $repodb->where('REQUEST_HDR_ID', $hdrId)->where('REQUEST_DTL_SI', $noContainer)->get("TX_REQ_DELIVERY_BRG_DTL");
            $arrReq               = $queryQtyReq->result_array();
            $qtyAwal              = $arrReq[0]["REQUEST_DTL_TOTAL"];
            $totalQty             = $qtyAwal - $detail["REQ_DTL_QTY"];

            if ($totalQty > 0) {
              $update             = $repodb->set("REQUEST_DTL_TOTAL", $totalQty)->where('REQUEST_DTL_SI', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_DELIVERY_BRG_DTL');
            } else {
              $update             = $repodb->set("REQUEST_DTL_TOTAL", $totalQty)->set("REQUEST_DTL_ACTIVE", 'T')->where('REQUEST_DTL_SI', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_DELIVERY_BRG_DTL');
            }

            $cek_jumlah_dtl       = $repodb->where('REQUEST_HDR_ID',$hdrId)->from('TX_REQ_DELIVERY_BRG_DTL')->count_all_results();
            $cek_jumlah_out       = $repodb->where('REQUEST_HDR_ID',$hdrId)->where('REQUEST_DTL_ACTIVE','T')->from('TX_REQ_DELIVERY_BRG_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
              $repodb->set('REQUEST_STATUS',2)->where('REQUEST_ID',$hdrId)->update('TX_REQ_DELIVERY_BRG_HDR');
            }
          } else if($header["CANCELLED_STATUS"] == 23) {
            // Batal Stuffing
            $queryhdr               = $repodb->where("STUFF_NO", $header["REQ_NO"])->get('TX_REQ_STUFF_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["STUFF_ID"];
            $update                 = $repodb->set("STUFF_DTL_ACTIVE", "T")->set("STUFF_DTL_CANCELLED", "Y")->set("STUFF_DTL_STATUS", "2")->where('STUFF_DTL_CONT', $noContainer)->where('STUFF_DTL_HDR_ID', $hdrId)->update('TX_REQ_STUFF_DTL');

            $cek_jumlah_dtl         = $repodb->where('STUFF_DTL_HDR_ID',$hdrId)->from('TX_REQ_STUFF_DTL')->count_all_results();
						$cek_jumlah_out         = $repodb->where('STUFF_DTL_HDR_ID',$hdrId)->where('STUFF_DTL_ACTIVE','T')->from('TX_REQ_STUFF_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
							$repodb->set('STUFF_STATUS',2)->where('STUFF_ID',$hdrId)->update('TX_REQ_STUFF_HDR');
						}

            // Batal Receiving
            $queryhdr               = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_RECEIVING_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["REQUEST_ID"];
            $update                 = $repodb->set("REQUEST_DTL_CANCELLED", "Y")->where('REQUEST_DTL_CONT', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_DTL');

            $cek_jumlah_dtl         = $repodb->where('REQUEST_HDR_ID',$hdrId)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
						$cek_jumlah_out         = $repodb->where('REQUEST_HDR_ID',$hdrId)->where('REQUEST_DTL_CANCELLED','Y')->from('TX_REQ_DELIVERY_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
							$repodb->set('REQUEST_STATUS',2)->where('REQUEST_HDR_ID',$hdrId)->update('TX_REQ_DELIVERY_HDR');
						}

          } else if($header["CANCELLED_STATUS"] == 24) {
            //Batal Delivery Container
            $queryhdr               = $repodb->where("REQ_NO", $header["REQ_NO"])->get('TX_REQ_DELIVERY_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["REQ_ID"];
            $update                 = $repodb->set("REQ_DTL_ACTIVE", "T")->where('REQ_DTL_CONT', $noContainer)->where('REQ_HDR_ID', $hdrId)->update('TX_REQ_DELIVERY_DTL');

            $cek_jumlah_dtl         = $repodb->where('REQ_HDR_ID',$hdrId)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
						$cek_jumlah_out         = $repodb->where('REQ_HDR_ID',$hdrId)->where('REQ_DTL_ACTIVE','T')->from('TX_REQ_DELIVERY_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
							$repodb->set('REQUEST_STATUS',2)->where('REQ_HDR_ID',$hdrId)->update('TX_REQ_DELIVERY_HDR');
						}

            // Batal Receiving
            $queryhdr               = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_RECEIVING_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["REQUEST_ID"];
            $update                 = $repodb->set("REQUEST_DTL_CANCELLED", "Y")->where('REQUEST_DTL_CONT', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_DTL');

            $cek_jumlah_dtl         = $repodb->where('REQUEST_HDR_ID',$hdrId)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
						$cek_jumlah_out         = $repodb->where('REQUEST_HDR_ID',$hdrId)->where('REQUEST_DTL_CANCELLED','Y')->from('TX_REQ_DELIVERY_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
							$repodb->set('REQUEST_STATUS',2)->where('REQUEST_HDR_ID',$hdrId)->update('TX_REQ_DELIVERY_HDR');
						}
          }  else if($header["CANCELLED_STATUS"] == 25) {
            // Batal Striping
            $queryhdr               = $repodb->where("STRIP_NO", $header["REQ_NO"])->get('TX_REQ_STRIP_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["STRIP_ID"];
            $update                 = $repodb->set("STRIP_DTL_ACTIVE", "T")->set("STRIP_DTL_CANCELLED", "Y")->set("STRIP_DTL_STATUS", "2")->where('STRIP_DTL_CONT', $noContainer)->where('STRIP_DTL_HDR_ID', $hdrId)->update('TX_REQ_STRIP_DTL');

            $cek_jumlah_dtl         = $repodb->where('STRIP_DTL_HDR_ID',$hdrId)->from('TX_REQ_STRIP_DTL')->count_all_results();
						$cek_jumlah_out         = $repodb->where('STRIP_DTL_HDR_ID',$hdrId)->where('STRIP_DTL_ACTIVE','T')->from('TX_REQ_STRIP_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
							$repodb->set('STRIP_STATUS',2)->where('STRIP_ID',$hdrId)->update('TX_REQ_STRIP_HDR');
						}

            // Batal Receiving
            $queryhdr               = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_RECEIVING_HDR');
            $hdrData                = $queryhdr->result_array();
            $hdrId                  = $hdrData[0]["REQUEST_ID"];
            $update                 = $repodb->set("REQUEST_DTL_CANCELLED", "Y")->where('REQUEST_DTL_CONT', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_DTL');

            $cek_jumlah_dtl         = $repodb->where('REQUEST_HDR_ID',$hdrId)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
						$cek_jumlah_out         = $repodb->where('REQUEST_HDR_ID',$hdrId)->where('REQUEST_DTL_CANCELLED','Y')->from('TX_REQ_DELIVERY_DTL')->count_all_results();

            if($cek_jumlah_out == $cek_jumlah_dtl){
							$repodb->set('REQUEST_STATUS',2)->where('REQUEST_HDR_ID',$hdrId)->update('TX_REQ_DELIVERY_HDR');
						}
          }

          // REFF_NAME
          $tmReff                 = $repodb->where("REFF_ID", $header["CANCELLED_STATUS"])->where('REFF_TR_ID', '4')->get('TM_REFF');
          $reffData               = $tmReff->result_array();
          $reffName               = $reffData[0]["REFF_NAME"];


          if ($header["CANCELLED_STATUS"] == 21 || $header["CANCELLED_STATUS"] == 22) {
            $storeHistory  = [
              "HIST_SI"         => $noContainer,
              "HIST_BRANCH_ID"  => $branch,
              "HIST_COUNTER"    => "",
              "HIST_STORAGE"    => "",
              "HIST_ACTIVITY_ID"=> $header["CANCELLED_STATUS"],
              "HIST_ACTIVITY"   => $reffName,
              "HIST_DATE"       => "",
              "HIST_NOREQ"      => $header["REQ_NO"],
              "HIST_DATE_REQ"   => "",
              "HIST_TOTAL"      => $totalQty,
              "HIST_IN"         => "",
              "HIST_OUT"        => "",
              "HIST_USER"       => "",
              "HIST_TIMESTAMP"  => null
            ];

            $historyBrg               = $devdb->set($storeHistory)->get_compiled_insert('TH_HISTORY_BRG');
            $queryHistory             = $devdb->query($historyBrg);

          } else {
            //insert history container
            $devdb->query("
                  CALL INSERT_HISTORY_CONTAINER(
                  '".$noContainer."',
                  '".$header["REQ_NO"]."',
                  '".$header["REQ_RECEIVING_DATE"]."',
                  '',
                  '',
                  NULL,
                  NULL,
                  NULL,
                  NULL,
                  NULL,
                  NULL,
                  ".$header["CANCELLED_STATUS"].",
                  '".$reffName."',
                  NULL,
                  NULL,
                  4,
                  '',
                  NULL,
                  NULL)");
          }

          $storeDetail            = [
            "CANCELLED_NOREQ"       => $header["REQ_NO"],
            "CANCELLED_NO_CONT"     => $noContainer,
            "CANCELLED_CREATE_DATE" => "",
            "CANCELLED_CREATE_BY"   => "",
            "CANCELLED_MARK"        => $header["REQ_MARK"],
            "CANCELLED_STATUS"      => "",
            "CANCELLED_NOREQ_OLD"   => "",
            "CANCELLED_REQ_DATE"    => $header["REQ_RECEIVING_DATE"],
            "CANCELLED_BRANCH_ID"   => $branch,
            "CANCELLED_NOREQ_NEW"   => ""
          ];

          $result["MSG"]          = "Success Cancel";
          $result["DETAIL"][]     = $storeDetail;

        } else {
        $storeDetail            = [
          "CANCELLED_NOREQ"       => $header["REQ_NO"],
          "CANCELLED_NO_CONT"     => $noContainer,
          "CANCELLED_CREATE_DATE" => "",
          "CANCELLED_CREATE_BY"   => "",
          "CANCELLED_MARK"        => $header["REQ_MARK"],
          "CANCELLED_STATUS"      => "",
          "CANCELLED_NOREQ_OLD"   => "",
          "CANCELLED_REQ_DATE"    => $header["REQ_RECEIVING_DATE"],
          "CANCELLED_BRANCH_ID"   => $branch,
          "CANCELLED_NOREQ_NEW"   => ""
        ];

        $result["MSG"]          = "Already Exist";
        $result["DETAIL"][]     = $storeDetail;

        // if ($header["CANCELLED_STATUS"] == 17) {
        //   //Batal Delivery Container
        //   $queryhdr               = $repodb->where("REQ_NO", $header["REQ_NO"])->get('TX_REQ_DELIVERY_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["REQ_ID"];
        //   $update                 = $repodb->set("REQ_DTL_ACTIVE", "T")->where('REQ_DTL_CONT', $noContainer)->where('REQ_HDR_ID', $hdrId)->update('TX_REQ_DELIVERY_DTL');
        // } else if($header["CANCELLED_STATUS"] == 16) {
        //   // Batal Receiving
        //   $queryhdr               = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_RECEIVING_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["REQUEST_ID"];
        //   $update                 = $repodb->set("REQUEST_DTL_CANCELLED", "Y")->where('REQUEST_DTL_CONT', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_DTL');
        // } else if($header["CANCELLED_STATUS"] == 8) {
        //   // Batal Stuffing
        //   $queryhdr               = $repodb->where("STUFF_NO", $header["REQ_NO"])->get('TX_REQ_STUFF_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["STUFF_ID"];
        //   $update                 = $repodb->set("STUFF_DTL_ACTIVE", "T")->set("STUFF_DTL_CANCELLED", "Y")->set("STUFF_DTL_STATUS", "2")->where('STUFF_DTL_CONT', $noContainer)->where('STUFF_DTL_HDR_ID', $hdrId)->update('TX_REQ_STUFF_DTL');
        //
        //   $cek_jumlah_dtl         = $repodb->where('STUFF_DTL_HDR_ID',$hdrId)->from('TX_REQ_STUFF_DTL')->count_all_results();
        //   $cek_jumlah_out         = $repodb->where('STUFF_DTL_HDR_ID',$hdrId)->where('STUFF_DTL_ACTIVE','T')->from('TX_REQ_STUFF_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('STUFF_STATUS',2)->where('STUFF_ID',$hdrId)->update('TX_REQ_STUFF_HDR');
        //   }
        // } else if($header["CANCELLED_STATUS"] == 18) {
        //   // Batal Striping
        //   $queryhdr               = $repodb->where("STRIP_NO", $header["REQ_NO"])->get('TX_REQ_STRIP_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["STRIP_ID"];
        //   $update                 = $repodb->set("STRIP_DTL_ACTIVE", "T")->set("STRIP_DTL_CANCELLED", "Y")->set("STRIP_DTL_STATUS", "2")->where('STRIP_DTL_CONT', $noContainer)->where('STRIP_DTL_HDR_ID', $hdrId)->update('TX_REQ_STRIP_DTL');
        //
        //   $cek_jumlah_dtl         = $repodb->where('STRIP_DTL_HDR_ID',$hdrId)->from('TX_REQ_STRIP_DTL')->count_all_results();
        //   $cek_jumlah_out         = $repodb->where('STRIP_DTL_HDR_ID',$hdrId)->where('STRIP_DTL_ACTIVE','T')->from('TX_REQ_STRIP_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('STRIP_STATUS',2)->where('STRIP_ID',$hdrId)->update('TX_REQ_STRIP_HDR');
        //   }
        // } else if($header["CANCELLED_STATUS"] == 19) {
        //   // Batal Fumigasi
        //   $queryhdr               = $repodb->where("FUMI_NO", $header["REQ_NO"])->get('TX_REQ_FUMI_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["FUMI_ID"];
        //   $update                 = $repodb->set("FUMI_DTL_ACTIVE", "T")->set("FUMI_DTL_CANCELLED", "Y")->set("FUMI_DTL_STATUS", "2")->where('FUMI_DTL_CONT', $noContainer)->where('FUMI_DTL_HDR_ID', $hdrId)->update('TX_REQ_FUMI_DTL');
        //
        //   $cek_jumlah_dtl         = $repodb->where('FUMI_DTL_HDR_ID',$hdrId)->from('TX_REQ_FUMI_DTL')->count_all_results();
        //   $cek_jumlah_out         = $repodb->where('FUMI_DTL_HDR_ID',$hdrId)->where('FUMI_DTL_ACTIVE','T')->from('TX_REQ_FUMI_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('FUMI_STATUS',2)->where('FUMI_ID',$hdrId)->update('TX_REQ_FUMI_HDR');
        //     }
        //   } else if($header["CANCELLED_STATUS"] == 20) {
        //     // Batal Plugging
        //     $queryhdr             = $repodb->where("PLUG_NO", $header["REQ_NO"])->get('TX_REQ_PLUG_HDR');
        //     $hdrData              = $queryhdr->result_array();
        //     $hdrId                = $hdrData[0]["PLUG_ID"];
        //     $update               = $repodb->set("PLUG_DTL_ACTIVE", "T")->set("PLUG_DTL_CANCELLED", "Y")->set("PLUG_DTL_STATUS", "2")->where('PLUG_DTL_CONT', $noContainer)->where('PLUG_DTL_HDR_ID', $hdrId)->update('TX_REQ_PLUG_DTL');
        //
        //     $cek_jumlah_dtl       = $repodb->where('PLUG_DTL_HDR_ID',$hdrId)->from('TX_REQ_PLUG_DTL')->count_all_results();
        //     $cek_jumlah_out       = $repodb->where('PLUG_DTL_HDR_ID',$hdrId)->where('PLUG_DTL_ACTIVE','T')->from('TX_REQ_PLUG_DTL')->count_all_results();
        //
        //     if($cek_jumlah_out == $cek_jumlah_dtl){
        //       $repodb->set('PLUG_STATUS',2)->where('PLUG_ID',$hdrId)->update('TX_REQ_PLUG_HDR');
        //     }
        // }  else if($header["CANCELLED_STATUS"] == 21) {
        //   // Batal Receiving Barang
        //   $queryhdr             = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_RECEIVING_BRG_HDR');
        //   $hdrData              = $queryhdr->result_array();
        //   $hdrId                = $hdrData[0]["REQUEST_ID"];
        //
        //   $queryQtyReq          = $repodb->where('REQUEST_HDR_ID', $hdrId)->where('REQUEST_DTL_SI', $noContainer)->get("TX_REQ_RECEIVING_BRG_DTL");
        //   $arrReq               = $queryQtyReq->result_array();
        //   $qtyAwal              = $arrReq[0]["REQUEST_DTL_TOTAL"];
        //
        //   $totalQty             = $qtyAwal - $detail["REQ_DTL_QTY"];
        //
        //   if ($totalQty > 0) {
        //     $update             = $repodb->set("REQUEST_DTL_TOTAL", $totalQty)->where('REQUEST_DTL_SI', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_BRG_DTL');
        //   } else {
        //     $update             = $repodb->set("REQUEST_DTL_TOTAL", $totalQty)->set("REQUEST_DTL_ACTIVE", 'T')->where('REQUEST_DTL_SI', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_BRG_DTL');
        //   }
        //
        //   $cek_jumlah_dtl       = $repodb->where('REQUEST_HDR_ID',$hdrId)->from('TX_REQ_RECEIVING_BRG_DTL')->count_all_results();
        //   $cek_jumlah_out       = $repodb->where('REQUEST_HDR_ID',$hdrId)->where('REQUEST_DTL_ACTIVE','T')->from('TX_REQ_RECEIVING_BRG_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('REQUEST_STATUS',2)->where('REQUEST_ID',$hdrId)->update('TX_REQ_RECEIVING_BRG_HDR');
        //   }
        // }  else if($header["CANCELLED_STATUS"] == 22) {
        //   // Batal Delivery Barang
        //   $queryhdr             = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_DELIVERY_BRG_HDR');
        //   $hdrData              = $queryhdr->result_array();
        //   $hdrId                = $hdrData[0]["REQUEST_ID"];
        //
        //   $queryQtyReq          = $repodb->where('REQUEST_HDR_ID', $hdrId)->where('REQUEST_DTL_SI', $noContainer)->get("TX_REQ_DELIVERY_BRG_DTL");
        //   $arrReq               = $queryQtyReq->result_array();
        //   $qtyAwal              = $arrReq[0]["REQUEST_DTL_TOTAL"];
        //   $totalQty             = $qtyAwal - $detail["REQ_DTL_QTY"];
        //
        //   if ($totalQty > 0) {
        //     $update             = $repodb->set("REQUEST_DTL_TOTAL", $totalQty)->where('REQUEST_DTL_SI', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_DELIVERY_BRG_DTL');
        //   } else {
        //     $update             = $repodb->set("REQUEST_DTL_TOTAL", $totalQty)->set("REQUEST_DTL_ACTIVE", 'T')->where('REQUEST_DTL_SI', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_DELIVERY_BRG_DTL');
        //   }
        //
        //   $cek_jumlah_dtl       = $repodb->where('REQUEST_HDR_ID',$hdrId)->from('TX_REQ_DELIVERY_BRG_DTL')->count_all_results();
        //   $cek_jumlah_out       = $repodb->where('REQUEST_HDR_ID',$hdrId)->where('REQUEST_DTL_ACTIVE','T')->from('TX_REQ_DELIVERY_BRG_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('REQUEST_STATUS',2)->where('REQUEST_ID',$hdrId)->update('TX_REQ_DELIVERY_BRG_HDR');
        //   }
        // } else if($header["CANCELLED_STATUS"] == 23) {
        //   // Batal Stuffing
        //   $queryhdr               = $repodb->where("STUFF_NO", $header["REQ_NO"])->get('TX_REQ_STUFF_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["STUFF_ID"];
        //   $update                 = $repodb->set("STUFF_DTL_ACTIVE", "T")->set("STUFF_DTL_CANCELLED", "Y")->set("STUFF_DTL_STATUS", "2")->where('STUFF_DTL_CONT', $noContainer)->where('STUFF_DTL_HDR_ID', $hdrId)->update('TX_REQ_STUFF_DTL');
        //
        //   $cek_jumlah_dtl         = $repodb->where('STUFF_DTL_HDR_ID',$hdrId)->from('TX_REQ_STUFF_DTL')->count_all_results();
        //   $cek_jumlah_out         = $repodb->where('STUFF_DTL_HDR_ID',$hdrId)->where('STUFF_DTL_ACTIVE','T')->from('TX_REQ_STUFF_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('STUFF_STATUS',2)->where('STUFF_ID',$hdrId)->update('TX_REQ_STUFF_HDR');
        //   }
        //
        //   // Batal Receiving
        //   $queryhdr               = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_RECEIVING_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["REQUEST_ID"];
        //   $update                 = $repodb->set("REQUEST_DTL_CANCELLED", "Y")->where('REQUEST_DTL_CONT', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_DTL');
        //
        //   $cek_jumlah_dtl         = $repodb->where('REQUEST_HDR_ID',$hdrId)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
        //   $cek_jumlah_out         = $repodb->where('REQUEST_HDR_ID',$hdrId)->where('REQUEST_DTL_CANCELLED','Y')->from('TX_REQ_DELIVERY_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('REQUEST_STATUS',2)->where('REQUEST_HDR_ID',$hdrId)->update('TX_REQ_DELIVERY_HDR');
        //   }
        //
        // } else if($header["CANCELLED_STATUS"] == 24) {
        //   //Batal Delivery Container
        //   $queryhdr               = $repodb->where("REQ_NO", $header["REQ_NO"])->get('TX_REQ_DELIVERY_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["REQ_ID"];
        //   $update                 = $repodb->set("REQ_DTL_ACTIVE", "T")->where('REQ_DTL_CONT', $noContainer)->where('REQ_HDR_ID', $hdrId)->update('TX_REQ_DELIVERY_DTL');
        //
        //   $cek_jumlah_dtl         = $repodb->where('REQ_HDR_ID',$hdrId)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
        //   $cek_jumlah_out         = $repodb->where('REQ_HDR_ID',$hdrId)->where('REQ_DTL_ACTIVE','T')->from('TX_REQ_DELIVERY_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('REQUEST_STATUS',2)->where('REQ_HDR_ID',$hdrId)->update('TX_REQ_DELIVERY_HDR');
        //   }
        //
        //   // Batal Receiving
        //   $queryhdr               = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_RECEIVING_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["REQUEST_ID"];
        //   $update                 = $repodb->set("REQUEST_DTL_CANCELLED", "Y")->where('REQUEST_DTL_CONT', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_DTL');
        //
        //   $cek_jumlah_dtl         = $repodb->where('REQUEST_HDR_ID',$hdrId)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
        //   $cek_jumlah_out         = $repodb->where('REQUEST_HDR_ID',$hdrId)->where('REQUEST_DTL_CANCELLED','Y')->from('TX_REQ_DELIVERY_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('REQUEST_STATUS',2)->where('REQUEST_HDR_ID',$hdrId)->update('TX_REQ_DELIVERY_HDR');
        //   }
        // }  else if($header["CANCELLED_STATUS"] == 25) {
        //   // Batal Striping
        //   $queryhdr               = $repodb->where("STRIP_NO", $header["REQ_NO"])->get('TX_REQ_STRIP_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["STRIP_ID"];
        //   $update                 = $repodb->set("STRIP_DTL_ACTIVE", "T")->set("STRIP_DTL_CANCELLED", "Y")->set("STRIP_DTL_STATUS", "2")->where('STRIP_DTL_CONT', $noContainer)->where('STRIP_DTL_HDR_ID', $hdrId)->update('TX_REQ_STRIP_DTL');
        //
        //   $cek_jumlah_dtl         = $repodb->where('STRIP_DTL_HDR_ID',$hdrId)->from('TX_REQ_STRIP_DTL')->count_all_results();
        //   $cek_jumlah_out         = $repodb->where('STRIP_DTL_HDR_ID',$hdrId)->where('STRIP_DTL_ACTIVE','T')->from('TX_REQ_STRIP_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('STRIP_STATUS',2)->where('STRIP_ID',$hdrId)->update('TX_REQ_STRIP_HDR');
        //   }
        //
        //   // Batal Receiving
        //   $queryhdr               = $repodb->where("REQUEST_NO", $header["REQ_NO"])->get('TX_REQ_RECEIVING_HDR');
        //   $hdrData                = $queryhdr->result_array();
        //   $hdrId                  = $hdrData[0]["REQUEST_ID"];
        //   $update                 = $repodb->set("REQUEST_DTL_CANCELLED", "Y")->where('REQUEST_DTL_CONT', $noContainer)->where('REQUEST_HDR_ID', $hdrId)->update('TX_REQ_RECEIVING_DTL');
        //
        //   $cek_jumlah_dtl         = $repodb->where('REQUEST_HDR_ID',$hdrId)->from('TX_REQ_DELIVERY_DTL')->count_all_results();
        //   $cek_jumlah_out         = $repodb->where('REQUEST_HDR_ID',$hdrId)->where('REQUEST_DTL_CANCELLED','Y')->from('TX_REQ_DELIVERY_DTL')->count_all_results();
        //
        //   if($cek_jumlah_out == $cek_jumlah_dtl){
        //     $repodb->set('REQUEST_STATUS',2)->where('REQUEST_HDR_ID',$hdrId)->update('TX_REQ_DELIVERY_HDR');
        //   }
        // }
        //
        // // REFF_NAME
        // $tmReff                 = $repodb->where("REFF_ID", $header["CANCELLED_STATUS"])->where('REFF_TR_ID', '4')->get('TM_REFF');
        // $reffData               = $tmReff->result_array();
        // $reffName               = $reffData[0]["REFF_NAME"];
        //
        //
        // if ($header["CANCELLED_STATUS"] == 21 || $header["CANCELLED_STATUS"] == 22) {
        //   $storeHistory  = [
        //     "HIST_SI"         => $noContainer,
        //     "HIST_BRANCH_ID"  => $branch,
        //     "HIST_COUNTER"    => "",
        //     "HIST_STORAGE"    => "",
        //     "HIST_ACTIVITY_ID"=> $header["CANCELLED_STATUS"],
        //     "HIST_ACTIVITY"   => $reffName,
        //     "HIST_DATE"       => "",
        //     "HIST_NOREQ"      => $header["REQ_NO"],
        //     "HIST_DATE_REQ"   => "",
        //     "HIST_TOTAL"      => $totalQty,
        //     "HIST_IN"         => "",
        //     "HIST_OUT"        => "",
        //     "HIST_USER"       => "",
        //     "HIST_TIMESTAMP"  => null
        //   ];
        //
        //   $historyBrg               = $devdb->set($storeHistory)->get_compiled_insert('TH_HISTORY_BRG');
        //   $queryHistory             = $devdb->query($historyBrg);
        //
        // } else {
        //   //insert history container
        //   $devdb->query("
        //         CALL INSERT_HISTORY_CONTAINER(
        //         '".$noContainer."',
        //         '".$header["REQ_NO"]."',
        //         '".$header["REQ_RECEIVING_DATE"]."',
        //         '',
        //         '',
        //         NULL,
        //         NULL,
        //         NULL,
        //         NULL,
        //         NULL,
        //         NULL,
        //         ".$header["CANCELLED_STATUS"].",
        //         '".$reffName."',
        //         NULL,
        //         NULL,
        //         4,
        //         '',
        //         NULL,
        //         NULL)");
        // }

        }}

        if ($encode == "true") {
          $out["result"] = base64_encode(json_encode($result));
          echo json_encode($out);
        } else {
          echo json_encode($result);
        }
      }
    }
?>
