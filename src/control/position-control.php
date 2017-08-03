<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/position-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/position-component.php');

class Position_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Position_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Position_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Position_Component($this->datas);
		$this->permits = array('Position_Page','Position_Init','Position_Edit');

	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());

		if ($this->action_class == 'Position_Page' ) {
			$this->pages->set_admin_menu_datas($this->comp->getAdminMenuDatas());
		}
		elseif ($this->action_class == 'Position_Init' ) {
			$this->pages->set_init_datas($this->datas->getInitDatas());
		}
		elseif ($this->action_class == 'Position_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->pages->set_table_data($res);
			if ($_POST['type'] == 'inserted' ) {
				$this->pages->set_position_cd($this->datas->insertTable( $res));
			}
			if ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res);
			}
			if ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteTable( $res);
			}
		}
		$this->pages->show_page();
		if ($this->action_class != 'Position_Page' ) wp_die();
	}
}		//class

