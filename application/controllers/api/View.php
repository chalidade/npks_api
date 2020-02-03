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

      $out["count"]   = count($newdt);
      $out["result"]  = $newdt;

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

      $out["count"]   = count($newdt);
      $out["result"]  = $newdt;

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

      $out["count"]   = count($newdt);
      $out["result"]  = $newdt;

      if ($encode == "true") {
        $result["result"] = base64_encode(json_encode($out));
        echo json_encode($result);
      } else {
        echo json_encode($out);
      }
    }

}
?>
