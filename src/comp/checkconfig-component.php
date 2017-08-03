<?php

class Checkconfig_Component {

	private $datas = null;
	private $setMessageArray = array();
	private $setPlugins = null;


	public function __construct(&$datas) {
		$this->datas = $datas;
	}

	public function checkCache() {
		$setMessage = __("Cache no use",SL_DOMAIN);
		if (function_exists("apc_cache_info")){
			$info = apc_cache_info();
			if ($info !== false) {
				$setMessage = __("It is better not to use chache.",SL_DOMAIN);
			}
		}
		return $setMessage;

	}

	public function checkSettings() {
 		$setMessage = __("Settings of Plugin are OK",SL_DOMAIN);
		//
		if ($this->check_plugin() == false) {
			$setMessage = implode("\n", $this->setMessageArray);
		}
		return $setMessage;
	}

	public function getPluginsInfo() {
		$result = array();
		foreach ($this->setPlugins as $k1 => $d1){
			$result[$k1] = $d1['Name']." ".$d1['Version']." ".$d1['Description'];
		}
		return $result;
	}

	public function getThemeInfo() {
		$result = array();
		$theme = wp_get_theme();
		$result[] =
		array(
			'Name' => $theme->get('Name')
			,'Version' => $theme->get('Version')
			,'Template'=> $theme->get('Template')
		);
		return $result;
	}

	public function check_plugin() {
		$isOk = true;

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$this->setPlugins = get_plugins();
		$names = wp_list_pluck( $this->setPlugins, 'Name' );


		$fileRestApi = array_search( "WP REST API", $names );
		$installedRestApi = ! empty( $fileRestApi );
		$fileFrontEditor = array_search( "Front-end Editor", $names );
		$installedFrontEditor = ! empty( $fileFrontEditor );

		if (installedRestApi && $installedFrontEditor) {
			if (is_plugin_active($fileRestApi)
			&& is_plugin_active($fileFrontEditor)) {
				$isOk = false;
				$this->setMessageArray[] = __("Salon Booking conflicts with the following plugins.[WP REST API][Front-end Editor]",SL_DOMAIN);
			}
		}

		return $isOk;
	}

}