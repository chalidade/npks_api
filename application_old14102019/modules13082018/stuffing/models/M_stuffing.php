<?php
class M_stuffing extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

  function getContainer(){
    $branch_id = $this->session->userdata('USER_BRANCH');

    $this->db->select('A.STUFF_DTL_ID, A.STUFF_DTL_CONT')
             ->from('TX_REQ_STUFF_DTL A')
             ->join('TX_REQ_STUFF_HDR B','A.STUFF_DTL_HDR_ID = B.STUFF_ID')
             ->where('B.STUFF_BRANCH_ID',$branch_id)
						 ->where('A.STUFF_DTL_ACTIVE','Y')
             ->where_not_in('A.STUFF_DTL_STATUS',array(2));
    $data = $this->db->get()->result_array();
    return $data;
  }

  function getContainerById(){
    $branch_id = $this->session->userdata('USER_BRANCH');
    $id = $this->input->post('id');
    $this->db->select('A.STUFF_DTL_HDR_ID, A.STUFF_DTL_ID, A.STUFF_DTL_CONT, A.STUFF_DTL_CONT_STATUS, A.STUFF_DTL_CONT_SIZE, A.STUFF_DTL_COMMODITY, B.STUFF_NO, to_char(B.STUFF_CREATE_DATE,\'DD/MM/YYYY\') STUFF_DATE')
             ->from('TX_REQ_STUFF_DTL A')
             ->join('TX_REQ_STUFF_HDR B','A.STUFF_DTL_HDR_ID = B.STUFF_ID')
             ->where('B.STUFF_BRANCH_ID',$branch_id)
             ->where_not_in('A.STUFF_DTL_STATUS',array(2))
             ->where('A.STUFF_DTL_ID',$id);
    $data = $this->db->get()->row_array();
    return $data;
  }

	function cekStuffingRun(){
		$hdrId = $this->input->post('hdrId');
		$dtlId = $this->input->post('dtlId');
		$status = $this->db->select('REAL_STUFF_STATUS')
						 ->where('REAL_STUFF_HDR_ID',$hdrId)
						 ->where('REAL_STUFF_DTL_ID',$dtlId)
						 ->order_by('REAL_STUFF_ID','DESC')
						 ->limit(1)
						 ->get('TX_REAL_STUFF')
						 ->row_array()['REAL_STUFF_STATUS'];
		if($status == null)
			$status = 0;

		return (int)$status;
	}

	function setStuffing(){
			$branch_id = $this->session->userdata('USER_BRANCH');
			$user = $this->session->userdata('isId');
			$message = 'SUKSES';

			$this->db->trans_start();

			$equipment = 0;
			if($_POST['equipment'] != null){
				$equipment = 1;
			}

			$arrData = array(
				'REAL_STUFF_HDR_ID' => $_POST['STUFF_HDR_ID'],
				'REAL_STUFF_DTL_ID' => $_POST['STUFF_DTL_ID'],
				'REAL_STUFF_CONT' => $_POST['STUFF_CONT'],
				'REAL_STUFF_STATUS' => $_POST['status'],
				'REAL_STUFF_NOREQ' => $_POST['NO_REQ'],
				'REAL_STUFF_MECHANIC_TOOLS' => $equipment,
				'REAL_STUFF_BRANCH_ID' => $branch_id,
				'REAL_STUFF_BY' => $user
		 );
			$this->db->insert('TX_REAL_STUFF',$arrData);
			$this->db->set('STUFF_DTL_STATUS',$_POST['status'])
							 ->where('STUFF_DTL_HDR_ID',$_POST['STUFF_HDR_ID'])
							 ->where('STUFF_DTL_ID',$_POST['STUFF_DTL_ID'])
							 ->update('TX_REQ_STUFF_DTL');
			// $data = $this->db->where('STUFF_DTL_HDR_ID',$_POST['STUFF_HDR_ID'])
			// 		 		 ->where('STUFF_DTL_STATUS !=', 2)
			// 				 ->from('TX_REQ_STUFF_DTL')
			// 				 ->count_all_results();
			// if($data == 0){
			// 	$this->db->set('STUFF_STATUS',2)
			// 					 ->where('STUFF_ID',$_POST['STUFF_HDR_ID'])
			// 					 ->update('TX_REQ_STUFF_HDR');
			// }

			if($_POST['status'] == 2){

				// $this->db->set('STUFF_DTL_ACTIVE','T')->where('STUFF_DTL_CONT',$_POST['STUFF_CONT'])->where('STUFF_DTL_HDR_ID',$_POST['STUFF_HDR_ID'])->from('TX_REQ_STUFF_DTL');
				$this->db->query("UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_ACTIVE = 'T' WHERE STUFF_DTL_CONT = '".$_POST['STUFF_CONT']."' AND STUFF_DTL_HDR_ID = ".$_POST['STUFF_HDR_ID']);

				$cek_jumlah_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = ".$_POST['STUFF_HDR_ID'])->row_array()['JML'];
				$cek_jumlah_complete = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_ACTIVE = 'T' AND STUFF_DTL_HDR_ID = ".$_POST['STUFF_HDR_ID'])->row_array()['JML'];

				if($cek_jumlah_dtl == $cek_jumlah_complete){
							$this->db->set('STUFF_STATUS',2)->where('STUFF_NO',$_POST['NO_REQ'])->update('TX_REQ_STUFF_HDR');
				}
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE)
			{
					$message = 'ERROR';
			}

			return $message;

		}

}
