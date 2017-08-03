<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/basic-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/basic-component.php');

class Basic_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Basic_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Basic_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Basic_Component($this->datas);
		$this->permits = array('Basic_Page','Basic_Init','Basic_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());

		if ($this->action_class == 'Basic_Page' ) {

			$user_login = $this->datas->getUserLogin();
			$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login));
			$set_branch_cd = $this->pages->get_set_branch_cd();
			if ($this->pages->isSalonAdmin() && empty($set_branch_cd) === false ) $branch_cd = $set_branch_cd;
			else $branch_cd = $this->datas->getBracnCdbyCurrentUser($user_login);
			$this->pages->set_current_user_branch_cd($branch_cd);
			$this->pages->set_branch_datas($this->datas->getBranchData($branch_cd));
			if ($this->pages->isSalonAdmin() ) $this->pages->set_all_branch_datas($this->datas->getAllBranchData());


		}
		elseif ($this->action_class == 'Basic_Init' ) {
			$branch_cd = $this->pages->get_target_branch_cd();
			$target_year = $this->pages->get_target_year();
			$this->pages->set_init_datas($this->datas->getAllSpDateData($target_year,$branch_cd));
		}
		elseif ($this->action_class == 'Basic_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->pages->set_table_data($res);

			if ( ($_POST['type'] == 'inserted' ) || ($_POST['type'] == 'deleted' ) ) {
				$this->datas->updateSpDate( $res );
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res );
			}
		}

		$this->pages->show_page();
		if ($this->action_class != 'Basic_Page') wp_die();
	}
}		//class


