<?php

class Stafflist_Component {

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	public function getTargetStaffData($branch_cd) {
		$result = $this->datas->getTargetStaffData($branch_cd);
		$url = site_url();
		$url = substr($url,strpos($url,':')+1);
		foreach ($result as $k1 => $d1 ) {
			//[PHOTO]
			$photo_result = $this->datas->getPhotoData($d1['photo']);
			$tmp = array();
			for($i = 0 ;$i<count($photo_result);$i++) {
				if (is_ssl() ) {
					$photo_result[$i]['photo_path'] =
						preg_replace("$([hH][tT][tT][pP]:".$url.")$"
							,"https:".$url,$photo_result[$i]['photo_path']);
					$photo_result[$i]['photo_resize_path'] =
						preg_replace("$([hH][tT][tT][pP]:".$url.")$"
							,"https:".$url,$photo_result[$i]['photo_resize_path']);
				}
				else {
					$photo_result[$i]['photo_path'] =
						preg_replace("$([hH][tT][tT][pP][sS]:".$url.")$"
								,"http:".$url,$photo_result[$i]['photo_path']);
					$photo_result[$i]['photo_resize_path'] =
						preg_replace("$([hH][tT][tT][pP][sS]:".$url.")$"
								,"http:".$url,$photo_result[$i]['photo_resize_path']);
				}
				$tmp[] = $photo_result[$i];
			}
			$result[$k1]['photo_result'] = $tmp;
			//[PHOTO]
		}
		return $result;
	}

}