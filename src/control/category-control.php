<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/category-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/category-component.php');

class Category_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;

	private $action_class = '';
	private $permits = null;



	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Category_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Category_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Category_Component($this->datas);
		$this->permits = array('Category_Page','Category_Init','Category_Edit','Category_Col_Edit','Category_Seq_Edit');
	}



	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch,$this->is_use_session);
		$this->pages->set_config_datas($this->datas->getConfigData());
		if ($this->action_class == 'Category_Page' ) {
			$this->pages->set_category_patern_datas($this->datas->getCategoryPatern());
			$this->pages->set_target_table_datas($this->datas->getTagetTable());
		}
		elseif ($this->action_class == 'Category_Init' ) {
			$this->pages->set_init_datas($this->datas->getAllCategoryData());
		}
		elseif ($this->action_class == 'Category_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->pages->set_table_data($res);
			if ($_POST['type'] == 'inserted' ) {
				$this->pages->set_category_cd($this->datas->insertTable( $res ));
			}
			if ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res );
			}
			if ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteTable( $res);
			}
//			$reRead = $this->datas->getAllCategoryData($this->pages->get_category_cd() );
//			$this->pages->set_table_data($reRead[0]);

		}
		elseif ($this->action_class == 'Category_Seq_Edit' ) {
			$this->set_response_type($this->pages->getResponseType());
			$this->pages->check_request();
			$res = $this->comp->editSeqData();
			$this->pages->set_table_data($res);
			$this->datas->updateSeq($res,'category','category_cd');
		}

		$this->pages->show_page();
		if ($this->action_class != 'Category_Page' ) wp_die();
	}
}		//class


// $staffs = new Category_Control();
// $staffs->exec();