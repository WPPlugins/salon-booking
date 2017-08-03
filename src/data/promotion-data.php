<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Promotion_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_promotion';	
	
	private $isCompleteDelete = false;
	
	function __construct() {
		parent::__construct();
	}
	

	public function insertTable ($table_data){
		$promotion_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%s,%s,%s,%s,%s,%d,%d,%d,%s,%s,%s');
		if ($promotion_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $promotion_cd;
	}

	public function updateTable ($table_data){

		$set_string = 	' branch_cd = %d , '.
						' set_code =  %s , '.
						' description =  %s , '.
						' valid_from =  %s , '.
						' valid_to =  %s , '.
						' usable_patern_cd =  %d , '.
//						' times =  %d , '.
//						' rank_patern_cd =  %d , '.
						' usable_data = %s , '.
						' discount_patern_cd =  %d , '.
						' discount =  %d , '.
						' remark =  %s , '.
						' update_time = %s ';
												
		$set_data_temp = array(
						 $table_data['branch_cd'] ,
						 $table_data['set_code'] ,
						 $table_data['description'] ,
						 $table_data['valid_from'] ,
						 $table_data['valid_to'] ,
						 $table_data['usable_patern_cd'] ,
//						 $table_data['times'] ,
//						 $table_data['rank_patern_cd'] ,
						 $table_data['usable_data'],
						 $table_data['discount_patern_cd'] ,
						 $table_data['discount'] ,
						 $table_data['remark'] ,
						date_i18n('Y-m-d H:i:s'),
						$table_data['promotion_cd']);
		$where_string = ' promotion_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['promotion_cd']);
		$where_string = ' promotion_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	static function getUsablePaternDatas(){
		$result = array();
		$result[Salon_Coupon::UNLIMITED] = __('Unlimited',SL_DOMAIN);
		$result[Salon_Coupon::TIMES] = __('Can only be used less than X times per customer',SL_DOMAIN);
		$result[Salon_Coupon::RANK] = __('Can only be used by customer\'s rank',SL_DOMAIN);
		$result[Salon_Coupon::FIRST] = __('Can only be used on the first reservation',SL_DOMAIN);
		return $result;
	}
	
	
	
}