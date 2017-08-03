<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Record_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_customer_record';
	
	function __construct() {
		parent::__construct();
	}


	public function insertTable ($table_data){
		$res = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%d,%s');
		if ($res === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return;
	}

	public function updateTable ($table_data){

		$set_string = 	' record = %s , '.
						' update_time = %s ';
												
		$set_data_temp = array($table_data['record'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	

	public function getInitDatas() {
	}
	

	
	
}