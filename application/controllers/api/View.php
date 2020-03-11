<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class View extends BD_Controller {

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
        $encode   = "false";
        $request  = $this->post();
        $input    = json_decode(json_encode($this->post()), TRUE);
      } else {
        $encode   = "true";
        $input    = $this->post();
        $input    = json_decode(json_encode($this->post()), TRUE);
        $input    =  json_decode(base64_decode($input['request']),TRUE);
      }

      // Encode json request
      // echo base64_encode(json_encode($input));

      $action     = $input["action"]."_post";
      $this->$action($input, $branch, $encode);
    }

    // New
    function generateGetIn_post($input, $branch, $encode) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
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


      $out["count"]   = count($newdt);
      $out["result"]  = $newdt;

      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

    function generateRecRealStorage_post($input, $branch, $encode) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                          = $this->db;
      $repodb                         = $this->reponpks;
      $branch                         = 3;
      $data                           = $input["data"];

      $newdt                          = [];

      foreach ($data as $data) {
        $noContainer = $data['NO_CONTAINER'];
        $noRequest   = $data['NO_REQUEST'];
        $branchId    = $data["BRANCH_ID"];

        $query   = "
                    SELECT
                    	X.*
                    FROM
                    	(
                    	SELECT
                    		A.REAL_STORAGE_ID,
                    		MIN(A.REAL_STORAGE_ID) OVER (PARTITION BY A.REAL_STORAGE_REQ,A.REAL_STORAGE_SI) AS id_selected,
                    		A.REAL_STORAGE_REQ as NO_REQUEST,
                    		A.REAL_STORAGE_SI as NO_CONTAINER,
                    		STORAGE_TOTAL as JUMLAH,
                    		A.REAL_STORAGE_TRUCK,
                    		A.REAL_STORAGE_TOTAL,
                    		A.REAL_STORAGE_COUNTER,
                    		A.REAL_STORAGE_STATUS,
                    		A.REAL_STORAGE_ACTIVITY,
                    		A.REAL_STORAGE_EQUIPMENT_ID,
                    		A.REAL_STORAGE_EQUIPMENT_NAME,
                    		A.REAL_STORAGE_OPERATOR,
                    		A.REAL_STORAGE_STORAGE,
                    		A.REAL_STORAGE_BRANCH_ID as BRANCH_ID,
                    		A.REAL_STORAGE_CREATE_BY,
                    		TO_CHAR(A.REAL_STORAGE_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS REAL_DATE,
                    		A.REAL_STORAGE_USED,
                    		A.REAL_STORAGE_YARD_ID,
                    		A.REAL_STORAGE_IN,
                    		A.REAL_STORAGE_OUT,
                    		A.REAL_MARK_BACKDATE
                    	FROM
                    		TX_REAL_STORAGE A,
                    		(
                    		SELECT
                    			C.REAL_STORAGE_REQ,
                    			C.REAL_STORAGE_SI,
                    			SUM(C.REAL_STORAGE_IN) STORAGE_TOTAL
                    		FROM
                    			TX_REAL_STORAGE C
                    		GROUP BY
                    			C.REAL_STORAGE_REQ,
                    			C.REAL_STORAGE_SI) B
                    	WHERE
                    		A.REAL_STORAGE_REQ = B.REAL_STORAGE_REQ
                    		AND A.REAL_STORAGE_SI = B.REAL_STORAGE_SI
                        AND A.REAL_STORAGE_SI = '$noContainer'
                        AND A.REAL_STORAGE_REQ = '$noRequest'
                        AND A.REAL_STORAGE_BRANCH_ID = '$branchId'
                    	ORDER BY
                    		REAL_STORAGE_CREATE_DATE )X
                    WHERE
                    X.REAL_STORAGE_ID = X.id_selected
                  ";

        $result          = $repodb->query($query);
        $resultservices  = $result->result_array();

        $data_view = json_encode($resultservices);
        $data_use  = json_decode($data_view);

        foreach ($data_use as $value) {
          $newdt[] = $value;
        }
      }


      $result         = [];
      $out["count"]   = count($newdt);
      $out["result"]  = $newdt;
      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

    function generateDelRealStorage_post($input, $branch, $encode) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                          = $this->db;
      $repodb                         = $this->reponpks;
      $branch                         = 3;
      $data                           = $input["data"];

      $newdt                          = [];

      foreach ($data as $data) {
        $noContainer = $data['NO_CONTAINER'];
        $noRequest   = $data['NO_REQUEST'];
        $branchId    = $data["BRANCH_ID"];
        $query   = "
                  SELECT
                      X.*
                      FROM
                      (
                      SELECT
                      A.DELIVERY_ID,
                      MAX(A.DELIVERY_ID) OVER (PARTITION BY A.DELIVERY_REQ,A.DELIVERY_SI) AS id_selected,
                      A.DELIVERY_REQ as NO_REQUEST,
                      A.DELIVERY_SI as NO_CONTAINER,
                      REAL_DELIVERY_TOTAL as JUMLAH,
                      A.DELIVERY_TRUCK,
                      A.DELIVERY_TOTAL,
                      A.DELIVERY_COUNTER,
                      A.DELIVERY_STATUS,
                      A.DELIVERY_ACTIVITY,
                      A.DELIVERY_EQUIPMENT,
                      A.DELIVERY_OPERATOR,
                      A.DELIVERY_CREATE_BY,
                      TO_CHAR(A.DELIVERY_CREATE_DATE,'MM/DD/YYYY HH24:MI:SS') AS REAL_DATE,
                      A.DELIVERY_BRANCH_ID,
                      A.DELIVERY_EQUIPMENT_NAME,
                      A.DELIVERY_STORAGE
                      FROM
                      TX_REAL_DELIVERY_BRG A,
                      (
                      SELECT
                      C.DELIVERY_REQ,
                      C.DELIVERY_SI,
                      SUM(C.DELIVERY_TOTAL) REAL_DELIVERY_TOTAL
                      FROM
                      TX_REAL_DELIVERY_BRG C
                      GROUP BY
                      C.DELIVERY_REQ,
                      C.DELIVERY_SI) B
                      WHERE
                      A.DELIVERY_REQ = B.DELIVERY_REQ
                      AND A.DELIVERY_SI = B.DELIVERY_SI
                      AND A.DELIVERY_SI = '$noContainer'
                      AND A.DELIVERY_REQ = '$noRequest'
                      AND A.DELIVERY_BRANCH_ID = '$branchId'
                      ORDER BY
                      DELIVERY_CREATE_DATE DESC )X
                      WHERE X.DELIVERY_ID = X.id_selected
                  ";

        $result                       = $repodb->query($query);
        $resultservices               = $result->result_array();

        $data_view = json_encode($resultservices);
        $data_use  = json_decode($data_view);

        foreach ($data_use as $value) {
          $newdt[] = $value;
        }
      }

      $result         = [];
      $out["count"]   = count($newdt);
      $out["result"]  = $newdt;

      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

    function generateGetOut_post($input, $branch, $encode) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
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

      $out["count"]   = count($newdt);
      $out["result"]  = $newdt;

      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

    function generateRealStuffing_post($input, $branch, $encode) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $npksdb                       = $this->npks;
      $data                         = $input["data"];

      $all                          = [];

      foreach ($data as $data) {
        // Change Later
        $sqlgetStuf                 = $repodb->where("REAL_STUFF_CONT",$data["NO_CONTAINER"])
                                             ->where("REAL_STUFF_NOREQ",$data["NO_REQUEST"])
                                             ->where("REAL_STUFF_BRANCH_ID",$data["BRANCH_ID"])
                                             ->where('REAL_STUFF_STATUS', '1')
                                             ->select("TX_REAL_STUFF.*, TO_CHAR(REAL_STUFF_DATE,'YYYY-MM-DD HH24:MI:SS') as REAL_STUFF, REAL_STUFF_CONT as NO_CONTAINER, REAL_STUFF_NOREQ as NO_REQUEST")
                                             ->order_by("REAL_STUFF_DATE", "ASC")
                                             ->get("TX_REAL_STUFF");

        $totalservice = $sqlgetStuf->result_array();

        $data_view = json_encode($totalservice);
        $data_use  = json_decode($data_view);

        foreach ($data_use as $value) {
          $newDt  = [];
          foreach ($value as $key => $value) {
            $newDt[$key] = $value;
            $newDt["STATUS"] = "FCL";
          }
        }

        $all[] = $newDt;
      }

      $out["count"]      = count($all);
      $out["result"]     = $all;

      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

    function generatePlacement_post($input, $branch, $encode) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $npksdb                       = $this->npks;
      $data                         = $input["data"];

      $newdt                        = [];

      foreach ($data as $data) {
        // Change Later
        $sqlPlacement            = $repodb->where("NO_CONTAINER",$data["NO_CONTAINER"])
                                          ->where("NO_REQUEST",$data["NO_REQUEST"])
                                          ->where("BRANCH_ID",$data["BRANCH_ID"])
                                          ->limit(1)
                                          ->select("TX_PLACEMENT.*, TO_CHAR(TGL_PLACEMENT,'YYYY-MM-DD HH24:MI:SS') as PLACEMENT_DATE")
                                          ->order_by("YBC_SLOT", "ASC")
                                          ->get("TX_PLACEMENT");

        $resultservices       = $sqlPlacement->result_array();
        $data_view            = json_encode($resultservices);
        $data_use             = json_decode($data_view);

        foreach ($data_use as $value) {
          $newdt[] = $value;
        }
      }

      $out["count"]   = count($newdt);
      $out["result"]  = $newdt;

      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

    function generateFumi_post($input, $branch, $encode) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $npksdb                       = $this->npks;
      $data                         = $input["data"];

      $newdt                        = [];

      foreach ($data as $data) {
        // Change Later
        $sqlfumi                    = $repodb->where("REAL_FUMI_CONT",$data["NO_CONTAINER"])
                                             ->where("REAL_FUMI_NOREQ",$data["NO_REQUEST"])
                                             ->where("REAL_FUMI_BRANCH_ID",$data["BRANCH_ID"])
                                             ->where("REAL_FUMI_STATUS","2")
                                             ->select("TX_REAL_FUMI.*,  TO_CHAR(REAL_FUMI_DATE,'YYYY-MM-DD HH24:MI:SS') as REAL_FUMI, REAL_FUMI_CONT as NO_CONTAINER, REAL_FUMI_NOREQ as NO_REQUEST")
                                             ->order_by("REAL_FUMI_DATE", "ASC")
                                             ->get("TX_REAL_FUMI");

        $resultservices             = $sqlfumi->result_array();
        $data_view                  = json_encode($resultservices);
        $data_use                   = json_decode($data_view);

        foreach ($data_use as $value) {
          $newdt[] = $value;
        }
      }

      $out["count"]   = count($newdt);
      $out["result"]  = $newdt;

      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

    function generatePlug_post($input, $branch, $encode) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $npksdb                       = $this->npks;
      $data                         = $input["data"];

      $newDt                        = [];


      foreach ($data as $dataStart) {
        // Change Later
        $sqlfumiStart                = $repodb->where("REAL_PLUG_CONT",$dataStart["NO_CONTAINER"])
                                             ->where("REAL_PLUG_NOREQ",$dataStart["NO_REQUEST"])
                                             ->where("REAL_PLUG_BRANCH_ID",$dataStart["BRANCH_ID"])
                                             ->where("REAL_PLUG_STATUS",1)
                                             ->select("TX_REAL_PLUG.*, REAL_PLUG_NOREQ as NO_REQUEST, REAL_PLUG_CONT as NO_CONTAINER,REAL_PLUG_STATUS as STATUS,TO_CHAR(REAL_PLUG_DATE,'YYYY-MM-DD HH24:MI:SS') as REAL_PLUG,REAL_PLUG_BRANCH_ID")
                                             ->get("TX_REAL_PLUG");

        $resultservicesStart        = $sqlfumiStart->result_array();
        $dataViewStart              = json_encode($resultservicesStart);
        $dataUseStart               = json_decode($dataViewStart);

        foreach ($dataUseStart as $valueStart) {
          $newDt[] = $valueStart;
        }
      }

      foreach ($data as $dataFinish) {
        // Change Later
        $sqlfumiFinish                = $repodb->where("REAL_PLUG_CONT",$dataFinish["NO_CONTAINER"])
                                             ->where("REAL_PLUG_NOREQ",$dataFinish["NO_REQUEST"])
                                             ->where("REAL_PLUG_BRANCH_ID",$dataFinish["BRANCH_ID"])
                                             ->where("REAL_PLUG_STATUS",2)
                                             ->select("TX_REAL_PLUG.*,TO_CHAR(REAL_PLUG_DATE,'YYYY-MM-DD HH24:MI:SS') as REAL_PLUG, REAL_PLUG_NOREQ as NO_REQUEST,REAL_PLUG_CONT as NO_CONTAINER,REAL_PLUG_STATUS as STATUS,REAL_PLUG_BRANCH_ID")
                                             ->order_by("REAL_PLUG_DATE", "ASC")
                                             ->get("TX_REAL_PLUG");

        $resultservicesFinish        = $sqlfumiFinish->result_array();
        $dataViewFinish              = json_encode($resultservicesFinish);
        $dataUseFinish               = json_decode($dataViewFinish);

        foreach ($dataUseFinish as $valueFinish) {
          $newDt[] = $valueFinish;
        }
      }

      $out["count"]   = count($newDt);
      $out["result"]  = $newDt;


      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

    function generateGateInCargo_post($input, $branch, $encode) {
      echo "GateInCargo";
    }

    function generateRealStripping_post($input, $branch, $encode) {
      // Initialization
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                        = $this->db;
      $repodb                       = $this->reponpks;
      $npksdb                       = $this->npks;
      $data                         = $input["data"];

      $all                          = [];

      foreach ($data as $data) {
        // Change Later
        $sqlgetStrip                = $repodb->where("REAL_STRIP_CONT",$data["NO_CONTAINER"])
                                             ->where("REAL_STRIP_NOREQ",$data["NO_REQUEST"])
                                             ->where("REAL_STRIP_BRANCH_ID",$data["BRANCH_ID"])
                                             ->select("TX_REAL_STRIP.*,TO_CHAR(REAL_STRIP_DATE,'YYYY-MM-DD HH24:MI:SS') as REAL_STRIP, REAL_STRIP_CONT as NO_CONTAINER, REAL_STRIP_NOREQ as NO_REQUEST")
                                             ->order_by("REAL_STRIP_DATE", "ASC")
                                             ->get("TX_REAL_STRIP");

        $totalservice               = $sqlgetStrip->result_array();
        $data_view                  = json_encode($totalservice);
        $data_use                   = json_decode($data_view);

        foreach ($data_use as $value) {
          $newDt  = [];
          foreach ($value as $key => $value) {
            $newDt[$key] = $value;
            $newDt["STATUS"] = "MTY";
          }
        }

        $all[] = $newDt;
      }

      $out["count"]   = count($all);
      $out["result"]  = $all;

      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

    function trackAndTrace_post($input,$branch, $encode) {
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                = $this->db;
      $data                 = $input["data"];
      $all                  = [];

      foreach ($data as $data) {
        $sqlHistory           = $devdb->where("HIST_CONT",$data["NO_CONTAINER"])->where('HIST_BRANCH_ID', $data["BRANCH_ID"])->get("TH_HISTORY_CONTAINER");
        $resultservices       = $sqlHistory->result_array();
        $all[] = $resultservices;
      }

      $out["count"]   = count($all);
      $out["result"]  = $all;

      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

    function trackAndTraceBrg_post($input,$branch, $encode) {
      header('Content-Type: application/json');
      $this->auth_basic();
      $devdb                = $this->db;
      $data                 = $input["data"];
      $all                  = [];

      foreach ($data as $data) {
        $sqlHistory           = $devdb->where("HIST_SI",$data["NO_SI"])->where('HIST_BRANCH_ID', $data["BRANCH_ID"])->get("TH_HISTORY_BRG");
        $resultservices       = $sqlHistory->result_array();
        $all[] = $resultservices;
      }

      $out["count"]   = count($all);
      $out["result"]  = $all;

      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }
}
?>
