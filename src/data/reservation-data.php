<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');



class Reservation_Data extends Salon_Data {

	const TABLE_NAME = 'salon_reservation';

	function __construct() {
		parent::__construct();
	}


	public function insertTable ($table_data){
		$reservation_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%d,%s,%s,%s,%s,%d,%s,%s,%s,%s,%s,%s,%s,%s');
		if ($reservation_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $reservation_cd;
	}

	public function updateTable ($table_data){
		$set_string = 	' staff_cd = %d , '.
						' time_from =  %s , '.
						' time_to =  %s , '.
						' item_cds =  %s , '.
						' remark =  %s , '.
						' memo = %s , '.
						' non_regist_name = %s , '.
						' non_regist_email = %s , '.
						' non_regist_tel = %s , '.
						' status = %d , '.
						' coupon = %s , '.
						' update_time = %s ';

		$set_data_temp = array(
						$table_data['staff_cd'],
						$table_data['time_from'],
						$table_data['time_to'],
						$table_data['item_cds'],
						$table_data['remark'],
						$table_data['memo'],
						$table_data['non_regist_name'],
						$table_data['non_regist_email'],
						$table_data['non_regist_tel'],
						$table_data['status'],
						$table_data['coupon'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}


	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['reservation_cd']);
		if ( is_user_logged_in() )	{
			$name = $this->getUserName();
			$set_string .= 	' ,remark = concat(remark,"'.sprintf(__(" Deleted by %s. ",SL_DOMAIN),$name).'") ';
		}
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}

	public function cancelTable ($table_data){
		$set_string = 	' status = %d, update_time = %s  ';
		if ( is_user_logged_in() )	{
			$name = $this->getUserName();
			$set_string .= 	' ,remark = concat(remark,"'.sprintf(__(" Canceled by %s. ",SL_DOMAIN),$name).'") ';
		}
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}

	public function confirmTable ($table_data){
		$set_string = 	' status = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::COMPLETE,
				date_i18n('Y-m-d H:i:s'),
				$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}





}