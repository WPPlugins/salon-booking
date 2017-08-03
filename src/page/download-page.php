<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Download_Page extends Salon_Page {

	private $download_items = null;



	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->download_items = parent::set_download_item();
	}


	public function set_position_datas ($position_datas) {
		$this->position_datas = $position_datas;
	}


	private function _setSelectArea( ){
		$result = '<p>'.__('Please check download items',SL_DOMAIN).'</p><div id="sl_download_select_area">';

		foreach ($this->download_items as $k1=>$d1 ) {
			if (empty($d1['no_disp']))
				$result .= '<p><input type="checkbox" id="'.$d1['id'].'_down'.'" value="'.$d1['id'].'" '.$d1['check'].' /><label for="'.$d1['id'].'_down" >'.$d1['label'].'</label></p>';
		}
		$result .= '</div>';
		return $result;
	}
	private function _setButtonArea() {
		return '<input type="button" id="button_exec" value="'.__('Exec',SL_DOMAIN).'" onclick="fnExecDownload()" class="sl_button"/><input type="button" id="button_cancel" value="'.__('Cancel',SL_DOMAIN).'" onclick="fnRemoveModalResult(this);" class="sl_button"/>';
	}

	public function show_page() {
		$result = $this->_setSelectArea();
		$result .= $this->_setButtonArea();

		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"cnt":'.count($result).',
				"set_data":'.json_encode($result).' }';
	}	//show_page
}		//class

