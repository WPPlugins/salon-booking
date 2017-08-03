<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Customer_Edit extends Salon_Page {

	private $branch_datas = null;
	private $table_data = null;
	private $customer_cd = '';
	private $branch_name = '';

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}


	public function set_table_data($table_data) {
		foreach ($table_data  as $k1 => $d1 ) {
			$this->table_data[$k1] = $d1;
		}
	}


	public function set_customer_cd($customer_cd) {
		$this->table_data['customer_cd'] = $customer_cd;
	}
//[TODO] これはいるの？
	public function set_user_id ($user_id) {
		$_POST['ID'] = $user_id;

	}

	public function get_branch_cd() {
		return $this->table_data['branch_cd'];
	}

	public function set_branch_name($branch_name) {
		$this->table_data['branch_name'] = $branch_name;
	}

	public function check_request() {
		if	( ($_POST['type'] != 'inserted' ) && empty($_POST['customer_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) ,1);
		}
		$msg = null;
		if ($_POST['type'] != 'deleted' ) {
			if (Salon_Page::serverCheck(array('first_name','last_name','branch_cd','zip','address','tel','mobile','customer_mail','user_login','remark','rank_patern'),$msg) == false) {
				throw new Exception($msg ,1);
			}
		}
	}

	public function show_page() {
		$res = array();

		$res['no'] = __($_POST['type'],SL_DOMAIN);
		$res['check'] = '';
		$res['customer_cd'] = $this->table_data['customer_cd'];


		if ( $_POST['type'] == 'deleted' ) {
			$res['branch_cd'] = '';
			$res['customer_cd'] = '';
		}
		else {
			$res['branch_cd'] = $this->table_data['branch_cd'];
		}

		$res['ID'] = $this->table_data['ID'];
		$res['zip'] = $this->table_data['zip'];
		$res['address'] = htmlspecialchars($this->table_data['address'],ENT_QUOTES);
		$res['tel'] = $this->table_data['tel'];
		$res['mobile'] = $this->table_data['mobile'];
		$res['mail'] = $this->table_data['mail'];
		$res['last_name'] = htmlspecialchars($this->table_data['last_name'],ENT_QUOTES);
		$res['first_name'] = htmlspecialchars($this->table_data['first_name'],ENT_QUOTES);
		$res['remark'] = htmlspecialchars($this->table_data['remark'],ENT_QUOTES);
		$res['branch_name'] = htmlspecialchars($this->table_data['branch_name'],ENT_QUOTES);
		$res['user_login'] =  htmlspecialchars($this->table_data['user_login']);
		$res['rank_patern_cd'] = $this->table_data['rank_patern_cd'];
//		}

		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($res).' }';
	}


}