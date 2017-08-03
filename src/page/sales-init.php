<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Sales_Init extends Salon_Page {

	private $init_datas =  null;
	private $target_day_from = '';
	private $target_day_to = '';
	private $sub_menu = '';

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$target_day = parent::calcTargetDate();

		if ($_POST['target_date_zengo'] == 'before' ) {
			$this->target_day_from  = $target_day;
			$this->target_day_to  = date_i18n("Y-m-d 23:59:59");
		}
		else {
			$this->target_day_from  = date_i18n("Y-m-d");
			$this->target_day_to  = $target_day;
		}
		if (!empty($_POST['sub_menu']) && $_POST['sub_menu'] == 'reserve' ) {
			$this->sub_menu = 'reserve';
		}

	}

	public function get_init_datas() {
		return $this->init_datas;

	}
	public function set_init_datas($init_datas) {
		$this->init_datas = $init_datas;

	}

	public function get_target_day_from () {
		return $this->target_day_from;
	}
	public function get_target_day_to () {
		return $this->target_day_to;
	}

	public function get_target_branch_cd() {
		return $_POST['target_branch_cd'];
	}

	public function get_sub_menu() {
		return $this->sub_menu;
	}

	public function show_page() {
		foreach ($this->init_datas as $k1 => $d1) {
			$this->init_datas[$k1]['remark'] = htmlspecialchars($d1['remark'],ENT_QUOTES);
			$this->init_datas[$k1]['remark_bef'] = htmlspecialchars($d1['remark_bef'],ENT_QUOTES);
			$this->init_datas[$k1]['name'] = htmlspecialchars($d1['name'],ENT_QUOTES);
			$this->init_datas[$k1]['staff_name_bef'] = htmlspecialchars($d1['staff_name_bef'],ENT_QUOTES);
			$this->init_datas[$k1]['staff_name_aft'] = htmlspecialchars($d1['staff_name_aft'],ENT_QUOTES);
			$this->init_datas[$k1]['item_name_bef'] = htmlspecialchars($d1['item_name_bef'],ENT_QUOTES);
			$this->init_datas[$k1]['coupon_name'] = htmlspecialchars($d1['coupon_name'],ENT_QUOTES);
			$this->init_datas[$k1]['memo'] = unserialize($d1['memo']);
		}

		$this->echoInitData($this->init_datas);
	}
}