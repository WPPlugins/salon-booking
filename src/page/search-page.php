<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Search_Page extends Salon_Page {

	private $search_items = null;
	private $result = null;
	private $isNodata = false;
	private $role = null;


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->search_items = array('mail'=>$_POST['mail'],'name'=>$_POST['name'],'tel'=>$_POST['tel']);
	}
	
	public function get_search_items () {
		return $this->search_items;
	}

	public function set_result($result) {
		$this->result = $result;
	}
	
	public function setNodata () {
		$this->isNodata = true;
	}

	public function set_role($role) {
		$this->role = $role;
	}


	public function check_request() {
		//ここは、弱い権限でログインしているスタッフがrequestを偽造して全部取得する場合などを想定
		if (in_array('edit_booking',$this->role) || $this->isSalonAdmin() ) {
		}
		else {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
	}
	
	
	public function show_page() {
		if ($this->isNodata ) {
			echo '{	"status":"Error","message":"'.Salon_Component::getMsg('E906').'",
					"set_data":'.json_encode($this->result).' }';
		}
		else {
			echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
					"cnt":1,
					"set_data":'.json_encode($this->result).' }';
		}
	}	//show_page
}		//class

