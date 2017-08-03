<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Item_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_item';
	
	function __construct() {
		parent::__construct();
	}
	

	public function insertTable ($table_data){
		//[2014/06/22]all_flgをチェックありで追加された場合は、対応する支店のスタッフの情報も更新する
		$item_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%s,%s,%d,%d,%d,%s,%s,%s,%s,%s,%d,%s');
		if ($item_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		$this->_setItemsOfStaff($table_data['branch_cd'],$item_cd,$table_data['all_flg']);
		return $item_cd;
	}
	
	private function _setItemsOfStaff($branch_cd,$item_cd,$all_flg) {
		global $wpdb;
		
		$where = 'branch_cd = %d ';
		if ($branch_cd == "" ) {
			$where = '1 = %d';
			$branch_cd = 1;
		}

		$sql =	$wpdb->prepare(
					' SELECT  '.
					' staff_cd ,in_items '.
					' FROM '.$wpdb->prefix.'salon_staff '.
					'   WHERE '.$where.
					'     AND delete_flg <> %d ',
					$branch_cd,Salon_Reservation_Status::DELETED);

		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}


		$set_string = 	' in_items = %s , '.
						' update_time = %s ';
		$where_string = ' staff_cd = %d ';

		foreach($result as $k1 => $d1 ) {
			//更新して
			$items_array = explode(",",$d1['in_items']);
			$key = array_search($item_cd, $items_array);
			if ($all_flg == Salon_Config::ALL_ITEMS_YES ) {
				if ($key === false ) {
					$items_array[] = $item_cd;
				}
			}
			else if ($all_flg == Salon_Config::ALL_ITEMS_NO ) {
				if ($key !== false ) {
					unset($items_array[$key]);	
				}
			}
			$in_items = implode(',',$items_array);
			$set_data_temp = array($in_items,
							date_i18n('Y-m-d H:i:s'),
							$d1['staff_cd']);
			if ( $this->updateSql('salon_staff',$set_string,$where_string,$set_data_temp) === false) {
				$this->_dbAccessAbnormalEnd();
			}
		}
	}

	public function updateTable ($table_data){
		//[2014/06/22]all_flgが変更された場合は、対応する支店のスタッフの情報も更新する
		//
		if ($_POST['is_change_all_flg'] == Salon_Config::ALL_ITEMS_CHANGE_YES) {
			$this->_setItemsOfStaff($table_data['branch_cd'],$table_data['item_cd'],$table_data['all_flg']);
		}
		
		$set_string = 	' name = %s , '.
						' short_name = %s , '.
						' branch_cd = %d , '.
						' minute = %d , '.
						' price = %d , '.
						' remark =  %s , '.
						' memo =  %s , '.
						' photo =  %s , '.
						' exp_from = %s , '.
						' exp_to = %s , '.
						' all_flg =  %d , '.
						' display_sequence = %d , '.
						' update_time = %s ';
												
		$set_data_temp = array($table_data['name'],
						$table_data['short_name'],
						$table_data['branch_cd'],
						$table_data['minute'],
						$table_data['price'],
						$table_data['remark'],
						$table_data['memo'],
						$table_data['photo'],
						$table_data['exp_from'],
						$table_data['exp_to'],
						$table_data['all_flg'],
						$table_data['display_sequence'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['item_cd']);
		$where_string = ' item_cd = %d ';
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
						$table_data['item_cd']);
		$where_string = ' item_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		
		
	}

	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['item_cd']);
		$where_string = ' item_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}

		$this->_setItemsOfStaff("",$table_data['item_cd'],Salon_Config::ALL_ITEMS_NO);

		return true;
	}
	

	public function getInitDatas() {
		return $this->getAllItemData();
	}
	
//	public function updateSeq($table_data) {
//		foreach ($table_data as $k1 => $d1) {
//			$set_string = 	'display_sequence = %d , '.
//							' update_time = %s ';
//															
//			$set_data_temp = array($d1,
//							date_i18n('Y-m-d H:i:s'),
//							$k1);
//			$where_string = ' item_cd = %d ';
//			if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false ) {
//				$this->_dbAccessAbnormalEnd();
//			}
//		}
//	}

	
	
}