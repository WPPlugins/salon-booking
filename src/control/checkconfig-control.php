<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/checkconfig-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/checkconfig-component.php');

class Checkconfig_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Checkconfig_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Checkconfig_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Checkconfig_Component($this->datas);
		$this->permits = array('Checkconfig_Page');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());

		if ($this->action_class == 'Checkconfig_Page' ) {
			$this->pages->setDatas("PLUGIN",$this->comp->checkSettings());
			$this->pages->setDatas("PLUGIN DETAIL",$this->comp->getPluginsInfo());
			$this->pages->setDatas("THEME",$this->comp->getThemeInfo());
			$this->pages->setDatas("CACHE",$this->comp->checkCache());
			$this->pages->setDatas("TABLE",$this->datas->getTableData());
			$this->pages->setDatas("CONFIG",$this->datas->getConfigShowData());

		}

		$this->pages->show_page();
		if ($this->action_class != 'Checkconfig_Page' ) wp_die();
	}
}		//class


