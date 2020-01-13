<?php
class M_tally extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

  function setTally(){
    $branch_id =  $this->session->USER_BRANCH;
    $user = $this->session->userdata('isId');
    $demage = 'N';
    $demage_type = '';
    $message = 'SUKSES';

    $this->db->trans_start();

    if($_POST['CONTAINER_DEMAGE_CHECK'] != 'null'){
      $demage = 'Y';
      $demage_type =$_POST['CONTAINER_DEMAGE'];
    }
    $arrData = array(
      'REALISASI_NO' => $_POST['PLAN_NO'],
      'REALISASI_CONT' => $_POST['CONTAINER_NO'],
      'REALISASI_TIPE' => $_POST['TYPE'],
      'REALISASI_STATUS' => $_POST['status'],
      'REALISASI_EQUIPMENT_JOB_ID' => $_POST['EQUIPMENT_ID'],
      'REALISASI_BRANCH_ID' => $branch_id,
      'REALISASI_BY' => $user,
      'REALISASI_CONT_DAMAGE' => $demage,
      'REALISASI_CONT_DAMAGE_TYPE' => $demage_type,
   );
    $this->db->insert('TX_CONV_REALISASI',$arrData);

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE)
    {
        $message = 'ERROR';
    }

    return $message;

  }

	function getReasonStatus(){
	    $sql = "SELECT A.REFF_ID, A.REFF_NAME, A.REFF_ORDER FROM TM_REFF A
	      JOIN TR_REFF B ON A.REFF_TR_ID = B.REFF_ID
	          WHERE B.REFF_ID = 2 AND A.REFF_ID NOT IN(1,2)";
	    $data = $this->db->query($sql)->result_array();
	    return $data;
	}

	function setTallyHold(){
    $branch_id =  $this->session->USER_BRANCH;
    $user = $this->session->userdata('isId');
    $demage = 'N';
    $demage_type = '';
    $message = 'SUKSES';

    $this->db->trans_start();

    if($_POST['CONTAINER_DEMAGE_CHECK'] != 'null'){
      $demage = 'Y';
      $demage_type =$_POST['CONTAINER_DEMAGE'];
    }
    $arrData = array(
      'REALISASI_NO' => $_POST['PLAN_NO'],
      'REALISASI_CONT' => $_POST['CONTAINER_NO'],
      'REALISASI_TIPE' => $_POST['TYPE'],
      'REALISASI_STATUS' => $_POST['status'],
      'REALISASI_EQUIPMENT_JOB_ID' => $_POST['EQUIPMENT_ID'],
      'REALISASI_BRANCH_ID' => $branch_id,
      'REALISASI_BY' => $user,
      'REALISASI_CONT_DAMAGE' => $demage,
      'REALISASI_CONT_DAMAGE_TYPE' => $demage_type,
			'REALISASI_MARK' => $_POST['note']
   );
    $this->db->insert('TX_CONV_REALISASI',$arrData);

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE)
    {
        $message = 'ERROR';
    }

    return $message;

  }

	function ceTallyRun(){
		$planNo = $this->input->post('planNo');
		$conNo = $this->input->post('conNo');
		$status = $this->db->select_max('REALISASI_STATUS')
						 ->where('REALISASI_NO',$planNo)
						 ->where('REALISASI_CONT',$conNo)
						 ->get('TX_CONV_REALISASI')
						 ->row()->REALISASI_STATUS;

		return (int)$status;
	}



}
