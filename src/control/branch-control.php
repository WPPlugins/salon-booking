<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/branch-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/branch-component.php');

class Branch_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';

	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Branch_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Branch_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Branch_Component($this->datas);
		$this->permits = array('Branch_Page','Branch_Init','Branch_Edit','Branch_Col_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());
		if ($this->action_class == 'Branch_Page' ) {
			$this->pages->set_branch_datas($this->datas->getAllBranchData());
			$this->pages->set_setting_patern_datas($this->datas->getSettingPaternDatas());
		}
		elseif ($this->action_class == 'Branch_Init' ) {
			$this->pages->set_init_datas($this->comp->editInitDatas());
		}
		elseif ($this->action_class == 'Branch_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->comp->serverCheck($res);
			$this->pages->set_table_data($res);
			if ($_POST['type'] == 'inserted' ) {
				$this->pages->set_branch_cd($this->datas->insertTable( $res));
			}
			if ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res );
				$name = $this->datas->getBranchData($this->pages->get_branch_cd(),'name');
				$this->pages->set_branch_name($name['name']);
			}
			if ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteTable( $res);
			}
		}
		elseif ($this->action_class == 'Branch_Col_Edit' ) {
			$this->set_response_type($this->pages->getResponseType());
			$this->pages->check_request();
			$res = $this->comp->editColumnData();
			$this->pages->set_table_data($res);
			$this->datas->updateColumn($res);
		}

		$this->pages->show_page();
		if ($this->action_class != 'Branch_Page' ) wp_die();
	}
}		//class


