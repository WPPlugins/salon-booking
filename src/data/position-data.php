<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Position_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_position';
	
	function __construct() {
		parent::__construct();
	}
	

	public function insertTable ($table_data){
		$position_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%s,%s,%s,%s');
		if ($position_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $position_cd;
	}

	public function updateTable ($table_data){

		$set_string = 	' name = %s , '.
						' wp_role = %s , '.
						' role = %s , '.
						' remark =  %s , '.
						' update_time = %s ';
												
		$set_data_temp = array($table_data['name'],
						$table_data['wp_role'],
						$table_data['role'],
						$table_data['remark'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['position_cd']);
		$where_string = ' position_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['position_cd']);
		$where_string = ' position_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	public function getInitDatas() {
		return $this->getAllPositionData();
	}
	

	
	
}