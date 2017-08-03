<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Category_Edit extends Salon_Page {

	private $table_data = null;


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}


	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}

	public function set_category_cd($category_cd) {
		 $this->table_data['category_cd'] = $category_cd;
	}

	public function get_category_cd() {
		return $this->table_data['category_cd'];
	}


	public function check_request() {
		if (empty($_REQUEST['type'])) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}

		if	( ($_POST['type'] != 'inserted' ) && empty($_POST['category_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),2 );
		}
		$msg = null;
		if ($_POST['type'] != 'deleted' ) {
			$checkItem = array('category_name','category_patern');
			if (isset($_POST['category_patern']) && $_POST['category_patern'] != Salon_Category::TEXT) {
				$checkItem[] = 'category_value';
			}
			if (Salon_Page::serverCheck($checkItem,$msg) == false) {
				throw new Exception($msg,3 );
			}
			if (array_key_exists("target_table_id",$_POST) ) {
				if ( '' == strval($_POST['target_table_id'])) {
					throw new Exception(Salon_Component::getMsg('E201',__('Select Target Table',SL_DOMAIN)),2 );
				}
			}
			else {
				throw new Exception( Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),3);
			}
		}
	}

	public function show_page() {

		$this->table_data['no'] = __($_POST['type'],SL_DOMAIN);
		$this->table_data['check'] = '';


		if ( $_POST['type'] != 'deleted' ) {

			$this->table_data['sl_category_name'] = htmlspecialchars($this->table_data['category_name']);
			$this->table_data['category_values'] = htmlspecialchars($this->table_data['category_values']);
			$this->table_data['remark'] = '';
		}


		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($this->table_data).' }';
	}


}