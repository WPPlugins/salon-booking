<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Staff_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_staff';	
	
	private $isCompleteDelete = false;
	
	function __construct() {
		parent::__construct();
	}
	

	public function insertTable ($table_data){
		$staff_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%d,%d,%s,%s,%s,%s,%s,%s,%s,%s,%s');
		if ($staff_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $staff_cd;
	}

	public function updateTable ($table_data){

		$set_string = 	' branch_cd = %d , '.
						' position_cd = %d , '.
						' remark =  %s , '.
						' memo =  %s , '.
						' duplicate_cnt =  %s , '.
						' photo =  %s , '.
						' user_login =  %s , '.
						' employed_day =  %s , '.
						' leaved_day =  %s , '.
						' in_items = %s , '.
						' update_time = %s ';
												
		$set_data_temp = array(
						$table_data['branch_cd'],
						$table_data['position_cd'],
						$table_data['remark'],
						$table_data['memo'],
						$table_data['duplicate_cnt'],
						$table_data['photo'],
						$table_data['user_login'],
						$table_data['employed_day'],
						$table_data['leaved_day'],
						$table_data['in_items'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['staff_cd']);
		$where_string = ' staff_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	
	public function updateColumn(&$table_data){
		//[2014/06/22]店舗を変更した場合は、メニュー内容を該当店舗のデフォルトに変更する
		$set_string = "";
		$set_data_temp = array();
		if ($_POST['column'] == 4 ) {
			$set_string  =  'in_items = %s ,';
			$set_data_temp[] = parent::getItemCdByBranch($table_data['value']);
			$table_data['in_items'] = $set_data_temp[0];
		}


		
		$set_string .= 	$table_data['column_name'].' , '.
								' update_time = %s ';
														
		array_push($set_data_temp,$table_data['value'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['staff_cd']);
		$where_string = ' staff_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		global $wpdb;
		//役職を変更した場合は、wp_capbilitiesも変更する
		if ($table_data['is_need_update_role'] ) {
			$role = $this->_getRoleByPosition($table_data['value']);
			update_user_meta( $table_data['ID'], $wpdb->prefix.'capabilities',array($role=>"1") );
		}

		
		
		
		
	}

	public function deleteTable ($table_data){
		//予約があれば更新なければDELETE。単純な間違いは削除しないと、実績登録で同じ名前のスタッフが羅列されてしまう
		$where_string = ' staff_cd = %d ';
		if ($this->_hasReservationByStaffcd($table_data['staff_cd']) ){
			$set_string = 	' delete_flg = %d, update_time = %s  ';
			$set_data_temp = array(Salon_Reservation_Status::DELETED,
							date_i18n('Y-m-d H:i:s'),
							$table_data['staff_cd']);
			if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
				$this->_dbAccessAbnormalEnd();
			}
		}
		else {
			$this->isCompleteDelete = true;
			$set_data_temp = array($table_data['staff_cd']);
			if ( $this->deleteSql(self::TABLE_NAME,$where_string,$set_data_temp) === false) {
				$this->_dbAccessAbnormalEnd();
			}
		}
		return true;
	}
	
	public function isCompleteDelete () { return $this->isCompleteDelete; }


	public function getInitDatas() {
		return $this->getStaffDataByStaffcd();
	}
	
	
	
	public function getStaffDataByStaffcd($staff_cd = "") {
		global $wpdb;
		$join = '';
		$where ='';
		if (!empty($staff_cd)) { 
			$where = $wpdb->prepare(' WHERE st.staff_cd = %d ',$staff_cd);
		}
		else {
			$join = ' AND st.delete_flg <> '.Salon_Reservation_Status::DELETED;
		}
		

		$sql = 'SELECT us.ID,us.user_login,um.* ,us.user_email,'.
				'        st.staff_cd,st.branch_cd,st.position_cd,st.remark,st.memo,st.notes,st.photo, st.duplicate_cnt, '.
				'        DATE_FORMAT(st.employed_day, "'.__("%m/%d/%Y",SL_DOMAIN).'")  as employed_day,'.
				'        DATE_FORMAT(st.leaved_day, "'.__("%m/%d/%Y",SL_DOMAIN).'")  as leaved_day ,display_sequence,'.
				'        in_items '.
				' FROM '.$wpdb->users.' us  '.
				' INNER JOIN '.$wpdb->usermeta.' um  '.
				'       ON    us.ID = um.user_id '.
				' LEFT  JOIN '.$wpdb->prefix.'salon_staff st  '.
				'       ON    us.user_login = st.user_login '.
				$join.
				$where.
				' ORDER BY st.branch_cd,display_sequence,ID';

		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if ($result){
			foreach ($result as $k1 => $d1 ) {
				if (str_replace('/','',$d1['employed_day']) == '00000000' ) $result[$k1]['employed_day'] = '';
				if (substr($d1['leaved_day'],0,4) == '2099' ) $result[$k1]['leaved_day'] = '';


			}
		}
		return $result;

	}


	public function getStaffDataByUser($user_login) {
		global $wpdb;

		$sql = 'SELECT us.ID,us.user_login,um.* ,us.user_email,'.
				'        "" AS staff_cd,"" AS branch_cd,"" AS position_cd,"" AS remark,"" AS memo,"" AS notes,"" AS photo, 0 AS duplicate_cnt, '.
				'        "" AS employed_day,"" AS  leaved_day ,0 AS display_sequence,"" AS in_items '.
				' FROM (SELECT * FROM '.$wpdb->users.' us WHERE user_login = %s ) us '.
				' INNER JOIN '.$wpdb->usermeta.' um  '.
				'       ON    us.ID = um.user_id ';
		$sql = $wpdb->prepare($sql,$user_login);


		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result;

	}

	private function _hasReservationByStaffcd($staff_cd) {
		global $wpdb;
		$where ='';
		if (!empty($staff_cd)) { 
			$where = $wpdb->prepare(' WHERE st.staff_cd = %d ',$staff_cd);
		}
		else {
			$join = ' AND st.delete_flg <> '.Salon_Reservation_Status::DELETED;
		}

		$sql = $wpdb->prepare('SELECT COUNT(*) as cnt  '.
				' FROM '.$wpdb->prefix.'salon_reservation '.
				' WHERE delete_flg <> '.Salon_Reservation_Status::DELETED.
				' AND   staff_cd = %d ',$staff_cd);
				
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if ($result[0]['cnt'] > 0 ) return true;
		return false;
	}


	public function updateStaffPhotoData($staff_cd,$new_photo_ids) {
		global $wpdb;
		$sql = 'SELECT photo '.
				' FROM '.$wpdb->prefix.'salon_staff  '.
				' WHERE staff_cd = %d ';
		
		$result = $wpdb->get_results($wpdb->prepare($sql,$staff_cd),ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		$this->fixedPhoto("updated",$new_photo_ids,$result[0]['photo']);
		
	}

	public function deleteStaffPhotoData($staff_cd) {
		global $wpdb;
		$sql = 'SELECT photo '.
				' FROM '.$wpdb->prefix.'salon_staff  '.
				' WHERE staff_cd = %d ';
		
		$result = $wpdb->get_results($wpdb->prepare($sql,$staff_cd),ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		$this->deletePhotoDatas($result[0]['photo']);
		
	}

	
}