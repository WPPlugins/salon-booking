<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Booking_Get_Mobile extends Salon_Page {

	private $target_day = '';
	private $reservation_datas = null;
	private $item_datas =  null;
	private $branch_cd = '';
	private $first_hour = '';
	private $last_hour = '';

	private $user_login = '';

	private $checkOk = true;
	private $msg = '';

	private $role = null;

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->branch_cd = $_POST['branch_cd'];
		$this->first_hour = $_POST['first_hour'];
		$this->last_hour = $_POST['last_hour'];
		$this->target_day = $_POST['target_day'];

	}

	public function set_role($role) {
		$this->role = $role;
	}
	private function _is_editBooking() {
			if (in_array('edit_booking',$this->role) || $this->isSalonAdmin() ) return true;
	}

	public function get_target_day() {
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



	public function check_request() {

		$check_item = array('target_day_mobile','branch_cd');
		$this->checkOk = parent::serverCheck($check_item,$this->msg);
		if ($this->checkOk) {
			$target = date("Y-m-d H:i:s", strtotime($_POST['target_day']));
			$before = Salon_Component::computeDate(-1*$this->config_datas['SALON_CONFIG_BEFORE_DAY']);
			$after = Salon_Component::computeDate($this->config_datas['SALON_CONFIG_AFTER_DAY']);
			if ($target < $before || $after < $target) {
			  $this->checkOk = false;
			  $this->msg  .=  (empty($this->msg) ? '' : "\n"). 'EM005 '.__('Date is out of ranges.',SL_DOMAIN);
			}
		}
		return $this->checkOk;

	}

	public function show_page() {
		if ($this->checkOk ) {
			$res = parent::echoMobileData($this->reservation_datas,$this->target_day
					,$this->first_hour,$this->last_hour,$this->user_login,$this->role);
			echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
			"set_data":'.'{"'.$this->target_day.'":'.$res[$this->target_day].'} }';
		}
		else {
			$msg['status'] = 'Error';
			if (empty($errno) ) $msg['message'] = $this->msg;
			else $msg['message'] = $errstr.$detail_msg.'('.$errno.')';
			echo json_encode($msg);
		}


	}
}