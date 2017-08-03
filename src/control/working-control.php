<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/working-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/working-component.php');

class Working_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';

	private $branch_cd = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Working_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
			$this->set_response_type(Response_Type::XML);
		}
		$this->datas = new Working_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Working_Component($this->datas);
		$this->permits = array('Working_Page','Working_Init','Working_Get_Data','Working_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());
		if ($this->action_class == 'Working_Page' ) {
			$user_login = $this->datas->getUserLogin();
			$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login));
			$set_branch_cd = $this->pages->get_set_branch_cd();
			if ($this->pages->isSalonAdmin() && empty($set_branch_cd) == false ) $branch_cd = $set_branch_cd;
			else $branch_cd = $this->datas->getBracnCdbyCurrentUser($user_login);

			if ($this->pages->isSalonAdmin() ) $this->pages->set_all_branch_datas($this->datas->getAllBranchData());

			$this->pages->set_current_user_branch_cd($branch_cd);
			$this->pages->set_branch_datas($this->datas->getBranchData($branch_cd));
			$this->pages->set_item_datas($this->datas->getTargetItemData($branch_cd));
			$this->pages->set_staff_datas($this->datas->getTargetStaffData($branch_cd));

		}
		elseif ($this->action_class == 'Working_Init' ) {
			$user_login = $this->datas->getUserLogin();
			$role = array();
			$isSalonAdmin = $this->datas->isSalonAdmin($user_login,$role);
			if (in_array('edit_workgin_all',$role) || $isSalonAdmin ) {
				$branch_cd = $this->pages->get_target_branch_cd();
				$this->pages->set_init_datas($this->comp->editInitData($branch_cd));
			}
			else {
				$this->pages->set_init_datas($this->comp->editInitData(null,$user_login));
			}

		}
		elseif ($this->action_class == 'Working_Get_Data' ) {
			$this->pages->set_working_datas($this->datas->getWorkingData($this->pages->get_target_day(),$this->pages->get_staff_cd()));
		}
		elseif ($this->action_class == 'Working_Edit' ) {
			$user_login = $this->datas->getUserLogin();
			$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login));
			$this->pages->check_request();
			$result = $this->comp->editTableData();
			$this->comp->serverCheck($result);
			$this->pages->set_table_data($result);
			if ($_POST['type'] == 'inserted' ) {
				$this->datas->insertTable($result);
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable($result);
			}
			elseif ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteTable($result);
			}

		}

		$this->pages->show_page();
		if ($this->action_class != 'Working_Page' ) wp_die();
	}
}		//class

