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
		$reqId = $this->input->post('reqId');
		$backdate = $this->input->post('DATE_REALIZATION');
		$status = $this->db->select('REAL_STUFF_STATUS')
						 //->where('REAL_STUFF_HDR_ID',$hdrId)
						 //->where('REAL_STUFF_DTL_ID',$dtlId)
						 ->where('REAL_STUFF_CONT',$dtlId)
						 ->where('REAL_STUFF_ACTIVE','Y')
						 ->order_by('REAL_STUFF_ID','DESC')
						 ->limit(1)
						 ->get('TX_REAL_STUFF')
						 ->row_array()['REAL_STUFF_STATUS'];
		if($status == null){
			$status = 0;
		}

		if($backdate == 0) {
			$now = date('Y-m-d');
		}
		else{
			$now = date_format(date_create($backdate),"Y-m-d");
		}

			$paid = $this->db->select("TO_CHAR(STUFF_DTL_END_STUFF_PLAN,'YYYY-MM-DD') PAID")
							->from('TX_REQ_STUFF_DTL A')
							->join('TX_REQ_STUFF_HDR B','B.STUFF_ID = A.STUFF_DTL_HDR_ID')
							//->where('B.STUFF_ID',$hdrId)
							//->where('A.STUFF_DTL_ID',$dtlId)
							->where('A.STUFF_DTL_CONT',$dtlId)
							->where('A.STUFF_DTL_ACTIVE','Y')
							->get()->row_array()['PAID'];
			$paid=date_format(date_create($paid),"Y-m-d");
			$exp = ($now > $paid)? 1 : 0;

		$return = array('status' => (int)$status, 'paid' => $exp);
		return $return;
	}

	function setStuffing(){
			$branch_id = $this->session->userdata('USER_BRANCH');
			$user = $this->session->userdata('isId');
			$message = 'SUKSES';
			$noreq_start = '';
			$this->db->trans_start();

			// get cont loc
			$cont_loc = $this->db->query("SELECT REAL_YARD_YBC_ID FROM (
					SELECT MAX(REAL_YARD_ID) OVER () AS MAX_ID, REAL_YARD_ID, REAL_YARD_YBC_ID FROM TX_REAL_YARD WHERE REAL_YARD_CONT = '".$_POST['STUFF_CONT']."' AND REAL_YARD_BRANCH_ID  = $branch_id
			)X WHERE X.MAX_ID = X.REAL_YARD_ID")->row_array()['REAL_YARD_YBC_ID'];

			//check counter container
			$cont_counter = $this->db->select('CONTAINER_COUNTER')->where('CONTAINER_NO',$_POST['STUFF_CONT'])->where('CONTAINER_BRANCH_ID',$branch_id)->get('TM_CONTAINER')->row_array()['CONTAINER_COUNTER'];

			if($_POST['status'] == 2){
				$noreq_start = $this->db->select('REAL_STUFF_NOREQ')->where('REAL_STUFF_CONT',$_POST['STUFF_CONT'])->where('REAL_STUFF_ACTIVE','Y')->get('TX_REAL_STUFF')->row_array()['REAL_STUFF_NOREQ'];
			}

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
				'REAL_STUFF_BY' => $user,
				'REAL_STUFF_COUNTER' => $cont_counter,
				'REAL_STUFF_NOREQ_START' => $noreq_start,
				'REAL_STUFF_YBC_ID' => $cont_loc
		 );
		 $tgl_real = 'NULL';
		 if($_POST['DATE_REALIZATION'] != 0){
				$tgl_real = $_POST['DATE_REALIZATION'].' '.$_POST['TIME_REALIZATION'];
				$this->db->set('REAL_STUFF_HDR_ID', $_POST['STUFF_HDR_ID']);
				$this->db->set('REAL_STUFF_DTL_ID', $_POST['STUFF_DTL_ID']);
				$this->db->set('REAL_STUFF_CONT', $_POST['STUFF_CONT']);
				$this->db->set('REAL_STUFF_STATUS', $_POST['status']);
				$this->db->set('REAL_STUFF_NOREQ', $_POST['NO_REQ']);
				$this->db->set('REAL_STUFF_MECHANIC_TOOLS', $equipment);
				$this->db->set('REAL_STUFF_BRANCH_ID', $branch_id);
				$this->db->set('REAL_STUFF_BY', $user);
				$this->db->set('REAL_STUFF_DATE',"to_date('$tgl_real','dd-mm-yyyy hh24:mi')",false);
				$this->db->set('REAL_STUFF_BACKDATE', $_POST['REASON']);
				$this->db->set('REAL_STUFF_COUNTER', $cont_counter);
				$this->db->set('REAL_STUFF_NOREQ_START', $noreq_start);
				$this->db->set('REAL_STUFF_YBC_ID', $cont_loc);
				$this->db->insert('TX_REAL_STUFF');
		 }
		 else{
			 $this->db->insert('TX_REAL_STUFF',$arrData);
		 }

			$this->db->set('STUFF_DTL_STATUS',$_POST['status'])
							 ->where('STUFF_DTL_HDR_ID',$_POST['STUFF_HDR_ID'])
							 ->where('STUFF_DTL_ID',$_POST['STUFF_DTL_ID'])
							 ->update('TX_REQ_STUFF_DTL');

			if($_POST['status'] == 2){

				$this->db->query("UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_STATUS = 2, STUFF_DTL_ACTIVE = 'T' WHERE STUFF_DTL_CONT = '".$_POST['STUFF_CONT']."' AND STUFF_DTL_HDR_ID = ".$_POST['STUFF_HDR_ID']);
				$this->db->query("UPDATE TX_REAL_STUFF SET REAL_STUFF_ACTIVE = 'T' WHERE REAL_STUFF_CONT = '".$_POST['STUFF_CONT']."' ");

				$cek_jumlah_dtl = $this->db->query("SELECT COUNT(1) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_HDR_ID = ".$_POST['STUFF_HDR_ID'])->row_array()['JML'];
				$cek_jumlah_complete = $this->db->query("SELECT COUNT(1) JML FROM TX_REQ_STUFF_DTL WHERE STUFF_DTL_STATUS = 2 AND STUFF_DTL_HDR_ID = ".$_POST['STUFF_HDR_ID'])->row_array()['JML'];

				if($cek_jumlah_dtl == $cek_jumlah_complete){
							$this->db->set('STUFF_STATUS',2)->where('STUFF_NO',$_POST['NO_REQ'])->update('TX_REQ_STUFF_HDR');
				}
			}

			if($_POST['status'] == 1){

				// update status dtl menjadi start
				$this->db->query("UPDATE TX_REQ_STUFF_DTL SET STUFF_DTL_STATUS = 1 WHERE STUFF_DTL_CONT = '".$_POST['STUFF_CONT']."' AND STUFF_DTL_HDR_ID = ".$_POST['STUFF_HDR_ID']);

				// gate request date
				$req_date = $this->db->query("SELECT TO_CHAR(REQ_DATE,'MM/DD/YYYY HH24:MI:SS') REQ_DATE FROM (
											SELECT STUFF_NO REQ_NO, STUFF_CREATE_DATE REQ_DATE, STUFF_BRANCH_ID BRANCH FROM TX_REQ_STUFF_HDR)
											WHERE BRANCH = ".$branch_id." AND REQ_NO = '".$_POST['NO_REQ']."'")->row_array()['REQ_DATE'];

			 $cont_in_yard = $this->db->query("SELECT * FROM (SELECT A.REAL_YARD_ID, A.REAL_YARD_CONT, A.REAL_YARD_CONT_SIZE, A.REAL_YARD_CONT_TYPE, A.REAL_YARD_CONT_STATUS, A.REAL_YARD_TIER TIER_, B.YBC_SLOT SLOT_, B.YBC_ROW ROW_, C.BLOCK_NAME BLOCK_, D.YARD_NAME YARD, C.BLOCK_ID, MAX(A.REAL_YARD_ID) over () as MAX_ID
				FROM TX_REAL_YARD A
				JOIN TX_YARD_BLOCK_CELL B ON A.REAL_YARD_YBC_ID = B.YBC_ID
				JOIN TM_BLOCK C ON B.YBC_BLOCK_ID = C.BLOCK_ID
				JOIN TM_YARD D ON B.YBC_YARD_ID = D.YARD_ID
				WHERE B.YBC_ACTIVE = 'Y' AND C.BLOCK_ACTIVE	= 'Y' AND A.REAL_YARD_STATUS = 1 AND A.REAL_YARD_USED = 1 AND A.REAL_YARD_CONT = '".$this->input->post('STUFF_CONT')."' AND A.REAL_YARD_BRANCH_ID = $branch_id) T
				WHERE T.REAL_YARD_ID = T.MAX_ID")->row_array();

				$size40 = '';
				if($cont_in_yard['REAL_YARD_CONT_SIZE'] == 40){
					$cont_in_yard40 = $this->db->query("SELECT * FROM (SELECT A.REAL_YARD_ID, A.REAL_YARD_CONT, B.YBC_SLOT SLOT_, MIN(A.REAL_YARD_ID) over () as MIN_ID
	 				FROM TX_REAL_YARD A
	 				JOIN TX_YARD_BLOCK_CELL B ON A.REAL_YARD_YBC_ID = B.YBC_ID
	 				JOIN TM_BLOCK C ON B.YBC_BLOCK_ID = C.BLOCK_ID
	 				JOIN TM_YARD D ON B.YBC_YARD_ID = D.YARD_ID
	 				WHERE B.YBC_ACTIVE = 'Y' AND C.BLOCK_ACTIVE	= 'Y' AND A.REAL_YARD_STATUS = 1 AND A.REAL_YARD_USED = 1 AND A.REAL_YARD_CONT = '".$this->input->post('STUFF_CONT')."' AND A.REAL_YARD_BRANCH_ID = $branch_id) T
	 				WHERE T.REAL_YARD_ID = T.MIN_ID")->row_array();
					$size40 = $cont_in_yard40['SLOT_'];
				}

				$this->db->set('STUFF_STATUS',1)->where('STUFF_NO',$_POST['NO_REQ'])->update('TX_REQ_STUFF_HDR');
				//update status container menjadi full
				$this->db->set('REAL_YARD_CONT_STATUS','FCL')
								 ->where('REAL_YARD_CONT',$this->input->post('STUFF_CONT'))
								 ->where('REAL_YARD_STATUS',1)
								 ->where('REAL_YARD_USED',1)
								 ->where('REAL_YARD_BRANCH_ID',$branch_id)
								 ->update('TX_REAL_YARD');

				if($_POST['DATE_REALIZATION'] != 0){
					//insert history container
					$this->db->query("CALL ADD_HISTORY_CONTAINER(
								'".$_POST['STUFF_CONT']."',
								'".$_POST['NO_REQ']."',
								'".$req_date."',
								'".$cont_in_yard['REAL_YARD_CONT_SIZE']."',
								'".$cont_in_yard['REAL_YARD_CONT_TYPE']."',
								'FCL',
								'".$cont_in_yard['YARD']."',
								'".$cont_in_yard['BLOCK_']."',
								".$cont_in_yard['SLOT_'].",
								".$cont_in_yard['ROW_'].",
								".$cont_in_yard['TIER_'].",
								1,
								'Realisasi Stuffing',
								NULL,
								'".$size40."',
								".$branch_id.",
								".$user.")");
					}
					else{
						//insert history container
						$this->db->query("CALL ADD_HISTORY_CONTAINER(
									'".$_POST['STUFF_CONT']."',
									'".$_POST['NO_REQ']."',
									'".$req_date."',
									'".$cont_in_yard['REAL_YARD_CONT_SIZE']."',
									'".$cont_in_yard['REAL_YARD_CONT_TYPE']."',
									'FCL',
									'".$cont_in_yard['YARD']."',
									'".$cont_in_yard['BLOCK_']."',
									".$cont_in_yard['SLOT_'].",
									".$cont_in_yard['ROW_'].",
									".$cont_in_yard['TIER_'].",
									1,
									'Realisasi Stuffing',
									NULL,
									'".$size40."',
									".$branch_id.",
									".$user.")");
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
