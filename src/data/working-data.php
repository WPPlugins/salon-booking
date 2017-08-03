<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');


class Working_Data extends Salon_Data {

	const TABLE_NAME = 'salon_working';

	public function __construct() {
		parent::__construct();
	}


	public function getWorkingData($target_day = null,$target_staff_cd = null){
		global $wpdb;
		if (empty($target_day) ) $target_day = Salon_Component::computeDate(-2);
		$sql = 	$wpdb->prepare(
						' SELECT  '.
						'staff_cd,in_time,out_time,working_cds,remark,memo '.
						' FROM '.$wpdb->prefix.self::TABLE_NAME.
						'   WHERE in_time >= %s '.
						'     AND staff_cd = %s ',
						$target_day,$target_staff_cd
					);
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result;
	}

	public function insertTable ($table_data){
		$result = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%s,%s,%s,%s');
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result;
	}

	public function updateTable ($table_data){

			$set_string = 	' in_time = %s , '.
							' out_time = %s , '.
							' working_cds = %s ,'.
							' remark = %s , '.
							' update_time = %s ';

			$set_data_temp = array(
							$table_data['in_time'],
							$table_data['out_time'],
							$table_data['working_cds'],
							$table_data['remark'],
							date_i18n('Y-m-d H:i:s')	,
							$table_data['staff_cd'],
							$table_data['key_in_time']);
		$where_string = ' staff_cd = %d AND in_time = %s ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}


	public function deleteTable ($table_data){
		$set_data_temp = array(
							$table_data['staff_cd'],
							$table_data['key_in_time']);
		$where_string = ' staff_cd = %d AND in_time = %s ';
		if ( $this->deleteSql(self::TABLE_NAME,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}



}