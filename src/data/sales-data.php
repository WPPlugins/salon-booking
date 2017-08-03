<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Sales_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_sales';	
	
	function __construct() {
		parent::__construct();
	}
	

	public function insertTable ($table_data){
		$result = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%d,%d,%s,%s,%s,%s,%d,%d,%s,%s,%d,%s');
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result;
	}

	public function updateTable ($table_data){
		$set_string = 	' staff_cd = %d , '.
						' time_from =  %s , '.
						' time_to =  %s , '.
						' item_cds =  %s , '.
						' remark =  %s , '.
						' price  = %d , '.
						' coupon =  %s , '.
						' update_time = %s ';
												
		$set_data_temp = array(
						$table_data['staff_cd'],
						$table_data['time_from'],
						$table_data['time_to'],
						$table_data['item_cds'],
						$table_data['remark'],
						$table_data['price'],
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
		$where_string = ' reservation_cd = %d ';
		if ( $this->deleteSql(self::TABLE_NAME,$where_string,$table_data['reservation_cd']) === false) {  
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	

	
	
}