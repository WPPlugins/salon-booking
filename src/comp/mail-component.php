<?php

class Mail_Component {

	private $version = '1.0';

	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}


	public function editTableData () {
		$set_data['SALON_CONFIG_SEND_MAIL_TEXT'] = stripslashes($_POST['config_mail_text']);
		$set_data['SALON_CONFIG_SEND_MAIL_TEXT_USER'] = stripslashes($_POST['config_mail_text_user']);
		$set_data['SALON_CONFIG_SEND_MAIL_SUBJECT'] = stripslashes($_POST['config_mail_subject']);
		$set_data['SALON_CONFIG_SEND_MAIL_SUBJECT_USER'] = stripslashes($_POST['config_mail_subject_user']);
		$edit_from = stripslashes($_POST['config_mail_from']);
		if (strpos($edit_from,"<") !== false ) {
			$edit_from_array = explode('<',$edit_from);
			$edit_from = trim($edit_from_array[0])." <".trim($edit_from_array[1]);
		}
		$set_data['SALON_CONFIG_SEND_MAIL_FROM'] = $edit_from;
		$set_data['SALON_CONFIG_SEND_MAIL_RETURN_PATH'] = stripslashes($_POST['config_mail_returnPath']);
		//[2014/11/01]Ver1.5.1
		$set_data['SALON_CONFIG_SEND_MAIL_TEXT_INFORMATION'] = stripslashes($_POST['config_mail_text_information']);
		$set_data['SALON_CONFIG_SEND_MAIL_SUBJECT_INFORMATION'] = stripslashes($_POST['config_mail_subject_information']);
		$set_data['SALON_CONFIG_SEND_MAIL_BCC'] = stripslashes($_POST['config_mail_bcc']);
		//Ver1.6.1
		$set_data['SALON_CONFIG_SEND_MAIL_TEXT_COMPLETED'] = stripslashes($_POST['config_mail_text_completed']);
		$set_data['SALON_CONFIG_SEND_MAIL_TEXT_ACCEPTED'] = stripslashes($_POST['config_mail_text_accepted']);
		$set_data['SALON_CONFIG_SEND_MAIL_TEXT_CANCELED'] = stripslashes($_POST['config_mail_text_canceled']);
		$set_data['SALON_CONFIG_SEND_MAIL_SUBJECT_COMPLETED'] = stripslashes($_POST['config_mail_subject_completed']);
		$set_data['SALON_CONFIG_SEND_MAIL_SUBJECT_ACCEPTED'] = stripslashes($_POST['config_mail_subject_accepted']);
		$set_data['SALON_CONFIG_SEND_MAIL_SUBJECT_CANCELED'] = stripslashes($_POST['config_mail_subject_canceled']);

		return $set_data;

	}


}