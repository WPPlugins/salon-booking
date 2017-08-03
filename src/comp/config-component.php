<?php

class Config_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}




	public function editTableData () {
		$set_data['SALON_CONFIG_BRANCH'] = Salon_Config::MULTI_BRANCH;
		$set_data['SALON_CONFIG_USER_LOGIN'] = empty($_POST['config_user_login']) ? Salon_Config::USER_LOGIN_NG : Salon_Config::USER_LOGIN_OK;
		$set_data['SALON_CONFIG_STAFF_HOLIDAY_SET'] = empty($_POST['config_staff_holiday_set']) ? Salon_Config::SET_STAFF_NORMAL : $_POST['config_staff_holiday_set'];
		$set_data['SALON_CONFIG_NAME_ORDER'] = empty($_POST['config_name_order_set']) ? Salon_Config::NAME_ORDER_JAPAN : $_POST['config_name_order_set'];
		$set_data['SALON_CONFIG_NO_PREFERENCE'] = empty($_POST['config_no_preference']) ? Salon_Config::NO_PREFERNCE_NG : Salon_Config::NO_PREFERNCE_OK;
		$set_data['SALON_CONFIG_SHOW_DETAIL_MSG'] = empty($_POST['config_show_detail_msg']) ? Salon_Config::DETAIL_MSG_NG : Salon_Config::DETAIL_MSG_OK;
		$set_data['SALON_CONFIG_BEFORE_DAY'] = intval($_POST['config_before_day']);
		$set_data['SALON_CONFIG_AFTER_DAY'] = intval($_POST['config_after_day']);
		$set_data['SALON_CONFIG_TIMELINE_Y_CNT'] = intval($_POST['config_timeline_y_cnt']);
		$set_data['SALON_CONFIG_LOG'] = empty($_POST['config_log']) ? Salon_Config::LOG_NO_NEED : Salon_Config::LOG_NEED;
		$set_data['SALON_CONFIG_DELETE_RECORD'] = empty($_POST['config_delete_record']) ? Salon_Config::DELETE_RECORD_NO : Salon_Config::DELETE_RECORD_YES;
		$set_data['SALON_CONFIG_DELETE_RECORD_PERIOD'] = empty($_POST['config_delete_record_period']) ? Salon_Config::DELETE_RECORD_PERIOD : $_POST['config_delete_record_period'];
		$set_data['SALON_CONFIG_MAINTENANCE_INCLUDE_STAFF'] = empty($_POST['config_maintenance_include_staff']) ? Salon_Config::MAINTENANCE_NOT_INCLUDE_STAFF : Salon_Config::MAINTENANCE_INCLUDE_STAFF;

		$set_data['SALON_CONFIG_MOBILE_USE'] = empty($_POST['config_mobile_use']) ? Salon_Config::MOBILE_USE_NO : Salon_Config::MOBILE_USE_YES;


		if ($set_data['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_REVERSE ) {
			$set_data['SALON_CONFIG_NO_PREFERENCE'] = Salon_Config::NO_PREFERNCE_NG ;
		}

		$set_data['SALON_CONFIG_LOAD_TAB'] = empty($_POST['config_load_tab']) ? Salon_Config::LOAD_STAFF : $_POST['config_load_tab'];
		$set_data['SALON_CONFIG_RESERVE_DEADLINE'] = intval($_POST['config_reserve_deadline']);



		$set_data['SALON_CONFIG_PC_DISPLAY_TAB_STAFF'] = empty($_POST['config_show_tab_staff']) ? Salon_Config::SHOW_NO_TAB : Salon_Config::SHOW_TAB;
		$set_data['SALON_CONFIG_PC_DISPLAY_TAB_MONTH'] = empty($_POST['config_show_tab_month']) ? Salon_Config::SHOW_NO_TAB : Salon_Config::SHOW_TAB;
		$set_data['SALON_CONFIG_PC_DISPLAY_TAB_WEEK'] = empty($_POST['config_show_tab_week']) ? Salon_Config::SHOW_NO_TAB : Salon_Config::SHOW_TAB;
		$set_data['SALON_CONFIG_PC_DISPLAY_TAB_DAY'] = empty($_POST['config_show_tab_day']) ? Salon_Config::SHOW_NO_TAB : Salon_Config::SHOW_TAB;

		$set_data['SALON_CONFIG_USE_SESSION_ID'] = empty($_POST['config_use_session']) ? Salon_Config::USE_NO_SESSION : Salon_Config::USE_SESSION;

		$set_data['SALON_CONFIG_CONFIRM_STYLE'] = empty($_POST['config_confirm_style']) ? Salon_Config::CONFIRM_BY_MAIL : intval($_POST['config_confirm_style']);
		return $set_data;

	}


}