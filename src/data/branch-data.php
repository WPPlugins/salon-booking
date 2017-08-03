<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Branch_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_branch';
	
	function __construct() {
		parent::__construct();
	}
	

	public function insertTable ($table_data){
		$branch_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%s,%s,%s,%s,%s,%s,%d,%s,%s,%s,%d,%s,%s');
		if ($branch_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $branch_cd;
	}

	public function updateTable ($table_data){

		$set_string = 	' name = %s , '.
						' zip = %s , '.
						' address = %s , '.
						' tel = %s , '.
						' mail = %s , '.
						' remark =  %s , '.
						' duplicate_cnt = %d , '.
						' open_time = %s , '.
						' close_time = %s , '.
						' time_step = %d , '.
						' closed = %s , '.
						' memo = %s , '.
						' notes = %s , '.
						' update_time = %s ';
												
		$set_data_temp = array($table_data['name'],
						$table_data['zip'],
						$table_data['address'],
						$table_data['tel'],
						$table_data['mail'],
						$table_data['remark'],
						$table_data['duplicate_cnt'],
						$table_data['open_time'],
						$table_data['close_time'],
						$table_data['time_step'],
						$table_data['closed'],
						$table_data['memo'],
						$table_data['notes'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['branch_cd']);
		$where_string = ' branch_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	
	public function updateColumn($table_data){
		
		$set_string = 	$table_data['column_name'].' , '.
								' update_time = %s ';
														
		$set_data_temp = array($table_data['value'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['branch_cd']);
		$where_string = ' branch_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		
		
	}

	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['branch_cd']);
		$where_string = ' branch_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	public function getInitDatas() {
		return $this->getAllBranchData();
	}
	
	static function getSettingPaternDatas(){
		$result = array();
		$result[Salon_Config::SETTING_PATERN_TIME] = __('Input time unit',SL_DOMAIN);
		$result[Salon_Config::SETTING_PATERN_ORIGINAL] = __('Input pre-determined time frames',SL_DOMAIN);
		return $result;
	}

	
	
}