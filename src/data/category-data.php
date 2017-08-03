<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Category_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_category';
	
	function __construct() {
		parent::__construct();
	}
	

	public function insertTable ($table_data){
		$category_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%s,%d,%s,%d');
		if ($category_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $category_cd;
	}
	

	public function updateTable ($table_data){
			if ($_POST['type'] == 'updated' ) 	{
				$set_data['category_cd'] = intval($_POST['category_cd']);
				$set_data['display_sequence'] = intval($_POST['display_sequence']);
			}
			else {
				$set_data['display_sequence'] = $this->datas->getMaxDisplaySequence('salon_item')+1;
			}

			$set_data['category_name'] = stripslashes($_POST['category_name']);
			$set_data['category_patern'] =  intval($_POST['category_patern']);
			$set_data['category_values'] =  stripslashes($_POST['category_values']);
			$set_data['target_table_id'] = intval($_POST['target_table_id']);

		$set_string = 	' category_name = %s , '.
						' category_patern = %d , '.
						' category_values = %s , '.
						' target_table_id = %d , '.
						' display_sequence = %d , '.
						' update_time = %s ';
												
		$set_data_temp = array($table_data['category_name'],
						$table_data['category_patern'],
						$table_data['category_values'],
						$table_data['target_table_id'],
						$table_data['display_sequence'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['category_cd']);
		$where_string = ' category_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['category_cd']);
		$where_string = ' category_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}


		return true;
	}
	

	public function getInitDatas() {
		return $this->getAllItemData();
	}
	
	
	
}