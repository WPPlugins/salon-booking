<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Photo_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_photo';	
	
	public function __construct() {
		parent::__construct();
	}
	

	public function insertTable ($table_data){
		$photo_id = $this->insertSql(self::TABLE_NAME,$table_data,'%s,%s,%s,%d,%d,%d');
		if ($photo_id === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $photo_id;
	}
	
	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'));
		$where_string = ' photo_id in  ('.$table_data['photo_ids'].') ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	
	
}