<?php

class Record_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}


	public function getReservationDataByDate($target_branch_cd ,$day_from="",$day_to="",$user_login = ""){
		global $wpdb;

		$staff_datas = $this->datas->getTargetStaffData($target_branch_cd);
		foreach ($staff_datas as $k1 => $d1) {
			$staff_table[$d1['staff_cd']]  = array('name'=> $d1['name']);
		}
		$staff_table[Salon_Default::NO_PREFERENCE] = array('name' => __('Anyone',SL_DOMAIN));


		//datepickerの表示タイミングがデータ取得の後なので1月分多くとる。
		if (empty($day_from) && empty($day_to) ) {
			$day_to = Salon_Component::computeDate();
			$day_from = Salon_Component::computeMonth(-3);
		}

		$add_where = "";
		if ($user_login != "" ) {
			$add_where = 'AND user_login  = "'.$user_login.'"';
		}



		//月の配列をつくる
		$start_month = $day_from;
		$month_array = array();
		$target_month = substr($day_to,0,4).substr($day_to,5,2);
		for(;;) {
			$set_month = substr($start_month,0,4).substr($start_month,5,2);
			$month_array[] = $set_month;
			if ($set_month >= $target_month) break;
			$start_month = Salon_Component::computeMonth(1,substr($start_month,0,4),substr($start_month,5,2),1);
		}
		$edit_result = array();
		foreach ($month_array as $d1) {
			$edit_result[$d1] = array();
		}

		$sql = 	$wpdb->prepare(
						' SELECT '.
						' rs.reservation_cd,branch_cd, '.
						' DATE_FORMAT(time_from,"%%Y%%m") as target_month,'.
						' DATE_FORMAT(time_from,"%%Y%%m%%d") as target_day,'.
						' DATE_FORMAT(time_from,"%%H%%i") as time_from,'.
						' DATE_FORMAT(time_to,"%%H%%i") as time_to, '.
						' user_login,non_regist_name as name,non_regist_email as email, '.
						' cr.customer_cd,cr.record,cr.insert_time '.
						',rs.staff_cd '.
						' FROM '.$wpdb->prefix.'salon_reservation  rs '.
						' LEFT JOIN '.$wpdb->prefix.'salon_customer_record  cr '.
						' ON rs.reservation_cd = cr.reservation_cd '.
						'   WHERE time_from >= %s '.
						'     AND time_to < %s '.
						'     AND (status = '.Salon_Reservation_Status::COMPLETE.
						'      OR  status = '.Salon_Reservation_Status::DUMMY_RESERVED.')'.
						'     AND rs.delete_flg <> '.Salon_Reservation_Status::DELETED.
						'     AND branch_cd = %s '.
						$add_where.
						' ORDER BY target_day,time_from,time_to ',
						$day_from,$day_to,$target_branch_cd
				);
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if (count($result)>0){

			foreach($result as $k1 => $d1){
				$d1['staff_name'] = @$staff_table[$d1['staff_cd']]['name'];;
				if (empty($d1['insert_time'])) $d1['operate'] = 'inserted';
				else $d1['operate'] = 'updated';
				unset($d1['insert_time']);
				$edit_result[$d1['target_month']][] = $d1;

			}
		}

		return $edit_result;
	}



	public function editTableData () {

		$set_data['reservation_cd'] = intval($_POST['reservation_cd']);
		if ($_POST['type'] == 'updated' ) 	{
			$set_data['customer_cd'] = intval($_POST['customer_cd']);
		}
		else if ($_POST['type'] == 'inserted' ) 	{
			if (empty($_POST['user_login'])) {
				$set_data['customer_cd'] = Salon_Config::NO_REGISTED_CUSTOMER_CD;
			}
			else {
				$customer_data =  $this->datas->getCustomerDataByUser($_POST['user_login']);
				$set_data['customer_cd'] =$customer_data[0]['customer_cd'];
			}
		}
		$edit_record = array();
		foreach ($_POST['record'] as $k1 => $d1 ){
			$edit_record[$k1] = stripslashes($d1);
		}

		$set_data['record'] = serialize($edit_record);


		return $set_data;

	}

	public function serverCheck($set_data) {
		global $wpdb;
		if ( $_POST['type'] == 'updated' )  {

			$sql =	$wpdb->prepare(
					' SELECT  count(*) as cnt '.
					' FROM '.$wpdb->prefix.'salon_customer_record '.
					'   WHERE delete_flg <> %d '
					.'  AND reservation_cd = %d '
					.'  AND customer_cd = %d '
					,Salon_Reservation_Status::DELETED
					,$set_data['reservation_cd']
					,$set_data['customer_cd']);

			if ($wpdb->query($sql) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
			else {
				$result = $wpdb->get_results($sql,ARRAY_A);
			}

			if ($result[0]['cnt'] == 0 ) {
				throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__)."(".__LINE__.")"),1);
			}
		}

	}


}