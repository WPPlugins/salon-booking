<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Photo_Edit extends Salon_Page {


	private $photo_id = null;
	private $resize_file_path = null;



	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);

	}

	public function check_request() {
		if (empty($_REQUEST['type'])) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),1 );
		}
		//nonceのチェックのみ
		if (Salon_Page::serverCheck(array(),$msg) == false) {
			throw new Exception($msg );
		}
		if	( ($_REQUEST['type'] == 'deleted' ) && empty($_POST['photo_id']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),2 );
		}
		$msg = null;
		if ($_REQUEST['type'] == 'inserted' ) {
			//ファイル名などのチェック。不正に対するチェックなので$_FILESはみないで直接ファイルをチェックする
			$attr = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') + 1));
			if ($attr == 'jpg' || $attr == 'png' ||$attr == 'gif'){}
			else {
				throw new Exception(Salon_Component::getMsg('E911',__('FILE TYPE ERROR',SL_DOMAIN))."(1)",3);
			}
			try {
				$size = getimagesize( $_FILES['file']['tmp_name']);
			} catch (Exception $e) {
				throw new Exception(Salon_Component::getMsg('E911',__('FILE TYPE ERROR',SL_DOMAIN))."(2)",4);
			}
			if ($size[2] == IMAGETYPE_JPEG || $size[2] == IMAGETYPE_PNG || $size[2] != IMAGETYPE_GIF) {}
			else {
				throw new Exception(Salon_Component::getMsg('E911',__('FILE TYPE ERROR',SL_DOMAIN))."(2)",5);
			}
			if (filesize( $_FILES['file']['tmp_name']) > SALON_MAX_FILE_SIZE * 1000 * 1000) {
				throw new Exception(Salon_Component::getMsg('E911',__('FILE MAX SIZE ERROR(10M)',SL_DOMAIN)),6);
			}
		}
	}

	public function set_photo_id($photo_id) {
		$this->photo_id = $photo_id;
	}
	public function set_resize_file_path($resize_file_path) {
		$this->resize_file_path = $resize_file_path;

	}

	public function show_page() {
		if ($_REQUEST['type'] == 'deleted')
			echo '{	"status":"Ok" }';
		else
			echo '{	"status":"Ok","photo_id":"'.$this->photo_id.'","resize_path":"'.$this->resize_file_path.'"}';
	}	//show_page
}		//class

