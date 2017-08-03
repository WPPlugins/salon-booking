<?php
/*
Plugin Name: Salon Booking
Plugin URI: http://salon.mallory.jp
Description: Salon Booking enables the reservation to one-on-one business between a client and a staff member.
Version: 1.7.26
Author: tanaka-hisao
Author URI: http://salon.mallory.jp
Text Domain: salon-booking
Domain Path: /languages/
*/
define( 'SL_VERSION', '1.7.26' );
define( 'SL_DOMAIN', 'salon-booking' );
define( 'SL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SL_PLUGIN_NAME', trim( dirname( SL_PLUGIN_BASENAME ), '/' ) );
define( 'SL_PLUGIN_DIR', plugin_dir_path(__FILE__)  );
define( 'SL_PLUGIN_SRC_DIR', SL_PLUGIN_DIR . 'src'.'/' );
define( 'SL_PLUGIN_URL',  plugin_dir_url(__FILE__) );
define( 'SL_PLUGIN_SRC_URL', SL_PLUGIN_URL  .'/' . 'src' );
define( 'SL_LOG_DIR', '..'.'/'.'..'.'/' );

define('SALON_JS_DIR', '/'.'booking'.'/');
define('SALON_CSS_DIR', '/'.'booking'.'/');
define('SALON_PHP_DIR', '/'.'booking'.'/');

define( 'SALON_DEMO', false);

if ( ! defined( 'SALON_FOR_THEME' ) ) {
	define( 'SALON_FOR_THEME', false);
}
// if ( ! defined( 'SALON_FOR_REFACTOR' ) ) {
// 	define( 'SALON_FOR_REFACTOR', true );
// }
define( 'SALON_UPLOAD_DIR_OLD', SL_PLUGIN_DIR . 'uploads');
define( 'SALON_UPLOAD_URL_OLD', SL_PLUGIN_URL .'/'. 'uploads');

define( 'SALON_MAX_FILE_SIZE', 10 );	//１０メガまでUPLOAD
define( 'SALON_UPLOAD_DIR_NAME','/'.'salon'.'/');

$uploads = wp_upload_dir();

define( 'SALON_UPLOAD_DIR', $uploads['basedir'].SALON_UPLOAD_DIR_NAME);
define( 'SALON_UPLOAD_URL', $uploads['baseurl'].SALON_UPLOAD_DIR_NAME);
define( 'SALON_COLORBOX_SIZE', '80%');

require_once(SL_PLUGIN_SRC_DIR . 'comp/salon-component.php');


$salon_booking = new Salon_Booking();

class Salon_Booking {

	private $config_branch;

	private $maintenance = '';
	private $management = '';

	private $user_role = '';
	private $post_id = '';


	public function __construct() {
		add_action('init', array( &$this, 'init_session_start'));

		$this->maintenance = SL_DOMAIN.'-maintenace';
		$this->management = SL_DOMAIN.'-management';

		$this->config_branch = Salon_Config::MULTI_BRANCH;
		register_activation_hook(__FILE__, array( &$this, 'salon_install'));
		load_plugin_textdomain( SL_DOMAIN, false, SL_PLUGIN_NAME.'/languages' );

		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_javascript' ) );
		add_filter( 'get_pages', array( &$this, 'get_pages' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'front_javascript' ) );
		add_shortcode('salon-booking', array( &$this, 'salon_booking_shortcode'));
		add_shortcode('salon-confirm', array( &$this,'salon_booking_confirm'));
		add_shortcode('salon-check-config', array( &$this, 'salon_booking_check_config'));
		add_shortcode('salon-staffs', array( &$this, 'salon_booking_staff'));
		add_shortcode('salon-menus', array( &$this, 'salon_booking_menu'));
		add_action('salon_daily_event', array( &$this, 'daily_action'));
		register_deactivation_hook(__FILE__, array( &$this, 'salon_deactivation'));

		add_filter('user_contactmethods',array( &$this,'update_profile_fields'),10,1);
		add_action('wp_ajax_slstaff', array( &$this,'edit_staff'));
		add_action('wp_ajax_slbasic', array( &$this,'edit_base'));
		add_action('wp_ajax_slbranch', array( &$this,'edit_branch'));
		add_action('wp_ajax_slbooking', array( &$this,'edit_booking'));
		add_action('wp_ajax_slconfig', array( &$this,'edit_config'));
		add_action('wp_ajax_slconfirm', array( &$this,'edit_confirm'));
		add_action('wp_ajax_slcustomer', array( &$this,'edit_customer'));
		add_action('wp_ajax_sldownload', array( &$this,'edit_download'));
		add_action('wp_ajax_slitem', array( &$this,'edit_item'));
		add_action('wp_ajax_slposition', array( &$this,'edit_position'));
		add_action('wp_ajax_slreservation', array( &$this,'edit_reservation'));
		add_action('wp_ajax_slsales', array( &$this,'edit_sales'));
		add_action('wp_ajax_slsearch', array( &$this,'edit_search'));
		add_action('wp_ajax_slworking', array( &$this,'edit_working'));
		add_action('wp_ajax_sllog', array( &$this,'edit_log'));
		add_action('wp_ajax_slphoto', array( &$this,'edit_photo'));
		add_action('wp_ajax_slmail', array( &$this,'edit_mail'));

		add_action('wp_ajax_slpromotion', array( &$this,'edit_promotion'));
		add_action('wp_ajax_slrecord', array( &$this,'edit_record'));
		add_action('wp_ajax_slcategory', array( &$this,'edit_category'));

		add_action('wp_ajax_slconfigbooking', array( &$this,'edit_configbooking'));

		add_action('wp_ajax_nopriv_slbooking', array( &$this,'edit_booking'));
		add_action('wp_ajax_nopriv_slconfirm', array( &$this,'edit_confirm'));
		add_action('wp_ajax_nopriv_slsearch', array( &$this,'edit_search'));


		if (SALON_DEMO ) {
			add_action( 'admin_bar_menu',  array( &$this,'remove_admin_bar_menu'), 201 );
//			add_action('admin_head',  array( &$this,'my_admin_head'));
			add_action('wp_before_admin_bar_render',  array( &$this,'add_new_item_in_admin_bar'));
			add_action('wp_dashboard_setup', array( &$this,'example_remove_dashboard_widgets'));
			remove_action( 'admin_menu', 'wpcf7_admin_menu', 9 );
		}

		add_action('admin_head',array( &$this, 'display_favicon'));


	}

//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/ デモ
// 管理バーの項目を非表示
public function remove_admin_bar_menu( $wp_admin_bar ) {
	if (SALON_DEMO && strtolower($this->user_role) != 'administrator') {
		$wp_admin_bar->remove_menu('wp-logo');			// ロゴ
		$wp_admin_bar->remove_menu('site-name');		// サイト名
		$wp_admin_bar->remove_menu('view-site');		// サイト名 -> サイトを表示
		$wp_admin_bar->remove_menu('comments');			// コメント
		$wp_admin_bar->remove_menu('new-content');		// 新規
		$wp_admin_bar->remove_menu('new-post');			// 新規 -> 投稿
		$wp_admin_bar->remove_menu('new-media');		// 新規 -> メディア
		$wp_admin_bar->remove_menu('new-link');			// 新規 -> リンク
		$wp_admin_bar->remove_menu('new-page');			// 新規 -> 固定ページ
		$wp_admin_bar->remove_menu('new-user');			// 新規 -> ユーザー
		$wp_admin_bar->remove_menu('updates');			// 更新
		$wp_admin_bar->remove_menu('my-account');		// マイアカウント
		$wp_admin_bar->remove_menu('user-info');		// マイアカウント -> プロフィール
		$wp_admin_bar->remove_menu('edit-profile');		// マイアカウント -> プロフィール編集
		$wp_admin_bar->remove_menu('logout');			// マイアカウント -> ログアウト
	}
}
// 管理バーのヘルプメニューを非表示にする
public function my_admin_head(){
	if (SALON_DEMO && strtolower($this->user_role) != 'administrator')
		 echo '<style type="text/css">#contextual-help-link-wrap{display:none;}</style>';
}

public function add_new_item_in_admin_bar() {
	if (SALON_DEMO && strtolower($this->user_role) != 'administrator') {
		global $wp_admin_bar;
		$wp_admin_bar->add_menu(array(
		'id' => 'new_item_in_admin_bar',
		'title' => __('Log Out'),
		'href' => wp_logout_url()
		));
	}
}
public function example_remove_dashboard_widgets() {
	if (SALON_DEMO && strtolower($this->user_role) != 'administrator') {
		global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);		// 現在の状況
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);	// 最近のコメント
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);	// 被リンク
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);			// プラグイン
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);		// クイック投稿
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);		// 最近の下書き
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);			// WordPressブログ
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);			// WordPressフォーラム
	}
}
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/ デモ




	public function daily_action() {
		error_log('Salon booking daily_action start  '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
		$this->_sl_daily__delete_temporary_sql();
		error_log('Salon booking daily_action temporary_data_delete  '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
		$this->_sl_daily_action_sql();
	}

	private function _sl_daily__delete_temporary_sql () {
		global $wpdb;
		$target = Salon_Component::computeDate(-1);
		//photodata
		$this->_delete_temp_photo_data($target);
		$current_time = date_i18n('Y-m-d H:i:s');
		$sql = 'SELECT * FROM '.$wpdb->prefix.'salon_reservation WHERE update_time < %s AND status = %d AND delete_flg <> %d';
		$edit_sql = $wpdb->prepare($sql,$target,Salon_Reservation_Status::TEMPORARY,Salon_Reservation_Status::DELETED);
		$result = $wpdb->get_results($edit_sql,ARRAY_A);
		if ($result === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			return;
		}
		if (count($result) == 0 ) {
			error_log('temporary no delete data '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			return;
		}
		else {
			error_log('_/_/_/ delete data _/_/_/'.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			$tmp = array();
			foreach ($result as $k1 => $d1 ) {
				$tmp[] = $d1['reservation_cd'];
			}
			error_log('data -> '.implode(',',$tmp)."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
		}
		$sql = 'UPDATE '.$wpdb->prefix.'salon_reservation SET delete_flg = %d, update_time = %s WHERE update_time < %s AND status = %d AND delete_flg <> %d';
		$edit_sql = $wpdb->prepare($sql,Salon_Reservation_Status::DELETED,$current_time,$target,Salon_Reservation_Status::TEMPORARY,Salon_Reservation_Status::DELETED);
		if ($wpdb->query($edit_sql) === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			return;
		}
		$current_time = date_i18n('Y-m-d H:i:s');
		$sql = 'INSERT INTO '.$wpdb->prefix.'salon_log  (`sql`,remark,insert_time ) VALUES  (%s,%s,%s) ';
		$result = $wpdb->query($wpdb->prepare($sql,$edit_sql,__FILE__.' '.__FUNCTION__,$current_time));
	}

	private function _delete_temp_photo_data($target) {
		global $wpdb;
		$sql = 'SELECT  photo_path,photo_resize_path,photo_id FROM '.$wpdb->prefix.'salon_photo WHERE update_time < %s AND delete_flg = %d';
		$edit_sql = $wpdb->prepare($sql,$target,Salon_Reservation_Status::TEMPORARY);
		$result = $wpdb->get_results($edit_sql,ARRAY_A);
		if ($result === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			return;
		}
		//これは削除もれがあった場合のみ有効なコード。確定前にF5とかか
		error_log('_/_/_/ photo delete data start _/_/_/'.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
		$sql = 'DELETE FROM '.$wpdb->prefix.'salon_photo  WHERE update_time < %s AND delete_flg = %d';
		$edit_sql = $wpdb->prepare($sql,$target,Salon_Reservation_Status::DELETED);
		if ($wpdb->query($edit_sql) === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			return;
		}
		foreach ($result as $k1 => $d1 ) {

			error_log($d1['photo_id'].' '.$d1['photo_path'].' '.$d1['photo_resize_path'].date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			if ( ! unlink(SALON_UPLOAD_DIR.basename($d1['photo_path'])) ) {
				error_log('delete error:'.SALON_UPLOAD_DIR.basename($d1['photo_path']).' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			}
			if ( ! unlink(SALON_UPLOAD_DIR.basename($d1['photo_resize_path'])) ) {
				error_log('delete error:'.SALON_UPLOAD_DIR.basename($d1['photo_resize_path']).' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			}
			$sql = 'UPDATE '.$wpdb->prefix.'salon_photo SET delete_flg = %d WHERE photo_id = %d';
			$edit_sql = $wpdb->prepare($sql,Salon_Reservation_Status::DELETED,$d1['photo_id']);
			if ($wpdb->query($edit_sql) === false ) {
				error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
				return;
			}
		}

	}

	private function _sl_daily_action_sql () {
		$result =  unserialize(get_option( 'SALON_CONFIG'));
		if (empty($result['SALON_CONFIG_DELETE_RECORD']) ) $result['SALON_CONFIG_DELETE_RECORD'] =  Salon_Config::DELETE_RECORD_NO;
		if ($result['SALON_CONFIG_DELETE_RECORD'] ==   Salon_Config::DELETE_RECORD_NO ) return;
		if (empty($result['SALON_CONFIG_DELETE_RECORD_PERIOD']) ) $result['SALON_CONFIG_DELETE_RECORD_PERIOD'] =  Salon_Config::DELETE_RECORD_PERIOD;
		$from = Salon_Component::computeMonth(-1*$result['SALON_CONFIG_DELETE_RECORD_PERIOD']);
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'salon_reservation WHERE update_time < %s ';
		$edit_sql = $wpdb->prepare($sql,$from);
		$result = $wpdb->get_results($edit_sql,ARRAY_A);
		if ($result === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			return;
		}
		if (count($result) == 0 ) {
			error_log($table_name.' no delete data '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			return;
		}
		else {
			error_log('_/_/_/ '.$table_name.' update mask data _/_/_/'.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			$tmp = array();
			foreach ($result as $k1 => $d1 ) {
				$tmp[] = $d1['reservation_cd'];
			}
			error_log('data -> '.implode(',',$tmp)."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
		}

		$sql = 'UPDATE '.$wpdb->prefix.'salon_reservation SET non_regist_name = "" , non_regist_email= "" , non_regist_tel= "" WHERE update_time < %s ';
		$edit_sql = $wpdb->prepare($sql,$from);
		if ($wpdb->query($edit_sql) === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
			return;
		}
		if ( !SALON_DEMO ) {
			$sql = 'DELETE FROM '.$wpdb->prefix.'salon_log  WHERE insert_time < %s ';
			$edit_sql = $wpdb->prepare($sql,$from);
			if ($wpdb->query($edit_sql) === false ) {
				error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
				return;
			}
		}
		$current_time = date_i18n('Y-m-d H:i:s');
		$sql = 'INSERT INTO '.$wpdb->prefix.'salon_log  (`sql`,remark,insert_time ) VALUES  (%s,%s,%s) ';
		$result = $wpdb->query($wpdb->prepare($sql,$edit_sql,__FILE__.' '.__FUNCTION__,$current_time));

	}


	public function init_session_start(){
		if (!isset($_SESSION))  session_start();
	}

	public function update_profile_fields( $contactmethods ) {
//		unset($contactmethods['aim']);
//		unset($contactmethods['jabber']);
//		unset($contactmethods['yim']);

		if(!array_key_exists('zip', $contactmethods)) $contactmethods['zip']= __('zip',SL_DOMAIN);
		if(!array_key_exists('address', $contactmethods))$contactmethods['address']= __('address',SL_DOMAIN);
		if(!array_key_exists('tel', $contactmethods))$contactmethods['tel']= __('tel',SL_DOMAIN);
		if(!array_key_exists('mobile', $contactmethods))$contactmethods['mobile']= __('mobile',SL_DOMAIN);

		return $contactmethods;
	}


	public function is_multi_branch (){
		return $this->config_branch == Salon_Config::MULTI_BRANCH;
	}

// 	public function get_default_brandh_cd (){
// 		return SALON_CONFIG_DEFAULT_BRANCH_CD;
// 	}


	function get_pages( $pages ) {
		$confirm_page_id =  get_option('salon_confirm_page_id');
		for ( $i = 0; $i < count($pages); $i++ ) {
			if ( !empty($pages[$i]->ID) && $pages[$i]->ID == $confirm_page_id  )
				unset( $pages[$i] );
		}

		return $pages;
	}

	public function admin_init() {

		global $plugin_page;
		if ( ! isset( $plugin_page ) || ( substr($plugin_page,0,5)  !=	'salon' )) return;

		if(!file_exists(SALON_UPLOAD_DIR)){
			mkdir(SALON_UPLOAD_DIR,0755,true);
		}

		remove_action( 'admin_notices', 'update_nag', 3 );


	}


	private function _get_userdata (&$user_role) {
		$edit_menu = array();
		//global $current_user;
		//get_currentuserinfo();
		$current_user = wp_get_current_user();
		$user_roles = $current_user->roles;
		$user_role = array_shift($user_roles);
		if ($user_role == 'subscriber' ) return;
		if (is_multisite() && is_super_admin() ) {
			$show_menu = array(
				'edit_customer'
				,'edit_item'
				,'edit_staff'
				,'edit_branch'
				,'edit_config'
				,'edit_position'
				,'edit_log'
				,'edit_reservation'
				,'edit_sales'
				,'edit_working'
				,'edit_working_all'
				,'edit_base'
				,'edit_promotion'
				,'edit_record'
				,'edit_category'
			);
		}
		else {
			global $wpdb;
			$sql =  'SELECT role FROM '.$wpdb->prefix.'salon_position po ,'.
									$wpdb->prefix.'salon_staff st '.
							' WHERE st.user_login = %s '.
							'   AND st.position_cd = po.position_cd '.
							'   AND st.delete_flg <> '.Salon_Reservation_Status::DELETED;
			$result = $wpdb->get_results(
						$wpdb->prepare($sql,$current_user->user_login),ARRAY_A);
			$show_menu =  explode(",",$result[0]['role']);
		}
		if (in_array('edit_customer',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_customer';
		if (in_array('edit_item',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_item';
		if (in_array('edit_staff',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_staff';
		if (in_array('edit_branch',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_branch';
		if (in_array('edit_config',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_config';
		if (in_array('edit_position',$show_menu) )  $edit_menu[$this->maintenance][] = 'edit_position';
		if (in_array('edit_log',$show_menu) )  $edit_menu[$this->maintenance][] = 'edit_log';
		if (in_array('edit_category',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_category';

		if (in_array('edit_reservation',$show_menu) ) $edit_menu[$this->management][] = 'edit_reservation';
		if (in_array('edit_sales',$show_menu) ) $edit_menu[$this->management][] = 'edit_sales';
		if (in_array('edit_working',$show_menu) ) $edit_menu[$this->management][] = 'edit_working';
		if (in_array('edit_working_all',$show_menu) ) $edit_menu[$this->management][] = 'edit_working';
		if (in_array('edit_base',$show_menu) ) $edit_menu[$this->management][] = 'edit_base';
		if (in_array('edit_promotion',$show_menu) ) $edit_menu[$this->management][] = 'edit_promotion';
		if (in_array('edit_record',$show_menu) ) $edit_menu[$this->management][] = 'edit_record';


		return $edit_menu;
	}



	public function admin_menu() {

		$show_menu = $this->_get_userdata($this->user_role);
		if (isset($show_menu[$this->maintenance]) && $show_menu[$this->maintenance] && count($show_menu[$this->maintenance]) > 0 ) {
			add_menu_page( __('Salon Maintenance',SL_DOMAIN), __('Salon Maintenance',SL_DOMAIN), 'edit_posts', $this->maintenance, array( &$this,$show_menu[$this->maintenance][0]),WP_PLUGIN_URL.'/salon-booking/images/menu-icon.png' );
			if (in_array('edit_customer',$show_menu[$this->maintenance]) ) {
				$file = $show_menu[$this->maintenance][0] == 'edit_customer' ? $this->maintenance : 'salon_customer';
				$my_admin_page = add_submenu_page(  $this->maintenance, __('Customer Info',SL_DOMAIN), __('Customer Info',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_customer' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_customer'));
			}
			if (in_array('edit_item',$show_menu[$this->maintenance]) ) {
				$file = $show_menu[$this->maintenance][0] == 'edit_item' ? $this->maintenance : 'salon_item';
				$my_admin_page = add_submenu_page(  $this->maintenance, __('Menu Information',SL_DOMAIN), __('Menu Information',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_item' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_item'));
			}
			if (in_array('edit_staff',$show_menu[$this->maintenance]) ) {
				$file = $show_menu[$this->maintenance][0] == 'edit_staff' ? $this->maintenance : 'salon_staff';
				$my_admin_page = add_submenu_page(  $this->maintenance, __('Staff Information',SL_DOMAIN), __('Staff Information',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_staff' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_staff'));
			}
			if (in_array('edit_branch',$show_menu[$this->maintenance]) &&  $this->is_multi_branch() )  {
				$file = $show_menu[$this->maintenance][0] == 'edit_branch' ? $this->maintenance : 'salon_branch';
				$my_admin_page = add_submenu_page( $this->maintenance, __('Shop Information',SL_DOMAIN), __('Shop Information',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_branch' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_branch'));
			}
			if (in_array('edit_config',$show_menu[$this->maintenance]) ) {
				$file = $show_menu[$this->maintenance][0] == 'edit_config' ? $this->maintenance : 'salon_config';
				$my_admin_page = add_submenu_page(  $this->maintenance, __('Environment Setting',SL_DOMAIN), __('Environment Setting',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_config' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_config'));
//				//[2014/07/30]
				$my_admin_page = add_submenu_page(  $this->maintenance, __('Mail Setting',SL_DOMAIN), __('Mail Setting',SL_DOMAIN), 'edit_posts', 'salon_mail', array( &$this, 'edit_mail' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_mail'));
				//[2017/03/02]
				$my_admin_page = add_submenu_page(  $this->maintenance, __('Reservation Screen',SL_DOMAIN), __('Reservation Screen',SL_DOMAIN), 'edit_posts', 'salon_configbooking', array( &$this, 'edit_configbooking' ) );

			}
			if (in_array('edit_position',$show_menu[$this->maintenance]) )  {
				$file = $show_menu[$this->maintenance][0] == 'edit_position' ? $this->maintenance : 'salon_position';
				$my_admin_page = add_submenu_page(  $this->maintenance, __('Position Information',SL_DOMAIN), __('Position Information',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_position' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_position'));
			}
			if (in_array('edit_log',$show_menu[$this->maintenance]) )  {
				$file = $show_menu[$this->maintenance][0] == 'edit_log' ? $this->maintenance : 'salon_log';
				$my_admin_page = add_submenu_page(  $this->maintenance, __('View Log',SL_DOMAIN), __('View Log',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_log' ) );
			}
//[Ver1.5.1]
			if (in_array('edit_category',$show_menu[$this->maintenance]) ) {
				$file = $show_menu[$this->maintenance][0] == 'edit_category' ? $this->maintenance : 'salon_category';
				$my_admin_page = add_submenu_page( $this->maintenance, __('Category',SL_DOMAIN), __('Category',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_category' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_category'));
			}



		}
		if (isset($show_menu[$this->management]) && $show_menu[$this->management] && count($show_menu[$this->management]) > 0 ) {
			add_menu_page( __('Salon Management',SL_DOMAIN), __('Salon Management',SL_DOMAIN), 'edit_posts', $this->management, array( &$this,$show_menu[$this->management][0] ),WP_PLUGIN_URL.'/salon-booking/images/menu-icon.png');
			if (in_array('edit_reservation',$show_menu[$this->management]) ) {
				$file = $show_menu[$this->management][0] == 'edit_reservation' ? $this->management : 'salon_reservation';
				$my_admin_page = add_submenu_page( $this->management, __('Reservation Regist',SL_DOMAIN), __('Reservation Regist',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_reservation' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_reservation'));
			}
			if (in_array('edit_sales',$show_menu[$this->management]) ) {
				$file = $show_menu[$this->management][0] == 'edit_sales' ? $this->management : 'salon_sales';
				$my_admin_page = add_submenu_page( $this->management, __('Performance Regist',SL_DOMAIN), __('Performance Regist',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_sales' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_sales'));
			}
			if (in_array('edit_promotion',$show_menu[$this->management]) ) {
				$file = $show_menu[$this->management][0] == 'edit_promotion' ? $this->management : 'salon_promotion';
				$my_admin_page = add_submenu_page( $this->management, __('Promotion',SL_DOMAIN), __('Promotion',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_promotion' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_promotion'));
			}
			if (in_array('edit_working',$show_menu[$this->management]) ) {
				$file = $show_menu[$this->management][0] == 'edit_working' ? $this->management : 'salon_working';
				$my_admin_page = add_submenu_page( $this->management, __('Time Card',SL_DOMAIN), __('Time Card',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_working' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_timecard'));
			}
			if (in_array('edit_base',$show_menu[$this->management]) ) {
				$file = $show_menu[$this->management][0] == 'edit_base' ? $this->management : 'salon_basic';
				$my_admin_page = add_submenu_page( $this->management, __('Basic Information',SL_DOMAIN), __('Basic Information',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_base' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_base'));
			}
//[Ver1.5.1]
			if (in_array('edit_record',$show_menu[$this->management]) ) {
				$file = $show_menu[$this->management][0] == 'edit_record' ? $this->management : 'salon_record';
				$my_admin_page = add_submenu_page( $this->management, __('Customer Record',SL_DOMAIN), __('Customer Record',SL_DOMAIN), 'edit_posts', $file, array( &$this, 'edit_record' ) );
				add_action('load-'.$my_admin_page, array(&$this,'admin_add_help_record'));
			}
		}

		if (SALON_DEMO && strtolower($this->user_role) != 'administrator') {
			global $menu;
			unset($menu[2]);//ダッシュボード
			unset($menu[4]);//メニューの線1
			unset($menu[5]);//ｐｏｓｔ
			unset($menu[10]);//メディア
			unset($menu[15]);//リンク
			unset($menu[20]);//ページ
			unset($menu[25]);//コメント
			unset($menu[59]);//メニューの線2
			unset($menu[60]);//テーマ
			unset($menu[65]);//プラグイン
			unset($menu[70]);//プロファイル
			unset($menu[75]);//ツール
			unset($menu[80]);//設定
			unset($menu[90]);//メニューの線3
		}


	}

	public function display_favicon($hook_suffix){
		global $plugin_page;
		if ( ! isset( $plugin_page ) || ( substr($plugin_page,0,5)  !=	'salon' )) return;
		echo "<!-- Favicon  From -->\r\n";
		echo '<link rel="shortcut icon" href="'.SL_PLUGIN_URL.'/images/favicon.ico">';
		echo "<!-- Favicon  To   -->\r\n";
	}

	public function admin_add_help_customer() {
		$screen = get_current_screen();
		$help = '<br>'.__('You can register the existing client not yet a registered member or update the clinet. ',SL_DOMAIN);
		$help .= '<ul >';
		$help .= '<li><strong>'.__('User Login',SL_DOMAIN).'</strong> - '.__('Supposing the Login Name is the same with Mail Address, any ID could be accepted so long as it is Sole and Unique within the site.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('Rank',SL_DOMAIN).'</strong> - '.__('Select Customers rank.',SL_DOMAIN ).'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,true,true));
		$this->help_side($screen);
	}

	public function admin_add_help_item() {
		$screen = get_current_screen();
		$help = '<ul>';
		$help .= '<li><strong>'.__('Required Time(minutes)',SL_DOMAIN).'</strong> - '.__('When a customer has reserved, this plugin used to calculate the end time automatically.Please enter in minutes.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('Price',SL_DOMAIN).'</strong> - '.__('When a customer has reserved, this plugin used to calculate the price automatically.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('All staff member can treat',SL_DOMAIN).'</strong> - '.__('If all staff member can treat this menu,check here.If some staff members can treat this menu,check in the screent of "Staff Information",not here.',SL_DOMAIN ).'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,true,true));
		$this->help_side($screen);
	}

	public function admin_add_help_staff() {
		$screen = get_current_screen();
		$help = '<br>'.__('If you register as a staff here, you will become also a user of "Word Press". If you are already registered to the Word Press with the authority of contributor or upper, you will be on the list of the staffs here.However, you are not still registered yet as a staff, you have to add the user information after the selection, and you can use the function as a staff just by the plug in. ',SL_DOMAIN);
		$help .= '<ul>';
		$help .= '<li><strong>'.__('Position',SL_DOMAIN).'</strong> - '.__('If you change the position,the role of wordpress also change.Be carefull!',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('The Maximum Number of the Redundant Reservations',SL_DOMAIN).'</strong> - '.__('Set the number of redundant reservations a staff can handle at the same timeframe.Normally the number should be zero, but could it be 1 if the staff should have an assistant and serve duplicate clients at the same time. Of course highly capable staff may also select 3, or 4.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('Menu',SL_DOMAIN).'</strong> - '.__('Check the menu which this staff member can treat.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('Introductions',SL_DOMAIN).'</strong> - '.__('Input put self-introductions.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('Addition/Update of Photograph',SL_DOMAIN).'</strong> - '.
				'<ol >'.
				'<li style="list-style-type: lower-alpha">'.__('Drag & drop of  a photograph of the staff  into "Photos of staff member" area.',SL_DOMAIN).'</li>'.
				'<li style="list-style-type: lower-alpha">'.__('Multiple photographs are allowed.When a customer clicks a photograph part of "the reservation screen",registered multiple photographs displayed sequentially. (only as for the PC)',SL_DOMAIN).'</li>'.
				'<li style="list-style-type: lower-alpha">'.__('The first photograph is displayed in the "the reservation screen". Drag & drop the order of  photographs.',SL_DOMAIN).'</li>'.
				'<li style="list-style-type: lower-alpha">'.__('When you change or add the photograph, do not forget to click the button "Update".',SL_DOMAIN).'</li>'.
				'</ol>'.
		'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,true,true));
		$this->help_side($screen);
	}

	public function admin_add_help_branch() {
		$screen = get_current_screen();
		$help = '<ul>';
		$help .= '<li><strong>'.__('Please copy and paste this tag to insert to the page',SL_DOMAIN).'</strong> - '.__('This field should be inserted to the fixed page as it reads, then the reservation screen will be displayed.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('Open Time, Close Time',SL_DOMAIN).'</strong> - '.__('If Close Time is over midnight, plus 24 to close time.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('The Maximum Number of the Redundant Reservations',SL_DOMAIN).'</strong> - '.__('By entering the number, you can set the maximum number of the reservations to coop cooperate with simultaneously by each shop. If the staff serves to only one client full time one on one, set the number as zero.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('Unit of Time (minutes)',SL_DOMAIN).'</strong> - '.__('Time unit is selectable for the clients when making reservation. If unit 15 is designated, then the time is selectable from 10:00, 10:15 and so on. If 30 is selected, 10:00, 10:30, and so, for example.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('Regular Closing Day',SL_DOMAIN).'</strong> - '.__('You can nominate a regular closing day of the week by the unit of week. If you open the shop on regular closing day or you close the shop on business day, you have to set it on the screen of "Basic Information" under "Salon Maintenance".',SL_DOMAIN ).'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,true,true));
		$this->help_side($screen);
	}

	public function admin_add_help_config() {
		$screen = get_current_screen();

		$help = '<ol style="list-style-type:decimal">';
///		$help .= '<li><strong>'.__('Number of the Shops',SL_DOMAIN).'</strong> - '.__('Select "plural shops", if the Salon holds more than one shop and has the need to have an independent reservation page for each shop.',SL_DOMAIN ) . '</li>';
///		$help .= '<li><strong>'.__('Number of the Shops',SL_DOMAIN).'</strong> - '.__('Now plural shops select only.',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Approval of the Login by the Clients',SL_DOMAIN).'</strong> - '.__('If you want the columns for user ID and the password be displayed, on the screen of Reservation, check mark the column of  "approve the client’s login". This is the function not required if the same function is provided by other means like sidebars and so.',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Opration Log Setting',SL_DOMAIN).'</strong> - '.__('Check the mark "Opration Log Setting" to recorded the opration of "Add","Update" or "Delete" to log file.',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Automatic Deletion',SL_DOMAIN).'</strong> - '.__('Check the mark "automatic deletion"to mask the personal information like names and phone numbers after the designated month since the reservation date. This is daily updated automatically at midnight. ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Months when to delete ',SL_DOMAIN).'</strong> - '.__('Enter the designated months ahead of the reservation date by number in the column of  "months when to delete". This column is only valid when you check marked the column of  "automatic deletion".  ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Display Details at messages',SL_DOMAIN).'</strong> - '.__('By check marking the column of  "Display Details", the detailed information of the errors will be displayed. If you want the errors be analyzed, this should b e presented to the analyzer.  ',SL_DOMAIN ) . '</li>';
		$help .= '</ol>';

		$this->_setTab($screen,'_content1',__( 'Content').'(1-5)',$help);

		$help = '<ol style="list-style-type:decimal"  start="6">';
		$help .= '<li><strong>'.__('Staff Holiday Settings',SL_DOMAIN).'</strong> - '.__('If you select "unable to enter when holidays" , clients cannot make reservation on the holidays of the designated staff. If the staff select "unable to enter other than when attendant", clients can only reserve where the staff registers as attendant on the "timecard" screen under the "Salon Management". You may select "unable to enter other than when attendant""if you could register your attendance and the absence correctly in advance, however you are strongly recommended to choose "unable to enter when holidays" on daily operations.  　　　  ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Sequence of Sur Name and Given Name',SL_DOMAIN).'</strong> - '.__('This is the selection of the sequence of Sur Name and Given Name. Select "Sur Name first"  mainly for Japanese and Chinese. "Given Name first" is another choice. ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('No Designation of Staff',SL_DOMAIN).'</strong> - '.__('If you allow the reservation without nomination of a certain staff, check the column. If you do not mark the column, it means the designation of any staffs becomes mandatory for any clients and may not recommend since the prospect clients may confuse.',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Maintenance staff member include staff',SL_DOMAIN).'</strong> - '.__('If you allow Maintenance staff member include staff, check the column.',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Mobile screen use',SL_DOMAIN).'</strong> - '.__('If you use the screen of mobiles and pc, check the column. If you do not mark the column, it means that on smart phone such as "iphone" pc screen displays. ',SL_DOMAIN ) . '</li>';
		$help .= '</ol>';

		$this->_setTab($screen,'_content2',__( 'Content').'(6-10)',$help);

		$help = '<ol style="list-style-type:decimal" start="11">';
		$help .= '<li><strong>'.__('past X days',SL_DOMAIN).'</strong> - '.__('This column is used to select the range of the days extracted from the data base of the reservation and the actual performance to view as a list. The criteria of X day are the day you currently operate the system. If you choose a large numerical value for the X to enter, the response time of the system may slow down. So, please make the X according to the situation and the need of the shop. (E.g. the reservation is full for another 1 year, or one month is enough for it, or else) ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('X days ahead',SL_DOMAIN).'</strong> - '.__('<br>This column is used to select the range of the days extracted from the data base of the reservation and the actual performance to view as a list. The criteria of X day are the day you currently operate the system. If you choose a large numerical value for the X to enter, the response time of the system may slow down. So, please make the X according to the situation and the need of the shop. (E.g. the reservation is full for another 1 year, or one month is enough for it, or else) ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Number of the staffs displayed',SL_DOMAIN).'</strong> - '.__('This is the screen showing staffs for the reservation. Recommend the number you enter to be 3 to 6 around, for the better visual appealing.  ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Default load tab',SL_DOMAIN).'</strong> - '.__('If you change the load default tab at "Reservation Screen",select "Staff","Month","Week" or  "Day". ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Deadline of reservations',SL_DOMAIN).'</strong> - '.__('How many days or hours is the deadline of reservation.If you set 0, cancellation of the start time just before it is possible',SL_DOMAIN ) . '</li>';
		$help .= '</ol>';

		$this->_setTab($screen,'_content3',__( 'Content').'(11-15)',$help);

		$this->help_common($screen,array(true,false,false));
		$this->help_side($screen);
	}

	public function admin_add_help_mail() {
		$screen = get_current_screen();
		$help = '<ul>';
		$help .= '<li><strong>'.__('Mail from',SL_DOMAIN).'</strong> - '.__('If you use original "from" of  Mail header fields, fill in this field using "Name<mail address>" formats.',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Mail return path',SL_DOMAIN).'</strong> - '.__('If you use original "return path" of  Mail header fields, fill in this field.',SL_DOMAIN ) . '</li>';

		$help .= '<li><strong>'.__('Select Mail',SL_DOMAIN).'</strong> - '.__('Select this field, the selected mail infromation displayed below.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('Mail address (Bcc)',SL_DOMAIN).'</strong> - '.__('This field is displayed in case of selected "The Mail to information to the registerd staff member".If you want to receive the mail about reservation, input mail addresses separated by commas.  ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('The Content of the Mail to Confirming Notice to the Client',SL_DOMAIN).'</strong> - '.__('When responding to the clients of without registration as a member, if you put [X-TO_NAME] and [X-TO_SALON] into the contents of the confirmation mail, which will be displayed as the name of the client and the shop automatically and sent to the client.  ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('The Content of the Mail to respond to the Client newly registered as a Member',SL_DOMAIN).'</strong> - '.__('This is the model contents of the mail to the client who checked "register as a member"on the screen of Reservation Confirmation. In this notice mail, the password for the client is also included. ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('The Content of the Mail to staff member new reservations',SL_DOMAIN).'</strong> - '.__('This is the model contents of the mail to the staff who enterd "Mail address (Bcc)"on this screen.',SL_DOMAIN ) . '</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,false,false));
		$this->help_side($screen);
	}


	public function admin_add_help_position() {
		$screen = get_current_screen();
		$help = '<br>'.__('You can select the functions according to the position of the staffs you selected. All the functions are set as default according to each position. Check with the screen what can be handled by each position from part time worker to the President. For example, part timers are set only capable of reservation registration as a default, and if you think a certain part timer be allowed to use the function of "TimeCard", then check here to activate the function. <font color="red">Take special care with the "wp_role".</font>',SL_DOMAIN);
		$help .= '<ul>';
		$help .= '<li><strong>'.__('Role',SL_DOMAIN).'</strong> - '.
				'<ol >'.
				'<li style="list-style-type: lower-alpha">'.__('If you check marking the "Authority of Management", then you can operate also information of other shops.',SL_DOMAIN).'</li>'.
				'<li style="list-style-type: lower-alpha">'.__('If you check marking the "Time Card (full members)", you can access all the information on attendances and the absences of all the staffs in your shop, so take caution in operating the system.',SL_DOMAIN).'</li>'.
				'</ol>'.
		'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,true,true));
		$this->help_side($screen);
	}

	public function admin_add_help_reservation() {
		$screen = get_current_screen();
		$help = '<br>'.__('It displays all the reservation for "X days ahead" from an operation day.',SL_DOMAIN);
		$help .= '<ul>';
		$help .= '<li><strong>'.__('Existing Clients',SL_DOMAIN).'</strong> - '.__('If the client is a member, select either by "Mail"  or by "Phone" , and the name of the client, then click the "Search" button. If the client, for example, is Taro YAMAMOTO, input minimum letters like "YAMA" in the "Name" column, then all the clients whose name have "YAMA" letters within their names are all listed up on the screen. If you select from the list by whom the reservation was made, then the screen returns back to the screen of Reservation Register with the rest of the information related to the client is already filled in.',SL_DOMAIN).'</li>';
		$help .= '<li><strong>'.__('Newly acquired Clients',SL_DOMAIN).'</strong> - '.__('As for new clients, the input of "Mail" or "Phone" is mandatory. Confirming the client of the will to join as a member, check the column of "Register as a Member" . The name for the login  could be either the Mail Address if the address is set in the column of "Mail", or the Phone No. of the "Phone" column with eliminating the symbol mark of " - " inserted among the phone number if the client has no email address. The Password initially assigned is the same with the login Name. Notice to the client the Login Name and tentative Password, and recommend change the password immediately to what the client created.',SL_DOMAIN).'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,true,true));
		$this->help_side($screen);
	}

	public function admin_add_help_sales() {
		$screen = get_current_screen();
		$help = '<br>'.sprintf(__('The Screen shows the list of all the reservation from "past X days" to an operation day.<br>Select the reservations as was actually performed, and register as required. Modify or update the columns where the letters are with blue colored since the data are transferred there from the reservation.<br><font color="green">Using this "performance" data, additional functions like accountings and statistics will be available in near future,probably.....</font><a href="%s" target="_blank">Please tell me if you have any good ideas about that.</a>',SL_DOMAIN),__('https://salon.mallory.jp/en/?page_id=16',SL_DOMAIN));
		$help .= '<ul>';
		$help .= '<li><strong>'.__('Register without Reservation',SL_DOMAIN).'</strong> - '.__('If you mark the column, the screen is displayed almost same with the "Reservation Register" under "Salon Management" automatically. Use this screen for the performance input without reservation.',SL_DOMAIN).'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,true,true));
		$this->help_side($screen);
	}

	public function admin_add_help_promotion() {
		$screen = get_current_screen();
		$help = '<ul>';
		$help .= '<li><strong>'.__('Code',SL_DOMAIN).'</strong> - '.__('Enter code for provide a coupon.',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Description',SL_DOMAIN).'</strong> - '.__('Enter name of a coupon.',SL_DOMAIN ) . '</li>';

		$help .= '<li><strong>'.__('Valid from, Valid to',SL_DOMAIN).'</strong> - '.__('If you want to set a time limit, enter this fields.',SL_DOMAIN ).'</li>';
		$help .= '<li><strong>'.__('Usable',SL_DOMAIN).'</strong> - '.__('Select "Can only be used less than X times per customer", it is not used more than x times including repeats. ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Discount Patern',SL_DOMAIN).'</strong> - '.__('This field apply a discount, as a percentage or as a flat amount. ',SL_DOMAIN ) . '</li>';
		$help .= '<li><strong>'.__('Discount',SL_DOMAIN).'</strong> - '.__('Enter percentage or amount.',SL_DOMAIN ) . '</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,false,false));
		$this->help_side($screen);
	}


	public function admin_add_help_timecard() {
		$screen = get_current_screen();
		$help = '<br>'.__('Set the working schedule of staffs. With the role of "TimeCard(Full Members)", the system displays all the staffs belonging to the shop.',SL_DOMAIN);
		$help .= '<ul>';
		$help .= '<li><strong>'.__('New Registration',SL_DOMAIN).'</strong> - '.__('Double click the date column on when you want register, or display the detailed screen by dragging the attendant hour and dropping it onto the leaving hour. And check mark either the column of "Regular Duty", "Absent", "Delay", "Leave Early" or "Holiday Shift".',SL_DOMAIN).'</li>';
		$help .= '<li><strong>'.__('Update/Delete',SL_DOMAIN).'</strong> - '.__('By double clicking the column of the already registered, display the detail list and update the data and/or delete.',SL_DOMAIN).'</li>';
		$help .= '<li><strong>'.__('Copy and Paste',SL_DOMAIN).'</strong> - '.__('Click the column of already registered and select which data be copied and pasted.Push down the "CTRL" and the "C" keys of PC at the same time to copy, and after clicking the date where to be pasted, push again down the "CTRL" and the "V" keys simultaneously.',SL_DOMAIN).'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(false,false,true));
		$this->help_side($screen);
	}

	public function admin_add_help_base() {
		$screen = get_current_screen();
		$help = '<br>'.__('The mandatory input items are almost same with those of "Shop Information".<br>Those cases namely to open specially on the regular holidays or take special absence for the trainings should be input at the "Basic Information" under "Shop Management",which operations are only valid for staff of the shop who logged in.',SL_DOMAIN);
		$help .= '<ul>';
		$help .= '<li><strong>'.__('Irregular Open/Closing day',SL_DOMAIN).'</strong> - '.
				'<ol >'.
				'<li style="list-style-type: lower-alpha">'.__('After setting the date, select the reason either of "On Business" or "Special Absence", and click the button of "Add".',SL_DOMAIN).'</li>'.
				'<li style="list-style-type: lower-alpha">'.__('It is possible to input data of the year other than that of currently operating, and if you want to confirm of the information on other year, click the button of "Display Again" after setting the designated year.',SL_DOMAIN).'</li>'.
				'</ol>'.
		'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,false,true));
		$this->help_side($screen);
	}
	//[Ver1.5.1]
	public function admin_add_help_record() {
		$screen = get_current_screen();
		$help = '<br>'.__('The detail information about customers is entered in this screen.',SL_DOMAIN);
		$help .= '<ul>';
		$help .= '<li><strong>'.__('Calendar',SL_DOMAIN).'</strong> - '.__('The reservated day become available  to click.Click the day,customers are displayed followin view formats.',SL_DOMAIN).'</li>';
		$help .= '<li><strong>'.__('Input each fields',SL_DOMAIN).'</strong> - '.__('The field which is registerd on the screen of "Category" are displayed.',SL_DOMAIN).'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,false,false));
		$this->help_side($screen);
	}

	public function admin_add_help_category() {
		$screen = get_current_screen();
		$help = '<br>'.__('The fields which use in the screen of "Customer Record" are entered in this screen.',SL_DOMAIN);
		$help .= '<ul>';
		$help .= '<li><strong>'.__('Category name',SL_DOMAIN).'</strong> - '.__('"Category name" is displayed as input field name in the screen of "Customer Record".',SL_DOMAIN).'</li>';
		$help .= '<li><strong>'.__('Category Patern',SL_DOMAIN).'</strong> - '.__('Select "Radio Button","Check Box","Text" or "Select Box".Selected patern  is displayed as input field value in the screen of "Customer Record".',SL_DOMAIN).'</li>';
		$help .= '<li><strong>'.__('Category Value',SL_DOMAIN).'</strong> - '.__('If you select  "Radio Button","Check Box" or "Select Box",you can enter this.Entered values which are separated by commas are able to select by  "Radio Button","Check Box" or "Select Box".',SL_DOMAIN).'</li>';
		$help .= '<li><strong>'.__('Select Target Table',SL_DOMAIN).'</strong> - '.__('Now you can select only "Record".',SL_DOMAIN).'</li>';
		$help .= '</ul>';
		$this->_setTab($screen,'_content',__( 'Content'),$help);
		$this->help_common($screen,array(true,false,false));
		$this->help_side($screen);
	}



	private function _setTab($screen,$id,$title,$content) {
		$screen->add_help_tab(array(
			'id'	=> $id,
			'title'	=> $title,
			'content'	=> $content,
		));
	}

	public function help_common ($screen,$patern = null) {
		$help = '<ol style="list-style-type:decimal;">';
			if (isset($patern[0]) && $patern[0]) {
				$help .= '<li>'.__('Button',SL_DOMAIN).
					'<br><img class="alignnone size-thumbnail wp-image-250" width="150" height="25" src="'.__('http://salon.mallory.jp/en/wp-content/uploads/2013/06/12_BUTTON.png',SL_DOMAIN).'">'.
					'<ol style="list-style-type:upper-alpha;">
						<li>'.__('"Add" and "Update" signify the addition and Updating of the information related.',SL_DOMAIN).'</li>
						<li>'.__('"Clear" does literally clear the detailed input columns.',SL_DOMAIN).'</li>
						<li>'.__('"Show Details" and "Hide Details" execute display and hide the detailed information by each.',SL_DOMAIN).'</li>
					</ol>
				</li>';
			}
			if (isset($patern[1]) && $patern[1]) {
				$help .= '<li>'.__('Part of Input Details',SL_DOMAIN).'<br>'.
				__('It is not displayed at the initial status. To display, you have to click the button of "Display Details" or click the "Select" from the list.',SL_DOMAIN).'</li>';
			}
			if (isset($patern[2]) && $patern[2]) {
				$help .= '<li>'.__('Listing',SL_DOMAIN).'<br><img width="150" height="87" src="'.__('http://salon.mallory.jp/en/wp-content/uploads/2013/06/13_OPRATION.png',SL_DOMAIN).'">
<br>'.

				__('It displays the list of Information. By clicking the button of "Select" in the operation column, information designated is updated. If you want to delete, click the button of "Delete" in the operation column. The information is deleted accordingly<br>The button of "up" or "down" on the Seq column is showing the order of item like staff or menus on the screen of "Reservation Detail.',SL_DOMAIN).'</li>';
			}
			$help .= '</ol>';

		$this->_setTab($screen,'_common', __( 'Common interface',SL_DOMAIN ),$help);

	}


	public function help_side ($screen) {
		$screen->set_help_sidebar(
			'<ul  style="margin-left:5px; list-style-type:disc">'.
			'<li>' . __( '<a href="http://salon.mallory.jp/en/?page_id=80" target="_blank">Documentation</a>',SL_DOMAIN ) . '</li>'.
			'<li>' . __( '<a href="https://salon.mallory.jp/en/?page_id=8" target="_blank">Sample</a>',SL_DOMAIN ) . '</li>'.
			__('user id : demologin<br>password : demo001',SL_DOMAIN)
		);

	}

	public function edit_config() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/config-control.php' );
		$control = new Config_Control();
		$control->exec();
	}
	public function edit_configbooking() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/configbooking-control.php' );
		$control = new Configbooking_Control();
		$control->exec();
	}
	public function edit_branch() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/branch-control.php' );
		$control = new Branch_Control();
		$control->exec();
	}
	public function edit_staff() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/staff-control.php' );
		$control = new Staff_Control();
		$control->exec();
	}
	public function edit_position() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/position-control.php' );
		$control = new Position_Control();
		$control->exec();
	}
	public function edit_item() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/item-control.php' );
		$control = new Item_Control();
		$control->exec();
	}
	public function edit_customer() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/customer-control.php' );
		$control = new Customer_Control();
		$control->exec();
	}
	public function edit_sales() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/sales-control.php' );
		$control = new Sales_Control();
		$control->exec();
	}
	public function edit_reservation() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/reservation-control.php' );
		$control = new Reservation_Control();
		$control->exec();
	}
	public function edit_working() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/working-control.php' );
		$control = new Working_Control();
		$control->exec();
	}
	public function edit_base() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/basic-control.php' );
		$control = new Basic_Control();
		$control->exec();
	}
	public function edit_booking() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/booking-control.php' );
		$control = new Booking_Control(@$branch_cd,@$post_id);
		$control->exec();
	}
	public function edit_log() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/log-control.php' );
		$control = new Log_Control();
		$control->exec();
	}
	public function edit_confirm() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/confirm-control.php' );
		$control = new Confirm_Control();
		$control->exec();
	}
	public function edit_download() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/download-control.php' );
		$control = new Download_Control();
		$control->exec();
	}
	public function edit_search() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/search-control.php' );
		$control = new Search_Control();
		$control->exec();
	}
	public function edit_photo() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/photo-control.php' );
		$control = new Photo_Control();
		$control->exec();
	}
	public function edit_mail() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/mail-control.php' );
		$control = new Mail_Control();
		$control->exec();
	}
	public function edit_promotion() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/promotion-control.php' );
		$control = new Promotion_Control();
		$control->exec();
	}
//[Ver1.5.1]
	public function edit_record() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/record-control.php' );
		$control = new Record_Control();
		$control->exec();
	}
	public function edit_category() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/category-control.php' );
		$control = new Category_Control();
		$control->exec();
	}


	public function admin_javascript($hook_suffix) {
		global $plugin_page;
		if ( ! isset( $plugin_page ) || ( substr($plugin_page,0,5)  !=	'salon' )) return;
		wp_enqueue_script( 'jquery');
		wp_enqueue_script('thickbox');
		wp_enqueue_script( 'jquery-ui-datepicker');
		wp_enqueue_script( 'edit', SL_PLUGIN_URL.'js/jquery.jeditable.js',array( 'jquery' ) );
		wp_enqueue_script( 'dataTables', SL_PLUGIN_URL.'js/jquery.dataTables.js',array( 'jquery' ) );
		wp_enqueue_script( 'dataTables_plugin1', SL_PLUGIN_URL.'js/fnReloadAjax.js',array( 'dataTables' ) );
		wp_enqueue_script( 'jsonparse', SL_PLUGIN_URL.'js/jquery.json-2.4.min.js',array( 'jquery' ) );
		wp_enqueue_script( 'dateformat', SL_PLUGIN_URL.'js/jquery.dateFormat.js',array( 'jquery' ) );

		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style('dataTables', SL_PLUGIN_URL.'css/dataTables.css');
		wp_enqueue_style('salon', SL_PLUGIN_URL.'css/salon.css');
		wp_enqueue_style('salon_date', SL_PLUGIN_URL.'css/salon_calendar_datepicker.css');
		if ($plugin_page == 'salon_working' || $plugin_page == 'salon-management') {	//出退勤しかない場合の対処
			wp_enqueue_script( 'dhtmlxscheduler', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler.js',array( 'jquery' ) );
			wp_enqueue_script( 'dhtmlxscheduler_limit', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_limit.js',array( 'dhtmlxscheduler' ) );
			wp_enqueue_script( 'dhtmlxscheduler_collision', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_collision.js',array( 'dhtmlxscheduler' ) );
			wp_enqueue_script( 'dhtmlxscheduler_key_nav', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_key_nav.js',array( 'dhtmlxscheduler' ) );
		}
		if ($plugin_page == 'salon_staff' || $plugin_page == 'salon-maintenace') {
			wp_enqueue_script( 'jquery-ui-sortable');
			wp_enqueue_script( 'colorbox', SL_PLUGIN_URL.'js/jquery.colorbox-min.js',array( 'jquery' ) );
			wp_enqueue_style('colorbox', SL_PLUGIN_URL.'css/colorbox.css');
			wp_enqueue_script( 'dropzone', SL_PLUGIN_URL.'js/dropzone.min.js',array( 'jquery' ) );
			wp_enqueue_style('dropzone', SL_PLUGIN_URL.'css/dropzone.css');
		}
		$configs =  unserialize(get_option( 'SALON_CONFIG'));

		if ( SALON_FOR_THEME
			|| isset($configs['SALON_CONFIG_DO_NEW_FUNCTION']))  {
			if ($plugin_page == 'salon_configbooking' ) {
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style( 'wp-color-picker');

			}
		}

		//YYYY年MM月DD日にするのをやめ
		remove_action( 'admin_enqueue_scripts', 'wp_localize_jquery_ui_datepicker', 1000 );


	}

	public function front_javascript() {
		global $post;
		$isBooking = false;
		if (strpos($post->post_content,"[salon-booking") !== false ) {
			$isBooking = true;
			//固定ページの値を最終的にoptionで保持する
			$this->post_id = $post->ID;
		}
		else if ( strpos($post->post_content,"[salon-confirm") !== false) {
		}
		else if ( strpos($post->post_content,"[salon-staffs") !== false) {
		}
		else if ( strpos($post->post_content,"[salon-menus") !== false) {
		}
		else {
			return;
		}

		if (Salon_Component::isMobile() ) {
			wp_enqueue_script( 'jquery');
			wp_enqueue_script( 'jquery-ui-datepicker');
			wp_enqueue_script( 'dateformat', SL_PLUGIN_URL.'js/jquery.dateFormat.js',array( 'jquery' ) );
			wp_enqueue_style('salon_date', SL_PLUGIN_URL.'css/salon_calendar_datepicker.css');
			wp_enqueue_script( 'salon-mobile', SL_PLUGIN_URL.'js/salon_mobile.js',array( 'jquery' ) ,SL_VERSION);
// 			if (SALON_FOR_REFACTOR) {
// 				wp_enqueue_style('salon-mobile-booking', SL_PLUGIN_URL.'css/salon_mobile_booking.css');
// 			}
// 			else {
// 				wp_enqueue_style('salon-mobile', SL_PLUGIN_URL.'css/salon_mobile.css');
// 			}
			wp_enqueue_style('salon-mobile-booking', SL_PLUGIN_URL.'css/salon_mobile_booking.css');
			//YYYY年MM月DD日にするのをやめ
			remove_action( 'wp_enqueue_scripts', 'wp_localize_jquery_ui_datepicker', 1000 );
		}
		else {
			wp_enqueue_script( 'jquery');
			wp_enqueue_script( 'colorbox', SL_PLUGIN_URL.'js/jquery.colorbox-min.js',array( 'jquery' ) );
			wp_enqueue_style('colorbox', SL_PLUGIN_URL.'css/colorbox.css');
// 			if (SALON_FOR_REFACTOR) {
// 				wp_enqueue_style('salon-booking', SL_PLUGIN_URL.'css/salon_booking.css');
// 			}
// 			else {
// 				wp_enqueue_style('salon', SL_PLUGIN_URL.'css/salon.css');
// 			}
			wp_enqueue_style('salon-booking', SL_PLUGIN_URL.'css/salon_booking.css');
			if ($isBooking) {
				wp_enqueue_script( 'dhtmlxscheduler', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler.js',array( 'jquery' ) );
				wp_enqueue_script( 'dhtmlxscheduler_limit', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_limit.js',array( 'dhtmlxscheduler' ) );
				wp_enqueue_script( 'dhtmlxscheduler_timeline', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_timeline.js',array( 'dhtmlxscheduler' ) );
				wp_enqueue_script( 'dhtmlxscheduler_collision', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_collision.js',array( 'dhtmlxscheduler' ) );
			}
		}
	}

	public function getOnlyBranchCd() {
		global $wpdb;
		$branch_cd = 1;
		$sql = 'SELECT branch_cd FROM '.$wpdb->prefix
		.'salon_branch WHERE delete_flg <> '.Salon_Reservation_Status::DELETED;

		if ($wpdb->query($sql) === false ) {
			//エラーは無視
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
			$branch_cd = $result[0]['branch_cd'];
		}
		return $branch_cd;
	}


	public function salon_booking_shortcode($atts) {
		$branch_cd = $this->getOnlyBranchCd();
		extract(shortcode_atts(array('branch_cd' => $branch_cd), $atts));
		require_once(SL_PLUGIN_SRC_DIR.'/control/booking-control.php');
		$control = new Booking_Control(@$branch_cd,$this->post_id);
		$control->exec();

	}


	public function salon_booking_confirm($atts) {
		require_once(SL_PLUGIN_SRC_DIR.'/control/confirm-control.php');
		$control = new Confirm_Control();
		$control->exec();

	}

	public function salon_booking_check_config($atts) {
		require_once(SL_PLUGIN_SRC_DIR.'/control/checkconfig-control.php');
		$control = new Checkconfig_Control();
		$control->exec();

	}

	public function salon_booking_staff($atts) {
		$branch_cd = $this->getOnlyBranchCd();
		extract(shortcode_atts(array('branch_cd' => $branch_cd), $atts));
		require_once(SL_PLUGIN_SRC_DIR.'/control/stafflist-control.php');
		$control = new Stafflist_Control(@$branch_cd);
		$control->exec();

	}

	public function salon_booking_menu($atts) {
		$branch_cd = $this->getOnlyBranchCd();
		extract(shortcode_atts(array('branch_cd' => $branch_cd), $atts));
		require_once(SL_PLUGIN_SRC_DIR.'/control/menulist-control.php');
		$control = new Menulist_Control(@$branch_cd);
		$control->exec();

	}

	function _isExixtColumn($table_name ,$column_name){
		global $wpdb;
		$sql = "show columns from ".$wpdb->prefix.$table_name;
		$columns = $wpdb->get_results($sql,ARRAY_A);
		foreach ($columns as $k1 => $d1 ) {
			if ($d1['Field'] == $column_name ) return true;
		}
		return false;
	}

	function salon_install(){

		if (!get_option('salon_confirm_page_id') ) {
			$post = array(
				'ID' => '' 	//[ <投稿 ID> ] // 既存の投稿を更新する場合。
				,'menu_order' => 999 //[ <順序値> ] // 追加する投稿が固定ページの場合、ページの並び順を番号で指定できます。
				,'comment_status' => 'closed'	//[ 'closed' | 'open' ] // 'closed' はコメントを閉じます。
				,'ping_status' => 'closed' //[ 'closed' | 'open' ] // 'closed' はピンバック／トラックバックをオフにします。
				,'pinged' => '' //[ ? ] // ピンバック済。
				,'post_author' => '' //[ <user ID> ] // 作成者のユーザー ID。
				,'post_content' => '[salon-confirm]' //[ <投稿の本文> ] // 投稿の全文。
				,'post_date' => date_i18n('Y-m-d H:i:s') //[ Y-m-d H:i:s ] // 投稿の作成日時。
				,'post_date_gmt' => gmdate('Y-m-d H:i:s') //[ Y-m-d H:i:s ] // 投稿の作成日時（GMT）。
				,'post_excerpt' => '' //[ <抜粋> ] // 投稿の抜粋。
				,'post_name' => ''	//[ <スラッグ名> ] // 投稿スラッグ。
				,'post_parent' => 0	//[ <投稿 ID> ] // 親投稿の ID。
				,'post_password' => '' //[ <投稿パスワード> ] // 投稿の閲覧時にパスワードが必要になります。
				,'post_status' => 'publish' //[ 'draft' | 'publish' | 'pending'| 'future' ] // 公開ステータス。
				,'post_title' => __('Reservation Confirm',SL_DOMAIN)	//[ <タイトル> ] // 投稿のタイトル。
				,'post_type' => 'page' //[ 'post' | 'page' ] // 投稿タイプ名。
				,'tags_input' => '' //[ '<タグ>, <タグ>, <...>' ] // 投稿タグ。
				,'to_ping' => ''	//[ ? ] //?
			);

			$id = wp_insert_post( $post );
			update_option('salon_confirm_page_id', $id);
		}

		global $wpdb;


		$charset_collate = '';

		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";


		$current = date_i18n('Y-m-d H:i:s');




		//ver 1.2.1 From
		$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_photo (
			`photo_id`		INT NOT NULL AUTO_INCREMENT,
			`photo_name`		varchar(255) default NULL,
			`photo_path`		varchar(255) default NULL,
			`photo_resize_path`		varchar(255) default NULL,
			`width`			INT NOT NULL default '0',
			`height`			INT NOT NULL default '0',
			`delete_flg` tinyint NOT NULL default '0',
			`insert_time` DATETIME,
			`update_time` DATETIME,
			UNIQUE  (`photo_id`)
		 ) ".$charset_collate);
		//
		//ver 1.4.8 From
		$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_promotion (
			`promotion_cd`	INT not null AUTO_INCREMENT,
			`branch_cd`		INT,
			`set_code`		VARCHAR(20),
			`description`	TEXT,
			`valid_from`	DATETIME default '0000-00-00 00:00:00' ,
			`valid_to`	DATETIME default '2099-12-30 00:00:00' ,
			`usable_patern_cd`		INT default 1,
			`usable_data`	TEXT,
			`times`			INT default 1,
			`discount_patern_cd`		INT default 1,
			`discount`		INT default 0,
			`remark`		TEXT,
			`memo`			TEXT,
			`notes`			TEXT,
			`delete_flg`	INT default 0,
			`insert_time`	DATETIME,
			`update_time`	DATETIME,
			PRIMARY KEY (`promotion_cd`)
		) ".$charset_collate);

//[Ver1.5.1]
		$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_category (
			`category_cd`	INT not null AUTO_INCREMENT,
			`category_name`	TEXT,
			`category_patern`	INT ,
			`category_values`	TEXT ,
			`target_table_id`	INT default 1 ,
			`display_sequence`		INT default 0,
			`delete_flg`	INT default 0,
			`insert_time`	DATETIME,
			`update_time`	DATETIME,
			PRIMARY KEY (`category_cd`)
		) ".$charset_collate);

		$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_customer_record (
			`reservation_cd`	INT not null ,
			`customer_cd`	INT not null ,
			`record`		TEXT,
			`delete_flg`	INT default 0,
			`insert_time`	DATETIME,
			`update_time`	DATETIME,
			PRIMARY KEY (`reservation_cd`),
			INDEX idx_customer(`customer_cd`)
		) ".$charset_collate);


		//ver 1.5.1
		$sql = 'SELECT COUNT(*) as cnt FROM '.$wpdb->prefix.'salon_category ';
		if ($wpdb->query($sql) === false ) {
			//エラーは無視
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
			if ($result[0]['cnt'] == 0 ) {
				//1がOPTION、2がチェック、3がテキスト、4がセレクト
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_category (category_cd,category_name,category_patern,category_values,display_sequence,insert_time,update_time) VALUES (1,%s,1,%s,1,%s,%s)",__('Hair Condition',SL_DOMAIN),__('Normal,Dry,Oily,opt4,opt5,opt6,opt7,opt8,opt9,opt10',SL_DOMAIN),$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_category (category_cd,category_name,category_patern,category_values,display_sequence,insert_time,update_time) VALUES (2,%s,2,%s,2,%s,%s)",__('Hair spa',SL_DOMAIN),__('Check here1,Check here2,Check here3',SL_DOMAIN),$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_category (category_cd,category_name,category_patern,category_values,display_sequence,insert_time,update_time) VALUES (3,%s,3,null,3,%s,%s)",__('Color',SL_DOMAIN),$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_category (category_cd,category_name,category_patern,category_values,display_sequence,insert_time,update_time) VALUES (4,%s,4,%s,4,%s,%s)",__('Scalp Condition',SL_DOMAIN),__('1,2,3,4,5,6,7,8,9,10',SL_DOMAIN),$current,$current));
			}
		}

		if (get_option('salon_installed') ) {

			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."salon_staff SET photo = null,update_time = %s WHERE photo LIKE %s ",$current,'%:%'));

			//ver 1.3.4
			$old = '"'.preg_replace("/[hH][tT][tT][pP].?:/","",SALON_UPLOAD_URL_OLD).'"';
			$new = '"'.preg_replace("/[hH][tT][tT][pP].?:/","",rtrim(SALON_UPLOAD_URL,'/')).'"';




			$sql  = "UPDATE ".$wpdb->prefix."salon_photo SET photo_path=REPLACE(photo_path,".$old.",".$new."), photo_resize_path=REPLACE(photo_resize_path,".$old.",".$new.")";
			$wpdb->query($sql);


			foreach(glob(SALON_UPLOAD_DIR_OLD.'/'.'{*.jpg,*.gif,*.png}', GLOB_BRACE) as $image) {
				@rename($image,rtrim(SALON_UPLOAD_DIR,'/').'/'.basename($image));
			}

			//ver 1.3.1
			if (! $this->_isExixtColumn("salon_item","display_sequence") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_item ADD `display_sequence` INT NOT NULL DEFAULT '0' AFTER `notes` ");
				//IDと同じ値を設定しとく
				$wpdb->query("UPDATE ".$wpdb->prefix."salon_item SET  display_sequence = item_cd ");

			}
			//ver 1.3.2
			if (! $this->_isExixtColumn("salon_staff","display_sequence") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_staff ADD `display_sequence` INT NOT NULL DEFAULT '0' AFTER `duplicate_cnt` ");
				//IDと同じ値を設定しとく
				$wpdb->query("UPDATE ".$wpdb->prefix."salon_staff SET  display_sequence = staff_cd ");

			}
			//ver 1.4.1
			//途中でカラムをつくれない場合を考慮して項目毎にチェックする
			if (! $this->_isExixtColumn("salon_item","exp_from") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_item ADD `exp_from` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `display_sequence` ");
			}
			if (! $this->_isExixtColumn("salon_item","exp_to") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_item ADD `exp_to` datetime NOT NULL DEFAULT '2099-12-31 00:00:00' AFTER `exp_from` ");
			}
			if (! $this->_isExixtColumn("salon_item","all_flg") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_item ADD `all_flg` int NOT NULL DEFAULT 1 AFTER `exp_to` ");
			}
			if (! $this->_isExixtColumn("salon_staff","in_items") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_staff ADD `in_items` text AFTER `display_sequence` ");
				//有効なメニューを設定しとく
				$this->_set_items();

			}
// 			//[2014/08/01]Ver 1.4.6
//			//[2016/10/01]不要なので削除
// 			$result =  unserialize(get_option( 'SALON_CONFIG'));
// 			//<br>を改行へ、%s、X-SHOPなどを変換
// 			unset($result['SALON_CONFIG_SEND_MAIL_TEXT']);
// 			unset($result['SALON_CONFIG_SEND_MAIL_TEXT_USER']);
// 			update_option('SALON_CONFIG',serialize($result));
			//[2014/08/08]Ver 1.4.8
			if (! $this->_isExixtColumn("salon_customer","rank_patern_cd") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_customer ADD `rank_patern_cd` INT default 1 AFTER `is_send_mail` ");
			}
			if (! $this->_isExixtColumn("salon_reservation","coupon") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_reservation ADD `coupon` varchar(2000)  default null AFTER `item_cds` ");
			}
			if (! $this->_isExixtColumn("salon_sales","coupon") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_sales ADD `coupon` varchar(2000)  default null AFTER `item_cds` ");
			}
			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."salon_position SET role = concat(role,',edit_promotion'),update_time = %s WHERE position_cd in (1,2,3,7) or wp_role = 'administrator' ",$current));
			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."salon_position SET role = concat(role,',edit_use_promotion'),update_time = %s WHERE position_cd in (4,5,6) or wp_role = 'administrator' ",$current));
			//[2014/09/12]Ver 1.4.9
			$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_staff ALTER COLUMN `leaved_day` SET DEFAULT '2099-12-28 00:01:00' ");
			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."salon_staff SET leaved_day = '2099-12-28 00:02:00',update_time = %s WHERE leaved_day = '0000-00-00 00:00:00' OR leaved_day IS NULL ",$current));
//[Ver1.5.1]
			if (! $this->_isExixtColumn("salon_customer","birthday") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_customer ADD `birthday` DATETIME default null AFTER `rank_patern_cd` ");
				//変更のタイミングが一緒なのでここで
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_position CHANGE COLUMN role role VARCHAR(600) ");

			}
			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."salon_position SET role = concat(role,',edit_record,edit_category'),update_time = %s WHERE position_cd in (1,2,3,4,7) or wp_role = 'administrator' ",$current));

			$this->_update_spDate();

			$this->_move_log();

		}
		else {
			//status 会員の場合は、Icomplete
			//       会員でない場合は、INIT→メールでactivate→complete
			//[TODO]nameとemailは最後は落とすか？。会員登録しない人のために残すか？
			//[TODO]済　item_cdは複数項目入っているので項目名を変更する。
			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_reservation (
								`reservation_cd`	INT not null  AUTO_INCREMENT,
								`branch_cd`		INT,
								`staff_cd`		INT,
								`user_login`		VARCHAR(60) default null,
								`non_regist_name`			VARCHAR(40),
								`non_regist_email`			VARCHAR(100),
								`non_regist_tel`		char(20) default null,
								`non_regist_activate_key`	VARCHAR(8),
								`time_from`		DATETIME,
								`time_to`		DATETIME,
								`item_cds`		VARCHAR(50),
								`coupon`		VARCHAR(2000) default null,
								`status`		INT,
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`reservation_cd`)
							) ".$charset_collate);

			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_sales (
								`reservation_cd`	INT not null ,
								`branch_cd`		INT,
								`staff_cd`		INT,
								`customer_cd`	INT,
								`time_from`		DATETIME,
								`time_to`		DATETIME,
								`item_cds`		VARCHAR(50),
								`coupon`		VARCHAR(2000) default null,
								`status`		INT,
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`price`		INT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`reservation_cd`)
							) ".$charset_collate);

			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_customer (
								`customer_cd`	INT not null AUTO_INCREMENT,
								`ID`		BIGINT,
								`user_login`		 	varchar(60) default null,
								`branch_cd`		INT,
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`photo`			TEXT default null,
								`is_send_mail`	INT default 1,
								`rank_patern_cd`			INT default 1,
								`birthday`		DATETIME default null,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`customer_cd`)
							) ".$charset_collate);


			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_branch (
								`branch_cd`		INT not null AUTO_INCREMENT,
								`name`			VARCHAR(40),
								`zip`			char(20),
								`address`			TEXT,
								`tel`			char(20),
								`mail`			char(50),
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`open_time`			char(4),
								`close_time`			char(4),
								`closed`				char(15),
								`sp_dates`			TEXT,
								`time_step`	INT default 15,
								`duplicate_cnt`	INT default 1,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`branch_cd`)
							) ".$charset_collate);

			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_staff (
								`staff_cd`		INT not null AUTO_INCREMENT,
								`user_login`		 	varchar(60) default null,
								`branch_cd`		INT,
								`position_cd`		INT,
								`day_off`		char(15),
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`employed_day`	DATETIME default null,
								`leaved_day`	DATETIME default '2099-12-28 00:00:00',
								`photo`			TEXT default null,
								`duplicate_cnt`	INT default 0,
								`display_sequence`		INT default 0,
								`in_items`			TEXT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`staff_cd`)
							) ".$charset_collate);


			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_working (
								`staff_cd`		INT not null,
								`in_time`	DATETIME,
								`out_time`	DATETIME,
								`working_cds`	VARCHAR(50),
								`remark`		TEXT,
								`memo`			TEXT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`staff_cd`,`in_time`)
							) ".$charset_collate);


			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_position (
								`position_cd`		INT not null AUTO_INCREMENT,
								`name`			VARCHAR(40),
								`wp_role`			VARCHAR(40),
								`role`			VARCHAR(600),
								`remark`		TEXT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`position_cd`)
							) ".$charset_collate);

			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_item (
								`item_cd`		INT not null AUTO_INCREMENT,
								`name`			TEXT,
								`branch_cd`		INT,
								`short_name`	TEXT,
								`minute`		INT,
								`price`			INT,
								`photo`			TEXT default null,
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`display_sequence`		INT default 0,
								`exp_from`	DATETIME default '0000-00-00 00:00:00' ,
								`exp_to`	DATETIME default '2099-12-30 00:00:00' ,
								`all_flg`		INT default 1,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
								PRIMARY KEY (`item_cd`)
							) ".$charset_collate);

			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_log (
								`no`		INT not null AUTO_INCREMENT,
								`sql`			TEXT,
								`remark`		TEXT,
								`insert_time`	DATETIME,
							  PRIMARY KEY  (`no`)
							) ".$charset_collate);


			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_branch VALUES (".Salon_Default::BRANCH_CD.",'".__('SAMPLE SHOP NAME',SL_DOMAIN)."','100-0001','".__('SAMPLE SHOOP ADDRESS',SL_DOMAIN)."','223456789','mail@1.com','".__('SHOP REMARK',SL_DOMAIN)."','1000,1900','','1000','1900','2','',30,1,0,%s,%s);",$current,$current));
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_item VALUES (1,'".__('SAMPLE MENU CUT',SL_DOMAIN)."',".Salon_Default::BRANCH_CD.",'".__('SAMPLE MENU CUT',SL_DOMAIN)."',".__('30,50',SL_DOMAIN).",null,null,null,null,1,'0000-00-00 00:00:00','2099-12-30 00:00:00',1,0,%s,%s);",$current,$current));
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_item VALUES (2,'".__('SAMPLE MENU PERM',SL_DOMAIN)."',".Salon_Default::BRANCH_CD.",'".__('SAMPLE MENU PERM',SL_DOMAIN)."',".__('90,100',SL_DOMAIN).",null,null,null,null,2,'0000-00-00 00:00:00','2099-12-30 00:00:00',1,0,%s,%s);",$current,$current));
			//インストールしたユーザを割り当てる
			$current_user = wp_get_current_user();

			$zip = get_user_option('zip',$current_user->ID);
			if (empty($zip)) update_user_meta( $current_user->ID, 'zip',__('zip',SL_DOMAIN));
			$address = get_user_option('address',$current_user->ID);
			if (empty($address)) update_user_meta( $current_user->ID, 'address',__('address',SL_DOMAIN));
			$tel = get_user_option('tel',$current_user->ID);
			if (empty($tel)) update_user_meta( $current_user->ID, 'tel',__('999-999-999',SL_DOMAIN));
			$mobile = get_user_option('mobile',$current_user->ID);
			if (empty($mobile)) update_user_meta( $current_user->ID, 'mobile',__('999-999-999',SL_DOMAIN));


			$staff_cd = $wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_staff (user_login,branch_cd,position_cd,remark,memo,notes,in_items,insert_time,update_time) VALUES ('".$current_user->user_login."',".Salon_Default::BRANCH_CD.",7,'remark','memo','notes','1,2',%s,%s);",$current,$current));
			//ver 1.4.1
			$this->_set_items();
			//
			update_option('salon_initial_user', $staff_cd);
			//
			if (defined ( 'SALON_DEMO' ) && SALON_DEMO   ) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (1,'".__('PRESIDENT',SL_DOMAIN)."','contributor','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all,edit_promotion,edit_category','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (2,'".__('DIRECTER',SL_DOMAIN)."','contributor','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all,edit_promotion,edit_category','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (3,'".__('SHOP MANAGER',SL_DOMAIN)."','contributor','edit_customer,edit_item,edit_staff,edit_reservation,edit_sales,edit_working,edit_base,edit_booking,edit_working_all,edit_promotion,edit_category','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (4,'".__('CHIEF',SL_DOMAIN)."','contributor','edit_customer,edit_reservation,edit_sales,edit_working,edit_booking,edit_category','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (5,'".__('STAFF',SL_DOMAIN)."','contributor','edit_reservation,edit_sales,edit_working,edit_booking,edit_category','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (6,'".__('TEMPORARY',SL_DOMAIN)."','contributor','edit_reservation,edit_sales','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (".Salon_Position::MAINTENANCE.",'".__('MAINTENANCE',SL_DOMAIN)."','administrator','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all,edit_promotion,edit_record,edit_category','".__('this data can not delete or update',SL_DOMAIN)."',0,%s,%s);",$current,$current));
			}
			else {
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (1,'".__('PRESIDENT',SL_DOMAIN)."','editor','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all,edit_promotion,edit_record,edit_category','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (2,'".__('DIRECTER',SL_DOMAIN)."','editor','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all,edit_promotion,edit_record,edit_category','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (3,'".__('SHOP MANAGER',SL_DOMAIN)."','editor','edit_customer,edit_item,edit_staff,edit_reservation,edit_sales,edit_working,edit_base,edit_booking,edit_working_all,edit_promotion,edit_record,edit_category','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (4,'".__('CHIEF',SL_DOMAIN)."','editor','edit_customer,edit_reservation,edit_sales,edit_working,edit_booking,edit_use_promotion,edit_record,edit_category','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (5,'".__('STAFF',SL_DOMAIN)."','author','edit_reservation,edit_sales,edit_working,edit_booking,edit_use_promotion,edit_record,edit_category','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (6,'".__('TEMPORARY',SL_DOMAIN)."','contributor','edit_reservation,edit_sales,edit_use_promotion','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (".Salon_Position::MAINTENANCE.",'".__('MAINTENANCE',SL_DOMAIN)."','administrator','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all,edit_log,edit_promotion,edit_use_promotion,edit_record,edit_category','".__('this data can not delete or update',SL_DOMAIN)."',0,%s,%s);",$current,$current));
			}


			$lang = get_locale();
			if ( file_exists(SL_PLUGIN_DIR.'/languages/holiday-'.$lang.'.php') )require_once(SL_PLUGIN_DIR.'/languages/holiday-'.$lang.'.php');
			else require_once(SL_PLUGIN_DIR.'/languages/holiday.php');


			update_option('salon_holiday', serialize($holiday));

			update_option('salon_installed', 1);
		}
		wp_schedule_event( ceil( time() / 86400 ) * 86400 + ( 1 - get_option( 'gmt_offset' ) ) * 3600, 'daily', 'salon_daily_event' );


	}

	//ver 1.4.1
	private function _set_items (){
		global $wpdb;
		$sql = 'SELECT branch_cd,item_cd FROM '.$wpdb->prefix.'salon_item WHERE delete_flg <> '.Salon_Reservation_Status::DELETED.' ORDER BY branch_cd,item_cd ';
		if ($wpdb->query($sql) === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if (count($result) > 0 ) {
			$save_branch_cd = $result[0]['branch_cd'];
			$tmp_item_cds = array();
			$set_possible_item = array();
			foreach ($result as $k1 => $d1 ) {
				if ($save_branch_cd != $d1['branch_cd'] ) {
					$set_possible_item[$save_branch_cd] = implode(',', $tmp_item_cds);
					$tmp_item_cds = array();
				}
				$tmp_item_cds[] = $d1['item_cd'];
				$save_branch_cd = $d1['branch_cd'];
			}
			$set_possible_item[$save_branch_cd] = implode(',', $tmp_item_cds);

			$sql = "UPDATE ".$wpdb->prefix."salon_staff SET  in_items = %s WHERE branch_cd = %d ";

			foreach($set_possible_item as $k1 => $d1) {
				$result = $wpdb->query($wpdb->prepare($sql,$d1,$k1));
				if ($result === false ) {
					error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
				}
			}

		}
	}
	//ver 1.4.1　

	//ver 1.6.5
	public function _update_spDate() {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'salon_branch WHERE delete_flg <> '.Salon_Reservation_Status::DELETED;
		if ($wpdb->query($sql) === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if (count($result) > 0 ) {
			foreach ($result as $k1 => $d1 ) {
				$sp_dates = unserialize($d1['sp_dates']);
				//$k2 = YYYY
				if (count($sp_dates) > 0  && is_array($sp_dates) ) {
					foreach ($sp_dates as $k2 => $d2) {
						//$k3 = YYYYMMDD
						foreach($d2 as $k3 => $d3) {
							if (!is_array($d3)) {
								$saveStatus = $d3;
								unset($sp_dates[$k2][$k3]);
								$sp_dates[$k2][$k3]['status'] = $saveStatus;
								$sp_dates[$k2][$k3]['fromHHMM'] = $d1['open_time'];
								$sp_dates[$k2][$k3]['toHHMM'] = $d1['close_time'];
							}
						}
					}
					$sql = "UPDATE ".$wpdb->prefix."salon_branch SET sp_dates = %s WHERE branch_cd = %d ";
					$setSpdates = serialize($sp_dates);
					$result = $wpdb->query($wpdb->prepare($sql,$setSpdates,$d1['branch_cd']));
					if ($result === false ) {
						error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, SALON_UPLOAD_DIR.date('Y').'.txt');
					}
				}
			}
		}
	}
	//ver 1.6.5

	//ver 1.6.11
	public function _move_log() {
		//2013-2017
		$oldLogDirctory = ABSPATH ."/";
		for ($year = 2013; $year <= 2017 ; $year++) {
			$target = $oldLogDirctory.$year.".txt";
			if (is_readable($target)) {
				//中身を読んで内容を一応確認
				$section = file_get_contents($target, NULL, NULL, 0, 1000);
				if (strpos($section, "daily_action start") !== false) {
					@rename($target, SALON_UPLOAD_DIR.$year.".txt");
				}
			}
			//デバッグファイルも存在すれば移動しとく
			$files = @glob($oldLogDirctory."debug".$year."*.txt");
			if ($files !== false )  {
				if (count($files) > 0) {
					foreach($files as $name) {
						$onlyName = explode("/",$name);
						$idx = count($onlyName) -1;
						@rename($name , SALON_UPLOAD_DIR.$onlyName[$idx]);
					}
				}
			}
		}
	}

	public function salon_deactivation() {
		wp_clear_scheduled_hook('salon_daily_event');
	}



}


?>