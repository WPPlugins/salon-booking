<?php

class Staff_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	private function _is_TargetUser($role,$target_role) {
		if ( array_key_exists('subscriber',$role) ) return false;
		switch($target_role) {
			case 'administrator':
				break;
			case 'editor':
				if (array_key_exists('administrator',$role) ) return false;
				break;
			case 'author':
				if (array_key_exists('administrator',$role) ) return false;
				if (array_key_exists('editor',$role) ) return false;
				break;
			case 'contributor':
				if (array_key_exists('administrator',$role) ) return false;
				if (array_key_exists('editor',$role) ) return false;
				if (array_key_exists('author',$role) ) return false;
				break;
			default:
				return false;
		}
		return true;
	}

	public function editInitData($result, $branch_cd) {

		$edit = array();
		$index = 0;
		$save_key = $result[0]['ID'];
		//ログインID単位の配列に変換する。
		for ($i =0; $i < count($result) ; $i++ ) {
			if ($save_key <> $result[$i]['ID'] ) {
				$index++;
			}
			$edit[$index][$result[$i]['meta_key']] = $result[$i]['meta_value'];
			//[TODO]ちと冗長？
			$edit[$index]['ID'] = $result[$i]['ID'];
			$edit[$index]['staff_cd'] = $result[$i]['staff_cd'];
			$edit[$index]['user_login'] = $result[$i]['user_login'];
			$edit[$index]['mail'] = $result[$i]['user_email'];
			$edit[$index]['branch_cd'] = $result[$i]['branch_cd'];
			$edit[$index]['position_cd'] = $result[$i]['position_cd'];
			$edit[$index]['remark'] = $result[$i]['remark'];
			$edit[$index]['memo'] = $result[$i]['memo'];
			$edit[$index]['notes'] = $result[$i]['notes'];
			$edit[$index]['photo'] = $result[$i]['photo'];
			$edit[$index]['duplicate_cnt'] = $result[$i]['duplicate_cnt'];
			$edit[$index]['employed_day'] = $result[$i]['employed_day'];
			$edit[$index]['leaved_day'] = $result[$i]['leaved_day'];
			$edit[$index]['display_sequence'] = $result[$i]['display_sequence'];
			$edit[$index]['in_items'] = $result[$i]['in_items'];

			$save_key = $result[$i]['ID'];
		}
		//不要な項目が多いので編集する
		$result_after = array();
		$index = 0;
		$current_user = wp_get_current_user();
		global $wpdb;
		foreach ( $edit as $k1 => $d1 ) {
			if (is_multisite() ) {
				if (!isset($d1[$wpdb->prefix.'capabilities']) ) {
					continue;
				}
			}
			$role = unserialize($d1[$wpdb->prefix.'capabilities']) ;
			//顧客は「購読者」のみで、スタッフは「購読者」以外
//			if ( ! array_key_exists('subscriber',$role) ){
			//後からマルチサイトでネットワークアドミンの場合を追加

			$check_role = false;
			if ( (is_multisite() && is_super_admin() ) ) {
				$check_role = $this->_is_TargetUser($role,'administrator');
			}
			else {
				$check_role = $this->_is_TargetUser($role,$current_user->roles[0]);
			}

			if ($check_role) {
				//管理者以外は他店舗をみられない
				if ($this->datas->isSalonAdmin()
						||	$branch_cd == $d1['branch_cd']) {
					$result_after[$index]['ID'] = $d1['ID'];
					$result_after[$index]['staff_cd'] = $d1['staff_cd'];
					$result_after[$index]['user_login'] = $d1['user_login'];
					$result_after[$index]['mail'] = $d1['mail'];
					$result_after[$index]['branch_cd'] = $d1['branch_cd'];
					$result_after[$index]['remark'] = $d1['remark'];
					$result_after[$index]['memo'] = $d1['memo'];
					$result_after[$index]['notes'] = $d1['notes'];
					$result_after[$index]['first_name'] = $d1['first_name'];
					$result_after[$index]['last_name'] = $d1['last_name'];
					$result_after[$index]['zip'] = @$d1['zip'];
					$result_after[$index]['address'] = @$d1['address'];
					$result_after[$index]['tel'] = @$d1['tel'];
					$result_after[$index]['mobile'] = @$d1['mobile'];
					$result_after[$index]['position_cd'] = $d1['position_cd'];
					$result_after[$index]['photo'] = $d1['photo'];
					$result_after[$index]['duplicate_cnt'] = $d1['duplicate_cnt'];
					$result_after[$index]['employed_day'] = $d1['employed_day'];
					$result_after[$index]['leaved_day'] = $d1['leaved_day'];
					$result_after[$index]['display_sequence'] = $d1['display_sequence'];
					$result_after[$index]['in_items'] = $d1['in_items'];

					$index++;
				}

			}
		}
		//地位
		$position_datas = $this->datas->getAllPositionData();
		$position_datas_after = array();
		foreach ($position_datas as $k1 => $d1 ) {
			$position_datas_after[$d1['position_cd']]= $d1['name'];
		}
		//氏名を編集したり、もろもろ
		foreach ( $result_after as $k1 => $d1) {
			if (empty($d1['first_name'] ) ) $result_after[$k1]['first_name'] = __('first name',SL_DOMAIN).__('not registered',SL_DOMAIN);
			if (empty($d1['last_name'] ) ) $result_after[$k1]['last_name'] = __('last name',SL_DOMAIN).__('not registered',SL_DOMAIN);
			if (empty($d1['address'] ) ) $result_after[$k1]['address'] = __('address',SL_DOMAIN).__('not registered',SL_DOMAIN);
			if (SALON_DEMO) $result_after[$k1]['user_login'] = 'SALON DEMO';
			if (empty($d1['branch_cd'] ) ) {
				$result_after[$k1]['branch_name'] = __('not registered',SL_DOMAIN);
			}
			else {
				$datas = $this->datas->getBranchData($d1['branch_cd']);
				$result_after[$k1]['branch_name'] = $datas['name'];
			}
//			$tmp = str_replace("\'","'",$d1['photo']);
//			if (!empty($_SERVER['HTTPS']) ) {
//				$url = site_url();
//				$url = substr($url,strpos($url,':')+1);
//				$tmp = preg_replace("$([hH][tT][tT][pP]:".$url.")$","https:".$url,$tmp);
//			}
//			$result_after[$k1]['photo'] = $tmp;

			//[PHOTO]
			$photo_result = $this->datas->getPhotoData($d1['photo']);
			$tmp = array();
			for($i = 0 ;$i<count($photo_result);$i++) {
				$tmp[] = $photo_result[$i];
			}
			$result_after[$k1]['photo_result'] = $tmp;
			//[PHOTO]


			$result_after[$k1]['position_name'] = '';
			if ($d1['position_cd']) $result_after[$k1]['position_name'] = $position_datas_after[ $d1['position_cd']];
		}
		return $result_after;

	}


	public function editUserData() {
		$set_data['ID'] = $_POST['ID'];
		$set_data['user_login'] = $_POST['user_login'];
		$set_data['mail'] = $_POST['mail'];
		$set_data['zip'] = $_POST['zip'];
		$set_data['address'] = stripslashes($_POST['address']);
		$set_data['tel'] = $_POST['tel'];
		$set_data['mobile'] = $_POST['mobile'];
		$set_data['first_name'] = stripslashes($_POST['first_name']);
		$set_data['last_name'] = stripslashes($_POST['last_name']);
		$set_data['position_cd'] = $_POST['position_cd'];
		return $set_data;
	}

	public function editTableData () {

		if ( $_POST['type'] == 'deleted' ) {
			$set_data['staff_cd'] = intval($_POST['staff_cd']);
			$set_data['user_login'] = $_POST['user_login'];
		}
		else {
			if ($_POST['type'] == 'updated' ) 	{
				$set_data['staff_cd'] = intval($_POST['staff_cd']);
				$set_data['display_sequence'] = intval($_POST['display_sequence']);

				if ($set_data['staff_cd'] ==  get_option('salon_initial_user',1)) {

					$_POST['position_cd'] = Salon_Position::MAINTENANCE;
				}


			}
			else {
				$set_data['display_sequence'] = $this->datas->getMaxDisplaySequence('salon_staff')+1;
			}
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
			$set_data['position_cd'] = intval($_POST['position_cd']);
			$set_data['remark'] = stripslashes($_POST['remark']);
			$set_data['memo'] = stripslashes($_POST['memo']);
			$set_data['notes'] = '';
			$set_data['duplicate_cnt'] = intval($_POST['duplicate_cnt']);
			//
//			$tmp = stripslashes($_POST['photo']);
//			if ( strpos($tmp, 'class=\'lightbox\'') === false)	{
//				$set_data['photo'] = preg_replace('/^<a(.*?)>(.*)$/','<a ${1} class=\'lightbox\' >${2}',$tmp);
//			}
//			else {
//				$set_data['photo'] = $tmp;
//			}

			$set_data['photo'] = str_replace("photo_id_","",stripslashes($_POST['photo']));
			//以下はstaffでは不要。選択した状態で追加はできないようにしている
			if ($_POST['type'] == 'inserted' && !empty($_POST['used_photo']) ) {
				$new_photo_id_array = $this->_copyPhotoData($_POST['used_photo']);
				$edit_tmp_array = explode(',',$set_data['photo']);
				for($i = 0 ; $i < count($edit_tmp_array) ; $i++) {
					if (array_key_exists($edit_tmp_array[$i],$new_photo_id_array) ) {

						$edit_tmp_array[$i] = $new_photo_id_array[$edit_tmp_array[$i]];
					}
				}
				$set_data['photo'] = implode(',',$edit_tmp_array);
			}



			$set_data['user_login'] = $_POST['user_login'];
			$set_data['employed_day'] = '';
			if (!empty($_POST['employed_day'])) $set_data['employed_day'] = Salon_Component::editRequestYmdForDb($_POST['employed_day']);
			$set_data['leaved_day'] = '';
			if (empty($_POST['leaved_day'])) $set_data['leaved_day'] = '2099-12-28 00:03:00';
			else $set_data['leaved_day'] = Salon_Component::editRequestYmdForDb($_POST['leaved_day']);
			//[2014/06/22]
			$set_data['in_items'] = stripslashes($_POST['item_cds']);
		}
		return $set_data;

	}

	public function copyPhotoData($ids,$target_width=100,$target_height=100) {
		return $this->_copyPhotoData($ids,$target_width,$target_height);
	}

	private function _copyPhotoData($ids,$target_width=100,$target_height=100) {

		$new_photo_id_array = array();

		$vals = explode(',',$ids);
		foreach ($vals as  $d1 ) {
			$photo_datas = explode(':',$d1);
			$photo_id =  $photo_datas[0];
			$base_name = $photo_datas[1];
			$attr = substr($base_name, strrpos($base_name, '.') );
			$randam_file_name = substr(md5(uniqid(mt_rand())),0,8).$attr;
			if (!copy(SALON_UPLOAD_DIR.$base_name,SALON_UPLOAD_DIR.$randam_file_name) ) {
				throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__." ".__('PHOTO IMAGE CAN\'T COPY',SL_DOMAIN)));
			}
			if (!copy(SALON_UPLOAD_DIR. $target_width."_".$target_height."_".$base_name,SALON_UPLOAD_DIR. $target_width."_".$target_height."_".$randam_file_name) ) {
				throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__." ".__('PHOTO IMAGE CAN\'T COPY',SL_DOMAIN)));
			}

			$new_photo_id_array[$photo_id] =	$this->datas->insertPhotoData($photo_id,$randam_file_name);
		}
		return $new_photo_id_array;

	}

	public function editColunDataForWpUser() {

		switch (intval($_POST['column'])) {
			case 2:
				if ($this->datas->getConfigData('SALON_CONFIG_NAME_ORDER') == Salon_Config::NAME_ORDER_JAPAN )	$meta = 'last_name';
				else $meta = 'first_name';
				break;
			case 3:
				if ($this->datas->getConfigData('SALON_CONFIG_NAME_ORDER') == Salon_Config::NAME_ORDER_JAPAN )	$meta = 'first_name';
				else $meta = 'last_name';
				break;
		}
		return array('ID'=>intval($_POST['ID']),'meta'=>$meta,'value'=>stripslashes($_POST['value']));

	}

	public function editColumnData() {
		$column = array();
		$column[4]="branch_cd = %d ";
		$column[5]="position_cd = %d ";
		$column[7]="remark = %s ";


		$set_data['column_name'] = $column[intval($_POST['column'])];
		$set_data['value'] = stripslashes($_POST['value']);
		$set_data['staff_cd'] = intval($_POST['staff_cd']);
		$set_data['is_need_update_role'] = false;
		if ($_POST['column'] == 5 ) {
			if ($set_data['staff_cd'] ==  get_option('salon_initial_user',1)) {
				$set_data['value'] = Salon_Position::MAINTENANCE;
			}
			else {
				$set_data['is_need_update_role']=true;
			}
		}
		$set_data['ID'] = intval($_POST['ID']);

		return $set_data;
	}

	public function editSeqData() {
		$keys = explode(',',$_POST['staff_cd']);
		$values = explode(',',$_POST['value']);
		$set_data = array($keys[0] => $values[1],$keys[1] => $values[0]);
		return $set_data;
	}

	public function editStaffData($result, $user_login, $branch_cd) {
		if ($this->datas->isSalonAdmin($user_login) ) {
			return $result;
		}
		$result_after = array();
		//$k1に支店単位で値がまとまっている
		foreach ( $result as $k1 => $d1 ) {
			if ($branch_cd == $k1) {
				$result_after[$k1] = $d1;
			}
		}
		return $result_after;

		return $result;
	}
}