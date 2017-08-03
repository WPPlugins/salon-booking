<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Checkconfig_Page extends Salon_Page {


	private $datas = null;



	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}

	public function setDatas($key,$data) {
		$this->datas[$key] = $data;
	}

	private function setShowData($datas) {
		if (is_array($datas)) {
			foreach($datas as $k1 => $d1) {
				if (is_numeric($k1)) {
					//テーブル項目のようなキーと値って前提で編集
					$details = "";
					$comma = "";
					foreach($d1 as $k2 => $d2 ) {
						$details .= $k2.":".$d2.$comma;
						$comma=",";
					}
					echo "<li>".$details."</li>";
				}
				else {
					echo "<li>".$k1."</li>";
					echo "<ul>";
					$this->setShowData($d1);
					echo "</ul>";
				}
			}
		}
		else {
			echo "<li>".$datas."</li>";
		}
	}
	public function show_page() {

		wp_enqueue_style('salon', SL_PLUGIN_URL.'/css/salon.css');
?>

		<div id="salon_detail">
		<ol >
<?php
		$this->setShowData($this->datas);
?>
		</ol>
		</div>
<?php
	}	//show_page
}		//class

