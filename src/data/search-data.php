<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Search_Data extends Salon_Data {
	
	
	public function __construct() {
		parent::__construct();
	}
	

	function getSearchCustomerData($keys){	
		global $wpdb;
		$sql = 'SELECT ID,user_login,user_email,meta_key,meta_value '.
				' FROM '.$wpdb->users.' us  '.
				' INNER JOIN '.$wpdb->usermeta.' um  '.
				'       ON    us.ID = um.user_id '.
				' WHERE '.
//				'      (user_email =  %s ) OR '.
				'      (meta_key = "first_name" OR '.
				'       meta_key = "last_name"  OR '.
				'       meta_key = "tel"        OR '.
				'       meta_key = "mobile" OR '.
				'       meta_key = "'.$wpdb->prefix.'capabilities" ) '.
				' ORDER BY ID';
//		$result = $wpdb->get_results($wpdb->prepare($sql,$keys['mail']),ARRAY_A);
		$result = $wpdb->get_results($sql,ARRAY_A);

		
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		$edit = array();
		$index = 0;
		$save_key = $result[0]['ID'];
		//ログインID単位の配列に変換する。
		for ($i =0; $i < count($result) ; $i++ ) {
			if ($save_key <> $result[$i]['ID'] ) {
				$index++;
			}
			$edit[$index][$result[$i]['meta_key']] = $result[$i]['meta_value'];
			//[TODO]ちと冗長？
			$edit[$index]['ID'] = $result[$i]['ID'];
			$edit[$index]['user_login'] = $result[$i]['user_login'];
			$edit[$index]['mail'] = $result[$i]['user_email'];
			$save_key = $result[$i]['ID'];
		}
		return $edit;
	}
	
}