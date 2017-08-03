<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Reservation_Edit extends Salon_Page {

	private $table_data = null;
	private $user_pass = '';

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$user_pass = '';
	}


	public function set_table_data($table_data) {
		$this->table_data = $table_data;

	}


	public function get_reservation_cd () {
		return $this->table_data['reservation_cd'];
	}
	public function get_branch_cd () {
		return $this->table_data['branch_cd'];
	}

	public function set_user_pass($user_pass) {
		$this->user_pass = $user_pass;
	}

	public function check_request() {
		if ( ($_POST['type'] != 'inserted' ) && empty($_POST['reservation_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}
		$msg = null;
		if ($_POST['type'] != 'deleted' && $_POST['type'] != 'cancel' && $_POST['type'] != 'confirm') {
			if (Salon_Page::serverCheck(array('customer_mail','customer_tel','customer_name','target_day','staff_cd','item_cds','remark','price'),$msg) == false) {
				throw new Exception($msg,1 );
			}
		}
	}

	public function show_page() {

		$res = array();

		$res['no'] = __($_POST['type'],SL_DOMAIN);
		$res['check'] = '';


		if ( $_POST['type'] != 'deleted' ) {
			$res['reservation_cd'] = $this->table_data['reservation_cd'];
			$res['target_day'] = $this->table_data['target_day'];
			$res['user_login'] = $this->table_data['user_login'];
			$res['name'] = htmlspecialchars($this->table_data['name'],ENT_QUOTES);
			$res['email'] = $this->table_data['email'];
			$res['tel'] = $this->table_data['tel'];
			$res['time_from_bef'] = $this->table_data['time_from_bef'];
			$res['time_to_bef'] = $this->table_data['time_to_bef'];
			$res['branch_cd'] = $this->table_data['branch_cd'];
			$res['staff_cd_bef'] = $this->table_data['staff_cd_bef'];
			$res['item_cds_bef'] = $this->table_data['item_cds_bef'];
			$res['remark_bef'] = htmlspecialchars($this->table_data['remark_bef'],ENT_QUOTES);
			$res['time_from_aft'] = $this->table_data['time_from_aft'];
			$res['time_to_aft'] = $this->table_data['time_to_aft'];
			$res['staff_cd_aft'] = $this->table_data['staff_cd_aft'];
			$res['item_cds_aft'] = $this->table_data['item_cds_aft'];
			$res['remark'] = htmlspecialchars($this->table_data['remark'],ENT_QUOTES);
			$res['price'] = $this->table_data['price'];
			$res['staff_name_bef'] = htmlspecialchars($this->table_data['staff_name_bef'],ENT_QUOTES);
			$res['status'] = $this->table_data['status'];
			$res['status_name'] = $this->table_data['status_name'];
			$res['staff_name_aft'] = htmlspecialchars($this->table_data['staff_name_aft'],ENT_QUOTES);
			$res['item_cd_array_bef'] = $this->table_data['item_cd_array_bef'];
			$res['item_name_bef'] = htmlspecialchars($this->table_data['item_name_bef'],ENT_QUOTES);
			$res['item_cd_array_aft'] = $this->table_data['item_cd_array_aft'];
			$res['non_regist_activate_key'] = $this->table_data['non_regist_activate_key'];
			$res['reserved_time'] = $this->table_data['reserved_time'];
			$res['rstatus_cd'] = $this->table_data['rstatus_cd'];
			$res['rstatus'] = $this->table_data['rstatus'];
			$res['coupon'] = $this->table_data['coupon'];
			$res['memo'] = $this->table_data['memo'];
			if ($_POST['type'] == 'inserted' && !empty($_POST['regist_customer'] ) )	$res['regist_msg'] = Salon_Component::getMsg('I002',array($this->table_data['user_login'],$this->user_pass));
		}
//		else {
//			$res['name'] = htmlspecialchars($this->table_data['non_regist_name'],ENT_QUOTES);
//
//			$res['reservation_cd'] = $this->table_data['reservation_cd'];
//			$res['branch_cd'] = $this->table_data['branch_cd'];
//			$res['staff_cd'] = $this->table_data['staff_cd'];
//
//			$res['time_from'] = $this->table_data['time_from'];
//			$res['time_to'] = $this->table_data['time_to'];
//			$res['remark'] = htmlspecialchars($this->table_data['remark'],ENT_QUOTES);
//			$res['status'] = $this->table_data['status'];
//			$res['non_regist_name'] = htmlspecialchars($this->table_data['non_regist_name'],ENT_QUOTES);
//			$res['non_regist_email'] = $this->table_data['non_regist_email'];
//			$res['non_regist_tel'] = $this->table_data['non_regist_tel'];
//			$res['user_login'] = $this->table_data['user_login'];
//		}
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($res).' }';
	}


}