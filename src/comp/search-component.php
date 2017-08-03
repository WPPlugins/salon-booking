<?php

class Search_Component {
	
	
	private $version = '1.0';
	private $datas = null;
	private $file_name = '';
	private $csv_data = null;
	
	public function __construct(&$datas) {
		$this->datas = $datas;
	}
	
	public function setSearchCustomerData($keys) {
		$datas = $this->datas->getSearchCustomerData($keys);
		$button = '<input type="button" value="'.__('close',SL_DOMAIN).'" onclick="fnRemoveModalResult(this);" class="sl_button"/>';
		$html = "";
		if (count($datas) == 0 ) {
			return false;
		}
		$html .= '<table><thead><tr><th>'.__('No',SL_DOMAIN).'</th><th>'.__('name',SL_DOMAIN).'</th><th>'.__('tel',SL_DOMAIN).'</th><th>'.__('mobile',SL_DOMAIN).'</th><th>'.__('mail',SL_DOMAIN).'</th></tr></thead><tbody>';
		$cnt = 0;

		global $wpdb;
		foreach ($datas as $k1 => $d1 ) {
			$is_exist = false;
			if (is_multisite() ) {
				if (!isset($d1[$wpdb->prefix.'capabilities']) ) {
					continue;
				}
			}
			if (strstr($d1[$wpdb->prefix.'capabilities'],'subscriber') ) {
				$tr = '';
				if ($this->datas->getConfigData('SALON_CONFIG_NAME_ORDER') == Salon_Config::NAME_ORDER_JAPAN ) {
					if ( $this->_setSearchCustomerDataEditTr($keys['name'],$d1['last_name'].' '.$d1['first_name'],$tr) ) $is_exist = true;
				}
				else {
					if ( $this->_setSearchCustomerDataEditTr($keys['name'],$d1['first_name'].' '.$d1['last_name'],$tr) ) $is_exist = true;
				}
				if ( isset($d1['tel']) && $this->_setSearchCustomerDataEditTr($keys['tel'],$d1['tel'],$tr) ) $is_exist = true;
				if ( isset($d1['mobile']) && $this->_setSearchCustomerDataEditTr($keys['tel'],$d1['mobile'],$tr) ) $is_exist = true;
				if ( $this->_setSearchCustomerDataEditTr($keys['mail'],$d1['mail'],$tr) ) $is_exist = true;
				if ($is_exist) {
					$cnt++;
					$html .= '<tr><td>'.$cnt.'</td>'.$tr.'<input type="hidden" value="'.$d1['user_login'].'" /></tr>';
				}
			}
		}
		$html .= '</tbody></table>';
		if ($cnt == 0) {
			return false;
		}
		else  {
			$html = $button.$html.$button;
		}
		return $html;
	}
	
	private function _setSearchCustomerDataEditTr($key,$data,&$tr) {
				
		if ($data && $key && strpos($data,$key) !== false ) {
			$tr .='<td class="sl_search_display">'.htmlspecialchars($data,ENT_QUOTES).'</td>';
			return true;
		}
		else {
			$tr .='<td >'.htmlspecialchars($data,ENT_QUOTES).'</td>';
			return false;
		}
	}
	
}