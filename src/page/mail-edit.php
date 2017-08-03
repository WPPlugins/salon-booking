<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Mail_Edit extends Salon_Page {

	private $table_data = null;
	private $default_mail = '';

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}

	public function check_request() {
		$msg = null;
		Salon_Page::serverCheck(array(),$msg);

		$edit_from = $_POST['config_mail_from'];
		if (strpos($edit_from,"<") !== false ) {
			$edit_from_array = explode('<',$edit_from);
			$edit_from = trim($edit_from_array[1]);
			$edit_from = str_replace('>', '', $edit_from);
		}
		$edit_returnPath = trim($_POST['config_mail_returnPath']);
		//
		$msg = "";
		if ( '' != strval($edit_from)) {
			if ($this->email_check($edit_from) == false) {
				$msg = Salon_Component::getMsg('E216',__('Mail from',SL_DOMAIN));
			}
		}
		if ( '' != strval($edit_returnPath)) {
			if ($this->email_check($edit_returnPath) == false) {
				$msg  .=  (empty($this->msg) ? '' : "\n"). Salon_Component::getMsg('E207',__('Mail return path',SL_DOMAIN));
			}
		}

		if ($msg != "") {
			throw new Exception($msg,__LINE__ );
		}

	}

	public function email_check($email)
	{
		if (function_exists("filter_var") ) {
			return filter_var($email, FILTER_VALIDATE_EMAIL);
		}
		if (preg_match('/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i',$email)) {
				return true;
		}
		return false;
	}

	public function show_page() {
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($this->table_data).' }';
	}


}