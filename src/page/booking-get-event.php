<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Booking_Get_Event extends Salon_Page {

	private $target_day = '';
	private $reservation_datas = null;
	private $item_datas =  null;
	private $branch_cd = '';

	private $user_login = '';
	private $role = null;


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->branch_cd = $_GET['branch_cd'];

	}

	public function set_role($role) {
		$this->role = $role;
	}
	private function _is_editBooking() {
			if (($this->branch_cd == $this->user_branch_cd && in_array('edit_booking',$this->role))
			|| $this->isSalonAdmin() ) {
				return true;
			}
	}

	public function get_target_day($before) {
		$this->target_day = Salon_Component::computeDate(-1*$before);
		return $this->target_day;
	}

	public function get_branch_cd() {
		return $this->branch_cd;
	}


	public function set_reservation_datas($reservation_datas) {
		$this->reservation_datas = $reservation_datas;

	}

	public function set_item_datas($item_datas) {
		$this->item_datas = $item_datas;

	}

	public function set_user_login($user_login) {
		$this->user_login = $user_login;
	}


	public function show_page() {
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8" ?>';
		$OK = Salon_Edit::OK;
		$NG = Salon_Edit::NG;
		$randam_num = mt_rand(1000000,9999999);
		echo '<data>';

		foreach ($this->reservation_datas as $k1 => $d1 ) {
			if (( ! empty($this->user_login) &&  $this->user_login === $d1['user_login'] )
				|| 	$this->_is_editBooking() ) {
				$name = htmlspecialchars($d1['name'],ENT_QUOTES);
				$edit_name = __('reserved',SL_DOMAIN);
				if (!empty($name) ) {
					$edit_name = sprintf(__("%s reserved",SL_DOMAIN),$name);
				}
				$edit_remark = htmlspecialchars($d1['remark'],ENT_QUOTES);

				$edit_tel= htmlspecialchars($d1['tel'],ENT_QUOTES);
				$edit_user_login = htmlspecialchars($d1['user_login'],ENT_QUOTES);

				$edit_memo = "";

				if ($this->config_datas['SALON_CONFIG_USE_SUBMENU'] == Salon_Config::USE_SUBMENU) {
					$edit_memo = htmlspecialchars(json_encode(unserialize($d1['memo'])));
				}

				echo <<<EOT2
					<event id ="{$d1['reservation_cd']}"
				 	staff_cd =  "{$d1['staff_cd']}"
				 	start_date= "{$d1['time_from']}"
				 	end_date  = "{$d1['time_to']}"
					edit_flg  = "{$OK}"
					name = "{$name}"
					mail = "{$d1['email']}"
					tel = "{$edit_tel}"
					text = "{$edit_name}"
					remark = "{$edit_remark}"
					item_cds = "{$d1['item_cds']}"
					p2 = "{$d1['non_regist_activate_key']}"
					user_login = "{$edit_user_login}"
					coupon = "{$d1['coupon']}"
					memo = "{$edit_memo}"
EOT2;
			}
			else {
				if ($d1['status'] == Salon_Reservation_Status::COMPLETE ) 	$edit_name = __('reserved',SL_DOMAIN);
				else $edit_name = __('tempolary reserved',SL_DOMAIN);
				$temp_num = $d1['reservation_cd']+$randam_num;
				echo <<<EOT3
					<event id ="{$temp_num}"
				 	staff_cd =  "{$d1['staff_cd']}"
				 	start_date= "{$d1['time_from']}"
				 	end_date  = "{$d1['time_to']}"
					edit_flg      = "{$NG}"
					name = "{$edit_name}"
					mail = ""
					tel = ""
					text = "{$edit_name}"
					remark = ""
					item_cds = ""
					p2 = ""
					user_login = ""
					coupon = ""
					memo = ""
EOT3;
			}
			echo ' status = "'.$d1['status'].'" '.'/>';
		}

		echo '</data>';


	}
}