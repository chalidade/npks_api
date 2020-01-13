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
		$reqId = $this->input->post('reqId');
		$backdate = $this->input->post('DATE_REALIZATION');
		$time = $this->input->post('TIME_REALIZATION');
		$status = $this->db->select('REAL_STRIP_STATUS')
						 //->where('REAL_STRIP_HDR_ID',$hdrId)
						 ->where('REAL_STRIP_CONT',$dtlId)
						 ->where('REAL_STRIP_ACTIVE','Y')
						 ->order_by('REAL_STRIP_ID','DESC')
						 ->limit(1)
						 ->get('TX_REAL_STRIP')
						 ->row_array()['REAL_STRIP_STATUS'];
		if($status == null){
			$status = 0;
		}

			if($backdate == 0) {
				$now = date('Y-m-d');
				$now_time = date('Y-m-d H:i');
			}
			else{
				$now = date_format(date_create($backdate),"Y-m-d");
				$backdate = $backdate.' '.$time;
				$now_time =  date_format(date_create($backdate),"Y-m-d H:i");
			}

			$rec_date = $this->db->select("TO_CHAR(STRIP_DTL_END_STRIP_PLAN,'YYYY-MM-DD') PAID, TO_CHAR(STRIP_CREATE_DATE,'YYYY-MM-DD HH24:MI') H_DATE")
							->from('TX_REQ_STRIP_DTL A')
							->join('TX_REQ_STRIP_HDR B','B.STRIP_ID = A.STRIP_DTL_HDR_ID')
							//->where('B.STRIP_ID',$hdrId)
							->where('A.STRIP_DTL_CONT',$dtlId)
							->where('A.STRIP_DTL_ACTIVE','Y')
							->get()->row_array();
			$paid = $rec_date['PAID'];
			$nota_date = $rec_date['H_DATE'];
			$paid=date_format(date_create($paid),"Y-m-d");
			$nota_date=date_format(date_create($nota_date),"Y-m-d H:i");

			$nota = ($now_time < $nota_date)? 1 : 0;
			$exp = ($now > $paid)? 1 : 0;

		$return = array('status' => (int)$status, 'paid' => $exp, 'nota' => 0);

		return $return;
	}

	function setStripping(){
			$branch_id = $this->session->userdata('USER_BRANCH');
			$user = $this->session->userdata('isId');
			$message = 'SUKSES';
			$noreq_start = '';
			$this->db->trans_start();

			$equipment = 0;
			if($_POST['equipment'] != null){
				$equipment = 1;
			}

			// get cont loc
			$cont_loc = $this->db->query("SELECT REAL_YARD_YBC_ID FROM (
					SELECT MAX(REAL_YARD_ID) OVER () AS MAX_ID, REAL_YARD_ID, REAL_YARD_YBC_ID FROM TX_REAL_YARD WHERE REAL_YARD_CONT = '".$_POST['STRIP_CONT']."' AND REAL_YARD_BRANCH_ID  = $branch_id
			)X WHERE X.MAX_ID = X.REAL_YARD_ID")->row_array()['REAL_YARD_YBC_ID'];

			$cont_counter = $this->db->select('CONTAINER_COUNTER')->where('CONTAINER_NO',$_POST['STRIP_CONT'])->where('CONTAINER_BRANCH_ID',$branch_id)->get('TM_CONTAINER')->row_array()['CONTAINER_COUNTER'];

			if($_POST['status'] == 2){
				$noreq_start = $this->db->select('REAL_STRIP_NOREQ')->where('REAL_STRIP_CONT',$_POST['STRIP_CONT'])->where('REAL_STRIP_ACTIVE','Y')->get('TX_REAL_STRIP')->row_array()['REAL_STRIP_NOREQ'];
			}

			$arrData = array(
				'REAL_STRIP_HDR_ID' => $_POST['STRIP_HDR_ID'],
				'REAL_STRIP_DTL_ID' => $_POST['STRIP_DTL_ID'],
				'REAL_STRIP_CONT' => $_POST['STRIP_CONT'],
				'REAL_STRIP_STATUS' => $_POST['status'],
				'REAL_STRIP_NOREQ' => $_POST['NO_REQ'],
				'REAL_STRIP_MECHANIC_TOOLS' => $equipment,
				'REAL_STRIP_BRANCH_ID' => $branch_id,
				'REAL_STRIP_BY' => $user,
				'REAL_STRIP_COUNTER' => $cont_counter,
				'REAL_STRIP_NOREQ_START' => $noreq_start,
				'REAL_STRIP_YBC_ID' => $cont_loc
		 );
		 $tgl_real = null;
		 if($_POST['DATE_REALIZATION'] != 0){
				$tgl_real = $_POST['DATE_REALIZATION'].' '.$_POST['TIME_REALIZATION'];
				$reason = $_POST['REASON'].'-';
				$this->db->set('REAL_STRIP_HDR_ID', $_POST['STRIP_HDR_ID']);
				$this->db->set('REAL_STRIP_DTL_ID', $_POST['STRIP_DTL_ID']);
				$this->db->set('REAL_STRIP_CONT', $_POST['STRIP_CONT']);
				$this->db->set('REAL_STRIP_STATUS', $_POST['status']);
				$this->db->set('REAL_STRIP_NOREQ', $_POST['NO_REQ']);
				$this->db->set('REAL_STRIP_MECHANIC_TOOLS', $equipment);
				$this->db->set('REAL_STRIP_BRANCH_ID', $branch_id);
				$this->db->set('REAL_STRIP_BY', $user);
				$this->db->set('REAL_STRIP_DATE',"to_date('$tgl_real','DD-MM-YYYY HH24:MI')",false);
				$this->db->set('REAL_STRIP_BACKDATE', $reason);
				$this->db->set('REAL_STRIP_COUNTER', $cont_counter);
				$this->db->set('REAL_STRIP_NOREQ_START', $noreq_start);
				$this->db->set('REAL_STRIP_YBC_ID', $cont_loc);
				$this->db->insert('TX_REAL_STRIP');
		 }
		 else{
			 $this->db->insert('TX_REAL_STRIP',$arrData);
		 }

			$this->db->set('STRIP_DTL_STATUS',$_POST['status'])
							 ->where('STRIP_DTL_HDR_ID',$_POST['STRIP_HDR_ID'])
							 ->where('STRIP_DTL_ID',$_POST['STRIP_DTL_ID'])
							 ->update('TX_REQ_STRIP_DTL');
			$data = $this->db->where('STRIP_DTL_HDR_ID',$_POST['STRIP_HDR_ID'])
					 		 ->where('STRIP_DTL_STATUS !=', 2)
							 ->from('TX_REQ_STRIP_DTL')
							 ->count_all_results();
			if($data == 0){
				$this->db->set('STRIP_STATUS',2)
								 ->where('STRIP_ID',$_POST['STRIP_HDR_ID'])
								 ->update('TX_REQ_STRIP_HDR');
			}

			if($_POST['status'] == 2){

				$this->db->query("UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_STATUS = 2 WHERE STRIP_DTL_CONT = '".$_POST['STRIP_CONT']."' AND STRIP_DTL_HDR_ID = ".$_POST['STRIP_HDR_ID']);
				$this->db->query("UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_ACTIVE = 'T' WHERE STRIP_DTL_CONT = '".$_POST['STRIP_CONT']."' AND STRIP_DTL_HDR_ID = ".$_POST['STRIP_HDR_ID']);
				$this->db->query("UPDATE TX_REAL_STRIP SET REAL_STRIP_ACTIVE = 'T' WHERE REAL_STRIP_CONT = '".$_POST['STRIP_CONT']."' ");

				$cek_jumlah_dtl = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_HDR_ID = ".$_POST['STRIP_HDR_ID'])->row_array()['JML'];
				$cek_jumlah_complete = $this->db->query("SELECT COUNT(*) JML FROM TX_REQ_STRIP_DTL WHERE STRIP_DTL_STATUS = 2 AND STRIP_DTL_HDR_ID = ".$_POST['STRIP_HDR_ID'])->row_array()['JML'];

				if($cek_jumlah_dtl == $cek_jumlah_complete){
				      $this->db->set('STRIP_STATUS',2)->where('STRIP_NO',$_POST['NO_REQ'])->update('TX_REQ_STRIP_HDR');
				}

				// gate request date
				$req_date = $this->db->query("SELECT TO_CHAR(REQ_DATE,'MM/DD/YYYY HH24:MI:SS') REQ_DATE FROM (
											SELECT STRIP_NO REQ_NO, STRIP_CREATE_DATE REQ_DATE, STRIP_BRANCH_ID BRANCH FROM TX_REQ_STRIP_HDR)
											WHERE BRANCH = ".$branch_id." AND REQ_NO = '".$_POST['NO_REQ']."'")->row_array()['REQ_DATE'];

			 $cont_in_yard = $this->db->query("SELECT * FROM (SELECT A.REAL_YARD_ID, A.REAL_YARD_CONT, A.REAL_YARD_CONT_SIZE, A.REAL_YARD_CONT_TYPE, A.REAL_YARD_CONT_STATUS, A.REAL_YARD_TIER TIER_, B.YBC_SLOT SLOT_, B.YBC_ROW ROW_, C.BLOCK_NAME BLOCK_, D.YARD_NAME YARD, C.BLOCK_ID, MAX(A.REAL_YARD_ID) over () as MAX_ID
				FROM TX_REAL_YARD A
				JOIN TX_YARD_BLOCK_CELL B ON A.REAL_YARD_YBC_ID = B.YBC_ID
				JOIN TM_BLOCK C ON B.YBC_BLOCK_ID = C.BLOCK_ID
				JOIN TM_YARD D ON B.YBC_YARD_ID = D.YARD_ID
				WHERE B.YBC_ACTIVE = 'Y' AND C.BLOCK_ACTIVE	= 'Y' AND A.REAL_YARD_STATUS = 1 AND A.REAL_YARD_USED = 1 AND A.REAL_YARD_CONT = '".$this->input->post('STRIP_CONT')."' AND A.REAL_YARD_BRANCH_ID = $branch_id) T
				WHERE T.REAL_YARD_ID = T.MAX_ID")->row_array();

				$size40 = '';
				if($cont_in_yard['REAL_YARD_CONT_SIZE'] == 40){
					$cont_in_yard40 = $this->db->query("SELECT * FROM (SELECT A.REAL_YARD_ID, A.REAL_YARD_CONT, B.YBC_SLOT SLOT_, MIN(A.REAL_YARD_ID) over () as MIN_ID
	 				FROM TX_REAL_YARD A
	 				JOIN TX_YARD_BLOCK_CELL B ON A.REAL_YARD_YBC_ID = B.YBC_ID
	 				JOIN TM_BLOCK C ON B.YBC_BLOCK_ID = C.BLOCK_ID
	 				JOIN TM_YARD D ON B.YBC_YARD_ID = D.YARD_ID
	 				WHERE B.YBC_ACTIVE = 'Y' AND C.BLOCK_ACTIVE	= 'Y' AND A.REAL_YARD_STATUS = 1 AND A.REAL_YARD_USED = 1 AND A.REAL_YARD_CONT = '".$this->input->post('STRIP_CONT')."' AND A.REAL_YARD_BRANCH_ID = $branch_id) T
	 				WHERE T.REAL_YARD_ID = T.MIN_ID")->row_array();
					$size40 = $cont_in_yard40['SLOT_'];
				}

				 //update status container menjadi empty
 				// $this->db->set('REAL_YARD_CONT_STATUS','MTY')->where('REAL_YARD_ID',$cont_in_yard['REAL_YARD_ID'])->update('TX_REAL_YARD');
				$this->db->set('REAL_YARD_CONT_STATUS','MTY')
								 ->where('REAL_YARD_CONT',$this->input->post('STRIP_CONT'))
								 ->where('REAL_YARD_STATUS',1)
								 ->where('REAL_YARD_USED',1)
								 ->where('REAL_YARD_BRANCH_ID',$branch_id)
								 ->update('TX_REAL_YARD');

				if($_POST['DATE_REALIZATION'] != 0){
					// insert history container
					$tgl_real = $_POST['DATE_REALIZATION'].' '.$_POST['TIME_REALIZATION'];
					$real_backdate = str_replace('-','/',$tgl_real);
					$this->db->query("CALL ADD_HISTORY_CONTAINER(
								'".$_POST['STRIP_CONT']."',
								'".$_POST['NO_REQ']."',
								'".$req_date."',
								'".$cont_in_yard['REAL_YARD_CONT_SIZE']."',
								'".$cont_in_yard['REAL_YARD_CONT_TYPE']."',
								'MTY',
								'".$cont_in_yard['YARD']."',
								'".$cont_in_yard['BLOCK_']."',
								".$cont_in_yard['SLOT_'].",
								".$cont_in_yard['ROW_'].",
								".$cont_in_yard['TIER_'].",
								2,
								'Realisasi Stripping',
								'".$real_backdate."',
								'".$size40."',
								".$branch_id.",
								'NULL',
								".$user.")");
				}
				else{
					// insert history container
					$this->db->query("CALL ADD_HISTORY_CONTAINER(
								'".$_POST['STRIP_CONT']."',
								'".$_POST['NO_REQ']."',
								'".$req_date."',
								'".$cont_in_yard['REAL_YARD_CONT_SIZE']."',
								'".$cont_in_yard['REAL_YARD_CONT_TYPE']."',
								'MTY',
								'".$cont_in_yard['YARD']."',
								'".$cont_in_yard['BLOCK_']."',
								".$cont_in_yard['SLOT_'].",
								".$cont_in_yard['ROW_'].",
								".$cont_in_yard['TIER_'].",
								2,
								'Realisasi Stripping',
								NULL,
								'".$size40."',
								".$branch_id.",
								NULL,
								".$user.")");
				}

			}
			else{
				 $this->db->set('STRIP_STATUS',1)->where('STRIP_NO',$_POST['NO_REQ'])->update('TX_REQ_STRIP_HDR');
				 $this->db->query("UPDATE TX_REQ_STRIP_DTL SET STRIP_DTL_STATUS = 1 WHERE STRIP_DTL_CONT = '".$_POST['STRIP_CONT']."' AND STRIP_DTL_HDR_ID = ".$_POST['STRIP_HDR_ID']);
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE)
			{
					$message = 'ERROR';
			}

			return $message;

		}

}
