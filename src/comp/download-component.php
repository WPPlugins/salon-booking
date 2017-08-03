<?php

class Download_Component {

	private $version = '1.0';

	private $datas = null;
	private $file_name = '';
	private $csv_data = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	public function getDownloadFileName() {
		$this->file_name = sprintf("%s_%d.csv",date_i18n("YmdHis"),mt_rand());
		return $this->file_name;
	}


	public function writeFile($target,$branch_cd,$items,$cols) {
		global $wpdb;

		$where = '';
		if ($branch_cd != 'ALL' ) $where = ' WHERE rs.branch_cd = '.$branch_cd.' ';
		if ($target == 'reservation' ) {
			$sql = ' FROM '.$wpdb->prefix.'salon_reservation rs '.
					' INNER JOIN '.$wpdb->prefix.'salon_staff st'.
					' ON (rs.staff_cd = st.staff_cd OR rs.staff_cd = '.Salon_Default::NO_PREFERENCE.' ) AND  rs.delete_flg <> '.Salon_Reservation_Status::DELETED.
					' INNER JOIN '.$wpdb->prefix.'salon_branch br'.
					' ON rs.branch_cd = br.branch_cd';
			$this->_editDownloadData($items,$cols,$where,$sql,$branch_cd);
		}
		elseif ($target == 'sales' ) {
			$sql = ' FROM '.$wpdb->prefix.'salon_reservation rs '.
				' INNER JOIN '.$wpdb->prefix.'salon_sales sa'.
				' ON rs.reservation_cd = sa.reservation_cd'.
				' INNER JOIN '.$wpdb->prefix.'salon_staff st'.
				' ON rs.staff_cd = st.staff_cd OR rs.staff_cd = '.Salon_Default::NO_PREFERENCE.
				' INNER JOIN '.$wpdb->prefix.'salon_branch br'.
				' ON rs.branch_cd = br.branch_cd'.
				' LEFT JOIN '.$wpdb->prefix.'salon_promotion po'.
				' ON po.set_code = sa.coupon';
			$this->_editDownloadData($items,$cols,$where,$sql,$branch_cd);
		}
		$this->_writeCsvFile();
		return count($this->csv_data);
	}

	private function _editDownloadData($items, $cols ,$where,$add_sql) {

		global $wpdb;

		$col = explode(',',$cols);
		array_unshift($col,'date');
		$select = array();
		$is_need_user_inf = false;
		$is_need_item_inf = false;
		$user_inf_col = array();
		$item_inf_col = array();
		foreach($col as $k1 => $d1 ) {
			$select[] = $items[$d1]['col'].' AS '.$items[$d1]['label'];
			if (!empty($items[$d1]['user']) && $items[$d1]['user']== 'need'  ) {
				$is_need_user_inf = true;
				$user_inf_col[] = $items[$d1]['label'];
			}
			if (!empty($items[$d1]['item']) && $items[$d1]['item']== 'need'  ) {
				$is_need_item_inf = true;
				$item_inf_col[] = $items[$d1]['label'];
			}
		}
		//[TODO]branch_cdは元でwhereの中に設定する前提。権限により全支店をOKにする？
		$sql = 'SELECT rs.branch_cd,br.name as '.__('branch_name',SL_DOMAIN).' ,'.implode(',',$select);
		$sql .= $add_sql;
		$sql .= $where;
		$sql .= ' ORDER BY rs.branch_cd ,'.__('Date',SL_DOMAIN);


		$result = $this->datas->getDownloadData	($sql);
		//ユーザ情報が必要な場合
		if ($is_need_user_inf) {
			$user_datas = $this->datas->getUserAllInf();
//					var_export($user_datas);
//					var_export($result);
//					var_export($user_inf_col);
			//[TODO]配列のキーが和名になるのはどうよ
			//user_loginの値が入っているはず
			foreach ($result as $k1 => $d1 ) {
				foreach ($user_inf_col as $d2 ){
//					$result[$k1][$d2] = $user_datas[$d1[$d2]]['last_name'].' '.$user_datas[$d1[$d2]]['first_name'].'('.$d1[$d2].')';
					if(isset($user_datas[$d1[$d2]])&&isset($user_datas[$d1[$d2]]['last_name']))
						$result[$k1][$d2] = $user_datas[$d1[$d2]]['last_name'].' '.$user_datas[$d1[$d2]]['first_name'];
					else
						$result[$k1][$d2] = $d1[$d2];
				}
			}
		}
		if ($is_need_item_inf) {
			$save_branch_cd = '';

			foreach ($result as $k1 => $d1 ) {
				if ($save_branch_cd != $d1['branch_cd'] ) {
					$item_datas = $this->datas->getTargetItemData($d1['branch_cd'] );
					$item_table = array();
					foreach ($item_datas as $k2 => $d2) {
						$item_table[$d2['item_cd']]  = array('name'=> $d2['name'],'price'=>$d2['price']);
					}
					$save_branch_cd = $d1['branch_cd'];
				}
				foreach ($item_inf_col as $d2 ){
					$tmp_items = explode( ',',$d1[$d2]);
					$res = array();
					foreach ($tmp_items as $d3 ) {
						if (isset($item_table[$d3]) )
							$res[] = $item_table[$d3]['name'];
						else
							$res[] = "";
					}
					$result[$k1][$d2] = implode(',',$res);
					unset($result[$k1]['branch_cd']);
				}
			}
		}

		 $this->csv_data = $result;

	}


	private function _writeCsvFile() {
		//見出し
		if ($this->csv_data) {
			foreach ($this->csv_data[0] as $k1 => $d1 ) {
				$edit_data[] = $k1;
			}
			$separater = "\t";
			if ($this->datas->getConfigData("SALON_CONFIG_DOWNLOAD_SEPARATOR") == Salon_Config::COMMA) {
				$separater = ',';
			}
			$file_data = implode($separater,$edit_data)."\n";
			for ($i=0;$i<count($this->csv_data);$i++) {
				unset($edit_data);
				foreach ($this->csv_data[$i] as $k1 => $d1){
					$edit_data[] = '"'.$d1.'"';
				}
				$file_data .= implode($separater,$edit_data)."\n";
			}
			$this->datas->writeCsvFile(SL_PLUGIN_DIR.'/csv/'.$this->file_name, $file_data);
		}
	}



}