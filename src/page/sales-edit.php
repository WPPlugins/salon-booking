<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Sales_Edit extends Salon_Page {

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
		if ( (substr($_POST['type'],0,8) != 'inserted' ) && empty($_POST['reservation_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}
		$msg = null;
		if ($_POST['type'] != 'deleted' ) {
			if ($_POST['type'] == 'inserted_reserve' ) $check_array = array('customer_mail','customer_tel','customer_name','target_day','staff_cd','item_cds','remark','price');
			else $check_array = array('target_day','staff_cd','item_cds','remark','price');
			if (Salon_Page::serverCheck($check_array,$msg) == false) {
				throw new Exception($msg ,1);
			}
		}
	}

	public function show_page() {
		$res = $this->table_data;

		$res['no'] = __(substr($_POST['type'],0,8),SL_DOMAIN);
		$res['check'] = '';

		$res['target_day'] = $this->table_data['target_day'];
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
		$res['reserved_time']  = $this->table_data['reserved_time'];
		if ($_POST['type'] == 'inserted_reserve' && !empty($_POST['regist_customer'] ) )	$res['regist_msg'] = Salon_Component::getMsg('I002',array($this->table_data['user_login'],$this->user_pass));

		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($res).' }';
	}


}