<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');


class Confirm_Data extends Salon_Data {


	const TABLE_NAME = 'salon_reservation';

	function __construct() {
		parent::__construct();
	}

	public function getTargetReservationData($reservation_cd) {
		global $wpdb;
		$sql = 			'SELECT '.
						' rs.reservation_cd ,'.
						' DATE_FORMAT(rs.time_from,"'.__('%%m/%%d/%%Y',SL_DOMAIN).'") as target_day,'.
						' rs.user_login,'.
						' rs.non_regist_name as name,'.
						' rs.non_regist_email as email,'.
						' rs.non_regist_tel as tel, '.
						' rs.non_regist_activate_key, '.
						' DATE_FORMAT(rs.time_from, "%%H:%%i")  as time_from,'.
						' DATE_FORMAT(rs.time_to, "%%H:%%i")   as time_to,'.
						' DATE_FORMAT(rs.time_from, "%%Y%%m%%d%%H%%i")  as check_day,'.
						' rs.branch_cd,'.
						' rs.staff_cd ,'.
						' st.user_login, '.
						' rs.item_cds ,'.
						' rs.status ,'.
						' rs.coupon ,'.
						' rs.remark '.
						' FROM '.$wpdb->prefix.'salon_reservation rs '.
						' LEFT JOIN '.$wpdb->prefix.'salon_staff st'.
						' ON rs.staff_cd = st.staff_cd'.
						' WHERE rs.reservation_cd = %d ';
		$result = $wpdb->get_results($wpdb->prepare($sql,$reservation_cd),ARRAY_A);
		if ( $result === false ){
			$this->_dbAccessAbnormalEnd();
		}
		return $result;
	}


	public function updateTable ($table_data){


		$set_string = 	' status = %d  '.
						' ,user_login = %s '.
						' ,update_time = %s ';

		$set_data_temp = array(
						$table_data['status']
						,$table_data['user_login']
						,date_i18n('Y-m-d H:i:s')
						,$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
	}


	public function deleteTable ($table_data){
		$set_string = 	' status = %d  '.
						' ,update_time = %s ';
		$set_string .= 	' ,remark = concat(remark,"'.sprintf(__(" Canceled by %s. ",SL_DOMAIN),__("[Screen of Confirm]",SL_DOMAIN)).'") ';

		$set_data_temp = array(
						$table_data['status']
						,date_i18n('Y-m-d H:i:s')
						,$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
	}


}