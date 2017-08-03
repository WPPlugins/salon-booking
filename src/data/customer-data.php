<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');


class Customer_Data extends Salon_Data {

	const TABLE_NAME = 'salon_customer';

	function __construct() {
		parent::__construct();
	}


	public function insertTable ($table_data){
		$customer_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%s,%d,%d,%s,%s,%s,%s');
		if ($customer_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $customer_cd;
	}

	public function updateTable ($table_data){

		$set_string = 	' ID = %d , '.
						' branch_cd = %d , '.
						' remark =  %s , '.
						' memo =  %s , '.
						' user_login =  %s , '.
						' rank_patern_cd =  %d , '.
						' update_time = %s ';

		$set_data_temp = array(
						$table_data['ID'],
						$table_data['branch_cd'],
						$table_data['remark'],
						$table_data['memo'],
						$table_data['user_login'],
						$table_data['rank_patern_cd'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['customer_cd']);
		$where_string = ' customer_cd = %d ';

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
						$table_data['customer_cd']);
		$where_string = ' customer_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false ) {
			$this->_dbAccessAbnormalEnd();
		}


	}

	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['customer_cd']);
		$where_string = ' customer_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}


	public function getInitDatas() {
		return $this->getCustomerDataByCustomercd();
	}


	public function getCustomerDataByCustomercd($customer_cd = "") {
		global $wpdb;
		$join = '';
		$where ='';
		$method = 'LEFT';
		if (!empty($customer_cd)) {
			$where = $wpdb->prepare(' WHERE cu.customer_cd = %d ',$customer_cd);
		}
		else {
			$join = ' AND cu.delete_flg <> '.Salon_Reservation_Status::DELETED;
			//他支店の情報はみられないようにする
			if ($this->isSalonAdmin() == false) {
				$method = 'INNER ';

			}
		}
		$sql = 'SELECT us.ID,us.user_login,um.* ,us.user_email,'.
				'        cu.customer_cd,cu.branch_cd,cu.remark,cu.memo,cu.notes,cu.delete_flg ,cu.rank_patern_cd'.
				' FROM '.$wpdb->users.' us  '.
				' INNER JOIN '.$wpdb->usermeta.' um  '.
				'       ON    us.ID = um.user_id '.
				$method.' JOIN '.$wpdb->prefix.'salon_customer cu  '.
				'       ON    us.user_login = cu.user_login '.
				$join.
				$where.
				' ORDER BY customer_cd desc,ID';

		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result;



	}



}