<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/promotion-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/promotion-component.php');

class Promotion_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;


	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Promotion_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Promotion_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Promotion_Component($this->datas);
		$this->permits = array('Promotion_Page','Promotion_Init','Promotion_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());


		if ($this->action_class == 'Promotion_Page' ) {

			$user_login = $this->datas->getUserLogin();
			$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login));
			$set_branch_cd = $this->pages->get_set_branch_cd();
			if ($this->pages->isSalonAdmin() && empty($set_branch_cd) === false ) $branch_cd = $set_branch_cd;
			else $branch_cd = $this->datas->getBracnCdbyCurrentUser($user_login);

			if ($this->pages->isSalonAdmin() ) $this->pages->set_all_branch_datas($this->datas->getAllBranchData());

			$this->pages->set_current_user_branch_cd($branch_cd);
			$this->pages->set_branch_datas($this->datas->getBranchData($branch_cd));



			$this->pages->set_usable_patern_datas($this->datas->getUsablePaternDatas());
			$this->pages->set_customer_rank_datas($this->datas->getCustomerRank());

		}
		elseif ($this->action_class == 'Promotion_Init' ) {
			$branch_cd = $this->pages->get_target_branch_cd();
			$this->pages->set_init_datas($this->datas->getPromotionData($branch_cd,null,null,true));
		}
		elseif ($this->action_class == 'Promotion_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();

			if ($_POST['type'] == 'inserted' ) {
				$this->comp->serverCheck($res);
				$promotion_cd = $this->datas->insertTable( $res);
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->comp->serverCheck($res);
				$this->datas->updateTable( $res);
				$promotion_cd = $res['promotion_cd'];
			}
			elseif ($_POST['type'] == 'deleted' ) {
				$this->comp->deleteCheck($res['promotion_cd']);
				$this->datas->deleteTable( $res);
			}
			if ($_POST['type'] != 'deleted' ) {
				$table_data = $this->datas->getPromotionData(null,$promotion_cd);
				$this->pages->set_table_data($table_data[0]);
			}
		}

		$this->pages->show_page();
		if ($this->action_class != 'Promotion_Page' ) wp_die();

	}
}		//class

