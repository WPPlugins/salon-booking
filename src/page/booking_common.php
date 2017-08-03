<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class BookingCommon_Page extends Salon_Page {

	static function echoSearchCustomer($url = '') {
		if (empty($url) ) $url = get_bloginfo( 'wpurl' );
		$target_src = $url.'/wp-admin/admin-ajax.php?action=slsearch';
		$check_char = __('No',SL_DOMAIN);
		echo <<<EOT
			\$j("#sl_button_search").click(function(){
				var get_name = \$j("#sl_name").val().trim();
				var get_mail = \$j("#sl_mail").val().trim();
				var get_tel = \$j("#sl_tel").val().trim();
				if (get_name || get_mail || get_tel) {
					\$j("#sl_button_search").prop("disabled",true);
					\$j.ajax({
						type: "post",
						url:  "{$target_src}",
						dataType : "json",
						data: {
							"type":"reservation",
							"name":get_name,
							"mail":get_mail,
							"menu_func":"Search_Page",
							"tel":get_tel
						},

						success: function(data) {
							if (data.status == "Error" ) {
								alert(data.message);
							}
							else {
								var mW = \$j("#sl_search").find('.modalBody').innerWidth() / 2;
								var mH = \$j("#sl_search").find('.modalBody').innerHeight() / 2;
								\$j("#sl_search").find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
								\$j("#sl_search").css({'display':'block'});
								\$j("#sl_search").animate({'opacity':'1'},'fast');
								\$j("#sl_search_result").html(data.set_data);
								if (+data.cnt > 0 ) {
									\$j("#sl_search_result tr").click(function(event) {
										if (this.children[0].innerHTML == "{$check_char}" ) return;
										var name = this.children[1].innerHTML;
										\$j("#sl_name").val(name);
										var tel = this.children[2].innerHTML;
										if (! tel) tel = this.children[3].innerHTML;
										\$j("#sl_tel").val(tel);
										\$j("#sl_mail").val(this.children[4].innerHTML);
										save_name = name;
										save_tel = tel;
										save_mail = this.children[4].innerHTML;
										save_user_login = \$j(this).find("input").val();
										fnRemoveModalResult(this.parentNode.parentNode);
									});
								}
							}
							\$j("#sl_button_search").prop("disabled",false);
						},
						error:  function(XMLHttpRequest, textStatus){
							alert (textStatus);
							\$j("#sl_button_search").prop("disabled",false);
						}
					});
				}
			});
		\$j("#sl_button_close1,#sl_button_close2").click(function(){
			fnRemoveModalResult(this);
		});
EOT;

	}

	static function echoClientItem($items) {
		$item_contents = Salon_Page::setItemContents();
		echo 'var check_items = { ';
		$tmp = array();
		if (is_array($items) ){
			foreach ($items as $d1) {
				$placeholder = "";
				if (!empty($item_contents[$d1]['place'])) {
					$placeholder = ',"placeholder" : "'.$item_contents[$d1]['place'].'"';
				}
				$add_class = '';
				$tmp[] ='"'.$item_contents[$d1]['id'].'": '.
						'{'.
						' "id" : "sl_'.$item_contents[$d1]['id'].'"'.
						',"class" : "'.implode(" ",$item_contents[$d1]['check'])." ".implode(" ",$item_contents[$d1]['class']).'"'.
						',"label" : "'.$item_contents[$d1]['label'].'"'.
						',"tips" : "'.$item_contents[$d1]['tips'].'"'.
						$placeholder.
						'}';
			}
		}
		echo join(',',$tmp);
		echo '};';
		self::echoHtmlpecialchars();

	}

	static function echoClientItemMobile($items) {
		$item_contents = Salon_Page::setItemContents();
		echo 'var check_items = { ';
		$tmp = array();
		if (is_array($items) ){
			foreach ($items as $d1) {
				$placeholder = "";
				if (!empty($item_contents[$d1]['place'])) {
					$placeholder = ',"placeholder" : "'.$item_contents[$d1]['place'].'"';
				}
				$add_class = '';
				$tmp[] ='"'.$item_contents[$d1]['id'].'": '.
						'{'.
						' "id" : "sl_'.$item_contents[$d1]['id'].'"'.
						',"label" : "'.$item_contents[$d1]['label'].'"'.
						$placeholder.
						'}';
			}
		}
		echo join(',',$tmp);
		echo '};';
		self::echoHtmlpecialchars();

	}

	static function echoItemInputCheckTable($item_datas,$is_noEcho = false,$is_displayMenu = true
			,$type = "checkbox"){
		$echo_data  = '<div id="sl_item_cds" class="sl_checkbox" >';
		if ( count($item_datas) == 1 && $is_displayMenu === false) {
			$echo_data  = '<div id="sl_item_cds_wrap" style="display:none" >'.$echo_data;
		}
		if ($item_datas) {
			$echo_data .= '<table id="sl_front_items"><tbody>';
			$loop_max = count($item_datas);
			for($i = 0 ; $i < $loop_max ; $i += 2 ){
				$echo_data .= '<tr>';
				for($j= 0 ; $j < 2 ; $j++ ) {
					if ( $loop_max > ($i+$j) ) {
						$d1 = $item_datas[$i+$j];
						$edit_price = number_format($d1['price']);
						$edit_name = htmlspecialchars($d1['name'],ENT_QUOTES);
						$echo_data .= <<<EOT
							<td>
							<input type="$type" name="itemCheck" id="sl_check_{$d1['item_cd']}" value="{$d1['item_cd']}" />
							<input type="hidden" id="sl_check_price_{$d1['item_cd']}" value="{$d1['price']}" />
							<input type="hidden" id="sl_check_minute_{$d1['item_cd']}" value="{$d1['minute']}" />
							</td><td>
							<label for="sl_check_{$d1['item_cd']}" class="sl_items_label" id="sl_items_lbl_{$d1['item_cd']}">{$edit_name}({$edit_price})</label>
							</td>
EOT;
					}
				}
				$echo_data .= '</tr>';
			}
			$echo_data .= "</tbody></table>";
		}
		else {
			$echo_data .= self::setNoMenuData();
		}
		$echo_data .= '</div>';
		if ( count($item_datas) == 1 && $is_displayMenu === false) {
			$echo_data  .= '</div>';
		}
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;

	}

	static function echoItemInputCheckTableForOneColunn($item_datas,$is_displayMenu = true
			,$type = "checkbox"){
		$echo_data  = '<div id="sl_item_cds" class="sl_checkbox" >';
		if ( count($item_datas) == 1 && $is_displayMenu === false) {
			$echo_data  = '<div id="sl_item_cds_wrap" style="display:none" >'.$echo_data;
		}
		if ($item_datas) {
			$echo_data .= '<table id="sl_front_items"><tbody>';
			$loop_max = count($item_datas);
			for($i = 0 ; $i < $loop_max ; $i++ ){
				$echo_data .= '<tr>';
						$d1 = $item_datas[$i];
						$edit_price = number_format($d1['price']);
						$edit_name = htmlspecialchars($d1['name'],ENT_QUOTES);
						$echo_data .= <<<EOT
							<td>
							<input type="{$type}" name="itemCheck" id="sl_check_{$d1['item_cd']}" value="{$d1['item_cd']}" />
							<input type="hidden" id="sl_check_price_{$d1['item_cd']}" value="{$d1['price']}" />
							<input type="hidden" id="sl_check_minute_{$d1['item_cd']}" value="{$d1['minute']}" />
							</td><td>
							<label for="sl_check_{$d1['item_cd']}" class="sl_items_label" id="sl_items_lbl_{$d1['item_cd']}">{$edit_name}({$edit_price})</label>
							</td>
EOT;
				$echo_data .= '</tr>';
			}
			$echo_data .= "</tbody></table>";
		}
		else {
			$echo_data .= self::setNoMenuData();
		}
		$echo_data .= '</div>';
		if (  count($item_datas) == 1 && $is_displayMenu === false) {
			$echo_data  .= '</div>';
		}
		echo $echo_data;

	}

	static function echoCheckClinet($check_patern) {
		$default_margin = self::INPUT_BOTTOM_MARGIN;
		echo <<<EOT
			function checkItem(target,except ) {
				var is_error = false;
				var tmp_excepts = Array();
				var focusId = "";
				if (except) {
					if (except.indexOf(",") > -1) {
						var tmp_excepts = except.split(",");
					}
					else {
						tmp_excepts.push(except);
					}
				}
				\$j("#"+target).find("input[type=text],textarea,select,.sl_checkbox").each(function(){
					if (\$j(this).hasClass("sl_nocheck") ) return;
					var id = \$j(this).attr("id");
					//this variable use for the refactoring about id
					var oid = id.replace("sl_","");
					if (except) {
						for(var i=0;i<tmp_excepts.length;i++){
							if ( id == tmp_excepts[i] ) return;
						}

					}
					var item_errors = Array();
					var cl = \$j(this).attr("class");
					if (cl) {
						var val = \$j(this).val();
EOT;
		$check_contents = self::setCheckContents();
		$key = array_search('chk_required',$check_patern);
		if ($key !== false) {
			echo $check_contents['chk_required'];
			unset($check_patern[$key]);
		}
		$key = array_search('reqOther',$check_patern) ;
		if ($key !== false) {
			echo $check_contents['reqOther'];
			unset($check_patern[$key]);
		}
		$key = array_search('reqCheck',$check_patern);
		if ($key !== false) {
			echo $check_contents['reqCheckbox'];
			unset($check_patern[$key]);
		}
		$key = array_search('reqCheck_radio',$check_patern);
		if ($key !== false) {
			echo $check_contents['reqRadio'];
			unset($check_patern[$key]);
		}
		if ( count($check_patern) > 0 ) {
			echo 'if (( item_errors.length == 0 ) && (val != "" ) && (val != null) ){';
			foreach ($check_patern as $d1) {
				echo $check_contents[$d1];
			}
			echo '}';
		}

		echo <<<EOT2
					}

					\$j(this).removeAttr("style");
					var label = \$j(this).prev().children(".small");
					label.removeClass("sl_coler_not_complete");
					label.removeAttr("style");
					if (  item_errors.length > 0 ) {
						label.text(item_errors.join(" "));
						label.addClass("error small");
						is_error = true;
						if (focusId == "") focusId = this.id;
						var label_tag = \$j(this).prev();
						var diff = label_tag.outerHeight(true) - \$j(this).outerHeight(true);
						if (diff > 0 ) {
							diff += {$default_margin}+5;
							\$j(this).attr("style","margin-bottom: "+diff+"px;");
							label.attr("style","text-align:left;");
						}
					}
					else {
						label.text(check_items[oid]["tips"]);
						label.removeClass("error");
						var label_tag = \$j(this).prev();
						var diff = label_tag.outerHeight(true) - \$j(this).outerHeight(true);
						if (diff > 0 ) {
							diff += {$default_margin}+5;
							\$j(this).attr("style","margin-bottom: "+diff+"px;");
							label.attr("style","text-align:left;");
						}
					}
				});
				if ( is_error ) {
					\$j("#"+focusId).focus();
					return false;
				}
				return true;
			}
EOT2;

	}

	static function echoBlueLogic() {
		$msg = __('please enter',SL_DOMAIN);
		$default_margin = parent::INPUT_BOTTOM_MARGIN;
		echo <<<EOT
		function _fnCheckOfRequire() {
			var val = \$j(this).val();
			var label = \$j(this).prev().children(".small");
			label.removeClass("sl_coler_not_complete");
			label.removeAttr("style");
			if(val == "" || val === null){
				setTimeout(function(){
				label.text("{$msg}");
				label.addClass("error small");
				var label_tag = \$j(this).prev();
				var diff = label_tag.outerHeight(true) - \$j(this).outerHeight(true);
				if (diff > 0 ) {
					diff += {$default_margin}+5;
					\$j(this).attr("style","margin-bottom: "+diff+"px;");
					label.attr("style","text-align:left;");
				}
				},300);
			}
			else {
				var id = \$j(this).attr("id").replace("sl_","");
				label.text(check_items[id]["tips"]);
				label.removeClass("error");
				var label_tag = \$j(this).prev();
				var diff = label_tag.outerHeight(true) - \$j(this).outerHeight(true);
				if (diff > 0 ) {
					diff += {$default_margin}+5;
					\$j(this).attr("style","margin-bottom: "+diff+"px;");
					label.attr("style","text-align:left;");
				}
			}
		}
EOT;
	}

	static function echoClientBlur($items,$isMobile=false) {
		echo '$j(function(){';
		$isExist = false;
		$item_contents = Salon_Page::setItemContents();
		if (is_array($items) ){
			foreach ($items as $d1) {
				if (in_array("chk_required",$item_contents[$d1]['check']))	{
					$isExist = true;
					echo '$j("#sl_'.$item_contents[$d1]['id'].'").blur(_fnCheckOfRequire);';
					if ($isMobile) {
						echo '$j("#sl_'.$item_contents[$d1]['id'].'").focus(_fnFocusOfRequire);';
					}
				}
			}
		}
		if ($isExist) {
			if ($isMobile) {
				self::echoBlueLogicMobile();
			}
			else {
				self::echoBlueLogic();
			}
		}
		echo '});';

	}

}
