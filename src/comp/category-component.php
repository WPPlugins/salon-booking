<?php

class Category_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}



	public function editTableData () {

		if ( $_POST['type'] == 'deleted' ) {
			$set_data['category_cd'] = intval($_POST['category_cd']);
		}
		else {
			if ($_POST['type'] == 'updated' ) 	{
				$set_data['category_cd'] = intval($_POST['category_cd']);
				$set_data['display_sequence'] = intval($_POST['display_sequence']);
			}
			else {
				$set_data['display_sequence'] = $this->datas->getMaxDisplaySequence('salon_category')+1;
			}

			$set_data['category_name'] = stripslashes($_POST['category_name']);
			$set_data['category_patern'] =  intval($_POST['category_patern']);
			$set_data['category_values'] = "";
			if (isset($_POST['category_values'])){
				$set_data['category_values'] =  stripslashes($_POST['category_values']);
			}
			$set_data['target_table_id'] = intval($_POST['target_table_id']);

		}
		return $set_data;

	}



	public function editSeqData() {
		$keys = explode(',',$_POST['category_cd']);
		$values = explode(',',$_POST['value']);
		$set_data = array($keys[0] => $values[1],$keys[1] => $values[0]);
		return $set_data;
	}

}