<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');


class Checkconfig_Data extends Salon_Data {


	const TABLE_NAME = 'salon_reservation';

	function __construct() {
		parent::__construct();
	}

	public function getTableData() {
		$return = array();
		global $wpdb;
		$sql = 			'SELECT '.
						' branch_cd'
						.',name'
						.',open_time'
						.',close_time'
						.',time_step'
						.',duplicate_cnt'
						.',delete_flg'
						.',DATE_FORMAT(insert_time,"%Y%m%d%H%i") as insert_time'
						.',DATE_FORMAT(update_time,"%Y%m%d%H%i") as update_time'
						.' FROM '.$wpdb->prefix.'salon_branch'
						.'';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		$return['BRANCH'] = $result;

		$sql = 			'SELECT '.
						' staff_cd'
						.',branch_cd'
						.',in_items'
						.',delete_flg'
						.',DATE_FORMAT(insert_time,"%Y%m%d%H%i") as insert_time'
						.',DATE_FORMAT(update_time,"%Y%m%d%H%i") as update_time'
						.' FROM '.$wpdb->prefix.'salon_staff'
						.'';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		$return['STAFF'] = $result;

		$sql = 			'SELECT '.
						' item_cd'
						.',branch_cd'
						.',delete_flg'
						.',DATE_FORMAT(insert_time,"%Y%m%d%H%i") as insert_time'
						.',DATE_FORMAT(update_time,"%Y%m%d%H%i") as update_time'
						.' FROM '.$wpdb->prefix.'salon_item'
						.'';
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		$return['MENU'] = $result;

		return $return;
	}

	public function getConfigShowData() {
		$result = $this->getConfigData();
		return $result;
	}



}