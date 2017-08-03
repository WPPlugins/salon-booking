<?php

class Position_Component {
	
	private $version = '1.0';
	
	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}
	
	
	
	public function editTableData () {
		
		if ( $_POST['type'] == 'deleted' ) {
			$set_data['position_cd'] = intval($_POST['position_cd']);
		}
		else {
			if ($_POST['type'] == 'updated' ) 	$set_data['position_cd'] = intval($_POST['position_cd']);


			$set_data['name'] = stripslashes($_POST['name']);
			$set_data['wp_role'] = stripslashes($_POST['wp_role']);
			$set_data['role'] = stripslashes($_POST['role']);
			//working allがある場合は、workingも有効にする
			$role_array = explode(',',$set_data['role']);
			if (in_array('edit_working_all',$role_array) && ! in_array('edit_working',$role_array) ) {
				$set_data['role'] = $set_data['role'].',edit_working';
			}
			$set_data['remark'] = stripslashes($_POST['remark']);
		}
		return $set_data;
		
	}
	
	public function  getAdminMenuDatas() {
		$set_data[0] = array('name'=>__('Reservation',SL_DOMAIN),'func'=>'edit_booking');
		$set_data[1] = array('name'=>__('Customer Information',SL_DOMAIN),'func'=>'edit_customer');
		$set_data[2] = array('name'=>__('Menu Information',SL_DOMAIN),'func'=>'edit_item');
		$set_data[3] = array('name'=>__('Staff Information',SL_DOMAIN),'func'=>'edit_staff');
		$set_data[4] = array('name'=>__('Shop Information',SL_DOMAIN),'func'=>'edit_branch');
		$set_data[5] = array('name'=>__('Environment Setting',SL_DOMAIN),'func'=>'edit_config');
		$set_data[6] = array('name'=>__('Position Information',SL_DOMAIN),'func'=>'edit_position');
		$set_data[7] = array('name'=>__('Reservation Regist',SL_DOMAIN),'func'=>'edit_reservation');
		$set_data[8] = array('name'=>__('Performance Regist',SL_DOMAIN),'func'=>'edit_sales');
		$set_data[9] = array('name'=>__('Promotion Regist',SL_DOMAIN),'func'=>'edit_promotion');
		$set_data[10] = array('name'=>__('Promotion Use',SL_DOMAIN),'func'=> 'edit_use_promotion');
		$set_data[11] = array('name'=>__('Time Card',SL_DOMAIN),'func'=>'edit_working');
		$set_data[12] = array('name'=>__('Time Card(full members)',SL_DOMAIN),'func'=>'edit_working_all');
		$set_data[13] = array('name'=>__('Customer Record',SL_DOMAIN),'func'=>'edit_record');
		$set_data[14] = array('name'=>__('Category Setting',SL_DOMAIN),'func'=>'edit_category');
		$set_data[15] = array('name'=>__('Basic Information',SL_DOMAIN),'func'=>'edit_base');
		$set_data[16] = array('name'=>__('Authoriy of Management',SL_DOMAIN),'func'=>'edit_admin');
		$set_data[17] = array('name'=>__('View Log',SL_DOMAIN),'func'=>'edit_log');
		return $set_data;
	}
}