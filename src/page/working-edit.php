<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Working_Edit extends Salon_Page {


	private $branch_datas = null;
	private $table_data = null;
	private $reservation_cd = '';


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}


	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}

	public function get_table_data() {
		return $this->table_data;
	}


	public function set_reservation_cd($reservation_cd) {
		$this->reservation_cd = $reservation_cd;
		$this->table_data['reservation_cd'] = $reservation_cd;
	}


	public function check_request() {
		$this->_parse_data();

		$msg = null;
		$check_item = array('time_from','time_to','working_cds');
		if (parent::serverCheck($check_item,$msg) == false) {
			throw new Exception($msg,1 );
		}
	}

	private function _parse_data() {
		$_POST['type'] = $_POST['!nativeeditor_status'];


	}

	public function show_page() {
		$edit_flg = Salon_Edit::OK;
		$type = htmlspecialchars($_POST['type']);
		$ID = floatval($_POST['id']);

		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8" ?>';
		echo <<<EOT
		<data>
			<action type="{$type}"
					sid="{$ID}"
					tid="{$ID}"
					key_in_time = "{$this->table_data['in_time']}"
					edit_flg="{$edit_flg}" >
			</action>
		</data>
EOT;
	}


}