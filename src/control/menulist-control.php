<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/menulist-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/menulist-component.php');

class Menulist_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;

	private $branch_cd = '';


	function __construct($branch_cd) {
		parent::__construct();
		$this->branch_cd = $branch_cd;
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Menulist_Page';
			$this->set_response_type(Response_Type::HTML);
		}
// 		else {
// 			$this->action_class = $_REQUEST['menu_func'];
// 		}
		$this->datas = new Menulist_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Menulist_Component($this->datas);
		$this->permits = array('Menulist_Page');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());
		if ($this->action_class == 'Menulist_Page' ) {
			$this->pages->set_branch_datas($this->datas->getBranchData($this->branch_cd));
			$this->pages->set_menu_datas($this->datas->getTargetItemData($this->branch_cd));
		}
		$this->pages->show_page();
		if ($this->action_class != 'Menulist_Page' ) wp_die();
	}
}		//class



