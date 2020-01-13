<?php
class M_stripping extends CI_Model {
	public function __construct(){
		$this->load->database();
	}

  function getContainer(){
    $branch_id = $this->session->userdata('USER_BRANCH');

    $this->db->select('A.STRIP_DTL_ID, A.STRIP_DTL_CONT')
             ->from('TX_REQ_STRIP_DTL A')
             ->join('TX_REQ_STRIP_HDR B','A.STRIP_DTL_HDR_ID = B.STRIP_ID')
             ->where('B.STRIP_BRANCH_ID',$branch_id)
						 ->where('A.STRIP_DTL_ACTIVE','Y')
             ->where_not_in('A.STRIP_DTL_STATUS',array(2));
    $data = $this->db->get()->result_array();
    return $data;
  }

  function getContainerById(){
    $branch_id = $this->session->userdata('USER_BRANCH');
    $id = $this->input->post('id');
    $this->db->select('A.STRIP_DTL_HDR_ID, A.STRIP_DTL_ID, A.STRIP_DTL_CONT, A.STRIP_DTL_LOCATION, A.STRIP_DTL_CONT_STATUS, A.STRIP_DTL_CONT_SIZE, A.STRIP_DTL_COMMODITY, B.STRIP_NO, to_char(B.STRIP_CREATE_DATE,\'DD/MM/YYYY\') STRIP_DATE')
             ->from('TX_REQ_STRIP_DTL A')
             ->join('TX_REQ_STRIP_HDR B','A.STRIP_DTL_HDR_ID = B.STRIP_ID')
             ->where('B.STRIP_BRANCH_ID',$branch_id)
             ->where_not_in('A.STRIP_DTL_STATUS',array(2))
             ->where('A.STRIP_DTL_ID',$id);
    $data = $this->db->get()->row_array();
    return $data;
  }

	function cekStrippingRun(){
		$hdrId = $this->input->post('hdrId');
		$dtlId = $this->input->post('dtlId');
		$status = $this->db->select('REAL_STRIP_STATUS')
						 ->where('REAL_STRIP_HDR_ID',$hdrId)
						 ->where('REAL_STRIP_DTL_ID',$dtlId)
						 ->order_by('REAL_STRIP_ID','DESC')
						 ->limit(1)
						 ->get('TX_REAL_STRIP')
						 ->row_array()['REAL_STRIP_STATUS'];
		if($status == null)
			$status = 0;

		return (int)$status;
	}

	function setStripping(){
			$branch_id = $this->session->userdata('USER_BRANCH');
			$user = $this->session->userdata('isId');
			$message = 'SUKSES';

			$this->db->trans_start();

			$equipment = 0;
			if($_POST['equipment'] != null){
				$equipment = 1;
			}

			$arrData = array(
				'REAL_STRIP_HDR_ID' => $_POST['STRIP_HDR_ID'],
				'REAL_STRIP_DTL_ID' => $_POST['STRIP_DTL_ID'],
				'REAL_STRIP_CONT' => $_POST['STRIP_CONT'],
				'REAL_STRIP_STATUS' => $_POST['status'],
				'REAL_STRIP_NOREQ' => $_POST['NO_REQ'],
				'REAL_STRIP_MECHANIC_TOOLS' => $equipment,
				'REAL_STRIP_BRANCH_ID' => $branch_id,
				'REAL_STRIP_BY' => $user
		 );
			$this->db->insert('TX_REAL_STRIP',$arrData);
			$this->db->set('STRIP_DTL_STATUS',$_POST['status'])
							 ->where('STRIP_DTL_HDR_ID',$_POST['STRIP_HDR_ID'])
							 ->where('STRIP_DTL_ID',$_POST['STRIP_DTL_ID'])
							 ->update('TX_REQ_STRIP_DTL');
			// $data = $this->db->where('STRIP_DTL_HDR_ID',$_POST['STRIP_HDR_ID'])
			// 		 		 ->where('STRIP_DTL_STATUS !=', 2)
			// 				 ->from('TX_REQ_STRIP_DTL')
			// 				 ->count_all_results();
			// if($data == 0){
			// 	$this->db->set('STRIP_STATUS',2)
			// 					 ->where('STRIP_ID',$_POST['STRIP_HDR_ID'])
			// 					 ->update('TX_REQ_STRIP_HDR');
			// }

			if($_POST['status'] == 2){

				// $this->db->set('STRIP_DTL_ACTIVE','T')->where('STRIP_DTL_CONT',$_POST['STRIP_CONT'])->where('STRIP_DTL_HDR_ID',$_POST['STRIP_HDR_ID'])->from('TX_REQ_STRIP_DTL');
				//
				// $cek_jumlah_dtl = $this->db->where('STRIP_DTL_HDR_ID',$_POST['STRIP_HDR_ID'])->from('TX_REQ_STRIP_DTL')->count_all_results();
				// $cek_jumlah_complete = $this->db->where('STRIP_DTL_HDR_ID',$_POST['STRIP_HDR_ID'])->where('STRIP_DTL_ACTIVE','T')->from('TX_REQ_STRIP_DTL')->count_all_results();
				//
			  // if($cek_jumlah_dtl == $cek_jumlah_complete){
			  //       $this->db->set('STRIP_STATUS',2)->where('STRIP_NO',$_POST['NO_REQ'])->update('TX_REQ_STRIP_HDR');
			  // }

				$this->db->query("UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_ACTIVE = 'T' WHERE STRIP_DTL_CONT = '".$_POST['STRIP_CONT']."' AND STRIP_DTL_HDR_ID = ".$_POST['STRIP_HDR_ID']);

				$cek_jumlah_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = ".$_POST['STRIP_HDR_ID'])->row_array()['JML'];
				$cek_jumlah_complete = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_ACTIVE = 'T' AND STRIP_DTL_HDR_ID = ".$_POST['STRIP_HDR_ID'])->row_array()['JML'];

				if($cek_jumlah_dtl == $cek_jumlah_complete){
				      $this->db->set('STRIP_STATUS',2)->where('STRIP_NO',$_POST['NO_REQ'])->update('TX_REQ_STRIP_HDR');
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
