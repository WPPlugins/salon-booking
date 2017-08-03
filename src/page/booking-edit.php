<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Booking_Edit extends Salon_Page {


	protected $table_data = null;
	protected $reservation_cd = '';
	private $role = null;
	private $branch_cd = '';

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->branch_cd = $_POST['branch_cd'];
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

	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}

	public function get_table_data() {
		return $this->table_data;
	}

	public function get_branch_cd() {
		return $this->branch_cd;
	}

	public function set_reservation_cd($reservation_cd) {
		$this->reservation_cd = $reservation_cd;
		$this->table_data['reservation_cd'] = $reservation_cd;
	}
	public function get_reservation_cd() {
		return $this->reservation_cd;
	}

	public function is_editBooking() {
		return $this->_is_editBooking();
	}


	public function check_request() {
		$this->_parse_data();

		$msg = null;
		if ($this->_is_editBooking() ) $check_item = array('customer_name','branch_cd','time_from','time_to');
		else  $check_item = array('customer_name','branch_cd','branch_tel','item_cds','mail','time_from','time_to');
		if (parent::serverCheck($check_item,$msg) == false) {
			throw new Exception($msg,__LINE__ );
		}
	}

	private function _parse_data() {
		$_POST['type'] = $_POST['!nativeeditor_status'];
		$_POST['reservation_cd'] = intval($_POST['id']);
		$this->reservation_cd = intval($_POST['id']);
//		if (empty($_POST['item_cd']) ) $_POST['item_cds'] = '';
//		else $_POST['item_cds'] = $_POST['item_cd'];
		$_POST['sl_memo'] = "";
		if ($this->config_datas['SALON_CONFIG_USE_SUBMENU'] == Salon_Config::USE_SUBMENU) {
			$_POST['sl_memo'] = json_decode(stripslashes($_POST['memo']),true);
			$msg = "";
			switch (json_last_error()) {
			case JSON_ERROR_NONE:
				break;
			case JSON_ERROR_DEPTH:
				$msg = ' - Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$msg = ' - Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$msg = ' - Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				$msg = ' - Syntax error, malformed JSON';
				break;
			default:
				$msg = ' - Unknown error';
				break;
			}
			if ($msg != "") {
				throw new Exception(Salon_Component::getMsg('E401',__LINE__."json decode".$msg),1);
			}
		}
	}

	public function show_page() {
		//ログインしていないときは、更新はできないようにする
		$edit_flg = Salon_Edit::NG;
		$type = htmlspecialchars($_POST['type']);
//		$ID = floatval($_POST['id']);
		$ID = $_POST['id'];
		$status = '';
		$time_to = '';
		$user_login = "";
		if (is_user_logged_in()	) {
			$edit_flg = Salon_Edit::OK;
			$tid = $this->reservation_cd;
			$msg = __('The reservation is completed',SL_DOMAIN);
			$edit_name = '';
			$p2  = '';
			if ($_POST['type'] != 'deleted' ) {
				$p2 = $this->table_data['non_regist_activate_key'];
				$edit_name = htmlspecialchars($this->table_data['non_regist_name'],ENT_QUOTES);
				$status = $this->table_data['status'];
				$time_to = $this->table_data['time_to'];
				$user_login = $this->table_data['user_login'];
			}
		}
		else {
			$edit_flg = Salon_Edit::NG;
			$p2 = '';
			$tid = $ID;
//			$edit_name = __('tempolary reserved',SL_DOMAIN).'('.htmlspecialchars($this->table_data['non_regist_name'],ENT_QUOTES).')';
			$edit_name = htmlspecialchars($this->table_data['non_regist_name'],ENT_QUOTES);
			if ($this->config_datas['SALON_CONFIG_CONFIRM_STYLE'] == Salon_Config::CONFIRM_BY_MAIL ) {
				$msg = __('The reservation is not completed.Please confirm your reservation by [confirm form] in E-mail ',SL_DOMAIN);
				$status = Salon_Reservation_Status::TEMPORARY;
			}
			else if ($this->config_datas['SALON_CONFIG_CONFIRM_STYLE'] == Salon_Config::CONFIRM_BY_ADMIN ) {
				$msg = __('The reservation is not completed.After your reservation confirmed by administrator ',SL_DOMAIN);
				$status = Salon_Reservation_Status::TEMPORARY;
			}
			else if ($this->config_datas['SALON_CONFIG_CONFIRM_STYLE'] == Salon_Config::CONFIRM_NO ) {
				$msg = __('The reservation is completed',SL_DOMAIN);
				$status = Salon_Reservation_Status::COMPLETE;
			}
			$time_to = $this->table_data['time_to'];
		}
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8" ?>';
		echo <<<EOT
		<data>
			<action type="{$type}"
					sid="{$ID}"
					tid="{$tid}"
					name="{$edit_name}"
					status="{$status}"
				 	end_date  = "{$time_to}"
					p2 = "{$p2}"
					user_login="{$user_login}"
					edit_flg="{$edit_flg}"
					alert_msg = "{$msg}" >
			</action>
		</data>
EOT;
	}


}