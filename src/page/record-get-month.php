<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Record_Get_Month extends Salon_Page {

	private $init_datas =  null;

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);

	}

	public function get_init_datas() {
		return $this->init_datas;

	}
	public function set_init_datas($init_datas) {

		$this->init_datas = $init_datas;

	}

	public function get_target_branch_cd() {
		return intval($_POST['target_branch_cd']);
	}

	public function get_from() {
		return $_POST['from'];
	}
	public function get_to() {
		return $_POST['to'];
	}

	public function get_customer_cd() {
		return $_POST['user_login'];
		}


	public function show_page() {
		$key = "";
		$cnt = 0;
		//ここには1月分の値しか設定されない
		foreach ($this->init_datas as $k1 => $d1) {
			$key = $k1;
			$cnt = count($d1);
			foreach ($d1 as $k2 => $d2 ) {
				$this->init_datas[$k1][$k2]['name'] = htmlspecialchars($d2['name'],ENT_QUOTES);
				$this->init_datas[$k1][$k2]['record'] = unserialize($d2['record']);
				unset($this->init_datas[$k1][$k2]['branch_cd']);
				unset($this->init_datas[$k1][$k2]['target_month']);
			}
			break;
		}
		$jdata = array();

		$jdata['yyyymm'] =$key;
		$jdata['cnt'] = $cnt;
		$datas = $this->init_datas[$key];
		if (is_null($datas) )$datas = array();
		$jdata['datas'] = $datas;
		echo json_encode($jdata);
	}
}