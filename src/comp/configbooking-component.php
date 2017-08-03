<?php

class Configbooking_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}



	public function editTableData ($fields_default) {

		if (isset($_POST['config_restore']) && $_POST['config_restore'] == "true"){
			$set_data['SALON_CONFIG_DISPLAY_TITLE1'] = "";
			$set_data['SALON_CONFIG_DISPLAY_HOLIDAY'] = __('Holiday',SL_DOMAIN);
			$set_data['SALON_CONFIG_DISPLAY_ONBUSINESS'] = __('Bookable',SL_DOMAIN);;
			$set_data['SALON_CONFIG_DISPLAY_SPECIAL_ONBUSINESS'] = __('On business',SL_DOMAIN);
			$set_data['SALON_CONFIG_DISPLAY_COLUMN'] = 2;
			$set_data['SALON_CONFIG_FRONT_ITEMS_SET'] = "";
			$set_data['SALON_CONFIG_DO_SORT_STAFF_AUTO'] = Salon_YesNo::Yes;
			$set_data['SALON_CONFIG_PC_BACK_COLOR'] = Salon_Color::PC_BACK;
			$set_data['SALON_CONFIG_PC_EVENT_COLOR'] = Salon_Color::PC_EVENT;
			$set_data['SALON_CONFIG_PC_EVENT_LINE_COLOR'] = Salon_Color::PC_EVENT_LINE;
			$set_data['SALON_CONFIG_PC_SELECTED_BACK_COLOR'] = Salon_Color::PC_BACK_SELCTED;
			$set_data['SALON_CONFIG_PC_UNSELECTED_BACK_COLOR'] = Salon_Color::PC_BACK_UNSELCTED;
			$set_data['SALON_CONFIG_PC_HOLIDAY_COLOR'] = Salon_Color::PC_HOLIDAY;
			$set_data['SALON_CONFIG_PC_ONBUSINESS_COLOR'] = Salon_Color::PC_ONBUSINESS;
			$set_data['SALON_CONFIG_MENU_TYPE'] = Salon_Category::CHECK_BOX;
			return $set_data;
		}


		$set_data['SALON_CONFIG_DISPLAY_TITLE1'] = stripslashes($_POST['config_before_title']);
		$set_data['SALON_CONFIG_DISPLAY_HOLIDAY'] = stripslashes($_POST['config_holiday_display']);
		$set_data['SALON_CONFIG_DISPLAY_ONBUSINESS'] = "";
		if (isset($_POST['config_onbusiness_display'])) {
			$set_data['SALON_CONFIG_DISPLAY_ONBUSINESS'] = stripslashes($_POST['config_onbusiness_display']);
		}
		$set_data['SALON_CONFIG_DISPLAY_SPECIAL_ONBUSINESS'] = stripslashes($_POST['config_special_onbusiness_display']);
		$set_data['SALON_CONFIG_DISPLAY_COLUMN'] = intval($_POST['config_menu_column']);
		$set_data['SALON_CONFIG_MENU_TYPE'] = intval($_POST['config_menu_type']);;
		$set_data['SALON_CONFIG_DO_SORT_STAFF_AUTO'] = empty($_POST['config_doSort']) ? Salon_YesNo::No : Salon_YesNo::Yes;
		$set_data['SALON_CONFIG_PC_BACK_COLOR'] = Salon_Color::PC_BACK;
		if (isset($_POST['config_pc_back_color'])) {
			$set_data['SALON_CONFIG_PC_BACK_COLOR'] = stripslashes($_POST['config_pc_back_color']);
			$set_data['SALON_CONFIG_PC_EVENT_COLOR'] = stripslashes($_POST['config_pc_event_color']);
			$set_data['SALON_CONFIG_PC_EVENT_LINE_COLOR'] = stripslashes($_POST['config_pc_event_line_color']);
			$set_data['SALON_CONFIG_PC_SELECTED_BACK_COLOR'] = stripslashes($_POST['config_pc_selected_back_color']);
			$set_data['SALON_CONFIG_PC_UNSELECTED_BACK_COLOR'] = stripslashes($_POST['config_pc_unselected_back_color']);
			$set_data['SALON_CONFIG_PC_HOLIDAY_COLOR'] = stripslashes($_POST['config_pc_holiday_color']);
			$set_data['SALON_CONFIG_PC_ONBUSINESS_COLOR'] = stripslashes($_POST['config_pc_onbusiness_color']);
		}

		//デフォルト値と同じかを確認する。
		//同じだったら設定しない
		$isChange = false;
		$editData = array();
		foreach ($_POST['config_fields'] as $k1 => $d1 ){
			$editData[$k1] = $fields_default[$k1];
			$editData[$k1]['set_label'] = stripslashes($d1['set_label']);
			if ($fields_default[$k1]['set_label'] != $d1['set_label']) {
				$isChange = true;
			}
			$editData[$k1]['set_tips'] = stripslashes($d1['set_tips']);
			if ($fields_default[$k1]['set_tips'] != $d1['set_tips']) {
				$isChange = true;
			}
			if($fields_default[$k1]['exist_check']) {
				if(isset($d1['check'])) {
					$editData[$k1]['check'] = $d1['check'];
					if (count($fields_default[$k1]['check']) != count($d1['check']) ){
						$isChange = true;
					}
				}
				//全部チェックを外された場合で必要チェックがない場合
				else {
					$editData[$k1]['check'] = array();
					$isChange = true;
				}

			}
			if($fields_default[$k1]['is_possible_not_display']) {
				$editData[$k1]['is_display'] = true;
				if ($d1['is_display'] == "false") {
					$isChange = true;
					$editData[$k1]['is_display'] = false;
				}
			}
		}
		$set_data['SALON_CONFIG_FRONT_ITEMS_SET'] = "";
		if ($isChange) {
			$set_data['SALON_CONFIG_FRONT_ITEMS_SET'] = serialize($editData);
		}

		return $set_data;

	}


}