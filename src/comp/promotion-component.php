<?php

class Promotion_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	public function editTableData () {
		if ( $_POST['type'] == 'deleted' ) {
			$set_data['promotion_cd'] = intval($_POST['promotion_cd']);
		}
		else {
			if ($_POST['type'] == 'updated' ) 	{
				$set_data['promotion_cd'] = intval($_POST['promotion_cd']);
			}
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
			$set_data['set_code'] = stripslashes($_POST['set_code']);
			$set_data['description'] = stripslashes($_POST['description']);
			if (empty($_POST['valid_from'] ) )  $set_data['valid_from'] = '0000-00-00 00:00:00';
			else $set_data['valid_from'] = Salon_Component::editRequestYmdForDb($_POST['valid_from']);
			if (empty($_POST['valid_to'] ) )  $set_data['valid_to'] = '2099-12-31 00:00:00';
			else $set_data['valid_to'] = Salon_Component::editRequestYmdForDb($_POST['valid_to']);
			$set_data['usable_patern_cd'] = intval($_POST['usable_patern_cd']);
			$set_data['usable_data'] = "";
			if ($set_data['usable_patern_cd'] == Salon_Coupon::TIMES ) {
				$set_data['usable_data'] = intval($_POST['times']);
			}
			else if ($set_data['usable_patern_cd'] == Salon_Coupon::RANK ) {
				$set_data['usable_data'] = intval($_POST['rank_patern_cd']);
			}
			$set_data['discount_patern_cd'] = intval($_POST['discount_patern_cd']);
			$set_data['discount'] = intval($_POST['discount']);

			$set_data['remark'] = stripslashes($_POST['remark']);
			$set_data['memo'] = '';
			$set_data['notes'] = '';

		}

		return $set_data;

	}

	public function serverCheck($set_data) {
		if (substr($set_data['valid_to'],0,4) != '2099' ) {
			$to = strtotime($set_data['valid_to']);
			$limit_time = new DateTime(date_i18n('Y-m-d'));
			//今日より前は登録できない
//			if ($limit_time->getTimestamp() > $to ) {
			if (+$limit_time->format('U') > $to ) {
				throw new Exception(Salon_Component::getMsg('E305',$set_data['set_code']),1);
			}
		}
		$result = $this->datas->getPromotionData($set_data['branch_cd'],null,$set_data['set_code'],true);
		if (count($result) > 0 ) {
			if (($_POST['type'] == 'updated' && $result[0]['promotion_cd'] != $set_data['promotion_cd'] )
					|| ($_POST['type'] == 'inserted'  )){
				throw new Exception(Salon_Component::getMsg('E304',$set_data['set_code']),1);
			}
		}

	}

	public function deleteCheck($promotion_cd) {
		$result = $this->datas->getPromotionData(null,$promotion_cd);
		global $wpdb;

		$sql = $wpdb->prepare(
				' FROM '.$wpdb->prefix.'salon_reservation '.
				' WHERE delete_flg <> '.Salon_Reservation_Status::COMPLETE.
				' AND time_to >= %s '.
				' AND coupon = %s ',date_i18n('Ymd'),$result[0]['set_code']);
		$sql_0 = 'SELECT  COUNT(*) as cnt';
		if ($wpdb->query($sql_0.$sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql_0.$sql,ARRAY_A);
		}
		if ($result[0]['cnt'] > 0 ) {
			$sql_0 = 'SELECT distinct DATE_FORMAT(time_to,"'.__('%m/%d/%Y',SL_DOMAIN).'") as in_time ';
			if ($wpdb->query($sql_0.$sql) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
			else {
				$result_day = $wpdb->get_results($sql_0.$sql,ARRAY_A);
			}
			$set_array = array();
			foreach ($result_day as $k1 => $d1  ) {
				$set_array[] = $d1['in_time'];
			}
			throw new Exception(Salon_Component::getMsg('E303',implode(',',$set_array)),1);
		}


	}



}