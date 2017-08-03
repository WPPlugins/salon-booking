<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Staff_Edit extends Salon_Page {

	private $branch_datas = null;
	private $table_data = null;
	private $branch_name = '';

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}


	public function set_table_data($table_data) {
		foreach ($table_data  as $k1 => $d1 ) {
			$this->table_data[$k1] = $d1;
		}
	}



	public function set_staff_cd($staff_cd) {
		$this->table_data['staff_cd'] = $staff_cd;
	}

	public function get_branch_cd() {
		return $this->table_data['branch_cd'];
	}

	public function set_branch_name($branch_name) {
		$this->table_data['branch_name'] = $branch_name;
	}
	public function set_position_name($position_name) {
		$this->table_data['position_name'] = $position_name;
	}

	public function check_request() {
		if (defined ( 'SALON_DEMO' ) && SALON_DEMO ) {
			throw new Exception(Salon_Component::getMsg('I001',null) ,1);
		}
		if	( ($_POST['type'] != 'inserted' ) && empty($_POST['staff_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) ,1);
		}
		if ( $_POST['type'] == 'deleted' && $_POST['staff_cd'] == get_option('salon_initial_user',1) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),2 );
		}
		$msg = null;
		if ($_POST['type'] != 'deleted' ) {
			if (Salon_Page::serverCheck(array('first_name','last_name','branch_cd','position_cd','zip','address','tel','mobile','mail','user_login','remark','employed_day','leaved_day','item_cds_set','memo'),$msg) == false) {
				throw new Exception($msg,4 );
			}
		}
	}

	public function show_page() {
		$res = array();
		$res['no'] = __($_POST['type'],SL_DOMAIN);
		$res['check'] = '';
		$res['staff_cd'] = $this->table_data['staff_cd'];
		if ( $_POST['type'] == 'deleted' ) {
			$res['staff_cd'] ='';
			$res['branch_cd'] = '';
		}
		else {
			$res['branch_cd'] = $this->table_data['branch_cd'];
		}
		$res['ID'] = $this->table_data['ID'];
		$res['branch_name'] = htmlspecialchars($this->table_data['branch_name'],ENT_QUOTES);
		$res['position_cd'] = $this->table_data['position_cd'];
		$res['position_name'] = htmlspecialchars($this->table_data['position_name'],ENT_QUOTES);
		$res['zip'] = $this->table_data['zip'];
		$res['address'] = htmlspecialchars($this->table_data['address'],ENT_QUOTES);
		$res['tel'] = $this->table_data['tel'];
		$res['mobile'] = $this->table_data['mobile'];
		$res['mail'] = $this->table_data['mail'];
		$res['last_name'] = htmlspecialchars($this->table_data['last_name'],ENT_QUOTES);
		$res['first_name'] = htmlspecialchars($this->table_data['first_name'],ENT_QUOTES);
		$res['remark'] = htmlspecialchars($this->table_data['remark'],ENT_QUOTES);
		$res['photo'] = $this->table_data['photo'];
		$res['photo_result'] = $this->table_data['photo_result'];
//			$res['employed_day'] = parent::editYmdForHtml($this->table_data['employed_day']);
//			$res['leaved_day'] = parent::editYmdForHtml($this->table_data['leaved_day']);
		$res['employed_day'] = $this->table_data['employed_day'];
		$res['leaved_day'] = $this->table_data['leaved_day'];
		$res['duplicate_cnt'] = $this->table_data['duplicate_cnt'];
		$res['user_login'] = $this->table_data['user_login'];
		$res['display_sequence'] = $this->table_data['display_sequence'];
		$res['in_items'] = $this->table_data['in_items'];
		$res['memo'] = htmlspecialchars($this->table_data['memo'],ENT_QUOTES);

		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($res).' }';
	}


}