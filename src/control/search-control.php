<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/search-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/search-component.php');

class Search_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';

	private $permits = null;



	function __construct() {
		parent::__construct();
		$this->action_class = $_POST['menu_func'];
		$this->datas = new Search_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Search_Component($this->datas);
		$this->permits = array('Search_Page');
	}



	public function do_action() {

		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);

		$role = array();

		$user_login = $this->datas->getUserLogin();
		$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login,$role));
		$this->pages->set_role($role);
		$this->pages->check_request();

		$result = $this->comp->setSearchCustomerData($this->pages->get_search_items());
		if ($result !== false)  {
			$this->pages->set_result($result);
		}
		else $this->pages->setNodata();

		$this->pages->show_page();
		wp_die();
	}
}		//class

