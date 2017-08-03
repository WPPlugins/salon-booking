<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Log_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_log';	
	
	function __construct() {
		parent::__construct();
	}
	

	public function getInitDatas($get_cnt = 100) {
		global $wpdb;
		$join = '';
		$where ='';

		$sql = 'SELECT `sql` as operation,remark,'.
				' DATE_FORMAT(insert_time,"'.__('%%m/%%d/%%Y',SL_DOMAIN).'") as logged_day ,'.
				' DATE_FORMAT(insert_time,"%%H:%%i") as logged_time '.
				' FROM '.$wpdb->prefix.'salon_log  '.
				' ORDER BY insert_time DESC'.
				' LIMIT %d ';
		$result = $wpdb->get_results($wpdb->prepare($sql,$get_cnt),ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result;



	}

	

	
	
}