<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Mail_Page extends Salon_Page {

	private $set_items = null;




	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->set_items = array(
				'send_mail_text_on_mail'
				,'regist_mail_text_on_mail'
				,'mail_from_on_mail'
				,'mail_returnPath_on_mail'
				,'target_mail_patern'
				,'send_mail_subject'
				,'regist_mail_subject'
				,'information_mail_text_on_mail'
				,'information_mail_subject'
 		,'send_mail_text_completed_on_mail','send_mail_subject_completed'
		,'send_mail_text_accepted_on_mail','send_mail_subject_accepted'
		,'send_mail_text_canceled_on_mail','send_mail_subject_canceled'
				,'mail_bcc');
	}


	public function show_page() {
?>

	<script type="text/javascript" charset="utf-8">

		var $j = jQuery;
		<?php parent::echoClientItem($this->set_items); ?>
		$j(document).ready(function() {
			$j("#salon_button_div input[type=button]").addClass("sl_button");
			<?php parent::echoSetItemLabel(false); ?>
			for(index in check_items) {
				if (check_items[index]) {
					var diff = 0;
					var id = check_items[index]["id"];
					$j("#"+id+"_lbl").children(".small").text(check_items[index]["tips"]);
					if ($j("#"+id)[0].tagName.toUpperCase() == "TEXTAREA" ) diff = 5;
					else {
						if ( $j("#"+id).parent().hasClass("config_item_wrap") ) {
							diff = $j("#"+id+"_lbl").outerHeight(true) - $j("#"+id).parent().outerHeight(true);
						}
						else {
							diff = $j("#"+id+"_lbl").outerHeight(true) - $j("#"+id).outerHeight(true);
						}
					}
					if (diff > 0 ) {
						diff += <?php echo parent::INPUT_BOTTOM_MARGIN; ?>+5;
						$j("#"+id).attr("style","margin-bottom: "+diff+"px;");
						$j("#"+id+"_lbl").children(".small").attr("style","text-align:left;");
					}
				}
			}

            $j("#button_update").click(function()	{
				fnClickUpdate();
			});
            $j("#target_mail_patern").change(function()	{

				$j(".sl_mail_wrap").hide();
				$j("#sl_mail_warp_"+$j(this).val()).show();

				$j("#sl_mail_wrap_bcc").hide();
				if ($j(this).val() == "information" ) {
					$j("#sl_mail_wrap_bcc").show();
				}

			});

			$j("#send_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n",'"'), array('\n','\n','\n','\"') , $this->config_datas['SALON_CONFIG_SEND_MAIL_TEXT']); ?>");
			$j("#regist_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n",'"'), array('\n','\n','\n','\"'), $this->config_datas['SALON_CONFIG_SEND_MAIL_TEXT_USER']); ?>");
			$j("#information_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n",'"'), array('\n','\n','\n','\"'), $this->config_datas['SALON_CONFIG_SEND_MAIL_TEXT_INFORMATION']); ?>");

			$j("#send_mail_subject").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_SUBJECT']; ?>");
			$j("#regist_mail_subject").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_SUBJECT_USER']; ?>");
			$j("#information_mail_subject").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_SUBJECT_INFORMATION']; ?>");

			$j("#mail_from").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_FROM']; ?>");
			$j("#mail_returnPath").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_RETURN_PATH']; ?>");

			$j("#mail_bcc").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_BCC']; ?>");

			$j("#sl_send_mail_text_completed").val("<?php echo str_replace(array("\r\n","\r","\n",'"'), array('\n','\n','\n','\"'), $this->config_datas['SALON_CONFIG_SEND_MAIL_TEXT_COMPLETED']); ?>");
			$j("#sl_send_mail_text_accepted").val("<?php echo str_replace(array("\r\n","\r","\n",'"'), array('\n','\n','\n','\"'), $this->config_datas['SALON_CONFIG_SEND_MAIL_TEXT_ACCEPTED']); ?>");
			$j("#sl_send_mail_text_canceled").val("<?php echo str_replace(array("\r\n","\r","\n",'"'), array('\n','\n','\n','\"'), $this->config_datas['SALON_CONFIG_SEND_MAIL_TEXT_CANCELED']); ?>");

			$j("#sl_send_mail_subject_completed").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_SUBJECT_COMPLETED']; ?>");
			$j("#sl_send_mail_subject_accepted").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_SUBJECT_ACCEPTED']; ?>");
			$j("#sl_send_mail_subject_canceled").val("<?php echo $this->config_datas['SALON_CONFIG_SEND_MAIL_SUBJECT_CANCELED']; ?>");


			$j("#target_mail_patern").val("confirm").change();



		});


		function fnClickUpdate() {
			if ( ! checkItem("data_detail") ) return false;


			var tmpBcc = $j("#mail_bcc").val();
			var tmpFrom = $j("#mail_from").val();
			var checkMail = Array();
			if (tmpBcc && (tmpFrom || $j("#mail_returnPath").val()) ) {
				tmpArray = tmpFrom.split(/<|>/);
				if (tmpArray[1]) {
					checkMail.push(tmpArray[1].trim());
				}
				checkMail.push($j("#mail_returnPath").val().trim());
				var targetMails = tmpBcc.split(/,/);
				for (var i = 0; i < targetMails.length; i++ ){
					if (checkMail.indexOf(targetMails[i]) != -1 ) {
						$j("#target_mail_patern").val("information").trigger("change");
						alert("<?php _e('\"from email address\" and \"Mail address (Bcc)\" shoud be deffernt',SL_DOMAIN); ?>");
						break;
					}
				}
			}


			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slmail",
					dataType : "json",
					data: {
						"config_mail_text":$j("#send_mail_text").val()
						,"config_mail_text_user":$j("#regist_mail_text").val()
						,"config_mail_text_information":$j("#information_mail_text").val()
						,"config_mail_subject":$j("#send_mail_subject").val()
						,"config_mail_subject_user":$j("#regist_mail_subject").val()
						,"config_mail_subject_information":$j("#information_mail_subject").val()
						,"config_mail_subject_completed":$j("#sl_send_mail_subject_completed").val()
						,"config_mail_subject_accepted":$j("#sl_send_mail_subject_accepted").val()
						,"config_mail_subject_canceled":$j("#sl_send_mail_subject_canceled").val()
						,"config_mail_text_completed":$j("#sl_send_mail_text_completed").val()
						,"config_mail_text_accepted":$j("#sl_send_mail_text_accepted").val()
						,"config_mail_text_canceled":$j("#sl_send_mail_text_canceled").val()
						,"config_mail_from":$j("#mail_from").val()
						,"config_mail_returnPath":$j("#mail_returnPath").val()
						,"config_mail_bcc":tmpBcc
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Mail_Edit"

					},
					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							alert(data.message);
							location.reload();
						}
			        },
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });
		}

		<?php parent::echoCheckClinet(array('chk_required','num','lenmax','chkMail')); ?>

	</script>

	<h2 id="sl_admin_title"><?php _e('Mail Setting',SL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<div id="salon_button_div" >
	<input id="button_update" type="button" value="<?php _e('update',SL_DOMAIN); ?>"/>
	</div>
	<div id="data_detail" >


		<input type="text" id="mail_from" />
		<input type="text" id="mail_returnPath" />
		<select id="target_mail_patern" >
			<option value="confirm" ><?php _e('Confirmation mail',SL_DOMAIN); ?></option>
			<option value="regist" ><?php _e('Newly registered as a Member mail',SL_DOMAIN); ?></option>
			<option value="completed" ><?php _e('Reservation complete mail',SL_DOMAIN); ?></option>
			<option value="canceled" ><?php _e('Reservation canceled mail',SL_DOMAIN); ?></option>
			<option value="accepted" ><?php _e('Reservation receipt mail',SL_DOMAIN); ?></option>
			<option value="information" ><?php _e('Notification mail to staff',SL_DOMAIN); ?></option>

		</select>
        <div id="sl_mail_wrap_bcc" >
        <textarea id="mail_bcc" ></textarea>
        </div>
		<div id="sl_mail_warp_confirm" class="sl_mail_wrap" >
			<input id="send_mail_subject"  />
			<textarea id="send_mail_text" class="sl_mail_area" ></textarea>
		</div>
		<div id="sl_mail_warp_regist" class="sl_mail_wrap">
			<input id="regist_mail_subject"  />
			<textarea id="regist_mail_text"  class="sl_mail_area"></textarea>
		</div>
		<div id="sl_mail_warp_completed" class="sl_mail_wrap" >
			<input id="sl_send_mail_subject_completed"  />
			<textarea id="sl_send_mail_text_completed" class="sl_mail_area" ></textarea>
		</div>
		<div id="sl_mail_warp_canceled" class="sl_mail_wrap" >
			<input id="sl_send_mail_subject_canceled"  />
			<textarea id="sl_send_mail_text_canceled" class="sl_mail_area" ></textarea>
		</div>
		<div id="sl_mail_warp_accepted" class="sl_mail_wrap" >
			<input id="sl_send_mail_subject_accepted"  />
			<textarea id="sl_send_mail_text_accepted" class="sl_mail_area" ></textarea>
		</div>
		<div id="sl_mail_warp_information" class="sl_mail_wrap">
			<input id="information_mail_subject"  />
			<textarea id="information_mail_text"  class="sl_mail_area"></textarea>
		</div>




		<div class="spacer"></div>
	</div>


<?php
	}	//show_page
}		//class

