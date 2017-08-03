<?php

class Working_Component {
	
	private $version = '1.0';
	
	private $datas = null;
	private $is_need_sendmail = false;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	public function editInitData($branch_cd,$user_login=null) {
		//branch_cdが空なのは個別ユーザって前提
		if (empty($branch_cd) )
			$result = $this->datas->getTargetStaffDataByUserlogin($user_login);
		else 
			$result = $this->datas->getTargetStaffData($branch_cd);
//		$cnt = count($result);
//		for ($i = 0 ; $i<$cnt ;$i++){
//			unset($result[$i]['photo']);
//		}
		return $result;
	}
	
	public function editTableData () {
		if  ($_POST['type'] == 'deleted' ) {
			$set_data['staff_cd'] = intval($_POST['staff_cd']);
			$set_data['key_in_time'] = $_POST['key_in_time'];
			$set_data['in_time'] = $_POST['start_date'];
			$set_data['out_time'] = $_POST['end_date'];		
			$set_data['working_cds'] = $_POST['working_cds'];		
		}
		else {
			$set_data['staff_cd'] = intval($_POST['staff_cd']);	
			$set_data['in_time'] = $_POST['start_date'];	
			$set_data['out_time'] = $_POST['end_date'];		
			$set_data['working_cds'] = $_POST['working_cds'];		
			$set_data['remark'] = stripslashes($_POST['remark']);	
			if  ($_POST['type'] == 'updated' ) {
				$set_data['key_in_time'] = $_POST['key_in_time'];
			}
		}
		return $set_data;
	}
	
	public function serverCheck($set_data) {
		$working_array = explode(',',$set_data['working_cds']);
		if (! in_array(Salon_Working::USUALLY,$working_array) ) {
			$cnt = $this->datas->countReservation($set_data['staff_cd'],$set_data['in_time'],$set_data['out_time']);
			if ($cnt > 0 ) {
				throw new Exception(Salon_Component::getMsg('W001'),1);
			}
		}
	}
}
