<?php

require_once(SL_PLUGIN_SRC_DIR . 'page/booking_common.php');


class BookingFront_Page extends BookingCommon_Page {

	const Y_PIX = 550;

	private $branch_datas = null;
	private $item_datas = null;
	private $staff_datas = null;
	private $working_datas = null;

	private $first_hour = '';
	private $last_hour = '';
	private $insert_max_day = '';
	private $datepicker_max_day = '';
	private $datepicker_min_day = '';

	private $reseration_cd = '';

	private $target_year = '';


	private $role = null;

	private $url = '';

	private $reservation_datas = null;

	private $user_inf = null;

	private $promotion_datas = null;

	private $current_time = '';
	private $close_24 = '';
	private $close_48 = '';

	private $month_datas = null;
	private $category_datas = null;

	private $first_valid_yyyymmdd = "";

	protected  $set_menu_type = "checkbox";


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->target_year = date_i18n("Y");
		$url = get_bloginfo('wpurl');
		if (is_ssl() && strpos(strtolower ( $url),'https') === false ) {
			$url = preg_replace("/[hH][tT][tT][pP]:/","https:",$url);
		}
		$this->url = $url;
		$this->current_time = date_i18n("Hi");
	}

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
		$this->first_hour = substr($this->branch_datas['open_time'],0,2);
		$this->last_hour = substr($this->branch_datas['close_time'],0,2);
		if (intval(substr($this->branch_datas['close_time'],2,2)) > 0 ) $this->last_hour++;
		$this->close_24 = $this->branch_datas['close_time'];
		$this->close_48 = +$this->branch_datas['close_time'];
		if ($this->last_hour > 23 ) {
			$this->close_24 = sprintf("%02d",+$this->last_hour-24).substr($this->branch_datas['close_time'],2,2);
		}
		$this->first_valid_yyyymmdd = $this->getFirstValidYYYYMMDD($branch_datas);
		$minDate = $this->get_targetDate_for_mobile();
		$this->datepicker_min_day = substr($minDate,0,4).','.(intval(substr($minDate,4,2))-1).','.(intval(substr($minDate,6,2)));
	}

	public function set_category_datas($category_datas) {
		$this->category_datas = $category_datas;
	}

	public function get_targetDate_for_mobile () {
		$init_target_day = date_i18n('Ymd');

		if ( $this->close_48 > 2400  ) {
			$close = +$this->current_time + 2400;
			if (+$this->branch_datas["open_time"] > +$this->current_time
			&& $this->close_48 >= $close)  {
				$init_target_day = date('Ymd',strtotime(date_i18n('Y-m-d')." -1 day"));
			}
		}
		return $init_target_day;
	}

	public function set_item_datas ($item_datas) {
		$this->item_datas = $item_datas;
	}

	public function set_staff_datas ($staff_datas) {
		$this->staff_datas = $staff_datas;
		if (count($this->staff_datas) === 0 ) {
			throw new Exception(Salon_Component::getMsg('E010',__function__.':'.__LINE__ ) );
		}
	}


	public function set_working_datas ($working_datas) {
		$this->working_datas = $working_datas;
	}


	public function set_promotion_datas ($promotion_datas) {
		$this->promotion_datas = $promotion_datas;

	}


	public function set_role($role) {
		$this->role = $role;
	}

	public function set_reservation_datas($reservation_datas) {
		$this->reservation_datas = $reservation_datas;

	}

	public function set_month_datas($month_datas) {
		$this->month_datas = $month_datas;

	}

	public function set_user_inf($user_inf) {
		$this->user_inf = $user_inf;
	}

	private function _is_userlogin() {
		return $this->config_datas['SALON_CONFIG_USER_LOGIN'] == Salon_Config::USER_LOGIN_OK ;
	}

	private function _is_noPreference() {
		return $this->config_datas['SALON_CONFIG_NO_PREFERENCE'] == Salon_Config::NO_PREFERNCE_OK;
	}

	private function _is_staffSetNormal() {
		return $this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_NORMAL;
	}

	private function _is_editBooking() {
			if (in_array('edit_booking',$this->role) || $this->isSalonAdmin() ) return true;
	}

	public function set_config_datas($config_datas) {
		$config_datas['SALON_CONFIG_FRONT_ITEMS'] = $this->setFrontItems($config_datas['SALON_CONFIG_FRONT_ITEMS']);
		//スタッフを表示しない場合は、指名なしに
		if ($config_datas['SALON_CONFIG_FRONT_ITEMS']['staff']['is_possible_not_display']
				&& ! $config_datas['SALON_CONFIG_FRONT_ITEMS']['staff']['is_display'] ) {
					$config_datas['SALON_CONFIG_NO_PREFERENCE'] = Salon_Config::NO_PREFERNCE_NG;
		}
		$this->config_datas = $config_datas;
		$edit = Salon_Component::computeDate($config_datas['SALON_CONFIG_AFTER_DAY']);
		$this->insert_max_day = substr($edit,0,4).','.(intval(substr($edit,5,2))-1).','.(intval(substr($edit,8,2)+1));

		$this->datepicker_max_day = substr($edit,0,4).','.(intval(substr($edit,5,2))-1).','.(intval(substr($edit,8,2)));

			$this->set_menu_type = "checkbox";
		if ($config_datas['SALON_CONFIG_MENU_TYPE'] == Salon_Category::RADIO) {
			$this->set_menu_type = "radio";
		}
	}

	public function echo_menu_type() {
		echo $this->set_menu_type;
	}

	public function doSortStaff() {
		if ($this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_NORMAL
		|| $this->config_datas['SALON_CONFIG_DO_SORT_STAFF_AUTO'] == Salon_YesNo::No) {
			return;
		}
		//最初に存在する配列をつくる
		$targetDate = $this->get_targetDate_for_mobile();
		if (array_key_exists($targetDate,
				$this->working_datas) !== false) {
			$existStaffCd = null;
			$tmpRowExist = null;
			$tmpRowNotExist = null;
			foreach ($this->working_datas[$targetDate] as $k1 => $d1) {
				$existStaffCd[$d1['staff_cd']] = "";
			}
			foreach ($this->staff_datas as $k1 => $d1){
				if (array_key_exists($d1['staff_cd'],$existStaffCd) ) {
					$tmpRowExist[] = $d1;
				}
				else {
					$tmpRowNotExist[] = $d1;
				}
			}
			$this->staff_datas = array();
			if (!is_null($tmpRowExist)) {
				foreach($tmpRowExist as $d1) {
					$this->staff_datas[] = $d1;
				}
			}
			if (!is_null($tmpRowNotExist)) {
				foreach($tmpRowNotExist as $d1) {
					$this->staff_datas[] = $d1;
				}
			}
		}
	}

	public function show_page() {

		if ( Salon_Component::isMobile() ) {
			require(SL_PLUGIN_SRC_DIR . '/page/booking_mobile-page.php');
		}
		else {
			require(SL_PLUGIN_SRC_DIR . '/page/booking_pc-page.php');
		}
	}
	private function _editDate($yyyymmdd) {
		return substr($yyyymmdd,0,4). substr($yyyymmdd,5,2).  substr($yyyymmdd,8,2);
	}
	private function _editTime($yyyymmdd) {
		return substr($yyyymmdd,11,2). substr($yyyymmdd,14,2);
	}

	private function _echoLoadTab() {
		if (empty($this->config_datas['SALON_CONFIG_LOAD_TAB'])) echo "timeline";
		else {
			$setData = array("timeline","timeline","month","week","day");
			echo $setData[$this->config_datas['SALON_CONFIG_LOAD_TAB']];
		}
	}

}		//class

