<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/item-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/item-component.php');

class Item_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Item_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Item_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Item_Component($this->datas);
		$this->permits = array('Item_Page','Item_Init','Item_Edit','Item_Col_Edit','Item_Seq_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());

		//現店舗コード
		$user_login = $this->datas->getUserLogin();
		$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login));
		$branch_cd = $this->datas->getBracnCdbyCurrentUser($user_login);
		$this->pages->set_user_branch_cd($branch_cd);


		if ($this->action_class == 'Item_Page' ) {
			$this->pages->set_check_staff_data($this->datas->getTargetStaffData());
			$this->pages->set_branch_datas($this->datas->getAllBranchData());
		}
		elseif ($this->action_class == 'Item_Init' ) {
			$this->pages->set_init_datas($this->comp->editInitData($this->datas->getInitDatas(),$user_login,$branch_cd));
		}
		elseif ($this->action_class == 'Item_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->comp->serverCheck($res);
			$this->pages->set_table_data($res);
			if ($_POST['type'] == 'inserted' ) {
				$this->pages->set_item_cd($this->datas->insertTable( $res ));
				$this->pages->set_branch_name($this->pages->get_branch_cd());
			}
			if ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res );
				$this->pages->set_branch_name($this->pages->get_branch_cd());
			}
			if ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteTable( $res);
			}
			$reRead = $this->datas->getAllItemData("WHERE it.item_cd = ".$this->pages->get_item_cd() );
			$this->pages->set_table_data($reRead[0]);

		}
		elseif ($this->action_class == 'Item_Col_Edit' ) {
			$this->set_response_type($this->pages->getResponseType());
			$this->pages->check_request();
			$res = $this->comp->editColumnData();
			$this->comp->serverCheckCol($res);
			$this->pages->set_table_data($res);
			$this->datas->updateColumn($res);
		}
		elseif ($this->action_class == 'Item_Seq_Edit' ) {
			$this->set_response_type($this->pages->getResponseType());
			$this->pages->check_request();
			$res = $this->comp->editSeqData();
			$this->pages->set_table_data($res);
			$this->datas->updateSeq($res,'item','item_cd');
		}

		$this->pages->show_page();
		if ($this->action_class != 'Item_Page' ) wp_die();
	}
}		//class

