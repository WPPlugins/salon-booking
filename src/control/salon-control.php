<?php
require_once(SL_PLUGIN_SRC_DIR . 'comp/salon-component.php');


abstract class Salon_Control  {


	protected $is_multi_branch = true;
	protected $is_use_session = true;
	private $respons_type = Response_Type::JASON;
	private $is_show_detail_msg = false;


	public function __construct() {
//[TODO]最後にけす
//ini_set( 'display_errors', 0 );
//		set_error_handler( array( &$this, '_error_handler' ), E_ALL );
		set_exception_handler(  array( &$this, '_error_action') );
	}




	public function set_config ($config) {
		$this->is_show_detail_msg = (Salon_Config::DETAIL_MSG_OK == $config[ 'SALON_CONFIG_SHOW_DETAIL_MSG' ]);
		$this->is_use_session = (Salon_Config::USE_SESSION == $config[ 'SALON_CONFIG_USE_SESSION_ID' ]);
	}

	public function set_response_type ( $response_type) {
		$this->respons_type = $response_type;
	}


	public function exec() {
		try {
			$this->_checkRole();
			$this->do_action();
		}
		//以下はUNITTEST用
		catch ( WPAjaxDieStopException $e ) {
			throw new WPAjaxDieStopException( '0' );
		} catch ( WPAjaxDieContinueException $e ) {
			throw new WPAjaxDieContinueException( '0' );
		} catch (Exception $e) {
			$this->_error_action($e);
		}
	}

	public function do_require($class_name,$type,$permits,$refactor = "") {
		if (! in_array($class_name,$permits) )  throw new Exception(__('invalid request',SL_DOMAIN));

		$path = SL_PLUGIN_SRC_DIR.$type.'/'.strtolower(str_replace('_','-',$class_name) ).$refactor.'.php';
		if ( file_exists($path)) {
			require_once($path);
		}
		else {
		   throw new Exception(__('no class file',SL_DOMAIN));
		}
		if (!class_exists($class_name)) {
		   throw new Exception(__('no class ',SL_DOMAIN));
		}

	}

	abstract  function do_action();

	private function _error_action($e) {
		$this->_error_handler($e->getCode(),$e->getMessage(),$e->getFile(),$e->getLine(),$e->getTraceAsString());
	}

	public function _error_handler ( $errno, $errstr, $errfile, $errline, $errcontext ) {
//defineでfalseにするとエラーがでなくなるのでやめ
//		if (error_reporting() === 0) return;
		$detail_msg = '';
		if ($this->is_show_detail_msg ) $detail_msg ="\n".$errfile.$errline."\n".$errcontext;
		if ($this->respons_type == Response_Type::JASON || $this->respons_type == Response_Type::JASON_406_RETURN) {
			if ($this->respons_type == Response_Type::JASON_406_RETURN ) {
				header('HTTP/1.1 406 Not Acceptable');
			}
			$msg['status'] = 'Error';
			if (empty($errno) ) $msg['message'] = Salon_Component::getMsg('E007',$errstr.$detail_msg);
			else $msg['message'] = $errstr.$detail_msg.'('.$errno.')';
			echo json_encode($msg);
		}
		elseif ($this->respons_type == Response_Type::HTML ) {
			$msg =  nl2br($errstr.$detail_msg);
			echo '<div id="sl_error_display"><h2>'.$msg.'</h2></div>';
		}
		elseif ($this->respons_type == Response_Type::XML ) {
			if (empty($errno) ) $msg =  $errstr.$detail_msg;
			else $msg =  $errstr.' '.$detail_msg.'('.$errno.')';
			$msg = str_replace("'",'"',$msg);
			if (empty($errno) ) $msg = Salon_Component::getMsg('E007',$msg);
			header('Content-type: text/xml');
			echo '<?xml version="1.0" encoding="UTF-8" ?>';
			echo "<data><action type='error' sid='".$_POST['id']."' tid='".$_POST['id']."' name='error' message='".$msg."' func='".$_POST['type']."' ></action><userdata name='result'>error</userdata><userdata name='message'>".$msg."</userdata></data>";
		}
		wp_die();
	}

	private function _checkRole() {
		Salon_Component::checkRole(get_class($this));
	}


}		//class


