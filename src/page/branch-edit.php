<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Branch_Edit extends Salon_Page {

	private $branch_datas = null;
	private $table_data = null;

	private $branch_name = '';

	public function __construct($use_session) {
		parent::__construct(true,$use_session);
	}


	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}


	public function set_branch_cd($branch_cd) {
		$this->table_data['branch_cd'] = $branch_cd;
	}

	public function get_branch_cd() {
		return $this->table_data['branch_cd'];
	}

	public function set_branch_name($branch_name) {
		$this->table_data['branch_name'] = $branch_name;
	}

	public function check_request() {
		if	( ($_POST['type'] != 'inserted' ) && empty($_POST['branch_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) ,1);
		}
		$msg = null;
		if ($_POST['type'] != 'deleted' ) {
			if (Salon_Page::serverCheck(array('branch_name','zip','address','branch_tel','mail','open_time','close_time','time_step','closed_day_check','remark','duplicate_cnt'),$msg) == false) {
				throw new Exception($msg ,1);
			}
		}
		if (defined ( 'SALON_DEMO' ) && SALON_DEMO && $_POST['type'] == 'deleted' ) {
			throw new Exception(Salon_Component::getMsg('I003',null) ,1);
		}
	}

	public function show_page() {
		$res = array();

		$res['no'] = __($_POST['type'],SL_DOMAIN);
		$res['check'] = '';

		$res['branch_cd'] = $this->table_data['branch_cd'];

		if ( $_POST['type'] != 'deleted' ) {
			$res['name'] = htmlspecialchars($this->table_data['name'],ENT_QUOTES);
			$res['zip'] = $this->table_data['zip'];
			$res['address'] = htmlspecialchars($this->table_data['address'],ENT_QUOTES);
			$res['tel'] = $this->table_data['tel'];
			$res['mail'] = $this->table_data['mail'];
			$res['remark'] = htmlspecialchars($this->table_data['remark'],ENT_QUOTES);
			$res['duplicate_cnt'] = $this->table_data['duplicate_cnt'];
			$res['open_time'] = $this->table_data['open_time'];
			$res['close_time'] = $this->table_data['close_time'];
			$res['time_step'] = $this->table_data['time_step'];
			$res['closed'] = $this->table_data['closed'];
			$res['memo'] = $this->table_data['memo'];
			$res['shortcode'] = '[salon-booking branch_cd='.$this->table_data['branch_cd'].']';

		}

		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($res).' }';
	}


}